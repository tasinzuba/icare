<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import ExamHeader from '@/Components/Exam/ExamHeader.vue';
import AntiCheat from '@/Components/Exam/AntiCheat.vue';
import ExitConfirmModal from '@/Components/Exam/ExitConfirmModal.vue';
import TextAnnotator from '@/Components/Exam/TextAnnotator.vue';
import AutoSubmitOverlay from '@/Components/Exam/AutoSubmitOverlay.vue';
import debounce from 'lodash/debounce';
// Drag & drop uses manual mousedown/mousemove/mouseup — zero library dependency

// CSS is injected dynamically in onMounted to prevent global scope bleeding
const props = defineProps({
    testSet: Object,
    attempt: Object,
    questions: Array,
    serverTime: String,
    audioUrl: String,
    timeLimitSeconds: Number,
    reviewTimeSeconds: { type: Number, default: 120 },
});

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name || '');
const userIdStr = computed(() => {
    const id = page.props.auth?.user?.id || 0;
    return `BI ${id.toString().padStart(6, '0')}`;
});

const audioVolume = ref(75);
const prevVolume = ref(75);
const toggleMute = () => {
    if (audioVolume.value > 0) {
        prevVolume.value = audioVolume.value;
        audioVolume.value = 0;
    } else {
        audioVolume.value = prevVolume.value || 75;
    }
};

// State
const isPageLoading = ref(true); // Loading screen before audio overlay
const isTestStarted = ref(false); // Controlled by "Play" overlay
const isSubmitting = ref(false);
const showSubmitModal = ref(false);
const isTimeUpSubmit = ref(false);
const isReviewPhase = ref(false); // True after audio ends — review time

const answers = ref({});
const flaggedQuestions = ref(new Set());
const currentPart = ref(1);
const currentFocusedNav = ref(1); // Currently active question number in bottom nav

// Auto-save logic
const saveStatus = ref('');

const audioPlayer = ref(null);
const isAudioPlaying = ref(false);
const annotatorRef = ref(null);

// Format draft answers (Database)
let dbDraft = {};
if (props.attempt.draft_answers) {
    dbDraft = typeof props.attempt.draft_answers === 'string' 
        ? JSON.parse(props.attempt.draft_answers) 
        : props.attempt.draft_answers;
}

// Format draft answers (LocalStorage - Higher Priority if page crashed before API success)
const storageKey = `listening_test_answers_${props.attempt.id}`;
let localDraft = {};
try {
    const saved = localStorage.getItem(storageKey);
    if (saved) localDraft = JSON.parse(saved);
} catch (e) {
    console.warn("Failed to parse local draft answers");
}

// Merge them, preferring local ones if they exist
const mergedAnswers = { ...dbDraft, ...localDraft };
for (const key in mergedAnswers) {
    answers.value[key] = mergedAnswers[key];
}

// Group Questions by Part
const questionsByPart = computed(() => {
    const grouped = {};
    props.questions.forEach(q => {
        if (!grouped[q.part_number]) {
            grouped[q.part_number] = [];
        }
        grouped[q.part_number].push(q);
    });
    return grouped;
});

const totalParts = computed(() => Object.keys(questionsByPart.value).length);

const groupedQuestionsArray = computed(() => {
    return Object.keys(questionsByPart.value)
        .sort((a,b) => parseInt(a) - parseInt(b))
        .map(partNumber => ({
            part_number: parseInt(partNumber),
            questions: questionsByPart.value[partNumber]
        }));
});

