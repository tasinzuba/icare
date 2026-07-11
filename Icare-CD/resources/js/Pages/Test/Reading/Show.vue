<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import debounce from 'lodash/debounce';
import Split from 'split.js';
import dragula from 'dragula';
import 'dragula/dist/dragula.css';
// CSS is injected dynamically in onMounted to prevent global scope bleeding
import ExamHeader from '@/Components/Exam/ExamHeader.vue';
import TextAnnotator from '@/Components/Exam/TextAnnotator.vue';
import AutoSubmitOverlay from '@/Components/Exam/AutoSubmitOverlay.vue';
import AntiCheat from '@/Components/Exam/AntiCheat.vue';
import ExitConfirmModal from '@/Components/Exam/ExitConfirmModal.vue';

const props = defineProps({
    testSet: { type: Object, required: true },
    attempt: { type: Object, required: true },
    initialAnswers: { type: Object, default: () => ({}) },
    timeLimitSeconds: { type: Number, required: true },
    serverTime: { type: String, required: true },
    attemptStartTime: { type: String, required: true }
});

const page = usePage();

// View state
const activePart = ref(1);
const isSubmitting = ref(false);
const saveStatus = ref('');

const STORAGE_KEY = `reading_test_answers_${props.attempt.id}`;

const getInitialAnswers = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored);
            // Ensure it's a plain object, not an array
            if (parsed && typeof parsed === 'object' && !Array.isArray(parsed) && Object.keys(parsed).length > 0) {
                return parsed;
            }
        }
    } catch (e) {
        console.error('Error reading from localStorage', e);
    }
    // Convert array to plain object if needed (PHP may send [] instead of {})
    const initial = props.initialAnswers || {};
    if (Array.isArray(initial)) {
        const obj = {};
        initial.forEach((val, idx) => {
            if (val !== null && val !== undefined) {
                obj[idx] = val;
            }
        });
        return obj;
    }
    return initial;
};

// Answers state
const answers = ref(getInitialAnswers());

// Group passages and questions by part
const passagesByPart = computed(() => {
    const groups = {};
    const passages = props.testSet.questions.filter(q => q.question_type === 'passage');
    passages.forEach(p => {
        if (!groups[p.part_number]) groups[p.part_number] = [];
        groups[p.part_number].push(p);
    });
    return groups;
});

const questionsByPart = computed(() => {
    const groups = {};
    const qs = props.testSet.questions.filter(q => q.question_type !== 'passage');
    qs.forEach(q => {
        if (!groups[q.part_number]) groups[q.part_number] = [];
        groups[q.part_number].push(q);
    });
    return groups;
});

const totalParts = computed(() => Math.max(
    ...Object.keys(passagesByPart.value).map(Number),
    ...Object.keys(questionsByPart.value).map(Number),
    1
));

// AutoSave logic
const autoSave = debounce(async () => {
    saveStatus.value = 'saving';
    Object.keys(answers.value).forEach(key => {
        if(answers.value[key] === null || answers.value[key] === undefined) delete answers.value[key];
    });
    try {
        await axios.post(`/student/test/reading/auto-save/${props.attempt.id}`, {
            answers: answers.value
        });
        saveStatus.value = 'saved';
        setTimeout(() => { if(saveStatus.value === 'saved') saveStatus.value = '' }, 2000);
    } catch (e) {
        saveStatus.value = 'error';
    }
}, 5000);

watch(answers, (newVal) => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newVal));
    } catch (e) {}
    autoSave();
}, { deep: true });

// Expose to window for traditional scripts (dragula)
window.readingTestAnswers = answers;

// Navigation logic 
const displayQuestions = computed(() => {
    const results = [];
    let currentQuestionNumber = 1;

    const allQuestions = props.testSet.questions
        .filter(q => q.question_type !== 'passage')
        .sort((a, b) => {
            if (a.part_number !== b.part_number) return a.part_number - b.part_number;
            return a.order_number - b.order_number;
        });

    allQuestions.forEach(question => {
        let blankCount = 0;

        if (question.question_type === 'matching_headings' && question.section_specific_data?.mappings?.length > 1) {
            const individualNumbers = question.section_specific_data.mappings.length; 
            
            const questionNumbers = [];
            for (let i = 0; i < individualNumbers; i++) {
                questionNumbers.push(currentQuestionNumber + i);
            }

            results.push({
                question: question,
                has_blanks: false,
                is_master: true,
                display_number: currentQuestionNumber,
                question_numbers: questionNumbers,
                count: individualNumbers
            });
            currentQuestionNumber += individualNumbers;

        } else if (question.question_type === 'multiple_choice') {
            const correctCount = question.max_selections || 1;
            
            if (correctCount > 1) {
                const questionNumbers = [];
                for (let i = 0; i < correctCount; i++) {
                    questionNumbers.push(currentQuestionNumber + i);
                }
                
                results.push({
                    question: question,
                    has_blanks: false,
                    is_multiple_choice: true,
                    display_number: currentQuestionNumber,
                    question_numbers: questionNumbers,
                    count: correctCount
                });
                currentQuestionNumber += correctCount;
            } else {
                results.push({
                    question: question,
                    has_blanks: false,
                    display_number: currentQuestionNumber
                });
                currentQuestionNumber++;
            }
        } else if (question.question_type === 'sentence_completion' && question.section_specific_data?.sentence_completion) {
            const scData = question.section_specific_data.sentence_completion;
            const sentenceCount = scData.sentences ? scData.sentences.length : 0;
            
            if (sentenceCount > 0) {
                const questionNumbers = [];
                for (let i = 0; i < sentenceCount; i++) {
                    questionNumbers.push(currentQuestionNumber + i);
                }
                
                results.push({
                    question: question,
                    has_blanks: false,
                    is_sentence_completion: true,
                    display_number: currentQuestionNumber,
                    question_numbers: questionNumbers,
                    count: sentenceCount
                });
                currentQuestionNumber += sentenceCount;
            } else {
                results.push({
                    question: question,
                    has_blanks: false,
                    display_number: currentQuestionNumber
                });
                currentQuestionNumber++;
            }
        } else {
            const blankMatches = question.content?.match(/\[BLANK_\d+\]|\[____\d+____\]/g) || [];
            const dropdownMatches = question.content?.match(/\[DROPDOWN_\d+\]/g) || [];
            const headingDropdownMatches = question.content?.match(/\[HEADING_DROPDOWN_\d+\]/g) || [];
            
            if (question.question_type === 'dropdown_selection') {
                blankCount = dropdownMatches.length;
            } else {
                blankCount = blankMatches.length + dropdownMatches.length + headingDropdownMatches.length;
            }
            
            if (blankCount > 0) {
                const blankNumbers = {};
                for (let i = 1; i <= blankCount; i++) {
                    blankNumbers[i] = currentQuestionNumber;
                    currentQuestionNumber++;
                }
                
                results.push({
                    question: question,
                    has_blanks: true,
                    blank_numbers: blankNumbers,
                    first_number: blankNumbers[1]
                });
            } else {
                results.push({
                    question: question,
                    has_blanks: false,
                    display_number: currentQuestionNumber
                });
                currentQuestionNumber++;
            }
        }
    });

    return results;
});

