<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';

const props = defineProps({
    attemptId: {
        type: [String, Number],
        required: true
    }
});

const isNotesPanelOpen = ref(false);
const notes = ref([]);
const isFloatingEditorVisible = ref(false);
const currentNoteText = ref('');
const charCount = ref(0);
const generalNotepad = ref('');
let currentSelectionRange = null;

const NOTEPAD_STORAGE_KEY = computed(() => `ielts_notepad_${props.attemptId}`);
const ANNOTATIONS_STORAGE_KEY = computed(() => `annotations_${props.attemptId}`);

// Menu State
const isMenuVisible = ref(false);
const menuPosition = ref({ top: 0, left: 0 });
const currentSelectedText = ref('');

// Draggability State
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });
const editorPosition = ref({ top: 0, left: 0 });

// Colors
const HIGHLIGHT_COLOR = '#fde047';
const NOTE_HILIGHT_COLOR = '#fee2e2';
const NOTE_TEXT_COLOR = '#dc2626';

// The allowed array ensures we only highlight in passages and question texts, NOT inputs
const ALLOWED_SELECTORS = [
    '.passage-content', '.questions-section', '.question-text', '.question-instruction', 
    '.question-instructions', '.part-instruction', '.question-item', '.ielts-question-item', 
    '.question-content', '.matching-question', '.form-label', '.question-group-header', 
    '.radio-option', '.checkbox-option', '.option-text', '.option-label', '.option-item', 
    '.part-questions', '.part-questions-inner', '.question-block', '.question-wrapper', 
    '.ielts-options', '.ielts-q-number', '.sentence-completion', '.fill-blanks-text', 
    '.true-false-question', '.matching-headings', '.dropdown-selection-question', 
    '.single-choice-question', '.multiple-choice-question', 'p', 'span', 'li', 'td', 'th',
    '#passage-section', '#questions-section',
    '.questions-container', '.content-area', '.main-container'
];

const FORBIDDEN_SELECTORS = [
    'input[type="radio"]', 'input[type="checkbox"]', 'input[type="text"]', 'select', 
    'textarea', 'button', '.drop-box', '.draggable-option', '.number-btn', '.answer-input', 
    '.passage-answer-input', '.no-select'
];

// Load from local storage
const loadAnnotations = () => {
    try {
        const stored = localStorage.getItem(ANNOTATIONS_STORAGE_KEY.value);
        if (stored) {
            const parsed = JSON.parse(stored);
            notes.value = parsed.filter(a => a.type === 'note');
            
            setTimeout(() => {
                restoreVisualAnnotations(parsed);
            }, 500);
        }

        const savedNotepad = localStorage.getItem(NOTEPAD_STORAGE_KEY.value);
        if (savedNotepad) generalNotepad.value = savedNotepad;
    } catch (e) {
        console.error('Failed to load annotations:', e);
    }
};

const saveGeneralNotepad = () => {
    try {
        localStorage.setItem(NOTEPAD_STORAGE_KEY.value, generalNotepad.value);
    } catch (e) {
        console.error('Failed to save notepad:', e);
    }
};

watch(generalNotepad, saveGeneralNotepad);

const saveAnnotationsToStorage = () => {
    try {
        const allAnnotations = [...notes.value];
        
        document.querySelectorAll('span[data-is-highlight="true"]').forEach(span => {
            allAnnotations.push({
                type: 'highlight',
                text: span.textContent,
                data: 'Yellow',
                timestamp: span.dataset.timestamp || Date.now()
            });
        });

        localStorage.setItem(ANNOTATIONS_STORAGE_KEY.value, JSON.stringify(allAnnotations));
    } catch (e) {
        console.error('Failed to save annotations:', e);
    }
};

