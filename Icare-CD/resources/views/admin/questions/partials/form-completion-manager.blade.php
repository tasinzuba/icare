{{-- Form Completion Manager --}}
<div id="form-completion-card" class="bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-base sm:text-lg font-medium text-gray-900">Form Completion Configuration</h3>
            <span id="fields-count" class="text-sm text-gray-500">0 fields</span>
        </div>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Form Title</label>
                    <input type="text" 
                           name="form_title" 
                           placeholder="e.g., Student Registration Form" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Instructions</label>
                    <input type="text" 
                           name="form_instructions" 
                           placeholder="e.g., Complete the form below" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <div class="space-y-3" id="form-fields-container">
            <!-- Fields will be added dynamically -->
        </div>
        
        <div class="mt-4">
            <button type="button" id="add-form-field-btn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Form Field
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const FormCompletionManager = {
    fieldCount: 0,
    
    init() {
        const addBtn = document.getElementById('add-form-field-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.addField());
        }
        
        // Add default fields
        this.addField('Name');
        this.addField('Date of Birth');
        this.addField('Phone Number');
    },
    
    addField(label = '') {
        const container = document.getElementById('form-fields-container');
        if (!container) return;
        
        const index = this.fieldCount;
        
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'form-field-item flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200';
        fieldDiv.setAttribute('data-field-index', index);
        
        fieldDiv.innerHTML = `
            <span class="font-medium text-gray-700 min-w-[20px]">${index + 1}.</span>
            <input type="text" 
                   name="form_fields[${index}][label]" 
                   placeholder="Field label (e.g., Name)" 
                   value="${label}"
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                   required>
            <input type="text" 
                   name="form_fields[${index}][answer]" 
                   placeholder="Correct answer" 
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                   required>
            <button type="button" onclick="FormCompletionManager.removeField(${index})" 
                    class="text-red-500 hover:text-red-700 p-1">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        container.appendChild(fieldDiv);
        this.fieldCount++;
        this.updateCount();
    },
    
    removeField(index) {
        const container = document.getElementById('form-fields-container');
        const fieldDiv = container.querySelector(`[data-field-index="${index}"]`);
        if (fieldDiv) {
            fieldDiv.remove();
            this.reindexFields();
        }
    },
    
    reindexFields() {
        const container = document.getElementById('form-fields-container');
        const fields = container.querySelectorAll('.form-field-item');
        this.fieldCount = 0;
        
        fields.forEach((field, index) => {
            field.setAttribute('data-field-index', index);
            field.querySelector('span').textContent = (index + 1) + '.';
            
            const labelInput = field.querySelector('input[name*="[label]"]');
            const answerInput = field.querySelector('input[name*="[answer]"]');
            
            labelInput.name = `form_fields[${index}][label]`;
            answerInput.name = `form_fields[${index}][answer]`;
            
            const btn = field.querySelector('button');
            btn.setAttribute('onclick', `FormCompletionManager.removeField(${index})`);
            
            this.fieldCount++;
        });
        
        this.updateCount();
    },
    
    updateCount() {
        const countSpan = document.getElementById('fields-count');
        if (countSpan) {
            countSpan.textContent = `${this.fieldCount} fields`;
        }
    }
};

window.FormCompletionManager = FormCompletionManager;
</script>
@endpush