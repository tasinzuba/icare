<!-- Guest Header Component -->
<header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-lg border-b border-gray-200 shadow-sm">
    <nav class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('welcome') }}" class="flex items-center">
                @if($websiteSettings->site_logo)
                    <img src="{{ $websiteSettings->logo_url }}" alt="{{ $websiteSettings->site_title }}" class="h-12 w-auto">
                @else
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-lg">
                        <span class="text-white font-bold text-xl">{{ substr($websiteSettings->site_title, 0, 1) }}</span>
                    </div>
                @endif
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-red-600 transition-colors font-medium">Mock Test</a>
                    @if(auth()->user()->isOfflineStudent())
                        <a href="{{ route('student.results') }}" class="text-gray-700 hover:text-red-600 transition-colors font-medium">Result</a>
                    @endif
                @else
                    <a href="{{ route('welcome') }}" class="text-gray-700 hover:text-red-600 transition-colors font-medium">Home</a>
                    <a href="{{ route('welcome') }}#tests" class="text-gray-700 hover:text-red-600 transition-colors font-medium">Mock Test</a>
                    <a href="{{ route('score-calculator') }}" class="text-gray-700 hover:text-red-600 transition-colors font-medium">Score Calculator</a>
                @endauth
            </div>
            
            <!-- Auth Buttons -->
            <div class="hidden md:flex items-center space-x-3">
                @guest
                    <a href="{{ route('offline.login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-white bg-gradient-to-r from-gray-800 to-gray-900 rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all font-medium">
                        <i class="fas fa-graduation-cap text-sm"></i>
                        Offline Login
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all font-medium">
                        Dashboard
                    </a>
                @endguest
            </div>
            
            <!-- Mobile Menu Button -->
            <button 
                onclick="toggleMobileMenu()"
                class="md:hidden text-gray-700 p-2 rounded-lg hover:bg-white/50 transition-colors"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden mt-4 py-4 border-t border-white/20">
            <div class="flex flex-col space-y-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-red-600 transition-colors font-medium py-2">Mock Test</a>
                    @if(auth()->user()->isOfflineStudent())
                        <a href="{{ route('student.results') }}" class="text-gray-700 hover:text-red-600 transition-colors font-medium py-2">Result</a>
                    @endif
                @else
                    <a href="{{ route('welcome') }}" class="text-gray-700 hover:text-red-600 transition-colors font-medium py-2">Home</a>
                    <a href="{{ route('welcome') }}#tests" class="text-gray-700 hover:text-red-600 transition-colors font-medium py-2">Mock Test</a>
                    <a href="{{ route('score-calculator') }}" class="text-gray-700 hover:text-red-600 transition-colors font-medium py-2">Score Calculator</a>
                @endauth
                
                <div class="pt-4 space-y-3 border-t border-white/20">
                    @guest
                        <a href="{{ route('offline.login') }}" class="inline-flex items-center justify-center gap-2 w-full px-5 py-2.5 text-center text-white bg-gradient-to-r from-gray-800 to-gray-900 rounded-lg hover:shadow-lg transition-all font-medium">
                            <i class="fas fa-graduation-cap text-sm"></i>
                            Offline Login
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="block w-full px-5 py-2.5 text-center bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:shadow-lg transition-all font-medium">
                            Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
    }
</script>

<style>
    /* Additional glass effect styles */
    header {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
</style>