const findAndStyleText = (searchText, styleCallback) => {
    if (!searchText || searchText.length < 3) return;

    const walker = document.createTreeWalker(
        document.body,
        NodeFilter.SHOW_TEXT,
        {
            acceptNode: function(node) {
                // Skip if inside an input or already styled span
                if (node.parentElement.tagName === 'INPUT' || 
                    node.parentElement.tagName === 'TEXTAREA' ||
                    node.parentElement.tagName === 'SELECT' ||
                    node.parentElement.dataset.isHighlight ||
                    node.parentElement.dataset.noteId) {
                    return NodeFilter.FILTER_REJECT;
                }
                
                // Only look inside test container areas
                if (!node.parentElement.closest('.test-container') && 
                    !node.parentElement.closest('.passage-content') &&
                    !node.parentElement.closest('.questions-section') &&
                    !node.parentElement.closest('#passage-section') &&
                    !node.parentElement.closest('#questions-section') &&
                    !node.parentElement.closest('.questions-container') &&
                    !node.parentElement.closest('.content-area')) {
                    return NodeFilter.FILTER_REJECT;
                }
                
                return NodeFilter.FILTER_ACCEPT;
            }
        },
        false
    );

    let node;
    const nodesToReplace = [];

    while (node = walker.nextNode()) {
        const nodeText = node.nodeValue;
        const index = nodeText.indexOf(searchText);
        
        if (index >= 0) {
            nodesToReplace.push({ node, index, length: searchText.length });
        }
    }

    // Process replacements backwards to not mess up indices if multiple matches in one node
    for (let i = nodesToReplace.length - 1; i >= 0; i--) {
        const { node, index, length } = nodesToReplace[i];
        try {
            const span = document.createElement('span');
            const matchText = node.nodeValue.substring(index, index + length);
            
            const beforeNode = document.createTextNode(node.nodeValue.substring(0, index));
            const afterNode = document.createTextNode(node.nodeValue.substring(index + length));
            
            span.textContent = matchText;
            styleCallback(span);
            
            const parent = node.parentElement;
            parent.insertBefore(beforeNode, node);
            parent.insertBefore(span, node);
            parent.insertBefore(afterNode, node);
            parent.removeChild(node);
        } catch (e) {
            console.error('Error restoring annotation:', e);
        }
    }
};

const restoreVisualAnnotations = (annotations) => {
    annotations.forEach(annotation => {
        if (annotation.type === 'note') {
            findAndStyleText(annotation.text, (span) => {
                span.style.cssText = `background-color: transparent; border-bottom: 2px solid ${NOTE_TEXT_COLOR}; cursor: pointer; padding: 0;`;
                span.dataset.note = annotation.data;
                span.dataset.noteId = annotation.timestamp;
                span.onclick = () => showNoteTooltip(span, annotation.data);
            });
        } else if (annotation.type === 'highlight') {
            findAndStyleText(annotation.text, (span) => {
                span.style.backgroundColor = HIGHLIGHT_COLOR;
                span.style.cursor = 'pointer';
                span.style.borderRadius = '2px';
                span.dataset.isHighlight = 'true';
                span.dataset.timestamp = annotation.timestamp;
                span.title = 'Yellow highlight - Click to remove';
                span.onclick = function(evt) {
                    evt.stopPropagation();
                    const text = this.textContent;
                    this.style.transition = 'all 0.3s ease';
                    this.style.backgroundColor = 'transparent';
                    setTimeout(() => {
                        this.replaceWith(document.createTextNode(text));
                        saveAnnotationsToStorage();
                    }, 300);
                };
            });
        }
    });
};

const handleMouseUp = (e) => {
    // If clicking inside UI elements, don't trigger new selection logic
    if (e.target.closest('.annotation-menu-container') || 
        e.target.closest('.floating-note-editor') || 
        e.target.closest('.notepad-ui-header') ||
        e.target.closest('.notepad-paper-effect')) {
        return;
    }
    
    if (e.button === 2) return; // Right click

    // Give browser time to update selection
    setTimeout(() => {
        const selection = window.getSelection();
        if (!selection || selection.isCollapsed || selection.rangeCount === 0) {
            // Hide menu if clicking away (not on menu itself)
            if (!e.target.closest('.annotation-menu-container')) {
                hideMenu();
            }
            return;
        }

        const selectedText = selection.toString().trim();

        if (selectedText && selectedText.length >= 3) {
            try {
                const range = selection.getRangeAt(0);
                let container = range.commonAncestorContainer;
                if (container.nodeType === 3) container = container.parentElement;

                // Check for forbidden elements
                const isForbidden = FORBIDDEN_SELECTORS.some(selector => {
                    try { return container.matches(selector) || container.closest(selector) !== null; } 
                    catch (err) { return false; }
                });

                if (isForbidden) {
                    hideMenu();
                    return;
                }

                // Strict area check: only allow selection in passage or question areas
                const isAllowedArea = container.closest('#passage-section') || 
                                     container.closest('#questions-section') ||
                                     container.closest('.passage-content') ||
                                     container.closest('.questions-section') ||
                                     container.closest('.questions-container') ||
                                     container.closest('.content-area');

                if (!isAllowedArea) {
                    hideMenu();
                    return;
                }

                // Simplified area check: just ensure we are NOT in the notepad or menu
                const isInsideUI = container.closest('.annotation-menu-container') || 
                                 container.closest('.notepad-paper-effect') ||
                                 container.closest('.floating-note-editor');
                
                if (isInsideUI) {
                    hideMenu();
                    return;
                }

                const rects = range.getClientRects();
                if (rects.length === 0) return;
                
                // Use the last rect for positioning (usually bottom of selection)
                const rect = rects[rects.length - 1];
                
                currentSelectionRange = range;
                currentSelectedText.value = selectedText;
                
                showMenu(rect);
            } catch (err) {
                console.error('Selection handling error:', err);
                hideMenu();
            }
        } else {
            if (!e.target.closest('.annotation-menu-container') && !e.target.closest('.floating-note-editor')) {
                hideMenu();
            }
        }
    }, 50);
};

