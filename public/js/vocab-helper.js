/**
 * Vocabulary Bank Helper
 *
 * Features:
 * - Wraps every meaningful word in clickable spans
 * - Shows dictionary popup using Tippy.js
 * - Allows saving words to vocabulary bank
 * - Caches dictionary lookups in localStorage
 */

class VocabHelper {
    constructor(options = {}) {
        this.enabled = options.enabled !== undefined ? options.enabled : true;
        this.containerSelector = options.containerSelector || '.vocab-content';
        this.stopWords = new Set([
            'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'be',
            'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will',
            'would', 'should', 'could', 'may', 'might', 'must', 'can', 'it', 'this',
            'that', 'these', 'those', 'i', 'you', 'he', 'she', 'we', 'they', 'my',
            'your', 'his', 'her', 'its', 'our', 'their', 'me', 'him', 'us', 'them'
        ]);
        this.cache = this.loadCache();
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        this.currentTippy = null;
    }

    /**
     * Initialize the vocab helper
     */
    init() {
        if (!this.enabled) return;

        const containers = document.querySelectorAll(this.containerSelector);
        containers.forEach(container => this.wrapWords(container));
    }

    /**
     * Wrap every meaningful word in clickable spans
     */
    wrapWords(container) {
        // Get all text nodes
        const walker = document.createTreeWalker(
            container,
            NodeFilter.SHOW_TEXT,
            null
        );

        const textNodes = [];
        let node;
        while (node = walker.nextNode()) {
            // Skip script, style, and already wrapped content
            if (node.parentNode.tagName !== 'SCRIPT' &&
                node.parentNode.tagName !== 'STYLE' &&
                !node.parentNode.classList.contains('vocab-word')) {
                textNodes.push(node);
            }
        }

        // Process each text node
        textNodes.forEach(textNode => {
            const text = textNode.textContent;

            // Regex to match words (3+ letters, alphabetic)
            const wordRegex = /\b([a-zA-Z]{3,}(?:'[a-z]{1,2})?)\b/g;

            let lastIndex = 0;
            const fragment = document.createDocumentFragment();
            let match;

            while ((match = wordRegex.exec(text)) !== null) {
                const word = match[1];
                const wordLower = word.toLowerCase();

                // Add text before the word
                if (match.index > lastIndex) {
                    fragment.appendChild(
                        document.createTextNode(text.substring(lastIndex, match.index))
                    );
                }

                // Check if word should be wrapped (not a stop word)
                if (!this.stopWords.has(wordLower)) {
                    const span = document.createElement('span');
                    span.className = 'vocab-word';
                    span.textContent = word;
                    span.dataset.word = wordLower;
                    span.style.cssText = 'cursor: pointer; transition: color 0.2s ease;';

                    // Add click handler
                    span.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.handleWordClick(span, wordLower);
                    });

                    fragment.appendChild(span);
                } else {
                    // Keep stop words as plain text
                    fragment.appendChild(document.createTextNode(word));
                }

                lastIndex = match.index + match[0].length;
            }

            // Add remaining text
            if (lastIndex < text.length) {
                fragment.appendChild(document.createTextNode(text.substring(lastIndex)));
            }

            // Replace the text node with the fragment
            textNode.parentNode.replaceChild(fragment, textNode);
        });
    }

