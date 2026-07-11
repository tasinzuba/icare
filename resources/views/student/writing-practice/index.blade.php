<x-dashboard-layout>
    <x-slot name="title">Writing Practice</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb & Header -->
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4 text-sm">
                <a href="{{ route('student.dashboard') }}" class="text-gray-400 hover:text-[#C8102E] transition-smooth">
                    <i class="fas fa-home"></i>
                </a>
                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                <span class="text-gray-600 font-medium">Writing Practice</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-3">Writing Practice</h1>
            <p class="text-gray-600 max-w-2xl">
                Practice individual questions at your own pace. Perfect for targeted skill development.
            </p>
        </div>

        <!-- Task Selection Cards -->
        <div class="grid md:grid-cols-2 gap-6 max-w-4xl">
            <!-- Task 1 Card -->
            <a href="{{ route('student.writing-practice.task1') }}"
               class="group block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-smooth card-hover overflow-hidden">
                <div class="p-6">
                    <!-- Icon -->
                    <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center mb-5 group-hover:bg-blue-100 transition-smooth">
                        <i class="fas fa-chart-bar text-blue-600 text-xl"></i>
                    </div>

                    <!-- Title -->
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Task 1 Practice</h2>
                    <p class="text-gray-600 text-sm mb-5">Practice describing charts, graphs, tables, diagrams, and maps</p>

                    <!-- Stats -->
                    <div class="space-y-2 mb-5">
                        <div class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-clock w-4 mr-2"></i>
                            20 minutes suggested
                        </div>
                        <div class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-file-alt w-4 mr-2"></i>
                            Minimum 150 words
                        </div>
                    </div>

                    <!-- Button -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <span class="text-sm font-semibold text-blue-600 group-hover:text-blue-700">Browse Questions</span>
                        <i class="fas fa-arrow-right text-blue-600 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- Task 2 Card -->
            <a href="{{ route('student.writing-practice.task2') }}"
               class="group block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-smooth card-hover overflow-hidden">
                <div class="p-6">
                    <!-- Icon -->
                    <div class="w-14 h-14 bg-purple-50 rounded-xl flex items-center justify-center mb-5 group-hover:bg-purple-100 transition-smooth">
                        <i class="fas fa-pen-fancy text-purple-600 text-xl"></i>
                    </div>

                    <!-- Title -->
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Task 2 Practice</h2>
                    <p class="text-gray-600 text-sm mb-5">Practice writing essays on various topics and argument types</p>

                    <!-- Stats -->
                    <div class="space-y-2 mb-5">
                        <div class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-clock w-4 mr-2"></i>
                            40 minutes suggested
                        </div>
                        <div class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-file-alt w-4 mr-2"></i>
                            Minimum 250 words
                        </div>
                    </div>

                    <!-- Button -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <span class="text-sm font-semibold text-purple-600 group-hover:text-purple-700">Browse Questions</span>
                        <i class="fas fa-arrow-right text-purple-600 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Info Section -->
        <div class="mt-8 max-w-4xl bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-3">Practice Mode Benefits</h3>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-600 mt-0.5"></i>
                            <span>Focus on specific question types</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-600 mt-0.5"></i>
                            <span>Get human evaluation feedback (offline students)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-600 mt-0.5"></i>
                            <span>Practice scores saved separately from exam scores</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-600 mt-0.5"></i>
                            <span>No time pressure - take as long as you need</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