// HTML Generation matching Blade 100%
const generateQuestionHtml = (q, dItem) => {
    const displayNumber = dItem.displayNumber;
    let html = '';

    if (q.question_type === 'single_choice') {
        html += `
        <div class="ielts-question-item" id="question-${q.id}" style="margin-bottom: 24px;">
            <div class="ielts-q-heading">
                <span class="ielts-q-number">${displayNumber}.</span>
                <div class="ielts-q-text">${q.content}</div>
            </div>`;
        
        if (q.options && q.options.length > 0) {
            html += `<div class="ielts-options">`;
            q.options.forEach(opt => {
                html += `
                <div class="ielts-option" style="margin-bottom: 6px !important; display: flex !important; align-items: center !important;">
                    <input type="radio" 
                           name="answers[${q.id}]" 
                           id="option-${q.id}-${opt.id}" 
                           value="${opt.id}"
                           data-question-id="${q.id}"
                           class="single-choice-radio ielts-radio"
                           style="-webkit-appearance: radio !important; appearance: radio !important; margin: 0 8px 0 0 !important; width: 14px !important; height: 14px !important; cursor: pointer !important; padding: 0 !important; background: none !important;">
                    <label for="option-${q.id}-${opt.id}" 
                           style="cursor: pointer !important; font-size: 14px !important; color: #000000 !important; font-weight: normal !important; line-height: 1.4 !important; margin: 0 !important; padding: 0 !important;">
                        ${opt.content || ''}
                    </label>
                </div>`;
            });
            html += `</div>`;
        } else {
            html += `
            <div style="margin-top: 8px;">
                <input type="text"
                       name="answers[${q.id}]"
                       class="gap-input inline-blank-input text-input-field"
                       style="width: 20px; min-width: 144px; max-width: 100%; padding: 3px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; color: #1f2937; background: #fff; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
                       onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 2px rgba(37,99,235,0.15)'"
                       onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'"
                       data-question-id="${q.id}"
                       placeholder="Enter your answer"
                       autocomplete="off" spellcheck="false">
            </div>`;
        }
        html += `</div>`;
    }
    else if (q.question_type === 'multiple_choice') {
        const correctCount = q.max_selections || 1;
        const hasMultipleCorrect = correctCount > 1;
        
        html += `
        <div class="ielts-question-item" id="question-${q.id}" style="margin-bottom: 24px;">`;
            
        if (hasMultipleCorrect) {
            html += `<div class="ielts-q-number ielts-q-number-block" style="font-weight: 700 !important; font-size: 14px !important; color: #000000 !important; line-height: 1.5 !important; margin-bottom: 8px !important; display: block !important; padding: 0 !important; background: none !important; border: none !important;">
                <span style="font-weight: 700 !important;">Questions ${displayNumber}-${displayNumber + correctCount - 1}</span>
            </div>`;
            html += `<div class="ielts-q-text" style="margin-bottom: 8px; font-size: 14px; line-height: 1.6; color: #111827;">${q.content}</div>`;
        } else {
            html += `<div class="ielts-q-heading">
                <span class="ielts-q-number">${displayNumber}.</span>
                <div class="ielts-q-text">${q.content}</div>
            </div>`;
        }
            
        if (q.options && q.options.length > 0) {
            html += `<div class="ielts-options" data-correct-count="${correctCount}">`;
            q.options.forEach(opt => {
                if (hasMultipleCorrect) {
                    html += `
                    <div class="ielts-option" style="margin-bottom: 6px !important; display: flex !important; align-items: center !important;">
                        <input type="checkbox" 
                               name="answers[${q.id}][]" 
                               id="option-${q.id}-${opt.id}" 
                               value="${opt.id}"
                               data-question-id="${q.id}"
                               class="multiple-choice-checkbox ielts-radio"
                               style="-webkit-appearance: checkbox !important; appearance: checkbox !important; margin: 0 8px 0 0 !important; width: 14px !important; height: 14px !important; cursor: pointer !important; padding: 0 !important; background: none !important;">
                        <label for="option-${q.id}-${opt.id}" 
                               style="cursor: pointer !important; font-size: 14px !important; color: #000000 !important; font-weight: normal !important; line-height: 1.4 !important; margin: 0 !important; padding: 0 !important;">
                            ${opt.content || ''}
                        </label>
                    </div>`;
                } else {
                    html += `
                    <div class="ielts-option" style="margin-bottom: 6px !important; display: flex !important; align-items: center !important;">
                        <input type="radio" 
                               name="answers[${q.id}]" 
                               id="option-${q.id}-${opt.id}" 
                               value="${opt.id}"
                               data-question-id="${q.id}"
                               class="single-choice-radio ielts-radio"
                               style="-webkit-appearance: radio !important; appearance: radio !important; margin: 0 8px 0 0 !important; width: 14px !important; height: 14px !important; cursor: pointer !important; padding: 0 !important; background: none !important;">
                        <label for="option-${q.id}-${opt.id}" 
                               style="cursor: pointer !important; font-size: 14px !important; color: #000000 !important; font-weight: normal !important; line-height: 1.4 !important; margin: 0 !important; padding: 0 !important;">
                            ${opt.content || ''}
                        </label>
                    </div>`;
                }
            });
            html += `</div>`;
        } else {
            html += `
            <div style="margin-top: 8px;">
                <input type="text"
                       name="answers[${q.id}]"
                       class="gap-input inline-blank-input text-input-field"
                       style="width: 200px; padding: 4px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; font-weight: 500; color: #1f2937; background: white; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
                       onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 2px rgba(59,130,246,0.15)'"
                       onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'"
                       data-question-id="${q.id}"
                       placeholder="Enter your answer"
                       autocomplete="off" spellcheck="false">
            </div>`;
        }
        html += `</div>`;
    }
    else if (q.question_type === 'drag_drop') {
        const dropZones = q.section_specific_data?.drop_zones || [];
        const options = q.section_specific_data?.draggable_options || [];
        const dropZoneCount = Math.max(Object.keys(dropZones).length, 1);
        const startNum = displayNumber;
        const endNum = startNum + dropZoneCount - 1;

        let processedContent = q.content || '';

        // Strip ALL pre-rendered Blade elements from content
        processedContent = processedContent.replace(/<span[^>]*class="[^"]*drop-box[^"]*"[^>]*>[\s\S]*?<\/span>/gi, '');
        processedContent = processedContent.replace(/<div[^>]*class="[^"]*drop-box[^"]*"[^>]*>[\s\S]*?<\/div>/gi, '');
        processedContent = processedContent.replace(/<input[^>]*\/?>/gi, '');
        processedContent = processedContent.replace(/<label[^>]*class="[^"]*placeholder-text[^"]*"[^>]*>[\s\S]*?<\/label>/gi, '');

        if (Array.isArray(dropZones)) {
            dropZones.forEach((zone, zoneIndex) => {
                const zoneNumber = startNum + zoneIndex;
                const dragNum = (zone && zone.zone_number !== undefined) ? zone.zone_number : (zoneIndex + 1);
                const pattern = new RegExp(`\\[DRAG_${dragNum}\\]`, 'g');

                const dropBoxHtml = `<span class="dd-drop-zone" data-question-id="${q.id}" data-zone-index="zone_${zoneIndex}" data-question-number="${zoneNumber}" style="display: inline-flex; min-width: 130px; height: 28px; align-items: center; justify-content: center; padding: 2px 10px; margin: 0 4px; background: #ffffff; border: 1px solid #93c5fd; border-radius: 3px; vertical-align: middle; cursor: pointer; transition: all 0.15s ease; font-size: 13px; outline: none;" tabindex="-1"><span class="dd-placeholder" style="color: #1e293b; font-weight: 700; font-size: 13px; pointer-events: none;">${zoneNumber}</span></span>`;

                processedContent = processedContent.replace(pattern, dropBoxHtml);
            });
        }

        html += `
        <div class="ielts-question-item drag-drop-question" id="question-${q.id}" style="margin-bottom: 28px;">
            <div style="display: flex; gap: 16px; align-items: flex-start;">
                <div class="dd-content" style="flex: 1 1 auto; font-size: 14px; line-height: 2.4; color: #1e293b; min-width: 0;">${processedContent}</div>
                <div style="flex: 0 0 auto; max-width: 480px; position: sticky; top: 16px;">
                    <div class="dd-source" id="dd-source-${q.id}" style="display: inline-flex; flex-direction: column; gap: 8px; padding: 4px; align-items: stretch;">`;

        if (Array.isArray(options)) {
            options.forEach((optText, optIdx) => {
                html += `<div class="dd-drag-item" data-question-id="${q.id}" data-option-value="${optText}" draggable="true" style="display: block; padding: 7px 14px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); font-size: 13px; font-weight: 500; color: #1e293b; cursor: grab; user-select: none; transition: all 0.15s ease; text-align: left; white-space: nowrap;">${optText}</div>`;
            });
        }

        html += `
                    </div>
                </div>
            </div>
        </div>`;
    }
    else if (q.question_type === 'dropdown_selection' || q.question_type === 'fill_blanks') {
        let dropdownContent = q.content || '';

        dropdownContent = dropdownContent.replace(/<span([^>]*style="[^"]*")([^>]*)>([^<]*\[DROPDOWN_\d+\][^<]*)<\/span>/gi, "$3");
        dropdownContent = dropdownContent.replace(/(<span[^>]*style="[^"]*)(color:[^;"]+;?)/gi, "$1");
        dropdownContent = dropdownContent.replace(/(<span[^>]*style="[^"]*)(letter-spacing:[^;"]+;?)/gi, "$1");

        let dropdownCounter = displayNumber;
        let blankCounter = displayNumber;

        // Detect IELTS matching-letters mode: dropdown_selection with single-letter options (A, B, C...)
        // → render as matrix grid (rows = statements, cols = letters, radios) instead of inline dropdowns.
        const opts = q.section_specific_data?.dropdown_options || {};
        const firstKey = Object.keys(opts)[0];
        const defaultColumns = firstKey ? String(opts[firstKey]).split(',').map(s => s.trim()) : [];
        const isLetterMatrix = q.question_type === 'dropdown_selection'
            && defaultColumns.length > 0
            && defaultColumns.every(c => /^[A-Z]$/.test(c));

        if (isLetterMatrix) {
            // Decompose into lines so legend/prologue stays above the matrix and only the
            // line containing each [DROPDOWN_X] becomes a row.
            const normalized = dropdownContent
                .replace(/<br\s*\/?>/gi, '\n')
                .replace(/<\/(p|div|li|h[1-6])>/gi, '\n')
                .replace(/<(p|div|li|h[1-6])[^>]*>/gi, '');
            const tmp = document.createElement('div');
            tmp.innerHTML = normalized;
            const cleanText = tmp.textContent || '';
            const lines = cleanText.split(/\n+/).map(l => l.replace(/\s+/g, ' ').trim()).filter(Boolean);

            const prologueLines = [];
            const rows = [];
            lines.forEach(line => {
                const mm = line.match(/\[DROPDOWN_(\d+)\]/);
                if (mm) {
                    const dn = mm[1];
                    const statement = line.replace(/\[DROPDOWN_\d+\]/g, '').trim()
                        .replace(/^[•·▪●◦*\-–—]+\s*/, '').replace(/^\d+[.)]\s+/, '').trim();
                    const colStr = opts[dn];
                    if (statement) {
                        rows.push({
                            dropdownNum: dn,
                            statement,
                            displayNum: dropdownCounter++,
                            columns: colStr ? String(colStr).split(',').map(s => s.trim()) : defaultColumns,
                        });
                    }
                } else if (rows.length === 0) {
                    // Only lines BEFORE the first row are prologue
                    prologueLines.push(line);
                }
            });

            if (rows.length > 0) {
                const optionColPx = 44;
                const statementColPx = 320;
                const tableMaxPx = statementColPx + (optionColPx * defaultColumns.length) + 4;
                let mh = '';
                if (prologueLines.length > 0) {
                    mh += `<div class="ielts-matching-prologue" style="margin-bottom: 12px; color: #1f2937; font-size: 14px; line-height: 1.65; max-width: ${tableMaxPx}px;">`;
                    prologueLines.forEach(p => {
                        mh += `<p style="margin: 0 0 4px 0;">${p}</p>`;
                    });
                    mh += `</div>`;
                }
                mh += `<div class="ielts-matching-matrix" style="margin-top: 12px;">
                    <table style="width: 100%; max-width: ${tableMaxPx}px; border-collapse: collapse; border: 1.5px solid #6b7280 !important; font-size: 14px; table-layout: fixed;">
                        <thead>
                            <tr style="background: #f3f4f6;">
                                <th style="border: 1.5px solid #6b7280 !important; padding: 10px; text-align: left; width: ${statementColPx}px;"></th>`;
                defaultColumns.forEach(col => {
                    mh += `<th style="border: 1.5px solid #6b7280 !important; padding: 10px 4px; text-align: center; font-weight: 700; color: #111827; width: ${optionColPx}px;">${col}</th>`;
                });
                mh += `</tr></thead><tbody>`;
                rows.forEach(row => {
                    mh += `<tr>
                        <td class="matrix-statement-cell" style="border:1.5px solid #6b7280 !important; padding:12px 14px !important; text-align:left !important; vertical-align:middle !important; line-height:1.5;">
                            <div style="display:flex; gap:10px; align-items:center;">
                                <span class="matrix-row-number" data-question-number="${row.displayNum}" style="font-weight:700; color:#111827; min-width:22px;">${row.displayNum}</span>
                                <span style="color:#1f2937;">${row.statement}</span>
                            </div>
                        </td>`;
                    row.columns.forEach(col => {
                        mh += `<td class="matrix-radio-cell" style="border:1.5px solid #6b7280 !important; padding:8px !important; text-align:center !important; vertical-align:middle !important; line-height:0;">
                            <label style="display:inline-flex; align-items:center; justify-content:center; cursor:pointer; padding:6px; margin:0; line-height:0;">
                                <input type="radio"
                                       class="matching-matrix-radio"
                                       name="mtx_${q.id}_${row.dropdownNum}"
                                       value="${col}"
                                       data-question-id="${q.id}"
                                       data-dropdown-key="dropdown_${row.dropdownNum}"
                                       data-question-number="${row.displayNum}"
                                       style="width:18px; height:18px; margin:0; padding:0; cursor:pointer; accent-color:#2563eb; vertical-align:middle; display:block;">
                            </label>
                        </td>`;
                    });
                    mh += `</tr>`;
                });
                mh += `</tbody></table></div>`;

                html += `<div class="question-item" id="question-${q.id}">${mh}</div>`;
                return html; // emit & exit — skip inline dropdown rendering for this question
            }
        }

        // Process DROPDOWN_X — fallback inline dropdowns (word options or no letters)
        if (q.section_specific_data?.dropdown_options) {
            dropdownContent = dropdownContent.replace(/\[DROPDOWN_(\d+)\]/g, (match, dropdownNum) => {
                const currentNum = dropdownCounter++;
                const optionsStr = q.section_specific_data.dropdown_options[dropdownNum];
                const options = optionsStr ? optionsStr.split(',').map(s => s.trim()) : [];

                let selectHtml = `<select class="gap-dropdown-input gap-dropdown inline-dropdown"
                        data-question-id="${q.id}"
                        data-dropdown-key="dropdown_${dropdownNum}">
                    <option value="">${currentNum}</option>`;

                options.forEach(opt => {
                    selectHtml += `<option value="${opt}">${opt}</option>`;
                });

                selectHtml += `</select>`;
                return selectHtml;
            });
        }
        
        // Process ____X____ (blanks) — matching Reading test's exact design
        dropdownContent = dropdownContent.replace(/\[____(\d+)____\]|\[BLANK_(\d+)\]/g, (match, m1, m2) => {
            const blankNum = m1 || m2;
            const currentNum = blankCounter++;
            return `<input type="text" class="gap-input inline-blank-input inline-blank"
                           style="width: 20px; min-width: 144px; max-width: 100%; display: inline-block; padding: 3px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; color: #1f2937; background: #fff; text-align: center; outline: none; transition: border-color 0.2s, box-shadow 0.2s; vertical-align: middle;"
                           onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 2px rgba(37,99,235,0.15)'"
                           onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'"
                           data-question-id="${q.id}"
                           data-blank-key="blank_${blankNum}"
                           placeholder="${currentNum}"
                           autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">`;
        });
        
        html += `
        <div class="question-item" id="question-${q.id}">
            <div class="prose prose-slate max-w-none prose-p:my-0 text-gray-800 text-base leading-relaxed" style="margin-bottom: 8px; font-size: 14px; line-height: 1.6; color: #111827;">
                ${dropdownContent}
            </div>
        </div>`;
    }
    else {
        html += `
        <div class="question-item ielts-question-item" id="question-${q.id}">
            <div class="ielts-q-heading">
                <span class="ielts-q-number">${displayNumber}.</span>
                <div class="ielts-q-text">${q.content}</div>
            </div>
            <div style="margin-top: 8px;">
                <input type="text" 
                       name="answers[${q.id}]" 
                       class="gap-input inline-blank-input text-input-field"
                       style="width: 20px; min-width: 144px; max-width: 100%; padding: 6px 10px; border: 1.5px solid #333; border-radius: 4px; font-size: 14px; color: #1f2937; background: #fff; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
                       onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 2px rgba(37,99,235,0.18)'"
                       onblur="this.style.borderColor='#333'; this.style.boxShadow='none'"
                       data-question-id="${q.id}"
                       placeholder="Enter your answer" 
                       autocomplete="off" spellcheck="false">
            </div>
        </div>`;
    }
    
    return html;
};

// Calculate Display Numbers sequentially AND Grouping
const processedQuestionsByPart = computed(() => {
    let displayNumber = 1;
    const processed = [];
    
    groupedQuestionsArray.value.forEach(part => {
        let groupsMap = {}; 
        let groupsOrder = [];
        
        // Setup Groups
        part.questions.forEach(q => {
             const gName = q.question_group || 'none';
             if (!groupsMap[gName]) {
                 groupsMap[gName] = [];
                 groupsOrder.push(gName);
             }
             groupsMap[gName].push(q);
        });

        const partGroups = [];
        groupsOrder.forEach(gName => {
             const groupQs = [];
             groupsMap[gName].forEach(q => {
                const isMultipleChoice = q.question_type === 'multiple_choice';
                let startNum = displayNumber;
                
                let itemsCount = 1;
                if (isMultipleChoice) {
                    const correctCount = q.max_selections || 1;
                    itemsCount = correctCount > 0 ? correctCount : 1;
                } else if (q.question_type === 'drag_drop') {
                    itemsCount = q.section_specific_data?.drag_zones ? Object.keys(q.section_specific_data.drag_zones).length : 1; 
                    itemsCount = Math.max(itemsCount, (q.section_specific_data?.drop_zones ? Object.keys(q.section_specific_data.drop_zones).length : 1));
                } else if (q.question_type === 'fill_blanks' || q.question_type === 'dropdown_selection') {
                    const bCount1 = (q.content.match(/\[____\d+____\]/g) || []).length;
                    const bCount2 = (q.content.match(/\[BLANK_\d+\]/g) || []).length;
                    const dCount = (q.content.match(/\[DROPDOWN_\d+\]/g) || []).length;
                    const totalInline = bCount1 + bCount2 + dCount;
                    itemsCount = totalInline > 0 ? totalInline : 1;
                }
                
                const dItem = {
                    ...q,
                    displayNumber: startNum,
                    endNumber: startNum + itemsCount - 1,
                    itemsCount: itemsCount
                };
                
                const finalHtml = generateQuestionHtml(q, dItem);
                
                groupQs.push({
                    ...dItem,
                    processedHtml: finalHtml
                });
                displayNumber += itemsCount;
             });

             partGroups.push({
                 name: gName === 'none' ? null : gName,
                 questions: groupQs
             });
        });

        processed.push({
             part_number: part.part_number,
             groups: partGroups,
             instructionRange: part.questions.length > 0 ? (partGroups[0].questions[0].displayNumber + '-' + (displayNumber - 1)) : '',
        });
    });
    
    return processed;
});

// Provide a flattened list of "Navigation Numbers" directly for rendering
const navNumberButtons = computed(() => {
    const list = [];
    processedQuestionsByPart.value.forEach(part => {
        part.groups.forEach(g => {
            g.questions.forEach(q => {
               for(let i=0; i<q.itemsCount; i++) {
                   list.push({
                       displayNumber: q.displayNumber + i,
                       part: part.part_number,
                       questionId: q.id,
                       index: i
                   });
               }
            });
        });
    });
    return list;
});

const getPartNumberButtons = (partNum) => {
    return navNumberButtons.value.filter(n => n.part === partNum);
};

// Process click events inside the question container
const handleContainerEvents = (e) => {
    let target = e.target;

    // Click on .ielts-option row → select the radio/checkbox inside it
    const optionRow = target.closest('.ielts-option');
    if (optionRow && e.type === 'click') {
        const radio = optionRow.querySelector('input[type="radio"]');
        const checkbox = optionRow.querySelector('input[type="checkbox"]');
        
        if (radio && !radio.checked) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));
        } else if (checkbox && target !== checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    // Click-to-remove: click a filled drop zone → return item to source list
    let dropZone = target.closest('.dd-drop-zone');
    if (dropZone && dropZone.querySelector('.dd-drag-item')) {
        const qid = dropZone.getAttribute('data-question-id');
        const zoneKey = dropZone.getAttribute('data-zone-index');
        const optEl = dropZone.querySelector('.dd-drag-item');

        if (qid && zoneKey && answers.value[qid]) {
            delete answers.value[qid][zoneKey];
        }

        if (optEl) {
            const questionEl = dropZone.closest('.drag-drop-question');
            const sourceContainer = questionEl?.querySelector('.dd-source');
            if (sourceContainer) {
                styleItemInSource(optEl);
                sourceContainer.appendChild(optEl);
            }
        }

        restoreDropZone(dropZone);
    }
};

// Restore UI state from answers object after rendering
const syncDOMWithAnswers = () => {
    const container = document.querySelector('.questions-container');
    if(!container) return;

    Object.keys(answers.value).forEach(qid => {
        const ans = answers.value[qid];
        
        if (typeof ans === 'object' && ans !== null && !Array.isArray(ans)) {
            // Dropdowns & Blanks & Drop Zones
            Object.keys(ans).forEach(key => {
                if(key.startsWith('dropdown_')) {
                    // Try inline-dropdown <select> first
                    const sel = container.querySelector(`select[data-question-id="${qid}"][data-dropdown-key="${key}"]`);
                    if(sel) {
                        sel.value = ans[key];
                    }
                    // Also restore matrix-style radio (same dropdown_X key shape)
                    const radio = container.querySelector(`input.matching-matrix-radio[data-question-id="${qid}"][data-dropdown-key="${key}"][value="${ans[key]}"]`);
                    if (radio) radio.checked = true;
                } else if(key.startsWith('blank_')) {
                    const inp = container.querySelector(`input[data-question-id="${qid}"][data-blank-key="${key}"]`);
                    if(inp) inp.value = ans[key];
                } else if(key.startsWith('zone_')) {
                    const zone = container.querySelector(`.dd-drop-zone[data-question-id="${qid}"][data-zone-index="${key}"]`);
                    const optEl = container.querySelector(`.dd-drag-item[data-question-id="${qid}"][data-option-value="${ans[key]}"]`);
                    if(zone && ans[key] && optEl) {
                        const placeholder = zone.querySelector('.dd-placeholder');
                        if (placeholder) placeholder.style.display = 'none';
                        zone.appendChild(optEl);
                        zone.style.border = '2px solid #1f2937';
                        zone.style.background = '#f9fafb';
                        styleItemInZone(optEl);
                    }
                }
            });
        } else if (Array.isArray(ans)) {
            // Multiple choice
            ans.forEach(val => {
                const chk = container.querySelector(`input.multiple-choice-checkbox[data-question-id="${qid}"][value="${val}"]`);
                if(chk) chk.checked = true;
            });
        } else {
            // Single choice / generic text
            const radio = container.querySelector(`input.single-choice-radio[data-question-id="${qid}"][value="${ans}"]`);
            if(radio) radio.checked = true;
            
            const text = container.querySelector(`input.text-input-field[data-question-id="${qid}"]:not([data-blank-key])`);
            if(text) text.value = ans;
        }
    });
};

watch(currentPart, () => {
    nextTick(() => {
        syncDOMWithAnswers();
        initializeDragDrop(); // Attach native drag listeners (idempotent — only runs once)
        applyHighlight();
    });
});

const isAnswered = (questionId, itemsIndex = 0, displayNumber = null) => {
     const ans = answers.value[questionId];
     if (typeof ans === 'object' && ans !== null && !Array.isArray(ans)) {
         // IELTS-style keys often match the displayed question number (e.g. dropdown_13 for Q13).
         // Try the displayNumber-based key first.
         if (displayNumber !== null) {
             for (const prefix of ['dropdown_', 'blank_']) {
                 const v = ans[prefix + displayNumber];
                 if (v !== undefined && v !== null && v !== '') return true;
             }
             const z = ans['zone_' + displayNumber];
             if (z !== undefined && z !== null && z !== '') return true;
         }
         // Fall back to 1-based sequential keys (dropdown_1, blank_1, zone_0).
         // Use exact-suffix regex to avoid `dropdown_13` matching `_1`.
         const seqRe = new RegExp(`(?:dropdown|blank)_${itemsIndex + 1}$|zone_${itemsIndex}$`);
         const key = Object.keys(ans).find(k => seqRe.test(k));
         if (key) {
             const v = ans[key];
             return v !== undefined && v !== null && v !== '';
         }
         return false;
     } else if (Array.isArray(ans)) {
         return ans.length > itemsIndex;
     }
     return !!ans;
};

const answeredCount = computed(() => {
    return navNumberButtons.value.filter(n => isAnswered(n.questionId, n.index, n.displayNumber)).length;
});

// ==========================================
// MANUAL DRAG & DROP (mousedown/mousemove/mouseup)
// Bypasses flaky native HTML5 drag API — zero library dependency
// ==========================================
let ddListenersAttached = false;

const restoreDropZone = (zone) => {
    // Restore placeholder showing zone number
    let placeholder = zone.querySelector('.dd-placeholder');
    const num = zone.getAttribute('data-question-number') || '';
    if (!placeholder) {
        placeholder = document.createElement('span');
        placeholder.className = 'dd-placeholder';
        zone.appendChild(placeholder);
    }
    placeholder.textContent = num;
    placeholder.style.cssText = 'color: #1e293b; font-weight: 700; font-size: 13px; pointer-events: none;';
    placeholder.style.display = '';

    // Remove any leftover number badge from filled state
    const oldNum = zone.querySelector('.dd-zone-num');
    if (oldNum) oldNum.remove();

    zone.style.cssText = 'display: inline-flex; min-width: 130px; height: 28px; align-items: center; justify-content: center; padding: 2px 10px; margin: 0 4px; background: #ffffff; border: 1px solid #93c5fd; border-radius: 3px; vertical-align: middle; cursor: pointer; transition: all 0.15s ease; font-size: 13px; outline: none;';
};

const styleItemInZone = (el) => {
    el.style.cssText = 'border: none; background: transparent; box-shadow: none; padding: 0; margin: 0; font-weight: 500; font-size: 13px; color: #1e293b; cursor: pointer; user-select: none; white-space: nowrap; text-align: center;';
    // Remove any dot indicator when in zone
    const dot = el.querySelector('span[data-dot]');
    if (dot) dot.remove();

    const zone = el.closest('.dd-drop-zone');
    if (zone) {
        zone.style.background = '#eff6ff';
        zone.style.borderColor = '#60a5fa';
        zone.style.borderStyle = 'solid';
        zone.style.outline = 'none';
    }
};

const styleItemInSource = (el) => {
    el.style.cssText = 'display: block; padding: 7px 14px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); font-size: 13px; font-weight: 500; color: #1e293b; cursor: grab; user-select: none; transition: all 0.15s ease; text-align: left; white-space: nowrap;';
    // Remove any leftover dot from filled state
    const dot = el.querySelector('span[data-dot]');
    if (dot) dot.remove();
};

const initializeDragDrop = () => {
    // Manual drag system (mousedown/mousemove/mouseup) — 100% reliable,
    // no dependency on flaky native HTML5 drag API.
    if (ddListenersAttached) return;

    const container = document.querySelector('.questions-container');
    if (!container) return;
    ddListenersAttached = true;

    let dragItem = null;       // The actual DOM element being dragged
    let ghostEl = null;        // Visual clone that follows the cursor
    let dragStarted = false;   // True once mouse moved enough to start drag
    let startX = 0, startY = 0;
    let highlightedZone = null;
    const DRAG_THRESHOLD = 4;  // px — minimum move to start drag

    // ── Helper: find drop zone under cursor ──
    const getZoneUnderCursor = (x, y) => {
        // Temporarily hide ghost so elementFromPoint hits elements behind it
        if (ghostEl) ghostEl.style.pointerEvents = 'none';
        if (ghostEl) ghostEl.style.display = 'none';
        const el = document.elementFromPoint(x, y);
        if (ghostEl) ghostEl.style.display = '';
        if (ghostEl) ghostEl.style.pointerEvents = '';
        return el?.closest('.dd-drop-zone');
    };

    // ── Helper: highlight / unhighlight zone ──
    const highlightZone = (zone) => {
        if (highlightedZone && highlightedZone !== zone) unhighlightZone(highlightedZone);
        if (!zone) return;
        highlightedZone = zone;
        zone.style.border = '2px solid #2563eb';
        zone.style.background = '#eff6ff';
    };

    const unhighlightZone = (zone) => {
        if (!zone) return;
        if (zone.querySelector('.dd-drag-item')) {
            zone.style.border = '2px solid #1f2937';
            zone.style.background = '#f9fafb';
        } else {
            zone.style.border = '2px dashed #9ca3af';
            zone.style.background = '#fafafa';
        }
        if (highlightedZone === zone) highlightedZone = null;
    };

    // ── Helper: create visual ghost ──
    const createGhost = (item, x, y) => {
        const rect = item.getBoundingClientRect();
        const ghost = item.cloneNode(true);
        ghost.style.cssText = `
            position: fixed; z-index: 99999; pointer-events: none;
            width: ${rect.width}px; padding: 7px 16px;
            background: #fff; border: 1.5px solid #2563eb; border-radius: 6px;
            box-shadow: 0 8px 24px rgba(37,99,235,0.22);
            font-size: 13px; font-weight: 500; color: #1f2937;
            opacity: 0.92; white-space: nowrap; text-align: center;
            transform: translate(-50%, -50%);
            left: ${x}px; top: ${y}px;
        `;
        document.body.appendChild(ghost);
        return ghost;
    };

    // ── MOUSEDOWN on drag items ──
    container.addEventListener('mousedown', (e) => {
        const item = e.target.closest('.dd-drag-item');
        if (!item || e.button !== 0) return;  // left click only

        e.preventDefault(); // prevent text selection during drag
        dragItem = item;
        dragStarted = false;
        startX = e.clientX;
        startY = e.clientY;
    });

    // ── MOUSEMOVE (document-level — tracks cursor everywhere) ──
    document.addEventListener('mousemove', (e) => {
        if (!dragItem) return;

        const dx = e.clientX - startX;
        const dy = e.clientY - startY;

        // Haven't moved enough yet — wait for threshold
        if (!dragStarted) {
            if (Math.abs(dx) < DRAG_THRESHOLD && Math.abs(dy) < DRAG_THRESHOLD) return;
            // Threshold reached — start the drag
            dragStarted = true;
            dragItem.style.opacity = '0.35';
            ghostEl = createGhost(dragItem, e.clientX, e.clientY);
        }

        // Move ghost to cursor
        if (ghostEl) {
            ghostEl.style.left = e.clientX + 'px';
            ghostEl.style.top = e.clientY + 'px';
        }

        // Highlight drop zone under cursor
        const zone = getZoneUnderCursor(e.clientX, e.clientY);
        if (zone) {
            const qid = dragItem.getAttribute('data-question-id');
            const zoneQid = zone.getAttribute('data-question-id');
            if (qid === zoneQid) {
                highlightZone(zone);
            } else {
                unhighlightZone(highlightedZone);
            }
        } else {
            unhighlightZone(highlightedZone);
        }
    });

    // ── MOUSEUP (document-level — finishes drag) ──
    document.addEventListener('mouseup', (e) => {
        if (!dragItem) return;

        const item = dragItem;
        const wasDragging = dragStarted;

        // Clean up ghost
        if (ghostEl) {
            ghostEl.remove();
            ghostEl = null;
        }
        item.style.opacity = '';
        dragItem = null;
        dragStarted = false;

        if (!wasDragging) return; // was just a click, not a drag

        const qid = item.getAttribute('data-question-id');
        const optionValue = item.getAttribute('data-option-value') || item.textContent.trim();

        // Check if dropped on a valid zone
        const zone = getZoneUnderCursor(e.clientX, e.clientY);
        const zoneQid = zone?.getAttribute('data-question-id');

        if (zone && qid === zoneQid) {
            // ── SUCCESSFUL DROP ──
            const zoneKey = zone.getAttribute('data-zone-index');

            // If zone already has a different item, return it to source
            const existingItem = zone.querySelector('.dd-drag-item');
            if (existingItem && existingItem !== item) {
                const questionEl = zone.closest('.drag-drop-question');
                const sourceContainer = questionEl?.querySelector('.dd-source');
                if (sourceContainer) {
                    styleItemInSource(existingItem);
                    sourceContainer.appendChild(existingItem);
                }
            }

            // If dragged item came from a different zone, restore that zone
            const prevZone = item.closest('.dd-drop-zone');
            if (prevZone && prevZone !== zone) {
                const prevKey = prevZone.getAttribute('data-zone-index');
                if (prevKey && answers.value[qid]) {
                    delete answers.value[qid][prevKey];
                }
                restoreDropZone(prevZone);
            }

            // Place item in drop zone
            const placeholder = zone.querySelector('.dd-placeholder');
            if (placeholder) placeholder.style.display = 'none';

            zone.appendChild(item);
            styleItemInZone(item);

            zone.style.border = '2px solid #1f2937';
            zone.style.background = '#f9fafb';

            // Save answer
            if (!answers.value[qid]) answers.value[qid] = {};
            answers.value[qid][zoneKey] = optionValue;
        } else {
            // ── DROPPED OUTSIDE — return to source ──
            const prevZone = item.closest('.dd-drop-zone');
            if (prevZone) {
                // Was in a zone — remove it and return to source
                const prevKey = prevZone.getAttribute('data-zone-index');
                if (prevKey && answers.value[qid]) {
                    delete answers.value[qid][prevKey];
                }
                const questionEl = prevZone.closest('.drag-drop-question');
                const sourceContainer = questionEl?.querySelector('.dd-source');
                if (sourceContainer) {
                    styleItemInSource(item);
                    sourceContainer.appendChild(item);
                }
                restoreDropZone(prevZone);
            }
            // If was already in source, nothing to do — item stays there
        }

        unhighlightZone(highlightedZone);
    });
};

// Audio & Test Start
const startAudioAndTest = () => {
    if (!audioPlayer.value) {
        alert("Audio player not ready. Please refresh the page and try again.");
        return;
    }

    if (!props.audioUrl) {
        alert("No audio file is attached to this listening test. Please contact your administrator.");
        return;
    }

    audioPlayer.value.volume = audioVolume.value / 100;

    const mediaErr = audioPlayer.value.error;
    if (mediaErr) {
        const codeMap = {
            1: 'Audio loading was aborted.',
            2: 'Network error while loading the audio.',
            3: 'Audio file is corrupted or cannot be decoded.',
            4: 'Audio source is not supported (broken URL, missing file, or CORS issue).',
        };
        const msg = codeMap[mediaErr.code] || 'Unknown audio error.';
        console.error('Audio element error:', mediaErr, 'URL:', props.audioUrl);
        alert(`${msg}\n\nAudio URL: ${props.audioUrl}`);
        return;
    }

    audioPlayer.value.play().then(() => {
        isAudioPlaying.value = true;
        isTestStarted.value = true;

        nextTick(() => {
            syncDOMWithAnswers();
            initializeDragDrop();
            applyHighlight();
        });
    }).catch(e => {
        console.error("Audio playback failed:", e, "URL:", props.audioUrl);

        // NotAllowedError = autoplay blocked; NotSupportedError = bad URL/format/CORS
        if (e.name === 'NotAllowedError') {
            alert("Browser blocked audio playback. Please click the Start button again.");
        } else if (e.name === 'NotSupportedError') {
            alert(`Audio file cannot be loaded. The URL may be broken, the file may be missing, or there's a CORS issue.\n\nURL: ${props.audioUrl}`);
        } else {
            alert(`Audio playback failed: ${e.message || e.name || 'Unknown error'}\n\nURL: ${props.audioUrl}`);
        }
    });
};

watch(audioVolume, (newVol) => {
    if (audioPlayer.value) {
        audioPlayer.value.volume = newVol / 100;
    }
});

const handleAudioEnd = () => {
    isAudioPlaying.value = false;
    isReviewPhase.value = true;
    // Cancel any pending auto-submit — review time takes priority
    if (isTimeUpSubmit.value) {
        isTimeUpSubmit.value = false;
    }
};

// Navigation
const changePart = (partNum) => {
    currentPart.value = partNum;
};

const toggleFlag = (questionDisplayNumber) => {
    if (flaggedQuestions.value.has(questionDisplayNumber)) {
        flaggedQuestions.value.delete(questionDisplayNumber);
    } else {
        flaggedQuestions.value.add(questionDisplayNumber);
    }
};

const isFlagged = (displayNumber) => {
    return flaggedQuestions.value.has(displayNumber);
};

// Scroll to a specific question by nav button data
const scrollToQuestion = (navItem) => {
    if (!navItem) return;
    const container = document.querySelector('.questions-container');
    if (!container) return;

    // Switch part if needed
    if (navItem.part !== currentPart.value) {
        currentPart.value = navItem.part;
        nextTick(() => doScrollToElement(navItem, container));
    } else {
        doScrollToElement(navItem, container);
    }
    currentFocusedNav.value = navItem.displayNumber;
};

// Find the SPECIFIC DOM element where a question number lives
const findNumberElement = (navItem) => {
    const container = document.querySelector('.questions-container');
    if (!container) return null;

    const questionEl = container.querySelector(`#question-${navItem.questionId}`);
    if (!questionEl) return null;

    const dn = String(navItem.displayNumber);

    // 1a. dropdown_selection matrix mode: row number span
    const matrixRow = questionEl.querySelector(`.matrix-row-number[data-question-number="${dn}"]`);
    if (matrixRow) return matrixRow;

    // 1b. dropdown_selection inline: <select> whose placeholder option text matches
    const selects = questionEl.querySelectorAll('select.inline-dropdown');
    for (const sel of selects) {
        const ph = sel.querySelector('option[value=""]');
        if (ph && ph.textContent.trim() === dn) return sel;
    }

    // 2. fill_blanks: <input> whose placeholder matches
    const inputs = questionEl.querySelectorAll('input.inline-blank, input.inline-blank-input, input.gap-input');
    for (const inp of inputs) {
        if (inp.placeholder.trim() === dn) return inp;
    }

    // 3. drag_drop: .dd-drop-zone with data-question-number
    const dropZone = questionEl.querySelector(`.dd-drop-zone[data-question-number="${dn}"]`);
    if (dropZone) return dropZone;

    // 4. single_choice / multiple_choice: .ielts-q-number (inline span "12." or block with child span)
    const qNumEl = questionEl.querySelector('.ielts-q-number');
    if (qNumEl) {
        const txt = qNumEl.textContent.trim();
        // Direct match — inline layout (span.ielts-q-number contains "12.")
        if (txt === `${dn}.` || txt === dn) return qNumEl;
        // Child span match — block layout ("Questions 12-14" header)
        const span = qNumEl.querySelector('span');
        if (span) {
            const sTxt = span.textContent.trim();
            if (sTxt === `${dn}.` || sTxt === dn) return span;
        }
    }

    // 5. Fallback: the question element itself
    return questionEl;
};

// Reactive highlight — tracks currently highlighted DOM element
let highlightedEl = null;

const applyHighlight = () => {
    // Remove previous highlight
    if (highlightedEl) {
        highlightedEl.classList.remove('q-num-highlight');
        highlightedEl = null;
    }

    const navItem = navNumberButtons.value.find(n => n.displayNumber === currentFocusedNav.value);
    if (!navItem) return;

    const el = findNumberElement(navItem);
    if (el) {
        el.classList.add('q-num-highlight');
        highlightedEl = el;
    }
};

// Watch currentFocusedNav → reactively move highlight
watch(currentFocusedNav, () => {
    nextTick(() => applyHighlight());
});

const doScrollToElement = (navItem, container) => {
    const el = findNumberElement(navItem);
    const questionEl = container.querySelector(`#question-${navItem.questionId}`);
    const target = el || questionEl;
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};

// Number button click — scroll to question (matching Blade)
const handleNavNumberClick = (navItem) => {
    scrollToQuestion(navItem);
};

// Previous / Next Question Navigation Arrows
const prevQuestion = () => {
    const idx = navNumberButtons.value.findIndex(n => n.displayNumber === currentFocusedNav.value);
    if (idx > 0) {
        scrollToQuestion(navNumberButtons.value[idx - 1]);
    }
};

const nextQuestion = () => {
    const idx = navNumberButtons.value.findIndex(n => n.displayNumber === currentFocusedNav.value);
    if (idx < navNumberButtons.value.length - 1) {
        scrollToQuestion(navNumberButtons.value[idx + 1]);
    }
};

// Interaction
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.log(`Error attempting to enable fullscreen: ${err.message}`);
        });
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
};

