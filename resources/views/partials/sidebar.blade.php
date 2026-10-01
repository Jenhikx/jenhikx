@php
    $isAdmin = Auth::user()->role === 'admin';

    $nav = collect([
        ['route' => $isAdmin ? 'admin.dashboard' : 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'ad-spend', 'label' => 'Ad Spend', 'icon' => 'adspend', 'module' => 'ad-spend'],
        ['route' => 'pnl.index', 'label' => 'P&L', 'icon' => 'pnl', 'module' => 'pnl'],
        ['route' => 'notes.index', 'label' => 'Notes', 'icon' => 'notes', 'module' => 'notes'],
    ])->filter(fn ($item) => !isset($item['module']) || Auth::user()->hasModuleAccess($item['module']));

    $adminNav = [
        ['route' => 'pnl.settings', 'label' => 'P&L Settings', 'icon' => 'settings'],
        ['route' => 'expenses.index', 'label' => 'Expenses', 'icon' => 'expenses'],
        ['route' => 'income.index', 'label' => 'Additional Income', 'icon' => 'income'],
        ['route' => 'withdrawals.index', 'label' => 'Founder Withdrawals', 'icon' => 'withdrawals'],
        ['route' => 'users.index', 'label' => 'Manage Users', 'icon' => 'users'],
        ['route' => 'access.index', 'label' => 'Manage Access', 'icon' => 'access'],
    ];
@endphp

<aside id="sidebar" class="jx-sidebar">

    <div class="jx-brand">
        <div class="jx-mark">JK</div>
        <span class="jx-label" style="font-size:17px;font-weight:700;letter-spacing:-0.02em;">JENHIKX</span>
        <button type="button" onclick="closeSidebar()" class="jx-close ml-auto items-center justify-center" aria-label="Close menu">
            @include('partials.icon', ['name' => 'close'])
        </button>
    </div>

    <nav class="jx-nav">
        @foreach ($nav as $item)
            @if (Route::has($item['route']))
                <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                   class="jx-link {{ request()->routeIs($item['route'], $item['route'] . '.*') ? 'active' : '' }}">
                    @include('partials.icon', ['name' => $item['icon']])
                    <span class="jx-label">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach

        @if ($isAdmin)
            <div class="jx-group">
                @foreach ($adminNav as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                           class="jx-link {{ request()->routeIs($item['route'], $item['route'] . '.*') ? 'active' : '' }}">
                            @include('partials.icon', ['name' => $item['icon']])
                            <span class="jx-label">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
    </nav>

    <div>
        <button type="button" onclick="toggleSidebar()" class="jx-link jx-desktop-only" title="Expand or collapse menu">
            @include('partials.icon', ['name' => 'expand'])
            <span class="jx-label">Collapse menu</span>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="jx-link" title="Log out">
                @include('partials.icon', ['name' => 'logout'])
                <span class="jx-label">Log out</span>
            </button>
        </form>

        <div class="jx-user">
            <div class="jx-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div class="jx-label">
                <div class="jx-name">{{ Auth::user()->name }}</div>
                <div class="jx-role">{{ Auth::user()->role }}</div>
            </div>
        </div>
    </div>

</aside>