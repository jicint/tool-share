<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}">
                                {{ config('app.name', 'Laravel') }}
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center">
                        @auth
                            <div class="ml-3 relative">
                                <div>
                                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="ml-4">
                                @csrf
                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                    Log Out
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main>
            {{ $slot }}
        </main>
    </div>
</body>
</html> 