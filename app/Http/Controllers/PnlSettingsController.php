<?php

namespace App\Http\Controllers;

use App\Models\PnlRate;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PnlSettingsController extends Controller
{
    // Show one sheet's rate timeline + the "add new rate" form
    public function index(Request $request)
    {
        // Disabled sheets are included, because their old P&L still needs rates
        $stores = Store::orderBy('name')->orderBy('product_name')->get();

        $store = Store::find($request->query('store')) ?? $stores->first();

        $rates = $store ? $store->pnlRates()->get() : collect();

        // Latest rate is used to pre-fill the "add new rate" form
        $latest = $rates->last();

        return view('pnl.settings', compact('stores', 'store', 'rates', 'latest'));
    }

    // Add a new rate (valid from a given date)
    public function store(Request $request)
    {
        $request->validate(
            ['store_id' => ['required', 'exists:stores,id']] + $this->rules($request->store_id),
            $this->messages()
        );

        PnlRate::create($request->only([
            'store_id', 'effective_from', 'delivery_percent', 'margin',
            'rto_charge', 'delivered_charge', 'gst_percent',
        ]));

        return redirect()
            ->route('pnl.settings', ['store' => $request->store_id])
            ->with('success', 'New rate added.');
    }

    // Edit an existing rate row
    public function update(Request $request, PnlRate $rate)
    {
        $request->validate($this->rules($rate->store_id, $rate->id), $this->messages());

        $rate->update($request->only([
            'effective_from', 'delivery_percent', 'margin',
            'rto_charge', 'delivered_charge', 'gst_percent',
        ]));

        return redirect()
            ->route('pnl.settings', ['store' => $rate->store_id])
            ->with('success', 'Rate updated. P&L for the affected days will recalculate automatically.');
    }

    // Delete a rate row
    public function destroy(PnlRate $rate)
    {
        $storeId = $rate->store_id;
        $rate->delete();

        return redirect()
            ->route('pnl.settings', ['store' => $storeId])
            ->with('success', 'Rate deleted. P&L for the affected days will recalculate automatically.');
    }

    private function rules($storeId, $ignoreId = null): array
    {
        return [
            'effective_from' => [
                'required', 'date',
                Rule::unique('pnl_rates', 'effective_from')
                    ->where(fn ($q) => $q->where('store_id', $storeId))
                    ->ignore($ignoreId),
            ],
            'delivery_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'margin' => ['required', 'numeric', 'min:0'],
            'rto_charge' => ['required', 'numeric', 'min:0'],
            'delivered_charge' => ['required', 'numeric', 'min:0'],
            'gst_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    private function messages(): array
    {
        return [
            'effective_from.unique' => 'This sheet already has a rate starting on that date. Edit that row instead.',
        ];
    }
}