const saveDraft = debounce(async () => {
    saveStatus.value = 'saving';
    Object.keys(answers.value).forEach(key => {
        if(answers.value[key] === null || answers.value[key] === undefined) delete answers.value[key];
    });

    try {
        await axios.post(`/student/test/listening/auto-save/${props.attempt.id}`, {
            answers: answers.value
        });
        saveStatus.value = 'saved';
        setTimeout(() => { if(saveStatus.value === 'saved') saveStatus.value = '' }, 2000);
    } catch (error) {
        saveStatus.value = 'error';
        console.error('Auto-save failed:', error);
    }
}, 5000);

watch(answers, (newVal) => {
    // 1. Immediately cache to localstorage on every keystroke/change for max reliability
    try {
        localStorage.setItem(`listening_test_answers_${props.attempt.id}`, JSON.stringify(newVal));
    } catch (e) {}
    
    // 2. Safely debounce API request so server is updated gracefully
    if (isTestStarted.value) {
        saveDraft();
    }
}, { deep: true });

const finalSubmit = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    showSubmitModal.value = false;
    
    if(audioPlayer.value) audioPlayer.value.pause();
    
    // Flatten answers to match what Laravel expects
    const payload = {};
    if (Object.keys(answers.value).length > 0) {
        Object.assign(payload, answers.value);
    } else {
        payload["__empty"] = true;
    }
    
    // Clear local draft to prevent leakage into future attempts
    try {
        localStorage.removeItem(`listening_test_answers_${props.attempt.id}`);
    } catch (e) {}
    
    router.post(`/student/test/listening/submit/${props.attempt.id}`, {
        answers: payload
    }, {
        onError: (errors) => {
            isSubmitting.value = false;
            console.error('Submission failed', errors);
            alert('Failed to submit test. Please check your connection and try again.');
        }
    });
};

