{{-- Answer-location marking for the passage / audioscript editor.

     A question offers its Location button on the result page only when the source text wraps that
     answer as {{Q5}}…{{Q5}}. The syntax was documented next to the question fields, but nothing
     said so here, where the author is actually editing the text — so no passage in the bank had a
     single marker. This puts the control on the editor itself: select the answer, give it a
     number, click Mark. --}}
<div class="mb-3 rounded-md border border-blue-100 bg-blue-50 px-3 py-2.5">
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs font-semibold text-blue-900">
            <i class="fas fa-location-dot mr-1"></i>Answer location
        </span>
        <span class="text-xs text-gray-600">Select the answer text, then</span>
        <label class="text-xs text-gray-600">Q</label>
        <input type="number" id="marker-number" min="1" max="60" value="1"
               class="w-16 px-2 py-1 border border-gray-300 rounded text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <button type="button" onclick="insertPassageMarker()"
                class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
            Mark
        </button>
        <button type="button" onclick="refreshPassageMarkers()"
                class="px-2 py-1 text-xs text-blue-700 hover:text-blue-900 underline">
            Refresh
        </button>
        <span id="marker-summary" class="text-xs text-gray-500 ml-auto">no answer locations marked</span>
    </div>
    <p class="text-[11px] text-gray-500 mt-1.5">
        Marking wraps the selection as
        <code class="bg-white px-1 rounded">&#123;&#123;Q5&#125;&#125;…&#123;&#123;Q5&#125;&#125;</code>.
        Students never see the braces — question 5 just gets a Location button that scrolls here.
    </p>
</div>

@once
@push('scripts')
{{-- @verbatim: this script builds literal {{Qn}} strings, which Blade would otherwise compile as
     echo tags. Nothing in here is Blade. --}}
@verbatim
<script>
/**
 * Wrap the current passage-editor selection in {{Qn}}…{{Qn}}.
 *
 * Works against TinyMCE when it has taken over #passage_text and the bare textarea otherwise, so
 * the button still does something before the editor finishes initialising.
 */
function insertPassageMarker() {
    const numberInput = document.getElementById('marker-number');
    const n = parseInt(numberInput?.value, 10);
    if (!Number.isFinite(n) || n < 1) {
        alert('Enter the question number this answer belongs to.');
        return;
    }

    const marker = '{{Q' + n + '}}';
    const editor = window.tinymce && tinymce.get('passage_text');

    if (editor) {
        const selected = editor.selection.getContent({ format: 'html' });
        if (!selected) {
            alert('Select the answer text in the passage first, then click Mark.');
            return;
        }
        if (selected.includes('{{Q')) {
            alert('That selection is already marked. Refresh to see the current list.');
            return;
        }
        editor.selection.setContent(marker + selected + marker);
    } else {
        const box = document.getElementById('passage_text');
        if (!box) return;
        const start = box.selectionStart, end = box.selectionEnd;
        if (start === end) {
            alert('Select the answer text in the passage first, then click Mark.');
            return;
        }
        box.value = box.value.slice(0, start) + marker + box.value.slice(start, end) + marker + box.value.slice(end);
    }

    if (numberInput) numberInput.value = n + 1; // next answer is usually the next question
    refreshPassageMarkers();
}

/** Re-read the passage and report which question numbers currently carry a location. */
function refreshPassageMarkers() {
    const summary = document.getElementById('marker-summary');
    if (!summary) return;

    const editor = window.tinymce && tinymce.get('passage_text');
    const box = document.getElementById('passage_text');
    const text = editor ? editor.getContent({ format: 'text' }) : (box ? box.value : '');

    // A location needs both halves of the pair; a lone {{Q5}} would not resolve on the result page.
    const paired = [];
    const seen = new Set();
    (text.match(/\{\{Q(\d+)\}\}/g) || []).forEach(function (m) {
        const num = m.replace(/\D/g, '');
        if (seen.has(num)) {
            if (!paired.includes(num)) paired.push(num);
        } else {
            seen.add(num);
        }
    });

    if (!paired.length) {
        summary.textContent = 'no answer locations marked';
        summary.className = 'text-xs text-gray-500 ml-auto';
        return;
    }

    paired.sort(function (a, b) { return a - b; });
    summary.textContent = paired.length + ' marked: Q' + paired.join(', Q');
    summary.className = 'text-xs text-green-700 font-medium ml-auto';
}

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(refreshPassageMarkers, 1200); // after TinyMCE takes over the textarea
    setInterval(refreshPassageMarkers, 3000);
});
</script>
@endverbatim
@endpush
@endonce
