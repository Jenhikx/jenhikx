<?php
// app\Services\DashboardService.php

namespace App\Services;

use App\Models\AdditionalIncome;
use App\Models\AdSpend;
use App\Models\Expense;
use App\Models\FounderWithdrawal;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(private PnlCalculator $calc)
    {
    }

    /**
     * period: 'this_month' | 'last_month' | 'this_year' | 'all_time'
     */
    public function build(string $period): array
    {
        [$start, $end, $granularity] = $this->range($period);

        $stores = Store::orderBy('name')->orderBy('product_name')->get();

        // ---- Per-store, per-day results for the whole range (used everywhere below) ----
        $dayRows = $this->collectDayRows($stores, $start, $end);

        // ---- Top cards ----
        $totals = [
            'orders' => 0, 'ad_cost_with_gst' => 0, 'net_pnl' => 0,
        ];
        foreach ($dayRows as $row) {
            if (!$row['result']) continue;
            $totals['orders'] += $row['result']['orders'];
            $totals['ad_cost_with_gst'] += $row['result']['ad_cost_with_gst'];
            $totals['net_pnl'] += $row['result']['net_pnl'];
        }

        $s = $start->toDateString();
        $e = $end->toDateString();

        $income = AdditionalIncome::whereBetween('date', [$s, $e])->sum('amount');
        $expenses = Expense::whereBetween('date', [$s, $e])->sum('amount');
        $withdrawals = FounderWithdrawal::whereBetween('date', [$s, $e])->sum('amount');

        // Founder-wise breakdown, taaki dashboard par "kisne kitna nikala" dikh sake
        $withdrawalsByFounder = FounderWithdrawal::whereBetween('date', [$s, $e])
            ->selectRaw('founder, sum(amount) as total')->groupBy('founder')->pluck('total', 'founder');

        // Income aur Expenses ka bhi source/category-wise breakdown — "Where P&L Went" me dikhega
        $incomeBySource = AdditionalIncome::whereBetween('date', [$s, $e])
            ->selectRaw('source as label, sum(amount) as total')->groupBy('source')->orderByDesc('total')->get();

        $expensesByCategory = Expense::whereBetween('date', [$s, $e])
            ->selectRaw('category as label, sum(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        $withdrawalsBreakdown = FounderWithdrawal::whereBetween('date', [$s, $e])
            ->selectRaw('founder as label, sum(amount) as total')->groupBy('founder')->orderByDesc('total')->get();

        $purse = $totals['net_pnl'] + $income - $expenses - $withdrawals;

        // ---- P&L Trend: date -> [orders, ad_cost_with_gst, net_pnl, stores => [name, orders, net_pnl]] ----
        $trend = $this->buildTrend($dayRows);

        // ---- P&L by store (only stores with orders > 0 in this period) ----
        $byStore = $this->groupBy($dayRows, fn ($r) => $r['store']->label);

        // ---- P&L by product ----
        $byProduct = $this->groupBy($dayRows, fn ($r) => $r['store']->product_name);

        return compact(
            'start', 'end', 'totals', 'income', 'expenses', 'withdrawals',
            'withdrawalsByFounder', 'incomeBySource', 'expensesByCategory', 'withdrawalsBreakdown',
            'purse', 'trend', 'byStore', 'byProduct'
        );
    }

    /**
     * Pichhle period ka Net P&L — dashboard ke "vs prev" badge ke liye.
     *
     * - this_month : pichhla mahina (utne hi din jitne is mahine ke data me hain)
     * - last_month : uske pichhla mahina (poora)
     * - this_year  : pichhla saal (utne hi din)
     * - all_time   : null (compare karne ko kuch nahi)
     *
     * $upTo = current period ki aakhri date jiska data hai (taaki fair comparison ho).
     */
    public function previousNetPnl(string $period, ?Carbon $upTo = null): ?float
    {
        if ($period === 'all_time') {
            return null;
        }

        [$start] = $this->range($period);

        if ($period === 'this_year') {
            $ps = $start->copy()->subYear()->startOfYear();
            $pe = $ps->copy()->endOfYear();
        } else {
            $ps = $start->copy()->subMonthNoOverflow()->startOfMonth();
            $pe = $ps->copy()->endOfMonth();
        }

        if ($upTo && $period !== 'last_month') {
            $span = (int) abs($start->copy()->startOfDay()->diffInDays($upTo->copy()->startOfDay()));
            $cap = $ps->copy()->addDays($span)->endOfDay();
            if ($cap->lt($pe)) {
                $pe = $cap;
            }
        }

        $rows = $this->collectDayRows(Store::get(), $ps, $pe);

        return (float) $rows->sum(fn ($r) => $r['result']['net_pnl'] ?? 0);
    }

    private function range(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth(), 'daily'],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'monthly'],
            'all_time' => [Carbon::create(2000, 1, 1), $now->copy()->endOfYear(), 'monthly'],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'daily'], // this_month
        };
    }

    /**
     * Har store ke har din ka result nikalta hai (sirf jin dino Ad Spend entry hui ho).
     */
    private function collectDayRows(Collection $stores, Carbon $start, Carbon $end): Collection
    {
        $rows = collect();

        foreach ($stores as $store) {
            $rates = $store->pnlRates()->get();

            $entries = AdSpend::where('store_id', $store->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get();

            foreach ($entries as $e) {
                $rows->push([
                    'date' => $e->date,
                    'store' => $store,
                    'result' => $this->calc->calculateDay($e->date, $e->orders, (float) $e->ad_cost, $rates),
                ]);
            }
        }

        return $rows;
    }

    private function buildTrend(Collection $dayRows): array
    {
        $byDate = $dayRows->filter(fn ($r) => $r['result'])->groupBy(fn ($r) => $r['date']->format('Y-m-d'));

        $trend = [];
        foreach ($byDate as $dateKey => $rows) {
            $storeSplit = $rows->map(fn ($r) => [
                'store' => $r['store']->label,
                'orders' => $r['result']['orders'],
                'net_pnl' => $r['result']['net_pnl'],
            ])->values()->all();

            $trend[] = [
                'date' => Carbon::parse($dateKey),
                'orders' => $rows->sum(fn ($r) => $r['result']['orders']),
                'ad_cost_with_gst' => $rows->sum(fn ($r) => $r['result']['ad_cost_with_gst']),
                'net_pnl' => $rows->sum(fn ($r) => $r['result']['net_pnl']),
                'stores' => $storeSplit,
            ];
        }

        // Oldest date first (1st of the month at the top)
        usort($trend, fn ($a, $b) => $a['date']->timestamp <=> $b['date']->timestamp);

        return $trend;
    }

    private function groupBy(Collection $dayRows, callable $labelFn): array
    {
        $groups = [];
        foreach ($dayRows as $row) {
            if (!$row['result']) continue;
            $label = $labelFn($row);
            $groups[$label]['orders'] = ($groups[$label]['orders'] ?? 0) + $row['result']['orders'];
            $groups[$label]['ad_cost_with_gst'] = ($groups[$label]['ad_cost_with_gst'] ?? 0) + $row['result']['ad_cost_with_gst'];
            $groups[$label]['net_pnl'] = ($groups[$label]['net_pnl'] ?? 0) + $row['result']['net_pnl'];
        }

        $out = [];
        foreach ($groups as $label => $vals) {
            $out[] = array_merge(['label' => $label], $vals);
        }

        usort($out, fn ($a, $b) => $b['net_pnl'] <=> $a['net_pnl']);

        return $out;
    }
}