{{-- resources/views/components/help-guide.blade.php --}}
<div id="help-modal" class="help-modal-overlay" style="display: none;">
    <div class="help-modal-container">
        <!-- Header -->
        <div class="help-modal-header">
            <div class="help-header-content">
                <svg class="help-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="help-modal-title">Test Guide</h2>
            </div>
            <button class="help-close-btn" onclick="HelpGuide.close()">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Tabs -->
        <div class="help-tabs-container">
            <button class="help-tab active" data-section="overview">Overview</button>
            <button class="help-tab" data-section="questions">Question Types</button>
            <button class="help-tab" data-section="navigation">Navigation</button>
            <button class="help-tab" data-section="tips">Tips & Tricks</button>
        </div>
        
        <!-- Dynamic Content Area -->
        <div class="help-content-area" id="help-content">
            <!-- Content will be loaded dynamically -->
        </div>
        
        <!-- Footer -->
        <div class="help-modal-footer">
            <div class="help-footer-left">
                <span class="help-version">ROX 1.0</span>
            </div>
            <div class="help-footer-right">
                <button class="help-btn-secondary" onclick="HelpGuide.showVideo()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Watch Tutorial
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Include CSS --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/help-guide.css') }}">
@endpush

{{-- Include JS --}}
@push('scripts')
<script src="{{ asset('js/help-guide.js') }}"></script>
<script>
    // Initialize with current test type
    document.addEventListener('DOMContentLoaded', function() {
        HelpGuide.init({
            testType: '{{ $testType ?? "reading" }}',
            language: '{{ app()->getLocale() }}'
        });
    });
</script>
@endpush