{{-- resources/views/components/dashboard-layout.blade.php --}}
{{-- Clean dashboard layout without sidebar - Light mode only --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ \App\Models\WebsiteSetting::getSettings()->site_name }}</title>

    @php
        $settings = \App\Models\WebsiteSetting::getSettings();
    @endphp

    @if($settings->favicon)
        <link rel="icon" type="image/png" href="{{ $settings->favicon_url }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Tippy.js for vocabulary popups -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            min-height: 100vh;
        }

        [x-cloak] { display: none !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #C8102E; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #A00E27; }

        /* Smooth transitions */
        .transition-smooth { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        /* Card hover effect */
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(200, 16, 46, 0.15); }

        /* Progress ring */
        .progress-ring { transition: stroke-dashoffset 0.5s ease-out; }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #C8102E 0%, #8B0000 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Upgrade button pulse animation - smooth & subtle */
        .upgrade-btn-pulse {
            position: relative;
            animation: btn-breathe 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            overflow: visible;
        }

        .upgrade-btn-pulse::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 14px;
            background: linear-gradient(135deg, #C8102E, #ff4757, #C8102E);
            background-size: 200% 200%;
            animation: gradient-smooth 3s ease infinite;
            z-index: -1;
        }

        @keyframes gradient-smooth {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes btn-breathe {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(200, 16, 46, 0.4), 0 4px 12px rgba(200, 16, 46, 0.2);
            }
            50% {
                transform: scale(1.03);
                box-shadow: 0 0 0 8px rgba(200, 16, 46, 0), 0 6px 20px rgba(200, 16, 46, 0.35);
            }
        }

        /* Nav link active indicator */
        .nav-link-active {
            position: relative;
        }
        .nav-link-active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 3px;
            background: #C8102E;
            border-radius: 3px;
        }

        /* Mega dropdown animation */
        .mega-dropdown {
            transform-origin: top center;
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased" x-data="dashboardData()">
    @php
        $isOfflineStudent = auth()->check() && auth()->user()->isOfflineStudent();
    @endphp

    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('student.dashboard') }}" class="flex items-center space-x-3">
                        @if($settings->site_logo)
                            <img src="{{ $settings->logo_url }}" alt="{{ $settings->site_title }}" class="h-9 w-auto">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex items-center justify-center shadow-lg shadow-[#C8102E]/20">
                                <i class="fas fa-graduation-cap text-white text-lg"></i>
                            </div>
                            <span class="font-bold text-xl text-gray-900">{{ $settings->site_title ?? 'IELTS Journey' }}</span>
                        @endif
                    </a>
                </div>

                <!-- Desktop Navigation - Center (Hidden for offline students) -->
                @if(!$isOfflineStudent)
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('student.dashboard') }}"
                       class="relative px-4 py-2 rounded-lg text-sm font-semibold transition-smooth {{ request()->routeIs('student.dashboard') ? 'text-[#C8102E] bg-[#C8102E]/5 nav-link-active' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50' }}">
                        Dashboard
                    </a>

                    <!-- Practice Dropdown -->
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="px-4 py-2 rounded-lg text-sm font-semibold transition-smooth flex items-center gap-1.5 {{ request()->routeIs('student.listening.*') || request()->routeIs('student.reading.*') || request()->routeIs('student.writing.*') || request()->routeIs('student.speaking.*') || request()->routeIs('student.full-test.*') ? 'text-[#C8102E] bg-[#C8102E]/5' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50' }}">
                            <span>Mock Test</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Mega Dropdown -->
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="mega-dropdown absolute left-1/2 -translate-x-1/2 mt-2 w-[420px] bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">

                            <!-- Header -->
                            <div class="px-5 py-3 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Test Sections</p>
                            </div>

                            <!-- Grid Menu -->
                            <div class="p-3 grid grid-cols-2 gap-2">
                                <a href="{{ route('student.listening.index') }}"
                                   class="group flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition-smooth {{ request()->routeIs('student.listening.*') ? 'bg-blue-50 ring-1 ring-blue-200' : '' }}">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center transition-smooth">
                                        <i class="fas fa-headphones text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm">Listening</p>
                                        <p class="text-xs text-gray-500">30 min • 40 questions</p>
                                    </div>
                                </a>

                                <a href="{{ route('student.reading.index') }}"
                                   class="group flex items-center gap-3 p-3 rounded-xl hover:bg-emerald-50 transition-smooth {{ request()->routeIs('student.reading.*') ? 'bg-emerald-50 ring-1 ring-emerald-200' : '' }}">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 group-hover:bg-emerald-200 flex items-center justify-center transition-smooth">
                                        <i class="fas fa-book-reader text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm">Reading</p>
                                        <p class="text-xs text-gray-500">60 min • 40 questions</p>
                                    </div>
                                </a>

                                <a href="{{ route('student.writing.index') }}"
                                   class="group flex items-center gap-3 p-3 rounded-xl hover:bg-violet-50 transition-smooth {{ request()->routeIs('student.writing.*') ? 'bg-violet-50 ring-1 ring-violet-200' : '' }}">
                                    <div class="w-10 h-10 rounded-xl bg-violet-100 group-hover:bg-violet-200 flex items-center justify-center transition-smooth">
                                        <i class="fas fa-pen-nib text-violet-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm">Writing</p>
                                        <p class="text-xs text-gray-500">60 min • 2 tasks</p>
                                    </div>
                                </a>

                                <a href="{{ route('student.speaking.index') }}"
                                   class="group flex items-center gap-3 p-3 rounded-xl hover:bg-orange-50 transition-smooth {{ request()->routeIs('student.speaking.*') ? 'bg-orange-50 ring-1 ring-orange-200' : '' }}">
                                    <div class="w-10 h-10 rounded-xl bg-orange-100 group-hover:bg-orange-200 flex items-center justify-center transition-smooth">
                                        <i class="fas fa-comment-dots text-orange-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm">Speaking</p>
                                        <p class="text-xs text-gray-500">15 min • 3 parts</p>
                                    </div>
                                </a>
                            </div>

                            <!-- Full Test Link -->
                            <div class="p-3 bg-gradient-to-r from-[#C8102E]/5 to-transparent border-t border-gray-100">
                                <a href="{{ route('student.full-test.index') }}"
                                   class="group flex items-center gap-3 p-3 rounded-xl hover:bg-[#C8102E]/10 transition-smooth {{ request()->routeIs('student.full-test.*') ? 'bg-[#C8102E]/10 ring-1 ring-[#C8102E]/20' : '' }}">
                                    <div class="w-10 h-10 rounded-xl bg-[#C8102E]/10 group-hover:bg-[#C8102E]/20 flex items-center justify-center transition-smooth">
                                        <i class="fas fa-layer-group text-[#C8102E]"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-800 text-sm">Full Mock Test</p>
                                        <p class="text-xs text-gray-500">Complete IELTS simulation</p>
                                    </div>
                                    <i class="fas fa-arrow-right text-[#C8102E] opacity-0 group-hover:opacity-100 transition-smooth"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('student.results') }}"
                       class="relative px-4 py-2 rounded-lg text-sm font-semibold transition-smooth {{ request()->routeIs('student.results*') ? 'text-[#C8102E] bg-[#C8102E]/5 nav-link-active' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50' }}">
                        Results
                    </a>

                    <!-- Self Practice Dropdown -->
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="px-4 py-2 rounded-lg text-sm font-semibold transition-smooth flex items-center gap-1.5 {{ request()->routeIs('student.writing-practice.*') ? 'text-[#C8102E] bg-[#C8102E]/5' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50' }}">
                            <span>Self Practice</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Mega Dropdown -->
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="mega-dropdown absolute left-1/2 -translate-x-1/2 mt-2 w-[420px] bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">

                            <!-- Header -->
                            <div class="px-5 py-3 bg-gradient-to-r from-violet-50 to-white border-b border-gray-100">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Question-wise Practice</p>
                            </div>

                            <!-- Grid Menu -->
                            <div class="p-3 grid grid-cols-1 gap-2">
                                <!-- Writing Practice -->
                                <div class="space-y-1">
                                    <div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase">Writing</div>
                                    <a href="{{ route('student.writing-practice.task1') }}"
                                       class="group flex items-center gap-3 p-3 rounded-xl hover:bg-violet-50 transition-smooth {{ request()->routeIs('student.writing-practice.task1') ? 'bg-violet-50 ring-1 ring-violet-200' : '' }}">
                                        <div class="w-10 h-10 rounded-xl bg-violet-100 group-hover:bg-violet-200 flex items-center justify-center transition-smooth">
                                            <i class="fas fa-chart-bar text-violet-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">Writing Task 1</p>
                                            <p class="text-xs text-gray-500">Charts, graphs & diagrams</p>
                                        </div>
                                    </a>

                                    <a href="{{ route('student.writing-practice.task2') }}"
                                       class="group flex items-center gap-3 p-3 rounded-xl hover:bg-violet-50 transition-smooth {{ request()->routeIs('student.writing-practice.task2') ? 'bg-violet-50 ring-1 ring-violet-200' : '' }}">
                                        <div class="w-10 h-10 rounded-xl bg-violet-100 group-hover:bg-violet-200 flex items-center justify-center transition-smooth">
                                            <i class="fas fa-pen-fancy text-violet-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">Writing Task 2</p>
                                            <p class="text-xs text-gray-500">Essay writing practice</p>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <!-- Coming Soon -->
                            <div class="p-3 bg-gradient-to-r from-gray-50/50 to-transparent border-t border-gray-100">
                                <div class="text-center py-2">
                                    <p class="text-xs text-gray-400">
                                        <i class="fas fa-clock mr-1"></i>
                                        More sections coming soon
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('student.ai-tutor.index') }}"
                       class="relative px-4 py-2 rounded-lg text-sm font-semibold transition-smooth flex items-center gap-1.5 {{ request()->routeIs('student.ai-tutor.*') ? 'text-[#C8102E] bg-[#C8102E]/5 nav-link-active' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50' }}">
                        AI Tutor
                        <span class="px-1.5 py-0.5 bg-emerald-500 text-white text-[9px] font-bold rounded uppercase">New</span>
                    </a>

                </div>
                @endif

                <!-- Right Section -->
                <div class="flex items-center space-x-2">
                    @if(!$isOfflineStudent)
                    <!-- Notifications -->
                    <div class="relative" @click.outside="notificationOpen = false">
                        <button @click="notificationOpen = !notificationOpen"
                                class="relative w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-smooth">
                            <i class="fas fa-bell text-lg"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-[#C8102E] rounded-full flex items-center justify-center text-[10px] font-bold text-white ring-2 ring-white">
                                    {{ min(auth()->user()->unreadNotifications->count(), 9) }}{{ auth()->user()->unreadNotifications->count() > 9 ? '+' : '' }}
                                </span>
                            @endif
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="notificationOpen" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                            <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                                <h3 class="font-bold text-gray-800">Notifications</h3>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="px-2 py-0.5 bg-[#C8102E]/10 text-[#C8102E] text-xs font-bold rounded-full">
                                        {{ auth()->user()->unreadNotifications->count() }} new
                                    </span>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <a href="{{ route('notifications.show', $notification->id) }}" class="block p-4 hover:bg-gray-50 border-b border-gray-50 transition-smooth">
                                        <div class="flex gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-[#C8102E]/10 flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-bell text-[#C8102E] text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-800">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-8 text-center">
                                        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-bell-slash text-2xl text-gray-300"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm font-medium">No new notifications</p>
                                    </div>
                                @endforelse
                            </div>
                            @if(auth()->user()->unreadNotifications->count() > 5)
                                <div class="p-3 border-t border-gray-100 bg-gray-50">
                                    <a href="{{ route('notifications.index') }}" class="block text-center text-sm font-semibold text-[#C8102E] hover:underline">
                                        View all notifications
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif {{-- End of !$isOfflineStudent for notifications --}}

                    @if(!$isOfflineStudent)
                    <!-- Profile Dropdown -->
                    <div class="relative" @click.outside="profileOpen = false">
                        <button @click="profileOpen = !profileOpen" class="flex items-center gap-1.5 p-1 pr-2 rounded-xl hover:bg-gray-100 transition-smooth">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-lg object-cover ring-2 ring-gray-200">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex items-center justify-center text-white font-bold text-sm shadow ring-2 ring-gray-200">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                            <i class="fas fa-chevron-down text-gray-400 text-[10px] transition-transform" :class="profileOpen ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Profile Menu -->
                        <div x-show="profileOpen" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                            <!-- User Info Header -->
                            <div class="p-4 bg-gradient-to-br from-gray-50 via-white to-gray-50 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-14 h-14 rounded-xl object-cover ring-2 ring-white shadow-lg">
                                    @else
                                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex items-center justify-center text-white font-bold text-xl ring-2 ring-white shadow-lg">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Menu Items -->
                            <div class="p-2">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-smooth group">
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 group-hover:bg-[#C8102E]/10 flex items-center justify-center transition-smooth">
                                        <i class="fas fa-user text-gray-500 group-hover:text-[#C8102E] transition-smooth"></i>
                                    </div>
                                    <span>My Profile</span>
                                </a>
                                <a href="{{ route('student.results') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-smooth group">
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 group-hover:bg-[#C8102E]/10 flex items-center justify-center transition-smooth">
                                        <i class="fas fa-history text-gray-500 group-hover:text-[#C8102E] transition-smooth"></i>
                                    </div>
                                    <span>Test History</span>
                                </a>
                            </div>

                            <!-- Logout -->
                            <div class="p-2 border-t border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl transition-smooth group">
                                        <div class="w-9 h-9 rounded-lg bg-red-50 group-hover:bg-red-100 flex items-center justify-center transition-smooth">
                                            <i class="fas fa-sign-out-alt text-red-500"></i>
                                        </div>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif {{-- End of !$isOfflineStudent for profile dropdown --}}

                    @if(!$isOfflineStudent)
                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-smooth">
                        <i class="fas text-lg" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>

        @if(!$isOfflineStudent)
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden border-t border-gray-200 bg-white shadow-lg">
            <div class="px-4 py-4 space-y-2">
                <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100 {{ request()->routeIs('student.dashboard') ? 'bg-[#C8102E]/5 text-[#C8102E]' : '' }}">
                    <i class="fas fa-home w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <!-- Practice Section (Mobile) -->
                <div x-data="{ practiceOpen: {{ request()->routeIs('student.listening.*') || request()->routeIs('student.reading.*') || request()->routeIs('student.writing.*') || request()->routeIs('student.speaking.*') || request()->routeIs('student.full-test.*') ? 'true' : 'false' }} }">
                    <button @click="practiceOpen = !practiceOpen" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100 {{ request()->routeIs('student.listening.*') || request()->routeIs('student.reading.*') || request()->routeIs('student.writing.*') || request()->routeIs('student.speaking.*') || request()->routeIs('student.full-test.*') ? 'bg-[#C8102E]/5 text-[#C8102E]' : '' }}">
                        <i class="fas fa-graduation-cap w-5 {{ request()->routeIs('student.listening.*') || request()->routeIs('student.reading.*') || request()->routeIs('student.writing.*') || request()->routeIs('student.speaking.*') || request()->routeIs('student.full-test.*') ? 'text-[#C8102E]' : 'text-gray-500' }}"></i>
                        <span class="font-medium">Mock Test</span>
                        <i class="fas fa-chevron-down ml-auto text-gray-400 text-xs transition-transform duration-200" :class="practiceOpen ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="practiceOpen" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-gray-200 pl-4">
                        <a href="{{ route('student.listening.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 {{ request()->routeIs('student.listening.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-headphones text-blue-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium">Listening</span>
                        </a>
                        <a href="{{ route('student.reading.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 {{ request()->routeIs('student.reading.*') ? 'bg-emerald-50 text-emerald-600' : '' }}">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <i class="fas fa-book-reader text-emerald-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium">Reading</span>
                        </a>
                        <a href="{{ route('student.writing.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-violet-50 hover:text-violet-600 {{ request()->routeIs('student.writing.*') ? 'bg-violet-50 text-violet-600' : '' }}">
                            <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center">
                                <i class="fas fa-pen-nib text-violet-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium">Writing</span>
                        </a>
                        <a href="{{ route('student.speaking.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-orange-50 hover:text-orange-600 {{ request()->routeIs('student.speaking.*') ? 'bg-orange-50 text-orange-600' : '' }}">
                            <div class="w-7 h-7 rounded-lg bg-orange-100 flex items-center justify-center">
                                <i class="fas fa-comment-dots text-orange-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium">Speaking</span>
                        </a>
                        <a href="{{ route('student.full-test.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-[#C8102E]/5 hover:text-[#C8102E] {{ request()->routeIs('student.full-test.*') ? 'bg-[#C8102E]/5 text-[#C8102E]' : '' }}">
                            <div class="w-7 h-7 rounded-lg bg-[#C8102E]/10 flex items-center justify-center">
                                <i class="fas fa-layer-group text-[#C8102E] text-xs"></i>
                            </div>
                            <span class="text-sm font-medium">Full Test</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('student.results') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100 {{ request()->routeIs('student.results*') ? 'bg-[#C8102E]/5 text-[#C8102E]' : '' }}">
                    <i class="fas fa-chart-bar w-5 {{ request()->routeIs('student.results*') ? 'text-[#C8102E]' : 'text-gray-500' }}"></i>
                    <span class="font-medium">Results</span>
                </a>

                <!-- Self Practice Section (Mobile) -->
                <div x-data="{ practiceOpen: {{ request()->routeIs('student.writing-practice.*') ? 'true' : 'false' }} }">
                    <button @click="practiceOpen = !practiceOpen" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-violet-50 {{ request()->routeIs('student.writing-practice.*') ? 'bg-violet-50 text-violet-600' : '' }}">
                        <i class="fas fa-dumbbell text-violet-500 w-5 {{ request()->routeIs('student.writing-practice.*') ? 'text-violet-600' : '' }}"></i>
                        <span class="font-medium">Self Practice</span>
                        <i class="fas fa-chevron-down ml-auto text-gray-400 text-xs transition-transform duration-200" :class="practiceOpen ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="practiceOpen" x-collapse class="mt-1 ml-4 space-y-1 border-l-2 border-violet-200 pl-4">
                        <a href="{{ route('student.writing-practice.task1') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-violet-50 hover:text-violet-600 {{ request()->routeIs('student.writing-practice.task1') ? 'bg-violet-50 text-violet-600' : '' }}">
                            <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center">
                                <i class="fas fa-chart-bar text-violet-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium">Writing Task 1</span>
                        </a>
                        <a href="{{ route('student.writing-practice.task2') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-violet-50 hover:text-violet-600 {{ request()->routeIs('student.writing-practice.task2') ? 'bg-violet-50 text-violet-600' : '' }}">
                            <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center">
                                <i class="fas fa-pen-fancy text-violet-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium">Writing Task 2</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('student.ai-tutor.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-emerald-50 {{ request()->routeIs('student.ai-tutor.*') ? 'bg-emerald-50 text-emerald-600' : '' }}">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <i class="fas fa-robot text-emerald-600 text-sm"></i>
                    </div>
                    <span class="font-medium">AI Tutor</span>
                    <span class="ml-auto px-1.5 py-0.5 bg-emerald-500 text-white text-[9px] font-bold rounded uppercase">New</span>
                </a>

            </div>
        </div>
        @endif {{-- End of !$isOfflineStudent for mobile menu --}}
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <!-- Copyright (Left) -->
                <p class="text-gray-600 text-sm font-medium">
                    {{ $settings->copyright_text ?? '© ' . date('Y') . ' ' . ($settings->site_name ?? 'IELTS Journey') . '. All rights reserved.' }}
                </p>

                <!-- Footer Links (Right) -->
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                    <a href="/privacy-policy" class="text-sm font-medium text-gray-600 hover:text-[#C8102E] transition-smooth">Privacy</a>
                    <a href="/terms-of-service" class="text-sm font-medium text-gray-600 hover:text-[#C8102E] transition-smooth">Terms</a>
                    <a href="/contact" class="text-sm font-medium text-gray-600 hover:text-[#C8102E] transition-smooth">Contact</a>
                    <a href="/help-center" class="text-sm font-medium text-gray-600 hover:text-[#C8102E] transition-smooth">Help</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notifications -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed bottom-4 right-4 z-50 bg-white rounded-2xl shadow-2xl border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="fas fa-check text-green-600"></i>
            </div>
            <p class="text-gray-800 text-sm font-medium">{{ session('success') }}</p>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 ml-2">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @stack('scripts')

    <script>
        function dashboardData() {
            return {
                mobileMenuOpen: false,
                profileOpen: false,
                notificationOpen: false
            }
        }
    </script>
</body>
</html>