const handleTimeUp = () => {
    if (isReviewPhase.value) {
        // Review time finished — submit now
        if (audioPlayer.value && !audioPlayer.value.paused) {
            audioPlayer.value.pause();
        }
        isAudioPlaying.value = false;
        isTimeUpSubmit.value = true;
    } else {
        // Main timer hit 0 — pause audio (if still playing) and enter review
        // phase. ExamTimer's watcher on isReviewPhase will restart the timer
        // with reviewTimeSeconds (2 min). When that hits 0, this same handler
        // fires again with isReviewPhase=true and auto-submits.
        if (audioPlayer.value && !audioPlayer.value.paused) {
            audioPlayer.value.pause();
        }
        isAudioPlaying.value = false;
        isReviewPhase.value = true;
    }
};

let listeningStyles = null;
let listeningFixStyles = null;

onMounted(() => {
    document.body.classList.add('ielts-test-mode');

    // Base page styles (layout, typography, containers, etc.)
    listeningStyles = document.createElement('link');
    listeningStyles.rel = 'stylesheet';
    listeningStyles.href = '/css/listening-style.css?v=20260629';
    document.head.appendChild(listeningStyles);

    // Override/fix CSS — loads AFTER listening-style.css to win cascade
    // Fixes drag-drop (overflow, height), radio/checkbox, inline blanks, etc.
    // Bumped version query to bust browser cache when this file changes.
    listeningFixStyles = document.createElement('link');
    listeningFixStyles.rel = 'stylesheet';
    listeningFixStyles.href = '/css/listening-test-fix.css?v=20260629-2';
    document.head.appendChild(listeningFixStyles);

    // Dismiss loading screen after a brief moment (let CSS/fonts settle)
    setTimeout(() => {
        isPageLoading.value = false;
    }, 1000);

    // Professional Event Delegation across the entire document during capture phase to beat Vue shadow DOM
    document.addEventListener('change', (e) => {
        if (e.target.matches('.inline-dropdown')) {
            const qid = e.target.getAttribute('data-question-id');
            const dropdownKey = e.target.getAttribute('data-dropdown-key');
            if (qid && dropdownKey) {
                if (!answers.value[qid]) answers.value[qid] = {};
                answers.value[qid][dropdownKey] = e.target.value;
                // dropdown answer saved
            }
        }
        else if (e.target.matches('.matching-matrix-radio')) {
            // Matrix-style matching (rows = statements, cols = letters) — same answer shape as inline-dropdown
            const qid = e.target.getAttribute('data-question-id');
            const dropdownKey = e.target.getAttribute('data-dropdown-key');
            if (qid && dropdownKey) {
                if (!answers.value[qid]) answers.value[qid] = {};
                answers.value[qid][dropdownKey] = e.target.value;
            }
        }
        else if (e.target.matches('.single-choice-radio')) {
            const qid = e.target.getAttribute('data-question-id');
            if (qid) {
                answers.value[qid] = e.target.value;
                // single choice answer saved
            }
        }
        else if (e.target.matches('.multiple-choice-checkbox')) {
            const qid = e.target.getAttribute('data-question-id');
            if (qid) {
                const ieltsOpts = e.target.closest('.ielts-options');
                const maxSelect = parseInt(ieltsOpts?.getAttribute('data-correct-count')) || 1;
                
                if (!answers.value[qid]) answers.value[qid] = [];
                if (!Array.isArray(answers.value[qid])) answers.value[qid] = [answers.value[qid]];
                
                const isChecked = e.target.checked;
                if (isChecked) {
                    if (answers.value[qid].length >= maxSelect) {
                        e.preventDefault();
                        e.target.checked = false;
                        return;
                    }
                    if(!answers.value[qid].includes(e.target.value)) answers.value[qid].push(e.target.value);
                } else {
                    answers.value[qid] = answers.value[qid].filter(id => id !== e.target.value);
                }
                // multiple choice answer saved
            }
        }
    }, true);
    
    document.addEventListener('input', (e) => {
        if (e.target.matches('.inline-blank')) {
            const qid = e.target.getAttribute('data-question-id');
            const blankKey = e.target.getAttribute('data-blank-key');
            if (qid && blankKey) {
                if (!answers.value[qid]) answers.value[qid] = {};
                answers.value[qid][blankKey] = e.target.value;
            }
        }
    }, true);
    
    // Drag & drop is set up via initializeDragDrop() (manual mouse events) called from startAudioAndTest()
    // Event delegation: attaches once, works for all parts (no re-init needed for hidden elements).
});

