<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'EcoStore') }}</title>

    @php
        $store = \App\Models\StoreSetting::first();
        $faviconUrl = $store?->logo_url ?? (file_exists(public_path('assets/img/logo.png')) ? asset('assets/img/logo.png') : asset('favicon.ico'));
    @endphp
    <!-- Dynamic Favicon -->
    <link rel="icon" id="dynamic-favicon" type="image/png" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="min-h-full flex flex-col font-sans text-slate-800 bg-slate-50 selection:bg-emerald-500 selection:text-white">
    @inertia
</body>
</html>
