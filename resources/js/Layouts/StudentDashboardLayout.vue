<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const nav = computed(() => page.props.dashboardNav || {});
const user = computed(() => page.props.auth?.user);
const settings = computed(() => nav.value.settings || {});
const routes = computed(() => nav.value.routes || {});
const isOffline = computed(() => nav.value.isOfflineStudent);
const notifications = computed(() => nav.value.notifications || []);
const unreadCount = computed(() => nav.value.unreadNotificationsCount || 0);
const currentPath = computed(() => nav.value.currentPath || '');
const avatarUrl = computed(() => nav.value.avatarUrl);
const csrfToken = computed(() => document.querySelector('meta[name="csrf-token"]')?.content || '');

// UI state
const mobileMenuOpen = ref(false);
const profileOpen = ref(false);
const notificationOpen = ref(false);
const practiceDropdownOpen = ref(false);
const selfPracticeDropdownOpen = ref(false);

// Route matching helpers
const isRoute = (pattern) => {
    return currentPath.value.includes(pattern);
};
const isActiveNav = (patterns) => {
    return patterns.some(p => currentPath.value.includes(p));
};

const handleLogout = () => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = routes.value.logout;
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = csrfToken.value;
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
};
</script>

<template>
    <div class="min-h-screen" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-lg border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <a :href="routes.dashboard" class="flex items-center space-x-2">
                            <img v-if="settings.logo_url" :src="settings.logo_url" :alt="settings.site_title" class="h-9 w-auto">
                            <template v-else>
                                <div class="w-9 h-9 bg-gradient-to-br from-[#C8102E] to-[#8B0000] rounded-lg flex items-center justify-center shadow">
                                    <span class="text-white font-bold text-sm">CD</span>
                                </div>
                                <span class="font-bold text-xl text-gray-900">{{ settings.site_title || 'IELTS Journey' }}</span>
                            </template>
                        </a>
                    </div>

                    <!-- Desktop Navigation - Center -->
                    <div v-if="!isOffline" class="hidden md:flex items-center space-x-1">
                        <a :href="routes.dashboard"
                           :class="['relative px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300', isRoute('student/dashboard') ? 'text-[#C8102E] bg-[#C8102E]/5' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50']">
                            Dashboard
                        </a>

                        <!-- Practice Dropdown -->
                        <div class="relative" @mouseenter="practiceDropdownOpen = true" @mouseleave="practiceDropdownOpen = false">
                            <button :class="['px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300 flex items-center gap-1.5', isActiveNav(['listening', 'reading', 'writing/index', 'speaking', 'full-test']) ? 'text-[#C8102E] bg-[#C8102E]/5' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50']">
                                <span>Mock Test</span>
                                <i class="fas fa-chevron-down text-[10px] transition-transform" :class="practiceDropdownOpen ? 'rotate-180' : ''"></i>
                            </button>

                            <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                                <div v-show="practiceDropdownOpen" class="absolute left-1/2 -translate-x-1/2 mt-2 w-[420px] bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                                    <div class="px-5 py-3 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Test Sections</p>
                                    </div>
                                    <div class="p-3 grid grid-cols-2 gap-2">
                                        <a :href="routes.listening" :class="['group flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition-all duration-300', isRoute('listening') ? 'bg-blue-50 ring-1 ring-blue-200' : '']">
                                            <div class="w-10 h-10 rounded-xl bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center transition-all duration-300">
                                                <i class="fas fa-headphones text-blue-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800 text-sm">Listening</p>
                                                <p class="text-xs text-gray-500">30 min • 40 questions</p>
                                            </div>
                                        </a>
                                        <a :href="routes.reading" :class="['group flex items-center gap-3 p-3 rounded-xl hover:bg-emerald-50 transition-all duration-300', isRoute('reading') ? 'bg-emerald-50 ring-1 ring-emerald-200' : '']">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-100 group-hover:bg-emerald-200 flex items-center justify-center transition-all duration-300">
                                                <i class="fas fa-book-reader text-emerald-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800 text-sm">Reading</p>
                                                <p class="text-xs text-gray-500">60 min • 40 questions</p>
                                            </div>
                                        </a>
                                        <a :href="routes.writing" :class="['group flex items-center gap-3 p-3 rounded-xl hover:bg-violet-50 transition-all duration-300', isRoute('writing') ? 'bg-violet-50 ring-1 ring-violet-200' : '']">
                                            <div class="w-10 h-10 rounded-xl bg-violet-100 group-hover:bg-violet-200 flex items-center justify-center transition-all duration-300">
                                                <i class="fas fa-pen-nib text-violet-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800 text-sm">Writing</p>
                                                <p class="text-xs text-gray-500">60 min • 2 tasks</p>
                                            </div>
                                        </a>
                                        <a :href="routes.speaking" :class="['group flex items-center gap-3 p-3 rounded-xl hover:bg-orange-50 transition-all duration-300', isRoute('speaking') ? 'bg-orange-50 ring-1 ring-orange-200' : '']">
                                            <div class="w-10 h-10 rounded-xl bg-orange-100 group-hover:bg-orange-200 flex items-center justify-center transition-all duration-300">
                                                <i class="fas fa-comment-dots text-orange-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800 text-sm">Speaking</p>
                                                <p class="text-xs text-gray-500">15 min • 3 parts</p>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="p-3 bg-gradient-to-r from-[#C8102E]/5 to-transparent border-t border-gray-100">
                                        <a :href="routes.fullTest" :class="['group flex items-center gap-3 p-3 rounded-xl hover:bg-[#C8102E]/10 transition-all duration-300', isRoute('full-test') ? 'bg-[#C8102E]/10 ring-1 ring-[#C8102E]/20' : '']">
                                            <div class="w-10 h-10 rounded-xl bg-[#C8102E]/10 group-hover:bg-[#C8102E]/20 flex items-center justify-center transition-all duration-300">
                                                <i class="fas fa-layer-group text-[#C8102E]"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-800 text-sm">Full Mock Test</p>
                                                <p class="text-xs text-gray-500">Complete IELTS simulation</p>
                                            </div>
                                            <i class="fas fa-arrow-right text-[#C8102E] opacity-0 group-hover:opacity-100 transition-all duration-300"></i>
                                        </a>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <a :href="routes.results"
                           :class="['relative px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300', isRoute('results') ? 'text-[#C8102E] bg-[#C8102E]/5' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50']">
                            Results
                        </a>

                        <!-- Self Practice Dropdown -->
                        <div class="relative" @mouseenter="selfPracticeDropdownOpen = true" @mouseleave="selfPracticeDropdownOpen = false">
                            <button :class="['px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300 flex items-center gap-1.5', isRoute('writing-practice') ? 'text-[#C8102E] bg-[#C8102E]/5' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50']">
                                <span>Self Practice</span>
                                <i class="fas fa-chevron-down text-[10px] transition-transform" :class="selfPracticeDropdownOpen ? 'rotate-180' : ''"></i>
                            </button>
                            <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                                <div v-show="selfPracticeDropdownOpen" class="absolute left-1/2 -translate-x-1/2 mt-2 w-[420px] bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                                    <div class="px-5 py-3 bg-gradient-to-r from-violet-50 to-white border-b border-gray-100">
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Question-wise Practice</p>
                                    </div>
                                    <div class="p-3 grid grid-cols-1 gap-2">
                                        <div class="space-y-1">
                                            <div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase">Writing</div>
                                            <a :href="routes.writingPracticeTask1" :class="['group flex items-center gap-3 p-3 rounded-xl hover:bg-violet-50 transition-all duration-300', isRoute('writing-practice/task1') ? 'bg-violet-50 ring-1 ring-violet-200' : '']">
                                                <div class="w-10 h-10 rounded-xl bg-violet-100 group-hover:bg-violet-200 flex items-center justify-center transition-all duration-300">
                                                    <i class="fas fa-chart-bar text-violet-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800 text-sm">Writing Task 1</p>
                                                    <p class="text-xs text-gray-500">Charts, graphs & diagrams</p>
                                                </div>
                                            </a>
                                            <a :href="routes.writingPracticeTask2" :class="['group flex items-center gap-3 p-3 rounded-xl hover:bg-violet-50 transition-all duration-300', isRoute('writing-practice/task2') ? 'bg-violet-50 ring-1 ring-violet-200' : '']">
                                                <div class="w-10 h-10 rounded-xl bg-violet-100 group-hover:bg-violet-200 flex items-center justify-center transition-all duration-300">
                                                    <i class="fas fa-pen-fancy text-violet-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800 text-sm">Writing Task 2</p>
                                                    <p class="text-xs text-gray-500">Essay writing practice</p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-gradient-to-r from-gray-50/50 to-transparent border-t border-gray-100">
                                        <div class="text-center py-2">
                                            <p class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i>More sections coming soon</p>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <a :href="routes.aiTutor"
                           :class="['relative px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300 flex items-center gap-1.5', isRoute('ai-tutor') ? 'text-[#C8102E] bg-[#C8102E]/5' : 'text-gray-600 hover:text-[#C8102E] hover:bg-gray-50']">
                            AI Tutor
                            <span class="px-1.5 py-0.5 bg-emerald-500 text-white text-[9px] font-bold rounded uppercase">New</span>
                        </a>

                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center space-x-2">
                        <template v-if="!isOffline">
                            <!-- Notifications -->
                            <div class="relative" @click.stop>
                                <button @click="notificationOpen = !notificationOpen"
                                        class="relative w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-all duration-300">
                                    <i class="fas fa-bell text-lg"></i>
                                    <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-[#C8102E] rounded-full flex items-center justify-center text-[10px] font-bold text-white ring-2 ring-white">
                                        {{ unreadCount > 9 ? '9+' : unreadCount }}
                                    </span>
                                </button>
                                <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 scale-95 -translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0">
                                    <div v-show="notificationOpen" @click.outside="notificationOpen = false" class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                                        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                                            <h3 class="font-bold text-gray-800">Notifications</h3>
                                            <span v-if="unreadCount > 0" class="px-2 py-0.5 bg-[#C8102E]/10 text-[#C8102E] text-xs font-bold rounded-full">{{ unreadCount }} new</span>
                                        </div>
                                        <div class="max-h-80 overflow-y-auto">
                                            <template v-if="notifications.length">
                                                <a v-for="notif in notifications" :key="notif.id" :href="notif.url" class="block p-4 hover:bg-gray-50 border-b border-gray-50 transition-all duration-300">
                                                    <div class="flex gap-3">
                                                        <div class="w-8 h-8 rounded-lg bg-[#C8102E]/10 flex items-center justify-center flex-shrink-0">
                                                            <i class="fas fa-bell text-[#C8102E] text-sm"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-800">{{ notif.title }}</p>
                                                            <p class="text-xs text-gray-500 mt-0.5">{{ notif.created_at }}</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </template>
                                            <div v-else class="p-8 text-center">
                                                <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                                    <i class="fas fa-bell-slash text-2xl text-gray-300"></i>
                                                </div>
                                                <p class="text-gray-500 text-sm font-medium">No new notifications</p>
                                            </div>
                                        </div>
                                        <div v-if="unreadCount > 5" class="p-3 border-t border-gray-100 bg-gray-50">
                                            <a :href="routes.notifications" class="block text-center text-sm font-semibold text-[#C8102E] hover:underline">View all notifications</a>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </template>

                        <!-- Profile Dropdown -->
                        <template v-if="!isOffline">
                            <div class="relative" @click.stop>
                                <button @click="profileOpen = !profileOpen" class="flex items-center gap-1.5 p-1 pr-2 rounded-xl hover:bg-gray-100 transition-all duration-300">
                                    <img v-if="avatarUrl" :src="avatarUrl" :alt="user?.name" class="w-9 h-9 rounded-lg object-cover ring-2 ring-gray-200">
                                    <div v-else class="w-9 h-9 rounded-lg bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex items-center justify-center text-white font-bold text-sm shadow ring-2 ring-gray-200">
                                        {{ user?.name?.charAt(0) || 'U' }}
                                    </div>
                                    <i class="fas fa-chevron-down text-gray-400 text-[10px] transition-transform" :class="profileOpen ? 'rotate-180' : ''"></i>
                                </button>
                                <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 scale-95 -translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0">
                                    <div v-show="profileOpen" @click.outside="profileOpen = false" class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                                        <!-- User Info Header -->
                                        <div class="p-4 bg-gradient-to-br from-gray-50 via-white to-gray-50 border-b border-gray-100">
                                            <div class="flex items-center gap-3">
                                                <img v-if="avatarUrl" :src="avatarUrl" :alt="user?.name" class="w-14 h-14 rounded-xl object-cover ring-2 ring-white shadow-lg">
                                                <div v-else class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex items-center justify-center text-white font-bold text-xl ring-2 ring-white shadow-lg">
                                                    {{ user?.name?.charAt(0) || 'U' }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-bold text-gray-900 truncate">{{ user?.name }}</p>
                                                    <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Menu Items -->
                                        <div class="p-2">
                                            <a :href="routes.profile" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-all duration-300 group">
                                                <div class="w-9 h-9 rounded-lg bg-gray-100 group-hover:bg-[#C8102E]/10 flex items-center justify-center transition-all duration-300"><i class="fas fa-user text-gray-500 group-hover:text-[#C8102E] transition-all duration-300"></i></div>
                                                <span>My Profile</span>
                                            </a>
                                            <a :href="routes.results" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-all duration-300 group">
                                                <div class="w-9 h-9 rounded-lg bg-gray-100 group-hover:bg-[#C8102E]/10 flex items-center justify-center transition-all duration-300"><i class="fas fa-history text-gray-500 group-hover:text-[#C8102E] transition-all duration-300"></i></div>
                                                <span>Test History</span>
                                            </a>
                                        </div>
                                        <!-- Logout -->
                                        <div class="p-2 border-t border-gray-100">
                                            <button @click="handleLogout" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl transition-all duration-300 group">
                                                <div class="w-9 h-9 rounded-lg bg-red-50 group-hover:bg-red-100 flex items-center justify-center transition-all duration-300"><i class="fas fa-sign-out-alt text-red-500"></i></div>
                                                <span>Logout</span>
                                            </button>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </template>

                        <!-- Mobile Menu Button -->
                        <button v-if="!isOffline" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-all duration-300">
                            <i class="fas text-lg" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0">
                <div v-if="!isOffline && mobileMenuOpen" class="md:hidden border-t border-gray-200 bg-white shadow-lg">
                    <div class="px-4 py-4 space-y-2">
                        <a :href="routes.dashboard" :class="['flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100', isRoute('student/dashboard') ? 'bg-[#C8102E]/5 text-[#C8102E]' : '']">
                            <i class="fas fa-home w-5"></i><span class="font-medium">Dashboard</span>
                        </a>
                        <a :href="routes.listening" :class="['flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100', isRoute('listening') ? 'bg-blue-50 text-blue-600' : '']">
                            <i class="fas fa-headphones w-5"></i><span class="font-medium">Listening</span>
                        </a>
                        <a :href="routes.reading" :class="['flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100', isRoute('reading') ? 'bg-emerald-50 text-emerald-600' : '']">
                            <i class="fas fa-book-reader w-5"></i><span class="font-medium">Reading</span>
                        </a>
                        <a :href="routes.writing" :class="['flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100', isRoute('writing') ? 'bg-violet-50 text-violet-600' : '']">
                            <i class="fas fa-pen-nib w-5"></i><span class="font-medium">Writing</span>
                        </a>
                        <a :href="routes.speaking" :class="['flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100', isRoute('speaking') ? 'bg-orange-50 text-orange-600' : '']">
                            <i class="fas fa-comment-dots w-5"></i><span class="font-medium">Speaking</span>
                        </a>
                        <a :href="routes.fullTest" :class="['flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100', isRoute('full-test') ? 'bg-[#C8102E]/5 text-[#C8102E]' : '']">
                            <i class="fas fa-layer-group w-5"></i><span class="font-medium">Full Test</span>
                        </a>
                        <a :href="routes.results" :class="['flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-100', isRoute('results') ? 'bg-[#C8102E]/5 text-[#C8102E]' : '']">
                            <i class="fas fa-chart-bar w-5"></i><span class="font-medium">Results</span>
                        </a>
                        <a :href="routes.aiTutor" :class="['flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-emerald-50', isRoute('ai-tutor') ? 'bg-emerald-50 text-emerald-600' : '']">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center"><i class="fas fa-robot text-emerald-600 text-sm"></i></div>
                            <span class="font-medium">AI Tutor</span>
                            <span class="ml-auto px-1.5 py-0.5 bg-emerald-500 text-white text-[9px] font-bold rounded uppercase">New</span>
                        </a>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Main Content -->
        <main class="min-h-screen">
            <slot></slot>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-gray-600 text-sm font-medium">{{ settings.copyright_text }}</p>
                    <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                        <a href="/privacy-policy" class="text-sm font-medium text-gray-600 hover:text-[#C8102E] transition-all duration-300">Privacy</a>
                        <a href="/terms-of-service" class="text-sm font-medium text-gray-600 hover:text-[#C8102E] transition-all duration-300">Terms</a>
                        <a href="/contact" class="text-sm font-medium text-gray-600 hover:text-[#C8102E] transition-all duration-300">Contact</a>
                        <a href="/help-center" class="text-sm font-medium text-gray-600 hover:text-[#C8102E] transition-all duration-300">Help</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Custom Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: #f1f1f1; }
::-webkit-scrollbar-thumb { background: #C8102E; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #A00E27; }
</style>
