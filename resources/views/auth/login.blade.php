{{-- resources/views/auth/login.blade.php --}}
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
    
    <title>Sign In - {{ $siteName }}</title>
    
    @if($favicon)
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @endif
    
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
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-16 w-auto mx-auto mb-6 brightness-0 invert">
                @else
                    <div class="flex items-center justify-center space-x-3 mb-6">
                        <div class="w-16 h-16 bg-white rounded-lg flex items-center justify-center">
                            <span class="text-red-500 font-bold text-3xl">CD</span>
                        </div>
                    </div>
                @endif
                <h1 class="text-4xl font-bold mb-4">Welcome Back!</h1>
                <p class="text-lg opacity-90">Continue your IELTS preparation journey</p>
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
                    <h1 class="text-2xl font-bold text-gray-900">Sign In</h1>
                    <p class="text-sm text-gray-600 mt-1">Welcome back to {{ $siteName }}</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
                    {{-- Desktop Header --}}
                    <div class="hidden lg:block text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Sign In</h2>
                        <p class="text-sm text-gray-600 mt-1">Enter your credentials to continue</p>
                    </div>

                    {{-- Error Alert (Redirected from Admin Login) --}}
                    @if(session('error'))
                        <div class="mb-5 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                    @if(session('info'))
                                        <p class="text-xs text-red-600 mt-1">{{ session('info') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Login Form --}}
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        {{-- Email/Phone --}}
                        <div>
                            <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email or Phone</label>
                            <input id="email" 
                                   name="email" 
                                   type="text" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('email') border-red-300 @enderror"
                                   placeholder="you@example.com or phone number">
                            @error('email')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <input id="password" 
                                       name="password" 
                                       type="password" 
                                       required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent pr-10 @error('password') border-red-300 @enderror"
                                       placeholder="Enter your password">
                                <button type="button" 
                                        onclick="togglePasswordVisibility()" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="eye-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remember & Forgot --}}
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="remember" 
                                       class="w-4 h-4 text-red-500 border-gray-300 rounded focus:ring-red-500">
                                <span class="ml-2 text-xs text-gray-700">Remember me for 30 days</span>
                            </label>
                            <a href="{{ route('password.request') }}" 
                               class="text-xs text-red-600 hover:text-red-500 font-medium">
                                Forgot password?
                            </a>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="w-full py-2.5 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition">
                            Sign In
                        </button>

                    </form>

                    {{-- Divider --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="text-center">
                            <p class="text-xs text-gray-500 mb-3">Having trouble signing in?</p>
                            <div class="flex justify-center space-x-4 text-xs">
                                <a href="{{ route('password.request') }}" class="text-red-600 hover:text-red-500">Reset Password</a>
                                <span class="text-gray-300">•</span>
                                <a href="{{ route('contact') }}" class="text-red-600 hover:text-red-500">Contact Support</a>
                            </div>
                        </div>
                    </div>
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
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
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
    </script>
</body>
</html>