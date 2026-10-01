<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — JENHIKX</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

<div style="background-color: #dbeafe; background-image: radial-gradient(at 10% 10%, #bae6fd 0px, transparent 50%), radial-gradient(at 90% 10%, #c7d2fe 0px, transparent 50%), radial-gradient(at 50% 90%, #e0e7ff 0px, transparent 50%), radial-gradient(at 90% 90%, #93c5fd 0px, transparent 50%), radial-gradient(at 10% 90%, #dd72e333 0px, transparent 50%); min-height: 100vh; width: 100%; display: flex; flex-direction: column; justify-content: space-between; padding: 1.5rem; position: relative; overflow: hidden;">

    <!-- Background Ambient Rings -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[650px] h-[650px] border border-white/50 rounded-full pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[950px] h-[950px] border border-white/30 rounded-full pointer-events-none"></div>

    <!-- TOP BAR: Brand Logo -->
    <div class="relative z-10 w-full max-w-7xl mx-auto flex items-center">
        <div class="flex items-center space-x-2.5 bg-white/70 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/90 shadow-sm">
            <div class="h-8 w-8 rounded-xl bg-slate-900 flex items-center justify-center shadow-md">
                <span class="text-white font-extrabold text-xs tracking-wider">JX</span>
            </div>
            <span class="text-base font-extrabold tracking-tight text-slate-900">
                JENHIK<span class="text-indigo-600">X</span>
            </span>
        </div>
    </div>

    <!-- CENTER: Floating Glass Card -->
    <div class="relative z-10 my-auto w-full max-w-[420px] mx-auto py-6">
        <div style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1.5px solid rgba(255, 255, 255, 0.9); box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.08); border-radius: 32px; padding: 2.25rem;">

            <!-- Card Icon Header -->
            <div class="flex justify-center mb-6">
                <div class="h-12 w-12 rounded-2xl bg-white border border-slate-200/80 flex items-center justify-center shadow-sm text-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                </div>
            </div>

            <!-- Header Title -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Sign in with email
                </h2>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
                    Access your performance ads, Shopify analytics &amp; campaign leads in one workspace.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-6 text-sm font-medium text-green-700 text-center">{{ session('status') }}</div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                @csrf

                <!-- Email Field -->
                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                            </svg>
                        </div>
                        <input
                            id="email"
                            class="block w-full text-sm py-3.5 pl-11 pr-4 rounded-2xl border border-slate-200/80 bg-white/80 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none transition-all duration-200"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Email">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                            </svg>
                        </div>
                        <input
                            id="password"
                            class="block w-full text-sm py-3.5 pl-11 pr-4 rounded-2xl border border-slate-200/80 bg-white/80 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none transition-all duration-200"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Password">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Forgot Password Link -->
                <div class="flex justify-end pt-1">
                    <a href="#" class="text-xs font-semibold text-slate-600 hover:text-indigo-600 transition-colors">
                        Forgot password?
                    </a>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full justify-center py-3.5 bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-semibold text-xs tracking-wider uppercase rounded-2xl transition-all duration-200 shadow-lg shadow-slate-900/10 cursor-pointer">
                        Sign in
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- FOOTER -->
    <div class="relative z-10 w-full text-center py-2 text-xs font-medium text-slate-600">
        &copy; {{ date('Y') }} JENHIKX. All rights reserved.
    </div>

</div>

</body>
</html>