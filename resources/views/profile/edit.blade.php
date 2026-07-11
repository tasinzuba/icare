{{-- resources/views/profile/edit.blade.php --}}
<x-dashboard-layout>
    <x-slot:title>My Profile</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ otpModalOpen: false, activeTab: 'profile' }">

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Account Settings</h1>
            <p class="text-gray-500 text-sm">Manage your profile and preferences</p>
        </div>

        <!-- Profile Card -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-center gap-5">
                <!-- Avatar -->
                <div class="relative flex-shrink-0">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-100">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-[#C8102E] flex items-center justify-center text-white text-2xl font-bold">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <form id="avatar-form" method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="avatar-input" name="avatar" accept="image/*">
                    </form>
                    <button type="button" onclick="document.getElementById('avatar-input').click()"
                            class="absolute -bottom-1 -right-1 w-8 h-8 bg-[#C8102E] rounded-lg flex items-center justify-center text-white hover:bg-[#A00E27] transition-all shadow-sm">
                        <i class="fas fa-camera text-xs"></i>
                    </button>
                </div>

                <!-- Info -->
                <div class="flex-1 text-center sm:text-left">
                    <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-500 text-sm mb-2">{{ $user->email }}</p>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-500">
                            Joined {{ $user->created_at->format('M Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex bg-gray-100 rounded-xl p-1 mb-6">
            <button @click="activeTab = 'profile'"
                    :class="activeTab === 'profile' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-medium text-sm transition-all">
                <i class="fas fa-user text-xs"></i>
                <span>Profile</span>
            </button>
            <button @click="activeTab = 'security'"
                    :class="activeTab === 'security' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-medium text-sm transition-all">
                <i class="fas fa-lock text-xs"></i>
                <span>Password</span>
            </button>
            <button @click="activeTab = 'preferences'"
                    :class="activeTab === 'preferences' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-medium text-sm transition-all">
                <i class="fas fa-cog text-xs"></i>
                <span>Settings</span>
            </button>
        </div>

        <!-- Profile Tab -->
        <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Personal Information</h3>

                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] transition-all bg-gray-50 focus:bg-white">
                            @error('name')
                                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                            <div class="relative">
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] transition-all bg-gray-50 focus:bg-white pr-20">
                                @if($user->hasVerifiedEmail() && !session('pending_email'))
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-500">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                @elseif(session('pending_email'))
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] px-2 py-0.5 rounded bg-amber-100 text-amber-700 font-medium">
                                        Pending
                                    </span>
                                @endif
                            </div>
                            @error('email')
                                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                            @enderror

                            @if(session('pending_email'))
                                <div class="mt-3 p-3 rounded-xl bg-gray-50 border border-gray-200">
                                    <p class="text-sm text-gray-600 mb-2">
                                        Verification code sent to <strong>{{ session('pending_email') }}</strong>
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" @click="otpModalOpen = true"
                                                class="text-sm px-3 py-1.5 bg-[#C8102E] text-white rounded-lg hover:bg-[#A00E27] transition font-medium">
                                            Enter Code
                                        </button>
                                        <form method="POST" action="{{ route('profile.resend-email-otp') }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                                                Resend
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('profile.cancel-email-change') }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm px-3 py-1.5 text-gray-500 hover:text-red-600 font-medium">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @php
                            $countries = \App\Helpers\CountryHelper::getAllCountries();
                        @endphp

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                            <div class="flex gap-2">
                                <select id="phone_country_code" name="phone_country_code"
                                    class="w-24 px-2 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] transition-all bg-gray-50 focus:bg-white text-sm">
                                    @foreach($countries as $code => $country)
                                        <option value="{{ $country['code'] }}">{{ $country['flag'] }} {{ $country['code'] }}</option>
                                    @endforeach
                                </select>
                                <input type="tel" name="phone_number" id="phone_number"
                                    value="{{ old('phone_number', $user->phone_number) }}"
                                    class="flex-1 px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] transition-all bg-gray-50 focus:bg-white"
                                    placeholder="Phone number">
                            </div>
                            @error('phone_number')
                                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Country -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                            <select name="country_code" id="country"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] transition-all bg-gray-50 focus:bg-white"
                                onchange="updatePhoneCountryCode(this.value)">
                                <option value="">Select Country</option>
                                @foreach($countries as $code => $country)
                                    <option value="{{ $code }}" data-phone="{{ $country['code'] }}" {{ $user->country_code == $code ? 'selected' : '' }}>
                                        {{ $country['flag'] }} {{ $country['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6 pt-6 border-t border-gray-100">
                        <button type="submit" class="px-6 py-2.5 bg-[#C8102E] text-white rounded-xl font-semibold hover:bg-[#A00E27] transition-all text-sm">
                            Save Changes
                        </button>
                        @if (session('status') === 'profile-updated')
                            <span class="text-sm text-emerald-600 font-medium flex items-center">
                                <i class="fas fa-check-circle mr-1.5"></i>Saved
                            </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Tab -->
        <div x-show="activeTab === 'security'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Change Password</h3>

                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="space-y-5">
                        <!-- Current Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Password</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="current_password" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] transition-all bg-gray-50 focus:bg-white pr-12">
                                <button type="button" onclick="togglePassword('current_password')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye" id="current_password_icon"></i>
                                </button>
                            </div>
                            @error('current_password', 'updatePassword')
                                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- New Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                                <div class="relative">
                                    <input type="password" name="password" id="password" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] transition-all bg-gray-50 focus:bg-white pr-12">
                                    <button type="button" onclick="togglePassword('password')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye" id="password_icon"></i>
                                    </button>
                                </div>
                                @error('password', 'updatePassword')
                                    <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="password_confirmation" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] transition-all bg-gray-50 focus:bg-white pr-12">
                                    <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye" id="password_confirmation_icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6 pt-6 border-t border-gray-100">
                        <button type="submit" class="px-6 py-2.5 bg-[#C8102E] text-white rounded-xl font-semibold hover:bg-[#A00E27] transition-all text-sm">
                            Update Password
                        </button>
                        @if (session('status') === 'password-updated')
                            <span class="text-sm text-emerald-600 font-medium flex items-center">
                                <i class="fas fa-check-circle mr-1.5"></i>Updated
                            </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Preferences Tab -->
        <div x-show="activeTab === 'preferences'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Preferences</h3>

                <div class="space-y-4">
                    <!-- Login Notifications -->
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50">
                        <div>
                            <h4 class="font-medium text-gray-900">Login Notifications</h4>
                            <p class="text-sm text-gray-500">Get notified when someone logs into your account</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C8102E]"></div>
                        </label>
                    </div>

                    <!-- Email Updates -->
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50">
                        <div>
                            <h4 class="font-medium text-gray-900">Email Updates</h4>
                            <p class="text-sm text-gray-500">Receive tips, news and study reminders</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C8102E]"></div>
                        </label>
                    </div>

                </div>

                <!-- Danger Zone -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Danger Zone</h4>
                    <div class="flex items-center justify-between p-4 rounded-xl border border-red-200 bg-red-50/50">
                        <div>
                            <h4 class="font-medium text-gray-900">Delete Account</h4>
                            <p class="text-sm text-gray-500">Permanently remove your account and all data</p>
                        </div>
                        <button type="button" class="px-4 py-2 text-sm font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-100 transition-all">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Verification OTP Modal -->
        <div x-show="otpModalOpen" x-cloak @keydown.escape.window="otpModalOpen = false"
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div @click="otpModalOpen = false" class="fixed inset-0 bg-black/50"></div>

                <div x-show="otpModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative bg-white rounded-2xl w-full max-w-sm p-6 shadow-xl">

                    <div class="text-center mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-[#C8102E]/10 mx-auto mb-4 flex items-center justify-center">
                            <i class="fas fa-envelope text-[#C8102E] text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Verify Email</h3>
                        <p class="text-sm text-gray-500 mt-1">Enter the 6-digit code sent to your email</p>
                    </div>

                    <form method="POST" action="{{ route('profile.verify-email-change') }}">
                        @csrf
                        <div class="mb-5">
                            <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" required
                                   class="w-full px-4 py-4 text-center text-2xl font-mono tracking-[0.5em] rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#C8102E]/20 focus:border-[#C8102E] transition-all"
                                   placeholder="------" autocomplete="off">
                            @error('otp')
                                <p class="mt-2 text-sm text-red-500 text-center">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 px-4 py-3 bg-[#C8102E] text-white rounded-xl font-semibold hover:bg-[#A00E27] transition-all text-sm">
                                Verify
                            </button>
                            <button type="button" @click="otpModalOpen = false" class="px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 text-center">
                        <form method="POST" action="{{ route('profile.resend-email-otp') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-[#C8102E]">
                                Didn't get the code? <span class="font-medium">Resend</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updatePhoneCountryCode(countryCode) {
            const countrySelect = document.getElementById('country');
            const phoneCodeSelect = document.getElementById('phone_country_code');
            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            const phoneCode = selectedOption.getAttribute('data-phone');

            if (phoneCode && phoneCodeSelect) {
                for (let i = 0; i < phoneCodeSelect.options.length; i++) {
                    if (phoneCodeSelect.options[i].value === phoneCode) {
                        phoneCodeSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const userCountryCode = '{{ $user->country_code }}';
            if (userCountryCode) {
                updatePhoneCountryCode(userCountryCode);
            }

            const phoneNumber = '{{ $user->phone_number }}';
            if (phoneNumber) {
                const match = phoneNumber.match(/^(\+\d+)\s*(.*)$/);
                if (match) {
                    const phoneCode = match[1];
                    const number = match[2];

                    const phoneCodeSelect = document.getElementById('phone_country_code');
                    for (let i = 0; i < phoneCodeSelect.options.length; i++) {
                        if (phoneCodeSelect.options[i].value === phoneCode) {
                            phoneCodeSelect.selectedIndex = i;
                            break;
                        }
                    }

                    document.getElementById('phone_number').value = number;
                }
            }
        });

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('avatar-input').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                document.getElementById('avatar-form').submit();
            }
        });
    </script>
    @endpush
</x-dashboard-layout>
