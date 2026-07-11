@if($question->question_type === 'matching_headings')
    @php
        $matchingData = $question->getMatchingHeadingsData();
        $headings = $matchingData['headings'] ?? [];
        $mappings = $matchingData['mappings'] ?? [];
        $questionId = $question->id;
    @endphp
    
    <!-- Dragula CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.css">
    
    <!-- Instructions -->
    <div class="matching-headings-section mb-8" data-question-id="{{ $questionId }}">
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <p class="text-gray-700">{{ $question->instructions ?? 'Choose the correct heading for each paragraph from the list of headings below.' }}</p>
        </div>
        
        <!-- Drag and Drop Container -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- LEFT SIDE: List of Headings (Draggable) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">List of Headings</h3>
                    <div id="headings-source-{{ $questionId }}" class="space-y-3">
                        @foreach($question->options as $index => $option)
                            <div class="heading-item bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-3 cursor-move hover:shadow-md transition-all duration-200" 
                                 data-heading="{{ chr(65 + $index) }}"
                                 draggable="true">
                                <div class="flex items-start">
                                    <span class="font-bold mr-2 text-blue-600 text-lg">{{ chr(65 + $index) }}</span>
                                    <span class="text-gray-700 text-sm leading-relaxed">{{ $option->content }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- RIGHT SIDE: Questions with Drop Zones -->
            <div class="lg:col-span-2">
                <div class="space-y-4">
                    @foreach($mappings as $mapping)
                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
                            <div class="flex items-start gap-4">
                                <!-- Question Number -->
                                <div class="flex-shrink-0">
                                    <span class="bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold text-lg shadow-md">
                                        {{ $mapping['question'] }}
                                    </span>
                                </div>
                                
                                <!-- Paragraph Label & Drop Zone -->
                                <div class="flex-grow">
                                    <div class="mb-2">
                                        <span class="text-gray-700 font-semibold text-base">
                                            Paragraph {{ $mapping['paragraph'] }}
                                        </span>
                                    </div>
                                    
                                    <!-- Drop Zone Box -->
                                    <div class="drop-zone min-h-[80px] border-2 border-dashed border-gray-300 rounded-lg p-4 bg-white hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 relative"
                                         data-question-number="{{ $mapping['question'] }}"
                                         data-paragraph="{{ $mapping['paragraph'] }}">
                                        
                                        <div class="empty-state text-center text-gray-400 text-sm">
                                            <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            </svg>
                                            Drag heading here
                                        </div>
                                        
                                        <div class="dropped-heading hidden">
                                            <!-- Heading will be placed here after drop -->
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden Input for Form Submission -->
                                    <input type="hidden" 
                                           name="answers[{{ $mapping['question'] }}]" 
                                           class="answer-input"
                                           value="">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
        </div>
    </div>

    <!-- Inline Styles -->
    <style>
        .heading-item {
            touch-action: none;
        }
        .heading-item.gu-mirror {
            opacity: 0.8;
            transform: rotate(2deg);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .drop-zone.gu-over {
            border-color: #3b82f6 !important;
            background-color: #dbeafe !important;
        }
        .drop-zone.has-heading {
            border-color: #10b981;
            background-color: #d1fae5;
        }
        .drop-zone.has-heading .empty-state {
            display: none;
        }
        .drop-zone.has-heading .dropped-heading {
            display: block;
        }
        .gu-transit {
            opacity: 0.5;
        }
    </style>

    <!-- Dragula JS and Initialization -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.js"></script>
    <script>
        (function() {
            const questionId = {{ $questionId }};
            const sourceContainer = document.getElementById('headings-source-' + questionId);
            const dropZones = document.querySelectorAll('[data-question-id="{{ $questionId }}"] .drop-zone');
            
            if (!sourceContainer || !dropZones.length) {
                console.error('Matching headings containers not found');
                return;
            }
            
            // Initialize Dragula
            const drake = dragula([sourceContainer, ...dropZones], {
                copy: function (el, source) {
                    return source === sourceContainer;
                },
                accepts: function (el, target) {
                    return target !== sourceContainer;
                },
                removeOnSpill: true
            });
            
            // On Drop Event
            drake.on('drop', function(el, target, source) {
                if (target && target.classList.contains('drop-zone')) {
                    const headingLetter = el.dataset.heading;
                    const headingText = el.querySelector('span:last-child').textContent;
                    const questionNumber = target.dataset.questionNumber;

                    // Remove any existing heading in this drop zone
                    const existingHeading = target.querySelector('.dropped-heading .heading-item');
                    if (existingHeading) {
                        existingHeading.remove();
                    }

                    // Move the dropped element to the dropped-heading container
                    const droppedContainer = target.querySelector('.dropped-heading');
                    droppedContainer.appendChild(el);

                    // Update visual state
                    target.classList.add('has-heading');

                    // Update hidden input
                    const input = target.nextElementSibling;
                    if (input && input.classList.contains('answer-input')) {
                        input.value = headingLetter;
                        console.log('Answer saved:', questionNumber, '=', headingLetter);
                    }

                    // FIXED: Update navigation button to show answered state (green indicator)
                    const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                    if (navButton) {
                        navButton.classList.add('answered');
                        console.log('Navigation button updated for question:', questionNumber);

                        // Update answered count
                        const answeredCount = document.querySelectorAll('.number-btn.answered').length;
                        const answeredSpan = document.getElementById('answered-count');
                        if (answeredSpan) {
                            answeredSpan.textContent = answeredCount;
                        }
                    }

                    // Make the dropped heading removable
                    el.addEventListener('click', function() {
                        if (confirm('Remove this heading?')) {
                            el.remove();
                            target.classList.remove('has-heading');
                            if (input) {
                                input.value = '';
                            }

                            // FIXED: Remove answered state when heading is removed
                            if (navButton) {
                                navButton.classList.remove('answered');

                                // Update answered count
                                const answeredCount = document.querySelectorAll('.number-btn.answered').length;
                                const answeredSpan = document.getElementById('answered-count');
                                if (answeredSpan) {
                                    answeredSpan.textContent = answeredCount;
                                }
                            }
                        }
                    });
                }
            });
            
            // Visual feedback during drag
            drake.on('drag', function(el) {
                el.style.opacity = '0.5';
            });
            
            drake.on('dragend', function(el) {
                el.style.opacity = '1';
            });
            
            console.log('Dragula initialized for question', questionId);
        })();
    </script>
@endif