onUnmounted(() => {
    document.body.classList.remove('ielts-test-mode');
    if (listeningStyles) listeningStyles.remove();
    if (listeningFixStyles) listeningFixStyles.remove();

    if (audioPlayer.value) {
        audioPlayer.value.pause();
        audioPlayer.value = null;
    }

    ddListenersAttached = false;
});
</script>

<template>
    <!-- Loading Screen — shown while page initializes -->
    <div v-if="isPageLoading" class="loading-screen" style="position: fixed; inset: 0; background: #000000; z-index: 999999; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 24px;">
        <div class="loading-spinner" style="width: 48px; height: 48px; border: 3px solid rgba(255,255,255,0.15); border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
        <div style="text-align: center;">
            <p style="color: #e2e8f0; font-size: 18px; font-weight: 600; margin: 0 0 6px 0;">IELTS Listening Test</p>
            <p style="color: #94a3b8; font-size: 14px; margin: 0;">Preparing your test environment...</p>
        </div>
    </div>

    <!-- Audio Start Overlay — shown after loading, before test begins -->
    <div v-if="!isPageLoading && !isTestStarted" id="audio-start-overlay" style="position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); z-index: 99999; display: flex; align-items: center; justify-content: center;">
        <div class="audio-overlay-content" style="text-align: center; color: white; max-width: 620px; width: 92%; padding: 30px 25px; display: flex; flex-direction: column; align-items: center;">
            <div class="audio-overlay-icon" style="margin-bottom: 25px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="1.2" style="width: 70px; height: 70px;">
                    <path d="M12 3C7.03 3 3 7.03 3 12v6c0 1.1.9 2 2 2h1c1.1 0 2-.9 2-2v-3c0-1.1-.9-2-2-2H5v-1c0-3.87 3.13-7 7-7s7 3.13 7 7v1h-1c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h1c1.1 0 2-.9 2-2v-6c0-4.97-4.03-9-9-9z"/>
                </svg>
            </div>
            <div class="audio-overlay-text" style="margin-bottom: 30px;">
                <p style="font-size: 16px; color: #d1d5db; margin: 0 0 10px 0; line-height: 1.6; font-weight: 500;">You will be listening to an audio clip during this test. You will not be permitted to pause or rewind the audio while answering the questions.</p>
                <p style="font-size: 16px; color: #d1d5db; margin: 0; line-height: 1.6;">To continue, click Play.</p>
            </div>
            <button @click="startAudioAndTest" id="start-audio-btn" style="background: rgba(255, 255, 255, 0.1); color: white; border: 1px solid rgba(255, 255, 255, 0.3); padding: 12px 40px; font-size: 15px; font-weight: 500; border-radius: 6px; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                Play
            </button>
        </div>
    </div>

    <AntiCheat />
    <ExitConfirmModal />
    <TextAnnotator ref="annotatorRef" :attempt-id="attempt.id" />
    <AutoSubmitOverlay 
        v-if="isTimeUpSubmit"
        :attemptId="attempt.id"
        :answers="answers"
        :storageKey="`listening_test_answers_${attempt.id}`"
        section="listening"
    />

    <!-- Fixed User Info Bar strictly mirroring blade -->
    <ExamHeader
        style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000; height: 50px;"
        :timeLimitSeconds="timeLimitSeconds"
        :serverTime="serverTime"
        :attemptStartTime="attempt.start_time"
        :showTimer="isTestStarted"
        :isReviewPhase="isReviewPhase"
        :reviewTimeSeconds="reviewTimeSeconds"
        @timeUp="handleTimeUp"
    >
        <template #extra-controls>
            <div class="volume-control">
                <!-- Mute/Unmute toggle button with reactive icon -->
                <button class="volume-btn" @click="toggleMute" :title="audioVolume == 0 ? 'Unmute' : 'Mute'">
                    <!-- Muted -->
                    <svg v-if="audioVolume == 0" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                    </svg>
                    <!-- Low volume -->
                    <svg v-else-if="audioVolume <= 40" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072"/>
                    </svg>
                    <!-- High volume -->
                    <svg v-else width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728"/>
                    </svg>
                </button>

                <!-- Styled range slider -->
                <div class="volume-slider-wrap">
                    <input type="range" min="0" max="100" v-model="audioVolume" class="volume-slider" id="volume-slider">
                    <div class="volume-fill" :style="{ width: audioVolume + '%' }"></div>
                </div>
            </div>
        </template>
    </ExamHeader>

    <!-- Main Container matching Blade exactly -->
    <div class="main-container">


        <audio v-if="audioUrl" ref="audioPlayer" :src="audioUrl" preload="auto" @ended="handleAudioEnd" class="hidden" style="display:none;"></audio>

        <div class="part-header-container" id="fixed-part-header" v-if="isTestStarted">
            <!-- Review phase banner -->
            <div v-if="isReviewPhase" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 20px; margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                <svg width="18" height="18" fill="none" stroke="#3b82f6" viewBox="0 0 24 24" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span style="font-size: 13px; color: #1e40af; font-weight: 600;">Review Time — Audio has ended. Use the remaining time to check your answers.</span>
            </div>
            <!-- Simulated fixed header styling matching blade's clone -->
            <div class="part-header" style="display: block; margin: 0; background: #f0f0f0; padding: 16px 24px; border-radius: 8px; border: 1px solid #e0e0e0; min-height: 80px;">
                <div class="part-title">Part {{ currentPart }}</div>
                <div class="part-instruction">Listen and answer questions {{ processedQuestionsByPart.find(p => p.part_number === currentPart)?.instructionRange || '' }}.</div>
            </div>
        </div>

        <!-- Content Area matching blade exactly -->
        <div class="content-area questions-container" v-if="isTestStarted" @input="handleContainerEvents" @change="handleContainerEvents" @click="handleContainerEvents">
            
            <div class="question-nav-arrows">
                <button type="button" class="nav-arrow prev-arrow" @click="prevQuestion" title="Previous Question"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                <button type="button" class="nav-arrow next-arrow" @click="nextQuestion" title="Next Question"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
            </div>
            
            <form @submit.prevent>
                <div v-for="part in processedQuestionsByPart" :key="part.part_number" 
                     :class="['part-section', { active: currentPart === part.part_number }]">
                    
                    <div class="part-header" style="display: none;">
                        <div class="part-title">Part {{ part.part_number }}</div>
                        <div class="part-instruction">Listen and answer questions {{ part.instructionRange }}.</div>
                    </div>

                    <div v-for="g in part.groups" :key="g.name">
                        <div v-if="g.name && g.name !== 'null'" class="question-group-header" style="font-weight: 600; margin-bottom: 12px; color: #1f2937;">{{ g.name }}</div>
                        
                        <template v-for="(q, idx) in g.questions" :key="q.id">
                            <div v-if="q.instructions && q.question_type !== 'multiple_choice' && (!g.questions[idx - 1] || g.questions[idx - 1].instructions !== q.instructions)"
                                 class="question-instructions" style="margin-bottom: 16px; font-weight: normal; color: #1f2937;" v-html="q.instructions"></div>

                            <div v-html="q.processedHtml"></div>
                        </template>
                    </div>
                </div>
            </form>
        </div>
        
    </div>

    <!-- Bottom Navigation matching blade exactly -->
    <div class="bottom-nav">
        <div class="nav-left">
            <div class="review-section">
                <input type="checkbox" id="review-checkbox" class="review-check"
                       :checked="isFlagged(currentFocusedNav)"
                       @change="toggleFlag(currentFocusedNav)">
                <label for="review-checkbox" class="review-label">Flag</label>
            </div>
            
            <div class="nav-section-container">
                <span class="section-label">Listening</span>
                
                <div class="parts-nav">
                    <button v-for="part in groupedQuestionsArray" :key="part.part_number"
                            :class="['part-btn', { active: currentPart === part.part_number }]"
                            @click="changePart(part.part_number)" type="button">
                        Part {{ part.part_number }}
                    </button>
                </div>
                
                <div class="nav-numbers">
                    <template v-for="part in groupedQuestionsArray" :key="'nav-'+part.part_number">
                         <div v-for="num in getPartNumberButtons(part.part_number)" :key="'nav-btn-'+num.displayNumber"
                              :class="['number-btn', {
                                  'hidden-part': currentPart !== part.part_number,
                                  flagged: isFlagged(num.displayNumber),
                                  answered: isAnswered(num.questionId, num.index, num.displayNumber),
                                  active: currentFocusedNav === num.displayNumber
                              }]"
                              @click="handleNavNumberClick(num)">
                              {{ num.displayNumber }}
                         </div>
                    </template>
                </div>
            </div>
        </div>
        
        <div class="nav-right">
             <button type="button" class="btn-secondary" @click="annotatorRef?.toggleNotesPanel()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Notepad
                <span v-if="annotatorRef?.notesCount > 0" class="notes-badge">{{ annotatorRef.notesCount }}</span>
            </button>
            <button type="button" class="submit-test-button" @click="showSubmitModal = true">
                Submit Test
            </button>
        </div>
    </div>

    <!-- Modals -->
    <!-- Submit Confirmation Modal -->
    <div v-if="showSubmitModal" style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);" @click="showSubmitModal = false"></div>
        <div style="position: relative; background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 380px; width: 100%; padding: 32px; text-align: center; animation: modalIn 0.3s cubic-bezier(0.34,1.56,0.64,1) forwards;">

            <!-- Icon -->
            <div style="width: 56px; height: 56px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <svg width="28" height="28" fill="none" stroke="#ef4444" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
            </div>

            <h3 style="font-size: 20px; font-weight: 700; color: #111827; margin: 0 0 8px;">End Test?</h3>
            <p style="font-size: 14px; color: #6b7280; margin: 0 0 28px; line-height: 1.6;">
                Are you sure you want to submit your answers? This action cannot be undone.
            </p>

            <!-- Buttons -->
            <div style="display: flex; gap: 10px;">
                <button @click="showSubmitModal = false" style="flex: 1; padding: 12px; background: #f3f4f6; color: #374151; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                    Continue Test
                </button>
                <button @click="finalSubmit" :disabled="isSubmitting" style="flex: 1; padding: 12px; background: #1a1a1a; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.background='#000'" onmouseout="this.style.background='#1a1a1a'">
                    {{ isSubmitting ? 'Submitting...' : 'Submit Test' }}
                </button>
            </div>
        </div>
    </div>
