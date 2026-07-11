{{-- IELTS Diagram Question — Image hotspots with draggable word bank --}}
@if($question->question_type === 'plan_map_diagram' && $question->diagram_hotspots)
    @php
        $hotspots = $question->diagram_hotspots['hotspots'] ?? [];
        $wordBank = $question->diagram_hotspots['dropdown_options'] ?? [];
        $startNumber = $question->diagram_hotspots['start_number'] ?? $displayNumber;
        $hotspotCount = count($hotspots);
    @endphp

    <div class="question-item diagram-drag-question" id="question-{{ $question->id }}" data-question-id="{{ $question->id }}">
        <div class="question-content">
            <span class="question-number">{{ $startNumber }}–{{ $startNumber + $hotspotCount - 1 }}</span>
            <div class="question-text">{!! $question->content !!}</div>
            <p class="diagram-instructions">
                <i class="fas fa-hand-pointer mr-1"></i>
                Drag each word from the bank into the correct numbered box on the image.
            </p>
        </div>

        <div class="diagram-layout">
            {{-- Image with positioned drop zones --}}
            <div class="diagram-image-wrap">
                <div class="diagram-image-inner">
                    <img src="{{ $question->getMediaUrlAttribute() }}" alt="Diagram" class="diagram-image">
                    @foreach($hotspots as $i => $h)
                        @php
                            $qNum = $startNumber + $i;
                            $x = $h['x'] ?? 50;
                            $y = $h['y'] ?? 50;
                        @endphp
                        <div class="diagram-dropzone"
                             style="left: {{ $x }}%; top: {{ $y }}%;"
                             data-question-id="{{ $question->id }}"
                             data-hotspot-index="{{ $i }}"
                             data-question-number="{{ $qNum }}">
                            <span class="dropzone-number">{{ $qNum }}</span>
                            <span class="dropzone-content"></span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Word Bank --}}
            <div class="word-bank-wrap">
                <div class="word-bank-header">Word Bank</div>
                <div class="word-bank" data-question-id="{{ $question->id }}">
                    @foreach($wordBank as $idx => $word)
                        <div class="word-chip"
                             draggable="true"
                             data-word="{{ $word }}"
                             data-word-index="{{ $idx }}">
                            {{ chr(65 + $idx) }} &nbsp;{{ $word }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Hidden inputs that mirror dropzone state for form submission --}}
        @for($i = 0; $i < $hotspotCount; $i++)
            <input type="hidden"
                   name="answers[{{ $question->id }}_{{ $i }}]"
                   class="diagram-answer-input"
                   data-question-id="{{ $question->id }}"
                   data-hotspot-index="{{ $i }}"
                   value="">
        @endfor
    </div>
    @php $currentQuestionNumber += $hotspotCount; @endphp
@endif

<style>
.diagram-drag-question {
    margin: 16px 0;
}

.diagram-instructions {
    font-size: 13px;
    color: #475569;
    margin: 6px 0 14px;
    padding: 8px 12px;
    background: #f1f5f9;
    border-left: 3px solid #3b82f6;
    border-radius: 4px;
}

.diagram-layout {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    flex-wrap: wrap;
}

/* Image area */
.diagram-image-wrap {
    flex: 1 1 60%;
    min-width: 320px;
}

.diagram-image-inner {
    position: relative;
    display: inline-block;
    max-width: 100%;
}

.diagram-image {
    display: block;
    max-width: 100%;
    height: auto;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}

/* Drop zones positioned on the image */
.diagram-dropzone {
    position: absolute;
    transform: translate(-50%, -50%);
    min-width: 70px;
    height: 28px;
    background: #ffffff;
    border: 2px dashed #94a3b8;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 8px;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    font-size: 12px;
}

.diagram-dropzone.drag-over {
    background: #dbeafe;
    border-color: #3b82f6;
    border-style: solid;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
}

.diagram-dropzone.filled {
    background: #ecfccb;
    border-color: #84cc16;
    border-style: solid;
}

.dropzone-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    background: #0f172a;
    color: #fff;
    border-radius: 3px;
    font-weight: 700;
    font-size: 11px;
    flex-shrink: 0;
}

.dropzone-content {
    color: #1e293b;
    font-weight: 600;
    font-size: 12px;
    white-space: nowrap;
}

.dropzone-content[data-has-word="true"] {
    cursor: grab;
}

/* Word Bank */
.word-bank-wrap {
    flex: 1 1 280px;
    min-width: 240px;
}

