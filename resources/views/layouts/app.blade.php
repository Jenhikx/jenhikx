{{-- resources\views\layouts\app.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#FFFFFF">
    <title>@yield('title', 'JENHIKX')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg: #FFFFFF;
            --surface: #F4F5F1;
            --line: #E6E8E1;
            --ink: #0F1210;
            --muted: #6E756B;
            --lime: #DDF45B;
            --lime-deep: #8FA800;
            --danger: #DC2626;
            --ok: #16A34A;
            --shadow: 0 1px 2px rgba(15,18,16,.04), 0 12px 32px -12px rgba(15,18,16,.10);
        }
        html, body { background: var(--bg); }
        body { font-family: 'Hanken Grotesk', ui-sans-serif, system-ui, sans-serif; color: var(--ink); -webkit-font-smoothing: antialiased; font-variant-numeric: tabular-nums; }
        a:focus-visible, button:focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
        ::-webkit-scrollbar { height: 8px; width: 8px; } ::-webkit-scrollbar-thumb { background: var(--line); border-radius: 99px; }

        /* Ambient background: ek soft lime glow + faint grid, sirf page ke peeche */
        body::before { content: ""; position: fixed; inset: 0; z-index: -1; pointer-events: none;
            background:
                radial-gradient(900px 420px at 88% -8%, rgba(221,244,91,.28), transparent 60%),
                linear-gradient(rgba(15,18,16,.035) 1px, transparent 1px) 0 0 / 100% 44px,
                linear-gradient(90deg, rgba(15,18,16,.035) 1px, transparent 1px) 0 0 / 44px 100%; }

        /* Shell */
        .jx-shell { display: flex; min-height: 100vh; width: 100%; }
        .jx-main { flex: 1; min-width: 0; padding: 16px; padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px)); }
        .jx-inner { max-width: 1680px; margin: 0 auto; }
        @media (min-width: 1024px) { .jx-main { padding: 28px 36px 40px; } }

        /* Sidebar */
        .jx-sidebar { background: rgba(255,255,255,.82); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border-right: 1px solid var(--line); color: var(--muted); padding: 16px 14px; display: flex; flex-direction: column;
            width: 80px; flex-shrink: 0; position: sticky; top: 0; height: 100vh; transition: width .25s ease; }
        body.sb-expanded .jx-sidebar { width: 244px; }
        .jx-label { display: none; white-space: nowrap; font-size: 14px; font-weight: 500; }
        body.sb-expanded .jx-label { display: inline; }
        .jx-brand { display: flex; align-items: center; gap: 12px; height: 48px; padding: 0 4px; margin-bottom: 20px; color: var(--ink); }
        .jx-mark { width: 44px; height: 44px; border-radius: 15px; background: var(--ink); color: var(--lime); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
        .jx-nav { flex: 1; overflow-y: auto; scrollbar-width: none; display: flex; flex-direction: column; gap: 4px; }
        .jx-group { margin: 14px 0 4px; border-top: 1px solid var(--line); padding-top: 14px; display: flex; flex-direction: column; gap: 4px; }
        .jx-link { display: flex; align-items: center; gap: 12px; height: 46px; padding: 0 14px; border-radius: 15px; color: var(--muted); transition: background .15s, color .15s; background: transparent; border: 0; width: 100%; cursor: pointer; }
        .jx-link:hover { background: var(--surface); color: var(--ink); }
        .jx-link.active { background: var(--ink); color: #fff; }
        .jx-link.active svg { color: var(--lime); }
        .jx-link svg { flex-shrink: 0; }
        .jx-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--ink); color: var(--lime); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
        .jx-user { display: flex; align-items: center; gap: 12px; padding: 12px 4px 0; margin-top: 10px; border-top: 1px solid var(--line); }
        .jx-name { font-size: 13px; font-weight: 600; color: var(--ink); line-height: 1.2; }
        .jx-role { font-size: 11px; color: var(--muted); text-transform: capitalize; }
        .jx-close { display: none; }

        /* Mobile drawer */
        .jx-overlay { position: fixed; inset: 0; background: rgba(15,18,16,.4); backdrop-filter: blur(3px); opacity: 0; pointer-events: none; transition: opacity .25s; z-index: 40; }
        body.sb-mobile-open .jx-overlay { opacity: 1; pointer-events: auto; }
        .jx-mobilebar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; position: sticky; top: 0; z-index: 30; padding: 8px 0; margin-top: -8px;
            background: rgba(255,255,255,.8); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); }
        @media (max-width: 1023px) {
            .jx-sidebar { position: fixed; z-index: 50; top: 0; left: 0; bottom: 0; height: 100%; width: 270px; border-right: 0; box-shadow: 0 0 40px rgba(0,0,0,.15); background: #fff;
                transform: translateX(-105%); transition: transform .28s cubic-bezier(.4,0,.2,1); }
            body.sb-mobile-open .jx-sidebar { transform: none; }
            .jx-label { display: inline; }
            .jx-close { display: flex; }
            .jx-desktop-only { display: none !important; }
        }
        @media (min-width: 1024px) { .jx-mobilebar { display: none; } }

        /* Header */
        .jx-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
        @media (max-width: 640px) { .jx-header { flex-direction: column; } .jx-header > div:last-child { width: 100%; } .jx-header .jx-btn { width: 100%; } }
        .jx-title { font-size: clamp(28px, 4.2vw, 54px); line-height: 1.06; letter-spacing: -0.03em; font-weight: 500; }
        .jx-chip { display: inline-flex; align-items: center; justify-content: center; height: .72em; padding: 0 .28em; border-radius: 999px; background: var(--surface); border: 1px solid var(--line); vertical-align: middle; margin: 0 .06em; }
        .jx-chip.lime { background: var(--lime); border-color: var(--lime); }
        .jx-chip svg { width: .46em; height: .46em; }

        /* Buttons, cards, bits */
        .jx-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 48px; padding: 0 22px; border-radius: 999px; font-size: 14px; font-weight: 500; transition: background .15s, transform .15s; white-space: nowrap; cursor: pointer; }
        .jx-btn:active { transform: scale(.97); }
        .jx-btn-dark { background: var(--ink); color: #fff; } .jx-btn-dark:hover { background: #2A2E2B; }
        .jx-btn-round { width: 48px; padding: 0; background: #fff; color: var(--ink); border: 1px solid var(--line); } .jx-btn-round:hover { background: var(--surface); }
        .jx-card { background: var(--surface); border: 1px solid var(--line); border-radius: 28px; padding: 22px; }
        .jx-card.lime { background: var(--lime); border-color: var(--lime); }
        .jx-card.dark { background: var(--ink); border-color: var(--ink); color: #fff; }
        .jx-iconchip { width: 40px; height: 40px; border-radius: 14px; background: #fff; border: 1px solid var(--line); color: var(--ink); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .jx-card.lime .jx-iconchip { border-color: transparent; }
        .jx-badge { background: #fff; color: var(--ink); border: 1px solid var(--line); border-radius: 999px; padding: 3px 10px; font-size: 12px; font-weight: 500; }
        .jx-card.lime .jx-badge { border-color: transparent; }
        .jx-tabs { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; padding-bottom: 2px; }
        .jx-tab { height: 38px; padding: 0 18px; border-radius: 999px; background: #fff; border: 1px solid var(--line); font-size: 13px; font-weight: 500; white-space: nowrap; transition: background .15s; text-align: center; display: inline-flex; align-items: center; justify-content: center; }
        .jx-tab:hover { background: var(--surface); }
        .jx-tab.active { background: var(--ink); border-color: var(--ink); color: #fff; }
        @media (max-width: 640px) {
            .jx-tabs { flex-wrap: wrap; overflow-x: visible; }
            .jx-tab { flex: 1 1 calc(50% - 4px); }
        }
        .jx-meter { display: flex; gap: 6px; height: 46px; }
        .jx-seg { flex: 1; border-radius: 999px; background: var(--ink); }
        .jx-seg.off { background: transparent; border: 1.5px dashed rgba(15,18,16,.28); }

        /* Forms and tables */
        .jx-input { height: 44px; border-radius: 14px; border: 1px solid var(--line); background: #fff; padding: 0 14px; font-size: 14px; color: var(--ink); font-family: inherit; }
        .jx-input:focus { outline: 2px solid var(--ink); outline-offset: 1px; }
        .jx-input.num { width: 120px; height: 40px; text-align: right; }
        .jx-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .jx-table th { text-align: left; font-weight: 500; font-size: 12px; color: var(--muted); padding: 12px 16px; background: var(--surface); white-space: nowrap; }
        .jx-table td { padding: 8px 16px; border-top: 1px solid var(--line); }
        .jx-table tfoot td { font-weight: 600; background: var(--surface); }
        .jx-table tr.today td { background: #FAFDE6; }
        .jx-table .r { text-align: right; }

        /* Chart (kept for other pages that may still use it) */
        .jx-plot { height: 240px; display: flex; align-items: flex-end; gap: 8px; background-image: linear-gradient(rgba(15,18,16,.07) 1px, transparent 1px); background-size: 100% 20%; }
        .jx-col { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; }
        .jx-bar { width: min(44px, 100%); border-radius: 999px; background: var(--ink); position: relative; }
        .jx-bar-in { position: absolute; left: 0; right: 0; bottom: 0; border-radius: 999px; background: var(--lime); }
        .jx-dot { position: absolute; top: 10px; left: 50%; transform: translateX(-50%); width: 10px; height: 10px; border-radius: 50%; background: #fff; }
        .jx-bar.empty { height: 100%; background: transparent; border: 1.5px dashed rgba(15,18,16,.28); }
        .jx-bar.empty .jx-dot { background: rgba(15,18,16,.22); }
    </style>

    @stack('head')
</head>
<body>

    <script>
        (function () {
            var b = document.body;
            try { if (localStorage.getItem('jx-sb') === '1' && window.innerWidth >= 1024) b.classList.add('sb-expanded'); } catch (e) {}
            window.toggleSidebar = function () {
                var on = b.classList.toggle('sb-expanded');
                try { localStorage.setItem('jx-sb', on ? '1' : '0'); } catch (e) {}
            };
            window.openSidebar = function () { b.classList.add('sb-mobile-open'); };
            window.closeSidebar = function () { b.classList.remove('sb-mobile-open'); };
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSidebar(); });
        })();
    </script>

    <div class="jx-overlay" onclick="closeSidebar()"></div>

    <div class="jx-shell">

        @include('partials.sidebar')

        <div class="jx-main">
            <div class="jx-inner">

                {{-- Mobile top bar --}}
                <div class="jx-mobilebar">
                    <button type="button" onclick="openSidebar()" class="jx-btn jx-btn-round" aria-label="Open menu">
                        @include('partials.icon', ['name' => 'menu'])
                    </button>
                    <span class="font-bold tracking-tight">JENHIKX</span>
                    <div class="jx-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                </div>

                @if (session('success'))
                    <div class="mb-4 px-4 py-3 rounded-2xl bg-[var(--lime)] text-sm font-medium">{{ session('success') }}</div>
                @endif

                <header class="jx-header">
                    <h1 class="jx-title">@yield('page-title', 'Dashboard')</h1>
                    <div class="flex items-center gap-2 shrink-0">@yield('header-actions')</div>
                </header>

                @yield('content')

            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>