const showMenu = (rect) => {
    // Positioning logic: Place it above the selection
    menuPosition.value = {
        top: Math.max(10, rect.top - 50),
        left: Math.max(10, rect.left + (rect.width / 2) - 85)
    };
    
    // Also default editor position nearby
    editorPosition.value = {
        top: rect.bottom + 10,
        left: Math.max(10, rect.left + (rect.width / 2) - 160)
    };
    
    isMenuVisible.value = true;
};

const hideMenu = () => {
    isMenuVisible.value = false;
    isFloatingEditorVisible.value = false;
};

const handleCopy = () => {
    navigator.clipboard.writeText(currentSelectedText.value);
    hideMenu();
};

const applyHighlight = () => {
    if (!currentSelectionRange) return;
    try {
        const timestamp = Date.now();
        const startNode = currentSelectionRange.startContainer;
        const startOffset = currentSelectionRange.startOffset;
        const endNode = currentSelectionRange.endContainer;
        const endOffset = currentSelectionRange.endOffset;
        
        let ancestor = currentSelectionRange.commonAncestorContainer;
        if (ancestor.nodeType === 3) ancestor = ancestor.parentNode;

        const walker = document.createTreeWalker(ancestor, NodeFilter.SHOW_TEXT, null, false);
        const textNodes = [];
        let inRange = false;

        if (startNode === endNode) {
            textNodes.push(startNode);
        } else {
            let node;
            while (node = walker.nextNode()) {
                if (node === startNode) {
                    inRange = true;
                    textNodes.push(node);
                } else if (node === endNode) {
                    textNodes.push(node);
                    break;
                } else if (inRange) {
                    textNodes.push(node);
                }
            }
        }

        textNodes.forEach(textNode => {
            if (textNode.nodeType !== Node.TEXT_NODE || textNode.nodeValue.trim() === '') return;
            
            if (textNode.parentElement && (
                textNode.parentElement.tagName === 'INPUT' || 
                textNode.parentElement.tagName === 'TEXTAREA' ||
                textNode.parentElement.tagName === 'SELECT' ||
                textNode.parentElement.dataset.isHighlight ||
                textNode.parentElement.dataset.noteId ||
                textNode.parentElement.closest('.annotation-menu-container') ||
                textNode.parentElement.closest('.floating-note-editor')
            )) return;

            let start = 0;
            let end = textNode.nodeValue.length;

            if (textNode === startNode) start = startOffset;
            if (textNode === endNode) end = endOffset;
            
            // Fix: ensure start and end are within valid bounds
            start = Math.max(0, start);
            end = Math.min(textNode.nodeValue.length, end);
            
            if (start >= end) return;

            const span = document.createElement('span');
            span.style.backgroundColor = HIGHLIGHT_COLOR;
            span.style.cursor = 'pointer';
            span.style.borderRadius = '2px';
            span.dataset.isHighlight = 'true';
            span.dataset.timestamp = timestamp;
            span.title = 'Yellow highlight - Click to remove';
            span.onclick = function(evt) {
                evt.stopPropagation();
                document.querySelectorAll(`span[data-timestamp="${timestamp}"]`).forEach(el => {
                    const text = el.textContent;
                    el.style.transition = 'all 0.3s ease';
                    el.style.backgroundColor = 'transparent';
                    setTimeout(() => el.replaceWith(document.createTextNode(text)), 300);
                });
                setTimeout(() => saveAnnotationsToStorage(), 310);
            };

            const fullText = textNode.nodeValue;
            const beforeText = fullText.substring(0, start);
            // Handle double tap selection edge case explicitly
            const middleText = fullText.substring(start, end);
            const afterText = fullText.substring(end);

            span.textContent = middleText;
            const parent = textNode.parentElement;
            if (beforeText) parent.insertBefore(document.createTextNode(beforeText), textNode);
            parent.insertBefore(span, textNode);
            if (afterText) parent.insertBefore(document.createTextNode(afterText), textNode);
            parent.removeChild(textNode);
        });

        saveAnnotationsToStorage();
        window.getSelection().removeAllRanges();
        hideMenu();
    } catch (e) {
        console.error('Error applying highlight:', e);
        // Fallback for tricky single-node double clicks that fail the tree walker
        try {
            const span = document.createElement('span');
            span.style.backgroundColor = HIGHLIGHT_COLOR;
            span.style.cursor = 'pointer';
            span.style.borderRadius = '2px';
            span.dataset.isHighlight = 'true';
            span.dataset.timestamp = Date.now();
            
            span.onclick = function(evt) {
                evt.stopPropagation();
                const text = this.textContent;
                this.style.transition = 'all 0.3s ease';
                this.style.backgroundColor = 'transparent';
                setTimeout(() => {
                    this.replaceWith(document.createTextNode(text));
                    saveAnnotationsToStorage();
                }, 300);
            };

            currentSelectionRange.surroundContents(span);
            saveAnnotationsToStorage();
            window.getSelection().removeAllRanges();
            hideMenu();
        } catch (err2) {
             console.error('Fallback highlight failed:', err2);
             alert('Cannot highlight text. Please try selecting a different text portion.');
        }
    }
};

