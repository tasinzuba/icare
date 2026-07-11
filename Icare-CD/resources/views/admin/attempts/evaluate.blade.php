<x-admin-layout>
    <x-slot:title>Evaluate Attempt - Admin</x-slot>
    
    <x-slot:header>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Evaluate Student Attempt') }}
            </h2>
            <a href="{{ route('admin.attempts.show', $attempt) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">
                Back to Attempt
            </a>
        </div>
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-1">{{ $attempt->testSet->title }}</h2>
                        <p class="text-gray-500">{{ ucfirst($attempt->testSet->section->name) }} Section by {{ $attempt->user->name }}</p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Date Taken</p>
                                <p class="font-medium">{{ $attempt->created_at->format('F j, Y, g:i a') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500">Time Spent</p>
                                @php
                                    $startTime = $attempt->start_time;
                                    $endTime = $attempt->end_time ?? $attempt->updated_at;
                                    $timeSpent = $startTime->diffInMinutes($endTime);
                                @endphp
                                <p class="font-medium">{{ $timeSpent }} minutes</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <p class="font-medium capitalize">{{ $attempt->status }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Student Responses -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-4">Student Responses</h3>
                        
                        @if($attempt->testSet->section->name === 'writing')
                            @foreach($attempt->answers->sortBy('question.order_number') as $answer)
                                <div class="mb-6 pb-6 border-b border-gray-200 last:border-0">
                                    <h4 class="font-medium mb-2">Task {{ $answer->question->order_number }}</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                        <div class="prose prose-sm max-w-none">
                                            {!! nl2br(e($answer->answer)) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @elseif($attempt->testSet->section->name === 'speaking')
                            @foreach($attempt->answers->sortBy('question.order_number') as $answer)
                                <div class="mb-6 pb-6 border-b border-gray-200 last:border-0">
                                    <h4 class="font-medium mb-2">Question {{ $answer->question->order_number }}</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        @if($answer->speakingRecording)
                                            <audio controls class="w-full">
                                                <source src="{{ asset('storage/' . $answer->speakingRecording->file_path) }}" type="audio/mpeg">
                                                Your browser does not support the audio element.
                                            </audio>
                                        @else
                                            <p class="text-yellow-600">No recording available.</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-yellow-600">
                                    {{ ucfirst($attempt->testSet->section->name) }} section is automatically scored.
                                </p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Evaluation Form -->
                    <form action="{{ route('admin.attempts.evaluate', $attempt) }}" method="POST">
                        @csrf
                        
                        <div class="mb-6">
                            <label for="band_score" class="block text-sm font-medium text-gray-700 mb-1">Band Score</label>
                            <select id="band_score" name="band_score" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Select a band score</option>
                                @foreach(range(0, 9) as $whole)
                                    @foreach([0, 0.5] as $decimal)
                                        @if(!($whole == 0 && $decimal == 0.5) && !($whole == 9 && $decimal == 0.5))
                                            @php $score = $whole + $decimal; @endphp
                                            <option value="{{ $score }}" {{ old('band_score', $attempt->band_score) == $score ? 'selected' : '' }}>
                                                {{ number_format($score, 1) }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endforeach
                            </select>
                            @error('band_score')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-6">
                            <label for="feedback" class="block text-sm font-medium text-gray-700 mb-1">Feedback</label>
                            <textarea id="feedback" name="feedback" rows="6" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('feedback', $attempt->feedback) }}</textarea>
                            @error('feedback')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Submit Evaluation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>