<x-admin-layout>
    <x-slot name="title">Edit User - {{ $user->name }}</x-slot>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-4 py-5 sm:p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="mt-1 text-sm text-gray-600">Update user information and role.</p>
            </div>

            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-300 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700">
                            Phone Number
                        </label>
                        <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('phone_number') border-red-300 @enderror">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            New Password
                        </label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('password') border-red-300 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Leave blank to keep current password</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm New Password
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">
                            Role Type <span class="text-red-500">*</span>
                        </label>
                        <select name="role" id="role" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('role') border-red-300 @enderror"
                            onchange="toggleCustomRole()">
                            <option value="student" {{ old('role', $user->role) === 'Student' ? 'selected' : '' }}>Student</option>
                            <option value="teacher" {{ old('role', $user->role) === 'Teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="admin" {{ old('role', $user->role) === 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="custom" {{ old('role', $user->role_id ? 'custom' : '') === 'custom' ? 'selected' : '' }}>Custom Role</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Select Custom Role for specific permissions</p>
                    </div>

                    <!-- Custom Role Selection -->
                    <div id="custom_role_section" style="display: {{ old('role', $user->role_id ? 'custom' : '') === 'custom' ? 'block' : 'none' }};">
                        <label for="custom_role_id" class="block text-sm font-medium text-gray-700">
                            Select Custom Role <span class="text-red-500">*</span>
                        </label>
                        <select name="custom_role_id" id="custom_role_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('custom_role_id') border-red-300 @enderror">
                            <option value="">Select a custom role</option>
                            @if(isset($roles) && $roles->count() > 0)
                                @foreach($roles as $role)
                                    @if(!$role->is_system_role)
                                        <option value="{{ $role->id }}" {{ old('custom_role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }} ({{ $role->permissions->count() }} permissions)
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @error('custom_role_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">This will replace the default role</p>
                    </div>

                    <!-- Email Verified -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="email_verified" id="email_verified" value="1" 
                                {{ old('email_verified', $user->email_verified_at ? '1' : '0') ? 'checked' : '' }}
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="email_verified" class="font-medium text-gray-700">Email is verified</label>
                            <p class="text-gray-500">Check to mark email as verified</p>
                        </div>
                    </div>

                    <!-- Ban Status -->
                    @if($user->isBanned())
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">User is banned</h3>
                                <p class="mt-1 text-sm text-red-700">Banned on {{ $user->banned_at->format('M d, Y') }}</p>
                                <p class="mt-1 text-sm text-red-700">Reason: {{ $user->ban_reason }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-6 flex items-center justify-end space-x-4">
                    <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-gray-600 hover:text-gray-900">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleCustomRole() {
    const roleSelect = document.getElementById('role');
    const customRoleSection = document.getElementById('custom_role_section');
    const customRoleSelect = document.getElementById('custom_role_id');
    
    if (roleSelect.value === 'custom') {
        customRoleSection.style.display = 'block';
        customRoleSelect.required = true;
    } else {
        customRoleSection.style.display = 'none';
        customRoleSelect.required = false;
        customRoleSelect.value = '';
    }
}

// Show custom role section on page load if custom is selected
document.addEventListener('DOMContentLoaded', function() {
    toggleCustomRole();
});
</script>
</x-admin-layout>
