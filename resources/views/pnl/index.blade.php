@extends('layouts.app')

@section('title', 'P&L — JENHIKX')

@section('page-title')
    See your <span class="jx-chip lime">@include('partials.icon', ['name' => 'pnl'])</span> profit<br>
    by sheet and by day
@endsection

@section('header-actions')
    @if (Auth::user()->role === 'admin')
        <a href="{{ route('pnl.settings') }}" class="jx-btn jx-btn-round" style="width:auto;padding:0 20px;">P&amp;L Settings</a>
    @endif
@endsection

@section('content')

@if (Auth::user()->role !== 'admin')

    <div class="jx-card text-center py-12">
        <p class="text-lg font-medium">P&amp;L is admin-only for now</p>
        <p class="text-sm text-[var(--muted)] mt-1">Ask an admin if you need access to this.</p>
    </div>

@elseif ($stores->isEmpty())

    <div class="jx-card text-center py-12">
        <p class="text-lg font-medium">No sheets yet</p>
        <p class="text-sm text-[var(--muted)] mt-1">Add a sheet in Ad Spend first, then set its rates in P&amp;L Settings.</p>
        <a href="{{ route('ad-spend.sheets') }}" class="jx-btn jx-btn-dark mt-5">Go to Manage Sheets</a>
    </div>