// PASSAGE DOM INJECTION LOGIC 
const injectPassageDropZones = () => {
    const allDropZones = [];
    
    props.testSet.questions.forEach(q => {
        if (q.question_type === 'matching_headings' && q.section_specific_data?.mappings?.length > 1) {
            const mappedQs = q.section_specific_data.mappings; 
            mappedQs.forEach(mapping => {
                allDropZones.push({
                    para: mapping.paragraph || 'A',
                    num: mapping.question || 0,
                    qid: q.id,
                    part: q.part_number,
                    question: q
                });
            });
        }
    });

    if (allDropZones.length === 0) return;

    const dropZonesByPart = {};
    allDropZones.forEach(dz => {
        if (!dropZonesByPart[dz.part]) dropZonesByPart[dz.part] = [];
        dropZonesByPart[dz.part].push(dz);
    });

    for (const [part, zones] of Object.entries(dropZonesByPart)) {
        const passageEl = document.getElementById(`passage-content-${part}`);
        if (!passageEl) continue;

        const allPs = passageEl.querySelectorAll('p');
        
        zones.forEach(dz => {
            // Look up actual display number from displayQuestions computed
            const displayItem = displayQuestions.value.find(item => item.question?.id === dz.qid && item.is_master);
            const displayNum = displayItem?.question_numbers?.[dz.num - 1] ?? dz.num;

            allPs.forEach(p => {
                const strong = p.querySelector('strong');

                if (strong) {
                    const text = strong.textContent.trim().replace(/\s+/g, '');

                    if (text === dz.para && !p.nextElementSibling?.classList.contains('passage-drop-zone-wrapper')) {
                        const box = document.createElement('div');
                        box.className = 'passage-drop-zone-wrapper';
                        box.style.cssText = 'margin: 10px 20% 12px 0;';
                        
                        const savedAnswer = answers.value[`${dz.qid}_q${dz.num}`] || answers.value[dz.qid]?.[`heading_${dz.num}`] || '';
                        const hasAnswer = savedAnswer !== '';
                        
                        // Find the heading text for saved answer
                        let headingText = savedAnswer;
                        if (hasAnswer && dz.question.section_specific_data?.headings) {
                            const h = dz.question.section_specific_data.headings.find(h => h.id === savedAnswer);
                            if (h) headingText = h.text;
                        } else if (hasAnswer && dz.question.options) {
                            const idx = savedAnswer.charCodeAt(0) - 65;
                            if (dz.question.options[idx]) headingText = dz.question.options[idx].content;
                        }

                        let answerHtml = '';
                        if (hasAnswer) {
                            answerHtml = `
                                <div class="mh-heading-item" data-heading="${savedAnswer}" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 12px; background-color: #ffffff; cursor: move; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: box-shadow 0.2s; min-height: 36px; display: flex; align-items: center;">
                                    <span style="color: #1f2937; line-height: 1.5; font-weight: 500;">${headingText}</span>
                                </div>
                            `;
                        }

                        box.innerHTML = `
                            <div class="passage-drop-zone passage-drop-${dz.qid}"
                                 data-question-number="${dz.num}"
                                 data-paragraph="${dz.para}"
                                 style="width: 100%; min-height: 48px; border: 1px dashed #334155; border-radius: 6px; padding: ${hasAnswer ? '4px' : '6px 16px'}; background: transparent; display: flex; align-items: center; ${hasAnswer ? 'justify-content: flex-start;' : 'justify-content: center;'} transition: all 0.2s;">
                                
                                <div class="passage-empty-state" style="color: #64748b; font-size: 14px; font-weight: 600; pointer-events: none; ${hasAnswer ? 'display: none;' : ''}">
                                    Question ${displayNum}
                                </div>
                                
                                ${answerHtml}
                            </div>
                            <input type="hidden" id="input_${dz.qid}_q${dz.num}" data-qid="${dz.qid}" data-heading="${dz.num}" name="${dz.qid}_q${dz.num}" class="passage-answer-input" value="${savedAnswer}">
                        `;
                        
                        p.parentNode.insertBefore(box, p.nextSibling);

                        const hiddenInput = document.getElementById(`input_${dz.qid}_q${dz.num}`);
                        if (hiddenInput) {
                            hiddenInput.addEventListener('change', (e) => {
                                answers.value[`${dz.qid}_q${dz.num}`] = e.target.value;
                            });
                        }
                    }
                }
            });
        });
    }
};


