<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $settings = \App\Models\WebsiteSetting::first();
        $siteName = $settings ? $settings->site_title : 'CD IELTS';
        $favicon = $settings && $settings->favicon_path ? Storage::url($settings->favicon_path) : null;
        $logo = $settings && $settings->logo_path ? Storage::url($settings->logo_path) : null;
    @endphp

    <title>@yield('title', 'Dashboard') - {{ $siteName }}</title>

    @if($favicon)
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand': {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#C8102E',
                            600: '#B00D28',
                            700: '#9A0B22',
                            800: '#7F091C',
                            900: '#650716',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                {{-- Logo --}}
                <div class="flex items-center gap-2.5">
                    @if($logo)
                        <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-7 w-auto">
                    @else
                        <div class="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">CD</span>
                        </div>
                    @endif
                    <span class="text-sm font-semibold text-gray-900 hidden sm:block">{{ $siteName }}</span>
                </div>

                {{-- Nav --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('student.results') }}" class="text-gray-500 hover:text-gray-800 transition text-sm font-medium flex items-center gap-1.5">
                        <i class="fas fa-chart-bar text-xs"></i>
                        <span class="hidden sm:inline">Results</span>
                    </a>
                    <div class="w-px h-5 bg-gray-200"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center">
                            <span class="text-gray-600 font-semibold text-[11px]">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        </div>
                        <span class="text-sm font-medium text-gray-700 hidden sm:block max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-500 transition p-1.5" title="Logout">
                            <i class="fas fa-sign-out-alt text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Main --}}
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 py-6">
        @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-5 text-sm">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="py-4 text-center">
        <p class="text-xs text-gray-400">&copy; {{ date('Y') }} {{ $siteName }}</p>
    </footer>

    @stack('scripts')
</body>
</html>