@else

    {{-- Filters --}}
    <form method="GET" action="{{ route('pnl.index') }}" class="flex flex-wrap items-center gap-2 mb-5">
        <select name="store" class="jx-input" onchange="this.form.submit()">
            <option value="all" @selected($selected === 'all')>All Stores</option>
            @foreach ($stores as $s)
                <option value="{{ $s->id }}" @selected((string) $selected === (string) $s->id)>
                    {{ $s->label }}{{ $s->is_active ? '' : ' (disabled)' }}
                </option>
            @endforeach
        </select>
        <input type="month" name="month" value="{{ $month }}" class="jx-input" onchange="this.form.submit()">
    </form>

    @if (!$store)

        {{-- ===================== ALL STORES VIEW ===================== --}}

        <div class="grid gap-4 md:grid-cols-4 mb-5">
            <div class="jx-card">
                <p class="text-sm text-[var(--muted)]">Total Orders</p>
                <p class="text-3xl font-medium tracking-tight mt-3">{{ number_format($company['orders']) }}</p>
            </div>
            <div class="jx-card">
                <p class="text-sm text-[var(--muted)]">Avg. CPA</p>
                <p class="text-3xl font-medium tracking-tight mt-3">{{ $company['cpa'] !== null ? '₹' . number_format($company['cpa'], 2) : '—' }}</p>
            </div>
            <div class="jx-card lime">
                <p class="text-sm">Ad Spend (w/o GST)</p>
                <p class="text-3xl font-medium tracking-tight mt-3">₹{{ number_format($company['ad_cost'], 2) }}</p>
            </div>
            <div class="jx-card dark">
                <span class="text-sm text-[#B9BEB6]">Net P&amp;L — all stores</span>
                <p class="text-3xl font-medium tracking-tight mt-3 {{ $company['net_pnl'] < 0 ? 'text-red-400' : '' }}">
                    ₹{{ number_format($company['net_pnl'], 2) }}
                </p>
            </div>
        </div>

        @if ($company['missing_rate_days'] > 0)
            <p class="text-xs text-[var(--muted)] mb-3">{{ $company['missing_rate_days'] }} day(s) across sheets have no rate set and are excluded from these totals.</p>
        @endif

        <div class="bg-white border border-[var(--line)] rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="jx-table">
                    <thead>
                        <tr>
                            <th>Store</th>
                            <th class="r">Orders</th>
                            <th class="r">Ad Spend (w/o GST)</th>
                            <th class="r">Net P&amp;L</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($storeSummaries as $row)
                            <tr>
                                <td class="font-medium">
                                    {{ $row['store']->label }}
                                    @if (!$row['store']->is_active)
                                        <span class="jx-badge text-[var(--muted)] ml-2">Disabled</span>
                                    @endif
                                </td>
                                <td class="r">{{ number_format($row['orders']) }}</td>
                                <td class="r">₹{{ number_format($row['ad_cost'], 2) }}</td>
                                <td class="r font-medium {{ $row['net_pnl'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    ₹{{ number_format($row['net_pnl'], 2) }}
                                </td>
                                <td class="r">
                                    <a href="{{ route('pnl.index', ['store' => $row['store']->id, 'month' => $month]) }}" class="text-sm font-medium">View details</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-[var(--muted)]" style="padding:32px;">No sheets yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else

        {{-- ===================== SINGLE STORE VIEW ===================== --}}

        <h2 class="text-xl font-medium tracking-tight mb-3">{{ $store->label }}</h2>

        <div class="grid gap-4 md:grid-cols-4 mb-5">
            <div class="jx-card">
                <p class="text-sm text-[var(--muted)]">Total Orders</p>
                <p class="text-3xl font-medium tracking-tight mt-3">{{ number_format($selectedTotals['orders']) }}</p>
            </div>
            <div class="jx-card">
                <p class="text-sm text-[var(--muted)]">Avg. CPA</p>
                <p class="text-3xl font-medium tracking-tight mt-3">{{ $selectedTotals['cpa'] !== null ? '₹' . number_format($selectedTotals['cpa'], 2) : '—' }}</p>
            </div>
            <div class="jx-card lime">
                <p class="text-sm">Ad Spend (w/o GST)</p>
                <p class="text-3xl font-medium tracking-tight mt-3">₹{{ number_format($selectedTotals['ad_cost'], 2) }}</p>
            </div>
            <div class="jx-card dark">
                <span class="text-sm text-[#B9BEB6]">Break Even CPA</span>
                <div class="flex gap-2 mt-3">
                    <div class="flex-1 rounded-xl px-3 py-2" style="background:#2A2D2B;">
                        <p class="text-[11px] text-[var(--lime)]">With GST</p>
                        <p class="text-sm font-semibold">{{ $selectedTotals['break_even_cpa_with_gst'] !== null ? '₹' . number_format($selectedTotals['break_even_cpa_with_gst'], 2) : '—' }}</p>
                    </div>
                    <div class="flex-1 rounded-xl px-3 py-2" style="background:#2A2D2B;">
                        <p class="text-[11px] text-[#B9BEB6]">W/o GST</p>
                        <p class="text-sm font-semibold">{{ $selectedTotals['break_even_cpa'] !== null ? '₹' . number_format($selectedTotals['break_even_cpa'], 2) : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="mb-3 text-sm">
            Net P&amp;L:
            <span class="text-xl font-bold {{ $selectedTotals['net_pnl'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                ₹{{ number_format($selectedTotals['net_pnl'], 2) }}
            </span>
            @if (!$store->pnlRates()->exists())
                <a href="{{ route('pnl.settings', ['store' => $store->id]) }}" class="jx-btn jx-btn-dark ml-3" style="height:36px;padding:0 16px;">Set up rates</a>
            @endif
        </p>

        <div class="bg-white border border-[var(--line)] rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="jx-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th class="r">Orders</th>
                            <th class="r">Ad Spend (w/GST)</th>
                            <th class="r">CPA (w/GST)</th>
                            <th class="r">Margin</th>
                            <th class="r">GST %</th>
                            <th class="r">RTO Charge</th>
                            <th class="r">Net P&amp;L</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($days as $day)
                            <tr class="{{ $day['date']->isToday() ? 'today' : '' }}">
                                <td class="whitespace-nowrap">{{ $day['date']->format('d M Y') }}</td>
                                <td>{{ $store->product_name }}</td>

                                @if ($day['result'] === null)
                                    <td colspan="7" class="text-[var(--muted)]">No rate set for this date</td>
                                @else
                                    @php $r = $day['result']; @endphp
                                    <td class="r">{{ $r['orders'] }}</td>
                                    <td class="r">₹{{ number_format($r['ad_cost_with_gst'], 2) }}</td>
                                    <td class="r">{{ $r['cpa_with_gst'] !== null ? '₹' . number_format($r['cpa_with_gst'], 2) : '—' }}</td>
                                    <td class="r">₹{{ number_format($r['rate_used']->margin, 2) }}</td>
                                    <td class="r">{{ rtrim(rtrim(number_format($r['rate_used']->gst_percent, 2), '0'), '.') }}%</td>
                                    <td class="r">₹{{ number_format($r['rate_used']->rto_charge, 2) }}</td>
                                    <td class="r font-medium {{ $r['net_pnl'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        ₹{{ number_format($r['net_pnl'], 2) }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-[var(--muted)]" style="padding:32px;">No ad spend logged for this month yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endif

@endif
@endsection