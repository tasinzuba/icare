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

    <title>{{ $title ?? 'Dashboard' }} - {{ \App\Models\WebsiteSetting::getSettings()->site_name }} Admin</title>

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 280px;
            --header-height: 70px;
        }
        
        * {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }
        
        *::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        *::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        
        *::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
            border: 2px solid #f1f5f9;
        }
        
        *::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        [x-cloak] { display: none !important; }
        
        /* Fix for sidebar scroll */
        aside {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        aside nav {
            flex: 1;
            min-height: 0;
        }
        
        /* Modern Sidebar */
        aside.modern-sidebar {
            background: #ffffff;
            border-right: 1px solid #f1f5f9;
        }

        .sidebar-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: #94a3b8;
            padding: 0 8px;
            margin-bottom: 10px;
        }

        .sidebar-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, #e2e8f0, transparent);
        }

        .sidebar-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 7px 10px;
            border-radius: 10px;
            color: #475569;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.18s ease;
            margin-bottom: 3px;
        }

        .sidebar-link-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: #f8fafc;
            color: #64748b;
            flex-shrink: 0;
            transition: all 0.18s ease;
        }

        .sidebar-link-icon svg {
            width: 17px;
            height: 17px;
        }

        .sidebar-link:hover {
            background-color: #f8fafc;
            color: #0f172a;
        }

        .sidebar-link:hover .sidebar-link-icon {
            background: #eef2ff;
            color: #6366f1;
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
            color: #4f46e5;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(79, 70, 229, 0.05);
        }

        .sidebar-link.active .sidebar-link-icon {
            background: #ffffff;
            color: #4f46e5;
            box-shadow: 0 1px 3px rgba(79, 70, 229, 0.15);
        }

        .sidebar-badge {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 20px;
            padding: 0 7px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 600;
            line-height: 1;
        }

        /* Bottom user card */
        .sidebar-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .sidebar-user-card:hover {
            border-color: #c7d2fe;
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
        }

        .sidebar-user-avatar {
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            box-shadow: 0 2px 4px rgba(99, 102, 241, 0.25);
        }

        .sidebar-user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user-name {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 11px;
            color: #64748b;
            margin-top: 1px;
        }

        .sidebar-logout-btn {
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: #ffffff;
            color: #ef4444;
            border: 1px solid #fee2e2;
            transition: all 0.18s ease;
            cursor: pointer;
        }

        .sidebar-logout-btn:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
        }
        
        .metric-card {
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: #6366f1;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .animate-pulse-custom {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Custom scrollbar for sidebar */
        nav::-webkit-scrollbar {
            width: 6px;
        }

        nav::-webkit-scrollbar-track {
            background: transparent;
        }

        nav::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 3px;
        }

        nav::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }

        /* Smooth page transitions */
        .main-content {
            animation: fadeIn 0.3s ease-in;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50/30 text-gray-900 antialiased">
    <div x-data="{
        sidebarOpen: false,
        profileDropdown: false,
        notificationOpen: false
    }" class="flex h-screen overflow-hidden bg-gray-50/30">
        
        <!-- Sidebar Backdrop (Mobile) -->
        <div x-show="sidebarOpen" 
             x-cloak
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="modern-sidebar fixed inset-y-0 left-0 z-50 flex flex-col w-[var(--sidebar-width)] transform shadow-2xl transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:shadow-none">
            
            <!-- Logo -->
            <div class="flex h-[var(--header-height)] items-center justify-between border-b border-gray-100 px-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center group">
                    @php
                        $settings = \App\Models\WebsiteSetting::getSettings();
                    @endphp
                    @if($settings->site_logo)
                        <img src="{{ $settings->logo_url }}" alt="{{ $settings->site_title }}" class="h-10 w-auto transition-transform group-hover:scale-105">
                    @else
                        <div class="flex items-center space-x-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600 text-white shadow-md transition-all group-hover:shadow-lg group-hover:scale-105">
                                <!-- Book Icon -->
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-lg font-bold text-gray-900 transition-colors group-hover:text-indigo-600">{{ $settings->site_title }}</h1>
                                <p class="text-xs font-medium text-gray-500">Admin Panel</p>
                            </div>
                        </div>
                    @endif
                </a>
                <button @click="sidebarOpen = false" class="rounded-xl p-2 text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all lg:hidden">
                    <!-- X Icon -->
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 py-6">
                <!-- Main Menu -->
                @php
                    $user = auth()->user();
                    $showMainMenu = $user->is_admin || $user->hasPermission('dashboard.view') || $user->hasPermission('questions.view') || $user->hasPermission('test-sets.view') || $user->hasPermission('full-tests.view') || $user->hasPermission('test-categories.view') || $user->hasPermission('attempts.view');
                @endphp

                @if($showMainMenu)
                <div class="mb-6">
                    <h3 class="sidebar-section-title">Main Menu</h3>

                    @if($user->is_admin || $user->hasPermission('dashboard.view'))
                    <a href="{{ route('admin.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </span>
                        <span>Dashboard</span>
                    </a>
                    @endif

                    @if($user->is_admin || $user->hasPermission('questions.view'))
                    <a href="{{ route('admin.questions.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span>Questions</span>
                    </a>
                    @endif

                    @if($user->is_admin || $user->hasPermission('test-sets.view'))
                    <a href="{{ route('admin.test-sets.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.test-sets.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </span>
                        <span>Test Sets</span>
                    </a>
                    @endif

                    @if($user->is_admin || $user->hasPermission('full-tests.view'))
                    <a href="{{ route('admin.full-tests.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.full-tests.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        <span>Full Tests</span>
                        <span class="sidebar-badge bg-indigo-600 text-white">New</span>
                    </a>
                    @endif

                    @if($user->is_admin || $user->hasPermission('test-categories.view'))
                    <a href="{{ route('admin.test-categories.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.test-categories.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </span>
                        <span>Test Categories</span>
                        @if($categoryCount = \App\Models\TestCategory::count())
                            <span class="sidebar-badge bg-purple-100 text-purple-700">{{ $categoryCount }}</span>
                        @endif
                    </a>
                    @endif

                    @if($user->is_admin || $user->hasPermission('attempts.view'))
                    <a href="{{ route('admin.attempts.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.attempts.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </span>
                        <span>Student Results</span>
                        @if($pendingCount = \App\Models\StudentAttempt::where('status', 'completed')->whereNull('band_score')->count())
                            <span class="sidebar-badge bg-red-500 text-white">{{ $pendingCount > 9 ? '9+' : $pendingCount }}</span>
                        @endif
                    </a>
                    @endif
                </div>
                @endif

                <!-- Human Evaluation Management -->
                @php
                    $showHumanEval = $user->is_admin || $user->hasPermission('teachers.view');
                @endphp

                @if($showHumanEval)
                <div class="mb-6">
                    <h3 class="sidebar-section-title">Human Evaluation</h3>

                    @if($user->is_admin || $user->hasPermission('teachers.view'))
                    <a href="{{ route('admin.teachers.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                            </svg>
                        </span>
                        <span>Teachers</span>
                        @if($teacherCount = \App\Models\Teacher::where('is_available', true)->count())
                            <span class="sidebar-badge bg-emerald-100 text-emerald-700">{{ $teacherCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('admin.avatar-teachers.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.avatar-teachers.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <span>Avatar Teachers</span>
                        @if($avatarTeacherCount = \App\Models\AvatarTeacher::where('is_active', true)->count())
                            <span class="sidebar-badge bg-indigo-100 text-indigo-700">{{ $avatarTeacherCount }}</span>
                        @endif
                    </a>
                    @endif

                </div>
                @endif

                <!-- User Management -->
                @php
                    $showUserManagement = $user->is_admin || $user->hasPermission('users.view');
                @endphp

                @if($showUserManagement)
                <div class="mb-6">
                    <h3 class="sidebar-section-title">User Management</h3>

                    @if($user->is_admin)
                    <a href="{{ route('admin.roles.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </span>
                        <span>Roles &amp; Permissions</span>
                        <span class="sidebar-badge bg-indigo-600 text-white">New</span>
                    </a>
                    @endif

                    @if($user->is_admin || $user->hasPermission('users.view'))
                    <a href="{{ route('admin.users.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.users.system') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </span>
                        <span>All Users</span>
                        @if($totalUsers = \App\Models\User::count())
                            <span class="sidebar-badge bg-blue-100 text-blue-700">{{ $totalUsers }}</span>
                        @endif
                    </a>

                    <a href="{{ route('admin.users.system') }}"
                       class="sidebar-link {{ request()->routeIs('admin.users.system') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                        </span>
                        <span>System Users</span>
                        @if($systemUsers = \App\Models\User::where(function($q) { $q->where('is_admin', true)->orWhereHas('teacher'); })->count())
                            <span class="sidebar-badge bg-purple-100 text-purple-700">{{ $systemUsers }}</span>
                        @endif
                    </a>
                    @endif

                    @if($user->is_admin || $user->hasPermission('users.view'))
                    <a href="{{ route('admin.branches.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </span>
                        <span>Branches</span>
                        @if($branchCount = \App\Models\Branch::where('active', true)->count())
                            <span class="sidebar-badge bg-indigo-100 text-indigo-700">{{ $branchCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('admin.offline-packages.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.offline-packages.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </span>
                        <span>Offline Packages</span>
                        @if($offlinePackageCount = \App\Models\OfflinePackage::where('is_active', true)->count())
                            <span class="sidebar-badge bg-orange-100 text-orange-700">{{ $offlinePackageCount }}</span>
                        @endif
                    </a>
                    @endif
                </div>
                @endif

                <!-- Settings -->
                @php
                    $showSettings = $user->is_admin || $user->hasPermission('settings.view');
                @endphp

                @if($showSettings)
                <div class="mb-6">
                    <h3 class="sidebar-section-title">Settings</h3>

                    @if($user->is_admin || $user->hasPermission('settings.view'))
                    <a href="{{ route('admin.settings.website') }}"
                       class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <span>Website Settings</span>
                    </a>
                    @endif
                </div>
                @endif
            </nav>
            
            <!-- Footer: User Card -->
            <div class="p-4 border-t border-gray-100 mt-auto">
                <div class="sidebar-user-card">
                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 flex-1 min-w-0">
                        <div class="sidebar-user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="sidebar-user-info">
                            <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                            <div class="sidebar-user-role">
                                {{ auth()->user()->is_admin ? 'Administrator' : (auth()->user()->teacher ? 'Teacher' : 'Staff') }}
                            </div>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="sidebar-logout-btn" title="Logout">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Header -->
            <header class="relative z-30 flex h-[var(--header-height)] items-center justify-between border-b border-gray-100 bg-white px-4 lg:px-8 shadow-sm">
                <!-- Left: Mobile menu + Page Title -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = true" class="rounded-xl p-2.5 text-gray-600 hover:bg-gray-50 transition-all lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $title ?? 'Dashboard' }}</h1>
                        <p class="text-sm text-gray-500 hidden sm:block">Welcome back, {{ auth()->user()->name }}</p>
                    </div>
                </div>

                <!-- Right side items -->
                <div class="flex items-center space-x-2">

                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative rounded-xl p-2.5 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all">
                            <!-- Bell Icon -->
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-indigo-500 animate-pulse-custom"></span>
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 rounded-xl bg-white shadow-xl ring-1 ring-gray-100">
                            <div class="border-b border-gray-100 p-4">
                                <h3 class="font-bold text-gray-900">Notifications</h3>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <div class="p-8 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-500">No new notifications</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="hidden items-center space-x-2 lg:flex">
                        <a href="{{ route('admin.questions.create') }}"
                           class="flex items-center rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md hover:from-indigo-700 hover:to-indigo-800 transition-all">
                            <!-- Plus Icon -->
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>New Question</span>
                        </a>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center rounded-xl p-2 hover:bg-gray-50 transition-all group">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600 text-white shadow-sm group-hover:shadow-md transition-all">
                                <span class="text-xs font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                            </div>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-xl ring-1 ring-gray-100">
                            <div class="p-2">
                                <a href="{{ route('admin.profile.edit') }}" class="flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-all">
                                    <!-- User Icon -->
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Profile</span>
                                </a>
                                <a href="#" class="flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-all">
                                    <!-- Settings Icon -->
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Settings</span>
                                    
                                </a>
                                <hr class="my-2 border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center rounded-lg px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition-all">
                                        <!-- Logout Icon -->
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 flex flex-col overflow-hidden">
                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto bg-gray-50/50">
                    <div class="p-4 lg:p-8">
                        <div class="fade-in">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
                
                <!-- Fixed Footer -->
                <footer class="bg-white border-t border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        <div class="flex flex-col md:flex-row justify-between items-center">
                            <div class="text-center md:text-left mb-2 md:mb-0">
                                <p class="text-gray-500 text-sm">
                                    {{ $settings->copyright_text ?? '© ' . date('Y') . ' ' . $settings->site_name . '. All rights reserved.' }}
                                </p>
                                @if($settings->footer_text)
                                    <p class="text-gray-400 text-xs mt-1">{{ $settings->footer_text }}</p>
                                @endif
                            </div>
                            @if($settings->hasSocialLinks())
                                <div class="flex space-x-4">
                                    @foreach($settings->social_links as $social)
                                        <a href="{{ $social['url'] }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="text-gray-400 hover:text-gray-600 transition-colors">
                                            <i class="{{ $social['icon'] }}"></i>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    @stack('scripts')
    
    <script>
        // Toast notification function
        function showToast(message, type = 'info') {
            const colors = {
                'success': 'bg-green-500',
                'error': 'bg-red-500',
                'warning': 'bg-yellow-500',
                'info': 'bg-blue-500'
            };
            
            const toast = document.createElement('div');
            toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
            toast.textContent = message;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>