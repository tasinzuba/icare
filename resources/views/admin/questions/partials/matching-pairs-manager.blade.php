{{-- Matching Pairs Manager --}}
<div id="matching-pairs-card" class="bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-base sm:text-lg font-medium text-gray-900">Matching Pairs Configuration</h3>
            <span id="pairs-count" class="text-sm text-gray-500">0 pairs</span>
        </div>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="mb-4">
            <p class="text-sm text-gray-600">Create matching pairs for this question. Students will match items from the left column to items in the right column.</p>
        </div>
        
        <div class="space-y-3" id="matching-pairs-container">
            <!-- Pairs will be added dynamically -->
        </div>
        
        <div class="mt-4">
            <button type="button" id="add-matching-pair-btn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Matching Pair
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const MatchingPairsManager = {
    pairCount: 0,
    
    init() {
        const addBtn = document.getElementById('add-matching-pair-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.addPair());
        }
        
        // Add default pairs
        for (let i = 0; i < 3; i++) {
            this.addPair();
        }
    },
    
    addPair() {
        const container = document.getElementById('matching-pairs-container');
        if (!container) return;
        
        const index = this.pairCount;
        
        const pairDiv = document.createElement('div');
        pairDiv.className = 'matching-pair-item flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200';
        pairDiv.setAttribute('data-pair-index', index);
        
        pairDiv.innerHTML = `
            <span class="font-medium text-gray-700 min-w-[20px]">${index + 1}.</span>
            <input type="text" 
                   name="matching_pairs[${index}][left]" 
                   placeholder="Left side item" 
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                   required>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
            </svg>
            <input type="text" 
                   name="matching_pairs[${index}][right]" 
                   placeholder="Right side item" 
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                   required>
            <button type="button" onclick="MatchingPairsManager.removePair(${index})" 
                    class="text-red-500 hover:text-red-700 p-1">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        container.appendChild(pairDiv);
        this.pairCount++;
        this.updateCount();
    },
    
    removePair(index) {
        const container = document.getElementById('matching-pairs-container');
        const pairDiv = container.querySelector(`[data-pair-index="${index}"]`);
        if (pairDiv) {
            pairDiv.remove();
            this.reindexPairs();
        }
    },
    
    reindexPairs() {
        const container = document.getElementById('matching-pairs-container');
        const pairs = container.querySelectorAll('.matching-pair-item');
        this.pairCount = 0;
        
        pairs.forEach((pair, index) => {
            pair.setAttribute('data-pair-index', index);
            pair.querySelector('span').textContent = (index + 1) + '.';
            
            const leftInput = pair.querySelector('input[name*="[left]"]');
            const rightInput = pair.querySelector('input[name*="[right]"]');
            
            leftInput.name = `matching_pairs[${index}][left]`;
            rightInput.name = `matching_pairs[${index}][right]`;
            
            const btn = pair.querySelector('button');
            btn.setAttribute('onclick', `MatchingPairsManager.removePair(${index})`);
            
            this.pairCount++;
        });
        
        this.updateCount();
    },
    
    updateCount() {
        const countSpan = document.getElementById('pairs-count');
        if (countSpan) {
            countSpan.textContent = `${this.pairCount} pairs`;
        }
    }
};

window.MatchingPairsManager = MatchingPairsManager;
</script>
@endpush