<x-admin-layout>
    <x-slot name="title">Bulk Import Students</x-slot>

    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
    </div>

    <div class="max-w-3xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Bulk Import Students</h1>
        <p class="text-gray-600 mb-6">Upload an Excel or CSV file to import multiple offline students at once.</p>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i>Instructions</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>1. Download the template and fill in student details</li>
                <li>2. Required columns: <strong>Name</strong>, <strong>Email</strong></li>
                <li>3. Optional: <strong>Number</strong> (phone), <strong>Password</strong> (if using per-student passwords)</li>
                <li>4. Select a branch, package, and password mode</li>
                <li>5. Existing active offline students will be skipped</li>
                <li>6. Expired/inactive users will be re-enrolled</li>
            </ul>
            <div class="mt-3 flex gap-4">
                <a href="{{ route('admin.users.import.template') }}" class="inline-flex items-center text-blue-700 hover:text-blue-900 font-medium">
                    <i class="fas fa-download mr-2"></i> Template (Basic)
                </a>
                <a href="{{ route('admin.users.import.template', ['with_password' => 1]) }}" class="inline-flex items-center text-blue-700 hover:text-blue-900 font-medium">
                    <i class="fas fa-download mr-2"></i> Template (With Password Column)
                </a>
            </div>
        </div>

        <!-- Import Form -->
        <div id="importForm" class="bg-white rounded-xl shadow-md p-6">
            <!-- Branch Selection -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">1. Select Branch</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-building text-indigo-500 mr-1"></i> Branch *
                    </label>
                    <select id="branch_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Package Selection -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">2. Select Package</h2>
                <div id="packageContainer">
                    <p class="text-sm text-gray-500" id="packagePlaceholder">Select a branch first to see available packages.</p>
                    <select id="package_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 hidden">
                    </select>
                </div>
                <div id="packageSummary" class="p-4 bg-indigo-50 rounded-lg mt-3 hidden">
                    <div class="flex items-center gap-4 text-sm">
                        <span><i class="fas fa-clipboard-list text-indigo-500 mr-1"></i> <strong id="summaryFullTests">0</strong> Full Tests</span>
                        <span><i class="fas fa-file-alt text-green-500 mr-1"></i> <strong id="summarySectionTests">0</strong> Section Tests</span>
                        <span><i class="fas fa-calendar-alt text-orange-500 mr-1"></i> <strong id="summaryValidity">0</strong> Days</span>
                    </div>
                </div>
            </div>

            <!-- Evaluation Type -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">3. Evaluation Type</h2>
                <select id="evaluation_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="ai" selected>AI Only</option>
                    <option value="human">Human Only</option>
                    <option value="both">AI & Human</option>
                </select>
            </div>

            <!-- Password Mode -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">4. Password Settings</h2>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                        <input type="radio" name="password_mode" value="custom" checked class="text-indigo-600">
                        <div>
                            <span class="font-medium text-gray-800">Same password for all</span>
                            <p class="text-xs text-gray-500">Set one password that all imported students will use</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                        <input type="radio" name="password_mode" value="auto" class="text-indigo-600">
                        <div>
                            <span class="font-medium text-gray-800">Auto-generate unique passwords</span>
                            <p class="text-xs text-gray-500">Each student gets a random 10-character password</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                        <input type="radio" name="password_mode" value="column" class="text-indigo-600">
                        <div>
                            <span class="font-medium text-gray-800">From CSV column</span>
                            <p class="text-xs text-gray-500">Use "Password" column from your file (auto-generates if missing)</p>
                        </div>
                    </label>
                </div>
                <div id="customPasswordField" class="mt-3">
                    <input type="text" id="password" minlength="6"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Enter password (min 6 characters)">
                </div>
            </div>

            <!-- File Upload -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">5. Upload File</h2>
                <input type="file" id="file" required accept=".xlsx,.xls,.csv"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-500 mt-1">Accepted: .xlsx, .xls, .csv (Max 5MB)</p>
            </div>

            <!-- Preview Section -->
            <div id="previewSection" class="mb-6 hidden">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">6. Preview</h2>
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-4">
                    <p class="text-green-800">
                        <i class="fas fa-check-circle mr-2"></i>
                        Found <strong id="totalRows">0</strong> students to import
                        <span id="passwordColumnDetected" class="hidden ml-2 text-sm">(Password column detected)</span>
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Name</th>
                                <th class="px-3 py-2 text-left">Email</th>
                                <th class="px-3 py-2 text-left">Number</th>
                                <th class="px-3 py-2 text-left" id="passwordHeader" style="display:none">Password</th>
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
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                <button type="button" id="uploadBtn" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-eye mr-2"></i> Preview File
                </button>
                <button type="button" id="importBtn" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition hidden">
                    <i class="fas fa-upload mr-2"></i> Start Import
                </button>
            </div>
        </div>

        <!-- Progress Section -->
        <div id="progressSection" class="bg-white rounded-xl shadow-md p-6 hidden">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-spinner fa-spin mr-2 text-indigo-500"></i> Importing Students...
            </h2>
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Processing <span id="currentCount">0</span> of <span id="totalCount">0</span></span>
                    <span id="progressPercent">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div id="progressBar" class="bg-indigo-600 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
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
        </div>

        <!-- Completed Section -->
        <div id="completedSection" class="bg-white rounded-xl shadow-md p-6 hidden">
            <h2 class="text-xl font-semibold text-green-700 mb-4">
                <i class="fas fa-check-circle mr-2"></i> Import Completed!
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
                <a href="{{ route('admin.users.import.export-results') }}" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-download mr-2"></i> Download Credentials CSV
                </a>
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-list mr-2"></i> View Users
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const branchSelect = document.getElementById('branch_id');
        const packageSelect = document.getElementById('package_id');
        const packagePlaceholder = document.getElementById('packagePlaceholder');
        const packageSummary = document.getElementById('packageSummary');
        const passwordModeRadios = document.querySelectorAll('input[name="password_mode"]');
        const customPasswordField = document.getElementById('customPasswordField');
        const fileInput = document.getElementById('file');
        const passwordInput = document.getElementById('password');
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
        let hasPasswordColumn = false;

        // Branch change → load packages
        branchSelect.addEventListener('change', async function() {
            const branchId = this.value;
            packageSelect.innerHTML = '';
            packageSelect.classList.add('hidden');
            packagePlaceholder.classList.remove('hidden');
            packageSummary.classList.add('hidden');

            if (!branchId) return;

            try {
                const resp = await fetch('{{ route("admin.users.import.packages") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ branch_id: branchId }),
                });
                const data = await resp.json();

                if (data.success && data.packages.length > 0) {
                    packagePlaceholder.classList.add('hidden');
                    packageSelect.classList.remove('hidden');

                    data.packages.forEach((pkg, i) => {
                        const opt = document.createElement('option');
                        opt.value = pkg.id;
                        opt.textContent = `${pkg.name} - ${pkg.full_tests_allowed} Full, ${pkg.section_tests_allowed} Section, ${pkg.validity_days} Days`;
                        opt.dataset.fullTests = pkg.full_tests_allowed;
                        opt.dataset.sectionTests = pkg.section_tests_allowed;
                        opt.dataset.validityDays = pkg.validity_days;
                        if (i === 0) opt.selected = true;
                        packageSelect.appendChild(opt);
                    });

                    packageSelect.dispatchEvent(new Event('change'));
                } else {
                    packagePlaceholder.textContent = 'No packages available for this branch.';
                }
            } catch (e) {
                packagePlaceholder.textContent = 'Failed to load packages.';
            }
        });

        // Package change → update summary
        packageSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt) {
                document.getElementById('summaryFullTests').textContent = opt.dataset.fullTests || 0;
                document.getElementById('summarySectionTests').textContent = opt.dataset.sectionTests || 0;
                document.getElementById('summaryValidity').textContent = opt.dataset.validityDays || 0;
                packageSummary.classList.remove('hidden');
            }
        });

        // Password mode toggle
        passwordModeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                customPasswordField.style.display = this.value === 'custom' ? 'block' : 'none';
            });
        });

        // Upload & Preview
        uploadBtn.addEventListener('click', async function() {
            const file = fileInput.files[0];
            if (!file) { showError('Please select a file'); return; }
            if (!branchSelect.value) { showError('Please select a branch'); return; }
            if (!packageSelect.value) { showError('Please select a package'); return; }

            const mode = document.querySelector('input[name="password_mode"]:checked').value;
            if (mode === 'custom' && (!passwordInput.value || passwordInput.value.length < 6)) {
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
                const response = await fetch('{{ route("admin.users.import.preview") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) {
                    const err = await response.json().catch(() => ({ message: 'Server error' }));
                    throw new Error(err.message || 'Upload failed');
                }

                const data = await response.json();

                if (data.success) {
                    importId = data.import_id;
                    totalRows = data.total_rows;
                    hasPasswordColumn = data.has_password_column;

                    document.getElementById('totalRows').textContent = totalRows;

                    if (hasPasswordColumn) {
                        document.getElementById('passwordColumnDetected').classList.remove('hidden');
                        document.getElementById('passwordHeader').style.display = '';
                    }

                    const previewBody = document.getElementById('previewBody');
                    previewBody.innerHTML = '';
                    data.preview.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.className = 'border-t';
                        let html = `<td class="px-3 py-2">${row[0] || '-'}</td>
                                    <td class="px-3 py-2">${row[1] || '-'}</td>
                                    <td class="px-3 py-2">${row[2] || '-'}</td>`;
                        if (hasPasswordColumn) {
                            html += `<td class="px-3 py-2">${row[3] || '-'}</td>`;
                        }
                        tr.innerHTML = html;
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
            if (!importId) { showError('Please upload a file first'); return; }

            importForm.classList.add('hidden');
            progressSection.classList.remove('hidden');
            document.getElementById('totalCount').textContent = totalRows;

            const mode = document.querySelector('input[name="password_mode"]:checked').value;
            await processImport(branchSelect.value, packageSelect.value, mode, passwordInput.value, document.getElementById('evaluation_type').value);
        });

        async function processImport(branchId, packageId, passwordMode, password, evaluationType) {
            let completed = false;
            while (!completed) {
                try {
                    const formData = new FormData();
                    formData.append('import_id', importId);
                    formData.append('branch_id', branchId);
                    formData.append('package_id', packageId);
                    formData.append('password_mode', passwordMode);
                    formData.append('password', password || '');
                    formData.append('evaluation_type', evaluationType);
                    formData.append('batch_size', 10);
                    formData.append('_token', '{{ csrf_token() }}');

                    const response = await fetch('{{ route("admin.users.import.process") }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    if (!response.ok) {
                        const err = await response.json().catch(() => ({ message: 'Server error' }));
                        throw new Error(err.message || 'Process failed');
                    }

                    const data = await response.json();
                    if (!data.success) {
                        showError(data.message || 'Import failed');
                        progressSection.classList.add('hidden');
                        importForm.classList.remove('hidden');
                        return;
                    }

                    const percent = Math.round((data.processed / totalRows) * 100);
                    document.getElementById('currentCount').textContent = data.processed;
                    document.getElementById('progressPercent').textContent = percent + '%';
                    document.getElementById('progressBar').style.width = percent + '%';
                    document.getElementById('liveSuccess').textContent = data.current_success || data.results?.success || 0;
                    document.getElementById('liveSkipped').textContent = data.current_skipped || data.results?.skipped || 0;
                    document.getElementById('liveErrors').textContent = data.current_errors || data.results?.errors || 0;

                    if (data.completed) {
                        completed = true;
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

        function showError(msg) { errorText.textContent = msg; errorMessage.classList.remove('hidden'); }
        function hideError() { errorMessage.classList.add('hidden'); }
    });
    </script>
    @endpush
</x-admin-layout>