.word-bank-header {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.word-bank {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    min-height: 80px;
}

.word-bank.drag-over {
    background: #eff6ff;
    border-color: #3b82f6;
}

.word-chip {
    padding: 8px 12px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    cursor: grab;
    font-size: 13px;
    font-weight: 500;
    color: #1e293b;
    user-select: none;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.word-chip:hover {
    border-color: #3b82f6;
    background: #f0f9ff;
}

.word-chip.dragging {
    opacity: 0.4;
    cursor: grabbing;
}

.word-chip.used {
    opacity: 0.35;
    background: #f1f5f9;
    cursor: not-allowed;
    pointer-events: none;
}

@media (max-width: 768px) {
    .diagram-layout { flex-direction: column; }
    .word-bank-wrap { width: 100%; }
}
</style>

<script>
(function () {
    function initDiagram(container) {
        const qid = container.dataset.questionId;
        const dropzones = container.querySelectorAll('.diagram-dropzone');
        const chips = container.querySelectorAll('.word-chip');
        const wordBank = container.querySelector('.word-bank');
        const inputs = container.querySelectorAll('.diagram-answer-input');

        let dragged = null;

        function syncChipUsedState() {
            const usedWords = new Set();
            dropzones.forEach(z => {
                const w = z.querySelector('.dropzone-content').textContent.trim();
                if (w) usedWords.add(w);
            });
            chips.forEach(c => {
                c.classList.toggle('used', usedWords.has(c.dataset.word));
            });
        }

        function syncInputs() {
            dropzones.forEach((z) => {
                const idx = z.dataset.hotspotIndex;
                const input = container.querySelector(`.diagram-answer-input[data-hotspot-index="${idx}"]`);
                if (input) input.value = z.querySelector('.dropzone-content').textContent.trim();
            });
        }

        function placeWord(zone, word) {
            // If zone had a word, mark it removed first (chip becomes available again)
            zone.querySelector('.dropzone-content').textContent = word;
            zone.querySelector('.dropzone-content').setAttribute('data-has-word', 'true');
            zone.classList.add('filled');
            syncChipUsedState();
            syncInputs();
        }

        function clearZone(zone) {
            zone.querySelector('.dropzone-content').textContent = '';
            zone.querySelector('.dropzone-content').removeAttribute('data-has-word');
            zone.classList.remove('filled');
            syncChipUsedState();
            syncInputs();
        }

        // Chip drag
        chips.forEach(chip => {
            chip.addEventListener('dragstart', (e) => {
                if (chip.classList.contains('used')) { e.preventDefault(); return; }
                dragged = { source: 'bank', word: chip.dataset.word };
                chip.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', chip.dataset.word);
            });
            chip.addEventListener('dragend', () => chip.classList.remove('dragging'));
        });

        // Dropzone drag-over + drop
        dropzones.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });
            zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
                const word = e.dataTransfer.getData('text/plain');
                if (!word) return;

                // If another zone already had this word, clear it (move semantics)
                dropzones.forEach(z => {
                    if (z !== zone && z.querySelector('.dropzone-content').textContent.trim() === word) {
                        clearZone(z);
                    }
                });

                placeWord(zone, word);
                dragged = null;
            });

            // Click on filled zone to clear
            zone.addEventListener('dblclick', () => {
                if (zone.classList.contains('filled')) clearZone(zone);
            });

            // Drag from zone back to bank
            zone.setAttribute('draggable', 'true');
            zone.addEventListener('dragstart', (e) => {
                if (!zone.classList.contains('filled')) { e.preventDefault(); return; }
                const word = zone.querySelector('.dropzone-content').textContent.trim();
                dragged = { source: 'zone', word, originZone: zone };
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', word);
            });
        });

        // Drop on bank → clears zone
        if (wordBank) {
            wordBank.addEventListener('dragover', (e) => {
                e.preventDefault();
                wordBank.classList.add('drag-over');
            });
            wordBank.addEventListener('dragleave', () => wordBank.classList.remove('drag-over'));
            wordBank.addEventListener('drop', (e) => {
                e.preventDefault();
                wordBank.classList.remove('drag-over');
                if (dragged && dragged.source === 'zone' && dragged.originZone) {
                    clearZone(dragged.originZone);
                }
                dragged = null;
            });
        }

        // Restore from existing inputs (e.g., on page reload)
        inputs.forEach(input => {
            if (input.value) {
                const idx = input.dataset.hotspotIndex;
                const zone = container.querySelector(`.diagram-dropzone[data-hotspot-index="${idx}"]`);
                if (zone) placeWord(zone, input.value);
            }
        });

        syncChipUsedState();
    }

    function bootDiagrams() {
        document.querySelectorAll('.diagram-drag-question').forEach(initDiagram);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootDiagrams);
    } else {
        bootDiagrams();
    }
})();
</script>
@endif