// Complex Grouping for Questions Rendering
const groupedQuestionsData = computed(() => {
    const partsMap = {};

    questionsByPart.value && Object.keys(questionsByPart.value).forEach(partNum => {
        const qList = questionsByPart.value[partNum];
        
        let processedContentList = [];

        // Match the layout object building from blade
        qList.forEach(question => {

            // Find its displayQuestion entry for number/count details
            const dItem = displayQuestions.value.find(dq => dq.question.id === question.id);
            if(!dItem) return;

            // Generate content
            let finalContent = question.content || '';

            if (dItem.has_blanks && dItem.blank_numbers) {
                let blankCounter = 0;
                
                // Replace Blanks with Inputs
                finalContent = finalContent.replace(/\[BLANK_(\d+)\]|\[____(\d+)____\]/g, (match, p1, p2) => {
                    blankCounter++;
                    const displayNum = dItem.blank_numbers[blankCounter];
                    
                    return `<input type="text" class="gap-input inline-blank-input border-b-2 border-gray-400 focus:border-blue-500 outline-none w-24 text-center px-1 font-bold text-gray-800 bg-transparent" data-qid="${question.id}" data-blank="blank_${blankCounter}" value="" placeholder="${displayNum}">`;
                });
                
                // Replace Heading Dropdowns
                finalContent = finalContent.replace(/\[HEADING_DROPDOWN_(\d+)\]/g, (match, dropdownNum) => {
                    blankCounter++;
                    const displayNum = dItem.blank_numbers[blankCounter];
                    
                    let headingOptions = [];
                    if (question.question_group) {
                        const allQs = props.testSet.questions;
                        const headingQuestion = allQs.find(q => q.question_type === 'matching_headings' && q.question_group === question.question_group && q.part_number === question.part_number);
                        if (headingQuestion && headingQuestion.options) {
                            headingOptions = headingQuestion.options;
                        }
                    }
                    
                    let selectHtml = `<select class="gap-dropdown-input gap-dropdown border border-gray-300 rounded px-2 py-1 ml-1 mr-1 text-gray-700 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer hover:border-blue-400" data-qid="${question.id}" data-dropdown="heading_${dropdownNum}">
                        <option value="">${displayNum}</option>`;
                    
                    headingOptions.forEach((opt, index) => {
                        const letter = String.fromCharCode(65 + index);
                        selectHtml += `<option value="${opt.id}">${letter}</option>`;
                    });
                    
                    selectHtml += `</select>`;
                    return selectHtml;
                });
                
                // Replace Standard Dropdowns
                if (question.section_specific_data?.dropdown_options) {
                    const dropdownOptions = question.section_specific_data.dropdown_options;
                    finalContent = finalContent.replace(/\[DROPDOWN_(\d+)\]/g, (match, dropdownNum) => {
                        blankCounter++;
                        const displayNum = dItem.blank_numbers[blankCounter];
                        const optionsStr = dropdownOptions[dropdownNum];
                        const options = optionsStr ? optionsStr.split(',').map(s => s.trim()) : [];
                        
                        let selectHtml = `<select class="gap-dropdown-input gap-dropdown border border-gray-300 rounded px-2 py-1 ml-1 mr-1 text-gray-700 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer hover:border-blue-400" data-qid="${question.id}" data-dropdown="dropdown_${dropdownNum}">
                        <option value="">${displayNum}</option>`;
                        
                        options.forEach(opt => {
                            selectHtml += `<option value="${opt}">${opt}</option>`;
                        });
                        
                        selectHtml += `</select>`;
                        return selectHtml;
                    });
                }
            }

            processedContentList.push({
                ...dItem, 
                question: question,
                processedHtml: finalContent,
                instruction: question.instructions || null,
                group: question.question_group || null,
                is_master: question.question_type === 'matching_headings' && question.section_specific_data?.mappings?.length > 1
            });
        });

        const instructionGroups = {};
        processedContentList.forEach(item => {
            const iKey = item.instruction || `no-inst-${item.question.id}`;
            if (!instructionGroups[iKey]) instructionGroups[iKey] = [];
            instructionGroups[iKey].push(item);
        });

        partsMap[partNum] = instructionGroups;
    });

    return partsMap;
});

// Build IELTS matrix rows for a dropdown_selection question.
// Cached per question id so the template can reference the same object across multiple v-fors.
// Only renders as matrix when ALL dropdown options are single letters (A, B, C...) — the
// IELTS "match statement to paragraph/feature" format. Word/phrase options stay as inline
// dropdowns (cloze-style fill-in-the-blank where sentence context matters).
const dropdownMatrixCache = new Map();
const buildDropdownMatrix = (item) => {
    const q = item.question;
    if (dropdownMatrixCache.has(q.id)) return dropdownMatrixCache.get(q.id);

    const opts = q.section_specific_data?.dropdown_options || {};
    const firstKey = Object.keys(opts)[0];
    const defaultColumns = firstKey ? String(opts[firstKey]).split(',').map(s => s.trim()) : [];

    // Heuristic: matrix only when every option is a single uppercase letter (matching-to-letter style)
    const isLetterOptions = defaultColumns.length > 0 && defaultColumns.every(c => /^[A-Z]$/.test(c));

    const content = q.content || '';
    const regex = /\[DROPDOWN_(\d+)\]/g;
    const rows = [];
    let cursor = 0;
    let rowIdx = 0;
    let match;
    // Decode HTML entities + strip tags by letting the browser parse the fragment once.
    const decodeAndStrip = (raw) => {
        const tmp = document.createElement('div');
        tmp.innerHTML = raw;
        // Trim, drop leading bullet/dash/numbering markers that came from list HTML.
        return (tmp.textContent || '')
            .replace(/\s+/g, ' ')
            .trim()
            .replace(/^[•·▪●◦*\-–—]+\s*/, '')
            .replace(/^\d+[.)]\s+/, '')
            .trim();
    };

    while ((match = regex.exec(content)) !== null) {
        const dropdownNum = match[1];
        const before = content.substring(cursor, match.index);
        const statement = decodeAndStrip(before);
        if (statement) {
            const colStr = opts[dropdownNum];
            rows.push({
                dropdownNum,
                statement,
                displayNum: item.blank_numbers?.[rowIdx + 1] ?? ((item.first_number ?? 0) + rowIdx),
                columns: colStr ? String(colStr).split(',').map(s => s.trim()) : defaultColumns,
            });
            rowIdx++;
        }
        cursor = match.index + match[0].length;
    }
    const result = { rows, columns: defaultColumns, isMatrix: isLetterOptions && rows.length > 0 };
    dropdownMatrixCache.set(q.id, result);
    return result;
};