</template>

<style>
/* IELTS matching-letters matrix — defensive border rules to defeat any framework reset */
.ielts-matching-matrix table {
    border-collapse: collapse !important;
    border: 2px solid #374151 !important;
}
.ielts-matching-matrix th,
.ielts-matching-matrix td {
    border: 1px solid #374151 !important;
}
.ielts-matching-matrix thead tr {
    background: #e5e7eb !important;
}
.ielts-matching-matrix thead th {
    font-weight: 700 !important;
    color: #111827 !important;
    border-bottom: 2px solid #374151 !important;
}
.ielts-matching-matrix tbody tr + tr td {
    border-top: 1px solid #374151 !important;
}

/* Submit modal animation */
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

/* Loading spinner animation */
@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ==========================================
   Question heading — number + content inline
   ========================================== */
.ielts-q-heading {
    display: flex;
    align-items: baseline;
    gap: 6px;
    margin-bottom: 2px;
}

.ielts-q-heading .ielts-q-number {
    font-weight: 700 !important;
    font-size: 14px !important;
    color: #000000 !important;
    flex-shrink: 0;
    white-space: nowrap;
}

.ielts-q-heading .ielts-q-text {
    font-size: 14px;
    line-height: 1.6;
    color: #111827;
}

/* Strip unwanted margins from TinyMCE content inside inline heading */
.ielts-q-heading .ielts-q-text p {
    margin: 0 !important;
    display: inline;
}

