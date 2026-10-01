@extends('layouts.app')

@section('title', 'Ad Spend — JENHIKX')

@section('page-title')
    Log daily <span class="jx-chip lime">@include('partials.icon', ['name' => 'adspend'])</span> spend<br>
    for every sheet
@endsection

@section('header-actions')
    @if (Auth::user()->role === 'admin')
        <a href="{{ route('ad-spend.sheets') }}" class="jx-btn jx-btn-round" style="width:auto;padding:0 20px;">Manage sheets</a>
    @endif
    @if ($store)
        <button type="submit" form="save-form" class="jx-btn jx-btn-dark">Save changes</button>
    @endif
@endsection

@section('content')

@if (!$store)
    <div class="jx-card text-center py-12">
        <p class="text-lg font-medium">No sheets yet</p>
        <p class="text-sm text-[var(--muted)] mt-1">A sheet is one store and one product. Add your first one to start logging.</p>
        @if (Auth::user()->role === 'admin')
            <a href="{{ route('ad-spend.sheets') }}" class="jx-btn jx-btn-dark mt-5">Add a sheet</a>
        @endif
    </div>
@else

    {{-- Filters --}}
    <form method="GET" action="{{ route('ad-spend') }}" class="flex flex-wrap items-center gap-2 mb-4">
        <select name="store" class="jx-input" onchange="this.form.submit()">
            @foreach ($options as $opt)
                <option value="{{ $opt->id }}" @selected($opt->id === $store->id)>
                    {{ $opt->label }}{{ $opt->is_active ? '' : ' (disabled)' }}
                </option>
            @endforeach
        </select>
        <input type="month" name="month" value="{{ $month }}" class="jx-input" onchange="this.form.submit()">
    </form>

    {{-- Summary --}}
    <div class="grid gap-4 md:grid-cols-3 mb-4">
        <div class="jx-card lime">
            <div class="flex items-center gap-3">
                <div class="jx-iconchip">@include('partials.icon', ['name' => 'adspend'])</div>
                <span class="text-sm font-medium">Total ad spend</span>
            </div>
            <div id="sum-cost" class="mt-4 text-4xl font-medium tracking-tight">₹0.00</div>
        </div>
        <div class="jx-card">
            <div class="flex items-center gap-3">
                <div class="jx-iconchip">@include('partials.icon', ['name' => 'home'])</div>
                <span class="text-sm font-medium">Total orders</span>
            </div>
            <div id="sum-orders" class="mt-4 text-4xl font-medium tracking-tight">0</div>
        </div>
        <div class="jx-card dark">
            <span class="text-sm text-[#B9BEB6]">Cost per order (CPA)</span>
            <div id="sum-cpa" class="mt-4 text-4xl font-medium tracking-tight">—</div>
        </div>
    </div>

    {{-- Sheet --}}
    <form id="save-form" method="POST" action="{{ route('ad-spend.save') }}">
        @csrf
        <input type="hidden" name="store_id" value="{{ $store->id }}">
        <input type="hidden" name="month" value="{{ $month }}">

        <div class="bg-white border border-[var(--line)] rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="jx-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="hidden md:table-cell">Store name</th>
                            <th class="hidden md:table-cell">Product name</th>
                            <th class="r">Orders</th>
                            <th class="r">Ad cost (excl. GST)</th>
                            <th class="r">CPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php $key = $row['date']->format('Y-m-d'); @endphp
                            <tr data-row class="{{ $row['date']->isToday() ? 'today' : '' }}">
                                <td class="whitespace-nowrap">
                                    {{ $row['date']->format('d M Y') }}
                                    <span class="text-xs text-[var(--muted)] ml-1">{{ $row['date']->format('D') }}</span>
                                </td>
                                <td class="hidden md:table-cell text-[var(--muted)]">{{ $store->name }}</td>
                                <td class="hidden md:table-cell">{{ $store->product_name }}</td>
                                <td class="r">
                                    <input type="number" min="0" step="1" inputmode="numeric" data-orders
                                           name="entries[{{ $key }}][orders]" value="{{ $row['orders'] }}"
                                           class="jx-input num" oninput="recalc()" onfocus="this.select()">
                                </td>
                                <td class="r">
                                    <input type="number" min="0" step="0.01" inputmode="decimal" data-cost
                                           name="entries[{{ $key }}][ad_cost]" value="{{ $row['ad_cost'] }}"
                                           class="jx-input num" oninput="recalc()" onfocus="this.select()">
                                </td>
                                <td class="r font-medium" data-cpa>—</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td class="hidden md:table-cell"></td>
                            <td class="hidden md:table-cell"></td>
                            <td class="r" id="foot-orders">0</td>
                            <td class="r" id="foot-cost">₹0.00</td>
                            <td class="r" id="foot-cpa">—</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <button type="submit" class="jx-btn jx-btn-dark">Save changes</button>
        </div>
    </form>

    <script>
        function money(n) {
            return '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function recalc() {
            var orders = 0, cost = 0;
            document.querySelectorAll('[data-row]').forEach(function (tr) {
                var o = parseFloat(tr.querySelector('[data-orders]').value) || 0;
                var c = parseFloat(tr.querySelector('[data-cost]').value) || 0;
                orders += o; cost += c;
                tr.querySelector('[data-cpa]').textContent = o > 0 ? money(c / o) : '—';
            });
            var cpa = orders > 0 ? money(cost / orders) : '—';
            document.getElementById('sum-cost').textContent = money(cost);
            document.getElementById('sum-orders').textContent = orders.toLocaleString('en-IN');
            document.getElementById('sum-cpa').textContent = cpa;
            document.getElementById('foot-orders').textContent = orders.toLocaleString('en-IN');
            document.getElementById('foot-cost').textContent = money(cost);
            document.getElementById('foot-cpa').textContent = cpa;
        }
        recalc();
    </script>

@endif
@endsection