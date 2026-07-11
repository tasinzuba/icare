{{-- resources/views/student/test/index.blade.php --}}
<x-student-layout>
    <x-slot:title>Practice Tests</x-slot>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 via-transparent to-pink-600/20"></div>
        
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-pink-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        </div>
        
        <div class="relative px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="max-w-7xl mx-auto">
                <div class="text-center">
                    <h1 class="text-5xl lg:text-6xl font-bold text-white mb-6 animated-gradient bg-clip-text text-transparent">
                        Practice Tests
                    </h1>
                    <p class="text-gray-300 text-xl max-w-3xl mx-auto">
                        Master all four IELTS modules with our comprehensive practice tests
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Full Test Section -->
    <section class="px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-7xl mx-auto">
            <!-- Full Test Card -->
            <div class="glass rounded-2xl p-8 mb-12 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
                <!-- Premium Badge -->
                <div class="absolute top-4 right-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-amber-500 to-yellow-500 text-white shadow-lg">
                        <i class="fas fa-crown mr-1"></i>
                        Premium Feature
                    </span>
                </div>
                
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/10 to-purple-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div class="mb-6 md:mb-0">
                            <h2 class="text-3xl font-bold text-white mb-3 flex items-center">
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center mr-4 neon-purple">
                                    <i class="fas fa-file-alt text-white text-2xl"></i>
                                </div>
                                Full IELTS Tests
                            </h2>
                            <p class="text-gray-300 text-lg mb-4">Experience the complete IELTS exam with all four modules in one sitting</p>
                            
                            <!-- Features -->
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center text-sm text-gray-400">
                                    <i class="fas fa-clock text-indigo-400 mr-2"></i>
                                    <span>~3 hours duration</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-400">
                                    <i class="fas fa-chart-line text-purple-400 mr-2"></i>
                                    <span>Overall band score</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-400">
                                    <i class="fas fa-tasks text-pink-400 mr-2"></i>
                                    <span>Real exam experience</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('student.full-test.index') }}" class="btn-primary group">
                            <span>Start Full Test</span>
                            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Individual Sections -->
            <h2 class="text-2xl font-bold text-white mb-6">Practice Individual Sections</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    // Get user's completed tests count for each section
                    $listeningCompleted = auth()->user()->attempts()
                        ->whereHas('testSet.section', function($q) { $q->where('name', 'listening'); })
                        ->where('status', 'completed')
                        ->distinct('test_set_id')
                        ->count('test_set_id');
                        
                    $readingCompleted = auth()->user()->attempts()
                        ->whereHas('testSet.section', function($q) { $q->where('name', 'reading'); })
                        ->where('status', 'completed')
                        ->distinct('test_set_id')
                        ->count('test_set_id');
                        
                    $writingCompleted = auth()->user()->attempts()
                        ->whereHas('testSet.section', function($q) { $q->where('name', 'writing'); })
                        ->where('status', 'completed')
                        ->distinct('test_set_id')
                        ->count('test_set_id');
                        
                    $speakingCompleted = auth()->user()->attempts()
                        ->whereHas('testSet.section', function($q) { $q->where('name', 'speaking'); })
                        ->where('status', 'completed')
                        ->distinct('test_set_id')
                        ->count('test_set_id');
                        
                    // Get retakes count
                    $listeningRetakes = auth()->user()->attempts()
                        ->whereHas('testSet.section', function($q) { $q->where('name', 'listening'); })
                        ->where('is_retake', true)
                        ->count();
                        
                    $readingRetakes = auth()->user()->attempts()
                        ->whereHas('testSet.section', function($q) { $q->where('name', 'reading'); })
                        ->where('is_retake', true)
                        ->count();
                        
                    $writingRetakes = auth()->user()->attempts()
                        ->whereHas('testSet.section', function($q) { $q->where('name', 'writing'); })
                        ->where('is_retake', true)
                        ->count();
                        
                    $speakingRetakes = auth()->user()->attempts()
                        ->whereHas('testSet.section', function($q) { $q->where('name', 'speaking'); })
                        ->where('is_retake', true)
                        ->count();
                @endphp
                
                <!-- Listening Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-600 to-purple-600 rounded-2xl blur opacity-50 group-hover:opacity-100 transition-all duration-300"></div>
                    <a href="{{ route('student.listening.index') }}" 
                       class="relative block">
                        <div class="glass rounded-2xl p-8 hover:border-violet-500/50 transition-all duration-300 hover:-translate-y-2">
                            <!-- Icon -->
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform neon-purple">
                                <i class="fas fa-headphones text-white text-3xl"></i>
                            </div>
                            
                            <!-- Content -->
                            <h3 class="text-2xl font-bold text-white mb-3 text-center">Listening</h3>
                            
                            @if($listeningCompleted > 0)
                                <div class="text-center mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ $listeningCompleted }} Completed
                                        @if($listeningRetakes > 0)
                                            <span class="ml-2 text-purple-400">| {{ $listeningRetakes }} Retakes</span>
                                        @endif
                                    </span>
                                </div>
                            @endif
                            
                            <!-- Details -->
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Duration</span>
                                    <span class="text-white font-medium">30 minutes</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Questions</span>
                                    <span class="text-white font-medium">40 questions</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Format</span>
                                    <span class="text-white font-medium">4 parts</span>
                                </div>
                            </div>
                            
                            <!-- CTA -->
                            <div class="flex items-center justify-center text-violet-400 font-medium group-hover:text-violet-300">
                                Start Practice
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Reading Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 to-green-600 rounded-2xl blur opacity-50 group-hover:opacity-100 transition-all duration-300"></div>
                    <a href="{{ route('student.reading.index') }}" 
                       class="relative block">
                        <div class="glass rounded-2xl p-8 hover:border-emerald-500/50 transition-all duration-300 hover:-translate-y-2">
                            <!-- Icon -->
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform neon-blue">
                                <i class="fas fa-book-open text-white text-3xl"></i>
                            </div>
                            
                            <!-- Content -->
                            <h3 class="text-2xl font-bold text-white mb-3 text-center">Reading</h3>
                            
                            @if($readingCompleted > 0)
                                <div class="text-center mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ $readingCompleted }} Completed
                                        @if($readingRetakes > 0)
                                            <span class="ml-2 text-purple-400">| {{ $readingRetakes }} Retakes</span>
                                        @endif
                                    </span>
                                </div>
                            @endif
                            
                            <!-- Details -->
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Duration</span>
                                    <span class="text-white font-medium">60 minutes</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Questions</span>
                                    <span class="text-white font-medium">40 questions</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Format</span>
                                    <span class="text-white font-medium">3 passages</span>
                                </div>
                            </div>
                            
                            <!-- CTA -->
                            <div class="flex items-center justify-center text-emerald-400 font-medium group-hover:text-emerald-300">
                                Start Practice
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Writing Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-600 to-orange-600 rounded-2xl blur opacity-50 group-hover:opacity-100 transition-all duration-300"></div>
                    <a href="{{ route('student.writing.index') }}" 
                       class="relative block">
                        <div class="glass rounded-2xl p-8 hover:border-amber-500/50 transition-all duration-300 hover:-translate-y-2">
                            <!-- Icon -->
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform neon-pink">
                                <i class="fas fa-pen-fancy text-white text-3xl"></i>
                            </div>
                            
                            <!-- Content -->
                            <h3 class="text-2xl font-bold text-white mb-3 text-center">Writing</h3>
                            
                            @if($writingCompleted > 0)
                                <div class="text-center mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ $writingCompleted }} Completed
                                        @if($writingRetakes > 0)
                                            <span class="ml-2 text-purple-400">| {{ $writingRetakes }} Retakes</span>
                                        @endif
                                    </span>
                                </div>
                            @endif
                            
                            <!-- Details -->
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Duration</span>
                                    <span class="text-white font-medium">60 minutes</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Tasks</span>
                                    <span class="text-white font-medium">2 tasks</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Words</span>
                                    <span class="text-white font-medium">150 & 250</span>
                                </div>
                            </div>
                            
                            <!-- CTA -->
                            <div class="flex items-center justify-center text-amber-400 font-medium group-hover:text-amber-300">
                                Start Practice
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Speaking Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600 to-pink-600 rounded-2xl blur opacity-50 group-hover:opacity-100 transition-all duration-300"></div>
                    <a href="{{ route('student.speaking.index') }}" 
                       class="relative block">
                        <div class="glass rounded-2xl p-8 hover:border-rose-500/50 transition-all duration-300 hover:-translate-y-2">
                            <!-- Icon -->
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-500 flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform neon-pink">
                                <i class="fas fa-microphone text-white text-3xl"></i>
                            </div>
                            
                            <!-- Content -->
                            <h3 class="text-2xl font-bold text-white mb-3 text-center">Speaking</h3>
                            
                            @if($speakingCompleted > 0)
                                <div class="text-center mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ $speakingCompleted }} Completed
                                        @if($speakingRetakes > 0)
                                            <span class="ml-2 text-purple-400">| {{ $speakingRetakes }} Retakes</span>
                                        @endif
                                    </span>
                                </div>
                            @endif
                            
                            <!-- Details -->
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Duration</span>
                                    <span class="text-white font-medium">11-14 min</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Parts</span>
                                    <span class="text-white font-medium">3 parts</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Format</span>
                                    <span class="text-white font-medium">1-on-1</span>
                                </div>
                            </div>
                            
                            <!-- CTA -->
                            <div class="flex items-center justify-center text-rose-400 font-medium group-hover:text-rose-300">
                                Start Practice
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-student-layout>