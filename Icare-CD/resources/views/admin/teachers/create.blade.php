<x-admin-layout>
    <x-slot:title>Add New Teacher</x-slot>

    <!-- Page Header with Gradient -->
    <div class="mb-8">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-8 shadow-xl">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Create New Teacher</h1>
                    <p class="mt-2 text-indigo-100">Add a new teacher to your IELTS evaluation team</p>
                </div>
                <a href="{{ route('admin.teachers.index') }}" 
                   class="inline-flex items-center rounded-lg border-2 border-white/30 bg-white/10 backdrop-blur-sm px-4 py-2 text-sm font-medium text-white hover:bg-white/20 transition-all">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Teachers
                </a>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Account Information Card -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-lg border border-gray-100">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-5 border-b border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-xl bg-indigo-600 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-gray-900">Account Information</h3>
                        <p class="text-sm text-gray-600">Login credentials for the teacher account</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Full Name with Icon -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        <span class="flex items-center">
                            <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Full Name <span class="text-red-500 ml-1">*</span>
                        </span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}" 
                               required
                               class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 @error('name') border-red-500 @enderror"
                               placeholder="Enter teacher's full name">
                        @error('name')
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @enderror
                    </div>
                    @error('name')
                        <p class="mt-2 flex items-center text-sm text-red-600">
                            <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <span class="flex items-center">
                            <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Email Address <span class="text-red-500 ml-1">*</span>
                        </span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           required
                           class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 @error('email') border-red-500 @enderror"
                           placeholder="teacher@example.com">
                    @error('email')
                        <p class="mt-2 flex items-center text-sm text-red-600">
                            <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="flex items-center">
                                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Password <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required 
                               minlength="8"
                               class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 @error('password') border-red-500 @enderror"
                               placeholder="••••••••">
                        @error('password')
                            <p class="mt-2 flex items-center text-sm text-red-600">
                                <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @else
                            <p class="mt-2 text-xs text-gray-500">Minimum 8 characters</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="flex items-center">
                                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Confirm Password <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               required 
                               minlength="8"
                               class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10"
                               placeholder="••••••••">
                        <p class="mt-2 text-xs text-gray-500">Repeat the password</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teacher Profile Card -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-lg border border-gray-100">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-5 border-b border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-xl bg-purple-600 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-gray-900">Teacher Profile</h3>
                        <p class="text-sm text-gray-600">Professional information and expertise</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 space-y-8">
                <!-- Specialization Cards -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-4">
                        <span class="flex items-center">
                            <svg class="mr-2 h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Specialization <span class="text-red-500 ml-1">*</span>
                        </span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach([
                            'writing' => ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'color' => 'blue'],
                            'speaking' => ['icon' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z', 'color' => 'green'],
                            'reading' => ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'purple'],
                            'listening' => ['icon' => 'M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 001.414 1.414m2.828-9.9a9 9 0 012.263 0m0 12.728a9 9 0 01-2.263 0', 'color' => 'orange']
                        ] as $spec => $data)
                        <label class="group relative cursor-pointer">
                            <input type="checkbox" 
                                   name="specialization[]" 
                                   value="{{ $spec }}" 
                                   {{ in_array($spec, old('specialization', [])) ? 'checked' : '' }}
                                   class="peer sr-only">
                            <div class="relative overflow-hidden rounded-2xl border-2 border-gray-200 bg-white p-6 transition-all duration-300 hover:border-{{ $data['color'] }}-400 hover:shadow-lg peer-checked:border-{{ $data['color'] }}-500 peer-checked:bg-gradient-to-br peer-checked:from-{{ $data['color'] }}-50 peer-checked:to-{{ $data['color'] }}-100 peer-checked:shadow-xl">
                                <!-- Check Badge -->
                                <div class="absolute top-3 right-3 hidden peer-checked:block">
                                    <div class="rounded-full bg-{{ $data['color'] }}-500 p-1">
                                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col items-center text-center">
                                    <div class="rounded-xl bg-{{ $data['color'] }}-100 p-3 transition-all group-hover:scale-110 peer-checked:bg-{{ $data['color'] }}-500">
                                        <svg class="h-8 w-8 text-{{ $data['color'] }}-600 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $data['icon'] }}" />
                                        </svg>
                                    </div>
                                    <span class="mt-3 text-sm font-bold text-gray-700 peer-checked:text-{{ $data['color'] }}-700">{{ ucfirst($spec) }}</span>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('specialization')
                        <p class="mt-3 flex items-center text-sm text-red-600">
                            <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Experience and Pricing -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 p-5 border-2 border-blue-100">
                        <label for="experience_years" class="block text-sm font-semibold text-gray-700 mb-3">
                            <span class="flex items-center">
                                <svg class="mr-2 h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Experience <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="experience_years" 
                                   id="experience_years" 
                                   value="{{ old('experience_years', 0) }}" 
                                   required 
                                   min="0"
                                   class="block w-full rounded-xl border-2 border-blue-200 bg-white px-4 py-3 pr-16 text-gray-900 transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 @error('experience_years') border-red-500 @enderror">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <span class="text-sm font-semibold text-blue-600">years</span>
                            </div>
                        </div>
                        @error('experience_years')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="rounded-xl bg-gradient-to-br from-green-50 to-emerald-50 p-5 border-2 border-green-100">
                        <label for="evaluation_price_tokens" class="block text-sm font-semibold text-gray-700 mb-3">
                            <span class="flex items-center">
                                <svg class="mr-2 h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Evaluation Price <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="evaluation_price_tokens" 
                                   id="evaluation_price_tokens" 
                                   value="{{ old('evaluation_price_tokens', 10) }}" 
                                   required 
                                   min="1"
                                   class="block w-full rounded-xl border-2 border-green-200 bg-white px-4 py-3 pr-20 text-gray-900 transition-all focus:border-green-500 focus:ring-4 focus:ring-green-500/10 @error('evaluation_price_tokens') border-red-500 @enderror">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <span class="text-sm font-semibold text-green-600">tokens</span>
                            </div>
                        </div>
                        @error('evaluation_price_tokens')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Qualifications -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center">
                            <svg class="mr-2 h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            Qualifications
                        </span>
                    </label>
                    <div id="qualifications-container" class="space-y-3">
                        @if(old('qualifications'))
                            @foreach(old('qualifications') as $qualification)
                                <div class="flex gap-3 group">
                                    <div class="flex-1 relative">
                                        <input type="text" 
                                               name="qualifications[]" 
                                               value="{{ $qualification }}"
                                               class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-500/10"
                                               placeholder="e.g., IELTS Examiner Certification">
                                    </div>
                                    <button type="button" 
                                            onclick="this.parentElement.remove()" 
                                            class="flex-shrink-0 rounded-xl bg-red-50 px-4 text-red-600 transition-all hover:bg-red-600 hover:text-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="flex gap-3 group">
                                <div class="flex-1 relative">
                                    <input type="text" 
                                           name="qualifications[]" 
                                           class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-500/10"
                                           placeholder="e.g., IELTS Examiner Certification">
                                </div>
                                <button type="button" 
                                        onclick="this.parentElement.remove()" 
                                        class="flex-shrink-0 rounded-xl bg-red-50 px-4 text-red-600 transition-all hover:bg-red-600 hover:text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                    <button type="button" 
                            onclick="addQualification()" 
                            class="mt-4 inline-flex items-center rounded-xl bg-purple-50 px-4 py-2.5 text-sm font-semibold text-purple-700 transition-all hover:bg-purple-100">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Qualification
                    </button>
                </div>

                <!-- Languages -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center">
                            <svg class="mr-2 h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                            </svg>
                            Languages
                        </span>
                    </label>
                    <div id="languages-container" class="space-y-3">
                        @if(old('languages'))
                            @foreach(old('languages') as $language)
                                <div class="flex gap-3 group">
                                    <div class="flex-1 relative">
                                        <input type="text" 
                                               name="languages[]" 
                                               value="{{ $language }}"
                                               class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-500/10"
                                               placeholder="e.g., English">
                                    </div>
                                    <button type="button" 
                                            onclick="this.parentElement.remove()" 
                                            class="flex-shrink-0 rounded-xl bg-red-50 px-4 text-red-600 transition-all hover:bg-red-600 hover:text-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="flex gap-3 group">
                                <div class="flex-1 relative">
                                    <input type="text" 
                                           name="languages[]" 
                                           value="English"
                                           class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-500/10"
                                           placeholder="e.g., English">
                                </div>
                                <button type="button" 
                                        onclick="this.parentElement.remove()" 
                                        class="flex-shrink-0 rounded-xl bg-red-50 px-4 text-red-600 transition-all hover:bg-red-600 hover:text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                    <button type="button" 
                            onclick="addLanguage()" 
                            class="mt-4 inline-flex items-center rounded-xl bg-purple-50 px-4 py-2.5 text-sm font-semibold text-purple-700 transition-all hover:bg-purple-100">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Language
                    </button>
                </div>

                <!-- Profile Description -->
                <div>
                    <label for="profile_description" class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center">
                            <svg class="mr-2 h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            Profile Description
                        </span>
                    </label>
                    <textarea name="profile_description" 
                              id="profile_description" 
                              rows="5"
                              class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-500/10 @error('profile_description') border-red-500 @enderror"
                              placeholder="Write a compelling description about the teacher's expertise, teaching style, achievements, and what makes them a great IELTS evaluator...">{{ old('profile_description') }}</textarea>
                    <div class="mt-2 flex items-center justify-between text-xs">
                        <span class="text-gray-500">Maximum 1000 characters</span>
                        <span class="text-gray-400">
                            <span id="char-count">0</span> / 1000
                        </span>
                    </div>
                    @error('profile_description')
                        <p class="mt-2 flex items-center text-sm text-red-600">
                            <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.teachers.index') }}" 
               class="inline-flex items-center rounded-xl border-2 border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-50 hover:border-gray-400">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-3 text-sm font-bold text-white shadow-lg transition-all hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl hover:scale-105">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Create Teacher Account
            </button>
        </div>
    </form>