const openFloatingEditor = () => {
    isFloatingEditorVisible.value = true;
    isMenuVisible.value = false;
    currentNoteText.value = '';
    charCount.value = 0;
};

// Dragging Logic
const startDrag = (e) => {
    isDragging.value = true;
    dragOffset.value = {
        x: e.clientX - editorPosition.value.left,
        y: e.clientY - editorPosition.value.top
    };
};

const onDragging = (e) => {
    if (!isDragging.value) return;
    editorPosition.value = {
        top: e.clientY - dragOffset.value.y,
        left: e.clientX - dragOffset.value.x
    };
};

const stopDrag = () => {
    isDragging.value = false;
};

const closeFloatingEditor = () => {
    isFloatingEditorVisible.value = false;
};

const updateCharCount = (e) => {
    if (e.target.value.length > 500) {
        currentNoteText.value = e.target.value.substring(0, 500);
    }
    charCount.value = currentNoteText.value.length;
};

const saveNote = () => {
    const text = currentNoteText.value.trim();
    if (text && currentSelectionRange) {
        const selectedText = currentSelectionRange.toString();
        const timestamp = Date.now();

        try {
            const startNode = currentSelectionRange.startContainer;
            const startOffset = currentSelectionRange.startOffset;
            const endNode = currentSelectionRange.endContainer;
            const endOffset = currentSelectionRange.endOffset;
            
            let ancestor = currentSelectionRange.commonAncestorContainer;
            if (ancestor.nodeType === 3) ancestor = ancestor.parentNode;

            const walker = document.createTreeWalker(ancestor, NodeFilter.SHOW_TEXT, null, false);
            const textNodes = [];
            let inRange = false;

            if (startNode === endNode) {
                textNodes.push(startNode);
            } else {
                let node;
                while (node = walker.nextNode()) {
                    if (node === startNode) {
                        inRange = true;
                        textNodes.push(node);
                    } else if (node === endNode) {
                        textNodes.push(node);
                        break;
                    } else if (inRange) {
                        textNodes.push(node);
                    }
                }
            }

            textNodes.forEach(textNode => {
                if (textNode.nodeType !== Node.TEXT_NODE || textNode.nodeValue.trim() === '') return;
                
                if (textNode.parentElement && (
                    textNode.parentElement.tagName === 'INPUT' || 
                    textNode.parentElement.tagName === 'TEXTAREA' ||
                    textNode.parentElement.tagName === 'SELECT' ||
                    textNode.parentElement.dataset.isHighlight ||
                    textNode.parentElement.dataset.noteId ||
                    textNode.parentElement.closest('.annotation-menu-container') ||
                    textNode.parentElement.closest('.floating-note-editor')
                )) return;

                let start = 0;
                let end = textNode.nodeValue.length;

                if (textNode === startNode) start = startOffset;
                if (textNode === endNode) end = endOffset;
                
                start = Math.max(0, start);
                end = Math.min(textNode.nodeValue.length, end);

                if (start >= end) return;

                const span = document.createElement('span');
                span.style.cssText = `background-color: transparent; border-bottom: 2px solid ${NOTE_TEXT_COLOR}; cursor: pointer; padding: 0;`;
                span.dataset.note = text;
                span.dataset.noteId = timestamp;
                span.onclick = () => showNoteTooltip(span, text);

                const fullText = textNode.nodeValue;
                const beforeText = fullText.substring(0, start);
                const middleText = fullText.substring(start, end);
                const afterText = fullText.substring(end);

                span.textContent = middleText;
                const parent = textNode.parentElement;
                if (beforeText) parent.insertBefore(document.createTextNode(beforeText), textNode);
                parent.insertBefore(span, textNode);
                if (afterText) parent.insertBefore(document.createTextNode(afterText), textNode);
                parent.removeChild(textNode);
            });
        } catch (error) {
            console.error('Error styling note:', error);
            // Fallback for tricky single-node double clicks
            try {
                const span = document.createElement('span');
                span.style.cssText = `background-color: transparent; border-bottom: 2px solid ${NOTE_TEXT_COLOR}; cursor: pointer; padding: 0; display: inline-block; line-height: 1; vertical-align: baseline;`;
                span.textContent = selectedText.trim();
                span.dataset.note = text;
                span.dataset.noteId = timestamp;
                span.onclick = () => showNoteTooltip(span, text);
                
                currentSelectionRange.deleteContents();
                currentSelectionRange.insertNode(span);
            } catch (err2) {
                console.error('Fallback note style failed:', err2);
            }
        }

        // Add to state
        notes.value.unshift({
            type: 'note',
            text: selectedText,
            data: text,
            timestamp: timestamp
        });

        saveAnnotationsToStorage();
        closeFloatingEditor();
        window.getSelection().removeAllRanges();
        hideMenu();
    }
};

