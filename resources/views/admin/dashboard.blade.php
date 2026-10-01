{{-- resources\views\admin\dashboard.blade.php --}}

@extends('layouts.app')

@section('title', 'Dashboard — JENHIKX')

@section('page-title')
    Tracking <span class="jx-chip">@include('partials.icon', ['name' => 'adspend'])</span> your stores<br>
    and <span class="jx-chip lime">@include('partials.icon', ['name' => 'pnl'])</span> profits
@endsection

@section('header-actions')
    @if (Route::has('ad-spend'))
        <a href="{{ route('ad-spend') }}" class="jx-btn jx-btn-dark">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
            <span class="hidden sm:inline">Add ad spend</span>
        </a>
    @endif
@endsection

@section('content')
@php
    // =====================================================================
    //  Helpers
    // =====================================================================
    $m   = fn ($n, $d = 2) => ($n < 0 ? '−' : '') . '₹' . number_format(abs($n), $d);
    $cp  = function ($n) {           // compact: 7588 -> 7.6K, 250000 -> 2.5L
        $a = abs($n); $s = $n < 0 ? '−' : '';
        if ($a >= 1e7) return $s . rtrim(rtrim(number_format($a / 1e7, 1), '0'), '.') . 'Cr';
        if ($a >= 1e5) return $s . rtrim(rtrim(number_format($a / 1e5, 1), '0'), '.') . 'L';
        if ($a >= 1e3) return $s . rtrim(rtrim(number_format($a / 1e3, 1), '0'), '.') . 'K';
        return $s . number_format($a, 0);
    };
    $svg = fn ($path, $cls = 'jx-ico') => '<svg xmlns="http://www.w3.org/2000/svg" class="' . $cls . '" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="' . $path . '"/></svg>';
    $P = [
        'bag'    => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z',
        'bars'   => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        'up'     => 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941',
        'down'   => 'M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181',
        'out'    => 'M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25',
        'chev'   => 'M19.5 8.25l-7.5 7.5-7.5-7.5',
    ];
    $chev = $svg($P['chev'], 'jx-chev');

    $periods = [
        'this_month' => 'This month', 'last_month' => 'Last month',
        'this_year' => 'This year', 'all_time' => 'All time',
    ];
    $founders = \App\Http\Controllers\FounderWithdrawalController::FOUNDERS;

    $income      = (float) $income;
    $expenses    = (float) $expenses;
    $withdrawals = (float) $withdrawals;
    $purse       = (float) $purse;

    // =====================================================================
    //  Chart series — seedha $trend se (controller se kuch extra nahi chahiye)
    //  60+ din ho to mahine-wise, warna din-wise
    // =====================================================================
    $trendC  = collect($trend);
    $monthly = $trendC->count() > 62;
    $series  = ['labels' => [], 'net' => [], 'cum' => [], 'orders' => [], 'ad' => []];
    $run = 0;
    foreach ($trendC->groupBy(fn ($d) => $d['date']->format($monthly ? 'Y-m' : 'Y-m-d')) as $g) {
        $net  = (float) $g->sum('net_pnl');
        $run += $net;
        $series['labels'][] = $g->first()['date']->format($monthly ? "M 'y" : 'd M');
        $series['net'][]    = round($net, 2);
        $series['cum'][]    = round($run, 2);
        $series['orders'][] = (int) $g->sum('orders');
        $series['ad'][]     = round((float) $g->sum(fn ($d) => $d['ad_cost_with_gst'] ?? 0), 2);
    }

    // =====================================================================
    //  "vs prev" badge
    // =====================================================================
    $lastDate = $trendC->last()['date'] ?? null;
    $prev = null;
    try {
        $svc = app(\App\Services\DashboardService::class);
        if (method_exists($svc, 'previousNetPnl')) {
            $prev = $svc->previousNetPnl($period, $lastDate);
        }
    } catch (\Throwable $e) {
        $prev = null;
    }
    $delta = ($prev !== null && abs($prev) > 0.009) ? ($totals['net_pnl'] - $prev) / abs($prev) * 100 : null;
    $prevLabel = ['this_month' => 'last month', 'last_month' => 'the month before', 'this_year' => 'last year'][$period] ?? 'prev';

    // =====================================================================
    //  Purse allocation bar
    // =====================================================================
    $pool = $totals['net_pnl'] + $income;
    if ($pool > 0) {
        $expPct = min(100, $expenses / $pool * 100);
        $wdPct  = min(100 - $expPct, $withdrawals / $pool * 100);
        $remPct = max(0, 100 - $expPct - $wdPct);
    } else {
        $expPct = $wdPct = $remPct = 0;
    }

    // =====================================================================
    //  Insights
    // =====================================================================
    $best       = $trendC->sortByDesc('net_pnl')->first();
    $worst      = $trendC->sortBy('net_pnl')->first();
    $daysN      = max(1, $trendC->count());
    $avgDay     = $totals['net_pnl'] / $daysN;
    $perOrder   = $totals['orders'] > 0 ? $totals['net_pnl'] / $totals['orders'] : 0;
    $cpo        = $totals['orders'] > 0 ? $totals['ad_cost_with_gst'] / $totals['orders'] : 0;
    $roas       = $totals['ad_cost_with_gst'] > 0 ? $totals['net_pnl'] / $totals['ad_cost_with_gst'] * 100 : 0;
    $profitDays = $trendC->filter(fn ($d) => $d['net_pnl'] > 0)->count();
    $projection = $period === 'this_month' && $trendC->count() > 0 ? $avgDay * now()->daysInMonth : null;
    $tMax       = max(1, $trendC->max(fn ($d) => abs($d['net_pnl'])) ?? 1);

    // =====================================================================
    //  KPI cards
    // =====================================================================
    $kpis = [
        ['k' => 'orders',   'label' => 'Total Orders',         'val' => $totals['orders'],           'dec' => 0, 'money' => false, 'ico' => 'bag',  'spark' => 'orders', 'cls' => ''],
        ['k' => 'ad',       'label' => 'Ad Spend (incl. GST)', 'val' => $totals['ad_cost_with_gst'], 'dec' => 2, 'money' => true,  'ico' => 'bars', 'spark' => 'ad',     'cls' => ''],
        ['k' => 'income',   'label' => 'Additional Income',    'val' => $income,                     'dec' => 2, 'money' => true,  'ico' => 'up',   'spark' => null,     'cls' => 'lime'],
        ['k' => 'expenses', 'label' => 'Total Expenses',       'val' => $expenses,                   'dec' => 2, 'money' => true,  'ico' => 'down', 'spark' => null,     'cls' => ''],
    ];

    // =====================================================================
    //  Profit calendar (month view) / month tiles (year + all time)
    // =====================================================================
    $isMonthView = in_array($period, ['this_month', 'last_month'], true);
    $byKey = $trendC->keyBy(fn ($d) => $d['date']->format('Y-m-d'));
    $missing = [];
    if ($isMonthView) {
        $calStart    = $period === 'last_month' ? now()->subMonthNoOverflow()->startOfMonth() : now()->startOfMonth();
        $daysInMonth = $calStart->daysInMonth;
        $lead        = $calStart->dayOfWeekIso - 1;           // Monday first
        $calMax      = $tMax;
        $limit       = now()->subDay()->startOfDay();         // kal tak ki entry honi chahiye
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dd = $calStart->copy()->day($i);
            if ($dd->gt($limit)) break;
            if (!isset($byKey[$dd->format('Y-m-d')])) $missing[] = $dd->format('d M');
        }
        $calProfit = $profitDays;
        $calLoss   = $trendC->filter(fn ($d) => $d['net_pnl'] < 0)->count();
    } else {
        $byMonth = $trendC->groupBy(fn ($d) => $d['date']->format('Y-m'))->map(fn ($g) => [
            'label'  => $g->first()['date']->format("M 'y"),
            'net'    => (float) $g->sum('net_pnl'),
            'orders' => (int) $g->sum('orders'),
        ]);
        $mMax = max(1, $byMonth->max(fn ($x) => abs($x['net'])) ?? 1);
    }
    $cellStyle = function ($v, $max) {
        $a = round(0.14 + 0.62 * min(1, abs($v) / $max), 2);
        return $v < 0 ? "background:rgba(220,38,38,$a)" : "background:rgba(163,194,26,$a)";
    };

    // =====================================================================
    //  Where P&L went
    // =====================================================================
    $wSegments = [
        ['key' => 'netpnl',      'label' => 'Net P&L',             'value' => $totals['net_pnl'], 'color' => $totals['net_pnl'] < 0 ? '#DC2626' : '#0F1210', 'rows' => $byStore],
        ['key' => 'income',      'label' => 'Additional Income',   'value' => $income,            'color' => '#A3C21A', 'rows' => $incomeBySource],
        ['key' => 'expenses',    'label' => 'Expenses',            'value' => -$expenses,         'color' => '#F59E0B', 'rows' => $expensesByCategory],
        ['key' => 'withdrawals', 'label' => 'Founder Withdrawals', 'value' => -$withdrawals,      'color' => '#8B5CF6', 'rows' => $withdrawalsBreakdown],
    ];
    $wMax = max(1, collect($wSegments)->max(fn ($s) => abs($s['value'])));

    $maxOrdersStore   = max(1, collect($byStore)->max('orders') ?? 1);
    $maxOrdersProduct = max(1, collect($byProduct)->max('orders') ?? 1);

    // =====================================================================
    //  Data for JS
    // =====================================================================
    $jxData = [
        'gran'   => $monthly ? 'monthly' : 'daily',
        'series' => $series,
        'stores' => [
            'labels' => collect($byStore)->pluck('label')->values(),
            'orders' => collect($byStore)->pluck('orders')->values(),
        ],
    ];
