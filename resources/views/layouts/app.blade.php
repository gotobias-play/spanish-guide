<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <header class="bg-white/80 backdrop-blur-lg shadow-sm sticky top-0 z-10">
                <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
                    <a href="/" class="auth-btn text-sm md:text-base px-3 py-2 rounded-lg">Back to Home</a>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Profile') }}
                    </h2>
                </nav>
            </header>

            <!-- Page Content -->
            <main class="container mx-auto p-4 md:p-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>