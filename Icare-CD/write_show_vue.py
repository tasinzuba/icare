import sys

vue_content = """<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import ExamTimer from '../../../Components/Exam/ExamTimer.vue';
import AntiCheat from '../../../Components/Exam/AntiCheat.vue';

import './style.css'; // Extracted from blade

const props = defineProps({
    testSet: Object,
    attempt: Object,
    questions: Array,
    serverTime: String,
    audioUrl: String,
    timeLimitSeconds: Number,
});

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name || '');
const userIdStr = computed(() => {
    const id = page.props.auth?.user?.id || 0;
    return `BI ${id.toString().padStart(6, '0')}`;
});

const audioVolume = ref(75);

// State
const isTestStarted = ref(false); // Controlled by "Play" overlay
const isSubmitting = ref(false);
const showSubmitModal = ref(false);

const answers = ref({});
const flaggedQuestions = ref(new Set());
const currentPart = ref(1);

// Auto-save logic
let autoSaveInterval = null;
const saveStatus = ref('');

const audioPlayer = ref(null);
const isAudioPlaying = ref(false);

// Format draft answers
if (props.attempt.draft_answers) {
    let parsedDraft = typeof props.attempt.draft_answers === 'string' 
        ? JSON.parse(props.attempt.draft_answers) 
        : props.attempt.draft_answers;
        
    for (const key in parsedDraft) {
        answers.value[key] = parsedDraft[key];
    }
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
        <div class="question-item single-choice-question" id="question-${q.id}">
            <div class="question-content" style="text-align: left;">
                <div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Question ${displayNumber}</div>
                <div class="question-text" style="text-align: left;">${q.content}</div>
            </div>`;
        
        if (q.options && q.options.length > 0) {
            html += `<div class="ielts-options options-list" style="margin-left: 24px; margin-top: 8px;">`;
            q.options.forEach(opt => {
                html += `
                <div class="ielts-option option-item" style="margin-bottom: 6px !important; display: flex !important; align-items: center !important; flex-direction: row; cursor: pointer;">
                    <input type="radio" 
                           name="answers[${q.id}]" 
                           id="option-${q.id}-${opt.id}" 
                           value="${opt.id}"
                           data-question-id="${q.id}"
                           class="single-choice-radio option-radio"
                           style="-webkit-appearance: radio !important; -moz-appearance: radio !important; appearance: radio !important; margin: 0; margin-right: 8px !important; width: 14px !important; height: 14px !important; cursor: pointer !important; padding: 0 !important;">
                    <label for="option-${q.id}-${opt.id}" class="option-label" style="cursor: pointer !important; font-size: 14px !important; color: #000000 !important; font-weight: normal !important; margin: 0 !important; padding: 0 !important; line-height: 1.4 !important; flex: 1;">
                        ${opt.text}
                    </label>
                </div>`;
            });
            html += `</div>`;
        } else {
            html += `
            <div class="answer-input" style="margin-left: 47px;">
                <input type="text" 
                       name="answers[${q.id}]" 
                       class="text-input-field text-input"
                       data-question-id="${q.id}"
                       style="width: 350px; padding: 10px 14px; border: 1px solid #ccc; border-radius: 0; font-size: 14px;" 
                       placeholder="Enter your answer" 
                       autocomplete="off" spellcheck="false">
            </div>`;
        }
        html += `</div>`;
    } 
    else if (q.question_type === 'multiple_choice') {
        const correctCount = q.options?.filter(opt => opt.is_correct).length || 1;
        const hasMultipleCorrect = correctCount > 1;
        
        html += `
        <div class="question-item multiple-choice-question" id="question-${q.id}">
            <div class="question-content" style="text-align: left;">`;
            
        if (hasMultipleCorrect) {
            html += `<div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Questions ${displayNumber}-${displayNumber + correctCount - 1}</div>`;
        } else {
            html += `<div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Question ${displayNumber}</div>`;
        }
        
        html += `<div class="question-text" style="text-align: left;">${q.content}</div>
            </div>`;
            
        if (q.options && q.options.length > 0) {
            html += `
            <div class="ielts-options options-list" style="margin-left: 24px; margin-top: 8px;" data-correct-count="${correctCount}">`;
            q.options.forEach(opt => {
                html += `
                <div class="ielts-option option-item" style="margin-bottom: 6px !important; display: flex !important; align-items: center !important; flex-direction: row; cursor: pointer;">
                    <input type="checkbox" 
                           name="answers[${q.id}][]" 
                           id="option-${q.id}-${opt.id}" 
                           value="${opt.id}"
                           data-question-id="${q.id}"
                           class="multiple-choice-checkbox option-checkbox"
                           style="-webkit-appearance: checkbox !important; -moz-appearance: checkbox !important; appearance: checkbox !important; margin: 0; margin-right: 8px !important; width: 14px !important; height: 14px !important; cursor: pointer !important; padding: 0 !important;">
                    <label for="option-${q.id}-${opt.id}" class="option-label" style="cursor: pointer !important; font-size: 14px !important; color: #000000 !important; font-weight: normal !important; margin: 0 !important; padding: 0 !important; line-height: 1.4 !important; flex: 1;">
                        ${opt.text}
                    </label>
                </div>`;
            });
            html += `</div>`;
        } else {
             html += `
             <div class="answer-input" style="margin-left: 47px;">
                <input type="text" 
                       name="answers[${q.id}]" 
                       class="text-input-field text-input"
                       data-question-id="${q.id}"
                       style="width: 350px; padding: 10px 14px; border: 1px solid #ccc; border-radius: 0; font-size: 14px;" 
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
        if(Array.isArray(dropZones)) {
             dropZones.forEach((zone, zoneIndex) => {
                const zoneNumber = startNum + zoneIndex;
                const pattern = new RegExp(`\\[DRAG_${zoneIndex + 1}\\]`, 'g');
                
                const dropBoxHtml = `
                    <span class="drop-box"
                          data-question-id="${q.id}"
                          data-zone-index="zone_${zoneIndex}"
                          style="display: inline-flex !important; min-width: 120px !important; width: auto !important; height: 40px !important; border: 1px dashed #000000 !important; border-radius: 4px !important; line-height: 38px !important; align-items: center !important; justify-content: center !important; background: white !important; font-size: 14px !important; padding: 0 20px !important; cursor: pointer !important; margin: 0 4px !important; vertical-align: middle !important; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; color: #1f2937 !important; text-align: center !important;">
                        <span class="placeholder-text" style="color: #000000 !important; font-weight: 600 !important; font-size: 14px !important;">${zoneNumber}</span>
                    </span>`;
                processedContent = processedContent.replace(pattern, dropBoxHtml);
            });
        }

        html += `
        <div class="question-item drag-drop-question" id="question-${q.id}" style="background: none; border: none; box-shadow: none; padding: 0; margin-bottom: 20px;">
            <div class="question-header" style="margin-bottom: 15px; text-align: left;">`;
                
        if (dropZoneCount > 1) {
            html += `<div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Questions ${startNum}-${endNum}</div>`;
        } else {
            html += `<div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Question ${displayNumber}</div>`;
        }
        
        html += `
            </div>
            
            <div class="drag-drop-layout" style="display: flex !important; gap: 40px !important; align-items: flex-start !important;">
                <div class="question-text draggable-options-container" style="flex: 1 !important; font-size: 15px; line-height: 2.4; color: #1f2937;">${processedContent}</div>
                <div class="matching-right-section" style="width: 150px; flex-shrink: 0; margin-left: auto;">
                    <div class="matching-options-grid draggable-options-grid" style="display: flex !important; flex-direction: column !important; flex-wrap: wrap !important; gap: 12px !important; padding: 0 !important; background: none !important; border: none !important;">`;
                    
        if(Array.isArray(options)){
             options.forEach((optText, optIdx) => {
                 html += `
                 <div class="draggable-option fake-drag-option"
                      data-question-id="${q.id}"
                      data-option-value="${optText}"
                      style="min-width: 120px !important; padding: 10px 20px !important; background: white !important; border: 1px solid #d1d5db !important; border-radius: 4px !important; cursor: pointer !important; font-size: 14px !important; font-weight: 400 !important; color: #1f2937 !important; text-align: center !important; user-select: none !important;">
                     ${optText}
                 </div>`;
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

        // Process DROPDOWN_X
        if (q.section_specific_data?.dropdown_options) {
            dropdownContent = dropdownContent.replace(/\[DROPDOWN_(\d+)\]/g, (match, dropdownNum) => {
                const currentNum = dropdownCounter++;
                const optionsStr = q.section_specific_data.dropdown_options[dropdownNum];
                const options = optionsStr ? optionsStr.split(',').map(s => s.trim()) : [];
                
                let selectHtml = `
                <span class="question-number-inline" style="font-weight: 700; background: #e8e8e8; padding: 4px 10px; border-radius: 3px; text-align: center; display: inline-flex; align-items: center; justify-content: center; height: 32px; margin-right: 8px;">${currentNum}</span>
                <span class="dropdown-wrapper" style="display: inline-block; position: relative; vertical-align: middle;">
                    <select class="dropdown-input inline-dropdown dropdown" 
                            data-question-id="${q.id}" 
                            data-dropdown-key="dropdown_${dropdownNum}"
                            style="width: 150px; padding: 8px 30px 8px 10px; border: 1px solid #ccc; border-radius: 0; background-color: #f5f5f5; color: #333; font-size: 14px; font-family: Arial, sans-serif; cursor: pointer; outline: none; height: 38px; vertical-align: middle; -webkit-appearance: menulist;">
                        <option value="">Select Option</option>`;
                        
                options.forEach(opt => {
                    selectHtml += `<option value="${opt}">${opt}</option>`;
                });
                
                selectHtml += `
                    </select>
                </span>`;
                return selectHtml;
            });
        }
        
        // Process ____X____ (blanks)
        dropdownContent = dropdownContent.replace(/\[____(\d+)____\]|\[BLANK_(\d+)\]/g, (match, m1, m2) => {
            const blankNum = m1 || m2;
            const currentNum = blankCounter++;
            return `<span class="question-number-inline" style="font-weight: 700; background: #e8e8e8; padding: 4px 10px; border-radius: 3px; text-align: center; display: inline-flex; align-items: center; justify-content: center; height: 32px; margin-right: 8px;">${currentNum}</span>
                    <input type="text" class="inline-blank text-input-field text-input" 
                           data-question-id="${q.id}" 
                           data-blank-key="blank_${blankNum}"
                           style="width: 150px; display: inline-block; margin: 4px 6px; padding: 10px 14px; height: 38px; border: 1px solid #ccc; border-radius: 0; background-color: #f5f5f5; font-size: 14px; color: #333; outline: none; vertical-align: middle;" 
                           autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">`;
        });
        
        html += `
        <div class="question-item" id="question-${q.id}">
            <div class="question-text" style="font-size: 15px; line-height: 1.6; color: #1f2937;">
                ${dropdownContent}
            </div>
        </div>`;
    }
    else {
        html += `
        <div class="question-item form-field-row" id="question-${q.id}" style="display: grid; grid-template-columns: 36px 1fr; align-items: start; margin-bottom: 25px; gap: 20px;">
            <div class="form-question-number" style="font-weight: 600; color: #333; font-size: 14px; background: #f0f0f0; border: 1px solid #999; padding: 6px 0; text-align: center; border-radius: 4px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">${displayNumber}</div>
            <div style="flex: 1;">
                <div class="question-text" style="text-align: left; margin-bottom: 8px;">${q.content}</div>
                <div class="answer-input" style="margin-left: 0;">
                    <input type="text" 
                           name="answers[${q.id}]" 
                           class="text-input-field text-input form-input"
                           data-question-id="${q.id}"
                           style="width: 100%; max-width: 300px; padding: 10px 14px; border: 1px solid #999; border-radius: 0; font-size: 14px; background: white;" 
                           placeholder="Enter your answer" 
                           autocomplete="off" spellcheck="false">
                </div>
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
                    const correctCount = q.options?.filter(o => o.is_correct).length || 1;
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

// Use event delegation for performance and simplicity across v-html updates
const handleContainerEvents = (e) => {
    const target = e.target;
    
    if (target.matches('.single-choice-radio')) {
        const qid = target.getAttribute('data-question-id');
        answers.value[qid] = target.value;
    }
    else if (target.matches('.multiple-choice-checkbox')) {
        const qid = target.getAttribute('data-question-id');
        const ieltsOpts = target.closest('.ielts-options');
        const maxSelect = parseInt(ieltsOpts.getAttribute('data-correct-count')) || 1;
        
        if (!answers.value[qid]) answers.value[qid] = [];
        if (!Array.isArray(answers.value[qid])) answers.value[qid] = [answers.value[qid]];
        
        const isChecked = target.checked;
        if (isChecked) {
            if (answers.value[qid].length >= maxSelect) {
                e.preventDefault();
                target.checked = false;
                return;
            }
            if(!answers.value[qid].includes(target.value)) answers.value[qid].push(target.value);
        } else {
            answers.value[qid] = answers.value[qid].filter(id => id !== target.value);
        }
    }
    else if (target.matches('.text-input-field')) {
        const qid = target.getAttribute('data-question-id');
        const blankKey = target.getAttribute('data-blank-key');
        
        if (blankKey) {
            if (!answers.value[qid]) answers.value[qid] = {};
            answers.value[qid][blankKey] = target.value;
        } else {
            answers.value[qid] = target.value;
        }
    }
    else if (target.matches('.inline-dropdown')) {
        const qid = target.getAttribute('data-question-id');
        const dropdownKey = target.getAttribute('data-dropdown-key');
        
        if (!answers.value[qid]) answers.value[qid] = {};
        answers.value[qid][dropdownKey] = target.value;
    }
    else if (target.matches('.fake-drag-option')) {
        e.preventDefault();
        const qid = target.getAttribute('data-question-id');
        const val = target.getAttribute('data-option-value');
        
        const emptyBox = document.querySelector(`.drop-box[data-question-id="${qid}"]:not(.has-answer):not(.filled)`);
        if (emptyBox) {
            emptyBox.classList.add('has-answer');
            emptyBox.classList.add('filled');
            emptyBox.innerHTML = val;
            emptyBox.setAttribute('data-filled-val', val);
            
            const zoneKey = emptyBox.getAttribute('data-zone-index');
            if (!answers.value[qid]) answers.value[qid] = {};
            answers.value[qid][zoneKey] = val;
            target.classList.add('placed');
        }
    }
    else if (target.matches('.drop-box.has-answer') || target.matches('.drop-box.filled')) {
        const qid = target.getAttribute('data-question-id');
        const val = target.getAttribute('data-filled-val');
        const zoneKey = target.getAttribute('data-zone-index');
        
        if (answers.value[qid] && answers.value[qid][zoneKey]) {
            delete answers.value[qid][zoneKey];
        }
        
        target.classList.remove('has-answer');
        target.classList.remove('filled');
        const placeholderNum = target.getAttribute('data-original-num') || zoneKey.replace('zone_', '');
        target.innerHTML = `<span class="placeholder-text" style="color: #000000 !important; font-weight: 600 !important; font-size: 14px !important;">${parseInt(placeholderNum) + 1}</span>`;
        target.removeAttribute('data-filled-val');
        
        // Restore option
        const opt = document.querySelector(`.fake-drag-option[data-question-id="${qid}"][data-option-value="${val}"]`);
        if (opt) {
            opt.classList.remove('placed');
        }
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
                    const sel = container.querySelector(`select[data-question-id="${qid}"][data-dropdown-key="${key}"]`);
                    if(sel) {
                        sel.value = ans[key];
                    }
                } else if(key.startsWith('blank_')) {
                    const inp = container.querySelector(`input[data-question-id="${qid}"][data-blank-key="${key}"]`);
                    if(inp) inp.value = ans[key];
                } else if(key.startsWith('zone_')) {
                    const box = container.querySelector(`.drop-box[data-question-id="${qid}"][data-zone-index="${key}"]`);
                    if(box && ans[key]) {
                        if (!box.getAttribute('data-original-num')) {
                            box.setAttribute('data-original-num', box.innerText.trim());
                        }
                        box.classList.add('has-answer');
                        box.classList.add('filled');
                        box.innerHTML = ans[key];
                        box.setAttribute('data-filled-val', ans[key]);
                        
                        // faint the option
                        const opt = container.querySelector(`.fake-drag-option[data-question-id="${qid}"][data-option-value="${ans[key]}"]`);
                        if(opt) {
                             opt.classList.add('placed');
                        }
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
    });
});

const isAnswered = (questionId, itemsIndex = 0) => {
     const ans = answers.value[questionId];
     if (typeof ans === 'object' && ans !== null && !Array.isArray(ans)) {
         const keys = Object.keys(ans);
         const key = keys.find(k => k.includes(`_${itemsIndex + 1}`) || k.includes(`zone_${itemsIndex}`));
         if (key) return !!ans[key];
         const values = Object.values(ans).filter(v => !!v);
         return values.length > itemsIndex;
     } else if (Array.isArray(ans)) {
         return ans.length > itemsIndex;
     }
     return !!ans;
};

const answeredCount = computed(() => {
    return navNumberButtons.value.filter(n => isAnswered(n.questionId, n.index)).length;
});

// Audio & Test Start
const startAudioAndTest = () => {
    if (!audioPlayer.value) return;
    
    audioPlayer.value.volume = audioVolume.value / 100;
    
    audioPlayer.value.play().then(() => {
        isAudioPlaying.value = true;
        isTestStarted.value = true;
        
        startAutoSave();
        
        nextTick(() => {
            syncDOMWithAnswers();
        });
    }).catch(e => {
        console.error("Audio playback failed:", e);
        alert("Audio playback failed. Please ensure your browser allows autoplay and try again.");
    });
};

watch(audioVolume, (newVol) => {
    if (audioPlayer.value) {
        audioPlayer.value.volume = newVol / 100;
    }
});

const handleAudioEnd = () => {
    isAudioPlaying.value = false;
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

const prevQuestion = () => {
    // Basic navigation logic not easily generalizable here without scrolling code
};
const nextQuestion = () => {
    // Basic navigation
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

const saveDraft = async () => {
    try {
        await axios.post(`/student/test/listening/auto-save/${props.attempt.id}`, {
            answers: answers.value
        });
    } catch (error) {
        console.error('Auto-save failed:', error);
    }
};

const startAutoSave = () => {
    autoSaveInterval = setInterval(() => {
        saveDraft();
    }, 30000); 
};

const finalSubmit = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    showSubmitModal.value = false;
    
    clearInterval(autoSaveInterval);
    if(audioPlayer.value) audioPlayer.value.pause();
    
    router.post(`/student/test/listening/submit/${props.attempt.id}`, {
        answers: answers.value
    }, {
        onFinish: () => { isSubmitting.value = false; }
    });
};

onUnmounted(() => {
    if (autoSaveInterval) clearInterval(autoSaveInterval);
    if (audioPlayer.value) {
        audioPlayer.value.pause();
        audioPlayer.value = null;
    }
});
</script>

<template>
    <AntiCheat />

    <!-- Fixed User Info Bar strictly mirroring blade -->
    <div class="user-bar" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000; height: 50px;">
        <div class="user-info">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ userName }} - {{ userIdStr }}</span>
        </div>
        
        <div class="timer-center-wrapper">
            <ExamTimer v-if="isTestStarted"
                :timeLimitSeconds="timeLimitSeconds"
                :serverTime="serverTime"
                :attemptStartTime="attempt.start_time"
                @timeUp="finalSubmit"
            />
        </div>
        
        <div class="user-controls">
            <button class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm help-button" id="help-button">Help ?</button>
            <button class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm no-nav">Hide</button>
            <div class="flex items-center ml-2">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071a1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                <input type="range" min="0" max="100" v-model="audioVolume" class="ml-2 w-20" id="volume-slider">
            </div>
        </div>
    </div>

    <!-- Main Container matching Blade exactly -->
    <div class="main-container">
        <!-- Floating audio player UI for overlay -->
        <div v-if="!isTestStarted" id="audio-start-overlay" style="position: fixed; inset: 0; background-color: rgba(255, 255, 255, 0.95); z-index: 2000; display: flex; align-items: center; justify-content: center;">
            <div class="audio-overlay-content" style="text-align: center; max-width: 600px; padding: 40px;">
                <div class="audio-overlay-icon" style="background: #111827; border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.2" style="width: 40px; height: 40px;">
                        <path d="M12 3C7.03 3 3 7.03 3 12v6c0 1.1.9 2 2 2h1c1.1 0 2-.9 2-2v-3c0-1.1-.9-2-2-2H5v-1c0-3.87 3.13-7 7-7s7 3.13 7 7v1h-1c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h1c1.1 0 2-.9 2-2v-6c0-4.97-4.03-9-9-9z"/>
                    </svg>
                </div>
                <div class="audio-overlay-text" style="color: #374151; font-size: 16px; margin-bottom: 32px;">
                    <p style="margin-bottom: 12px; font-weight: 500;">You will be listening to an audio clip during this test. You will not be permitted to pause or rewind the audio while answering the questions.</p>
                    <p>To continue, click Play.</p>
                </div>
                <button @click="startAudioAndTest" id="start-audio-btn" style="background: white; border: 1px solid #d1d5db; color: #111827; padding: 10px 24px; border-radius: 6px; font-size: 15px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    Play
                </button>
            </div>
        </div>

        <audio v-if="audioUrl" ref="audioPlayer" :src="audioUrl" preload="auto" @ended="handleAudioEnd" class="hidden" style="display:none;"></audio>

        <div class="part-header-container" id="fixed-part-header" v-if="isTestStarted">
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
                        <div v-if="g.name && g.name !== 'null'" class="question-group-header">{{ g.name }}</div>
                        
                        <template v-for="(q, idx) in g.questions" :key="q.id">
                            <div v-if="q.instructions && (!g.questions[idx - 1] || g.questions[idx - 1].instructions !== q.instructions)" 
                                 class="question-instruction" v-html="q.instructions"></div>
                            
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
                <input type="checkbox" id="review-checkbox" class="review-check">
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
                                  answered: isAnswered(num.questionId, num.index),
                                  active: currentPart === part.part_number
                              }]"
                              @click="toggleFlag(num.displayNumber)">
                              {{ num.displayNumber }}
                         </div>
                    </template>
                </div>
            </div>
        </div>
        
        <div class="nav-right">
             <button type="button" class="btn-secondary" id="notes-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Notes
            </button>
             <button type="button" class="btn-secondary" @click="toggleFullscreen">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                Fullscreen
            </button>
            <button type="button" class="submit-test-button" @click="showSubmitModal = true">
                Submit Test
            </button>
        </div>
    </div>

    <!-- Modals -->
    <div v-if="showSubmitModal" class="modal-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 3000; display: flex; align-items: center; justify-content: center;">
        <div class="modal-content" style="background: white; padding: 30px; border-radius: 8px; max-width: 400px; width: 100%; text-align: center;">
            <div class="modal-title" style="font-size: 20px; font-weight: bold; margin-bottom: 20px;">Submit Test?</div>
            <div class="modal-message" style="margin-bottom: 30px; color: #4b5563;">
                 You have answered {{ answeredCount }} out of {{ navNumberButtons.length }} questions.
            </div>
            <div class="modal-actions" style="display: flex; gap: 10px; justify-content: center;">
                <button @click="showSubmitModal = false" style="padding: 10px 20px; background: #e5e7eb; border-radius: 4px; border: none; font-weight: 500; cursor: pointer;">Cancel</button>
                <button @click="finalSubmit" :disabled="isSubmitting" style="padding: 10px 20px; background: #ef4444; color: white; border-radius: 4px; border: none; font-weight: 500; cursor: pointer;">
                    {{ isSubmitting ? 'Sumitting...' : 'Yes, Submit' }}
                </button>
            </div>
        </div>
    </div>
</template>
"""

with open('resources/js/Pages/Test/Listening/Show.vue', 'w') as f:
    f.write(vue_content)
