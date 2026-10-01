<?php

namespace App\Http\Controllers;

use App\Models\AdSpend;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdSpendController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::where('is_active', true)->orderBy('name')->orderBy('product_name')->get();

        $store = Store::find($request->query('store')) ?? $stores->first();

        // A disabled sheet can still be opened to view old data
        $options = $stores->when($store && !$store->is_active, fn ($c) => $c->push($store));

        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('month'))
            ? $request->query('month')
            : now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();

        $entries = collect();
        if ($store) {
            $entries = AdSpend::where('store_id', $store->id)
                ->whereBetween('date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])
                ->get()
                ->keyBy(fn ($e) => $e->date->format('Y-m-d'));
        }

        $rows = [];
        for ($d = 1; $d <= $start->daysInMonth; $d++) {
            $date = $start->copy()->day($d);
            $e = $entries->get($date->format('Y-m-d'));
            $rows[] = [
                'date' => $date,
                'orders' => $e->orders ?? 0,
                'ad_cost' => $e->ad_cost ?? 0,
            ];
        }

        return view('ad-spend.index', compact('options', 'store', 'month', 'rows'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'entries' => ['array'],
            'entries.*.orders' => ['nullable', 'integer', 'min:0'],
            'entries.*.ad_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $prefix = $request->month . '-';

        foreach ($request->input('entries', []) as $date => $row) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
                || !str_starts_with($date, $prefix)
                || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
                continue;
            }

            $orders = (int) ($row['orders'] ?? 0);
            $cost = round((float) ($row['ad_cost'] ?? 0), 2);

            $entry = AdSpend::firstOrNew(['store_id' => $request->store_id, 'date' => $date]);

            // Do not create empty rows
            if (!$entry->exists && $orders === 0 && $cost == 0) {
                continue;
            }
            if (!$entry->exists) {
                $entry->created_by = auth()->id();
            }

            $entry->orders = $orders;
            $entry->ad_cost = $cost;
            $entry->save();
        }

        return redirect()
            ->route('ad-spend', ['store' => $request->store_id, 'month' => $request->month])
            ->with('success', 'Ad spend saved.');
    }
}