<x-admin-layout>
    <x-slot:title>Website Settings</x-slot>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Website Settings</h1>
                <p class="text-gray-600 mt-2">Manage your website's appearance and general information</p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.settings.website.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                @csrf

                <!-- Basic Information -->
                <div class="bg-white shadow-xl rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Basic Information
                    </h2>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Site Title -->
                        <div>
                            <label for="site_title" class="block text-sm font-medium text-gray-700">Site Title</label>
                            <input type="text" name="site_title" id="site_title" 
                                   value="{{ old('site_title', $settings->site_title) }}" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">This will be used as the website title in browser tabs and SEO</p>
                            @error('site_title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Site Logo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Site Logo</label>
                            <div class="mt-2 flex items-center space-x-6">
                                @if($settings->site_logo)
                                    <div class="relative group">
                                        <img src="{{ $settings->logo_url }}" alt="Site Logo" class="h-20 w-auto rounded-lg shadow-sm">
                                        <button type="button" 
                                                onclick="if(confirm('Are you sure you want to remove the logo?')) { document.getElementById('remove-logo-form').submit(); }"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="h-20 w-20 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <input type="file" name="site_logo" id="site_logo" accept="image/*" 
                                           onchange="previewImage(this, 'logo-preview')"
                                           class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                    <div id="logo-preview" class="mt-2 hidden">
                                        <p class="text-xs text-gray-500 mb-1">Preview:</p>
                                        <img src="" alt="Logo Preview" class="h-16 w-auto rounded shadow">
                                    </div>
                                </div>
                            </div>
                            @error('site_logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dark Mode Logo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dark Mode Logo</label>
                            <div class="mt-2 flex items-center space-x-6">
                                @if($settings->dark_mode_logo)
                                    <div class="relative group bg-gray-900 p-2 rounded-lg">
                                        <img src="{{ $settings->dark_mode_logo_url }}" alt="Dark Mode Logo" class="h-16 w-auto rounded">
                                        <button type="button" 
                                                onclick="if(confirm('Are you sure you want to remove the dark mode logo?')) { document.getElementById('remove-dark-logo-form').submit(); }"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="h-20 w-20 rounded-lg border-2 border-dashed border-gray-300 bg-gray-900 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <input type="file" name="dark_mode_logo" id="dark_mode_logo" accept="image/*" 
                                           onchange="previewImage(this, 'dark-logo-preview')"
                                           class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF up to 2MB (for dark backgrounds)</p>
                                    <div id="dark-logo-preview" class="mt-2 hidden">
                                        <p class="text-xs text-gray-500 mb-1">Preview:</p>
                                        <div class="bg-gray-900 p-2 rounded inline-block">
                                            <img src="" alt="Dark Logo Preview" class="h-16 w-auto rounded">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('dark_mode_logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Favicon -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Favicon</label>
                            <div class="mt-2 flex items-center space-x-6">
                                @if($settings->favicon)
                                    <div class="relative group">
                                        <img src="{{ $settings->favicon_url }}" alt="Favicon" class="h-12 w-12 rounded shadow-sm">
                                        <button type="button" 
                                                onclick="if(confirm('Are you sure you want to remove the favicon?')) { document.getElementById('remove-favicon-form').submit(); }"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="h-12 w-12 rounded border-2 border-dashed border-gray-300 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <input type="file" name="favicon" id="favicon" accept=".ico,.png" 
                                           onchange="previewImage(this, 'favicon-preview')"
                                           class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="mt-1 text-xs text-gray-500">ICO or PNG, 16x16 or 32x32 pixels</p>
                                    <div id="favicon-preview" class="mt-2 hidden">
                                        <p class="text-xs text-gray-500 mb-1">Preview:</p>
                                        <img src="" alt="Favicon Preview" class="h-8 w-8 rounded shadow">
                                    </div>
                                </div>
                            </div>
                            @error('favicon')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white shadow-xl rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        Contact Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Contact Email -->
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                            <input type="email" name="contact_email" id="contact_email" 
                                   value="{{ old('contact_email', $settings->contact_email) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('contact_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contact Phone -->
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                            <input type="text" name="contact_phone" id="contact_phone" 
                                   value="{{ old('contact_phone', $settings->contact_phone) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('contact_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" id="address" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $settings->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="bg-white shadow-xl rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                        </svg>
                        Social Media Links
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Facebook -->
                        <div>
                            <label for="facebook_url" class="block text-sm font-medium text-gray-700">
                                <i class="fab fa-facebook text-blue-600 mr-1"></i> Facebook URL
                            </label>
                            <input type="url" name="facebook_url" id="facebook_url" 
                                   value="{{ old('facebook_url', $settings->facebook_url) }}"
                                   placeholder="https://facebook.com/yourpage"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Twitter -->
                        <div>
                            <label for="twitter_url" class="block text-sm font-medium text-gray-700">
                                <i class="fab fa-twitter text-blue-400 mr-1"></i> Twitter URL
                            </label>
                            <input type="url" name="twitter_url" id="twitter_url" 
                                   value="{{ old('twitter_url', $settings->twitter_url) }}"
                                   placeholder="https://twitter.com/yourhandle"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Instagram -->
                        <div>
                            <label for="instagram_url" class="block text-sm font-medium text-gray-700">
                                <i class="fab fa-instagram text-pink-600 mr-1"></i> Instagram URL
                            </label>
                            <input type="url" name="instagram_url" id="instagram_url" 
                                   value="{{ old('instagram_url', $settings->instagram_url) }}"
                                   placeholder="https://instagram.com/yourhandle"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- YouTube -->
                        <div>
                            <label for="youtube_url" class="block text-sm font-medium text-gray-700">
                                <i class="fab fa-youtube text-red-600 mr-1"></i> YouTube URL
                            </label>
                            <input type="url" name="youtube_url" id="youtube_url" 
                                   value="{{ old('youtube_url', $settings->youtube_url) }}"
                                   placeholder="https://youtube.com/channel/yourchannel"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- LinkedIn -->
                        <div>
                            <label for="linkedin_url" class="block text-sm font-medium text-gray-700">
                                <i class="fab fa-linkedin text-blue-700 mr-1"></i> LinkedIn URL
                            </label>
                            <input type="url" name="linkedin_url" id="linkedin_url" 
                                   value="{{ old('linkedin_url', $settings->linkedin_url) }}"
                                   placeholder="https://linkedin.com/company/yourcompany"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Footer Settings -->
                <div class="bg-white shadow-xl rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Footer Settings
                    </h2>

                    <div class="space-y-6">
                        <!-- Footer Text -->
                        <div>
                            <label for="footer_text" class="block text-sm font-medium text-gray-700">Footer Text</label>
                            <textarea name="footer_text" id="footer_text" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('footer_text', $settings->footer_text) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">This text will appear in the footer of your website</p>
                            @error('footer_text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Copyright Text -->
                        <div>
                            <label for="copyright_text" class="block text-sm font-medium text-gray-700">Copyright Text</label>
                            <input type="text" name="copyright_text" id="copyright_text" 
                                   value="{{ old('copyright_text', $settings->copyright_text) }}"
                                   placeholder="© 2024 Your Company. All rights reserved."
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('copyright_text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="bg-white shadow-xl rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        SEO Settings
                    </h2>

                    <div class="space-y-6">
                        <!-- Meta Description -->
                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" rows="2"
                                      maxlength="160"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('meta_description', $settings->meta_tags['description'] ?? '') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Maximum 160 characters for search engine optimization</p>
                            @error('meta_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Meta Keywords -->
                        <div>
                            <label for="meta_keywords" class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                            <input type="text" name="meta_keywords" id="meta_keywords" 
                                   value="{{ old('meta_keywords', $settings->meta_tags['keywords'] ?? '') }}"
                                   placeholder="ielts, mock test, practice test, english test"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Separate keywords with commas</p>
                            @error('meta_keywords')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Feature Toggles -->
                <div class="bg-white shadow-xl rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Feature Toggles
                    </h2>

                    <div class="space-y-4">
                        <!-- Human Evaluation Toggle -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Expert Evaluation (Online Students)</h3>
                                <p class="text-xs text-gray-500 mt-1">Allow online students to request expert evaluation from IELTS examiners</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="human_evaluation_enabled" value="0">
                                <input type="checkbox" name="human_evaluation_enabled" value="1"
                                       {{ old('human_evaluation_enabled', $settings->human_evaluation_enabled) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            id="submitBtn"
                            class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span id="submitText">Save Settings</span>
                    </button>
                </div>
            </form>
            
            {{-- Hidden forms for delete actions --}}
            <form id="remove-logo-form" action="{{ route('admin.settings.website.remove-logo') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            
            <form id="remove-dark-logo-form" action="{{ route('admin.settings.website.remove-dark-logo') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            
            <form id="remove-favicon-form" action="{{ route('admin.settings.website.remove-favicon') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4F46E5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Success notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            background: #10B981;
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
    @endpush
    
    @push('scripts')
    <script>
        // Image preview function
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const previewImg = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.classList.add('hidden');
            }
        }
        
        // Show loading overlay
        function showLoading() {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="loading-spinner"></div>';
            document.body.appendChild(overlay);
        }
        
        // Show success notification
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = `
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('settingsForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            
            // File size validation
            const logoInput = document.getElementById('site_logo');
            const darkLogoInput = document.getElementById('dark_mode_logo');
            const faviconInput = document.getElementById('favicon');
            
            function validateFileSize(file, maxSizeMB) {
                const maxSize = maxSizeMB * 1024 * 1024; // Convert to bytes
                if (file && file.size > maxSize) {
                    alert(`File size must be less than ${maxSizeMB}MB. Your file is ${(file.size / 1024 / 1024).toFixed(2)}MB`);
                    return false;
                }
                return true;
            }
            
            logoInput.addEventListener('change', function(e) {
                if (!validateFileSize(e.target.files[0], 2)) {
                    e.target.value = '';
                    document.getElementById('logo-preview').classList.add('hidden');
                }
            });
            
            darkLogoInput.addEventListener('change', function(e) {
                if (!validateFileSize(e.target.files[0], 2)) {
                    e.target.value = '';
                    document.getElementById('dark-logo-preview').classList.add('hidden');
                }
            });
            
            faviconInput.addEventListener('change', function(e) {
                if (!validateFileSize(e.target.files[0], 0.5)) {
                    e.target.value = '';
                    document.getElementById('favicon-preview').classList.add('hidden');
                }
            });
            
            // Form submission with AJAX
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                submitBtn.disabled = true;
                submitText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
                showLoading();
                
                // Create FormData
                const formData = new FormData(form);
                
                // Submit form via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Remove loading overlay
                    document.querySelector('.loading-overlay').remove();
                    
                    // Reset button
                    submitBtn.disabled = false;
                    submitText.innerHTML = '<svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Save Settings';
                    
                    if (data.success) {
                        showNotification(data.message || 'Settings saved successfully!');
                        
                        // Reload page after a short delay to show updated images
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        alert('Error saving settings. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Remove loading overlay
                    const overlay = document.querySelector('.loading-overlay');
                    if (overlay) overlay.remove();
                    
                    // Reset button
                    submitBtn.disabled = false;
                    submitText.innerHTML = '<svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Save Settings';
                    
                    alert('Error saving settings. Please try again.');
                });
            });
            
            // Check for success message from server
            @if(session('success'))
                showNotification('{{ session('success') }}');
            @endif
        });
    </script>
    @endpush
</x-admin-layout>
