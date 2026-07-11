<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2EDLFCQKLH"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-2EDLFCQKLH');
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
        $settings = \App\Models\WebsiteSetting::first();
        $siteName = $settings ? $settings->site_title : 'CD IELTS';
        $favicon = $settings && $settings->favicon_path ? Storage::url($settings->favicon_path) : null;
    @endphp

    <title>{{ $siteName }} - {{ $title ?? 'IELTS Mock Test Platform' }}</title>
    
    <!-- Favicon -->
    @if($favicon)
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Crimson Theme Styles -->
    <style>
        :root {
            --crimson-50: #fef2f2;
            --crimson-100: #fee2e2;
            --crimson-200: #fecaca;
            --crimson-300: #fca5a5;
            --crimson-400: #f87171;
            --crimson-500: #ef4444;
            --crimson-600: #dc2626;
            --crimson-700: #b91c1c;
            --crimson-800: #991b1b;
            --crimson-900: #7f1d1d;
        }
        
        .bg-crimson-50 { background-color: var(--crimson-50); }
        .bg-crimson-100 { background-color: var(--crimson-100); }
        .bg-crimson-500 { background-color: var(--crimson-500); }
        .bg-crimson-600 { background-color: var(--crimson-600); }
        .bg-crimson-700 { background-color: var(--crimson-700); }
        .bg-crimson-800 { background-color: var(--crimson-800); }
        .bg-crimson-900 { background-color: var(--crimson-900); }
        
        .text-crimson-500 { color: var(--crimson-500); }
        .text-crimson-600 { color: var(--crimson-600); }
        .text-crimson-700 { color: var(--crimson-700); }
        
        .border-crimson-500 { border-color: var(--crimson-500); }
        .border-crimson-600 { border-color: var(--crimson-600); }
        
        /* Glassmorphism Effects */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glass-dark {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
    
    @stack('styles')
</head>
<body class="font-sans antialiased" x-data="{ open: false }">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-gray-900 text-white mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Logo & Description -->
                    <div class="col-span-1 md:col-span-2">
                        @php
                            $settings = \App\Models\WebsiteSetting::first();
                            $logo = $settings && $settings->dark_mode_logo_path ? Storage::url($settings->dark_mode_logo_path) : 
                                   ($settings && $settings->logo_path ? Storage::url($settings->logo_path) : null);
                            $siteName = $settings ? $settings->site_title : 'CD IELTS';
                        @endphp
                        
                        @if($logo)
                            <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-10 w-auto mb-4">
                        @else
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-xl">CD</span>
                                </div>
                                <span class="text-white font-bold text-2xl">{{ $siteName }}</span>
                            </div>
                        @endif
                        
                        <p class="text-gray-400 max-w-md">
                            Your trusted partner for IELTS preparation. Master all four modules with our AI-powered platform.
                        </p>
                        
                        <!-- Social Links -->
                        <div class="flex space-x-4 mt-6">
                            <a href="#" class="text-gray-400 hover:text-white transition">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="{{ route('student.listening.index') }}" class="text-gray-400 hover:text-white transition">Listening Test</a></li>
                            <li><a href="{{ route('student.reading.index') }}" class="text-gray-400 hover:text-white transition">Reading Test</a></li>
                            <li><a href="{{ route('student.writing.index') }}" class="text-gray-400 hover:text-white transition">Writing Test</a></li>
                            <li><a href="{{ route('student.speaking.index') }}" class="text-gray-400 hover:text-white transition">Speaking Test</a></li>
                        </ul>
                    </div>
                    
                    <!-- Support -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Support</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white transition">Help Center</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Bottom Bar -->
                <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400">
                    <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
    
    @stack('scripts')
</body>
</html>