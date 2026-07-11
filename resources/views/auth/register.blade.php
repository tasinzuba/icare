{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @php
        $settings = \App\Models\WebsiteSetting::first();
        $siteName = $settings ? $settings->site_title : 'CD IELTS';
        $favicon = $settings && $settings->favicon_path ? Storage::url($settings->favicon_path) : null;
        $logo = $settings && $settings->logo_path ? Storage::url($settings->logo_path) : null;
    @endphp
    
    <title>Register - {{ $siteName }}</title>
    
    @if($favicon)
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        {{-- Left Side - Branding (Desktop Only) --}}
        <div class="hidden lg:flex lg:w-2/5 bg-gradient-to-br from-red-500 to-rose-600 items-center justify-center px-12">
            <div class="text-white text-center">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-16 w-auto mx-auto mb-6 brightness-0 invert">
                @else
                    <div class="flex items-center justify-center space-x-3 mb-6">
                        <div class="w-16 h-16 bg-white rounded-lg flex items-center justify-center">
                            <span class="text-red-500 font-bold text-3xl">CD</span>
                        </div>
                    </div>
                @endif
                <h1 class="text-4xl font-bold mb-4">Join {{ $siteName }}</h1>
                <p class="text-lg opacity-90">Start your journey to success with 10,000+ students</p>
            </div>
        </div>

        {{-- Right Side - Form --}}
        <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-md">
                {{-- Logo for Mobile and Desktop Form --}}
                <div class="text-center mb-6">
                    <a href="{{ url('/') }}" class="inline-block">
                        @if($logo)
                            <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-12 w-auto mx-auto">
                        @else
                            <div class="flex items-center justify-center space-x-2">
                                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-xl">CD</span>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">{{ $siteName }}</span>
                            </div>
                        @endif
                    </a>
                </div>
                
                {{-- Mobile Header --}}
                <div class="lg:hidden text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
                    <p class="text-sm text-gray-600 mt-1">Join thousands of successful students</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
                    {{-- Registration Form - Compact Version --}}
                    <form method="POST" action="{{ route('register') }}" class="space-y-3">
                        @csrf

                        {{-- Name & Email in one row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="name" class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                                <input id="name" 
                                       name="name" 
                                       type="text" 
                                       value="{{ old('name') }}" 
                                       required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('name') border-red-300 @enderror"
                                       placeholder="John Doe">
                                @error('name')
                                    <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                <input id="email" 
                                       name="email" 
                                       type="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('email') border-red-300 @enderror"
                                       placeholder="you@example.com">
                                @error('email')
                                    <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone_number" class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                            <div class="flex rounded-lg overflow-hidden border border-gray-300 focus-within:ring-2 focus-within:ring-red-500 focus-within:border-transparent">
                                <select name="country_phone_code" class="px-2 text-sm bg-gray-50 border-r border-gray-300 focus:outline-none">
                                    @foreach($phoneCodes as $countryCode => $phoneData)
                                        <option value="{{ $phoneData['code'] }}"
                                                {{ old('country_phone_code', $locationData['country_code'] ?? 'BD') == $countryCode ? 'selected' : '' }}>
                                            {{ $phoneData['flag'] }} {{ $phoneData['code'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="tel" 
                                       name="phone_number" 
                                       value="{{ old('phone_number') }}"
                                       required
                                       pattern="[0-9]{10,15}"
                                       class="flex-1 px-3 py-2 text-sm focus:outline-none"
                                       placeholder="1234567890">
                            </div>
                            @error('phone_number')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Country --}}
                        <div>
                            <label for="country_code" class="block text-xs font-medium text-gray-700 mb-1">Country</label>
                            <select id="country_code" 
                                    name="country_code" 
                                    required
                                    onchange="updateCountryName()"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                <option value="">Select country</option>
                                @foreach($countries as $code => $name)
                                    <option value="{{ $code }}" 
                                            data-name="{{ $name }}"
                                            {{ ($locationData['countryCode'] ?? '') === $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="country_name" name="country_name" value="{{ $locationData['countryName'] ?? '' }}">
                            @if($locationData)
                                
                            @endif
                        </div>
                        
                        {{-- Password & Confirm in one row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="password" class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                                <div class="relative">
                                    <input id="password" 
                                           name="password" 
                                           type="password" 
                                           required 
                                           class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('password') border-red-300 @enderror"
                                           placeholder="Min 8 characters">
                                    <button type="button" 
                                            onclick="togglePasswordVisibility('password', 'password-eye-icon')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="password-eye-icon">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">Confirm</label>
                                <div class="relative">
                                    <input id="password_confirmation" 
                                           name="password_confirmation" 
                                           type="password" 
                                           required 
                                           class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                           placeholder="Confirm password">
                                    <button type="button" 
                                            onclick="togglePasswordVisibility('password_confirmation', 'confirm-eye-icon')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="confirm-eye-icon">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Terms --}}
                        <div class="flex items-start">
                            <input id="terms" 
                                   name="terms" 
                                   type="checkbox" 
                                   required
                                   class="w-4 h-4 mt-0.5 text-red-500 border-gray-300 rounded focus:ring-red-500">
                            <label for="terms" class="ml-2 text-xs text-gray-700">
                                I agree to the <a href="{{ route('terms-of-service') }}" class="text-red-600 hover:underline" target="_blank">Terms</a>
                                and <a href="{{ route('privacy-policy') }}" class="text-red-600 hover:underline" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        @error('terms')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="w-full py-2.5 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition">
                            Create Account
                        </button>

                        {{-- Login Link --}}
                        <p class="text-center text-xs text-gray-600 pt-2">
                            Already have an account?
                            <a href="{{ route('login') }}" class="text-red-600 hover:text-red-500 font-medium">Sign in</a>
                        </p>
                    </form>
                </div>

                {{-- Footer Links --}}
                <div class="mt-6 text-center text-xs text-gray-500">
                    <a href="{{ route('terms-of-service') }}" class="hover:text-gray-700 transition">Terms</a>
                    <span class="mx-2">•</span>
                    <a href="{{ route('privacy-policy') }}" class="hover:text-gray-700 transition">Privacy</a>
                    <span class="mx-2">•</span>
                    <a href="{{ route('contact') }}" class="hover:text-gray-700 transition">Support</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateCountryName() {
            const select = document.getElementById('country_code');
            const selectedOption = select.options[select.selectedIndex];
            const countryName = selectedOption.getAttribute('data-name');
            document.getElementById('country_name').value = countryName || '';
            
            // Auto-update phone code
            const countryPhoneCodes = {
                'BD': '+880', 'IN': '+91', 'US': '+1', 'GB': '+44',
                'AU': '+61', 'CA': '+1', 'AE': '+971', 'SA': '+966'
            };
            
            const phoneSelect = document.querySelector('select[name="country_phone_code"]');
            const phoneCode = countryPhoneCodes[select.value];
            if (phoneCode && phoneSelect) {
                for (let option of phoneSelect.options) {
                    if (option.value === phoneCode) {
                        phoneSelect.value = phoneCode;
                        break;
                    }
                }
            }
        }

        // Password visibility toggle function
        function togglePasswordVisibility(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                passwordField.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }

        // Initialize on load
        updateCountryName();
    </script>
</body>
</html>