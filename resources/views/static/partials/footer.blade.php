<footer class="bg-[#f5f5f7] border-t border-black/5">
    <div class="max-w-6xl mx-auto px-6 py-16">

        <div class="grid grid-cols-1 md:grid-cols-5 gap-10 mb-12">

            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-[#1d1d1f] flex items-center justify-center">
                        <span class="text-white font-semibold text-xs">JX</span>
                    </div>
                    <span class="text-[15px] font-semibold tracking-tight">JENHIKX</span>
                </div>
                <p class="text-[13px] text-[#1d1d1f]/60 leading-relaxed max-w-xs">
                    Performance marketing agency helping Shopify brands scale with ads, store design and creatives.
                </p>
            </div>

            <div>
                <h4 class="text-[11px] font-semibold uppercase tracking-wider text-[#1d1d1f]/40 mb-4">Services</h4>
                <ul class="space-y-2.5 text-[13px] text-[#1d1d1f]/70">
                    <li><a href="{{ url('/services') }}" class="hover:text-[#1d1d1f] transition">Google &amp; Meta Ads</a></li>
                    <li><a href="{{ url('/services') }}" class="hover:text-[#1d1d1f] transition">Shopify Store Design</a></li>
                    <li><a href="{{ url('/services') }}" class="hover:text-[#1d1d1f] transition">Landing Pages</a></li>
                    <li><a href="{{ url('/services') }}" class="hover:text-[#1d1d1f] transition">Creative Production</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-[11px] font-semibold uppercase tracking-wider text-[#1d1d1f]/40 mb-4">Company</h4>
                <ul class="space-y-2.5 text-[13px] text-[#1d1d1f]/70">
                    <li><a href="{{ url('/about') }}" class="hover:text-[#1d1d1f] transition">About</a></li>
                    <li><a href="{{ url('/work') }}" class="hover:text-[#1d1d1f] transition">Results</a></li>
                    <li><a href="{{ url('/contact') }}" class="hover:text-[#1d1d1f] transition">Contact</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-[11px] font-semibold uppercase tracking-wider text-[#1d1d1f]/40 mb-4">Contact</h4>
                <ul class="space-y-2.5 text-[13px] text-[#1d1d1f]/70">
                    <li><a href="mailto:hello@jenhikx.in" class="hover:text-[#1d1d1f] transition">hello@jenhikx.in</a></li>
                </ul>
            </div>
        </div>

        <div class="pt-8 border-t border-black/5 text-[12px] text-[#1d1d1f]/40">
            &copy; {{ date('Y') }} JENHIKX. All rights reserved.
        </div>
    </div>
</footer>