const onDropdownMatrixChange = (qid, dropdownNum, value) => {
    if (!answers.value[qid] || typeof answers.value[qid] !== 'object') {
        answers.value[qid] = {};
    }
    answers.value[qid]['dropdown_' + dropdownNum] = value;
};

const setupInputListeners = () => {
    const inputs = document.querySelectorAll('.inline-blank-input');
    inputs.forEach(input => {
        const qid = input.getAttribute('data-qid');
        const blankKey = input.getAttribute('data-blank');
        
        const newInp = input.cloneNode(true);
        input.parentNode.replaceChild(newInp, input);
        
        // Restore value on the NEW node manually so reactivity does not remount HTML
        if (answers.value[qid] && answers.value[qid][blankKey] !== undefined) {
            newInp.value = answers.value[qid][blankKey];
        }
        
        newInp.addEventListener('input', (e) => {
            if(!answers.value[qid]) answers.value[qid] = {};
            answers.value[qid][blankKey] = e.target.value;
        });
    });

    const dropdowns = document.querySelectorAll('.gap-dropdown-input');
    dropdowns.forEach(sel => {
        const qid = sel.getAttribute('data-qid');
        const dropdownKey = sel.getAttribute('data-dropdown');
        
        const newSel = sel.cloneNode(true);
        sel.parentNode.replaceChild(newSel, sel);
        
        // Restore value on the NEW node manually
        if (answers.value[qid] && answers.value[qid][dropdownKey] !== undefined) {
            newSel.value = answers.value[qid][dropdownKey];
        }
        
        newSel.addEventListener('change', (e) => {
            if(!answers.value[qid]) answers.value[qid] = {};
            answers.value[qid][dropdownKey] = e.target.value;
        });
    });
}

const handleMultipleChoiceChange = (e, item, optId) => {
    const qid = item.question.id;
    if (!answers.value[qid]) {
        answers.value[qid] = [];
    }
    
    // Ensure it's an array
    if (!Array.isArray(answers.value[qid])) {
        answers.value[qid] = [answers.value[qid]];
    }
    
    const max = item.count || 1;
    const isChecked = e.target.checked;
    
    if (isChecked) {
        if (answers.value[qid].length >= max) {
            e.preventDefault();
            e.target.checked = false;
            // Limit reached, ignore quietly (UI will have disabled the unselected checkboxes)
            return;
        }
        if (!answers.value[qid].includes(optId)) {
            answers.value[qid].push(optId);
        }
    } else {
        answers.value[qid] = answers.value[qid].filter(id => id !== optId);
    }
};

const initMultipleChoiceArrays = () => {
    groupedQuestionsData.value && Object.values(groupedQuestionsData.value).forEach(groups => {
        Object.values(groups).forEach(items => {
            items.forEach(item => {
                if (item.question.question_type === 'multiple_choice') {
                    const qid = item.question.id;
                    const val = answers.value[qid];
                    
                    if (!val) {
                        answers.value[qid] = [];
                    } else if (!Array.isArray(val)) {
                        // Cast existing boolean map from legacy bug back to array
                        if (typeof val === 'object' && val !== null) {
                            const newArr = [];
                            Object.keys(val).forEach(k => {
                                if (val[k] === true) newArr.push(Number(k));
                            });
                            answers.value[qid] = newArr;
                        } else {
                            answers.value[qid] = [Number(val) || val];
                        }
                    } else {
                        // Clean up nulls and sync string numbers to actual numbers
                        answers.value[qid] = val
                            .filter(v => v !== null && v !== undefined && v !== '')
                            .map(v => (!isNaN(Number(v)) ? Number(v) : v));
                    }
                }
            });
        });
    });
};


// Reactive source list — keeps Vue in sync with Dragula's DOM moves
const headingSources = ref({});

const initHeadingSources = () => {
    const newSources = {};
    props.testSet.questions.forEach(q => {
        if (q.question_type === 'matching_headings' && q.section_specific_data?.mappings?.length > 1) {
            let available = [];
            if (q.section_specific_data?.headings?.length) {
                available = [...q.section_specific_data.headings];
            } else if (q.options?.length) {
                available = q.options.map((opt, idx) => ({ id: String.fromCharCode(65 + idx), text: opt.content }));
            }
            const usedIds = [];
            Object.keys(answers.value).forEach(k => {
                if (k.startsWith(`${q.id}_q`)) {
                    usedIds.push(answers.value[k]);
                }
            });
            newSources[q.id] = available.filter(h => !usedIds.includes(h.id));
        }
    });
    headingSources.value = newSources;
};

// Render source items via DOM (NOT Vue v-for) so Dragula has full control
const populateSourceContainers = () => {
    Object.keys(headingSources.value).forEach(qid => {
        const sourceEl = document.getElementById(`mh-source-${qid}`);
        if (!sourceEl) return;
        sourceEl.innerHTML = '';
        const items = headingSources.value[qid] || [];
        items.forEach(h => {
            const div = document.createElement('div');
            div.className = 'mh-heading-item cursor-move transition-shadow py-2 px-3 bg-white border border-slate-200 rounded shadow-sm hover:shadow-md hover:border-blue-300 w-full block';
            div.setAttribute('data-heading', h.id);
            div.innerHTML = `<span class="text-gray-800 leading-relaxed font-medium block">${h.text}</span>`;
            sourceEl.appendChild(div);
        });
    });
};

