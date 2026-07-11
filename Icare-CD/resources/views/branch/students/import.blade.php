@extends('layouts.branch')

@section('title', 'Bulk Import Students')

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.students.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Students
    </a>
</div>

<div class="max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Bulk Import Students</h1>
    <p class="text-gray-600 mb-6">Upload an Excel or CSV file to import multiple students at once.</p>

    <!-- Instructions Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <h3 class="font-semibold text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i>Instructions</h3>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>1. Download the template file and fill in student details</li>
            <li>2. Required columns: <strong>Name</strong>, <strong>Email</strong></li>
            <li>3. Optional column: <strong>Number</strong> (phone number)</li>
            <li>4. Select a package and evaluation type for all students</li>
            <li>5. Set a common password for all students</li>
            <li>6. Existing offline students with active enrollment will be skipped</li>
            <li>7. Existing users (expired offline / public without subscription) will be re-enrolled</li>
        </ul>
        <div class="mt-3">
            <a href="{{ route('branch.students.import.template') }}" class="inline-flex items-center text-blue-700 hover:text-blue-900 font-medium">
                <i class="fas fa-download mr-2"></i> Download Template (CSV)
            </a>
        </div>
    </div>

    <!-- Import Form -->
    <div id="importForm" class="bg-white rounded-xl shadow-md p-6">
        <!-- Plan Selection -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">1. Select Plan</h2>

            {{-- Plan Type Toggle --}}
            <div class="grid grid-cols-2 gap-3 mb-4">
                <label class="cursor-pointer">
                    <input type="radio" name="plan_type" value="preset" id="planTypePreset" class="sr-only peer" checked>
                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-[#C8102E] peer-checked:bg-[#C8102E]/5 transition">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-box text-[#C8102E]"></i>
                            <span class="font-semibold text-sm text-gray-900">Preset Package</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Choose from existing packages</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="plan_type" value="custom" id="planTypeCustom" class="sr-only peer">
                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-[#C8102E] peer-checked:bg-[#C8102E]/5 transition">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-sliders-h text-[#C8102E]"></i>
                            <span class="font-semibold text-sm text-gray-900">Custom Plan</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Set your own test counts &amp; validity</p>
                    </div>
                </label>
            </div>

            {{-- Preset Package Mode --}}
            <div id="presetPanel">
                @if(isset($packages) && $packages->count() > 0)
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-box text-[#C8102E] mr-1"></i> Package for all students *
                </label>
                <select id="package_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C8102E]/30 focus:border-[#C8102E]/50">
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}"
                                data-full-tests="{{ $package->full_tests_allowed }}"
                                data-section-tests="{{ $package->section_tests_allowed }}"
                                data-validity-days="{{ $package->validity_days }}"
                                {{ $loop->first ? 'selected' : '' }}>
                            {{ $package->name }} - {{ $package->full_tests_allowed }} Full Tests, {{ $package->section_tests_allowed }} Section Tests, {{ $package->validity_days }} Days
                        </option>
                    @endforeach
                </select>
                <div id="packageSummary" class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center gap-4 text-sm">
                        <span><i class="fas fa-clipboard-list text-indigo-500 mr-1"></i> <strong id="summaryFullTests">0</strong> Full Tests</span>
                        <span><i class="fas fa-file-alt text-green-500 mr-1"></i> <strong id="summarySectionTests">0</strong> Section Tests</span>
                        <span><i class="fas fa-calendar-alt text-orange-500 mr-1"></i> <strong id="summaryValidity">0</strong> Days</span>
                    </div>
                </div>
                @else
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800"><i class="fas fa-exclamation-triangle mr-1"></i> No packages available. Use Custom Plan or ask admin to create packages.</p>
                </div>
                @endif
            </div>

            {{-- Custom Plan Mode --}}
            <div id="customPanel" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-clipboard-list text-indigo-500 mr-1"></i> Full Tests *
                        </label>
                        <input type="number" id="custom_full_tests" min="0" max="100" value="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C8102E]/30 focus:border-[#C8102E]/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-file-alt text-emerald-500 mr-1"></i> Section Tests *
                        </label>
                        <input type="number" id="custom_section_tests" min="0" max="500" value="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C8102E]/30 focus:border-[#C8102E]/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-calendar-alt text-orange-500 mr-1"></i> Validity Days *
                        </label>
                        <input type="number" id="custom_validity_days" min="1" max="365" value="30"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C8102E]/30 focus:border-[#C8102E]/50">
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2 italic">These limits will apply to every student in this import.</p>
            </div>
        </div>

        <!-- Evaluation Type -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">2. Evaluation Type</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-pen-fancy text-indigo-500 mr-1"></i> Writing & Speaking Evaluation *
                </label>
                <select id="evaluation_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="ai" selected>AI Only</option>
                    <option value="human">Human Only</option>
                    <option value="both">AI & Human</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">How writing and speaking sections will be evaluated for all imported students.</p>
            </div>
        </div>

        <!-- Batch -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">3. Batch (Optional)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-users text-indigo-500 mr-1"></i> Select Existing Batch
                    </label>
                    <select id="batch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- No Batch --</option>
                        @foreach(\App\Models\Batch::forBranch($branch->id)->active()->orderBy('name')->get() as $batch)
                            <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-plus-circle text-indigo-500 mr-1"></i> Or Create New Batch
                    </label>
                    <input type="text" id="new_batch_name"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="e.g. Batch 2026 - April">
                </div>
            </div>
        </div>

        <!-- Password -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">4. Set Password</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-lock text-indigo-500 mr-1"></i> Password for all students *
                </label>
                <input type="text" id="password" required minlength="6"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Enter password (min 6 characters)">
                <p class="text-xs text-gray-500 mt-1">All students will use this password to login.</p>
            </div>
        </div>

        <!-- File Upload -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">5. Upload File</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-file-excel text-green-500 mr-1"></i> Excel or CSV File *
                </label>
                <input type="file" id="file" required accept=".xlsx,.xls,.csv"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-500 mt-1">Accepted formats: .xlsx, .xls, .csv (Max 5MB)</p>
            </div>
        </div>

        <!-- Preview Section (Hidden initially) -->
        <div id="previewSection" class="mb-6 hidden">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">6. Preview</h2>
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-4">
                <p class="text-green-800">
                    <i class="fas fa-check-circle mr-2"></i>
                    Found <strong id="totalRows">0</strong> students to import
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Name</th>
                            <th class="px-3 py-2 text-left">Email</th>
                            <th class="px-3 py-2 text-left">Number</th>
                        </tr>
                    </thead>
                    <tbody id="previewBody"></tbody>
                </table>
                <p class="text-xs text-gray-500 mt-2 italic">Showing first 5 rows...</p>
            </div>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="mb-6 hidden">
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-800"><i class="fas fa-exclamation-circle mr-2"></i><span id="errorText"></span></p>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('branch.students.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="button" id="uploadBtn" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-eye mr-2"></i> Preview File
            </button>
            <button type="button" id="importBtn" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition hidden">
                <i class="fas fa-upload mr-2"></i> Start Import
            </button>
        </div>
    </div>

    <!-- Progress Section (Hidden initially) -->
    <div id="progressSection" class="bg-white rounded-xl shadow-md p-6 hidden">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            <i class="fas fa-spinner fa-spin mr-2 text-indigo-500"></i>
            Importing Students...
        </h2>

        <!-- Progress Bar -->
        <div class="mb-6">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Processing <span id="currentCount">0</span> of <span id="totalCount">0</span></span>
                <span id="progressPercent">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div id="progressBar" class="bg-indigo-600 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        <!-- Live Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-green-600" id="liveSuccess">0</p>
                <p class="text-sm text-green-700">Imported</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-yellow-600" id="liveSkipped">0</p>
                <p class="text-sm text-yellow-700">Skipped</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-red-600" id="liveErrors">0</p>
                <p class="text-sm text-red-700">Errors</p>
            </div>
        </div>

        <!-- Current Item -->
        <div class="text-center text-gray-500">
            <p id="currentItem">Preparing...</p>
        </div>
    </div>

    <!-- Completed Section (Hidden initially) -->
    <div id="completedSection" class="bg-white rounded-xl shadow-md p-6 hidden">
        <h2 class="text-xl font-semibold text-green-700 mb-4">
            <i class="fas fa-check-circle mr-2"></i>
            Import Completed!
        </h2>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-green-600" id="finalSuccess">0</p>
                <p class="text-sm text-green-700">Imported</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-yellow-600" id="finalSkipped">0</p>
                <p class="text-sm text-yellow-700">Skipped</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-red-600" id="finalErrors">0</p>
                <p class="text-sm text-red-700">Errors</p>
            </div>
        </div>

        <div class="flex justify-center space-x-4">
            <a href="{{ route('branch.students.import.export-results') }}" id="downloadCredentials"
               class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-download mr-2"></i> Download Credentials CSV
            </a>
            <a href="{{ route('branch.students.index') }}" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-list mr-2"></i> View Students
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('package_id');
    const summaryFullTests = document.getElementById('summaryFullTests');
    const summarySectionTests = document.getElementById('summarySectionTests');
    const summaryValidity = document.getElementById('summaryValidity');

    const fileInput = document.getElementById('file');
    const passwordInput = document.getElementById('password');
    const evaluationTypeSelect = document.getElementById('evaluation_type');
    const uploadBtn = document.getElementById('uploadBtn');
    const importBtn = document.getElementById('importBtn');

    const importForm = document.getElementById('importForm');
    const previewSection = document.getElementById('previewSection');
    const progressSection = document.getElementById('progressSection');
    const completedSection = document.getElementById('completedSection');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');

    let importId = null;
    let totalRows = 0;

    // Package selection
    if (packageSelect) {
        packageSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (summaryFullTests) summaryFullTests.textContent = selectedOption.dataset.fullTests || 0;
            if (summarySectionTests) summarySectionTests.textContent = selectedOption.dataset.sectionTests || 0;
            if (summaryValidity) summaryValidity.textContent = selectedOption.dataset.validityDays || 0;
        });
        packageSelect.dispatchEvent(new Event('change'));
    }

    // Plan type toggle (preset vs custom)
    const planTypeRadios = document.querySelectorAll('input[name="plan_type"]');
    const presetPanel = document.getElementById('presetPanel');
    const customPanel = document.getElementById('customPanel');
    planTypeRadios.forEach(r => r.addEventListener('change', () => {
        const isCustom = document.getElementById('planTypeCustom').checked;
        presetPanel.classList.toggle('hidden', isCustom);
        customPanel.classList.toggle('hidden', !isCustom);
    }));

    // Upload and Preview
    uploadBtn.addEventListener('click', async function() {
        const file = fileInput.files[0];
        if (!file) {
            showError('Please select a file');
            return;
        }

        const password = passwordInput.value;
        if (!password || password.length < 6) {
            showError('Password must be at least 6 characters');
            return;
        }

        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Uploading...';
        hideError();

        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch('{{ route("branch.students.import.preview") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Server error occurred' }));
                throw new Error(errorData.message || 'Upload failed');
            }

            const data = await response.json();

            if (data.success) {
                importId = data.import_id;
                totalRows = data.total_rows;

                document.getElementById('totalRows').textContent = totalRows;

                // Show preview
                const previewBody = document.getElementById('previewBody');
                previewBody.innerHTML = '';
                data.preview.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-t';
                    tr.innerHTML = `
                        <td class="px-3 py-2">${row[0] || '-'}</td>
                        <td class="px-3 py-2">${row[1] || '-'}</td>
                        <td class="px-3 py-2">${row[2] || '-'}</td>
                    `;
                    previewBody.appendChild(tr);
                });

                previewSection.classList.remove('hidden');
                importBtn.classList.remove('hidden');
                uploadBtn.classList.add('hidden');
            } else {
                showError(data.message || 'Failed to read file');
            }
        } catch (error) {
            showError('Upload failed: ' + error.message);
        }

        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-eye mr-2"></i> Preview File';
    });

    // Start Import
    importBtn.addEventListener('click', async function() {
        if (!importId) {
            showError('Please upload a file first');
            return;
        }

        const isCustom = document.getElementById('planTypeCustom').checked;
        const password = passwordInput.value;
        const evaluationType = evaluationTypeSelect.value;
        const batchId = document.getElementById('batch_id')?.value || '';
        const newBatchName = document.getElementById('new_batch_name')?.value || '';

        const plan = { type: isCustom ? 'custom' : 'preset' };
        if (isCustom) {
            plan.full = document.getElementById('custom_full_tests').value || '0';
            plan.section = document.getElementById('custom_section_tests').value || '0';
            plan.validity = document.getElementById('custom_validity_days').value || '30';
            if (parseInt(plan.validity) < 1) {
                showError('Validity days must be at least 1');
                return;
            }
        } else {
            plan.packageId = packageSelect?.value;
            if (!plan.packageId) {
                showError('Please select a package');
                return;
            }
        }

        // Hide form, show progress
        importForm.classList.add('hidden');
        progressSection.classList.remove('hidden');

        document.getElementById('totalCount').textContent = totalRows;

        await processImport(plan, password, evaluationType, batchId, newBatchName);
    });

    async function processImport(plan, password, evaluationType, batchId, newBatchName) {
        let completed = false;

        while (!completed) {
            try {
                const formData = new FormData();
                formData.append('import_id', importId);
                formData.append('plan_type', plan.type);
                if (plan.type === 'preset') {
                    formData.append('package_id', plan.packageId);
                } else {
                    formData.append('full_tests_allowed', plan.full);
                    formData.append('section_tests_allowed', plan.section);
                    formData.append('validity_days', plan.validity);
                }
                formData.append('password', password);
                formData.append('evaluation_type', evaluationType);
                formData.append('batch_size', 10);
                if (batchId) formData.append('batch_id', batchId);
                if (newBatchName) formData.append('new_batch_name', newBatchName);
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch('{{ route("branch.students.import.process") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Server error occurred' }));
                    throw new Error(errorData.message || 'Process failed');
                }

                const data = await response.json();

                if (!data.success) {
                    showError(data.message || 'Import failed');
                    progressSection.classList.add('hidden');
                    importForm.classList.remove('hidden');
                    return;
                }

                // Update progress
                const percent = Math.round((data.processed / totalRows) * 100);
                document.getElementById('currentCount').textContent = data.processed;
                document.getElementById('progressPercent').textContent = percent + '%';
                document.getElementById('progressBar').style.width = percent + '%';
                document.getElementById('liveSuccess').textContent = data.current_success || data.results?.success || 0;
                document.getElementById('liveSkipped').textContent = data.current_skipped || data.results?.skipped || 0;
                document.getElementById('liveErrors').textContent = data.current_errors || data.results?.errors || 0;
                document.getElementById('currentItem').textContent = `Processing row ${data.processed} of ${totalRows}...`;

                if (data.completed) {
                    completed = true;

                    // Show completed section
                    progressSection.classList.add('hidden');
                    completedSection.classList.remove('hidden');

                    document.getElementById('finalSuccess').textContent = data.results.success;
                    document.getElementById('finalSkipped').textContent = data.results.skipped;
                    document.getElementById('finalErrors').textContent = data.results.errors;
                }

            } catch (error) {
                showError('Import error: ' + error.message);
                progressSection.classList.add('hidden');
                importForm.classList.remove('hidden');
                return;
            }
        }
    }

    function showError(message) {
        errorText.textContent = message;
        errorMessage.classList.remove('hidden');
    }

    function hideError() {
        errorMessage.classList.add('hidden');
    }
});
</script>
@endpush
@endsection