const deleteNote = (noteTimestamp) => {
    // Remove from state
    notes.value = notes.value.filter(n => n.timestamp !== noteTimestamp);
    saveAnnotationsToStorage();

    // Remove from DOM
    document.querySelectorAll(`span[data-note-id="${noteTimestamp}"]`).forEach(span => {
        const text = span.textContent;
        span.replaceWith(document.createTextNode(text));
    });
};

const showNoteTooltip = (element, noteText) => {
    const existing = document.getElementById('note-tooltip');
    if (existing) existing.remove();

    const tooltip = document.createElement('div');
    tooltip.id = 'note-tooltip';
    tooltip.style.cssText = `
        position: absolute; background: #fffcf0; border: 1px solid #e8dfc4; border-radius: 8px;
        padding: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); max-width: 250px; z-index: 99999; 
        font-size: 13px; color: #2c2616; border-left: 4px solid #f59e0b;
        animation: tooltipPop 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    `;
    tooltip.innerHTML = `
        <div style="line-height: 1.5; color: #2c2616;">${noteText}</div>
    `;
    
    document.body.appendChild(tooltip);

    const rect = element.getBoundingClientRect();
    tooltip.style.top = `${rect.bottom + window.scrollY + 8}px`;
    tooltip.style.left = `${rect.left + window.scrollX}px`;

    // Add animation style if not exists
    if (!document.getElementById('tooltip-animation')) {
        const style = document.createElement('style');
        style.id = 'tooltip-animation';
        style.textContent = `
            @keyframes tooltipPop {
                from { opacity: 0; transform: scale(0.9) translateY(-5px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
        `;
        document.head.appendChild(style);
    }

    setTimeout(() => {
        const clickAway = (e) => {
            if (!tooltip.contains(e.target) && e.target !== element) {
                tooltip.style.opacity = '0';
                tooltip.style.transform = 'scale(0.9) translateY(-5px)';
                tooltip.style.transition = 'all 0.15s ease';
                setTimeout(() => tooltip.remove(), 150);
                document.removeEventListener('click', clickAway);
            }
        };
        document.addEventListener('click', clickAway);
    }, 10);
};

