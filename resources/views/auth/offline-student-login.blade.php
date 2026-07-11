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

    <title>Student Login - {{ $siteName }}</title>

    @if($favicon)
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            {{-- Logo --}}
            <div class="text-center mb-6">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-12 w-auto mx-auto">
                @else
                    <div class="flex items-center justify-center space-x-2">
                        <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-2xl">CD</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-200">
                {{-- Header --}}
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Student Login</h2>
                    <p class="text-gray-500 text-sm mt-1">Enter your credentials to access tests</p>
                </div>

                {{-- Error Alert --}}
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3">
                        <p class="text-sm text-red-600">{{ $errors->first() }}</p>
                    </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="{{ route('offline.login.submit') }}" class="space-y-4">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input id="email"
                               name="email"
                               type="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="you@example.com">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input id="password"
                                   name="password"
                                   type="password"
                                   required
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Enter password">
                            <button type="button"
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input type="checkbox"
                               name="remember"
                               id="remember"
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                            class="w-full py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        Login
                    </button>
                </form>
            </div>

            {{-- Footer --}}
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">Contact your branch if you need help</p>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-400">Powered by</p>
                    @if($logo)
                        <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-6 w-auto mx-auto mt-1 opacity-60">
                    @else
                        <p class="text-sm font-semibold text-gray-500 mt-1">{{ $siteName }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