@media (max-width: 768px) {
    .ielts-q-heading {
        gap: 5px;
        margin-bottom: 1px;
    }
    .ielts-q-heading .ielts-q-number {
        font-size: 13px !important;
    }
    .ielts-q-heading .ielts-q-text {
        font-size: 13px;
    }
}

/* Drag & Drop: uses INLINE STYLES + unique class names (dd-drop-zone, dd-drag-item, dd-source)
   No CSS conflicts with listening-style.css. Dragula classes (.gu-mirror, .gu-transit) in listening-test-fix.css */

/* Navigation button styles */
.number-btn {
    width: 32px;
    height: 32px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
}

.number-btn:hover {
    background: #f8f9fa;
    border-color: #3b82f6;
    color: #3b82f6;
}

.number-btn.answered {
    background: #10b981 !important;
    color: white !important;
    border-color: #10b981 !important;
    font-weight: 600 !important;
}

.number-btn.flagged {
    position: relative;
    overflow: visible;
}

.number-btn.flagged::after {
    content: '';
    position: absolute;
    top: -3px;
    right: -3px;
    width: 10px;
    height: 10px;
    background: #f59e0b;
    border-radius: 50%;
    border: 2px solid white;
}

.number-btn.hidden-part {
    display: none;
}

/* Active nav button — currently focused question */
.number-btn.active {
    outline: 2px solid #3b82f6;
    outline-offset: 1px;
    color: #2563eb;
    font-weight: 700;
}