@endphp

<style>
    :root { --pos: #16A34A; --neg: #DC2626; --lime-deep: #A3C21A; --shadow-soft: 0 1px 0 rgba(15,18,16,.03), 0 24px 48px -32px rgba(15,18,16,.22); }

    /* ---------- Grids (plain CSS — Tailwind rebuild ki zaroorat nahi) ---------- */
    .jx-row-split { display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr); margin-bottom: 16px; }
    .jx-kpis { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 16px; }
    .jx-kpis .jx-wide { grid-column: span 2; }
    .jx-stats { display: grid; gap: 10px; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 16px; }
    @media (min-width: 768px) {
        .jx-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .jx-kpis .jx-wide { grid-column: span 2; }
        .jx-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    }
    @media (min-width: 1280px) {
        .jx-row-split { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .jx-c2 { grid-column: span 2; }
        .jx-c3 { grid-column: span 3; }
        .jx-kpis { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .jx-kpis .jx-wide { grid-column: span 1; }
    }
    .jx-ico { width: 20px; height: 20px; flex-shrink: 0; }
    .jx-pos { color: var(--pos); }
    .jx-neg { color: var(--neg); }

    /* ---------- One page-load sequence (sirf ek baar) ---------- */
    .jx-rise { opacity: 0; transform: translateY(12px); animation: jxRise .65s cubic-bezier(.2, .7, .2, 1) forwards; animation-delay: calc(var(--i, 0) * 70ms); }
    @keyframes jxRise { to { opacity: 1; transform: none; } }
    @keyframes jxGrow { from { transform: scaleX(0); } }
    @keyframes jxWipe { to { clip-path: inset(0 0 0 0); } }
    @keyframes jxDrift { to { transform: translate(-40px, 34px) scale(1.12); } }
    @media (prefers-reduced-motion: reduce) {
        .jx-rise { opacity: 1; transform: none; }
        .jx-spark svg { clip-path: none !important; }
    }

    /* ---------- Panels ---------- */
    .jx-panel { background: #fff; border: 1px solid var(--line); border-radius: 24px; padding: 18px; box-shadow: var(--shadow-soft); min-width: 0; }
    .jx-panel.flush { padding: 0; overflow: hidden; }
    .jx-panel-head { padding: 18px 18px 0; }
    .jx-h { font-size: 19px; font-weight: 600; letter-spacing: -.02em; line-height: 1.2; }
    .jx-sub { font-size: 12px; color: var(--muted); margin-top: 4px; margin-bottom: 14px; }
    .jx-ph { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    @media (min-width: 768px) { .jx-panel { padding: 22px; } .jx-panel-head { padding: 22px 22px 0; } }

    /* ---------- HERO: Company Purse + cumulative chart ---------- */
    .jx-hero { position: relative; overflow: hidden; isolation: isolate; border-radius: 32px; color: #fff; background: #0F1210; border: 1px solid #1B2016; margin-bottom: 16px; display: grid; grid-template-columns: minmax(0, 1fr); }
    .jx-hero::before { content: ''; position: absolute; inset: 0; z-index: -1;
        background-image: linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
        background-size: 30px 30px; -webkit-mask-image: radial-gradient(100% 90% at 70% 10%, #000, transparent); mask-image: radial-gradient(100% 90% at 70% 10%, #000, transparent); }
    .jx-hero::after { content: ''; position: absolute; z-index: -1; width: 420px; height: 320px; right: -110px; top: -150px; border-radius: 50%; background: var(--lime); filter: blur(110px); opacity: .3; animation: jxDrift 10s ease-in-out infinite alternate; }
    .jx-hero-l { padding: 22px; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
    .jx-hero-r { padding: 4px 22px 22px; display: flex; flex-direction: column; min-width: 0; }
    @media (min-width: 768px) { .jx-hero-l { padding: 30px; } .jx-hero-r { padding: 4px 30px 28px; } }
    @media (min-width: 1024px) {
        .jx-hero { grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); }
        .jx-hero-r { padding: 26px 30px 26px 0; }
    }
    .jx-purse-label { display: inline-flex; align-items: center; gap: 8px; font-size: 14px; color: #C4C9BD; }
    .jx-state { font-size: 12px; font-weight: 600; padding: 4px 11px; border-radius: 999px; margin-left: 10px; }
    .jx-state.pos { background: rgba(221,244,91,.16); color: var(--lime); }
    .jx-state.neg { background: rgba(248,113,113,.16); color: #FCA5A5; }
    .jx-purse-num { font-size: clamp(38px, 5.4vw, 66px); font-weight: 500; letter-spacing: -.035em; line-height: 1; margin-top: 16px; word-break: break-word; }
    .jx-purse-formula { font-size: 12px; color: #9BA197; margin-top: 12px; line-height: 1.5; }
    .jx-mini-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-top: 22px; }
    @media (min-width: 1440px) { .jx-mini-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    .jx-mini { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.09); border-radius: 16px; padding: 11px 13px; min-width: 0; }
    .jx-mini p:first-child { font-size: 12px; color: #9BA197; }
    .jx-mini p:last-child { font-size: 14px; font-weight: 600; margin-top: 4px; overflow-wrap: anywhere; }
    .jx-alloc { display: flex; height: 9px; border-radius: 999px; overflow: hidden; background: rgba(255,255,255,.1); margin-top: 20px; }
    .jx-alloc i { display: block; height: 100%; transform-origin: left; animation: jxGrow 1s .5s cubic-bezier(.2, .7, .2, 1) both; }
    .jx-alloc-legend { display: flex; flex-wrap: wrap; gap: 6px 16px; margin-top: 10px; font-size: 12px; color: #B9BEB6; }
    .jx-alloc-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .jx-alloc-legend i { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }

    .jx-hero-rh { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .jx-hero-cap { font-size: 13px; color: #B9BEB6; }
    .jx-delta { font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 999px; white-space: nowrap; }
    .jx-delta.pos { background: #E3F8DC; color: #166534; }
    .jx-delta.neg { background: #FDE4E4; color: #991B1B; }
    .jx-toggle { display: inline-flex; padding: 4px; border-radius: 999px; background: var(--surface); border: 1px solid var(--line); max-width: 100%; overflow-x: auto; scrollbar-width: none; }
    .jx-toggle button { height: 30px; padding: 0 14px; border-radius: 999px; font-size: 12px; font-weight: 500; color: var(--muted); transition: background .2s, color .2s; white-space: nowrap; }
    .jx-toggle button.active { background: var(--ink); color: #fff; }
    .jx-toggle.dark { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.1); margin-top: 12px; align-self: flex-start; }
    .jx-toggle.dark button { color: #9BA197; }
    .jx-toggle.dark button.active { background: var(--lime); color: var(--ink); }
    .jx-hero-chart { position: relative; height: 270px; margin-top: 10px; }
    @media (min-width: 768px) { .jx-hero-chart { height: 300px; } }
    @media (min-width: 1024px) { .jx-hero-chart { flex: 1; height: auto; min-height: 290px; } }
    .jx-empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; text-align: center; padding: 0 20px; color: var(--muted); font-size: 13px; }
    .jx-hero .jx-empty { color: #9BA197; }
    .jx-empty[hidden] { display: none; }
    .jx-donut-box { position: relative; height: 210px; margin: 6px 0 14px; }

    /* ---------- Sparklines ---------- */
    .jx-spark svg { display: block; clip-path: inset(0 100% 0 0); animation: jxWipe 1.2s .35s cubic-bezier(.2, .7, .2, 1) forwards; }

    /* ---------- KPI cards ---------- */
    .jx-kpi { position: relative; overflow: hidden; padding: 16px; border-radius: 22px; display: flex; flex-direction: column; min-width: 0; }
    @media (min-width: 768px) { .jx-kpi { padding: 20px; } }
    .jx-kpi-top { display: flex; align-items: center; gap: 10px; }
    .jx-kpi-top p { font-size: 13px; color: var(--muted); line-height: 1.25; }
    .jx-card.lime .jx-kpi-top p { color: rgba(15,18,16,.7); }
    .jx-kpi-num { font-size: clamp(20px, 2.1vw, 28px); font-weight: 500; letter-spacing: -.02em; margin-top: 16px; line-height: 1.1; overflow-wrap: anywhere; }
    .jx-kpi .jx-spark { position: absolute; left: 0; right: 0; bottom: 0; height: 40px; color: var(--ink); opacity: .85; }
    .jx-kpi.has-spark { padding-bottom: 54px; }
    .jx-founders { margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--line); display: grid; gap: 6px; }
    .jx-founders div { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 12px; }
    .jx-founders span:first-child { color: var(--muted); }
    .jx-founders span:last-child { font-weight: 600; }

    /* ---------- Insight stats ---------- */
    .jx-stat { background: rgba(255,255,255,.9); border: 1px solid var(--line); border-radius: 18px; padding: 13px 15px; min-width: 0; }
    .jx-stat-l { font-size: 12px; color: var(--muted); }
    .jx-stat-v { font-size: 19px; font-weight: 600; letter-spacing: -.02em; margin-top: 6px; overflow-wrap: anywhere; }
    .jx-stat-s { font-size: 11px; color: var(--muted); margin-top: 3px; }

    /* ---------- Profit calendar ---------- */
    .jx-cal-head, .jx-cal { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 6px; }
    .jx-cal-head span { font-size: 11px; color: var(--muted); text-align: center; padding-bottom: 6px; }
    .jx-cal-cell { min-height: 54px; border-radius: 12px; padding: 6px 7px; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid transparent; min-width: 0; }
    .jx-cal-cell.blank { visibility: hidden; }
    .jx-cal-cell.empty { border: 1px dashed var(--line); color: #B5BAB1; }
    .jx-cal-cell.today { outline: 2px solid var(--ink); outline-offset: 1px; }
    .jx-cal-d { font-size: 11px; color: rgba(15,18,16,.55); }
    .jx-cal-v { font-size: 11px; font-weight: 600; text-align: right; letter-spacing: -.01em; white-space: nowrap; }
    @media (min-width: 768px) {
        .jx-cal-head, .jx-cal { gap: 8px; }
        .jx-cal-cell { min-height: 76px; padding: 9px 11px; }
        .jx-cal-v { font-size: 14px; }
        .jx-cal-d { font-size: 12px; }
    }
    .jx-cal-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px 20px; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--line); font-size: 12px; color: var(--muted); }
    .jx-scale { display: inline-flex; align-items: center; gap: 8px; }
    .jx-scale i { display: block; width: 90px; height: 8px; border-radius: 999px; background: linear-gradient(90deg, rgba(220,38,38,.75), rgba(220,38,38,.14) 42%, rgba(163,194,26,.14) 58%, rgba(163,194,26,.76)); }
    .jx-missing { display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .jx-missing b { color: var(--ink); font-weight: 600; }
    .jx-months { display: grid; gap: 10px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (min-width: 768px) { .jx-months { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (min-width: 1280px) { .jx-months { grid-template-columns: repeat(6, minmax(0, 1fr)); } }
    .jx-month { border-radius: 16px; padding: 14px; min-width: 0; }
    .jx-month p:first-child { font-size: 12px; color: rgba(15,18,16,.6); }
    .jx-month p:nth-child(2) { font-size: 17px; font-weight: 600; letter-spacing: -.02em; margin-top: 6px; }
    .jx-month p:last-child { font-size: 11px; color: rgba(15,18,16,.55); margin-top: 2px; }

    /* ---------- Where P&L went ---------- */
    .jx-where { border: 1px solid var(--line); border-radius: 16px; padding: 13px 15px; cursor: pointer; transition: background .15s, border-color .15s; }
    .jx-where:hover { background: var(--surface); }
    .jx-where.open { border-color: var(--ink); }
    .jx-where-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 9px; font-size: 14px; }
    .jx-where-row b { font-weight: 600; }
    .jx-track { height: 8px; border-radius: 999px; background: var(--surface); overflow: hidden; }
    .jx-fill { height: 100%; border-radius: 999px; transform-origin: left; animation: jxGrow .9s .5s cubic-bezier(.2, .7, .2, 1) both; }
    .jx-detail { padding: 10px 6px 2px; display: grid; gap: 6px; }
    .jx-detail[hidden] { display: none; }
    .jx-detail div { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 13px; background: var(--surface); border-radius: 12px; padding: 9px 14px; }
    .jx-detail p { font-size: 12px; color: var(--muted); padding: 6px 2px; }
    .jx-remain { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--line); }

    /* ---------- Store share legend ---------- */
    .jx-legend { list-style: none; display: grid; gap: 8px; }
    .jx-legend li { display: flex; align-items: center; gap: 10px; font-size: 13px; }
    .jx-legend i { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .jx-legend span { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .jx-legend b { font-weight: 600; }
    .jx-legend em { font-style: normal; color: var(--muted); width: 44px; text-align: right; }

    /* ---------- Tables ---------- */
    .jx-scroll { overflow: auto; -webkit-overflow-scrolling: touch; }
    .jx-scroll.tall { max-height: 560px; }
    .jx-scroll .jx-table { min-width: 520px; }
    .jx-scroll thead th { position: sticky; top: 0; z-index: 2; }
    .jx-scroll td:first-child, .jx-scroll th:first-child { position: sticky; left: 0; z-index: 1; background: #fff; }
    .jx-scroll th:first-child { background: var(--surface); z-index: 3; }
    .jx-scroll tr.today td:first-child { background: #FAFDE6; }
    .jx-table .c { text-align: center; }
    .jx-acc { cursor: pointer; }
    .jx-acc.open td, .jx-acc.open td:first-child { background: var(--surface) !important; font-weight: 600; }
    .jx-chev { display: inline-block; width: 14px; height: 14px; margin-left: 6px; vertical-align: middle; transition: transform .2s; }
    .open .jx-chev { transform: rotate(180deg); }
    .jx-sub-tr[hidden] { display: none; }
    .jx-scroll .jx-sub-tr td, .jx-scroll .jx-sub-tr td:first-child { background: #F9FAF6; border-top: 1px solid var(--line); font-size: 13px; padding-top: 11px; padding-bottom: 11px; }
    .jx-sub-tr td:first-child { padding-left: 34px; }
    .jx-sub-name { display: inline-flex; align-items: center; gap: 10px; font-weight: 500; }
    .jx-sub-name::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--lime-deep); flex-shrink: 0; }
    .jx-rank { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 9px; background: var(--surface); font-size: 12px; font-weight: 600; margin-right: 10px; flex-shrink: 0; }
    .jx-rank.first { background: var(--lime); }
    .jx-cellwrap { display: flex; align-items: center; }
    .jx-mini-bar { height: 4px; width: 84px; border-radius: 999px; background: var(--surface); margin-top: 6px; margin-left: auto; overflow: hidden; }
    .jx-mini-bar i { display: block; height: 100%; border-radius: 999px; background: var(--pos); }
    .jx-mini-bar.neg i { background: var(--neg); }
    @media (max-width: 640px) { .jx-sub-tr td:first-child { padding-left: 20px; } .jx-table td, .jx-table th { padding-left: 12px; padding-right: 12px; } }
    .jx-nodata { text-align: center; color: var(--muted); padding: 34px 16px !important; }
</style>

    <script type="application/json" id="jx-data">{!! json_encode($jxData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) !!}</script>

    {{-- Period filter --}}
    <div class="jx-tabs mb-5 jx-rise" style="--i:0">
        @foreach ($periods as $key => $label)
            <a href="{{ route('admin.dashboard', ['period' => $key]) }}" class="jx-tab {{ $period === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    {{-- =====================================================================
         HERO — Company Purse + Cumulative Net P&L
         ===================================================================== --}}
    <section class="jx-hero jx-rise" style="--i:1">
        <div class="jx-hero-l">
            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px 0;">
                <span class="jx-purse-label">Company Purse Remaining</span>
                <span class="jx-state {{ $purse < 0 ? 'neg' : 'pos' }}">{{ $purse < 0 ? 'In deficit' : 'In profit' }}</span>
            </div>

            <p class="jx-purse-num" style="{{ $purse < 0 ? 'color:#FCA5A5' : 'color:var(--lime)' }}">
                <span data-count="{{ round($purse, 2) }}" data-dec="2" data-prefix="₹">{{ $m($purse) }}</span>
            </p>
            <p class="jx-purse-formula">Net P&amp;L + Additional Income − Expenses − Founder Withdrawals</p>

            <div class="jx-mini-grid">
                <div class="jx-mini"><p>Net P&amp;L</p><p style="{{ $totals['net_pnl'] < 0 ? 'color:#FCA5A5' : '' }}">{{ $m($totals['net_pnl']) }}</p></div>
                <div class="jx-mini"><p>+ Income</p><p>{{ $m($income) }}</p></div>
                <div class="jx-mini"><p>− Expenses</p><p>{{ $m($expenses) }}</p></div>
                <div class="jx-mini"><p>− Withdrawals</p><p>{{ $m($withdrawals) }}</p></div>
            </div>

            <div class="jx-alloc" role="img" aria-label="Split of income pool between remaining purse, expenses and withdrawals">
                <i style="width:{{ $remPct }}%;background:var(--lime)"></i>
                <i style="width:{{ $expPct }}%;background:#F59E0B"></i>
                <i style="width:{{ $wdPct }}%;background:#8B5CF6"></i>
            </div>
            <div class="jx-alloc-legend">
                <span><i style="background:var(--lime)"></i>Kept {{ number_format($remPct, 0) }}%</span>
                <span><i style="background:#F59E0B"></i>Expenses {{ number_format($expPct, 0) }}%</span>
                <span><i style="background:#8B5CF6"></i>Withdrawn {{ number_format($wdPct, 0) }}%</span>
            </div>
        </div>

        <div class="jx-hero-r">
            <div class="jx-hero-rh">
                <p class="jx-hero-cap" id="heroTitle">Cumulative Net P&amp;L · {{ $monthly ? 'monthly' : 'daily' }}</p>
                @if ($delta !== null)
                    <span class="jx-delta {{ $delta >= 0 ? 'pos' : 'neg' }}" title="{{ $m($totals['net_pnl'], 0) }} now vs {{ $m($prev, 0) }} in the same span of {{ $prevLabel }}">
                        {{ $delta >= 0 ? '▲' : '▼' }} {{ number_format(abs($delta), 1) }}% P&amp;L vs {{ $prevLabel }}
                    </span>
                @endif
            </div>
            <div class="jx-toggle dark" id="heroToggle" role="group" aria-label="Chart view">
                <button type="button" class="active" data-mode="cum">Cumulative</button>
                <button type="button" data-mode="day">{{ $monthly ? 'Monthly' : 'Daily' }} P&amp;L</button>
                <button type="button" data-mode="orders">Orders</button>
                <button type="button" data-mode="ad">Ad spend</button>
            </div>
            <div class="jx-hero-chart">
                <canvas id="heroChart" aria-label="Net P&L chart" role="img"></canvas>
                <div class="jx-empty" id="heroEmpty" hidden>No ad spend logged for this period yet.</div>
            </div>
        </div>
    </section>

    {{-- ===== KPI cards ===== --}}
    <div class="jx-kpis jx-rise" style="--i:2">
        @foreach ($kpis as $c)
            <div class="jx-card jx-kpi {{ $c['cls'] }} {{ $c['spark'] ? 'has-spark' : '' }}">
                <div class="jx-kpi-top">
                    <span class="jx-iconchip">{!! $svg($P[$c['ico']]) !!}</span>
                    <p>{{ $c['label'] }}</p>
                </div>
                <p class="jx-kpi-num">
                    <span data-count="{{ round($c['val'], 2) }}" data-dec="{{ $c['dec'] }}" @if($c['money']) data-prefix="₹" @endif>{{ $c['money'] ? $m($c['val']) : number_format($c['val']) }}</span>
                </p>
                @if ($c['spark'])
                    <div class="jx-spark" data-spark="{{ $c['spark'] }}" aria-hidden="true"></div>
                @endif
            </div>
        @endforeach

        <div class="jx-card jx-kpi jx-wide">
            <div class="jx-kpi-top">
                <span class="jx-iconchip">{!! $svg($P['out']) !!}</span>
                <p>Founder Withdrawals</p>
            </div>
            <p class="jx-kpi-num">
                <span data-count="{{ round($withdrawals, 2) }}" data-dec="2" data-prefix="₹">{{ $m($withdrawals) }}</span>
            </p>
            <div class="jx-founders">
                @foreach ($founders as $f)
                    <div>
                        <span>{{ $f }}</span>
                        <span>{{ $m($withdrawalsByFounder[$f] ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== Insights ===== --}}
    <div class="jx-stats jx-rise" style="--i:3">
        <div class="jx-stat">
            <p class="jx-stat-l">Best day</p>
            <p class="jx-stat-v {{ $best && $best['net_pnl'] >= 0 ? 'jx-pos' : '' }}">{{ $best ? $m($best['net_pnl'], 0) : '—' }}</p>
            <p class="jx-stat-s">{{ $best ? $best['date']->format('d M Y') : 'No data yet' }}</p>
        </div>
        <div class="jx-stat">
            <p class="jx-stat-l">Worst day</p>
            <p class="jx-stat-v {{ $worst && $worst['net_pnl'] < 0 ? 'jx-neg' : '' }}">{{ $worst ? $m($worst['net_pnl'], 0) : '—' }}</p>
            <p class="jx-stat-s">{{ $worst ? $worst['date']->format('d M Y') : 'No data yet' }}</p>
        </div>
        <div class="jx-stat">
            <p class="jx-stat-l">Average P&amp;L per day</p>
            <p class="jx-stat-v {{ $avgDay < 0 ? 'jx-neg' : '' }}">{{ $m($avgDay, 0) }}</p>
            <p class="jx-stat-s">Over {{ $trendC->count() }} {{ \Illuminate\Support\Str::plural('day', $trendC->count()) }} with entries</p>
        </div>
        <div class="jx-stat">
            <p class="jx-stat-l">Profitable days</p>
            <p class="jx-stat-v">{{ $profitDays }} <span style="font-size:13px;font-weight:500;color:var(--muted)">of {{ $trendC->count() }}</span></p>
            <p class="jx-stat-s">{{ $trendC->count() ? number_format($profitDays / $trendC->count() * 100, 0) : 0 }}% win rate</p>
        </div>
        <div class="jx-stat">
            <p class="jx-stat-l">Profit per order</p>
            <p class="jx-stat-v {{ $perOrder < 0 ? 'jx-neg' : '' }}">{{ $m($perOrder, 0) }}</p>
            <p class="jx-stat-s">Net P&amp;L ÷ orders</p>
        </div>
        <div class="jx-stat">
            <p class="jx-stat-l">Ad cost per order</p>
            <p class="jx-stat-v">{{ $m($cpo, 0) }}</p>
            <p class="jx-stat-s">Incl. GST</p>
        </div>
        <div class="jx-stat">
            <p class="jx-stat-l">Profit on ad spend</p>
            <p class="jx-stat-v {{ $roas < 0 ? 'jx-neg' : 'jx-pos' }}">{{ number_format($roas, 0) }}%</p>
            <p class="jx-stat-s">Net P&amp;L ÷ ad spend</p>
        </div>
        @if ($projection !== null)
            <div class="jx-stat">
                <p class="jx-stat-l">Month-end projection</p>
                <p class="jx-stat-v {{ $projection < 0 ? 'jx-neg' : '' }}">{{ $m($projection, 0) }}</p>
                <p class="jx-stat-s">At the current daily average</p>
            </div>
        @else
            <div class="jx-stat">
                <p class="jx-stat-l">Orders per day</p>
                <p class="jx-stat-v">{{ number_format($totals['orders'] / $daysN, 0) }}</p>
                <p class="jx-stat-s">Average across days with entries</p>
            </div>
        @endif
    </div>

    {{-- ===== Profit calendar / month tiles ===== --}}
    <div class="jx-panel jx-rise" style="margin-bottom:16px;--i:4">
        @if ($isMonthView)
            <p class="jx-h">Profit calendar — {{ $calStart->format('F Y') }}</p>
            <p class="jx-sub">Har din ka net P&amp;L. Jitna gehra rang, utna bada profit ya loss.</p>

            <div class="jx-cal-head">
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $wd)
                    <span>{{ $wd }}</span>
                @endforeach
            </div>
            <div class="jx-cal">
                @for ($i = 0; $i < $lead; $i++)
                    <div class="jx-cal-cell blank"></div>
                @endfor
                @for ($i = 1; $i <= $daysInMonth; $i++)
                    @php
                        $dd  = $calStart->copy()->day($i);
                        $row = $byKey[$dd->format('Y-m-d')] ?? null;
                    @endphp
                    @if ($row)
                        <div class="jx-cal-cell {{ $dd->isToday() ? 'today' : '' }}" style="{{ $cellStyle($row['net_pnl'], $calMax) }}" title="{{ $dd->format('d M') }} · {{ number_format($row['orders']) }} orders · {{ $m($row['net_pnl']) }}">
                            <span class="jx-cal-d">{{ $i }}</span>
                            <span class="jx-cal-v">{{ $cp($row['net_pnl']) }}</span>
                        </div>
                    @else
                        <div class="jx-cal-cell empty {{ $dd->isToday() ? 'today' : '' }}" title="{{ $dd->format('d M') }} · no entry">
                            <span class="jx-cal-d">{{ $i }}</span>
                            <span class="jx-cal-v">·</span>
                        </div>
                    @endif
                @endfor
            </div>

            <div class="jx-cal-foot">
                <span class="jx-scale">Loss <i></i> Profit</span>
                <span>{{ $calProfit }} profit {{ \Illuminate\Support\Str::plural('day', $calProfit) }} · {{ $calLoss }} loss {{ \Illuminate\Support\Str::plural('day', $calLoss) }}</span>
                <span class="jx-missing">
                    @if (count($missing))
                        No entry on: <b>{{ implode(', ', array_slice($missing, 0, 6)) }}{{ count($missing) > 6 ? ' +' . (count($missing) - 6) . ' more' : '' }}</b>
                    @else
                        Every day is logged ✓
                    @endif
                </span>
            </div>
        @else
            <p class="jx-h">Month by month</p>
            <p class="jx-sub">Har mahine ka net P&amp;L. Jitna gehra rang, utna bada profit ya loss.</p>
            <div class="jx-months">
                @forelse ($byMonth as $mo)
                    <div class="jx-month" style="{{ $cellStyle($mo['net'], $mMax) }}">
                        <p>{{ $mo['label'] }}</p>
                        <p>{{ $m($mo['net'], 0) }}</p>
                        <p>{{ number_format($mo['orders']) }} orders</p>
                    </div>
                @empty
                    <p style="font-size:13px;color:var(--muted);">No data for this period yet.</p>
                @endforelse
            </div>
        @endif
    </div>

    {{-- ===== Where P&L went + store share ===== --}}
    <div class="jx-row-split">
        <div class="jx-panel jx-c3 jx-rise" style="--i:5">
            <p class="jx-h">Where P&amp;L Went</p>
            <p class="jx-sub">Total P&amp;L split across income, expenses, withdrawals &amp; remaining purse. Tap a row for details.</p>

            <div style="display:grid;gap:8px;">
                @foreach ($wSegments as $seg)
                    <div>
                        <div class="jx-where" data-acc="where-{{ $seg['key'] }}" onclick="jxAcc('where-{{ $seg['key'] }}')">
                            <div class="jx-where-row">
                                <span style="display:inline-flex;align-items:center;font-weight:500;">{{ $seg['label'] }} {!! $chev !!}</span>
                                <b class="{{ $seg['value'] < 0 && $seg['key'] === 'netpnl' ? 'jx-neg' : '' }}">
                                    {{ $seg['value'] < 0 ? '− ' : ($seg['key'] === 'income' ? '+ ' : '') }}₹{{ number_format(abs($seg['value']), 2) }}
                                </b>
                            </div>
                            <div class="jx-track"><div class="jx-fill" style="width:{{ max(3, (abs($seg['value']) / $wMax) * 100) }}%;background:{{ $seg['color'] }};"></div></div>
                        </div>
                        <div class="jx-detail" data-child="where-{{ $seg['key'] }}" hidden>
                            @forelse ($seg['rows'] as $row)
                                @php
                                    $rLabel = is_array($row) ? $row['label'] : $row->label;
                                    $rTotal = is_array($row) ? ($row['net_pnl'] ?? 0) : $row->total;
                                @endphp
                                <div>
                                    <span>{{ $rLabel }}</span>
                                    <span style="font-weight:600;" class="{{ $rTotal < 0 ? 'jx-neg' : '' }}">{{ $m($rTotal) }}</span>
                                </div>
                            @empty
                                <p>No entries for this period.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="jx-remain">
                <span style="font-size:14px;font-weight:600;">Remaining Purse</span>
                <span style="font-size:20px;font-weight:700;" class="{{ $purse < 0 ? 'jx-neg' : 'jx-pos' }}">{{ $m($purse) }}</span>
            </div>
        </div>

        <div class="jx-panel jx-c2 jx-rise" style="--i:6">
            <p class="jx-h">Store share</p>
            <p class="jx-sub">Orders split by store for this period.</p>
            <div class="jx-donut-box" id="donutBox">
                <canvas id="storeChart" aria-label="Orders by store" role="img"></canvas>
                <div class="jx-empty" id="storeEmpty" hidden>No orders yet.</div>
            </div>
            <ul class="jx-legend" id="storeLegend"></ul>
        </div>
    </div>

    {{-- ===== P&L Trend ===== --}}
    <div class="jx-panel flush jx-rise" style="margin-bottom:16px;--i:7">
        <div class="jx-panel-head">
            <p class="jx-h">P&amp;L Trend</p>
            <p class="jx-sub">Daily breakdown — tap a row to see the store-wise split.</p>
        </div>
        <div class="jx-scroll tall">
            <table class="jx-table">
                <colgroup><col style="width:46%"><col style="width:24%"><col style="width:30%"></colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="c">Orders</th>
                        <th class="r">Net P&amp;L</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trend as $day)
                        @php $dk = $day['date']->format('Y-m-d'); @endphp
                        <tr class="jx-acc {{ $day['date']->isToday() ? 'today' : '' }}" data-acc="trend-{{ $dk }}" onclick="jxAcc('trend-{{ $dk }}')">
                            <td style="font-weight:500;white-space:nowrap;">{{ $day['date']->format('d M Y') }} {!! $chev !!}</td>
                            <td class="c">{{ number_format($day['orders']) }}</td>
                            <td class="r">
                                <span style="font-weight:600;" class="{{ $day['net_pnl'] < 0 ? 'jx-neg' : 'jx-pos' }}">{{ $m($day['net_pnl']) }}</span>
                                <div class="jx-mini-bar {{ $day['net_pnl'] < 0 ? 'neg' : '' }}"><i style="width:{{ max(4, abs($day['net_pnl']) / $tMax * 100) }}%"></i></div>
                            </td>
                        </tr>
                        @foreach ($day['stores'] as $s)
                            <tr class="jx-sub-tr" data-child="trend-{{ $dk }}" hidden>
                                <td><span class="jx-sub-name">{{ $s['store'] }}</span></td>
                                <td class="c" style="color:var(--muted)">{{ number_format($s['orders']) }}</td>
                                <td class="r" style="font-weight:600;"><span class="{{ $s['net_pnl'] < 0 ? 'jx-neg' : 'jx-pos' }}">{{ $m($s['net_pnl']) }}</span></td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="3" class="jx-nodata">No ad spend logged for this period yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== P&L by Store ===== --}}
    <div class="jx-panel flush jx-rise" style="margin-bottom:16px;--i:8">
        <div class="jx-panel-head">
            <p class="jx-h">P&amp;L by Store</p>
            <p class="jx-sub">Store-wise net P&amp;L, ad spend and orders. Ranked by profit.</p>
        </div>
        <div class="jx-scroll">
            <table class="jx-table">
                <thead>
                    <tr>
                        <th>Store</th>
                        <th class="c">Orders</th>
                        <th class="r">Ad Spend (incl. GST)</th>
                        <th class="r">Profit on ad</th>
                        <th class="r">Net P&amp;L</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($byStore as $i => $row)
                        @php $rr = $row['ad_cost_with_gst'] > 0 ? $row['net_pnl'] / $row['ad_cost_with_gst'] * 100 : 0; @endphp
                        <tr>
                            <td>
                                <div class="jx-cellwrap">
                                    <span class="jx-rank {{ $i === 0 ? 'first' : '' }}">{{ $i + 1 }}</span>
                                    <div>
                                        <span style="font-weight:500;">{{ $row['label'] }}</span>
                                        <div class="jx-mini-bar" style="margin-left:0;"><i style="width:{{ max(4, $row['orders'] / $maxOrdersStore * 100) }}%;background:var(--ink)"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td class="c">{{ number_format($row['orders']) }}</td>
                            <td class="r">{{ $m($row['ad_cost_with_gst']) }}</td>
                            <td class="r"><span class="{{ $rr < 0 ? 'jx-neg' : 'jx-pos' }}">{{ number_format($rr, 0) }}%</span></td>
                            <td class="r" style="font-weight:600;"><span class="{{ $row['net_pnl'] < 0 ? 'jx-neg' : 'jx-pos' }}">{{ $m($row['net_pnl']) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="jx-nodata">No data for this period yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== P&L by Product ===== --}}
    <div class="jx-panel flush jx-rise" style="margin-bottom:16px;--i:9">
        <div class="jx-panel-head">
            <p class="jx-h">P&amp;L by Product</p>
            <p class="jx-sub">Product-wise orders and net P&amp;L. Ranked by profit.</p>
        </div>
        <div class="jx-scroll">
            <table class="jx-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="c">Orders</th>
                        <th class="r">Ad Spend (incl. GST)</th>
                        <th class="r">Profit on ad</th>
                        <th class="r">Net P&amp;L</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($byProduct as $i => $row)
                        @php $rr = $row['ad_cost_with_gst'] > 0 ? $row['net_pnl'] / $row['ad_cost_with_gst'] * 100 : 0; @endphp
                        <tr>
                            <td>
                                <div class="jx-cellwrap">
                                    <span class="jx-rank {{ $i === 0 ? 'first' : '' }}">{{ $i + 1 }}</span>
                                    <div>
                                        <span style="font-weight:500;">{{ $row['label'] }}</span>
                                        <div class="jx-mini-bar" style="margin-left:0;"><i style="width:{{ max(4, $row['orders'] / $maxOrdersProduct * 100) }}%;background:var(--ink)"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td class="c">{{ number_format($row['orders']) }}</td>
                            <td class="r">{{ $m($row['ad_cost_with_gst']) }}</td>
                            <td class="r"><span class="{{ $rr < 0 ? 'jx-neg' : 'jx-pos' }}">{{ number_format($rr, 0) }}%</span></td>
                            <td class="r" style="font-weight:600;"><span class="{{ $row['net_pnl'] < 0 ? 'jx-neg' : 'jx-pos' }}">{{ $m($row['net_pnl']) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="jx-nodata">No data for this period yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Charts + animations (is page ke liye, layout par depend nahi) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@verbatim
<script>
(function () {
    'use strict';

    var dataEl = document.getElementById('jx-data');
    if (!dataEl) return;

    var DATA = JSON.parse(dataEl.textContent || '{}');
    var S = DATA.series || { labels: [], net: [], cum: [], orders: [], ad: [] };
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var INK = '#0F1210', MUTED = '#6E756B', LINE = '#E6E8E1', LIME = '#DDF45B';
    var DIM = '#9BA197';
    var PALETTE = ['#0F1210', '#A3C21A', '#8B5CF6', '#F59E0B', '#38BDF8', '#F472B6', '#34D399', '#94A3B8'];
    var FONT = "'Hanken Grotesk', system-ui, sans-serif";
    var mode = 'cum';
    var hero = null;

    /* ---------- helpers ---------- */
    function num(v) { return Number(v) || 0; }
    function fmt(v, d) { return num(v).toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d }); }
    function money(v, d) { var n = num(v); return (n < 0 ? '−' : '') + '₹' + fmt(Math.abs(n), d === undefined ? 2 : d); }
    function compact(v) {
        var a = Math.abs(v), s = v < 0 ? '−' : '';
        if (a >= 1e7) return s + (a / 1e7).toFixed(1).replace(/\.0$/, '') + 'Cr';
        if (a >= 1e5) return s + (a / 1e5).toFixed(1).replace(/\.0$/, '') + 'L';
        if (a >= 1e3) return s + (a / 1e3).toFixed(1).replace(/\.0$/, '') + 'K';
        return s + Math.round(a);
    }
    function tween(from, to, dur, cb) {
        if (reduce || from === to) { cb(to); return; }
        var t0 = null;
        function step(ts) {
            if (t0 === null) t0 = ts;
            var p = Math.min(1, (ts - t0) / dur), e = 1 - Math.pow(1 - p, 3);
            cb(from + (to - from) * e);
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    function whenVisible(el, fn) {
        if (!el || !('IntersectionObserver' in window)) { fn(); return; }
        var io = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) { io.disconnect(); fn(); }
        }, { threshold: .15 });
        io.observe(el);
    }

    /* ---------- count-up numbers ---------- */
    document.querySelectorAll('[data-count]').forEach(function (el) {
        var to = num(el.getAttribute('data-count'));
        var d = parseInt(el.getAttribute('data-dec') || '0', 10);
        var pre = el.getAttribute('data-prefix') || '';
        tween(0, to, 1100, function (v) { el.textContent = (v < 0 ? '−' : '') + pre + fmt(Math.abs(v), d); });
    });

    /* ---------- KPI sparklines ---------- */
    document.querySelectorAll('[data-spark]').forEach(function (el) {
        var arr = (S[el.getAttribute('data-spark')] || []).map(num);
        if (!arr.length) return;
        if (arr.length === 1) arr = [arr[0], arr[0]];
        var min = Math.min.apply(null, arr), max = Math.max.apply(null, arr), rng = (max - min) || 1;
        var W = 100, H = 32, pad = 3;
        var pts = arr.map(function (v, i) { return [(i / (arr.length - 1)) * W, H - pad - ((v - min) / rng) * (H - pad * 2)]; });
        var line = 'M' + pts.map(function (p) { return p[0].toFixed(2) + ' ' + p[1].toFixed(2); }).join(' L');
        el.innerHTML = '<svg viewBox="0 0 100 32" preserveAspectRatio="none" width="100%" height="100%" aria-hidden="true">' +
            '<path d="' + line + ' L' + W + ' ' + H + ' L0 ' + H + ' Z" fill="currentColor" opacity=".14"/>' +
            '<path d="' + line + '" fill="none" stroke="currentColor" stroke-width="1.6" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round"/></svg>';
    });

    /* ---------- accordions (where-went rows + trend rows) ---------- */
    window.jxAcc = function (id) {
        var kids = document.querySelectorAll('[data-child="' + id + '"]');
        var head = document.querySelector('[data-acc="' + id + '"]');
        if (!kids.length) return;
        var opening = kids[0].hidden;
        kids.forEach(function (k) { k.hidden = !opening; });
        if (head) head.classList.toggle('open', opening);
    };

    /* ---------- charts ---------- */
    if (typeof window.Chart === 'undefined') {
        var e1 = document.getElementById('heroEmpty');
        if (e1) { e1.textContent = 'Charts could not load. Check your internet connection and refresh.'; e1.hidden = false; }
        return;
    }
    Chart.defaults.font.family = FONT;
    Chart.defaults.color = MUTED;

    // left-to-right sweep
    var reveal = {
        id: 'reveal',
        beforeInit: function (c) { c.$p = reduce ? 1 : 0; },
        beforeDatasetsDraw: function (c) {
            if (c.$p === undefined || c.$p >= 1) return;
            var a = c.chartArea, x = c.ctx;
            x.save(); x.beginPath();
            x.rect(0, 0, a.left + (a.right - a.left + 24) * c.$p, c.height);
            x.clip(); c.$clip = true;
        },
        afterDatasetsDraw: function (c) { if (c.$clip) { c.ctx.restore(); c.$clip = false; } }
    };
    function play(chart, dur) {
        if (reduce) { chart.$p = 1; chart.draw(); return; }
        chart.$p = 0; chart.draw();
        tween(0, 1, dur || 1000, function (p) { chart.$p = p; chart.draw(); });
    }

    // soft glow under the lime line
    var glow = {
        id: 'glow',
        beforeDatasetDraw: function (c, args) {
            if (args.meta.type !== 'line') return;
            c.ctx.save(); c.ctx.shadowColor = 'rgba(221,244,91,.55)'; c.ctx.shadowBlur = 14;
        },
        afterDatasetDraw: function (c, args) { if (args.meta.type === 'line') c.ctx.restore(); }
    };

    // hover guide line
    var crosshair = {
        id: 'crosshair',
        afterDatasetsDraw: function (c) {
            var t = c.tooltip;
            if (!t || !t.getActiveElements || !t.getActiveElements().length) return;
            var x = t.getActiveElements()[0].element.x, a = c.chartArea, g = c.ctx;
            g.save(); g.beginPath(); g.setLineDash([4, 4]);
            g.moveTo(x, a.top); g.lineTo(x, a.bottom);
            g.lineWidth = 1; g.strokeStyle = 'rgba(255,255,255,.28)'; g.stroke(); g.restore();
        }
    };

    // glowing dot on the latest point of the cumulative line
    var endDot = {
        id: 'endDot',
        afterDatasetsDraw: function (c) {
            if (mode !== 'cum' || c.$p < 1) return;
            var pts = c.getDatasetMeta(0).data, last = pts[pts.length - 1];
            if (!last) return;
            var g = c.ctx;
            g.save();
            g.beginPath(); g.arc(last.x, last.y, 9, 0, Math.PI * 2); g.fillStyle = 'rgba(221,244,91,.22)'; g.fill();
            g.beginPath(); g.arc(last.x, last.y, 4, 0, Math.PI * 2); g.fillStyle = LIME; g.fill();
            g.restore();
        }
    };

    function grad(top, bottom) {
        return function (ctx) {
            var el = ctx.element;
            if (!el || !ctx.chart || !ctx.chart.ctx) return top;
            var p = el.getProps(['y', 'base'], true);
            var g = ctx.chart.ctx.createLinearGradient(0, p.y, 0, p.base);
            g.addColorStop(0, top); g.addColorStop(1, bottom);
            return g;
        };
    }
    function area(ctx) {
        var a = ctx.chart.chartArea;
        if (!a) return 'rgba(221,244,91,.2)';
        var g = ctx.chart.ctx.createLinearGradient(0, a.top, 0, a.bottom);
        g.addColorStop(0, 'rgba(221,244,91,.38)'); g.addColorStop(1, 'rgba(221,244,91,0)');
        return g;
    }

    function heroDatasets() {
        if (mode === 'day') {
            return [{ type: 'bar', label: 'Net P&L', data: S.net, borderRadius: 7, borderSkipped: false, maxBarThickness: 30,
                backgroundColor: function (ctx) { return (ctx.raw < 0 ? grad('#F87171', '#B91C1C') : grad(LIME, '#8FB011'))(ctx); } }];
        }
        if (mode === 'orders') {
            return [{ type: 'bar', label: 'Orders', data: S.orders, borderRadius: 7, borderSkipped: false, maxBarThickness: 30, backgroundColor: grad('rgba(255,255,255,.9)', 'rgba(255,255,255,.22)') }];
        }
        if (mode === 'ad') {
            return [{ type: 'bar', label: 'Ad spend', data: S.ad, borderRadius: 7, borderSkipped: false, maxBarThickness: 30, backgroundColor: grad('#FCD34D', '#D97706') }];
        }
        return [{ type: 'line', label: 'Cumulative P&L', data: S.cum, borderColor: LIME, backgroundColor: area, fill: 'origin', borderWidth: 2.5, tension: .35,
            pointRadius: 0, pointHoverRadius: 6, pointHoverBackgroundColor: INK, pointHoverBorderColor: LIME, pointHoverBorderWidth: 3 }];
    }

    function heroTitle() {
        var per = DATA.gran === 'monthly' ? 'monthly' : 'daily';
        var t = { cum: 'Cumulative Net P&L · ' + per, day: (per === 'monthly' ? 'Monthly' : 'Daily') + ' Net P&L', orders: 'Orders · ' + per, ad: 'Ad spend (incl. GST) · ' + per };
        document.getElementById('heroTitle').textContent = t[mode];
    }

    function buildHero() {
        var has = (S.labels || []).length > 0;
        document.getElementById('heroEmpty').hidden = has;
        var ad = (S.ad || []).some(function (v) { return num(v) > 0; });
        if (!ad) { var b = document.querySelector('#heroToggle [data-mode="ad"]'); if (b) b.style.display = 'none'; }

        hero = new Chart(document.getElementById('heroChart'), {
            type: 'bar',
            data: { labels: S.labels || [], datasets: heroDatasets() },
            plugins: [reveal, glow, crosshair, endDot],
            options: {
                responsive: true, maintainAspectRatio: false, animation: false,
                layout: { padding: { top: 8, right: 6 } },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1A2014', borderColor: 'rgba(221,244,91,.35)', borderWidth: 1, padding: 12, cornerRadius: 12, boxPadding: 5,
                        titleColor: '#fff', bodyColor: '#D6DACF', titleFont: { weight: '600', family: FONT }, bodyFont: { family: FONT },
                        callbacks: { label: function (t) { return ' ' + t.dataset.label + ': ' + (t.dataset.label === 'Orders' ? fmt(t.parsed.y, 0) : money(t.parsed.y)); } }
                    }
                },
                scales: {
                    x: { offset: false, grid: { display: false }, border: { display: false }, ticks: { color: DIM, maxRotation: 0, autoSkip: true, maxTicksLimit: 8, padding: 8 } },
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,.07)', drawTicks: false }, border: { display: false },
                         ticks: { color: DIM, padding: 10, maxTicksLimit: 5, callback: function (v) { return (mode === 'orders' ? '' : '₹') + compact(v); } } }
                }
            }
        });
        play(hero, 1200);
    }

    function updateHero() {
        heroTitle();
        hero.data.datasets = heroDatasets();
        hero.options.scales.x.offset = (mode !== 'cum');
        hero.$p = reduce ? 1 : 0;
        hero.update('none');
        play(hero, 900);
    }

    document.querySelectorAll('#heroToggle button').forEach(function (b) {
        b.addEventListener('click', function () {
            if (mode === b.getAttribute('data-mode')) return;
            mode = b.getAttribute('data-mode');
            document.querySelectorAll('#heroToggle button').forEach(function (x) { x.classList.toggle('active', x === b); });
            updateHero();
        });
    });

    /* ---------- store share donut ---------- */
    var centerText = {
        id: 'centerText',
        afterDraw: function (chart) {
            var d = chart.data.datasets[0].data, a = chart.chartArea;
            if (!a) return;
            var total = d.reduce(function (s, v) { return s + num(v); }, 0);
            var x = (a.left + a.right) / 2, y = (a.top + a.bottom) / 2, g = chart.ctx;
            g.save(); g.textAlign = 'center'; g.textBaseline = 'middle';
            g.fillStyle = INK; g.font = '600 28px ' + FONT; g.fillText(fmt(total, 0), x, y - 8);
            g.fillStyle = MUTED; g.font = '500 12px ' + FONT; g.fillText('orders', x, y + 15);
            g.restore();
        }
    };

    function buildDonut() {
        var s = DATA.stores || { labels: [], orders: [] };
        var ul = document.getElementById('storeLegend');
        var total = (s.orders || []).reduce(function (a, v) { return a + num(v); }, 0);
        (s.labels || []).forEach(function (label, i) {
            var li = document.createElement('li');
            var dot = document.createElement('i'); dot.style.background = PALETTE[i % PALETTE.length];
            var name = document.createElement('span'); name.textContent = label; name.title = label;
            var val = document.createElement('b'); val.textContent = fmt(s.orders[i], 0);
            var pct = document.createElement('em'); pct.textContent = total ? Math.round(num(s.orders[i]) / total * 100) + '%' : '0%';
            li.appendChild(dot); li.appendChild(name); li.appendChild(val); li.appendChild(pct);
            ul.appendChild(li);
        });
        document.getElementById('storeEmpty').hidden = total > 0;
        if (!total) return;
        new Chart(document.getElementById('storeChart'), {
            type: 'doughnut',
            data: { labels: s.labels, datasets: [{ data: s.orders, backgroundColor: s.labels.map(function (_, i) { return PALETTE[i % PALETTE.length]; }), borderColor: '#fff', borderWidth: 3, hoverOffset: 6 }] },
            plugins: [centerText],
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '72%',
                animation: { duration: reduce ? 0 : 1100, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: INK, padding: 10, cornerRadius: 12, callbacks: { label: function (t) { return ' ' + t.label + ': ' + fmt(t.parsed, 0) + ' orders'; } } }
                }
            }
        });
    }

    buildHero();
    whenVisible(document.getElementById('donutBox'), buildDonut);   // neeche ka chart scroll par animate hota hai
})();
</script>
@endverbatim
@endsection