<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CD IELTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        {{-- Left Side - Branding (Desktop Only) --}}
        <div class="hidden lg:flex lg:w-2/5 bg-gradient-to-br from-red-500 to-rose-600 items-center justify-center px-12">
            <div class="text-white text-center">
                <div class="mb-8">
                    <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto backdrop-blur-sm">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-4xl font-bold mb-4">Reset Your Password</h1>
                <p class="text-lg opacity-90">Create a new secure password for your account</p>
            </div>
        </div>

        {{-- Right Side - Form --}}
        <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-md">
                {{-- Mobile Header --}}
                <div class="lg:hidden text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
                    <p class="text-sm text-gray-600 mt-1">Create your new password</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
                    {{-- Desktop Header --}}
                    <div class="hidden lg:block text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Create New Password</h2>
                        <p class="text-sm text-gray-600 mt-1">Enter your new password below</p>
                    </div>

                    {{-- Success/Error Messages --}}
                    @if (session('status'))
                        <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-3 py-2 rounded-lg text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Reset Form --}}
                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

                        {{-- Email (readonly) --}}
                        <div>
                            <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   value="{{ $email ?? old('email') ?? request()->get('email') }}" 
                                   required 
                                   readonly
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:outline-none">
                            @error('email')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- New Password --}}
                        <div>
                            <label for="password" class="block text-xs font-medium text-gray-700 mb-1">New Password</label>
                            <div class="relative">
                                <input id="password" 
                                       name="password" 
                                       type="password" 
                                       required 
                                       autofocus
                                       onkeyup="checkPasswordStrength()"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent pr-10 @error('password') border-red-300 @enderror"
                                       placeholder="Enter new password">
                                <button type="button" 
                                        onclick="togglePassword('password')" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="eye-icon-password">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            
                            {{-- Password Strength Indicator --}}
                            <div class="mt-2">
                                <div class="flex items-center space-x-1">
                                    <div class="flex-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                                        <div id="password-strength" class="h-full bg-gray-300 transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                    <span id="strength-text" class="text-xs text-gray-500">Weak</span>
                                </div>
                            </div>
                            
                            {{-- Password Requirements --}}
                            <div class="mt-2 text-xs text-gray-500">
                                <p class="font-medium mb-1">Password must contain:</p>
                                <div class="grid grid-cols-2 gap-1">
                                    <div id="length-check" class="flex items-center text-gray-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        8+ characters
                                    </div>
                                    <div id="uppercase-check" class="flex items-center text-gray-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Upper & lowercase
                                    </div>
                                    <div id="number-check" class="flex items-center text-gray-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        One number
                                    </div>
                                    <div id="special-check" class="flex items-center text-gray-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Special character
                                    </div>
                                </div>
                            </div>
                            
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <div class="relative">
                                <input id="password_confirmation" 
                                       name="password_confirmation" 
                                       type="password" 
                                       required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent pr-10"
                                       placeholder="Confirm new password">
                                <button type="button" 
                                        onclick="togglePassword('password_confirmation')" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="eye-icon-confirm">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="w-full py-2.5 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition mt-6">
                            Reset Password
                        </button>

                        {{-- Back to Login --}}
                        <p class="text-center text-xs text-gray-600 pt-2">
                            Remember your password? 
                            <a href="{{ route('login') }}" class="text-red-600 hover:text-red-500 font-medium">
                                Back to login
                            </a>
                        </p>
                    </form>

                    {{-- Security Note --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <p class="text-xs text-gray-500">
                                For security, you'll be logged out from all devices after resetting your password.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eyeIcon = document.getElementById('eye-icon-' + fieldId.split('_')[0]);
            
            if (field.type === 'password') {
                field.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                field.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('password-strength');
            const strengthText = document.getElementById('strength-text');
            
            // Check individual requirements
            const hasLength = password.length >= 8;
            const hasUpperLower = password.match(/[a-z]/) && password.match(/[A-Z]/);
            const hasNumber = password.match(/[0-9]/);
            const hasSpecial = password.match(/[^a-zA-Z0-9]/);
            
            // Update requirement indicators
            updateRequirement('length-check', hasLength);
            updateRequirement('uppercase-check', hasUpperLower);
            updateRequirement('number-check', hasNumber);
            updateRequirement('special-check', hasSpecial);
            
            // Calculate strength
            let strength = 0;
            if (hasLength) strength++;
            if (hasUpperLower) strength++;
            if (hasNumber) strength++;
            if (hasSpecial) strength++;
            
            const percentage = (strength / 4) * 100;
            strengthBar.style.width = percentage + '%';
            
            if (strength === 0) {
                strengthBar.className = 'h-full bg-gray-300 transition-all duration-300';
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-xs text-gray-500';
            } else if (strength <= 2) {
                strengthBar.className = 'h-full bg-yellow-400 transition-all duration-300';
                strengthText.textContent = 'Fair';
                strengthText.className = 'text-xs text-yellow-600';
            } else if (strength === 3) {
                strengthBar.className = 'h-full bg-blue-500 transition-all duration-300';
                strengthText.textContent = 'Good';
                strengthText.className = 'text-xs text-blue-600';
            } else {
                strengthBar.className = 'h-full bg-green-500 transition-all duration-300';
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-xs text-green-600';
            }
        }

        function updateRequirement(id, isValid) {
            const element = document.getElementById(id);
            if (isValid) {
                element.className = 'flex items-center text-green-600';
                element.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />';
            } else {
                element.className = 'flex items-center text-gray-400';
                element.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
            }
        }
    </script>
</body>
</html>