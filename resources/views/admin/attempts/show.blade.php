<x-admin-layout>
    <x-slot:title>View Attempt - Admin</x-slot>
    
    <x-slot:header>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Attempt Details') }}
            </h2>
            <a href="{{ route('admin.attempts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-2"></i>Back to Attempts
            </a>
        </div>
    </x-slot:header>

    <style>
        /* Minimal Audio Player Styles */
        .audio-player-container {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-top: 12px;
        }
        
        .audio-player-container audio {
            width: 100%;
            height: 40px;
            outline: none;
        }
        
        .audio-meta {
            display: flex;
            gap: 12px;
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .audio-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        

        
        .no-recording {
            padding: 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            color: #991b1b;
            font-size: 14px;
            margin-top: 12px;
        }
        
        .question-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .question-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 12px;
            gap: 12px;
        }
        
        .question-number {
            font-weight: 600;
            color: #111827;
        }
        
        .question-text {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.5;
            margin: 8px 0;
        }
        
        .status-badge {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .status-recorded {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-missing {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Info Cards */
        .info-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .info-card-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .stat-item dt {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        
        .stat-item dd {
            font-size: 14px;
            color: #111827;
            font-weight: 500;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            
            <!-- Test Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Test Information -->
                <div class="info-card">
                    <h3 class="info-card-title">Test Information</h3>
                    <dl class="stat-grid">
                        <div class="stat-item">
                            <dt>Test Set</dt>
                            <dd>{{ $attempt->testSet->title }}</dd>
                        </div>
                        <div class="stat-item">
                            <dt>Section</dt>
                            <dd class="capitalize">{{ $attempt->testSet->section->name }}</dd>
                        </div>
                        <div class="stat-item">
                            <dt>Status</dt>
                            <dd>
                                @if ($attempt->status === 'completed')
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @elseif ($attempt->status === 'in_progress')
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800">
                                        In Progress
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">
                                        Abandoned
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="stat-item">
                            <dt>Band Score</dt>
                            <dd>
                                @if ($attempt->band_score)
                                    <span class="text-lg font-bold text-blue-600">{{ $attempt->band_score }}</span>
                                @else
                                    <span class="text-gray-500">Pending</span>
                                @endif
                            </dd>
                        </div>
                        <div class="stat-item">
                            <dt>Started At</dt>
                            <dd>{{ $attempt->start_time->format('M d, Y g:i a') }}</dd>
                        </div>
                        <div class="stat-item">
                            <dt>Completed At</dt>
                            <dd>{{ $attempt->end_time ? $attempt->end_time->format('M d, Y g:i a') : 'Not completed' }}</dd>
                        </div>
                        <div class="stat-item col-span-2">
                            <dt>Time Spent</dt>
                            <dd>
                                @php
                                    $startTime = $attempt->start_time;
                                    $endTime = $attempt->end_time ?? $attempt->updated_at;
                                    $timeSpent = $startTime->diffInMinutes($endTime);
                                @endphp
                                {{ $timeSpent }} minutes
                            </dd>
                        </div>
                    </dl>
                </div>
                
                <!-- Student Information -->
                <div class="info-card">
                    <h3 class="info-card-title">Student Information</h3>
                    <dl class="stat-grid">
                        <div class="stat-item">
                            <dt>Name</dt>
                            <dd>{{ $attempt->user->name }}</dd>
                        </div>
                        <div class="stat-item">
                            <dt>Email</dt>
                            <dd class="text-sm">{{ $attempt->user->email }}</dd>
                        </div>
                        <div class="stat-item">
                            <dt>Registered Since</dt>
                            <dd>{{ $attempt->user->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div class="stat-item">
                            <dt>Total Attempts</dt>
                            <dd>{{ $attempt->user->attempts->count() }} tests</dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <!-- Feedback Section -->
            @if($attempt->status === 'completed' && in_array($attempt->testSet->section->name, ['writing', 'speaking']))
                <div class="info-card mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="info-card-title mb-0">Evaluation & Feedback</h3>
                        
                        @if(!$attempt->band_score)
                            <a href="{{ route('admin.attempts.evaluate-form', $attempt) }}" 
                               class="px-4 py-2 bg-green-600 text-white rounded text-sm font-medium hover:bg-green-700">
                                Evaluate Now
                            </a>
                        @endif
                    </div>
                    
                    @if($attempt->band_score)
                        <div class="bg-gray-50 p-4 rounded border border-gray-200">
                            <div class="mb-3">
                                <span class="text-sm text-gray-600">Band Score:</span>
                                <span class="ml-2 text-2xl font-bold text-blue-600">{{ $attempt->band_score }}</span>
                            </div>
                            
                            @if($attempt->feedback)
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Feedback:</h4>
                                    <div class="text-gray-700 text-sm">
                                        {!! nl2br(e($attempt->feedback)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 p-3 rounded">
                            <p class="text-yellow-800 text-sm">This attempt has not been evaluated yet.</p>
                        </div>
                    @endif
                </div>
            @endif
            
            <!-- Student Responses Section - MINIMAL VERSION -->
            <div class="info-card">
                <h3 class="info-card-title mb-4">Student Responses</h3>
                
                @if($attempt->testSet->section->name === 'speaking')
                    <!-- Speaking Answers - MINIMAL -->
                    @php
                        $recordedCount = $attempt->answers->filter(fn($a) => $a->speakingRecording)->count();
                        $totalQuestions = $attempt->answers->count();
                        $percentage = $totalQuestions > 0 ? round(($recordedCount / $totalQuestions) * 100) : 0;
                    @endphp
                    
                    <!-- Simple Summary -->
                    <div class="bg-blue-50 border border-blue-200 p-3 rounded mb-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-medium text-gray-700">
                                Recordings: {{ $recordedCount }} / {{ $totalQuestions }}
                            </span>
                            <span class="text-gray-600">
                                {{ $percentage }}% complete
                            </span>
                        </div>
                    </div>
                    
                    <!-- Question List -->
                    @foreach($attempt->answers->sortBy('question.order_number') as $answer)
                        <div class="question-item">
                            <div class="question-header">
                                <div class="flex-1">
                                    <span class="question-number">
                                        Question {{ $answer->question->order_number }} 
                                        (Part {{ $answer->question->part_number }})
                                    </span>
                                </div>
                                <div>
                                    @if($answer->speakingRecording)
                                        <span class="status-badge status-recorded">Recorded</span>
                                    @else
                                        <span class="status-badge status-missing">Not Recorded</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="question-text">
                                {!! $answer->question->content !!}
                            </div>
                            
                            @if($answer->speakingRecording)
                                @php
                                    $recording = $answer->speakingRecording;
                                    $audioUrl = $recording->file_url ?? $recording->getFileUrlAttribute();
                                    $mimeType = $recording->mime_type ?? 'audio/webm';
                                @endphp
                                
                                <div class="audio-player-container">
                                    <audio controls preload="metadata">
                                        <source src="{{ $audioUrl }}" type="{{ $mimeType }}">
                                        Your browser does not support the audio element.
                                    </audio>
                                    
                                    <div class="audio-meta">
                                        <span>
                                            <i class="fas fa-database"></i>
                                            {{ strtoupper($recording->storage_disk ?? 'public') }}
                                        </span>
                                        @if($recording->file_size)
                                            <span>
                                                <i class="fas fa-file"></i>
                                                {{ $recording->formatted_size }}
                                            </span>
                                        @endif
                                        <span>
                                            <i class="fas fa-music"></i>
                                            {{ strtoupper(str_replace('audio/', '', $mimeType)) }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="no-recording">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No recording available
                                </div>
                            @endif
                        </div>
                    @endforeach
                    
                @elseif($attempt->testSet->section->name === 'writing')
                    <!-- Writing Answers -->
                    @foreach($attempt->answers->sortBy('question.order_number') as $answer)
                        <div class="question-item">
                            <div class="question-number">Task {{ $answer->question->order_number }}</div>
                            <div class="question-text">{!! $answer->question->content !!}</div>

                            @if($answer->question->media_url)
                                <div class="mt-3">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                        <i class="fas fa-image text-gray-400 mr-1"></i> Reference Image / Chart
                                    </p>
                                    <a href="{{ $answer->question->media_url }}" target="_blank" rel="noopener" class="inline-block">
                                        <img src="{{ $answer->question->media_url }}"
                                             alt="Task {{ $answer->question->order_number }} reference"
                                             class="rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition" style="max-width: 280px; width: 100%; height: auto;"
                                             loading="lazy">
                                    </a>
                                    <p class="text-xs text-gray-400 mt-1.5">Click image to open in new tab</p>
                                </div>
                            @endif

                            <div class="mt-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                    <i class="fas fa-pen text-gray-400 mr-1"></i> Student's Response
                                </p>
                                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                                    <div class="text-gray-800 text-sm whitespace-pre-wrap leading-relaxed">{{ $answer->answer }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                @else
                    <!-- Multiple Choice Answers -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Question</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student's Answer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Correct Answer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($attempt->answers->sortBy('question.order_number') as $answer)
                                    <tr>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="font-medium">{{ $answer->question->order_number }}.</span> 
                                            {{ Str::limit(strip_tags($answer->question->content), 60) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($answer->selectedOption)
                                                {{ $answer->selectedOption->content }}
                                            @elseif($answer->answer)
                                                {{ $answer->answer }}
                                            @else
                                                <span class="text-gray-400">No answer</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($answer->question->correctOption())
                                                {{ $answer->question->correctOption()->content }}
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($answer->selectedOption && $answer->selectedOption->is_correct)
                                                <span class="text-green-600 font-semibold">✓</span>
                                            @elseif($answer->selectedOption || $answer->answer)
                                                <span class="text-red-600 font-semibold">✗</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @php
                        $correctAnswers = $attempt->answers->filter(fn($a) => $a->selectedOption && $a->selectedOption->is_correct)->count();
                        $totalQuestions = $attempt->answers->count();
                        $accuracy = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
                    @endphp
                    
                    <div class="mt-4 grid grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-600">Correct</div>
                            <div class="text-lg font-bold">{{ $correctAnswers }} / {{ $totalQuestions }}</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-600">Accuracy</div>
                            <div class="text-lg font-bold">{{ number_format($accuracy, 1) }}%</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-600">Band Score</div>
                            @php
                                if ($attempt->testSet->section->name === 'listening') {
                                    $estimatedScore = App\Helpers\ScoreCalculator::calculateListeningBandScore($correctAnswers, $totalQuestions);
                                } else {
                                    $estimatedScore = App\Helpers\ScoreCalculator::calculateReadingBandScore($correctAnswers, $totalQuestions, $attempt->testSet->test_type ?? 'academic');
                                }
                            @endphp
                            <div class="text-lg font-bold text-blue-600">{{ $estimatedScore }}</div>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3 mt-6">
                @if($attempt->status === 'completed' && in_array($attempt->testSet->section->name, ['writing', 'speaking']) && !$attempt->band_score)
                    <a href="{{ route('admin.attempts.evaluate-form', $attempt) }}" 
                       class="px-4 py-2 bg-green-600 text-white rounded text-sm font-medium hover:bg-green-700">
                        Evaluate Attempt
                    </a>
                @endif
                
                <form action="{{ route('admin.attempts.destroy', $attempt) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this attempt?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded text-sm font-medium hover:bg-red-700">
                        Delete Attempt
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // Simple audio error handling
        document.addEventListener('DOMContentLoaded', function() {
            const audioElements = document.querySelectorAll('audio');
            
            audioElements.forEach(audio => {
                audio.addEventListener('error', function(e) {
                    console.error('Audio error:', e);
                    const container = audio.closest('.audio-player-container');
                    if (container) {
                        container.style.borderColor = '#fca5a5';
                        container.style.backgroundColor = '#fef2f2';
                    }
                });
            });
        });
    </script>
    @endpush
</x-admin-layout>
