{{-- resources/views/student/ai-tutor/index.blade.php --}}
<x-dashboard-layout>
    <x-slot:title>AI Tutor</x-slot>

    <div class="min-h-[80vh] flex items-center justify-center px-4">
        <div class="max-w-2xl w-full text-center">
            <!-- Animated Robot Icon -->
            <div class="relative inline-block mb-8">
                <div class="w-32 h-32 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-3xl flex items-center justify-center mx-auto shadow-xl shadow-emerald-200/50 animate-pulse">
                    <i class="fas fa-robot text-5xl text-emerald-600"></i>
                </div>
                <!-- Floating particles -->
                <div class="absolute -top-2 -right-2 w-6 h-6 bg-amber-400 rounded-full flex items-center justify-center animate-bounce">
                    <i class="fas fa-sparkles text-white text-xs"></i>
                </div>
                <div class="absolute -bottom-1 -left-3 w-5 h-5 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                <div class="absolute top-1/2 -right-4 w-4 h-4 bg-violet-400 rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
            </div>

            <!-- Main Heading -->
            <h1 class="text-4xl sm:text-5xl font-black text-gray-900 mb-4">
                AI Tutor is
                <span class="bg-gradient-to-r from-emerald-500 to-teal-500 bg-clip-text text-transparent">Coming Soon</span>
            </h1>

            <!-- Subheading -->
            <p class="text-xl text-gray-600 mb-8 max-w-lg mx-auto">
                Your personal IELTS coach powered by AI. Get instant feedback, practice conversations, and improve your band score.
            </p>

            <!-- Features Preview -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-comments text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Speaking Practice</h3>
                    <p class="text-sm text-gray-500">Real-time conversation with AI examiner</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="w-12 h-12 bg-violet-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-pen-fancy text-violet-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Writing Review</h3>
                    <p class="text-sm text-gray-500">Instant essay feedback & corrections</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-lightbulb text-amber-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Smart Tips</h3>
                    <p class="text-sm text-gray-500">Personalized improvement strategies</p>
                </div>
            </div>

            <!-- Stay Tuned Badge -->
            <div class="inline-flex items-center gap-3 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-full px-6 py-3">
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse" style="animation-delay: 0.2s;"></span>
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></span>
                </div>
                <span class="text-emerald-700 font-semibold">Stay Tuned! We're building something amazing</span>
            </div>
        </div>
    </div>
</x-dashboard-layout>