    /**
     * Handle word click - show popup with definition
     */
    async handleWordClick(element, word) {
        // Close existing popup if any
        this.closePopup();

        // Show loading popup
        this.showLoadingPopup();

        try {
            // Check cache first
            let data = this.getFromCache(word);

            if (!data) {
                // Fetch from server
                const response = await fetch('/student/test/vocabulary-bank/lookup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ word })
                });

                data = await response.json();

                if (data.success) {
                    this.saveToCache(word, data);
                }
            }

            // Show definition popup
            this.showDefinitionPopup(word, data);

        } catch (error) {
            console.error('Error fetching definition:', error);
            this.showErrorPopup();
        }
    }

    /**
     * Close popup
     */
    closePopup() {
        const existingModal = document.getElementById('vocab-modal');
        if (existingModal) {
            existingModal.remove();
        }
    }

    /**
     * Show loading popup in center
     */
    showLoadingPopup() {
        this.closePopup();

        const modal = document.createElement('div');
        modal.id = 'vocab-modal';
        modal.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div style="background: white; border-radius: 12px; padding: 40px; max-width: 400px; text-align: center;">
                    <div style="width: 50px; height: 50px; border: 4px solid #f0f0f0; border-top: 4px solid #C8102E; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    <p style="margin-top: 20px; color: #666; font-size: 16px;">Loading definition...</p>
                </div>
            </div>
        `;

        // Add spin animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        modal.appendChild(style);

        document.body.appendChild(modal);

        // Click outside to close
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.parentElement === modal) {
                this.closePopup();
            }
        });
    }

    /**
     * Show error popup
     */
    showErrorPopup() {
        this.closePopup();

        const modal = document.createElement('div');
        modal.id = 'vocab-modal';
        modal.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div style="background: white; border-radius: 12px; padding: 30px; max-width: 400px; text-align: center;">
                    <div style="color: #C8102E; font-size: 48px; margin-bottom: 15px;">⚠️</div>
                    <p style="color: #C8102E; font-size: 18px; font-weight: 600;">Failed to load definition</p>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Click outside to close
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.parentElement === modal) {
                this.closePopup();
            }
        });

        // Auto close after 2 seconds
        setTimeout(() => this.closePopup(), 2000);
    }

    /**
     * Show definition popup with full word details
     */
    showDefinitionPopup(word, data) {
        this.closePopup();

        if (!data.success) {
            const modal = document.createElement('div');
            modal.id = 'vocab-modal';
            modal.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                    <div style="background: white; border-radius: 12px; padding: 30px; max-width: 400px; text-align: center;">
                        <p style="color: #666; font-size: 16px;">No definition found for "${word}"</p>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal || e.target.parentElement === modal) {
                    this.closePopup();
                }
            });
            return;
        }

        const content = this.buildPopupContent(word, data);

        const modal = document.createElement('div');
        modal.id = 'vocab-modal';
        modal.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div style="background: white; border-radius: 16px; padding: 0; max-width: 950px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative;">
                    ${content}
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Click outside to close
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.classList.contains('vocab-close-btn')) {
                this.closePopup();
            }
        });

        // Attach event listeners after popup is shown
        setTimeout(() => {
            this.attachPopupListeners(word, data);
        }, 100);
    }

    /**
     * Build popup HTML content
     */
    buildPopupContent(word, data) {
        const { phonetic, audio_url, definitions, examples, in_bank } = data;

        // Get part of speech from first definition
        const partOfSpeech = definitions && definitions.length > 0 ? definitions[0].pos : 'word';

        // Definitions HTML (left column)
        let definitionsHtml = '';
        if (definitions && definitions.length > 0) {
            definitions.slice(0, 4).forEach((def, index) => {
                definitionsHtml += `
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; align-items: start; gap: 8px;">
                            <span style="color: #C8102E; font-weight: 700; font-size: 16px; min-width: 24px;">${index + 1}.</span>
                            <div>
                                <span style="display: inline-block; padding: 3px 10px; background: rgba(200, 16, 46, 0.1); color: #C8102E; font-size: 11px; border-radius: 10px; margin-bottom: 6px; font-weight: 600; text-transform: uppercase;">${def.pos}</span>
                                <p style="color: #333; font-size: 14px; line-height: 1.7; margin: 0;">${def.definition}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // Examples HTML (right column)
        let examplesHtml = '';
        if (examples && examples.length > 0) {
            examples.slice(0, 4).forEach((ex, index) => {
                examplesHtml += `
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; align-items: start; gap: 8px;">
                            <span style="color: #C8102E; font-weight: 700; font-size: 16px; min-width: 24px;">${index + 1}.</span>
                            <p style="color: #555; font-size: 14px; font-style: italic; line-height: 1.7; margin: 0;">"${ex}"</p>
                        </div>
                    </div>
                `;
            });
        } else {
            examplesHtml = `<p style="color: #999; font-size: 14px; font-style: italic; text-align: center; padding: 20px 0;">No examples available</p>`;
        }

        // Add to bank button
        const addButton = !in_bank
            ? `<button class="vocab-add-btn" style="width: 100%; padding: 14px 24px; background: linear-gradient(to right, #C8102E, #A00E27); color: white; font-size: 15px; font-weight: 600; border-radius: 10px; border: none; cursor: pointer; margin-top: 20px;">
                 Add to My Vocab Bank
               </button>`
            : `<div style="width: 100%; padding: 14px 24px; background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 15px; text-align: center; border-radius: 10px; border: 1px solid #10b981; margin-top: 20px;">
                 ✓ Already in your Vocab Bank
               </div>`;

        return `
            <!-- Close Button -->
            <button class="vocab-close-btn" style="position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 24px; font-weight: bold; z-index: 10;">
                ×
            </button>

            <!-- Header with crimson background -->
            <div style="background: linear-gradient(135deg, #C8102E 0%, #A00E27 100%); padding: 28px 32px; border-radius: 16px 16px 0 0;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="color: rgba(255,255,255,0.85); font-size: 18px; font-weight: 500;">${partOfSpeech}</span>
                    <span style="color: rgba(255,255,255,0.5); font-size: 24px; font-weight: 300;">›</span>
                    <h3 style="color: white; font-size: 32px; font-weight: 700; margin: 0;">${word}</h3>
                    ${phonetic ? `<span style="color: rgba(255,255,255,0.85); font-size: 18px; font-style: italic; margin-left: 8px;">${phonetic}</span>` : ''}
                    ${audio_url ? `<button class="vocab-audio-btn" data-audio="${audio_url}" style="background: rgba(255,255,255,0.15); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; margin-left: 8px;">🔊</button>` : ''}
                </div>
            </div>

            <!-- Split Content Layout -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; padding: 28px 32px;">
                <!-- Left: Definitions -->
                <div>
                    <h4 style="color: #C8102E; font-size: 16px; font-weight: 700; margin: 0 0 20px 0; text-transform: uppercase; letter-spacing: 0.5px;">Definitions</h4>
                    <div class="definitions">
                        ${definitionsHtml}
                    </div>
                </div>

                <!-- Right: Examples -->
                <div style="border-left: 2px solid #f0f0f0; padding-left: 32px;">
                    <h4 style="color: #C8102E; font-size: 16px; font-weight: 700; margin: 0 0 20px 0; text-transform: uppercase; letter-spacing: 0.5px;">Examples</h4>
                    <div class="examples">
                        ${examplesHtml}
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding: 0 32px 28px 32px;">
                ${addButton}
            </div>
        `;
    }

    /**
     * Attach event listeners to popup buttons
     */
    attachPopupListeners(word, data) {
        // Audio button
        const audioBtn = document.querySelector('.vocab-audio-btn');
        if (audioBtn) {
            audioBtn.addEventListener('click', () => {
                const audio = new Audio(audioBtn.dataset.audio);
                audio.play();
            });
        }

        // Add to bank button
        const addBtn = document.querySelector('.vocab-add-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => {
                this.addToVocabBank(word, data, addBtn);
            });
        }
    }

    /**
     * Add word to vocabulary bank
     */
    async addToVocabBank(word, data, button) {
        button.disabled = true;
        button.textContent = 'Adding...';

        try {
            const response = await fetch('/student/test/vocabulary-bank/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    word: word,
                    definitions: data.definitions,
                    examples: data.examples,
                    phonetic: data.phonetic,
                    audio_url: data.audio_url,
                    source: 'practice'
                })
            });

            const result = await response.json();

            if (result.success) {
                button.className = 'w-full mt-3 px-4 py-2 bg-green-50 text-green-700 text-sm text-center rounded-lg border border-green-200';
                button.textContent = '✓ Added to Vocab Bank';
                button.disabled = true;

                // Update cache
                data.in_bank = true;
                this.saveToCache(word, data);
            } else {
                button.textContent = 'Failed to add';
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error adding to vocab bank:', error);
            button.textContent = 'Error - Try again';
            button.disabled = false;
        }
    }

    /**
     * Cache management
     */
    loadCache() {
        try {
            const cached = localStorage.getItem('vocab_cache');
            return cached ? JSON.parse(cached) : {};
        } catch {
            return {};
        }
    }

    saveToCache(word, data) {
        this.cache[word] = {
            data: data,
            timestamp: Date.now()
        };

        try {
            localStorage.setItem('vocab_cache', JSON.stringify(this.cache));
        } catch (e) {
            // Cache full, clear old entries
            this.clearOldCache();
        }
    }

    getFromCache(word) {
        const cached = this.cache[word];
        if (!cached) return null;

        // Cache valid for 7 days
        const maxAge = 7 * 24 * 60 * 60 * 1000;
        if (Date.now() - cached.timestamp > maxAge) {
            delete this.cache[word];
            return null;
        }

        return cached.data;
    }

    clearOldCache() {
        const maxAge = 7 * 24 * 60 * 60 * 1000;
        const now = Date.now();

        Object.keys(this.cache).forEach(word => {
            if (now - this.cache[word].timestamp > maxAge) {
                delete this.cache[word];
            }
        });

        try {
            localStorage.setItem('vocab_cache', JSON.stringify(this.cache));
        } catch (e) {
            // Still too big, clear everything
            this.cache = {};
            localStorage.removeItem('vocab_cache');
        }
    }

    /**
     * Toggle vocab helper on/off
     */
    toggle() {
        this.enabled = !this.enabled;
        if (this.enabled) {
            this.init();
        } else {
            // Remove all word wrapping
            document.querySelectorAll('.vocab-word').forEach(span => {
                const text = document.createTextNode(span.textContent);
                span.parentNode.replaceChild(text, span);
            });
        }
        return this.enabled;
    }
}

// Export for use in pages
window.VocabHelper = VocabHelper;