// Toggle Sidebar exposure
const toggleNotesPanel = () => {
    isNotesPanelOpen.value = !isNotesPanelOpen.value;
};

// Global Exposure for Bottom Nav Button
defineExpose({
    toggleNotesPanel,
    notesCount: computed(() => notes.value.length)
});

onMounted(() => {
    loadAnnotations();
    document.addEventListener('mouseup', handleMouseUp);
    document.addEventListener('mousemove', onDragging);
    document.addEventListener('mouseup', stopDrag);
});

onUnmounted(() => {
    document.removeEventListener('mouseup', handleMouseUp);
    document.removeEventListener('mousemove', onDragging);
    document.removeEventListener('mouseup', stopDrag);
    hideMenu();
});
</script>

<template>
    <div class="text-annotator-ui relative z-[100000]">
        
        <!-- Animated Context Menu -->
        <Transition name="pop">
            <div 
                v-if="isMenuVisible" 
                class="annotation-menu-container fixed z-[99999] bg-white border border-slate-200 rounded-full shadow-xl p-1.5 flex gap-1 items-center"
                :style="{ top: menuPosition.top + 'px', left: menuPosition.left + 'px' }"
            >
                <button 
                    @click="applyHighlight"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-yellow-400 text-slate-900 rounded-full text-xs font-bold hover:bg-yellow-500 transition-all active:scale-[0.95]"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    Highlight
                </button>
                
                <button 
                    @click="openFloatingEditor"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-full text-xs font-bold hover:bg-blue-700 transition-all active:scale-[0.95]"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Note
                </button>

                <div class="w-px h-4 bg-slate-200 mx-1"></div>

                <button 
                    @click="handleCopy"
                    class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-full transition-all active:scale-[0.95]"
                    title="Copy Text"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                </button>
            </div>
        </Transition>
        
        <!-- Floating Inline Notepad Editor -->
        <Transition name="pop">
            <div 
                v-if="isFloatingEditorVisible"
                class="floating-note-editor fixed z-[100001] w-[320px] bg-[#fffcf0] rounded-2xl shadow-[0_20px_40px_rgba(0,0,0,0.2)] border border-[#e8dfc4] overflow-hidden flex flex-col"
                :style="{ top: editorPosition.top + 'px', left: editorPosition.left + 'px' }"
            >
                <!-- Editor Header (Draggable) -->
                <div 
                    class="px-4 py-2 bg-[#f9f3db] border-b border-[#e8dfc4] flex justify-between items-center cursor-move select-none"
                    @mousedown="startDrag"
                >
                    <span class="text-xs font-bold text-[#8c7a4e] tracking-widest uppercase">Quick Note ✍️</span>
                    <button @click="closeFloatingEditor" class="text-[#8c7a4e] hover:text-[#5c4a1e] text-lg font-bold leading-none">&times;</button>
                </div>

                <div class="p-4 notepad-paper-effect bg-white/50">
                    <textarea 
                        v-model="currentNoteText"
                        class="w-full h-32 bg-transparent border-none focus:ring-0 text-[#2c2616] text-sm leading-[1.8] resize-none placeholder:text-[#b5a987] notepad-textarea"
                        placeholder="Type your note here..."
                        maxlength="500"
                        autofocus
                    ></textarea>
                </div>

                <div class="px-4 py-2.5 bg-[#f9f3db]/50 border-t border-[#e8dfc4] flex justify-between items-center">
                    <span class="text-[10px] font-bold tracking-widest text-[#8c7a4e]">
                        {{ currentNoteText.length }}/500
                    </span>
                    <div class="flex gap-2">
                        <button 
                            @click="closeFloatingEditor" 
                            class="px-3 py-1 rounded-lg text-[11px] font-bold text-[#8c7a4e] hover:bg-[#efead2]"
                        >Cancel</button>
                        <button 
                            @click="saveNote" 
                            class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[11px] font-bold shadow-md shadow-amber-500/20 active:scale-95 disabled:opacity-50"
                            :disabled="!currentNoteText.trim()"
                        >Save</button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Unified Notepad-Style Sidebar Panel -->
        <div 
            class="fixed top-0 right-0 h-full w-[400px] bg-white shadow-[-10px_0_30px_rgba(0,0,0,0.15)] transition-all duration-500 ease-[cubic-bezier(0.2,1,0.3,1)] flex flex-col z-[99998] border-l border-slate-200 overflow-hidden"
            :class="isNotesPanelOpen ? 'translate-x-0' : 'translate-x-[110%]'">
            
            <!-- Notepad Header -->
            <div class="notepad-ui-header p-5 border-b border-slate-100 bg-white flex justify-between items-center shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">📝</span>
                    <div>
                        <h3 class="m-0 text-lg font-bold text-slate-800 tracking-tight">Your Notes</h3>
                    </div>
                </div>
                <button 
                    @click="toggleNotesPanel" 
                    class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-900 transition-all text-xl"
                >×</button>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col bg-white">
                
                <!-- Contextual Notes List -->
                <div class="p-6">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                        Your Notes ({{ notes.length }})
                    </h4>

                    <div v-if="notes.length === 0" class="text-center py-12 px-6 bg-white/30 rounded-xl border border-dashed border-[#d8cfb4]">
                        <p class="text-sm font-medium text-[#8c7a4e]">No contextual notes yet</p>
                        <p class="text-[11px] text-[#b5a987] mt-1">Select text in the passage to add specific notes</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="note in notes" :key="note.timestamp" class="bg-white/60 hover:bg-white/90 border border-[#e8dfc4] rounded-xl p-4 relative group transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                            <button 
                                @click="deleteNote(note.timestamp)"
                                class="absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:bg-red-600 shadow-sm z-10"
                                title="Delete note"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            
                            <div class="text-[11px] text-[#8c7a4e] italic mb-2 py-2 px-3 bg-[#fcf8e8] rounded-lg border-l-4 border-amber-400 line-clamp-3 font-medium">
                                "{{ note.text }}"
                            </div>
                            
                            <div class="text-[13px] text-[#2c2616] leading-relaxed font-medium whitespace-pre-wrap pl-1">{{ note.data }}</div>
                            
                        </div>
                    </div>
                </div>

            </div>
            
            <!-- Notepad Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 text-[10px] text-slate-400 text-center font-bold tracking-widest uppercase italic">
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Pop Transition for Menu */
.pop-enter-active {
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pop-leave-active {
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}
.pop-enter-from, .pop-leave-to {
    opacity: 0;
    transform: scale(0.8) translateY(10px);
}

/* CSS Isolation for Annotation Menu */
.annotation-menu-container, 
.annotation-menu-container * {
    margin: revert !important;
    padding: revert !important;
    line-height: normal !important;
    font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
    box-sizing: border-box !important;
}

.annotation-menu-container {
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
    padding: 6px !important;
    background-color: white !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 9999px !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}

.annotation-menu-container button {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 6px 12px !important;
    border-radius: 9999px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border: none !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}

.annotation-menu-container button svg {
    width: 14px !important;
    height: 14px !important;
    display: block !important;
}

.annotation-menu-container .bg-yellow-400 {
    background-color: #facc15 !important;
    color: #0f172a !important;
}

.annotation-menu-container .bg-yellow-400:hover {
    background-color: #eab308 !important;
}

.annotation-menu-container .bg-blue-600 {
    background-color: #2563eb !important;
    color: white !important;
}

.annotation-menu-container .bg-blue-600:hover {
    background-color: #1d4ed8 !important;
}

.annotation-menu-container .text-slate-500 {
    color: #64748b !important;
    background: transparent !important;
    padding: 6px !important;
}

.annotation-menu-container .text-slate-500:hover {
    color: #0f172a !important;
    background-color: #f1f5f9 !important;
}

.annotation-menu-container .bg-slate-200 {
    background-color: #e2e8f0 !important;
    width: 1px !important;
    height: 16px !important;
    margin: 0 4px !important;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d8cfb4;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #c9bf9f;
}

.notepad-paper-effect {
    background-image: linear-gradient(rgba(232, 223, 196, 0.5) 1px, transparent 1px);
    background-size: 100% 28px;
    background-attachment: local;
    position: relative;
}

.animation-modalPop {
    animation: modalPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalPop {
    from { opacity: 0; transform: scale(0.9) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

:deep(.notepad-textarea) {
    font-family: 'Segoe Print', 'Comic Sans MS', cursive;
}

/* Ensure annotations stand out but professional */
:deep(span[data-is-highlight="true"]) {
    transition: background-color 0.3s ease;
}
:deep(span[data-note-id]) {
    transition: transform 0.2s ease;
}
:deep(span[data-note-id]:hover) {
    transform: translateY(-1px);
    filter: brightness(0.98);
}
</style>