let splitInstance = null;
let dragulaInstances = [];

const initializeDragula = () => {
    dragulaInstances.forEach(d => d.destroy());
    dragulaInstances = [];
    
    props.testSet.questions.forEach(q => {
        if (q.question_type === 'matching_headings' && q.section_specific_data?.mappings?.length > 1) {
            const sourceEl = document.getElementById(`mh-source-${q.id}`);
            if (!sourceEl) return;
            
            const dropZoneEls = document.querySelectorAll(`.passage-drop-zone.passage-drop-${q.id}`);
            const containers = [sourceEl, ...dropZoneEls];
            
            const drake = dragula(containers, {
                copy: false,
                moves: (el) => el.classList.contains('mh-heading-item'),
                accepts: (el, target) => {
                    if (target.id === `mh-source-${q.id}`) return true;
                    return true;
                },
                revertOnSpill: true
            });
            
            drake.on('drop', (el, target, source) => {
                if (!target) return;
                
                const headingId = el.getAttribute('data-heading');
                
                if (target.classList.contains('passage-drop-zone')) {
                    const qNum = target.getAttribute('data-question-number');
                    const dzKey = `${q.id}_q${qNum}`;
                    
                    const hiddenInput = document.getElementById(`input_${dzKey}`);
                    if (hiddenInput) {
                        hiddenInput.value = headingId;
                        hiddenInput.dispatchEvent(new Event('change'));
                    }
                    
                    answers.value[dzKey] = headingId;
                    
                    const emptyState = target.querySelector('.passage-empty-state');
                    if (emptyState) emptyState.style.display = 'none';
                    
                } else if (target.id === `mh-source-${q.id}`) {
                    if (source.classList.contains('passage-drop-zone')) {
                        const qNum = source.getAttribute('data-question-number');
                        const dzKey = `${q.id}_q${qNum}`;
                        
                        const hiddenInput = document.getElementById(`input_${dzKey}`);
                        if (hiddenInput) {
                            hiddenInput.value = '';
                            hiddenInput.dispatchEvent(new Event('change'));
                        }
                        
                        const newAnswers = {...answers.value};
                        delete newAnswers[dzKey];
                        answers.value = newAnswers;
                        
                        const emptyState = source.querySelector('.passage-empty-state');
                        if (emptyState) emptyState.style.display = '';
                    }
                }
            });
            
            dragulaInstances.push(drake);
        }
    });
};

const annotatorRef = ref(null);

let readingStyles = null;

onMounted(() => {
    document.body.classList.add('ielts-test-mode');
    readingStyles = document.createElement('link');
    readingStyles.rel = 'stylesheet';
    readingStyles.href = '/css/reading-test.css';
    document.head.appendChild(readingStyles);

    splitInstance = Split(['#passage-section', '#questions-section'], {
        sizes: [50, 50],
        minSize: [300, 300],
        gutterSize: 12,
        cursor: 'col-resize',
        direction: 'horizontal'
    });

    initHeadingSources();

    nextTick(() => {
        initMultipleChoiceArrays();
        injectPassageDropZones();
        populateSourceContainers();
        setupInputListeners();
        nextTick(() => {
            initializeDragula();
        });
    });

    setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
        if (typeof window.HelpGuide !== 'undefined') {
            window.HelpGuide.init();
        }
    }, 100);
});

watch(activePart, () => {
    nextTick(() => {
        injectPassageDropZones();
        populateSourceContainers();
        setupInputListeners();
        nextTick(() => {
            initializeDragula();
        });
    });
});

onUnmounted(() => {
    document.body.classList.remove('ielts-test-mode');
    if (readingStyles) readingStyles.remove();

    autoSave.cancel();
    if (splitInstance) {
        splitInstance.destroy();
    }
    dragulaInstances.forEach(d => d.destroy());
    dragulaInstances = [];
});


const isTimeUpSubmit = ref(false);

