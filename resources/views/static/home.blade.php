@extends('static.layout')

@section('content')

{{-- Hero Section --}}
<section class="max-w-6xl mx-auto px-6 pt-20 pb-24 text-center">
    <p class="text-[13px] font-semibold text-[#0071e3] mb-4 tracking-wide">Performance Marketing for Shopify Brands</p>
    <h1 class="text-[40px] md:text-[56px] font-bold tracking-tight text-[#1d1d1f] leading-[1.1] max-w-3xl mx-auto">
        Scale your Shopify store, profitably.
    </h1>
    <p class="mt-6 text-[17px] md:text-[19px] text-[#1d1d1f]/60 max-w-xl mx-auto leading-relaxed">
        Google Ads, Meta Ads, store design and creatives — built for D2C brands that care about real profit, not just revenue.
    </p>
    <div class="mt-10 flex items-center justify-center gap-4">
        <a href="{{ url('/contact') }}" class="inline-flex items-center px-6 py-3 rounded-full bg-[#0071e3] text-white text-[15px] font-medium hover:bg-[#0071e3]/90 transition">
            Get Started
        </a>
        <a href="{{ url('/work') }}" class="inline-flex items-center px-6 py-3 rounded-full border border-black/10 text-[#1d1d1f] text-[15px] font-medium hover:bg-black/[0.03] transition">
            See Results
        </a>
    </div>
</section>

{{-- Services Section --}}
<section class="bg-[#f5f5f7] py-24">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="text-[13px] font-semibold text-[#0071e3] tracking-wide text-center mb-3">What We Do</h2>
        <p class="text-[28px] md:text-[34px] font-bold text-[#1d1d1f] text-center mb-16 tracking-tight">Everything your store needs to grow</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-white rounded-2xl p-7 border border-black/5">
                <div class="w-10 h-10 rounded-xl bg-[#0071e3]/10 flex items-center justify-center mb-5">
                    <svg class="w-5 h-5 text-[#0071e3]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-[16px] font-semibold text-[#1d1d1f] mb-2">Google &amp; Meta Ads</h3>
                <p class="text-[14px] text-[#1d1d1f]/60 leading-relaxed">Data-driven campaigns focused on profitable CPA, not vanity metrics.</p>
            </div>

            <div class="bg-white rounded-2xl p-7 border border-black/5">
                <div class="w-10 h-10 rounded-xl bg-[#0071e3]/10 flex items-center justify-center mb-5">
                    <svg class="w-5 h-5 text-[#0071e3]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v18H3V3zm4 4h10M7 12h10M7 17h6"/></svg>
                </div>
                <h3 class="text-[16px] font-semibold text-[#1d1d1f] mb-2">Shopify Store Design</h3>
                <p class="text-[14px] text-[#1d1d1f]/60 leading-relaxed">Clean, conversion-focused storefronts built to turn visitors into buyers.</p>
            </div>

            <div class="bg-white rounded-2xl p-7 border border-black/5">
                <div class="w-10 h-10 rounded-xl bg-[#0071e3]/10 flex items-center justify-center mb-5">
                    <svg class="w-5 h-5 text-[#0071e3]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m6 10V11m-9 6h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-[16px] font-semibold text-[#1d1d1f] mb-2">Landing Pages</h3>
                <p class="text-[14px] text-[#1d1d1f]/60 leading-relaxed">High-converting pages built specifically for each ad campaign.</p>
            </div>

            <div class="bg-white rounded-2xl p-7 border border-black/5">
                <div class="w-10 h-10 rounded-xl bg-[#0071e3]/10 flex items-center justify-center mb-5">
                    <svg class="w-5 h-5 text-[#0071e3]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16"/></svg>
                </div>
                <h3 class="text-[16px] font-semibold text-[#1d1d1f] mb-2">Creative Production</h3>
                <p class="text-[14px] text-[#1d1d1f]/60 leading-relaxed">Scroll-stopping ad creatives and videos built for performance.</p>
            </div>

        </div>
    </div>
</section>

{{-- Results Section --}}
<section class="py-24">
    <div class="max-w-6xl mx-auto px-6 text-center">
        <h2 class="text-[13px] font-semibold text-[#0071e3] tracking-wide mb-3">Results</h2>
        <p class="text-[28px] md:text-[34px] font-bold text-[#1d1d1f] mb-16 tracking-tight">Numbers that matter</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div>
                <p class="text-[40px] font-bold text-[#1d1d1f] tracking-tight">3L+</p>
                <p class="text-[14px] text-[#1d1d1f]/60 mt-2">Monthly Sales Generated</p>
            </div>
            <div>
                <p class="text-[40px] font-bold text-[#1d1d1f] tracking-tight">10+</p>
                <p class="text-[14px] text-[#1d1d1f]/60 mt-2">Brands Scaled</p>
            </div>
            <div>
                <p class="text-[40px] font-bold text-[#1d1d1f] tracking-tight">4x</p>
                <p class="text-[14px] text-[#1d1d1f]/60 mt-2">Average ROAS Improvement</p>
            </div>
        </div>
    </div>
</section>

@endsection