<x-guest-layout>
    <x-slot name="title">I-Care | IELTS Intensive Care Test Portal</x-slot>

    <x-slot name="head">
        <!-- SEO Meta Tags -->
        <meta name="description" content="I-Care — IELTS Intensive Care Test Portal. Prepare for your IELTS exam in a real test environment with computer-delivered mock tests for Listening, Reading, Writing & Speaking.">
        <meta name="keywords" content="I-Care, ICARE IELTS, IELTS intensive care, IELTS preparation, computer delivered IELTS, real IELTS environment, IELTS mock test, IELTS portal">
        <meta name="robots" content="index, follow">
        <meta name="author" content="I-Care">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:title" content="I-Care | IELTS Intensive Care Test Portal">
        <meta property="og:description" content="Prepare for IELTS in a real exam environment. Computer-delivered mock tests for all four sections — designed for intensive, focused preparation.">
        <meta property="og:image" content="{{ $websiteSettings->logo_url ?? asset('images/og-image.png') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="I-Care - IELTS Intensive Care Test Portal">
        <meta property="og:site_name" content="I-Care">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url('/') }}">
        <meta name="twitter:title" content="I-Care | IELTS Intensive Care Test Portal">
        <meta name="twitter:description" content="IELTS Intensive Care Test Portal — practice in real exam environment.">
        <meta name="twitter:image" content="{{ $websiteSettings->logo_url ?? asset('images/og-image.png') }}">

        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url('/') }}">

        <!-- Structured Data (JSON-LD) -->
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "WebSite",
            "name": "I-Care",
            "url": "{{ url('/') }}",
            "description": "IELTS Intensive Care Test Portal — practice in a real exam environment with computer-delivered mock tests.",
            "potentialAction": {
                "@@type": "SearchAction",
                "target": "{{ url('/') }}/search?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
        </script>
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "EducationalOrganization",
            "name": "I-Care",
            "url": "{{ url('/') }}",
            "description": "IELTS Intensive Care Test Portal — real exam environment preparation for Listening, Reading, Writing & Speaking.",
            "sameAs": []
        }
        </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-b from-white to-gray-50 py-16 md:py-24">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23dc2626" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        
        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-6xl mx-auto">
                <!-- Main Content Grid -->
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div class="text-center lg:text-left">
                        <!-- Brand badge -->
                        <div class="inline-flex items-center gap-2 px-3 py-1 mb-5 bg-red-50 border border-red-200 rounded-full text-xs font-bold text-[#C8102E] uppercase tracking-wider">
                            <i class="fas fa-heart-pulse"></i> Intensive Care for IELTS
                        </div>

                        <!-- Main Headline -->
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-6 tracking-tight">
                            Welcome to
                            <span class="relative inline-block">
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-red-700">I-Care</span>
                                <svg class="absolute -bottom-2 left-0 w-full" height="10" viewBox="0 0 200 10">
                                    <path d="M0,8 Q50,0 100,8 T200,8" stroke="#ef4444" stroke-width="3" fill="none" opacity="0.6"/>
                                </svg>
                            </span>
                            <br class="hidden md:block">
                            <span class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-700 block mt-3">IELTS Intensive Care Test Portal</span>
                        </h1>

                        <!-- Sub-headline -->
                        <p class="text-lg md:text-xl text-gray-700 mb-8 font-medium leading-relaxed">
                            Prepare for your IELTS exam in a <span class="font-bold text-gray-900">real test environment</span>.
                            Computer-delivered mock tests designed for <span class="font-bold text-[#C8102E]">focused, intensive practice</span>.
                        </p>

                        <!-- CTA Buttons -->
                        <div class="mb-10 flex flex-wrap gap-3 justify-center lg:justify-start">
                            <a href="{{ route('offline.login') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-red-500 to-red-600 text-white font-bold text-lg rounded-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-200 group">
                                <i class="fas fa-graduation-cap mr-3"></i>
                                Offline Login
                                <i class="fas fa-arrow-right ml-3 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                            <a href="{{ route('score-calculator') }}" class="inline-flex items-center px-8 py-4 bg-white border-2 border-gray-900 text-gray-900 font-bold text-lg rounded-xl hover:bg-gray-900 hover:text-white hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-200 group">
                                <i class="fas fa-calculator mr-3"></i>
                                Score Calculator
                            </a>
                        </div>
                        
                        <!-- Key Features -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900">Real Exam Environment</h3>
                                    <p class="text-sm text-gray-600 font-normal">Practice exactly like the real test</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900">Teacher Evaluation</h3>
                                    <p class="text-sm text-gray-600 font-normal">Expert review for Writing & Speaking</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900">Authentic IELTS Format</h3>
                                    <p class="text-sm text-gray-600 font-normal">All four sections, real test pattern</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900">Detailed Progress</h3>
                                    <p class="text-sm text-gray-600 font-normal">Track every section, every attempt</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Visual -->
                    <div class="relative lg:block hidden">
                        <!-- Floating Cards Animation -->
                        <div class="relative w-full h-[500px]">
                            <!-- Main Card -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 bg-white rounded-2xl shadow-2xl p-6 animate-float">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-900">Your Progress</h3>
                                    <span class="text-3xl">📈</span>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Listening</span>
                                            <span class="font-bold text-green-600">Band 8.5</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: 85%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Reading</span>
                                            <span class="font-bold text-blue-600">Band 7.5</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: 75%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Writing</span>
                                            <span class="font-bold text-yellow-600">Band 7.0</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 70%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Speaking</span>
                                            <span class="font-bold text-purple-600">Band 8.0</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-purple-500 h-2 rounded-full" style="width: 80%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Floating Achievement Badge -->
                            <div class="absolute top-10 right-10 bg-gradient-to-br from-yellow-400 to-yellow-600 text-white p-4 rounded-2xl shadow-lg animate-bounce-slow">
                                <div class="text-center">
                                    <i class="fas fa-trophy text-3xl mb-2"></i>
                                    <p class="text-sm font-bold">Band 8+</p>
                                    <p class="text-xs">Achieved!</p>
                                </div>
                            </div>
                            
                            <!-- AI Feedback Card -->
                            <div class="absolute bottom-10 left-10 bg-white rounded-xl shadow-lg p-4 max-w-xs animate-pulse-slow">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-robot text-white"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">AI Feedback</p>
                                        <p class="text-xs text-gray-600">"Great improvement! Focus on complex sentences for Band 8+"</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Wave -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,96L48,80C96,64,192,32,288,26.7C384,21,480,43,576,48C672,53,768,43,864,42.7C960,43,1056,53,1152,58.7C1248,64,1344,64,1392,64L1440,64L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z" fill="#f9fafb"/>
            </svg>
        </div>
    </section>

    <!-- Top Performers Section - Minimal & Clean -->
    @if($topPerformers && $topPerformers->count() > 0)
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-12">
                
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Top Performers</h2>
                <p class="text-gray-600 max-w-xl mx-auto">See who's leading this week and get inspired to achieve your goals!</p>
            </div>
            
            <!-- Leaderboard Cards -->
            <div class="max-w-4xl mx-auto">
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($topPerformers as $index => $performer)
                        @php
                            $rank = $index + 1;
                            $colors = [
                                1 => ['gradient' => 'from-yellow-50 to-yellow-100', 'border' => 'border-yellow-200', 'badge' => 'bg-yellow-500', 'text' => 'text-yellow-700', 'icon' => 'text-yellow-500'],
                                2 => ['gradient' => 'from-gray-50 to-gray-100', 'border' => 'border-gray-200', 'badge' => 'bg-gray-400', 'text' => 'text-gray-700', 'icon' => 'text-gray-400'],
                                3 => ['gradient' => 'from-orange-50 to-orange-100', 'border' => 'border-orange-200', 'badge' => 'bg-orange-500', 'text' => 'text-orange-700', 'icon' => 'text-orange-500']
                            ];
                            $color = $colors[$rank];
                            $medals = ['🥇', '🥈', '🥉'];
                        @endphp
                        
                        <div class="relative {{ $rank === 1 ? 'md:-translate-y-4' : '' }}">
                            <!-- Rank Badge -->
                            <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-10">
                                <div class="w-10 h-10 {{ $color['badge'] }} rounded-full flex items-center justify-center shadow-lg border-4 border-white">
                                    <span class="text-white font-bold text-lg">{{ $rank }}</span>
                                </div>
                            </div>
                            
                            <!-- Card -->
                            <div class="bg-gradient-to-br {{ $color['gradient'] }} border-2 {{ $color['border'] }} rounded-2xl p-6 pt-8 hover:shadow-xl transition-all duration-300">
                                <!-- Medal -->
                                <div class="text-center mb-3">
                                    <span class="text-4xl">{{ $medals[$index] }}</span>
                                </div>
                                
                                <!-- Avatar -->
                                <div class="flex justify-center mb-4">
                                    @if($performer->user->avatar_url)
                                        <img src="{{ $performer->user->avatar_url }}" 
                                             alt="{{ $performer->user->name }}" 
                                             class="w-16 h-16 rounded-full border-3 border-white shadow-md object-cover">
                                    @else
                                        <div class="w-16 h-16 bg-white rounded-full border-3 border-white shadow-md flex items-center justify-center">
                                            <span class="text-2xl font-bold {{ $color['text'] }}">
                                                {{ substr($performer->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Name -->
                                <h3 class="text-center font-bold text-gray-900 mb-1 truncate px-2">
                                    {{ $performer->user->name }}
                                </h3>
                                
                                <!-- Band Score -->
                                <div class="flex justify-center mb-3">
                                    <div class="inline-flex items-center bg-white rounded-full px-4 py-1.5 shadow-sm">
                                        <i class="fas fa-star {{ $color['icon'] }} mr-1.5 text-sm"></i>
                                        <span class="font-bold text-gray-900">Band {{ number_format($performer->average_score, 1) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Stats -->
                                <div class="text-center">
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                        {{ $performer->tests_taken }} tests completed
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- CTA -->
                <div class="text-center mt-10">
                    <p class="text-gray-700 mb-4 font-medium">Ready to join them?</p>
                    <a href="{{ route('register') }}" 
                       class="inline-flex items-center px-6 py-3 bg-[#C8102E] text-white font-semibold rounded-xl hover:bg-[#A00E27] transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Start Practicing Free
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Why Use IELTS Online Tests Section -->
    <section class="py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Why I-Care?</h2>
                <p class="text-lg text-gray-600 font-medium max-w-2xl mx-auto">Intensive Care for your IELTS preparation — every detail matters.</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                <!-- Feature 1 -->
                <div class="group">
                    <div class="bg-white rounded-2xl p-8 h-full shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-100 to-transparent rounded-full -translate-y-16 translate-x-16 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Take recent actual IELTS Tests</h3>
                            <p class="text-gray-600 leading-relaxed">Real IELTS Listening and IELTS Reading tests based on actual IELTS tests and following the Cambridge IELTS book format.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="group">
                    <div class="bg-white rounded-2xl p-8 h-full shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-orange-100 to-transparent rounded-full -translate-y-16 translate-x-16 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Community-driven</h3>
                            <p class="text-gray-600 leading-relaxed">Created by our community of IELTS teachers, previous IELTS examiners and IELTS exam takers.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="group">
                    <div class="bg-white rounded-2xl p-8 h-full shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-100 to-transparent rounded-full -translate-y-16 translate-x-16 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Comprehensive analytics tool</h3>
                            <p class="text-gray-600 leading-relaxed">Our IELTS Analytics is a tool that allows you to set a target IELTS band score, analyse your progress and find how to improve.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 4 -->
                <div class="group">
                    <div class="bg-white rounded-2xl p-8 h-full shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-100 to-transparent rounded-full -translate-y-16 translate-x-16 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">View IELTS Score and Answer Explanations</h3>
                            <p class="text-gray-600 leading-relaxed">After taking our IELTS mock tests with real audio, you can check your Listening or Reading band score and view your answer sheets.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 5 -->
                <div class="group">
                    <div class="bg-white rounded-2xl p-8 h-full shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-pink-100 to-transparent rounded-full -translate-y-16 translate-x-16 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative">
                            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">FREE to use</h3>
                            <p class="text-gray-600 leading-relaxed">Our online IELTS tests are always free. We are here to help users for study abroad, immigration and finding jobs.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 6 -->
                <div class="group">
                    <div class="bg-white rounded-2xl p-8 h-full shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-red-100 to-transparent rounded-full -translate-y-16 translate-x-16 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative">
                            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Increase your IELTS band score</h3>
                            <p class="text-gray-600 leading-relaxed">Using our online tests for IELTS preparation is proven to help students improve by 0.5 - 1.5.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Practice Section - Full Test & Individual Test -->
    <section class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-6">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-extrabold text-gray-900 mb-4 leading-tight">
                    Practice Makes <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-pink-600">Perfect</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Master your IELTS skills with our comprehensive practice tests - choose full exam simulation or focus on individual sections.
                </p>
            </div>

            <!-- Complete Test Experience -->
            <div class="max-w-7xl mx-auto bg-white rounded-3xl p-12 shadow-lg mb-20">
                <!-- Content Grid -->
                <div class="grid lg:grid-cols-2 gap-20 items-center">
                    <!-- Left Side - Visual Mockup -->
                    <div class="relative order-2 lg:order-1">
                        <div class="bg-gradient-to-br from-red-50 via-pink-50 to-orange-50 rounded-2xl p-8 shadow-lg">
                            <!-- Test Header Card -->
                            <div class="bg-white rounded-2xl p-6 mb-6 shadow-xl">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="text-sm font-semibold text-gray-500">15,847 Test Takers</span>
                                        </div>
                                        <h3 class="text-2xl font-bold text-gray-900">IELTS Complete Mock Test 1</h3>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-xs font-bold mb-2">Intermediate</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="text-sm text-gray-600">
                                        <span class="font-semibold text-gray-900">Test Completed</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-2xl font-bold text-green-600">8.0</span>
                                        <span class="text-gray-400 text-sm">/9.0</span>
                                    </div>
                                </div>
                                
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-3">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-2.5 rounded-full" style="width: 89%"></div>
                                </div>
                            </div>

                            <!-- Full Test Info Card -->
                            <div class="bg-white rounded-2xl p-6 shadow-xl">
                                <div class="flex items-start space-x-4 mb-6">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-[#C8102E] to-[#A00E27] rounded-xl flex items-center justify-center">
                                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900 mb-1">Complete IELTS Exam</h4>
                                        <p class="text-sm text-gray-600">Duration: 2 hours 45 minutes</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-semibold text-gray-700">Test Information</span>
                                        </div>
                                        <ul class="space-y-2 text-sm text-gray-600">
                                            <li class="flex items-start">
                                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>Simulates official computer-delivered IELTS examination</span>
                                            </li>
                                            <li class="flex items-start">
                                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>4 Sections: Listening, Reading, Writing, Speaking</span>
                                            </li>
                                            <li class="flex items-start">
                                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>Cannot pause during the test session (exam mode)</span>
                                            </li>
                                            <li class="flex items-start">
                                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>Auto-resume if accidentally closed during test</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Success Badge -->
                                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-4 flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-green-900 text-sm">Test Completed Successfully!</p>
                                            <p class="text-xs text-green-700">Your results are ready. Click below to view detailed feedback.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Content -->
                    <div class="order-1 lg:order-2">
                        
                        <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 leading-tight">Complete IELTS Exam Experience</h2>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-gray-700 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-gray-700 text-lg leading-relaxed">Complete all 4 sections in one sitting - just like the real IELTS test day.</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-gray-700 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-gray-700 text-lg leading-relaxed">Strict timing enforcement helps you build speed and exam-day confidence.</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-gray-700 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-gray-700 text-lg leading-relaxed">Test your endurance and concentration across 2 hours 45 minutes.</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-gray-700 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-gray-700 text-lg leading-relaxed">Get your overall band score plus breakdown for each module instantly.</p>
                            </div>
                        </div>

                        <a href="{{ route('register') }}" class="inline-flex items-center px-7 py-3.5 bg-[#C8102E] text-white font-bold text-base rounded-lg hover:bg-[#A00E27] transition-all shadow-md hover:shadow-lg">
                            Try Full Test Now
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Individual Test -->
            <div class="max-w-7xl mx-auto bg-white rounded-3xl p-12 shadow-lg">
                <!-- Content Grid -->
                <div class="grid lg:grid-cols-2 gap-20 items-center">
                    <!-- Left Side - Compact Visual Card -->
                    <div class="relative">
                        <div class="bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 rounded-2xl p-8 shadow-lg">
                            <!-- Video Card -->
                            <div class="bg-gradient-to-br from-gray-700 to-gray-900 rounded-xl overflow-hidden shadow-xl mb-6">
                                <div class="aspect-video flex items-center justify-center relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-purple-600/20"></div>
                                    <div class="relative z-10 text-center">
                                        <div class="w-20 h-20 bg-white/10 backdrop-blur-sm rounded-full mx-auto mb-3 flex items-center justify-center">
                                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <p class="text-white font-semibold text-base">Describe your hometown</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Audio/Timer Bar -->
                            <div class="bg-white rounded-xl p-5 shadow-md">
                                <div class="flex items-center space-x-4 mb-3">
                                    <span class="text-[#C8102E] text-sm font-bold">00:32</span>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-1">
                                            <div class="w-1 h-4 bg-[#C8102E] rounded-full"></div>
                                            <div class="w-1 h-5 bg-[#C8102E] rounded-full"></div>
                                            <div class="w-1 h-6 bg-[#C8102E] rounded-full"></div>
                                            <div class="w-1 h-5 bg-[#C8102E] rounded-full"></div>
                                            <div class="w-1 h-7 bg-[#C8102E] rounded-full"></div>
                                            <div class="w-1 h-4 bg-[#C8102E] rounded-full"></div>
                                            <div class="w-1 h-6 bg-[#C8102E] rounded-full"></div>
                                            <div class="w-1 h-5 bg-[#C8102E] rounded-full"></div>
                                            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1.5 rounded">Recording ●</span>
                                    <div class="flex space-x-2">
                                        <button class="px-5 py-2 bg-[#C8102E] text-white text-sm font-semibold rounded-lg">Stop</button>
                                        <button class="px-5 py-2 bg-gray-200 text-gray-600 text-sm font-semibold rounded-lg">Next</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Content -->
                    <div>
                       
                        <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 leading-tight">Master Each Skill Separately</h2>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-gray-700 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-gray-700 text-lg leading-relaxed">Separate tests for Listening, Reading, Writing & Speaking.</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-gray-700 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-gray-700 text-lg leading-relaxed">Target your weakest areas with focused practice.</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-gray-700 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-gray-700 text-lg leading-relaxed">Get detailed scores and personalized feedback instantly.</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-gray-700 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-gray-700 text-lg leading-relaxed">Build balanced performance across all modules.</p>
                            </div>
                        </div>

                        <a href="{{ route('register') }}" class="inline-flex items-center px-7 py-3.5 bg-[#C8102E] text-white font-bold text-base rounded-lg hover:bg-[#A00E27] transition-all shadow-md hover:shadow-lg">
                            Try Individual Test
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Speaking Coaching Session Section - Coming Soon -->
    <section class="relative py-20 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 overflow-hidden">
        <!-- Animated Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 20% 50%, rgba(239, 68, 68, 0.3) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(239, 68, 68, 0.2) 0%, transparent 50%);"></div>
        </div>
        
        <!-- Floating Decorative Elements -->
        <div class="absolute top-10 left-10 w-20 h-20 bg-red-500/20 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-10 right-10 w-32 h-32 bg-blue-500/20 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1s;"></div>
        
        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div class="text-center lg:text-left">
                        <!-- Main Headline -->
                        <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6 leading-tight">
                            <span class="relative inline-block">
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 via-pink-400 to-purple-400">One-on-One Live Sessions</span>
                                <svg class="absolute -bottom-2 left-0 w-full" height="12" viewBox="0 0 300 12">
                                    <path d="M0,10 Q75,2 150,10 T300,10" stroke="url(#gradient)" stroke-width="3" fill="none"/>
                                    <defs>
                                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" style="stop-color:#ef4444;stop-opacity:0.6" />
                                            <stop offset="100%" style="stop-color:#a855f7;stop-opacity:0.6" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </span>
                        </h2>
                        
                        <!-- Description -->
                        <p class="text-lg text-gray-300 mb-8 leading-relaxed">
                            Get personalized feedback from <span class="font-bold text-white">IELTS certified examiners</span> through interactive video sessions. Practice real exam scenarios and improve your speaking score with expert guidance.
                        </p>
                        
                        <!-- Key Features Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                            <div class="flex items-center space-x-3 bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <h3 class="font-bold text-white text-sm">Live Video Sessions</h3>
                                    <p class="text-xs text-gray-400">Real-time practice</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3 bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <h3 class="font-bold text-white text-sm">Certified Examiners</h3>
                                    <p class="text-xs text-gray-400">Expert guidance</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3 bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-yellow-400 to-orange-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <h3 class="font-bold text-white text-sm">Detailed Feedback</h3>
                                    <p class="text-xs text-gray-400">Score improvement tips</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3 bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <h3 class="font-bold text-white text-sm">Flexible Scheduling</h3>
                                    <p class="text-xs text-gray-400">Book anytime</p>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="flex flex-col sm:flex-row gap-4 items-center justify-center lg:justify-start">
                            <button onclick="notifyMe()" class="inline-flex items-center px-8 py-4 bg-[#C8102E] text-white font-bold text-lg rounded-xl hover:bg-[#A00E27] hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span>Notify Me When Live</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Right Visual - Video Call Mockup -->
                    <div class="relative lg:block hidden">
                        <div class="relative">
                            <!-- Main Video Frame -->
                            <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-3xl shadow-2xl overflow-hidden border-4 border-gray-700">
                                <!-- Video Header -->
                                <div class="bg-gray-900/80 backdrop-blur-sm px-6 py-4 flex items-center justify-between border-b border-gray-700">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex space-x-2">
                                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                        </div>
                                        <span class="text-white font-semibold text-sm">IELTS Speaking Session</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                        <span class="text-red-400 text-xs font-semibold">LIVE</span>
                                    </div>
                                </div>
                                
                                <!-- Video Grid -->
                                <div class="p-4 space-y-4">
                                    <!-- Examiner Video (Larger) -->
                                    <div class="relative bg-gradient-to-br from-blue-900/30 to-purple-900/30 rounded-2xl overflow-hidden border-2 border-blue-500/30 h-64">
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="text-center">
                                                <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-white font-semibold">Rocks Mike</p>
                                                
                                            </div>
                                        </div>
                                        <div class="absolute top-3 left-3 bg-black/60 backdrop-blur-sm px-3 py-1 rounded-full">
                                            <span class="text-white text-xs font-semibold">Examiner</span>
                                        </div>
                                        <div class="absolute bottom-3 right-3 flex space-x-2">
                                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Student Video (Smaller) -->
                                    <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl overflow-hidden border-2 border-gray-600 h-32">
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-full mx-auto mb-2 flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-white text-sm font-semibold">You</p>
                                            </div>
                                        </div>
                                        <div class="absolute top-2 left-2 bg-black/60 backdrop-blur-sm px-2 py-0.5 rounded-full">
                                            <span class="text-white text-xs font-semibold">Student</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Controls Bar -->
                                <div class="bg-gray-900/80 backdrop-blur-sm px-6 py-4 flex items-center justify-center space-x-4 border-t border-gray-700">
                                    <button class="w-12 h-12 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center transition-colors">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                        </svg>
                                    </button>
                                    <button class="w-12 h-12 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center transition-colors">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                    <button class="w-14 h-14 bg-red-600 hover:bg-red-700 rounded-full flex items-center justify-center transition-colors">
                                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }
        
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-10px) scale(1.05); }
        }
        
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.9; transform: scale(0.98); }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        .animate-bounce-slow {
            animation: bounce-slow 4s ease-in-out infinite;
        }
        
        .animate-pulse-slow {
            animation: pulse-slow 4s ease-in-out infinite;
        }
    </style>

    
    <x-slot name="scripts">
        <script>
            function watchDemo() {
                alert('Demo video coming soon! For now, sign up for free to explore.');
            }
            
            function notifyMe() {
                // Simple notification form
                const email = prompt('Enter your email to get notified when Speaking Coaching goes live:');
                if (email && email.trim() !== '') {
                    // Here you can add AJAX call to save email to database
                    alert('Thank you! We\'ll notify you at ' + email + ' when Speaking Coaching launches.');
                    // TODO: Send email to backend
                    console.log('Waitlist email:', email);
                } else if (email !== null) {
                    alert('Please enter a valid email address.');
                }
            }
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
        </script>
    </x-slot>
</x-guest-layout>