const submitTest = () => {
    if (!confirm('Are you sure you want to finish the Reading test?')) return;
    isTimeUpSubmit.value = false;
    isSubmitting.value = true;
    
    // Flatten answers to match what Laravel expects (questionId => answer)
    // Blade used flat inputs like name="answers[807_q1]" and name="answers[808]"
    const payload = {};
    if (Object.keys(answers.value).length > 0) {
        Object.assign(payload, answers.value);
    } else {
        payload["__empty"] = true;
    }
    
    // Clear localStorage upon manual submit
    localStorage.removeItem(STORAGE_KEY);
    
    router.post(`/student/test/reading/submit/${props.attempt.id}`, {
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
    isTimeUpSubmit.value = true;
    
    // Stop autosave
    if (autosaveInterval) {
        clearInterval(autosaveInterval);
    }
};

const scrollToQuestion = (partNum, questionId) => {
    activePart.value = partNum;
    nextTick(() => {
        const el = document.getElementById('question-' + questionId);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50/50');
            setTimeout(() => {
                el.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50/50');
            }, 1500);
        }
    });
};

const isAnswered = (item, type, indexOrKey, number) => {
    const qid = item.question?.id;
    if (!qid) return false;
    
    const ans = answers.value;
    
    if (type === 'blank') {
        const blankKey = 'blank_' + indexOrKey;
        const dropdownKey = 'dropdown_' + indexOrKey;
        const headingKey = 'heading_' + indexOrKey;
        
        if (ans[qid]) {
            if (ans[qid][blankKey] && String(ans[qid][blankKey]).trim() !== '') return true;
            if (ans[qid][dropdownKey] && String(ans[qid][dropdownKey]).trim() !== '') return true;
            if (ans[qid][headingKey] && String(ans[qid][headingKey]).trim() !== '') return true;
        }
        return false;
    }
    
    if (type === 'group') {
        // For matching headings (is_master), answers are stored as "qid_q{mapping.question}"
        // where mapping.question is the actual question number from DB (e.g., 27, 28, 29...)
        // NOT a sequential 1-based index. We must use the actual mapping.question value
        // to match the key used when storing answers via drag-and-drop.
        const mappings = item.question?.section_specific_data?.mappings;
        const mappingNum = item.is_master && mappings?.[indexOrKey]
            ? (mappings[indexOrKey].question || (indexOrKey + 1))
            : (item.is_master ? (indexOrKey + 1) : number);
        if (ans[`${qid}_q${mappingNum}`] && String(ans[`${qid}_q${mappingNum}`]).trim() !== '') return true;

        if (ans[qid]) {
            const sel = ans[qid];
            if (item.is_multiple_choice) {
                if (Array.isArray(sel) && sel.length > indexOrKey) return true;
                if (typeof sel === 'object' && !Array.isArray(sel) && sel[indexOrKey]) return true;
            } else {
                if (sel[indexOrKey] && String(sel[indexOrKey]).trim() !== '') return true;
                if (sel['heading_'+indexOrKey] && String(sel['heading_'+indexOrKey]).trim() !== '') return true;
            }
        }
        return false;
    }
    
    if (type === 'single') {
        const val = ans[qid];
        if (val !== undefined && val !== null) {
            if (typeof val === 'string' || typeof val === 'number') return String(val).trim() !== '';
            if (Array.isArray(val)) return val.length > 0;
            if (typeof val === 'object') return Object.keys(val).length > 0;
        }
        if (ans['q_'+qid]) return true;
        return false;
    }
    
    return false;
};

</script>

<template>
    <AntiCheat />
    <ExitConfirmModal />
    
    <!-- Auto-Submit Overlay (Time's Up) -->
    <AutoSubmitOverlay 
        v-if="isTimeUpSubmit"
        :attemptId="attempt.id"
        :answers="answers"
        :storageKey="STORAGE_KEY"
    />

    <!-- Manual Submission Loading State -->
    <div v-if="isSubmitting && !isTimeUpSubmit" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-white/95 backdrop-blur-md">
        <div class="relative w-20 h-20 mb-8">
            <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
            <div class="absolute inset-0 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
        </div>
        
        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Submitting...</h2>
        <p class="text-slate-600 mt-3 text-lg font-medium">Saving your test answers...</p>
        <p class="text-slate-400 mt-1 text-sm">Please wait while we secure your test data.</p>
    </div>

    <div class="h-screen bg-[#f3f4f6] flex flex-col font-sans overflow-hidden">
        
        <ExamHeader
            moduleName="Reading"
            :attempt="attempt"
            :timeLimitSeconds="timeLimitSeconds"
            :serverTime="serverTime"
            :attemptStartTime="attemptStartTime"
            @timeUp="handleTimeUp"
        />

        <main class="flex-1 flex overflow-hidden relative pb-[60px]">
            
            <div id="passage-section" class="h-full bg-white overflow-y-auto relative custom-scrollbar pb-10">


                <div class="px-8 py-8">
                    <div v-for="(passages, partNum) in passagesByPart" :key="'p'+partNum" v-show="activePart === Number(partNum)">
                        <div :id="'passage-content-' + partNum" class="transition-all duration-300">
                            <div v-for="passage in passages" :key="passage.id" class="mb-10 text-gray-800 text-base leading-relaxed passage-content prose prose-slate max-w-none">
                                <div v-html="passage.passage_text || passage.content"></div>
                                <img v-if="passage.media_path" :src="'/storage/' + passage.media_path" class="max-w-full rounded mt-6 border border-gray-200 shadow-sm" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="questions-section" class="h-full bg-slate-50 overflow-y-auto px-8 py-8 custom-scrollbar pb-10">
                <div v-for="(instructionGroups, partNum) in groupedQuestionsData" :key="'qpart'+partNum" v-show="activePart === Number(partNum)">
                    
                    <div v-for="(items, instruction) in instructionGroups" :key="instruction">

                        <!-- Suppress instruction block when the whole group is multiple_choice: admin writes the instruction inside the question content itself, and the saved `instructions` field is just the default "Choose TWO letters, A-E" which would duplicate. -->
                        <div v-if="!instruction.startsWith('no-inst-') && !items.every(it => it.question.question_type === 'multiple_choice')" class="question-instructions" style="margin-bottom: 16px; font-weight: normal; color: #1f2937;" v-html="instruction"></div>

                        <div v-for="item in items" :key="item.question.id" :id="'question-' + item.question.id" class="ielts-question-item" style="margin-bottom: 24px;">
                            
                            <div v-if="item.question.question_type === 'matching_headings' && item.is_master">
                                <div class="font-bold text-gray-900 mb-4 text-base">List of Headings</div>
                                
                                <div :id="'mh-source-' + item.question.id" class="flex flex-col gap-3 mh-source-container bg-transparent min-h-[100px] mb-6">
                                    <!-- Source items rendered by JavaScript, managed by Dragula -->
                                </div>
                                
                                <p class="text-sm text-gray-500 mt-4 italic"><span class="font-semibold text-gray-700">Note:</span> Drag these headings into the empty boxes embedded within the passage on the left side.</p>
                            </div>

                            <div v-else>
                                <div v-if="item.is_multiple_choice && item.count > 1" class="ielts-q-number" style="font-weight: 700 !important; font-size: 14px !important; color: #000000 !important; line-height: 1.5 !important; margin-bottom: 10px !important; display: block !important; padding: 0 !important; background: none !important; border: none !important;">
                                    <span style="font-weight: 700 !important;">Questions {{ item.display_number }}-{{ item.display_number + item.count - 1 }}</span>
                                </div>
                                <div v-else-if="!item.has_blanks && item.display_number" class="ielts-q-number" style="font-weight: 700 !important; font-size: 14px !important; color: #000000 !important; line-height: 1.5 !important; margin-bottom: 10px !important; display: block !important; padding: 0 !important; background: none !important; border: none !important;">
                                    <span style="font-weight: 700 !important;">{{ item.display_number }}.</span>
                                </div>

                                <!-- dropdown_selection with letter-only options → IELTS matching matrix grid -->
                                <div v-if="item.question.question_type === 'dropdown_selection' && buildDropdownMatrix(item).isMatrix" class="ielts-dropdown-matrix" style="margin-top: 8px;">
                                    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #6b7280 !important; font-size: 14px;">
                                        <thead>
                                            <tr style="background: #f3f4f6;">
                                                <th style="border: 1.5px solid #6b7280 !important; padding: 10px; text-align: left; width: 60%;"></th>
                                                <th v-for="col in buildDropdownMatrix(item).columns" :key="col"
                                                    style="border: 1.5px solid #6b7280 !important; padding: 10px 6px; text-align: center; font-weight: 700; color: #111827;">
                                                    {{ col }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="row in buildDropdownMatrix(item).rows" :key="row.dropdownNum">
                                                <td style="border: 1.5px solid #6b7280 !important; padding: 12px 10px; vertical-align: top;">
                                                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                                                        <span style="font-weight: 700; color: #111827; min-width: 22px;">{{ row.displayNum }}</span>
                                                        <span style="color: #1f2937; line-height: 1.5;">{{ row.statement }}</span>
                                                    </div>
                                                </td>
                                                <td v-for="col in row.columns" :key="col"
                                                    style="border: 1.5px solid #6b7280 !important; text-align: center; padding: 10px;">
                                                    <label style="display: inline-flex; align-items: center; justify-content: center; cursor: pointer; padding: 4px;">
                                                        <input type="radio"
                                                               :name="'mtx_' + item.question.id + '_' + row.dropdownNum"
                                                               :value="col"
                                                               :checked="answers[item.question.id] && answers[item.question.id]['dropdown_' + row.dropdownNum] === col"
                                                               @change="onDropdownMatrixChange(item.question.id, row.dropdownNum, col)"
                                                               style="width: 18px; height: 18px; cursor: pointer; accent-color: #C8102E;" />
                                                    </label>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="prose prose-slate max-w-none prose-p:my-0 text-gray-800 text-base leading-relaxed" style="margin-bottom: 8px; font-size: 14px; line-height: 1.6; color: #111827;" v-html="item.processedHtml"></div>

                                <div class="ielts-options" style="margin-left: 24px; margin-top: 8px;">
                                    <template v-if="['single_choice', 'true_false', 'yes_no'].includes(item.question.question_type)">
                                        <div v-for="opt in item.question.options" :key="opt.id" 
                                             class="ielts-option" style="margin-bottom: 6px !important; display: flex !important; align-items: center !important;">
                                            <input type="radio" :name="'q_' + item.question.id" :value="opt.id" :id="'option-' + item.question.id + '-' + opt.id"
                                                v-model="answers[item.question.id]"
                                                class="ielts-radio" style="-webkit-appearance: radio !important; appearance: radio !important; margin: 0 8px 0 0 !important; width: 14px !important; height: 14px !important; cursor: pointer !important; padding: 0 !important; background: none !important;" />
                                            <label :for="'option-' + item.question.id + '-' + opt.id" 
                                                   style="cursor: pointer !important; font-size: 14px !important; color: #000000 !important; font-weight: normal !important; line-height: 1.4 !important; margin: 0 !important; padding: 0 !important;"
                                                   v-html="opt.content"></label>
                                        </div>
                                    </template>
                                    
                                    <template v-else-if="item.question.question_type === 'multiple_choice'">
                                        <div v-for="opt in item.question.options" :key="opt.id" 
                                             class="ielts-option" 
                                             :style="{
                                                marginBottom: '6px !important', 
                                                display: 'flex !important', 
                                                alignItems: 'center !important',
                                                opacity: (answers[item.question.id] && answers[item.question.id].length >= (item.count || 1) && !answers[item.question.id].includes(opt.id)) ? '0.4' : '1',
                                                cursor: (answers[item.question.id] && answers[item.question.id].length >= (item.count || 1) && !answers[item.question.id].includes(opt.id)) ? 'not-allowed' : 'auto'
                                             }">
                                            <input type="checkbox" :value="opt.id" :id="'option-' + item.question.id + '-' + opt.id"
                                                :checked="answers[item.question.id] && Array.isArray(answers[item.question.id]) && answers[item.question.id].includes(opt.id)"
                                                :disabled="answers[item.question.id] && answers[item.question.id].length >= (item.count || 1) && !answers[item.question.id].includes(opt.id)"
                                                @change="(e) => handleMultipleChoiceChange(e, item, opt.id)"
                                                class="ielts-radio" style="-webkit-appearance: checkbox !important; appearance: checkbox !important; margin: 0 8px 0 0 !important; width: 14px !important; height: 14px !important; padding: 0 !important; background: none !important;"
                                                :style="{ cursor: (answers[item.question.id] && answers[item.question.id].length >= (item.count || 1) && !answers[item.question.id].includes(opt.id)) ? 'not-allowed !important' : 'pointer !important' }" />
                                            <label :for="'option-' + item.question.id + '-' + opt.id" 
                                                   style="font-size: 14px !important; color: #000000 !important; font-weight: normal !important; line-height: 1.4 !important; margin: 0 !important; padding: 0 !important;"
                                                   :style="{ cursor: (answers[item.question.id] && answers[item.question.id].length >= (item.count || 1) && !answers[item.question.id].includes(opt.id)) ? 'not-allowed !important' : 'pointer !important' }"
                                                   v-html="opt.content"></label>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <!-- Media if attached -->
                            <img v-if="item.question.media_path" :src="'/storage/' + item.question.media_path" class="max-w-full rounded mt-6 border border-gray-200 shadow-sm" />
                        </div>
                        
                    </div>
                </div>

            </div>

        </main>

        <div class="bottom-nav">
            <div class="nav-left">
                <div class="review-section">
                    <!-- Flag functionality will be added here later if needed -->
                    <span class="review-label">Review</span>
                </div>
                
                <div class="nav-section-container">
                    <span class="section-label hidden md:block">Reading</span>

                    <div class="parts-nav">
                        <button v-for="part in totalParts" :key="part"
                                @click="activePart = part"
                                class="part-btn"
                                :class="{ 'active': activePart === part }">
                            Part {{ part }}
                        </button>
                    </div>

                    <div class="nav-numbers overflow-x-auto flex-nowrap pr-4">
                        <template v-for="(item, index) in displayQuestions" :key="item.question.id + '-' + index">
                            
                            <template v-if="item.has_blanks">
                                <button v-for="(number, blankIndex) in item.blank_numbers" :key="'blank-'+number"
                                    @click="scrollToQuestion(item.question.part_number, item.question.id)"
                                    class="number-btn shrink-0"
                                    :class="{ 
                                        'answered': isAnswered(item, 'blank', blankIndex, number),
                                        'hidden-part': activePart !== item.question.part_number
                                    }">
                                    {{ number }}
                                </button>
                            </template>

                            <template v-else-if="item.is_master || (item.is_multiple_choice && item.count > 1) || item.is_sentence_completion">
                                <button v-for="(number, subIndex) in item.question_numbers" :key="'group-'+number"
                                    @click="scrollToQuestion(item.question.part_number, item.question.id)"
                                    class="number-btn shrink-0"
                                    :class="{ 
                                        'answered': isAnswered(item, 'group', subIndex, number),
                                        'hidden-part': activePart !== item.question.part_number
                                    }">
                                    {{ number }}
                                </button>
                            </template>

                            <template v-else>
                                <button @click="scrollToQuestion(item.question.part_number, item.question.id)"
                                    class="number-btn shrink-0"
                                    :class="{ 
                                        'answered': isAnswered(item, 'single', null, item.display_number),
                                        'hidden-part': activePart !== item.question.part_number
                                    }">
                                    {{ item.display_number }}
                                </button>
                            </template>

                        </template>
                    </div>
                </div>
            </div>

            <div class="exam-nav-right">
                
                <button @click="annotatorRef?.toggleNotesPanel()" class="flex items-center gap-1.5 px-3 py-1.5 rounded hover:bg-slate-100 text-slate-700 font-medium text-sm border border-transparent hover:border-slate-200 transition-colors">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Notepad <span v-if="annotatorRef?.notesCount > 0" class="bg-blue-100 text-blue-700 text-[10px] px-1.5 py-0.5 rounded-full ml-1">{{ annotatorRef.notesCount }}</span>
                </button>

                <button onclick="document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()" class="flex items-center gap-1.5 px-3 py-1.5 rounded hover:bg-slate-100 text-slate-700 font-medium text-sm border border-transparent hover:border-slate-200 transition-colors">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                    Full Screen
                </button>
                
                <span v-if="saveStatus === 'saving'" class="text-blue-600 ml-2 mr-4 flex items-center gap-2 text-sm italic">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Saving...
                </span>
                <button @click="submitTest" :disabled="isSubmitting" class="submit-test-button">
                    {{ isSubmitting ? 'Submitting...' : 'Submit Test' }}
                </button>
            </div>
        </div>

    </div>


    <!-- Global Annotations & Notepad Components -->
    <TextAnnotator ref="annotatorRef" :attempt-id="attempt.id" />