/* ==========================================
   Reactive question-number highlight in content area
   Blue transparent — applied to the specific number element
   ========================================== */
.q-num-highlight {
    background-color: rgba(59, 130, 246, 0.15) !important;
    outline: 2px solid rgba(59, 130, 246, 0.45) !important;
    outline-offset: 2px !important;
    border-radius: 4px !important;
    transition: background-color 0.25s ease, outline-color 0.25s ease !important;
}

/* dropdown / input variants need slight padding tweak */
select.inline-dropdown.q-num-highlight {
    outline-offset: 1px !important;
}

input.inline-blank.q-num-highlight,
input.inline-blank-input.q-num-highlight,
input.gap-input.q-num-highlight {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.18) !important;
    outline: none !important;
}

/* drop-zone variant */
.dd-drop-zone.q-num-highlight {
    border-color: #1f2937 !important;
    border-style: solid !important;
    background: rgba(31, 41, 55, 0.06) !important;
    box-shadow: 0 0 0 3px rgba(31, 41, 55, 0.12) !important;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .q-num-highlight {
        outline-width: 1.5px !important;
        outline-offset: 1px !important;
    }
    .number-btn.active {
        outline-width: 1.5px;
    }
}

/* ==========================================
   Volume Control — ExamHeader slot
   ========================================== */
.volume-control {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 8px;
}

.volume-btn {
    background: none;
    border: none;
    color: rgba(255,255,255,0.7);
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.volume-btn:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
}

.volume-slider-wrap {
    position: relative;
    width: 80px;
    height: 20px;
    display: flex;
    align-items: center;
}

.volume-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 4px;
    border-radius: 2px;
    background: rgba(255,255,255,0.15);
    outline: none;
    position: relative;
    z-index: 2;
    cursor: pointer;
}

.volume-fill {
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    height: 4px;
    border-radius: 2px;
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
    pointer-events: none;
    z-index: 1;
    transition: width 0.1s ease;
}

/* Slider thumb */
.volume-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.3);
    cursor: pointer;
    position: relative;
    z-index: 3;
    transition: transform 0.15s ease;
}

.volume-slider::-webkit-slider-thumb:hover {
    transform: scale(1.2);
}

.volume-slider::-webkit-slider-thumb:active {
    transform: scale(1.3);
    box-shadow: 0 0 0 4px rgba(59,130,246,0.3), 0 1px 4px rgba(0,0,0,0.3);
}

.volume-slider::-moz-range-thumb {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.3);
    cursor: pointer;
    border: none;
}

.volume-slider::-moz-range-track {
    background: transparent;
    height: 4px;
}
</style>
