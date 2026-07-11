@php
    use Illuminate\Support\Str;
@endphp
<x-test-layout>
    <x-slot name="title">IELTS Reading Test</x-slot>
    
    <x-slot name="meta">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    </x-slot>
    
 {{-- CSS Link --}}
@vite(['resources/css/reading-test.css', 'resources/css/test-notepad.css'])

    <!-- Universal Loading Screen Component -->
    <x-test-loading-screen />

    <!-- User Info Bar WITH Integrated Timer -->
    <div class="user-bar" style="height: 50px;">
        <div class="user-info">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ auth()->user()->name }} - BI {{ str_pad(auth()->id(), 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        
        {{-- Integrated Timer Component - Center Position --}}
        <div class="timer-center-wrapper">
            <x-test-timer 
                :attempt="$attempt" 
                auto-submit-form-id="reading-form"
                position="integrated"
                :warning-time="600"
                :danger-time="300"
            />
        </div>
        
        <div class="user-controls">
            <button class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm help-button" id="help-button">Help ?</button>
            <button class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm no-nav">Hide</button>
        </div>
    </div>


    <!-- Main Content Wrapper -->
    <div class="main-content-wrapper">
        <!-- Part Header Container -->
        <div class="global-part-header" id="global-part-header">
            <!-- Part header will be inserted here by JavaScript -->
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
        <!-- Reading Passage(s) Section -->
        <div class="passage-section" style="position: relative; z-index: 1;">
            @php
                // Get all passages ordered by part and order
                $passages = $testSet->questions
                    ->where('question_type', 'passage')
                    ->sortBy(['part_number', 'order_number']);
                
                // Group passages by part
                $passagesByPart = $passages->groupBy('part_number');
                
                // Get all questions excluding passages
                $allQuestions = $testSet->questions
                    ->where('question_type', '!=', 'passage')
                    ->sortBy(['part_number', 'order_number']);
                    
                // Build display array with proper numbering for blanks
                $displayQuestions = [];
                $currentQuestionNumber = 1;
                
                foreach ($allQuestions as $question) {
                    $blankCount = 0;
                    
                    // Special handling for master matching headings
                    if ($question->question_type === 'matching_headings' && $question->isMasterMatchingHeading()) {
                        $individualNumbers = $question->getIndividualQuestionNumbers();
                        $mappingCount = count($individualNumbers);
                        
                        if ($mappingCount > 0) {
                            // Store all question numbers for this master question
                            $questionNumbers = [];
                            for ($i = 0; $i < $mappingCount; $i++) {
                                $questionNumbers[$i] = $currentQuestionNumber + $i;
                            }
                            
                            $displayQuestions[] = [
                                'question' => $question,
                                'has_blanks' => false,
                                'is_master' => true,
                                'display_number' => $currentQuestionNumber,
                                'question_numbers' => $questionNumbers,
                                'count' => $mappingCount
                            ];
                            
                            $currentQuestionNumber += $mappingCount;
                        } else {
                            // Regular matching heading
                            $displayQuestions[] = [
                                'question' => $question,
                                'has_blanks' => false,
                                'display_number' => $currentQuestionNumber
                            ];
                            $currentQuestionNumber++;
                        }
                    } elseif ($question->question_type === 'multiple_choice') {
                        // Handle multiple choice questions with multiple correct answers
                        $correctCount = $question->options->filter(function($opt) { return $opt->is_correct; })->count();
                        
                        if ($correctCount > 1) {
                            // Store all question numbers for this multiple choice
                            $questionNumbers = [];
                            for ($i = 0; $i < $correctCount; $i++) {
                                $questionNumbers[$i] = $currentQuestionNumber + $i;
                            }
                            
                            $displayQuestions[] = [
                                'question' => $question,
                                'has_blanks' => false,
                                'is_multiple_choice' => true,
                                'display_number' => $currentQuestionNumber,
                                'question_numbers' => $questionNumbers,
                                'count' => $correctCount
                            ];
                            
                            $currentQuestionNumber += $correctCount;
                        } else {
                            // Single correct answer (treat as regular question)
                            $displayQuestions[] = [
                                'question' => $question,
                                'has_blanks' => false,
                                'display_number' => $currentQuestionNumber
                            ];
                            $currentQuestionNumber++;
                        }
                    } elseif ($question->question_type === 'sentence_completion' && isset($question->section_specific_data['sentence_completion'])) {
                        // Handle sentence completion with multiple sentences
                        $scData = $question->section_specific_data['sentence_completion'];
                        $sentenceCount = isset($scData['sentences']) ? count($scData['sentences']) : 0;
                        
                        if ($sentenceCount > 0) {
                            // Store all question numbers for this sentence completion
                            $questionNumbers = [];
                            for ($i = 0; $i < $sentenceCount; $i++) {
                                $questionNumbers[$i] = $currentQuestionNumber + $i;
                            }
                            
                            $displayQuestions[] = [
                                'question' => $question,
                                'has_blanks' => false,
                                'is_sentence_completion' => true,
                                'display_number' => $currentQuestionNumber,
                                'question_numbers' => $questionNumbers,
                                'count' => $sentenceCount
                            ];
                            
                            $currentQuestionNumber += $sentenceCount;
                        } else {
                            // Fallback for sentence completion without data
                            $displayQuestions[] = [
                                'question' => $question,
                                'has_blanks' => false,
                                'display_number' => $currentQuestionNumber
                            ];
                            $currentQuestionNumber++;
                        }
                    } else {
                        // Count blanks in this question
                        preg_match_all('/\[BLANK_\d+\]|\[____\d+____\]/', $question->content, $blankMatches);
                        preg_match_all('/\[DROPDOWN_\d+\]/', $question->content, $dropdownMatches);
                        preg_match_all('/\[HEADING_DROPDOWN_\d+\]/', $question->content, $headingDropdownMatches);
                        
                        // For dropdown_selection type, count dropdowns as individual questions
                        if ($question->question_type === 'dropdown_selection') {
                            $blankCount = count($dropdownMatches[0]);
                        } else {
                            $blankCount = count($blankMatches[0]) + count($dropdownMatches[0]) + count($headingDropdownMatches[0]);
                        }
                        
                        if ($blankCount > 0) {
                            // Store blank numbers for this question
                            $blankNumbers = [];
                            for ($i = 1; $i <= $blankCount; $i++) {
                                $blankNumbers[$i] = $currentQuestionNumber;
                                $currentQuestionNumber++;
                            }
                            
                            $displayQuestions[] = [
                                'question' => $question,
                                'has_blanks' => true,
                                'blank_numbers' => $blankNumbers,
                                'first_number' => $blankNumbers[1]
                            ];
                        } else {
                            // Regular question
                            $displayQuestions[] = [
                                'question' => $question,
                                'has_blanks' => false,
                                'display_number' => $currentQuestionNumber
                            ];
                            $currentQuestionNumber++;
                        }
                    }
                }
                
                $totalQuestionCount = $currentQuestionNumber - 1;
                $partsWithQuestions = $allQuestions->groupBy('part_number')->keys()->filter()->sort();
            @endphp
            
            @if ($passages->count() > 0)
                {{-- Show all passages for all parts --}}
                @foreach($partsWithQuestions as $partNumber)
                    <div class="passage-container {{ $loop->first ? 'active' : '' }}" 
                         data-part="{{ $partNumber }}"
                         id="passage-part-{{ $partNumber }}">
                        
                        @if($passagesByPart->has($partNumber))
                            {{-- Part has passages --}}
                            @foreach($passagesByPart[$partNumber] as $passage)
                                @php
                                    // Find matching headings questions for this part to get drop zones
                                    $matchingHeadingQuestions = $testSet->questions
                                        ->where('question_type', 'matching_headings')
                                        ->where('part_number', $partNumber);
                                    
                                    $dropZoneData = [];
                                    foreach($matchingHeadingQuestions as $mhQuestion) {
                                        if ($mhQuestion->isMasterMatchingHeading()) {
                                            $displayData = $mhQuestion->generateMatchingHeadingsDisplay();
                                            if (isset($displayData['questions'])) {
                                                foreach($displayData['questions'] as $qData) {
                                                    $dropZoneData[] = [
                                                        'question_id' => $mhQuestion->id,
                                                        'question_number' => $qData['number'] ?? $qData['question'] ?? 0,
                                                        'paragraph' => $qData['paragraph'] ?? 'A',
                                                        'headings' => $displayData['headings'] ?? []
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                
                                <div class="passage-content-wrapper" style="position: relative;">
                                    
                                    @php
                                        // Get matching headings questions for this part
                                        $mhQuestions = $testSet->questions
                                            ->where('question_type', 'matching_headings')
                                            ->where('part_number', $partNumber);
                                        
                                        $dropZones = [];
                                        foreach($mhQuestions as $mhQ) {
                                            try {
                                                if (method_exists($mhQ, 'isMasterMatchingHeading') && $mhQ->isMasterMatchingHeading()) {
                                                    $display = $mhQ->generateMatchingHeadingsDisplay();
                                                    if (isset($display['questions'])) {
                                                        foreach($display['questions'] as $q) {
                                                            $dropZones[] = [
                                                                'qid' => $mhQ->id,
                                                                'num' => $q['number'] ?? $q['question'] ?? 0,
                                                                'para' => $q['paragraph'] ?? 'A'
                                                            ];
                                                        }
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                // Skip if error
                                            }
                                        }
                                    @endphp
                                    
                                    {{-- Show drop zones if found --}}
                                    @if(count($dropZones) > 0)
                                        {{-- Drop zones will be inserted inline with paragraphs --}}
                                    @endif
                                    
                                    {{-- Passage content - Check both fields --}}
                                    @php
                                        $passageHtml = $passage->passage_text ?? $passage->content ?? '';
                                    @endphp
                                    
                                    @if($passageHtml)
                                        <div class="passage-content" id="passage-content-{{ $partNumber }}">
                                            {!! $passageHtml !!}
                                        </div>
                                        
                                        {{-- Inject Drop Zones via JavaScript - DYNAMIC DATA --}}
                                        <script>
                                            (function() {
                                                // Get dynamic drop zone data for this part
                                                const dropZones = [];
                                                
                                                @foreach($testSet->questions->where('question_type', 'matching_headings')->where('part_number', $partNumber) as $mhQ)
                                                    @if($mhQ->isMasterMatchingHeading())
                                                        @php
                                                            $display = $mhQ->generateMatchingHeadingsDisplay();
                                                        @endphp
                                                        @if(isset($display['questions']))
                                                            @foreach($display['questions'] as $q)
                                                                dropZones.push({
                                                                    para: '{{ $q["paragraph"] ?? "A" }}',
                                                                    num: {{ $q['number'] ?? $q['question'] ?? 0 }},
                                                                    qid: {{ $mhQ->id }}
                                                                });
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                @endforeach
                                                
                                                if (dropZones.length === 0) {
                                                    return;
                                                }
                                                
                                                const passageEl = document.getElementById('passage-content-{{ $partNumber }}');
                                                
                                                if (passageEl) {
                                                    const allPs = passageEl.querySelectorAll('p');
                                                    
                                                    dropZones.forEach(dz => {
                                                        allPs.forEach(p => {
                                                            const strong = p.querySelector('strong');
                                                            if (strong) {
                                                                const text = strong.textContent.trim().replace(/\s+/g, '');
                                                                
                                                                if (text === dz.para) {
                                                                    const box = document.createElement('div');
                                                                    box.style.cssText = 'margin: 10px 20% 8px 0;';
                                                                    box.innerHTML = `
                                                                        <div class="passage-drop-zone passage-drop-${dz.qid}"
                                                                             data-question-number="${dz.num}"
                                                                             data-paragraph="${dz.para}"
                                                                             style="width: 100%; min-height: 40px; border: 1px dashed #000000; border-radius: 4px; padding: 6px 12px; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                                                                            <div class="passage-empty-state" style="color: #000000; font-size: 13px; font-weight: 700; pointer-events: none;">
                                                                                ${dz.num}
                                                                            </div>
                                                                        </div>
                                                                        <input type="hidden" name="answers[${dz.qid}_q${dz.num}]" class="passage-answer-input" value="">
                                                                    `;
                                                                    
                                                                    // Insert AFTER the paragraph (next sibling)
                                                                    p.parentNode.insertBefore(box, p.nextSibling);
                                                                }
                                                            }
                                                        });
                                                    });
                                                }
                                            })();
                                        </script>
                                    @elseif($passage->content)
                                        <div class="passage-content">
                                            {!! $passage->content !!}
                                        </div>
                                    @endif
                                    
                                    @if ($passage->media_path)
                                        <div class="mt-4">
                                            <img src="{{ Storage::url($passage->media_path) }}" 
                                                 alt="Passage Image" 
                                                 class="max-w-full h-auto rounded border border-gray-200">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            {{-- Part has no passage - show message --}}
                            <div class="passage-content-wrapper">
                                <div class="no-passage-message">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <p>No reading passage available for Part {{ $partNumber }}.</p>
                                    <p class="text-sm mt-2">Questions are shown on the right side.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="no-passage-message">
                    <svg class="w-12 h-12 mx-auto mb-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p>No reading passages available for this test.</p>
                    <p class="text-sm mt-2">Please contact your administrator.</p>
                </div>
            @endif
        </div>

         <!-- Split Divider - Minimal Line with Icon -->
    <div class="split-divider" id="split-divider" title="Drag to resize | Double-click to reset" style="position: relative; z-index: 100;">
        <span></span>
    </div>
        
        <!-- Questions Section -->
        <div class="questions-section" style="position: relative; z-index: 1;">
            <form id="reading-form" action="{{ route('student.reading.submit', $attempt) }}" method="POST">
                @csrf
                
                @php
                    $groupedQuestions = collect($displayQuestions)->groupBy(function($item) {
                        return $item['question']->part_number;
                    });
                    $globalShownInstructions = [];
                    $globalProcessedQuestions = []; // Track globally processed questions
                @endphp
                
                @foreach ($groupedQuestions as $partNumber => $partQuestions)
                    <div class="part-questions" data-part="{{ $partNumber }}" style="{{ !$loop->first ? 'display: none;' : '' }}">
                        @php
                            // Calculate question range for this part
                            // Step 1: Count questions from ALL previous parts
                            $previousPartsCount = 0;
                            foreach ($displayQuestions as $item) {
                                if ($item['question']->part_number < $partNumber) {
                                    if ($item['has_blanks']) {
                                        $previousPartsCount += count($item['blank_numbers']);
                                    } else {
                                        $previousPartsCount++;
                                    }
                                }
                            }

                            // Step 2: Count questions in THIS part
                            $currentPartCount = 0;
                            foreach ($partQuestions as $pItem) {
                                if ($pItem['has_blanks']) {
                                    $currentPartCount += count($pItem['blank_numbers']);
                                } else {
                                    $currentPartCount++;
                                }
                            }

                            // Step 3: Calculate start and end
                            $startNumber = $previousPartsCount + 1;
                            $endNumber = $previousPartsCount + $currentPartCount;
                        @endphp
                        <div class="part-questions-inner" data-start-number="{{ $startNumber }}" data-end-number="{{ $endNumber }}" data-part-number="{{ $partNumber }}">
                        
                        @php
                        // Group questions by instruction first, then by group
                        $instructionGroups = [];
                        
                        foreach($partQuestions as $item) {
                            $question = $item['question'];
                            $instructionKey = $question->instructions ?: 'no-instruction-' . $question->id;
                            
                            if (!isset($instructionGroups[$instructionKey])) {
                                $instructionGroups[$instructionKey] = [];
                            }
                            $instructionGroups[$instructionKey][] = $item;
                        }
                        @endphp
                        
                        @foreach ($instructionGroups as $instructionKey => $instructionQuestions)
                            @php
                                $hasInstruction = !str_starts_with($instructionKey, 'no-instruction-');
                                $instructionText = $hasInstruction ? $instructionKey : '';
                                
                                // Check if any question in this group is already processed
                                $groupAlreadyProcessed = false;
                                foreach($instructionQuestions as $item) {
                                    if (in_array($item['question']->id, $globalProcessedQuestions)) {
                                        $groupAlreadyProcessed = true;
                                        break;
                                    }
                                }
                                
                                if ($groupAlreadyProcessed) {
                                    continue;
                                }
                            @endphp
                            
                            {{-- Show instruction once per unique instruction --}}
                            @if($hasInstruction && !isset($globalShownInstructions[$instructionKey]))
                                <div class="question-instructions" style="margin-bottom: 16px; font-weight: normal; color: #1f2937;">
                                    {!! $instructionText !!}
                                </div>
                                @php $globalShownInstructions[$instructionKey] = true; @endphp
                            @endif
                            
                            {{-- Now group by question_group within same instruction --}}
                            @php
                                $questionGroups = [];
                                foreach($instructionQuestions as $item) {
                                    $groupKey = $item['question']->question_group ?: 'no-group-' . $item['question']->id;
                                    if (!isset($questionGroups[$groupKey])) {
                                        $questionGroups[$groupKey] = [];
                                    }
                                    $questionGroups[$groupKey][] = $item;
                                }
                            @endphp
                            
                            @foreach ($questionGroups as $groupName => $questions)
                                @if($groupName && !str_starts_with($groupName, 'no-group-'))
                                    @php
                                        // Check if this group contains sentence completion questions
                                        $groupHasSentenceCompletion = false;
                                        foreach($questions as $item) {
                                            if($item['question']->question_type === 'sentence_completion') {
                                                $groupHasSentenceCompletion = true;
                                                break;
                                            }
                                        }
                                    @endphp

                                    @if(!$groupHasSentenceCompletion)
                                        <div class="question-group-header" style="margin-top: 20px; margin-bottom: 12px; font-weight: 700; font-size: 15px;">
                                            {{ $groupName }}
                                        </div>
                                    @endif
                                @endif

                                @foreach ($questions as $item)
                                    @php
                                        $question = $item['question'];
                                        $hasBlanks = $item['has_blanks'];
                                        
                                        // Mark this question as processed
                                        $globalProcessedQuestions[] = $question->id;
                                    @endphp
                                    
                                    <div class="ielts-question-item" id="question-{{ $question->id }}" style="margin-bottom: 24px;">

                                        @if($hasBlanks)
                                            {{-- Check if this is a heading dropdown question --}}
                                            @php
                                                $hasHeadingDropdowns = preg_match('/\[HEADING_DROPDOWN_\d+\]/', $question->content);
                                                $headingOptions = [];
                                                
                                                if ($hasHeadingDropdowns && $question->question_group) {
                                                    // Get heading options from matching_headings question in same group
                                                    $headingQuestion = $testSet->questions
                                                        ->where('question_type', 'matching_headings')
                                                        ->where('question_group', $question->question_group)
                                                        ->where('part_number', $question->part_number)
                                                        ->first();
                                                    
                                                    if ($headingQuestion && $headingQuestion->options) {
                                                        $headingOptions = $headingQuestion->options;
                                                    }
                                                }
                                            @endphp
                                            
                                            {{-- Show heading list if this is first heading dropdown question in group --}}
                                            @if($hasHeadingDropdowns && count($headingOptions) > 0)
                                                @php
                                                    $showHeadingsList = true;
                                                    if ($loop->index > 0) {
                                                        $prevQuestion = $questions[$loop->index - 1]['question'] ?? null;
                                                        if ($prevQuestion && $prevQuestion->question_group === $question->question_group && preg_match('/\[HEADING_DROPDOWN_\d+\]/', $prevQuestion->content)) {
                                                            $showHeadingsList = false;
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($showHeadingsList)
                                                    <div style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border: 1px solid #ddd;">
                                                        <div style="font-weight: bold; margin-bottom: 10px;">List of Headings</div>
                                                        @foreach ($headingOptions as $optionIndex => $option)
                                                            <div style="margin-bottom: 5px;">
                                                                {{ $option->content }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endif
                                            
                                            {{-- Fill-in-the-blanks question with simple numbered blanks --}}
                                            @php
                                                $processedContent = $question->content;
                                                $blankNumbers = $item['blank_numbers'];
                                                
                                                // Replace blanks with simple underline inputs
                                                $blankCounter = 0;
    $processedContent = preg_replace_callback('/\[BLANK_(\d+)\]|\[____(\d+)____\]/', function($matches) use ($question, &$blankCounter, $blankNumbers) {
        $blankCounter++;
        $displayNum = $blankNumbers[$blankCounter];
        
        return '<input type="text" 
                name="answers[' . $question->id . '][blank_' . $blankCounter . ']" 
                class="gap-input" 
                data-question-number="' . $displayNum . '"
                placeholder="' . $displayNum . '"
                autocomplete="off">';
    }, $processedContent);
                                                
                                                // Replace heading dropdowns
                                                $processedContent = preg_replace_callback('/\[HEADING_DROPDOWN_(\d+)\]/', function($matches) use ($question, &$blankCounter, $blankNumbers) {
                                                    $dropdownNum = $matches[1];
                                                    $blankCounter++;
                                                    $displayNum = $blankNumbers[$blankCounter];
                                                    
                                                    // Get heading options from the first matching_headings question in the group
                                                    $headingOptions = [];
                                                    if ($question->question_group) {
                                                        $headingQuestion = $question->testSet->questions()
                                                            ->where('question_type', 'matching_headings')
                                                            ->where('question_group', $question->question_group)
                                                            ->where('part_number', $question->part_number)
                                                            ->first();
                                                        
                                                        if ($headingQuestion && $headingQuestion->options) {
                                                            $headingOptions = $headingQuestion->options;
                                                        }
                                                    }
                                                    
                                                    $selectHtml = '<select name="answers[' . $question->id . '][heading_' . $dropdownNum . ']" 
                           class="gap-dropdown" 
                           data-question-number="' . $displayNum . '">
                   <option value="">' . $displayNum . '</option>';
                                                    
                                                    foreach ($headingOptions as $index => $option) {
                                                        $selectHtml .= '<option value="' . $option->id . '">' . chr(65 + $index) . '</option>';
                                                    }
                                                    
                                                    $selectHtml .= '</select>';
                                                    return $selectHtml;
                                                }, $processedContent);
                                                
                                                // Replace dropdowns similarly
                                                if ($question->section_specific_data) {
                                                    $dropdownOptions = $question->section_specific_data['dropdown_options'] ?? [];
                                                    
                                                    $processedContent = preg_replace_callback('/\[DROPDOWN_(\d+)\]/', function($matches) use ($question, $dropdownOptions, &$blankCounter, $blankNumbers) {
                                                        $dropdownNum = $matches[1];
                                                        $blankCounter++;
                                                        $displayNum = $blankNumbers[$blankCounter];
                                                        $options = isset($dropdownOptions[$dropdownNum]) ? explode(',', $dropdownOptions[$dropdownNum]) : [];
                                                        
                                                        $selectHtml = '<select name="answers[' . $question->id . '][dropdown_' . $dropdownNum . ']" 
                           class="gap-dropdown" 
                           data-question-number="' . $displayNum . '">
                   <option value="">' . $displayNum . '</option>';
                                                        
                                                        foreach ($options as $option) {
                                                            $selectHtml .= '<option value="' . trim($option) . '">' . trim($option) . '</option>';
                                                        }
                                                        
                                                        $selectHtml .= '</select>';
                                                        return $selectHtml;
                                                    }, $processedContent);
                                                }
                                            @endphp
                                            
                                            <div class="question-content">
                                                {!! $processedContent !!}
                                            </div>
                                        @else
                                            {{-- Regular question --}}
                                            @if($question->question_type !== 'sentence_completion' && $question->question_type !== 'dropdown_selection')
                                                @if(isset($item['is_multiple_choice']) && $item['is_multiple_choice'] && isset($item['count']) && $item['count'] > 1)
                                                    {{-- Multiple choice with range --}}
                                                    @php
                                                        $startNum = $item['display_number'];
                                                        $endNum = $startNum + $item['count'] - 1;
                                                    @endphp
                                                    <div class="ielts-q-number" style="font-weight: 700 !important; font-size: 14px !important; color: #000000 !important; line-height: 1.5 !important; margin-bottom: 10px !important; display: block !important; padding: 0 !important; background: none !important; border: none !important;">
                                                        <span style="font-weight: 700 !important;">Questions {{ $startNum }}-{{ $endNum }}</span>
                                                    </div>
                                                    <div style="margin-bottom: 8px; font-size: 14px; line-height: 1.6; color: #111827;">
                                                        {!! strip_tags($question->content) !!}
                                                    </div>
                                                @else
                                                    {{-- Single question number --}}
                                                    <div class="ielts-q-number" style="font-weight: 700 !important; font-size: 14px !important; color: #000000 !important; line-height: 1.5 !important; margin-bottom: 10px !important; display: block !important; padding: 0 !important; background: none !important; border: none !important;">
                                                        <span style="font-weight: 700 !important;">{{ $item['display_number'] }}.</span> {!! strip_tags($question->content) !!}
                                                    </div>
                                                @endif
                                            @endif
                                                
                                                @if ($question->media_path)
                                                    <div class="mb-3">
                                                        <img src="{{ Storage::url($question->media_path) }}" alt="Question Image" class="max-w-full h-auto rounded">
                                                    </div>
                                                @endif
                                                
                                            <div class="ielts-options" style="margin-left: 24px; margin-top: 8px;">
                                                @switch($question->question_type)
                                                    @case('single_choice')
                                                        <x-question-types.single-choice
                                                            :question="$question"
                                                            :displayNumber="$item['display_number']"
                                                        />
                                                        @break

                                                    @case('multiple_choice')
                                                        <x-question-types.multiple-choice
                                                            :question="$question"
                                                            :displayNumber="$item['display_number']"
                                                        />
                                                        @break

                                                    @case('true_false')
                                                    @case('yes_no')
                                                        <x-question-types.true-false
                                                            :question="$question"
                                                            :displayNumber="$item['display_number']"
                                                        />
                                                        @break
                                                    
                                                    @case('matching_headings')
                                                        {{-- DRAG & DROP Matching Headings Implementation --}}
                                                        @php
                                                            $displayData = $question->generateMatchingHeadingsDisplay();
                                                            $isFirstInGroup = true;
                                                            
                                                            // Check if this is first question in group
                                                            if ($question->question_group) {
                                                                foreach($questions as $prevItem) {
                                                                    if ($prevItem['question']->id == $question->id) break;
                                                                    if ($prevItem['question']->question_type === 'matching_headings' && 
                                                                        $prevItem['question']->question_group === $question->question_group) {
                                                                        $isFirstInGroup = false;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            
                                                            $matchingQuestionId = 'mh-' . $question->id;
                                                        @endphp
                                                        
                                                        @if($question->isMasterMatchingHeading())
                                                            {{-- Drag & Drop UI for Master Matching Headings --}}
                                                            
                                                            @if($isFirstInGroup && !empty($displayData['headings']))
                                                                {{-- Dragula CSS (only once) --}}
                                                                @once('dragula-css')
                                                                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.css">
                                                                <style>
                                                                    /* Heading items - smooth dragging */
                                                                    .mh-heading-item { 
                                                                        cursor: move !important; 
                                                                        user-select: none;
                                                                        -webkit-user-select: none;
                                                                        -moz-user-select: none;
                                                                        transition: box-shadow 0.2s ease, transform 0.2s ease;
                                                                    }
                                                                    
                                                                    /* Hover effect */
                                                                    .mh-heading-item:hover {
                                                                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                                                                        transform: translateY(-2px);
                                                                    }
                                                                    
                                                                    /* Heading inside ANY drop zone - remove border */
                                                                    .mh-drop-container .mh-heading-item,
                                                                    .passage-drop-zone .mh-heading-item {
                                                                        border: none !important;
                                                                        padding: 0 !important;
                                                                        background: transparent !important;
                                                                    }
                                                                    
                                                                    /* Mirror (the element being dragged) */
                                                                    .mh-heading-item.gu-mirror { 
                                                                        opacity: 0.9 !important; 
                                                                        cursor: grabbing !important;
                                                                        transform: rotate(3deg) !important;
                                                                        box-shadow: 0 15px 40px rgba(0,0,0,0.4) !important;
                                                                        pointer-events: none !important;
                                                                        z-index: 9999 !important;
                                                                        transition: none !important;
                                                                    }
                                                                    
                                                                    /* Transit (original position placeholder) */
                                                                    .gu-transit { 
                                                                        opacity: 0.3 !important;
                                                                        transition: none !important;
                                                                    }
                                                                    
                                                                    /* Drop containers hover */
                                                                    .mh-drop-container.gu-over,
                                                                    .passage-drop-zone.gu-over { 
                                                                        border-color: #666666 !important; 
                                                                        background-color: #f9fafb !important;
                                                                        border-style: solid !important;
                                                                        transition: all 0.15s ease !important;
                                                                    }
                                                                    
                                                                    /* Passage drop zones specific styling */
                                                                    .passage-drop-zone {
                                                                        transition: all 0.15s ease;
                                                                    }
                                                                    
                                                                    .passage-drop-zone .passage-empty-state {
                                                                        pointer-events: none;
                                                                    }
                                                                    
                                                                    /* Drop container with item - NO background color */
                                                                    .mh-drop-container.mh-has-item { 
                                                                        border-color: #000000 !important; 
                                                                        border-style: solid !important;
                                                                        background-color: transparent !important;
                                                                    }
                                                                    
                                                                    .mh-drop-container.mh-has-item .mh-empty-state { 
                                                                        display: none !important; 
                                                                    }
                                                                    
                                                                    /* Hide dragging from source */
                                                                    .gu-hide {
                                                                        display: none !important;
                                                                    }
                                                                    
                                                                    /* Smooth cursor during drag */
                                                                    .gu-unselectable {
                                                                        cursor: grabbing !important;
                                                                        user-select: none !important;
                                                                        -webkit-user-select: none !important;
                                                                    }
                                                                    
                                                                    /* Drop zone with heading - align left */
                                                                    .passage-drop-zone:has(.mh-heading-item) {
                                                                        justify-content: flex-start !important;
                                                                        width: auto !important;
                                                                        display: inline-flex !important;
                                                                    }
                                                                </style>
                                                                @endonce
                                                                
                                                                {{-- Headings List (Draggable) - MINIMAL with BORDER --}}
                                                                <div style="margin-bottom: 20px;">
                                                                    <div style="font-weight: 700; margin-bottom: 12px; font-size: 15px; color: #000000;">List of Headings</div>
                                                                    <div id="headings-source-{{ $matchingQuestionId }}" style="display: flex; flex-direction: column; gap: 6px; align-items: flex-start;">
                                                                        @foreach ($displayData['headings'] as $heading)
                                                                            <div class="mh-heading-item" 
                                                                                 data-heading="{{ $heading['letter'] }}"
                                                                                 draggable="true"
                                                                                 style="padding: 6px 10px; cursor: move; border: 1px solid #d1d5db; border-radius: 4px; background: #ffffff; display: inline-block;">
                                                                                <span style="color: #000000; font-size: 14px; line-height: 1.4;">{{ $heading['text'] }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            
                                                            {{-- NO DROP ZONES IN QUESTION PANEL - All in Passage --}}
                                                            
                                                            {{-- Dragula - Only Passage Drop Zones --}}
                                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.js"></script>
                                                            <script>
                                                                // Listen to ALL answer inputs for navigation update
                                                                document.addEventListener('DOMContentLoaded', function() {
                                                                    // Watch for changes in passage answer inputs
                                                                    document.addEventListener('change', function(e) {
                                                                        if (e.target.classList && e.target.classList.contains('passage-answer-input')) {
                                                                            const qNum = e.target.name.match(/_q(\d+)/);
                                                                            if (qNum && qNum[1]) {
                                                                                const questionNumber = qNum[1];
                                                                                const hasValue = e.target.value !== '';
                                                                                
                                                                                // Find button by number
                                                                                const allButtons = document.querySelectorAll('button');
                                                                                allButtons.forEach(btn => {
                                                                                    if (btn.textContent.trim() === questionNumber) {
                                                                                        if (hasValue) {
                                                                                            btn.classList.add('answered');
                                                                                        } else {
                                                                                            btn.classList.remove('answered');
                                                                                        }
                                                                                    }
                                                                                });
                                                                                console.log('✅ Nav updated for Q', questionNumber, hasValue ? 'answered' : 'cleared');
                                                                            }
                                                                        }
                                                                    });
                                                                });
                                                                
                                                                // Wait for drop zones to be injected, then initialize dragula
                                                                setTimeout(function() {
                                                                    console.log('=== DRAGULA INIT START ===');
                                                                    const qId = '{{ $matchingQuestionId }}';
                                                                    const source = document.getElementById('headings-source-' + qId);
                                                                    const passageDrops = Array.from(document.querySelectorAll('.passage-drop-zone'));

                                                                    console.log('Source:', !!source);
                                                                    console.log('Passage drops:', passageDrops.length);

                                                                    if (!source || passageDrops.length === 0) {
                                                                        console.error('❌ Cannot init dragula');
                                                                        return;
                                                                    }

                                                                    // CRITICAL: Initialize Dragula instance tracking for cleanup
                                                                    window.dragulaInstances = window.dragulaInstances || [];

                                                                    // CRITICAL: Destroy existing Dragula instance for this question if exists
                                                                    if (window.dragulaInstances.length > 0) {
                                                                        window.dragulaInstances.forEach((drake, index) => {
                                                                            if (drake && drake.containers && drake.containers.includes(source)) {
                                                                                console.log('Destroying existing Dragula for', qId);
                                                                                drake.destroy();
                                                                                window.dragulaInstances.splice(index, 1);
                                                                            }
                                                                        });
                                                                    }

                                                                    console.log('✅ Initializing dragula...');

                                                                    const drake = dragula([source, ...passageDrops], {
                                                                        accepts: function(el, target, source, sibling) {
                                                                            if (target === source) return true;
                                                                            
                                                                            if (target.classList && target.classList.contains('passage-drop-zone')) {
                                                                                const existing = target.querySelector('.mh-heading-item');
                                                                                return !existing || source === target;
                                                                            }
                                                                            
                                                                            return true;
                                                                        }
                                                                    });
                                                                    
                                                                    drake.on('drop', function(el, target, source, sibling) {
                                                                        const letter = el.dataset.heading;
                                                                        console.log('🎯 Dropped:', letter, 'into', target.dataset);
                                                                        
                                                                        // Handle drop in passage zones
                                                                        if (target.classList && target.classList.contains('passage-drop-zone')) {
                                                                            const empty = target.querySelector('.passage-empty-state');
                                                                            if (empty) empty.style.display = 'none';
                                                                            
                                                                            target.style.borderStyle = 'solid';
                                                                            
                                                                            // Make dropped heading text bold
                                                                            const headingSpan = el.querySelector('span');
                                                                            if (headingSpan) {
                                                                                headingSpan.style.fontWeight = '700';
                                                                            }
                                                                            
                                                                            const input = target.nextElementSibling;
                                                                            if (input && input.classList.contains('passage-answer-input')) {
                                                                                input.value = letter;
                                                                                // Save to localStorage
                                                                                const attemptId = '{{ $attempt->id }}';
                                                                                const storageKey = `reading_test_${attemptId}_answers`;
                                                                                let savedAnswers = JSON.parse(localStorage.getItem(storageKey) || '{}');
                                                                                savedAnswers[input.name] = letter;
                                                                                localStorage.setItem(storageKey, JSON.stringify(savedAnswers));
                                                                                
                                                                                // Trigger change event for answer tracking
                                                                                const changeEvent = new Event('change', { bubbles: true });
                                                                                input.dispatchEvent(changeEvent);
                                                                                
                                                                                // Update bottom navigation
                                                                                const qNum = target.dataset.questionNumber;
                                                                                if (qNum) {
                                                                                    console.log('🔍 Looking for nav button with Q:', qNum);
                                                                                    
                                                                                    // Debug: Show ALL buttons
                                                                                    const allButtons = document.querySelectorAll('button');
                                                                                    console.log('Total buttons:', allButtons.length);
                                                                                    
                                                                                    // Show all button texts
                                                                                    allButtons.forEach((btn, i) => {
                                                                                        console.log(`Button ${i}: "${btn.textContent.trim()}" classes:`, btn.className);
                                                                                    });
                                                                                    
                                                                                    // Find by text OR data attribute
                                                                                    let navBtn = null;
                                                                                    allButtons.forEach(btn => {
                                                                                        const text = btn.textContent.trim();
                                                                                        const dataQ = btn.getAttribute('data-question') || btn.getAttribute('data-question-number');
                                                                                        
                                                                                        if (text === qNum.toString() || dataQ == qNum) {
                                                                                            console.log('🎯 MATCH:', btn);
                                                                                            navBtn = btn;
                                                                                        }
                                                                                    });
                                                                                    
                                                                                    if (navBtn) {
                                                                                        console.log('✅ FOUND and styling:', navBtn);
                                                                                        navBtn.style.cssText = 'background-color: #10b981 !important; color: #ffffff !important;';
                                                                                    } else {
                                                                                        console.log('❌ NOT FOUND for Q', qNum);
                                                                                    }
                                                                                }
                                                                                
                                                                                // CRITICAL: Update answered count immediately
                                                                                setTimeout(() => {
                                                                                    const countEl = document.getElementById('answered-count');
                                                                                    if (countEl) {
                                                                                        // Count only visible inputs and passage answers with values
                                                                                        let count = 0;
                                                                                        
                                                                                        // Count visible form inputs (selects, visible inputs)
                                                                                        const visibleInputs = document.querySelectorAll('select[name^="answers"], input[name^="answers"][type="text"], textarea[name^="answers"]');
                                                                                        visibleInputs.forEach(inp => {
                                                                                            if (inp.value && inp.value.trim() !== '' && inp.offsetParent !== null) {
                                                                                                count++;
                                                                                            }
                                                                                        });
                                                                                        
                                                                                        // Count passage answers (hidden inputs with class)
                                                                                        const passageInputs = document.querySelectorAll('.passage-answer-input');
                                                                                        passageInputs.forEach(inp => {
                                                                                            if (inp.value && inp.value.trim() !== '') {
                                                                                                count++;
                                                                                            }
                                                                                        });

                                                                                        countEl.textContent = count;
                                                                                    }
                                                                                }, 100);
                                                                            }
                                                                        }
                                                                        
                                                                        // Clear source if it was a drop zone OR back to list
                                                                        if (source.classList && source.classList.contains('passage-drop-zone')) {
                                                                            const empty = source.querySelector('.passage-empty-state');
                                                                            if (empty) empty.style.display = 'block';
                                                                            
                                                                            source.style.borderStyle = 'dashed';
                                                                            
                                                                            const input = source.nextElementSibling;
                                                                            if (input && input.classList.contains('passage-answer-input')) {
                                                                                const qNum = source.dataset.questionNumber;
                                                                                input.value = '';
                                                                                console.log('✅ Answer cleared:', input.name);
                                                                                
                                                                                // Trigger change event
                                                                                const changeEvent = new Event('change', { bubbles: true });
                                                                                input.dispatchEvent(changeEvent);
                                                                                
                                                                                // Update bottom navigation to unanswered
                                                                                if (qNum) {
                                                                                    // Direct update
                                                                                    const navBtn = document.querySelector(`.question-nav-btn[data-question="${qNum}"]`);
                                                                                    if (navBtn) {
                                                                                        navBtn.classList.remove('answered');
                                                                                        navBtn.classList.add('unanswered');
                                                                                        navBtn.removeAttribute('style'); // Remove inline style
                                                                                        console.log('✅ Nav button cleared for Q', qNum);
                                                                                    }
                                                                                    
                                                                                    // Also try window function
                                                                                    if (window.updateQuestionStatus) {
                                                                                        window.updateQuestionStatus(qNum, 'unanswered');
                                                                                    }
                                                                                }
                                                                                
                                                                                // Update count
                                                                                setTimeout(() => {
                                                                                    const countEl = document.getElementById('answered-count');
                                                                                    if (countEl) {
                                                                                        let count = 0;
                                                                                        
                                                                                        // Count visible inputs
                                                                                        const visibleInputs = document.querySelectorAll('select[name^="answers"], input[name^="answers"][type="text"], textarea[name^="answers"]');
                                                                                        visibleInputs.forEach(inp => {
                                                                                            if (inp.value && inp.value.trim() !== '' && inp.offsetParent !== null) {
                                                                                                count++;
                                                                                            }
                                                                                        });
                                                                                        
                                                                                        // Count passage answers
                                                                                        const passageInputs = document.querySelectorAll('.passage-answer-input');
                                                                                        passageInputs.forEach(inp => {
                                                                                            if (inp.value && inp.value.trim() !== '') {
                                                                                                count++;
                                                                                            }
                                                                                        });
                                                                                        
                                                                                        countEl.textContent = count;
                                                                                        console.log('📊 Updated count to:', count);
                                                                                    }
                                                                                }, 100);
                                                                            }
                                                                        }
                                                                        
                                                                        // Reset heading text weight when going back to list
                                                                        if (target.id && target.id.includes('headings-source')) {
                                                                            const headingSpan = el.querySelector('span');
                                                                            if (headingSpan) {
                                                                                headingSpan.style.fontWeight = '400';
                                                                            }
                                                                        }
                                                                    });

                                                                    // CRITICAL: Store drake instance for cleanup
                                                                    window.dragulaInstances.push(drake);

                                                                    console.log('=== DRAGULA READY ===');

                                                                    // CRITICAL: Restore saved answers on page load
                                                                    setTimeout(() => {
                                                                        const attemptId = '{{ $attempt->id }}';
                                                                        const storageKey = `reading_test_${attemptId}_answers`;
                                                                        const savedAnswers = JSON.parse(localStorage.getItem(storageKey) || '{}');
                                                                        
                                                                        Object.keys(savedAnswers).forEach(inputName => {
                                                                            const letter = savedAnswers[inputName];
                                                                            const input = document.querySelector(`input[name="${inputName}"]`);
                                                                            
                                                                            if (input && letter) {
                                                                                input.value = letter;
                                                                                
                                                                                const dropZone = input.previousElementSibling;
                                                                                if (dropZone && dropZone.classList.contains('passage-drop-zone')) {
                                                                                    const heading = document.querySelector(`.mh-heading-item[data-heading="${letter}"]`);
                                                                                    
                                                                                    if (heading) {
                                                                                        const empty = dropZone.querySelector('.passage-empty-state');
                                                                                        if (empty) empty.style.display = 'none';
                                                                                        
                                                                                        dropZone.style.borderStyle = 'solid';
                                                                                        dropZone.appendChild(heading);
                                                                                        
                                                                                        const span = heading.querySelector('span');
                                                                                        if (span) span.style.fontWeight = '700';
                                                                                    }
                                                                                }
                                                                            }
                                                                        });
                                                                    }, 1500);
                                                                }, 1000); // Wait 1 second for injection
                                                            </script>
                                                        @else
                                                            {{-- Non-master fallback (keep old dropdown) --}}
                                                            <div style="margin-left: 24px; display: flex; align-items: center; gap: 10px;">
                                                                <select name="answers[{{ $question->id }}]" 
                                                                        data-question-number="{{ $item['display_number'] }}" 
                                                                        style="width: 60px; padding: 5px; border: 1px solid #ccc;">
                                                                    <option value=""></option>
                                                                    @foreach ($question->options as $optionIndex => $option)
                                                                        <option value="{{ $option->id }}">{{ chr(65 + $optionIndex) }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <span>{{ strip_tags($question->content) }}</span>
                                                            </div>
                                                        @endif
                                                        @break
                                                        
                                                    @case('matching')
                                                    @case('matching_information')
                                                    @case('matching_features')
                                                        <x-question-types.matching-dropdown
                                                            :question="$question"
                                                            :displayNumber="$item['display_number']"
                                                        />
                                                        @break

                                                    @case('fill_blanks')
                                                    @case('dropdown_selection')
                                                        <x-question-types.dropdown-selection
                                                            :question="$question"
                                                            :displayNumber="$item['display_number']"
                                                        />
                                                        @break
                                                    
                                                @case('sentence_completion')
                                                        {{-- Enhanced Sentence Completion Display --}}
                                                        @php
                                                            $sectionData = $question->section_specific_data;
                                                            $hasSentenceCompletionData = isset($sectionData['sentence_completion']);
                                                        @endphp
                                                        
                                                        @if($hasSentenceCompletionData)
                                                            @php
                                                                $scData = $sectionData['sentence_completion'];
                                                                
                                                                $showWordList = true;
                                                                
                                                                // Check if word list already shown in this group
                                                                if ($question->question_group && isset($globalShownInstructions[$question->question_group . '_sc_wordlist'])) {
                                                                    $showWordList = false;
                                                                } elseif ($question->question_group) {
                                                                    $globalShownInstructions[$question->question_group . '_sc_wordlist'] = true;
                                                                }
                                                                
                                                                // Get question range for title
                                                                $startNum = $item['display_number'];
                                                                $sentenceCount = isset($scData['sentences']) ? count($scData['sentences']) : 0;
                                                                $endNum = $startNum + $sentenceCount - 1;
                                                            @endphp
                                                            
                                                            {{-- Question Title with Range --}}
                                                            <div style="margin-bottom: 16px; font-weight: 700; font-size: 15px; color: #111827;">
                                                                Questions {{ $startNum }}-{{ $endNum }}
                                                            </div>
                                                            
                                                            @if($showWordList)
                                                                {{-- Word List Box - Simple Black-White Design --}}
                                                                @if(isset($scData['options']) && count($scData['options']) > 0)
                                                                    <div class="word-list-box">
                                                                        <div style="font-weight: 600; margin-bottom: 12px; font-size: 15px; color: #000000; display: flex; align-items: center;">
                                                                            <svg style="width: 18px; height: 18px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                                            </svg>
                                                                            Word List
                                                                        </div>
                                                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px;">
                                                                            @foreach($scData['options'] as $option)
                                                                                <div class="word-list-item">
                                                                                    <strong style="color: #000000; font-size: 15px;">{{ $option['id'] }}</strong>
                                                                                    <span style="color: #333333; margin-left: 6px;">{{ $option['text'] }}</span>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                            
                                                            {{-- Sentences --}}
                                                            @if(isset($scData['sentences']))
                                                                <div style="margin-top: 15px;">
                                                                    @foreach($scData['sentences'] as $sentenceIndex => $sentence)
                                                                        @php
                                                                            // Get the display number from the item's question_numbers array
                                                                            $displayNum = isset($item['question_numbers'][$sentenceIndex]) ? $item['question_numbers'][$sentenceIndex] : ($item['display_number'] + $sentenceIndex);
                                                                            $sentenceText = $sentence['text'];
                                                                            
                                                                            // Replace [GAP] with dropdown - ULTRA COMPACT WITH PROPER ALIGNMENT
                                                                            $dropdownHtml = '<select name="answers[' . $question->id . '_q' . $displayNum . ']" '
                                                                                          . 'class="sc-dropdown visible-dropdown" '
                                                                                          . 'data-question-number="' . $displayNum . '" '
                                                                                          . 'data-question-id="' . $question->id . '" '
                                                                                          . 'style="display: inline-block; margin: 0 4px; padding: 2px 6px; border: 1px solid #666666; border-radius: 2px; font-size: 12px; font-weight: 500; min-width: 40px; max-width: 50px; background: #ffffff; color: #000000; cursor: pointer; vertical-align: baseline; line-height: normal;">';
                                                                            $dropdownHtml .= '<option value="" style="color: #666666;">Select</option>';
                                                                            
                                                                            foreach($scData['options'] as $option) {
                                                                                $dropdownHtml .= '<option value="' . $option['id'] . '">' . $option['id'] . '</option>';
                                                                            }
                                                                            
                                                                            $dropdownHtml .= '</select>';
                                                                            
                                                                            $processedText = str_replace('[GAP]', $dropdownHtml, $sentenceText);
                                                                            
                                                                            // SMART FALLBACK: Handle cases where [GAP] might be missing or malformed
                                                                            if (strpos($processedText, '<select') === false) {
                                                                                // No dropdown found in processed text, add it intelligently
                                                                                if (trim($sentenceText)) {
                                                                                    // If sentence ends with punctuation, add dropdown before it
                                                                                    if (preg_match('/[.!?]\s*$/', $sentenceText)) {
                                                                                        $processedText = preg_replace('/([.!?]\s*)$/', ' ' . $dropdownHtml . '$1', $sentenceText);
                                                                                    } else {
                                                                                        // Add dropdown at the end
                                                                                        $processedText = trim($sentenceText) . ' ' . $dropdownHtml;
                                                                                    }
                                                                                } else {
                                                                                    // Empty sentence, just show dropdown
                                                                                    $processedText = $dropdownHtml;
                                                                                }
                                                                            }
                                                                        @endphp
                                                                        
                                                                        <div style="margin-bottom: 10px; display: flex; align-items: baseline;">
                                                                            <span style="font-weight: 700; min-width: 35px; margin-right: 8px; font-size: 14px; color: #1f2937;">{{ $displayNum }}.</span>
                                                                            <div style="flex: 1; font-size: 14px; line-height: 1.6; color: #374151;">{!! $processedText !!}</div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @else
                                                            {{-- Fallback to old display --}}
                                                            <input type="text" 
                                                                   name="answers[{{ $question->id }}]" 
                                                                   style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;"
                                                                   placeholder="Enter your answer"
                                                                   maxlength="100"
                                                                   data-question-number="{{ $item['display_number'] }}">
                                                        @endif
                                                        @break
                                                    @case('summary_completion')
                                                    @case('short_answer')
                                                    @default
                                                        <x-question-types.text-input
                                                            :question="$question"
                                                            :displayNumber="$item['display_number']"
                                                        />
                                                        @break
                                                @endswitch
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        @endforeach
                        </div> <!-- End part-questions-inner -->
                    </div>
                @endforeach
                
                <button type="submit" id="submit-button" class="hidden" onclick="console.log('Submit button clicked')">Submit</button>
            </form>
        </div>
        </div> <!-- End content-area -->
    </div> <!-- End main-content-wrapper -->

    <!-- Bottom Navigation -->
    <div class="bottom-nav" style="height: 60px;">
        <div class="nav-left">
            <div class="review-section">
    <input type="checkbox" id="review-checkbox" class="review-check">
    <label for="review-checkbox" class="review-label">Flag</label>
</div>
            
            <div class="nav-section-container">
                <span class="section-label">Reading</span>
                
                {{-- Parts Navigation - Show all parts --}}
                <div class="parts-nav">
                    @foreach($partsWithQuestions as $partNum)
                        <button type="button" class="part-btn {{ $loop->first ? 'active' : '' }}" data-part="{{ $partNum }}">
                            Part {{ $partNum }}
                        </button>
                    @endforeach
                </div>
                
                {{-- Question Numbers --}}
                <div class="nav-numbers">
                    @foreach($displayQuestions as $item)
                        @if($item['has_blanks'])
                            {{-- Show number for each blank --}}
                            @foreach($item['blank_numbers'] as $blankIndex => $number)
                                <div class="number-btn {{ $loop->parent->first && $loop->first ? 'active' : '' }}" 
                                     data-question="{{ $item['question']->id }}"
                                     data-blank="{{ $blankIndex }}"
                                     data-display-number="{{ $number }}"
                                     data-part="{{ $item['question']->part_number }}">
                                    {{ $number }}
                                </div>
                            @endforeach
                        @elseif(isset($item['is_master']) && $item['is_master'])
                            {{-- Master matching heading buttons --}}
                            @foreach($item['question_numbers'] as $subIndex => $number)
                                <div class="number-btn {{ $loop->parent->first && $loop->first ? 'active' : '' }}" 
                                     data-question="{{ $item['question']->id }}"
                                     data-sub-question="{{ $subIndex }}"
                                     data-display-number="{{ $number }}"
                                     data-part="{{ $item['question']->part_number }}">
                                    {{ $number }}
                                </div>
                            @endforeach
                        @elseif(isset($item['is_multiple_choice']) && $item['is_multiple_choice'] && isset($item['count']) && $item['count'] > 1)
                            {{-- Multiple choice buttons --}}
                            @foreach($item['question_numbers'] as $subIndex => $number)
                                <div class="number-btn {{ $loop->parent->first && $loop->first ? 'active' : '' }}" 
                                     data-question="{{ $item['question']->id }}"
                                     data-sub-question="{{ $subIndex }}"
                                     data-display-number="{{ $number }}"
                                     data-part="{{ $item['question']->part_number }}">
                                    {{ $number }}
                                </div>
                            @endforeach
                        @elseif(isset($item['is_sentence_completion']) && $item['is_sentence_completion'])
                            {{-- Sentence completion buttons --}}
                            @foreach($item['question_numbers'] as $subIndex => $number)
                                <div class="number-btn {{ $loop->parent->first && $loop->first ? 'active' : '' }}" 
                                     data-question="{{ $item['question']->id }}"
                                     data-sub-question="{{ $subIndex }}"
                                     data-display-number="{{ $number }}"
                                     data-part="{{ $item['question']->part_number }}">
                                    {{ $number }}
                                </div>
                            @endforeach
                        @else
                            {{-- Regular question button --}}
                            <div class="number-btn {{ $loop->first ? 'active' : '' }}" 
                                 data-question="{{ $item['question']->id }}"
                                 data-display-number="{{ $item['display_number'] }}"
                                 data-part="{{ $item['question']->part_number }}">
                                {{ $item['display_number'] }}
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="nav-right">
            <button type="button" id="submit-test-btn" class="submit-test-button">
                Submit Test
            </button>
        </div>
    </div>

    <!-- Submit Modal -->
    <div id="submit-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-title" style="color: #059669;">Submit Test?</div>
            <div class="modal-message">
                Are you sure you want to submit your test? You cannot change your answers after submission.
            </div>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button class="modal-button" id="confirm-submit-btn">Yes, Submit</button>
                <button class="modal-button secondary" id="cancel-submit-btn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Help Guide Modal (Inline Implementation) -->
    <div id="help-modal" class="help-modal-overlay" style="display: none;">
        <div class="help-modal-container">
            <!-- Header -->
            <div class="help-modal-header">
                <div class="help-header-content">
                    <svg class="help-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="help-modal-title">Test Guide</h2>
                </div>
                <button class="help-close-btn" id="help-close-btn">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
         
            
            <!-- Dynamic Content Area -->
            <div class="help-content-area" id="help-content">
                <!-- Content will be loaded here -->
            </div>
            
            <!-- Footer -->
            <div class="help-modal-footer">
                <div class="help-footer-left">
                    <span class="help-version">RX 1.0</span>
                </div>
                <div class="help-footer-right">
                    <button class="help-btn-secondary" id="help-video-btn">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Watch Tutorial
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // ====================================
    // Prevent Back Navigation During Test
    // ====================================
    (function() {
        // Push a dummy state to prevent going back
        history.pushState(null, null, location.href);

        window.addEventListener('popstate', function(event) {
            // Push state again to keep user on the test page
            history.pushState(null, null, location.href);

            // Show submit modal instead of allowing back navigation
            const submitModal = document.getElementById('submit-modal');
            if (submitModal) {
                submitModal.style.display = 'flex';
            }
        });

        // Also prevent on initial load
        window.addEventListener('load', function() {
            history.pushState(null, null, location.href);
        });
    })();
    </script>

    <script>
    // ====================================
    // AGGRESSIVE Ctrl+F Find Disabler
    // ====================================
    
    // Method 1: keydown event (Primary)
    document.addEventListener('keydown', function(e) {
        // Cmd+F (Mac) or Ctrl+F (Windows)
        if ((e.ctrlKey === true || e.metaKey === true) && (e.key === 'f' || e.key === 'F')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.warn('❌ Find disabled - Cmd+F blocked');
            return false;
        }
    }, true);
    
    // Method 2: keyup event (Backup)
    document.addEventListener('keyup', function(e) {
        if ((e.ctrlKey === true || e.metaKey === true) && (e.key === 'f' || e.key === 'F')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.warn('❌ Find disabled - Cmd+F blocked (keyup)');
            return false;
        }
    }, true);
    
    // Method 3: Check for keyboard event with code
    document.addEventListener('keydown', function(e) {
        // F keyCode: 70, MetaLeft: 91, ControlLeft: 17
        if ((e.metaKey || e.ctrlKey) && e.keyCode === 70) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);
    
    // Method 4: Disable via window object
    window.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && (e.key === 'f' || e.keyCode === 70)) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    }, true);

    window.testConfig = {
        attemptId: {{ $attempt->id }},
        testSetId: {{ $testSet->id }},
        totalQuestions: {{ $totalQuestionCount }}
    };
    
    // Debug form submission
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reading-form');
        const submitBtn = document.getElementById('submit-button');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submission triggered');
                
                // Check if there are any required fields
                const requiredFields = form.querySelectorAll('[required]');
                let hasEmptyRequired = false;
                
                requiredFields.forEach(field => {
                    if (!field.value || field.value.trim() === '') {
                        console.error('Empty required field:', field.name);
                        hasEmptyRequired = true;
                    }
                });
                
                if (hasEmptyRequired) {
                    console.error('Form has empty required fields');
                }
                
                // Log all form data
                const formData = new FormData(form);
                console.log('Form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
            });
        }
    });
</script>

@vite('resources/js/reading-test.js')
<script src="{{ asset('js/matching-headings-enhanced-fix.js') }}"></script>
<script src="{{ asset('js/sentence-completion-handler.js') }}"></script>
<script src="{{ asset('js/student/multiple-choice-handler.js') }}"></script>

<!-- CLEAN MINIMAL DROPDOWN STYLING -->
<style>
.sc-dropdown, .visible-dropdown {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 999 !important;
    margin: 0 4px !important;
    padding: 2px 6px !important;
    border: 1px solid #666666 !important;
    border-radius: 2px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    min-width: 40px !important;
    max-width: 50px !important;
    background: #ffffff !important;
    color: #000000 !important;
    cursor: pointer !important;
    appearance: auto !important;
    -webkit-appearance: menulist !important;
    -moz-appearance: menulist !important;
    vertical-align: baseline !important;
    line-height: normal !important;
}

.sc-dropdown:hover, .visible-dropdown:hover {
    background: #f5f5f5 !important;
    border-color: #333333 !important;
}

.sc-dropdown:focus, .visible-dropdown:focus {
    outline: none !important;
    border-color: #000000 !important;
    background: #ffffff !important;
}

.sc-dropdown[data-answered="true"], .visible-dropdown[data-answered="true"] {
    background: #f0f0f0 !important;
    border-color: #333333 !important;
    color: #000000 !important;
    font-weight: 600 !important;
}

.sc-dropdown option, .visible-dropdown option {
    padding: 8px 12px !important;
    font-size: 14px !important;
    background-color: white !important;
    color: #000000 !important;
}

.sc-dropdown option:first-child, .visible-dropdown option:first-child {
    color: #666666 !important;
    font-style: italic !important;
}

/* Force all parent containers to show dropdowns */
.ielts-question-item, .sentence-preview, div[style*="display: flex"] {
    overflow: visible !important;
}

/* Make sure dropdowns are always on top */
select[name*="_q"] {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 999 !important;
    margin: 0 4px !important;
    padding: 2px 6px !important;
    border: 1px solid #666666 !important;
    background: #ffffff !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    color: #000000 !important;
    border-radius: 2px !important;
    cursor: pointer !important;
    min-width: 40px !important;
    max-width: 50px !important;
    vertical-align: baseline !important;
    line-height: normal !important;
}

/* Simple navigation button styles */
.number-btn.answered {
    background: #10b981 !important; /* Green for answered questions */
    color: white !important;
    font-weight: 600 !important;
}

/* Simple black-white word list styling */
.word-list-box {
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 6px;
    padding: 16px;
    margin-bottom: 20px;
}

.word-list-item {
    background: #ffffff;
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #cccccc;
    text-align: center;
    margin-bottom: 8px;
}

.word-list-item:hover {
    background: #f5f5f5;
    border-color: #999999;
}

/* Dropdown Wrapper - Fixed Width for Consistency */
.dropdown-wrapper {
    position: relative !important;
    display: inline-block !important;
    width: 130px !important;
    vertical-align: middle !important;
}

/* Dropdown Select */
.dropdown {
    width: 100% !important;
    padding: 0 20px 0 8px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 4px !important;
    background-color: #fff !important;
    color: #374151 !important;
    font-size: 13px !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    outline: none !important;
    height: 36px !important;
    line-height: 34px !important;
    text-overflow: ellipsis !important;
    overflow: hidden !important;
    white-space: nowrap !important;
}

.dropdown:hover {
    border-color: #9ca3af !important;
}

.dropdown:focus {
    outline: none !important;
    border-color: #6b7280 !important;
    box-shadow: 0 0 0 2px rgba(156, 163, 175, 0.15) !important;
}

/* SVG Arrow */
.dropdown-arrow {
    position: absolute !important;
    right: 8px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    width: 12px !important;
    height: 12px !important;
    color: #6b7280 !important;
    pointer-events: none !important;
}

/* Answered State */
.dropdown[data-answered="true"] {
    background-color: #f0fdf4 !important;
    border-color: #86efac !important;
}

.dropdown-selection-question {
    line-height: 2 !important;
    font-size: 14px !important;
    color: #111827 !important;
}
</style>

<script>
// CRITICAL FIX: Copy passage answer inputs to form before submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reading-form');

    if (form) {
        // Intercept form submission
        form.addEventListener('submit', function(e) {
            // Remove beforeunload listener to prevent "Leave page?" popup
            window.removeEventListener('beforeunload', preventLeave);
            window.onbeforeunload = null;

            // Find all passage answer inputs (outside form)
            const passageInputs = document.querySelectorAll('.passage-answer-input');

            // Copy each to form
            passageInputs.forEach(input => {
                if (input.value && input.value !== '') {
                    const clone = input.cloneNode(true);
                    form.appendChild(clone);
                }
            });
        });
    }
    
    // Function to update answered count including passage answers
    window.updateTotalAnsweredCount = function() {
        let count = 0;
        const countedKeys = new Set();
        
        // 1. Count regular selects (multiple choice, true/false, etc)
        document.querySelectorAll('select[name^="answers"]').forEach(inp => {
            if (inp.value && inp.value.trim() !== '' && !countedKeys.has(inp.name)) {
                countedKeys.add(inp.name);
                count++;
            }
        });
        
        // 2. Count text inputs (but NOT blanks which we count separately)
        document.querySelectorAll('input[name^="answers"][type="text"]').forEach(inp => {
            // Skip if it's a blank (has [blank_] in name)
            if (inp.name.includes('[blank_')) return;
            
            if (inp.value && inp.value.trim() !== '' && !countedKeys.has(inp.name)) {
                countedKeys.add(inp.name);
                count++;
            }
        });
        
        // 3. Count textareas
        document.querySelectorAll('textarea[name^="answers"]').forEach(inp => {
            if (inp.value && inp.value.trim() !== '' && !countedKeys.has(inp.name)) {
                countedKeys.add(inp.name);
                count++;
            }
        });
        
        // 4. Count radio buttons (checked ones only)
        const radioGroups = new Set();
        document.querySelectorAll('input[name^="answers"][type="radio"]:checked').forEach(inp => {
            if (!radioGroups.has(inp.name)) {
                radioGroups.add(inp.name);
                count++;
            }
        });
        
        // 5. Count checkboxes (checked ones only)
        document.querySelectorAll('input[name^="answers"][type="checkbox"]:checked').forEach(inp => {
            if (!countedKeys.has(inp.name)) {
                countedKeys.add(inp.name);
                count++;
            }
        });
        
        // 6. Count fill blanks (each blank separately)
        document.querySelectorAll('input[name*="[blank_"]').forEach(inp => {
            if (inp.value && inp.value.trim() !== '' && !countedKeys.has(inp.name)) {
                countedKeys.add(inp.name);
                count++;
            }
        });
        
        // 7. Count dropdowns in array format
        document.querySelectorAll('select[name*="[dropdown_"]').forEach(inp => {
            if (inp.value && inp.value.trim() !== '' && !countedKeys.has(inp.name)) {
                countedKeys.add(inp.name);
                count++;
            }
        });
        
        // 8. Count passage answers (matching headings)
        document.querySelectorAll('.passage-answer-input').forEach(inp => {
            if (inp.value && inp.value.trim() !== '' && !countedKeys.has(inp.name)) {
                countedKeys.add(inp.name);
                count++;
            }
        });
        
        // Update display
        const countEl = document.getElementById('answered-count');
        if (countEl) {
            countEl.textContent = count;
        }
        
        return count;
    };
    
    // CRITICAL: Function to update ALL navigation colors (blanks + passage answers)
    window.updateAllNavigationColors = function() {
        // Update passage answers (matching headings)
        const passageInputs = document.querySelectorAll('.passage-answer-input');
        passageInputs.forEach(inp => {
            if (inp.value && inp.value.trim() !== '') {
                const match = inp.name.match(/_q(\d+)/);
                if (match) {
                    const qNum = match[1];
                    const navBtn = document.querySelector(`.number-btn[data-display-number="${qNum}"]`);
                    if (navBtn) {
                        navBtn.classList.add('answered');
                        navBtn.style.cssText = 'background-color: #10b981 !important; color: #ffffff !important;';
                    }
                }
            } else {
                const match = inp.name.match(/_q(\d+)/);
                if (match) {
                    const qNum = match[1];
                    const navBtn = document.querySelector(`.number-btn[data-display-number="${qNum}"]`);
                    if (navBtn) {
                        navBtn.classList.remove('answered');
                        navBtn.style.cssText = '';
                    }
                }
            }
        });
        
        // Update fill blanks
        const blankInputs = document.querySelectorAll('[name*="[blank_"]');
        blankInputs.forEach(inp => {
            if (inp.value && inp.value.trim() !== '') {
                const qNum = inp.dataset.questionNumber;
                if (qNum) {
                    const navBtn = document.querySelector(`.number-btn[data-display-number="${qNum}"]`);
                    if (navBtn) {
                        navBtn.classList.add('answered');
                        navBtn.style.cssText = 'background-color: #10b981 !important; color: #ffffff !important;';
                    }
                }
            } else {
                const qNum = inp.dataset.questionNumber;
                if (qNum) {
                    const navBtn = document.querySelector(`.number-btn[data-display-number="${qNum}"]`);
                    if (navBtn) {
                        navBtn.classList.remove('answered');
                        navBtn.style.cssText = '';
                    }
                }
            }
        });
    };
    
    // ========== GLOBAL INTERVAL TRACKING ==========
    window.readingTestIntervals = window.readingTestIntervals || [];
    window.readingTestObserver = null;
    window.isUpdatingNav = false; // Flag to prevent infinite MutationObserver loop

    // Watch for navigation buttons to be added to DOM
    // FIXED: Only observe briefly after DOM loads, not continuously
    const observer = new MutationObserver((mutations) => {
        // CRITICAL: Prevent infinite loop - skip if we're already updating
        if (window.isUpdatingNav) return;

        // Only process mutations that are actually relevant (question inputs added)
        let hasRelevantChanges = false;
        for (const mutation of mutations) {
            if (mutation.addedNodes.length > 0) {
                for (const node of mutation.addedNodes) {
                    if (node.nodeType === 1 && (
                        node.classList?.contains('passage-drop-zone') ||
                        node.classList?.contains('number-btn') ||
                        node.querySelector?.('.passage-drop-zone') ||
                        node.querySelector?.('.number-btn')
                    )) {
                        hasRelevantChanges = true;
                        break;
                    }
                }
            }
            if (hasRelevantChanges) break;
        }

        if (hasRelevantChanges) {
            window.isUpdatingNav = true;
            window.updateAllNavigationColors();
            window.isUpdatingNav = false;
        }
    });

    // Store observer for cleanup
    window.readingTestObserver = observer;

    // Start observing when DOM is ready - DISCONNECT after initial load
    setTimeout(() => {
        const body = document.body;
        observer.observe(body, { childList: true, subtree: true });

        // Initial call
        window.isUpdatingNav = true;
        window.updateAllNavigationColors();
        window.isUpdatingNav = false;

        // CRITICAL: Disconnect observer after 5 seconds - DOM should be stable by then
        setTimeout(() => {
            observer.disconnect();
            console.log('MutationObserver disconnected - DOM stable');
        }, 5000);
    }, 2000);

    // Store interval IDs for cleanup - use DEBOUNCED updates (2 seconds instead of 500ms)
    const countIntervalId = setInterval(window.updateTotalAnsweredCount, 2000);
    const navIntervalId = setInterval(() => {
        if (!window.isUpdatingNav) {
            window.isUpdatingNav = true;
            window.updateAllNavigationColors();
            window.isUpdatingNav = false;
        }
    }, 2000);

    window.readingTestIntervals.push(countIntervalId, navIntervalId);

    // Cleanup function for all intervals and observer
    window.cleanupReadingTest = function() {
        console.log('Cleaning up reading test resources...');

        // Clear all intervals
        if (window.readingTestIntervals) {
            window.readingTestIntervals.forEach(id => {
                if (id) clearInterval(id);
            });
            window.readingTestIntervals = [];
        }

        // Disconnect observer
        if (window.readingTestObserver) {
            window.readingTestObserver.disconnect();
            window.readingTestObserver = null;
        }

        // Destroy Dragula instances
        if (window.dragulaInstances) {
            window.dragulaInstances.forEach(drake => {
                if (drake && typeof drake.destroy === 'function') {
                    drake.destroy();
                }
            });
            window.dragulaInstances = [];
        }

        console.log('Reading test cleanup complete');
    };

    // Cleanup on page unload
    window.addEventListener('beforeunload', window.cleanupReadingTest);
    window.addEventListener('unload', window.cleanupReadingTest);
});
</script>

    @endpush
    
</x-test-layout>