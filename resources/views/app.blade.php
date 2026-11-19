@php($seo = \App\Support\Seo::for(request()))
<!DOCTYPE html>
<html lang="en-GB">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Consent Mode: analytics and ads stay off until the visitor accepts (CookieBanner.vue).
             Must run before Google Tag Manager. --}}
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('consent', 'default', {
                ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied',
                analytics_storage: 'denied', functionality_storage: 'granted', security_storage: 'granted',
                wait_for_update: 500
            });
            try {
                if (localStorage.getItem('imagesafe-cookie-consent') === 'accepted') {
                    gtag('consent', 'update', { analytics_storage: 'granted' });
                }
            } catch (e) {}
        </script>
        @if (config('seo.gtm_id'))
        {{-- Google Tag Manager --}}
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ config('seo.gtm_id') }}');</script>
        @endif

        <title>{{ $seo['title'] }}</title>
        <meta name="description" content="{{ $seo['description'] }}">
        <meta name="robots" content="{{ $seo['robots'] }}">
        <link rel="canonical" href="{{ $seo['canonical'] }}">

        {{-- Link previews (Open Graph / Twitter): always ImageSafe. --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $seo['site_name'] }}">
        <meta property="og:locale" content="en_GB">
        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
        <meta property="og:url" content="{{ $seo['canonical'] }}">
        <meta property="og:image" content="{{ $seo['image'] }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ $seo['image_alt'] }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="{{ $seo['twitter'] }}">
        <meta name="twitter:title" content="{{ $seo['title'] }}">
        <meta name="twitter:description" content="{{ $seo['description'] }}">
        <meta name="twitter:image" content="{{ $seo['image'] }}">
        <meta name="twitter:image:alt" content="{{ $seo['image_alt'] }}">

        {{-- Icons --}}
        <link rel="icon" href="/favicon.ico" sizes="32x32">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">
        <meta name="theme-color" content="{{ $seo['theme_color'] }}">

        @if (request()->routeIs('home'))
        <script type="application/ld+json">{!! json_encode(\App\Support\Seo::structuredData(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
        @endif

        <link rel="preconnect" href="https://api.fontshare.com">
        <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="antialiased">
        @if (config('seo.gtm_id'))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('seo.gtm_id') }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        @endif
        @inertia
    </body>
</html>
