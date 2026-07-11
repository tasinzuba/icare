{{-- resources/views/components/teacher-layout.blade.php --}}
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

    <title>{{ $title ?? 'Teacher Dashboard' }} - {{ \App\Models\WebsiteSetting::getSettings()->site_name }}</title>

    @php
        $settings = \App\Models\WebsiteSetting::getSettings();
    @endphp
    
    <!-- Favicon -->
    @if($settings->favicon)
        <link rel="icon" type="image/png" href="{{ $settings->favicon_url }}">
    @endif
    
    <!-- Meta Tags -->
    @if($settings->meta_tags)
        @if($settings->meta_tags['description'] ?? null)
            <meta name="description" content="{{ $settings->meta_tags['description'] }}">
        @endif
        @if($settings->meta_tags['keywords'] ?? null)
            <meta name="keywords" content="{{ $settings->meta_tags['keywords'] }}">
        @endif
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer></script>
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #f8fafc;
        }
        
        [x-cloak] { display: none !important; }
        
        /* Light Mode Glass Effect */
        .glass-light {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        /* Card Shadows */
        .card-shadow {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
        }
        
        .card-shadow-hover:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        /* Emerald Gradient for Teacher Theme */
        .emerald-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .emerald-gradient-subtle {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }
        
        /* Custom Scrollbar - Light */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Active Navigation Item */
        .nav-active {
            background: #ecfdf5;
            border-left: 3px solid #10b981;
        }
        
        /* Notification Badge Pulse */
        @keyframes pulse-emerald {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
        
        .pulse-emerald {
            animation: pulse-emerald 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
    
    @stack('styles')
</head>
<body class="antialiased bg-gray-50">
    <div x-data="layoutData()" 
         class="relative z-10 flex h-screen bg-gray-50 overflow-hidden">
        
        <!-- Mobile Menu Overlay -->
        <div x-show="sidebarOpen" 
             x-cloak
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 backdrop-blur-sm lg:hidden"></div>

        <!-- Teacher Sidebar -->
        <div :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
             class="fixed inset-y-0 left-0 z-50 w-72 h-full bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:h-screen flex flex-col flex-shrink-0 shadow-sm">
            
            <!-- Logo Section -->
            <div class="p-6 border-b border-gray-200 bg-white">
                <div class="flex items-center justify-between">
                    <a href="{{ route('teacher.dashboard') }}" class="flex items-center">
                        @if($settings->site_logo)
                            <img src="{{ $settings->logo_url }}" alt="{{ $settings->site_title }}" class="h-12 w-auto">
                        @else
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-xl emerald-gradient flex items-center justify-center shadow-md">
                                    <i class="fas fa-chalkboard-teacher text-white text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">Teacher Portal</h2>
                                    <p class="text-xs text-gray-500">{{ $settings->site_title }}</p>
                                </div>
                            </div>
                        @endif
                    </a>
                    <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Teacher Stats -->
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                @php
                    $teacher = \App\Models\Teacher::where('user_id', auth()->id())->first();
                @endphp
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" 
                                 class="w-16 h-16 rounded-xl object-cover border-2 border-emerald-400 shadow-sm">
                        @else
                            <div class="w-16 h-16 rounded-xl emerald-gradient flex items-center justify-center text-white font-bold text-xl shadow-md">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                        @if($teacher && $teacher->is_available)
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white shadow-sm"></div>
                        @else
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-gray-400 rounded-full border-2 border-white shadow-sm"></div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h3 class="text-gray-900 font-semibold">{{ auth()->user()->name }}</h3>
                        <div class="flex items-center space-x-2 mt-1">
                            @if($teacher)
                                <div class="flex items-center">
                                    <span class="text-yellow-500">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $teacher->rating ? '' : 'opacity-30' }} text-xs"></i>
                                        @endfor
                                    </span>
                                    <span class="text-xs text-gray-600 ml-1">{{ number_format($teacher->rating, 1) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                @if($teacher)
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                        <p class="text-xs text-gray-600 font-medium">Completed</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">{{ $teacher->total_evaluations_done }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center border border-gray-200 shadow-sm">
                        <p class="text-xs text-gray-600 font-medium">Avg Time</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">{{ $teacher->average_turnaround_hours }}h</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto py-4 bg-white">
                <!-- Main Section -->
                <div class="px-4 mb-6">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-4">Main</h4>
                    
                    <a href="{{ route('teacher.dashboard') }}"
                       class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 mb-2
                              {{ request()->routeIs('teacher.dashboard') ? 'nav-active shadow-sm' : 'hover:bg-gray-50' }}">
                        <div class="w-10 h-10 rounded-lg emerald-gradient flex items-center justify-center shadow-sm">
                            <i class="fas fa-home text-white"></i>
                        </div>
                        <span class="text-gray-900 font-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('teacher.student-results.index') }}"
                       class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 mb-2
                              {{ request()->routeIs('teacher.student-results.*') ? 'nav-active shadow-sm' : 'hover:bg-gray-50' }}">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center shadow-sm">
                            <i class="fas fa-clipboard-list text-white"></i>
                        </div>
                        <span class="text-gray-900 font-medium">Student Results</span>
                    </a>

                    @php
                        // Count unassigned offline evaluations this teacher can claim
                        $unassignedOfflineCount = 0;
                        if ($teacher) {
                            $unassignedOfflineCount = \App\Models\HumanEvaluationRequest::whereNull('teacher_id')
                                ->where('status', 'pending')
                                ->where('is_offline_request', true)
                                ->whereHas('studentAttempt.testSet.section', function ($q) use ($teacher) {
                                    $specializations = $teacher->specialization ?? [];
                                    $q->whereIn('name', $specializations);
                                })
                                ->count();
                        }
                    @endphp

                    <!-- Offline Queue (Unassigned Branch Evaluations) -->
                    <a href="{{ route('teacher.evaluations.pending') }}"
                       class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 mb-2 {{ $unassignedOfflineCount > 0 ? 'bg-orange-50 border border-orange-200' : 'hover:bg-orange-50' }}">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center shadow-sm">
                            <i class="fas fa-building text-white"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-gray-900 font-medium block">Offline Queue</span>
                            <span class="text-xs text-gray-500">Branch evaluations to claim</span>
                        </div>
                        @if($unassignedOfflineCount > 0)
                            <span class="px-2 py-1 bg-orange-500 text-white text-xs rounded-full shadow-sm pulse-emerald">
                                {{ $unassignedOfflineCount }}
                            </span>
                        @endif
                    </a>

                    <!-- My Pending (Assigned to me) -->
                    <a href="{{ route('teacher.evaluations.pending') }}"
                       class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 mb-2
                              {{ request()->routeIs('teacher.evaluations.pending') ? 'nav-active shadow-sm' : 'hover:bg-gray-50' }}">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-yellow-500 flex items-center justify-center shadow-sm">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-gray-900 font-medium block">My Pending</span>
                            <span class="text-xs text-gray-500">Assigned to me</span>
                        </div>
                        @if($teacher && $teacher->evaluationRequests()->whereIn('status', ['assigned', 'in_progress'])->count() > 0)
                            <span class="px-2 py-1 bg-amber-500 text-white text-xs rounded-full shadow-sm pulse-emerald">
                                {{ $teacher->evaluationRequests()->whereIn('status', ['assigned', 'in_progress'])->count() }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('teacher.evaluations.completed') }}" 
                       class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 mb-2
                              {{ request()->routeIs('teacher.evaluations.completed') ? 'nav-active shadow-sm' : 'hover:bg-gray-50' }}">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center shadow-sm">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                        <span class="text-gray-900 font-medium">Completed</span>
                    </a>
                </div>

                <!-- Resources -->
                <div class="px-4 mb-6">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-4">Resources</h4>
                    
                    <a href="#" class="group flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-50 transition-all duration-200 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-sm">
                            <i class="fas fa-book text-white"></i>
                        </div>
                        <span class="text-gray-900 font-medium">Evaluation Guide</span>
                    </a>

                    <a href="#" class="group flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-50 transition-all duration-200 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-sm">
                            <i class="fas fa-chart-bar text-white"></i>
                        </div>
                        <span class="text-gray-900 font-medium">Statistics</span>
                    </a>
                </div>

                <!-- Settings -->
                <div class="px-4 mb-6">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-4">Settings</h4>
                    
                    <a href="{{ route('teacher.profile.edit') }}" class="group flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-gray-50 transition-all duration-200 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center shadow-sm">
                            <i class="fas fa-user-cog text-white"></i>
                        </div>
                        <span class="text-gray-900 font-medium">Profile Settings</span>
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                @if($teacher)
                <div class="bg-white rounded-xl p-4 mb-4 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-gray-600 font-medium">Availability Status</span>
                        <form action="{{ route('teacher.toggle-availability') }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                                {{ $teacher->is_available ? 'bg-emerald-600' : 'bg-gray-300' }}">
                                <span class="sr-only">Toggle availability</span>
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm
                                    {{ $teacher->is_available ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </div>
                    <p class="text-xs text-gray-600">
                        {{ $teacher->is_available ? 'Available for new evaluations' : 'Not accepting new evaluations' }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col relative z-20">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 z-30 flex-shrink-0 relative shadow-sm">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <!-- Left Section -->
                        <div class="flex items-center space-x-4">
                            <!-- Mobile Menu Button -->
                            <button @click="sidebarOpen = true" class="lg:hidden text-gray-700 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-bars text-xl"></i>
                            </button>

                            <!-- Page Title -->
                            @if(isset($header))
                                {{ $header }}
                            @else
                                <h1 class="text-xl font-semibold text-gray-900">Teacher Dashboard</h1>
                            @endif
                        </div>

                        <!-- Right Section -->
                        <div class="flex items-center space-x-3">
                            <!-- Notifications -->
                            <div class="relative" @click.outside="notificationOpen = false">
                                <button @click="notificationOpen = !notificationOpen" 
                                        class="relative w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200">
                                    <i class="fas fa-bell"></i>
                                    @if(auth()->user()->unreadNotifications->count() > 0)
                                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-rose-500 to-pink-500 rounded-full flex items-center justify-center text-xs text-white shadow-md pulse-emerald">
                                            {{ auth()->user()->unreadNotifications->count() }}
                                        </span>
                                    @endif
                                </button>

                                <!-- Notification Dropdown -->
                                <div x-show="notificationOpen"
                                     x-cloak
                                     x-transition
                                     class="absolute right-0 mt-2 w-80 bg-white rounded-xl overflow-hidden shadow-2xl border border-gray-200">
                                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-gray-900 font-semibold">Notifications</h3>
                                            @if(auth()->user()->unreadNotifications->count() > 0)
                                                <span class="text-xs text-gray-600 bg-emerald-100 px-2 py-1 rounded-full">{{ auth()->user()->unreadNotifications->count() }} new</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        @forelse(auth()->user()->unreadNotifications->take(10) as $notification)
                                            @php
                                                $evaluationId = $notification->data['evaluation_request_id'] ?? null;
                                                $evaluationUrl = $evaluationId ? route('teacher.evaluations.show', $evaluationId) : '#';
                                            @endphp
                                            <a href="{{ $evaluationUrl }}" 
                                               class="block p-4 hover:bg-gray-50 transition-colors border-b border-gray-100 group">
                                                <div class="flex items-start space-x-3">
                                                    <div class="w-10 h-10 rounded-lg emerald-gradient flex items-center justify-center flex-shrink-0 shadow-sm">
                                                        <i class="fas fa-clipboard-check text-white"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm text-gray-900 font-medium group-hover:text-emerald-600 transition-colors">
                                                            {{ $notification->data['title'] ?? 'New Evaluation Request' }}
                                                        </p>
                                                        <p class="text-xs text-gray-600 mt-1">
                                                            {{ $notification->data['message'] ?? 'You have a new evaluation to complete' }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            <i class="fas fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                    @if($evaluationId)
                                                        <div class="flex-shrink-0">
                                                            <i class="fas fa-chevron-right text-gray-400 group-hover:text-emerald-600 transition-colors"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </a>
                                        @empty
                                            <div class="p-8 text-center">
                                                <i class="fas fa-bell-slash text-4xl text-gray-300 mb-3"></i>
                                                <p class="text-gray-500">No new notifications</p>
                                            </div>
                                        @endforelse
                                    </div>
                                    @if(auth()->user()->unreadNotifications->count() > 0)
                                        <div class="p-3 border-t border-gray-200 text-center bg-gray-50">
                                            <a href="{{ route('teacher.evaluations.pending') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
                                                View all evaluations →
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Profile Dropdown -->
                            <div class="relative" @click.outside="profileDropdown = false">
                                <button @click="profileDropdown = !profileDropdown" 
                                        class="flex items-center space-x-3 px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-all duration-200">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" 
                                             class="w-8 h-8 rounded-lg object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-lg emerald-gradient flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <i class="fas fa-chevron-down text-gray-600 text-xs"></i>
                                </button>

                                <!-- Profile Menu -->
                                <div x-show="profileDropdown"
                                     x-cloak
                                     x-transition
                                     class="absolute right-0 mt-2 w-56 bg-white rounded-xl overflow-hidden shadow-xl border border-gray-200">
                                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                                        <p class="text-gray-900 font-medium">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-gray-600">{{ auth()->user()->email }}</p>
                                    </div>
                                    <div class="p-2">
                                        <a href="{{ route('teacher.profile.edit') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                            <i class="fas fa-user text-gray-600 w-4"></i>
                                            <span class="text-gray-900 text-sm font-medium">Profile Settings</span>
                                        </a>
                                        <hr class="my-2 border-gray-200">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-red-50 transition-colors text-left">
                                                <i class="fas fa-sign-out-alt text-red-600 w-4"></i>
                                                <span class="text-red-600 text-sm font-medium">Sign Out</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden">
                <div class="min-h-full flex flex-col">
                    <!-- Page Content -->
                    <div class="flex-1">
                        {{ $slot }}
                    </div>
                    
                    <!-- Footer -->
                    <footer class="bg-white border-t border-gray-200 mt-12">
                        <div class="px-4 sm:px-6 lg:px-8 py-8">
                            <div class="text-center">
                                <p class="text-gray-600 text-sm">
                                    {{ $settings->copyright_text ?? '© ' . date('Y') . ' ' . $settings->site_name . '. All rights reserved.' }}
                                </p>
                            </div>
                        </div>
                    </footer>
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Notifications -->
    @if(session('success'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             x-transition
             class="fixed bottom-4 right-4 z-50">
            <div class="bg-white rounded-xl p-4 flex items-center space-x-3 min-w-[300px] shadow-lg border border-emerald-200">
                <div class="w-10 h-10 rounded-lg emerald-gradient flex items-center justify-center shadow-sm">
                    <i class="fas fa-check text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="text-gray-900 font-medium">Success!</p>
                    <p class="text-sm text-gray-600">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             x-transition
             class="fixed bottom-4 right-4 z-50">
            <div class="bg-white rounded-xl p-4 flex items-center space-x-3 min-w-[300px] shadow-lg border border-red-200">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-500 to-rose-500 flex items-center justify-center shadow-sm">
                    <i class="fas fa-exclamation text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="text-gray-900 font-medium">Error!</p>
                    <p class="text-sm text-gray-600">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @stack('scripts')
    
    <script>
        function layoutData() {
            return {
                sidebarOpen: false,
                profileDropdown: false,
                notificationOpen: false
            }
        }
    </script>
</body>
</html>