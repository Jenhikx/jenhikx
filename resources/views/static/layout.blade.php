<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'JENHIKX — Performance Marketing Agency for Shopify Brands')</title>
    <meta name="description" content="@yield('description', 'JENHIKX helps Shopify brands scale profitably with Google Ads, Meta Ads, premium store design and conversion-focused creatives.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="JENHIKX">
    <meta property="og:title" content="@yield('title', 'JENHIKX — Performance Marketing Agency')">
    <meta property="og:description" content="@yield('description', 'Google Ads, Meta Ads, Shopify store design & creatives — built to scale profitable D2C brands.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
    </style>
</head>
<body class="bg-white text-[#1d1d1f] antialiased">

    @include('static.partials.header')

    <main>
        @yield('content')
    </main>

    @include('static.partials.footer')

</body>
</html>