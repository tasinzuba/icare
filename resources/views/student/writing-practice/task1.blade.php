<x-dashboard-layout>
    <x-slot name="title">Task 1 Practice Questions</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Task 1 Practice Questions</h1>
            <p class="text-gray-600">{{ $questions->count() }} {{ Str::plural('question', $questions->count()) }} available</p>
        </div>

        @if($questions->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-16 h-16 bg-[#C8102E]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-[#C8102E] text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">No Questions Available</h3>
                <p class="text-gray-600">Task 1 practice questions will appear here when available</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($questions as $question)
                    <a href="{{ route('student.writing-practice.question', $question) }}"
                       class="block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:border-[#C8102E]/30 transition-all cursor-pointer">
                        <div class="p-4">
                            <!-- Question Number and Title -->
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-br from-[#C8102E] to-[#A00E27] rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold">{{ $loop->iteration }}</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-gray-900 leading-tight">
                                        {{ $question->getDisplayTitle() }}
                                    </h3>
                                </div>
                            </div>

                            <!-- Chart Preview (if exists) -->
                            @if($question->media_path)
                                <div class="mt-4">
                                    <img src="{{ $question->media_url }}"
                                         alt="Chart"
                                         class="w-full h-32 object-cover rounded-lg border border-gray-200">
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-dashboard-layout>
