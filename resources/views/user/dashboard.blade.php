@extends('layouts.app')

@section('title', 'Dashboard — JENHIKX')

@section('page-title')
    Hello, {{ Str::before(Auth::user()->name, ' ') }}<br>
    log today's <span class="jx-chip lime">@include('partials.icon', ['name' => 'adspend'])</span> spend
@endsection

@section('header-actions')
    <a href="{{ Route::has('ad-spend') ? route('ad-spend') : '#' }}" class="jx-btn jx-btn-dark">
        @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
        <span class="hidden sm:inline">Add ad spend</span>
    </a>
@endsection

@section('content')
@php
    // SAMPLE DATA: replace with real values once the Ad Spend module is built.
    $orders = 780; $adSpend = 48250; $cpa = 61.8;
    $daysLogged = 18; $daysInMonth = 30;
    $segLogged = round(8 * $daysLogged / $daysInMonth);
    $week = [
        ['Mon', .88, .55], ['Tue', .62, .40], ['Wed', .75, .30], ['Thu', null, null],
        ['Fri', .90, .55], ['Sat', .70, .45], ['Sun', .60, .38],
    ];
    $quick = [
        ['ad-spend', 'Ad Spend', 'Log daily spend and orders for each store.', 'adspend'],
        ['pnl.index', 'Profit & Loss', 'See profit by store and by month.', 'pnl'],
    ];
@endphp

<div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_290px]">

    {{-- LEFT --}}
    <div class="space-y-4 min-w-0">

        <div class="jx-tabs">
            <button class="jx-tab active">This month</button>
            <button class="jx-tab">Last month</button>
            <button class="jx-tab">This year</button>
        </div>

        <div class="grid gap-4 md:grid-cols-3">

            {{-- Orders --}}
            <div class="jx-card">
                <div class="flex items-center gap-3">
                    <div class="jx-iconchip">@include('partials.icon', ['name' => 'home'])</div>
                    <span class="text-sm font-medium">Orders</span>
                </div>
                <div class="mt-5 text-5xl font-medium tracking-tight">{{ number_format($orders) }}</div>
                <p class="text-xs text-[var(--muted)] mt-2">Total orders this month</p>
            </div>

            {{-- Ad Spend --}}
            <div class="jx-card lime">
                <div class="flex items-center gap-3">
                    <div class="jx-iconchip">@include('partials.icon', ['name' => 'adspend'])</div>
                    <span class="text-sm font-medium">Ad spend</span>
                </div>
                <div class="mt-5 text-5xl font-medium tracking-tight">₹{{ number_format($adSpend) }}</div>
                <div class="mt-2 text-xs font-medium">{{ $daysLogged }} / {{ $daysInMonth }} days logged</div>
                <div class="jx-meter mt-3">
                    @for ($i = 1; $i <= 8; $i++)<div class="jx-seg {{ $i > $segLogged ? 'off' : '' }}"></div>@endfor
                </div>
            </div>

            {{-- CPA --}}
            <div class="jx-card dark flex flex-col justify-between min-h-[190px]">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-[#B9BEB6]">Cost per order</span>
                    <span class="text-[var(--lime)]">@include('partials.icon', ['name' => 'arrow'])</span>
                </div>
                <div>
                    <div class="text-4xl xl:text-[40px] font-medium tracking-tight">₹{{ $cpa }}</div>
                    <p class="text-xs text-[#9BA197] mt-1">Ad spend divided by orders</p>
                </div>
                <a href="{{ Route::has('ad-spend') ? route('ad-spend') : '#' }}" class="jx-btn" style="background:#fff;color:var(--ink);height:44px;">Open Ad Spend</a>
            </div>
        </div>

        {{-- Chart --}}
        <div class="jx-card">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div class="flex items-center gap-3">
                    <div class="jx-iconchip">@include('partials.icon', ['name' => 'pnl'])</div>
                    <span class="text-xl font-medium tracking-tight">Statistics</span>
                    <span class="hidden sm:flex items-center gap-4 ml-3 text-xs">
                        <span class="flex items-center gap-1.5"><i class="w-2.5 h-2.5 rounded-full bg-[var(--ink)] inline-block"></i>Ad spend</span>
                        <span class="flex items-center gap-1.5"><i class="w-2.5 h-2.5 rounded-full bg-[var(--lime)] inline-block"></i>Orders</span>
                    </span>
                </div>
                <span class="jx-badge">Last 7 days</span>
            </div>

            <div class="jx-plot">
                @foreach ($week as [$label, $spend, $ord])
                    <div class="jx-col">
                        @if ($spend === null)
                            <div class="jx-bar empty"><span class="jx-dot"></span></div>
                        @else
                            <div class="jx-bar" style="height: {{ $spend * 100 }}%">
                                <span class="jx-dot"></span>
                                <div class="jx-bar-in" style="height: {{ ($ord / $spend) * 100 }}%"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="flex gap-2 mt-3 text-xs text-[var(--muted)]">
                @foreach ($week as [$label])
                    <div class="flex-1 text-center">{{ $label }}</div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- RIGHT --}}
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 content-start">
        @foreach ($quick as [$r, $title, $desc, $icon])
            @php $ready = Route::has($r); @endphp
            <{{ $ready ? 'a' : 'div' }} @if($ready) href="{{ route($r) }}" @endif
                class="jx-card block !p-4 transition {{ $ready ? 'hover:bg-[#EBEDE6]' : 'opacity-60' }}">
                <div class="flex items-center justify-between">
                    <div class="jx-iconchip">@include('partials.icon', ['name' => $icon])</div>
                    @if ($ready)
                        @include('partials.icon', ['name' => 'arrow', 'class' => 'w-4 h-4'])
                    @else
                        <span class="jx-badge">Soon</span>
                    @endif
                </div>
                <div class="mt-4 text-sm font-semibold">{{ $title }}</div>
                <p class="text-xs text-[var(--muted)] mt-1">{{ $desc }}</p>
            </{{ $ready ? 'a' : 'div' }}>
        @endforeach
    </div>

</div>
@endsection