</x-admin-layout>

@push('scripts')
<script>
    // Character counter
    const textarea = document.getElementById('profile_description');
    const charCount = document.getElementById('char-count');
    
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        
        // Initial count
        charCount.textContent = textarea.value.length;
    }

    function addQualification() {
        const container = document.getElementById('qualifications-container');
        const div = document.createElement('div');
        div.className = 'flex gap-3 group';
        div.innerHTML = `
            <div class="flex-1 relative">
                <input type="text" 
                       name="qualifications[]" 
                       class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-500/10"
                       placeholder="e.g., IELTS Examiner Certification">
            </div>
            <button type="button" 
                    onclick="this.parentElement.remove()" 
                    class="flex-shrink-0 rounded-xl bg-red-50 px-4 text-red-600 transition-all hover:bg-red-600 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        `;
        container.appendChild(div);
    }
    
    function addLanguage() {
        const container = document.getElementById('languages-container');
        const div = document.createElement('div');
        div.className = 'flex gap-3 group';
        div.innerHTML = `
            <div class="flex-1 relative">
                <input type="text" 
                       name="languages[]" 
                       class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-500/10"
                       placeholder="e.g., English">
            </div>
            <button type="button" 
                    onclick="this.parentElement.remove()" 
                    class="flex-shrink-0 rounded-xl bg-red-50 px-4 text-red-600 transition-all hover:bg-red-600 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        `;
        container.appendChild(div);
    }
</script>
@endpush
