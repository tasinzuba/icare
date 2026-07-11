<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Branch Admin') - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .sidebar-link {
            position: relative;
            transition: all 0.15s ease;
        }
        .sidebar-link:hover {
            background-color: #fef2f3;
            color: #C8102E;
        }
        .sidebar-link:hover .sidebar-icon {
            color: #C8102E;
        }
        .sidebar-link.active {
            background-color: #fef2f3;
            color: #C8102E;
            font-weight: 600;
        }
        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 6px;
            bottom: 6px;
            width: 3px;
            background-color: #C8102E;
            border-radius: 0 2px 2px 0;
        }
        .sidebar-link.active .sidebar-icon {
            color: #C8102E;
        }
        /* Pagination polish */
        nav[role="navigation"] .relative { font-size: 13px; }
    </style>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 h-full w-[250px] bg-white border-r border-gray-200 z-30 flex flex-col">
        <!-- Brand -->
        <div class="h-16 flex items-center px-5 border-b border-gray-200">
            <a href="{{ route('branch.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 bg-gradient-to-br from-[#C8102E] to-[#A00E27] rounded-lg flex items-center justify-center shadow-sm group-hover:scale-105 transition">
                    <span class="text-white font-bold text-sm">i</span>
                </div>
                <div class="leading-tight min-w-0">
                    <p class="text-[13px] font-bold text-gray-900 truncate max-w-[160px]">{{ $currentBranch->name ?? 'I-Care' }}</p>
                    <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Admin Panel</p>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
            <p class="px-3 mb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Main</p>

            <a href="{{ route('branch.dashboard') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] text-gray-600 hover:bg-gray-50 {{ request()->routeIs('branch.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large w-4 text-center text-gray-400 sidebar-icon text-[13px]"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('branch.batches.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] text-gray-600 hover:bg-gray-50 {{ request()->routeIs('branch.batches.*') ? 'active' : '' }}">
                <i class="fas fa-layer-group w-4 text-center text-gray-400 sidebar-icon text-[13px]"></i>
                <span>Batches</span>
            </a>

            <a href="{{ route('branch.students.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] text-gray-600 hover:bg-gray-50 {{ request()->routeIs('branch.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate w-4 text-center text-gray-400 sidebar-icon text-[13px]"></i>
                <span>Students</span>
            </a>

            <a href="{{ route('branch.tests.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] text-gray-600 hover:bg-gray-50 {{ request()->routeIs('branch.tests.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt w-4 text-center text-gray-400 sidebar-icon text-[13px]"></i>
                <span>Tests</span>
            </a>

            <a href="{{ route('branch.payments.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] text-gray-600 hover:bg-gray-50 {{ request()->routeIs('branch.payments.*') ? 'active' : '' }}">
                <i class="fas fa-credit-card w-4 text-center text-gray-400 sidebar-icon text-[13px]"></i>
                <span>Payments</span>
            </a>

            <a href="{{ route('branch.reports.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] text-gray-600 hover:bg-gray-50 {{ request()->routeIs('branch.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar w-4 text-center text-gray-400 sidebar-icon text-[13px]"></i>
                <span>Reports</span>
            </a>

            @if($branchStaffRole === 'admin')
            <div class="pt-4 mt-4 border-t border-gray-100">
                <p class="px-3 mb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Admin</p>
                <a href="{{ route('branch.reports.students') }}"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-file-export w-4 text-center text-gray-400 sidebar-icon text-[13px]"></i>
                    <span>Export Data</span>
                </a>
            </div>
            @endif
        </nav>

        <!-- User Section (Bottom) -->
        <div class="border-t border-gray-200 px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-br from-[#C8102E] to-[#A00E27] rounded-full flex items-center justify-center flex-shrink-0 shadow-sm">
                    <span class="text-white font-bold text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-gray-500 capitalize font-medium uppercase tracking-wider">{{ $branchStaffRole ?? 'staff' }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-[#C8102E] hover:bg-gray-50 transition rounded-md p-1.5" title="Logout">
                        <i class="fas fa-sign-out-alt text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Area -->
    <div class="ml-[250px] min-h-screen">
        <!-- Top Bar -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 z-20">
            <div>
                <h1 class="text-[15px] font-bold text-gray-900 tracking-tight">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-[12px] font-medium text-gray-500 inline-flex items-center gap-1.5">
                    <i class="far fa-calendar text-gray-400"></i>
                    {{ now()->format('l, M d Y') }}
                </span>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-8">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 text-sm" role="alert">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm" role="alert">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