</template>

<style scoped></style>

<style>
/* Reset all question styles */
.exam-nav-right {
    display: flex;
    align-items: center;
    gap: 8px;
}
.gutter {
    background-color: #e5e7eb;
    background-repeat: no-repeat;
    background-position: 50%;
    position: relative;
    z-index: 10;
    transition: background-color 0.2s ease;
}

.gutter.gutter-horizontal {
    background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAeCAYAAADkftS9AAAAIklEQVQoU2M4c+bMfxAGAgYYmwGrIIIDAHOcgO7nBwLzEAIAlk4Zf/YhHPEAAAAASUVORK5CYII=');
    cursor: col-resize;
    display: flex;
    align-items: center;
    justify-content: center;
    border-left: 1px solid #d1d5db;
    border-right: 1px solid #d1d5db;
}

.gutter.gutter-horizontal:hover {
    background-color: #d1d5db;
}

/* Dragula specific styling */
.mh-heading-item {
    touch-action: none;
    user-select: none;
    -webkit-user-select: none;
}
.mh-heading-item.gu-mirror { 
    opacity: 0.9 !important; 
    cursor: grabbing !important;
    transform: rotate(3deg) !important;
    box-shadow: 0 15px 40px rgba(0,0,0,0.4) !important;
    pointer-events: none !important;
    z-index: 9999 !important;
}

.gu-transit { 
    opacity: 0.3 !important;
}

.passage-drop-zone.gu-over { 
    border-color: #666666 !important; 
    background-color: #f9fafb !important;
    border-style: solid !important;
}

.passage-drop-zone .mh-heading-item {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    padding: 0 !important;
}

.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
.passage-content p { margin-bottom: 1.25rem; line-height: 1.8; }

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

.number-btn.hidden-part {
    display: none;
}

.nav-numbers {
    display: flex;
    gap: 4px;
    flex-wrap: nowrap;
}

.parts-nav {
    display: flex;
    gap: 4px;
    margin-right: 8px;
}

.part-btn {
    padding: 4px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    background: white;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.part-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.part-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 50;
}

.nav-section-container {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    overflow: hidden;
}

.section-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
}

.review-section {
    display: flex;
    align-items: center;
}

.review-label {
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    white-space: nowrap;
}
</style>
