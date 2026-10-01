<header x-data="{ mobileOpen: false }" class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-black/5">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-[#1d1d1f] flex items-center justify-center">
                    <span class="text-white font-semibold text-xs">JX</span>
                </div>
                <span class="text-[15px] font-semibold tracking-tight">JENHIKX</span>
            </a>

            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ url('/') }}" class="text-[13px] font-medium text-[#1d1d1f]/70 hover:text-[#1d1d1f] transition">Home</a>
                <a href="{{ url('/services') }}" class="text-[13px] font-medium text-[#1d1d1f]/70 hover:text-[#1d1d1f] transition">Services</a>
                <a href="{{ url('/work') }}" class="text-[13px] font-medium text-[#1d1d1f]/70 hover:text-[#1d1d1f] transition">Results</a>
                <a href="{{ url('/about') }}" class="text-[13px] font-medium text-[#1d1d1f]/70 hover:text-[#1d1d1f] transition">About</a>
                <a href="{{ url('/contact') }}" class="text-[13px] font-medium text-[#1d1d1f]/70 hover:text-[#1d1d1f] transition">Contact</a>
            </nav>

            <div class="flex items-center gap-4">
                <a href="{{ url('/contact') }}" class="hidden sm:inline-flex items-center px-4 py-1.5 rounded-full bg-[#0071e3] text-white text-[13px] font-medium hover:bg-[#0071e3]/90 transition">
                    Get Started
                </a>
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 -mr-2">
                    <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="md:hidden bg-white border-t border-black/5 px-6 py-5 space-y-4">
        <a href="{{ url('/') }}" class="block text-sm font-medium text-[#1d1d1f]">Home</a>
        <a href="{{ url('/services') }}" class="block text-sm font-medium text-[#1d1d1f]">Services</a>
        <a href="{{ url('/work') }}" class="block text-sm font-medium text-[#1d1d1f]">Results</a>
        <a href="{{ url('/about') }}" class="block text-sm font-medium text-[#1d1d1f]">About</a>
        <a href="{{ url('/contact') }}" class="block text-sm font-medium text-[#1d1d1f]">Contact</a>
        <a href="{{ url('/contact') }}" class="block text-center mt-2 px-4 py-2.5 rounded-full bg-[#0071e3] text-white text-sm font-medium">
            Get Started
        </a>
    </div>
</header>