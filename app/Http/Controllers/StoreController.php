<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::orderBy('name')->orderBy('product_name')->get();
        return view('ad-spend.sheets', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_name' => [
                'required', 'string', 'max:255',
                Rule::unique('stores', 'product_name')->where(fn ($q) => $q->where('name', $request->name)),
            ],
        ]);

        Store::create($request->only('name', 'product_name'));

        return back()->with('success', 'Sheet added.');
    }

    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_name' => [
                'required', 'string', 'max:255',
                Rule::unique('stores', 'product_name')
                    ->where(fn ($q) => $q->where('name', $request->name))
                    ->ignore($store->id),
            ],
        ]);

        $store->update($request->only('name', 'product_name'));

        return back()->with('success', 'Sheet updated.');
    }

    public function toggle(Store $store)
    {
        $store->update(['is_active' => !$store->is_active]);
        return back()->with('success', $store->is_active ? 'Sheet activated.' : 'Sheet disabled.');
    }
}