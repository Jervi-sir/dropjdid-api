<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- SEO Metadata -->
        <meta name="description" content="Dropjdid is a modern drop shipping and logistics management platform designed to streamline e-commerce fulfillment and operations.">
        <meta name="keywords" content="drop shipping, logistics, e-commerce, fulfillment, supply chain, dropjdid">
        <meta name="robots" content="index, follow">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://dropjdid.com">
        <meta property="og:title" content="Dropjdid - Modern Drop Shipping & Logistics Platform">
        <meta property="og:description" content="Streamline your e-commerce fulfillment and operations with Dropjdid's professional drop shipping and logistics platform.">
        <meta property="og:image" content="https://dropjdid.com/og-image.png">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="https://dropjdid.com">
        <meta property="twitter:title" content="Dropjdid - Modern Drop Shipping & Logistics Platform">
        <meta property="twitter:description" content="Streamline your e-commerce fulfillment and operations with Dropjdid's professional drop shipping and logistics platform.">
        <meta property="twitter:image" content="https://dropjdid.com/og-image.png">

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Dropjdid') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
