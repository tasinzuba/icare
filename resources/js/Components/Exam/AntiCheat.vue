<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    disableContextMenu: {
        type: Boolean,
        default: true
    },
    disableFind: {
        type: Boolean,
        default: true
    },
    disableInspect: {
        type: Boolean,
        default: true
    },
    disableSpellcheck: {
        type: Boolean,
        default: true
    },
    trapBackButton: {
        type: Boolean,
        default: true
    }
});

let observer = null;

const applyAntiCheatAttributes = (el) => {
    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
        el.setAttribute('spellcheck', 'false');
        el.setAttribute('autocorrect', 'off');
        el.setAttribute('autocomplete', 'off');
        el.setAttribute('autocapitalize', 'off');
        el.classList.add('no-spellcheck');
    }
};

const handleContextMenu = (e) => {
    if (props.disableContextMenu) {
        e.preventDefault();
    }
};

const handleKeydown = (e) => {
    // Block Find on Page (Ctrl+F / Cmd+F)
    if (props.disableFind && (e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'f') {
        e.preventDefault();
    }

    // Block Inspect Element shortcuts
    if (props.disableInspect) {
        // F12
        if (e.key === 'F12') {
            e.preventDefault();
        }
        // Ctrl+Shift+I / Cmd+Option+I - Use code instead of key since shift modifies the key value to uppercase
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.code === 'KeyI') {
            e.preventDefault();
        }
        // Ctrl+Shift+J / Cmd+Option+J
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.code === 'KeyJ') {
            e.preventDefault();
        }
        // Ctrl+U / Cmd+Option+U (View Source)
        if ((e.ctrlKey || e.metaKey) && e.code === 'KeyU') {
             e.preventDefault();
        }
        // Cmd+Option+I / Cmd+Option+J / Cmd+Option+U
        if (e.metaKey && e.altKey) {
            if (e.code === 'KeyI' || e.code === 'KeyJ' || e.code === 'KeyU') {
                e.preventDefault();
            }
        }
    }
};

const handlePopState = (e) => {
    if (props.trapBackButton) {
        // Push the state back immediately to trap the user
        history.pushState(null, null, window.location.href);
        // Dispatch a custom event if the parent component wants to show a warning modal
        window.dispatchEvent(new CustomEvent('back-button-trapped'));
    }
};

onMounted(() => {
    // 1. Initial Attributes Application
    if (props.disableSpellcheck) {
        document.querySelectorAll('input, textarea').forEach(applyAntiCheatAttributes);

        // 2. Setup MutationObserver for dynamically added inputs (blanks, dropdowns)
        observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) { // Element node
                        if (node.tagName === 'INPUT' || node.tagName === 'TEXTAREA') {
                            applyAntiCheatAttributes(node);
                        }
                        // Also check children if a whole container was added
                        if (node.querySelectorAll) {
                            node.querySelectorAll('input, textarea').forEach(applyAntiCheatAttributes);
                        }
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // 3. Attach Event Listeners
    if (props.disableContextMenu) {
        document.addEventListener('contextmenu', handleContextMenu);
    }
    
    if (props.disableFind || props.disableInspect) {
        document.addEventListener('keydown', handleKeydown);
    }

    if (props.trapBackButton) {
        history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', handlePopState);
    }
});

onUnmounted(() => {
    // Cleanup listeners and observers
    if (observer) {
        observer.disconnect();
    }
    document.removeEventListener('contextmenu', handleContextMenu);
    document.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('popstate', handlePopState);
});
</script>

<template>
    <!-- Passive component, no UI -->
    <div style="display: none;"></div>
</template>

<style>
/* CSS fallback to ensure no spellcheck styles override */
.no-spellcheck {
    -webkit-spellcheck: false;
    -moz-spellcheck: false;
    spellcheck: false;
}
</style>
