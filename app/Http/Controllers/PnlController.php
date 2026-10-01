<?php

namespace App\Http\Controllers;

use App\Models\AdSpend;
use App\Models\Store;
use App\Services\PnlCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PnlController extends Controller
{
    public function index(Request $request, PnlCalculator $calc)
    {
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('month'))
            ? $request->query('month')
            : now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth();

        $stores = Store::orderBy('name')->orderBy('product_name')->get();

        $selected = $request->query('store', 'all');
        $store = $selected !== 'all' ? Store::find($selected) : null;

        // ---- "All" view: company totals + one row per store ----
        $company = $this->aggregate($stores, $start, $end, $calc);

        $storeSummaries = $stores->map(function ($s) use ($start, $end, $calc) {
            return array_merge(['store' => $s], $this->aggregate(collect([$s]), $start, $end, $calc));
        });

        // ---- Specific store view: totals + daily rows ----
        $selectedTotals = null;
        $days = [];

        if ($store) {
            $selectedTotals = $this->aggregate(collect([$store]), $start, $end, $calc);

            $rates = $store->pnlRates()->get();

            $entries = AdSpend::where('store_id', $store->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date')
                ->get();

            foreach ($entries as $e) {
                $days[] = [
                    'date' => $e->date,
                    'result' => $calc->calculateDay($e->date, $e->orders, (float) $e->ad_cost, $rates),
                ];
            }
        }

        return view('pnl.index', compact(
            'stores', 'store', 'selected', 'month',
            'company', 'storeSummaries', 'selectedTotals', 'days'
        ));
    }

    /**
     * Ek ya zyada sheets ke, ek month ke saare entries jodkar totals nikalta hai.
     * "All" view ke company card aur har sheet ki summary row, dono isi se banti hain.
     */
    private function aggregate(Collection $stores, Carbon $start, Carbon $end, PnlCalculator $calc): array
    {
        $totals = [
            'orders' => 0, 'ad_cost' => 0, 'ad_cost_with_gst' => 0, 'net_pnl' => 0,
            'break_even_weighted' => 0, 'break_even_with_gst_weighted' => 0,
            'missing_rate_days' => 0,
        ];

        foreach ($stores as $s) {
            $rates = $s->pnlRates()->get();

            $entries = AdSpend::where('store_id', $s->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get();

            foreach ($entries as $e) {
                $r = $calc->calculateDay($e->date, $e->orders, (float) $e->ad_cost, $rates);

                if (!$r) {
                    $totals['missing_rate_days']++;
                    continue;
                }

                $totals['orders'] += $r['orders'];
                $totals['ad_cost'] += $r['ad_cost'];
                $totals['ad_cost_with_gst'] += $r['ad_cost_with_gst'];
                $totals['net_pnl'] += $r['net_pnl'];
                $totals['break_even_weighted'] += $r['break_even_cpa'] * $r['orders'];
                $totals['break_even_with_gst_weighted'] += $r['break_even_cpa_with_gst'] * $r['orders'];
            }
        }

        $totals['cpa'] = $totals['orders'] > 0 ? $totals['ad_cost'] / $totals['orders'] : null;
        $totals['break_even_cpa'] = $totals['orders'] > 0 ? $totals['break_even_weighted'] / $totals['orders'] : null;
        $totals['break_even_cpa_with_gst'] = $totals['orders'] > 0 ? $totals['break_even_with_gst_weighted'] / $totals['orders'] : null;

        return $totals;
    }
}