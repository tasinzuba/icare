{{-- Universal Test Loading Screen Component --}}
{{-- Shows "Your test will begin shortly / Please wait" on test start --}}

<div id="loading-screen" style="
    display: flex;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #000;
    z-index: 100000;
    justify-content: center;
    align-items: center;
    flex-direction: column;
">
    <div style="text-align: center; color: white;">
        <!-- Loading Spinner -->
        <div style="
            width: 60px;
            height: 60px;
            margin: 0 auto 30px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid #fff;
            border-radius: 50%;
            animation: loadingSpinner 1s linear infinite;
        "></div>
        <h2 style="font-size: 24px; font-weight: 500; margin-bottom: 12px; letter-spacing: 0.5px;">Your test will begin shortly</h2>
        <p style="font-size: 16px; color: #9ca3af;">Please wait</p>
    </div>
    <style>
        @keyframes loadingSpinner {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</div>

<script>
// Auto-hide loading screen after delay (default 2 seconds)
document.addEventListener('DOMContentLoaded', function() {
    const loadingScreen = document.getElementById('loading-screen');

    // Check if there's an audio overlay (listening/speaking tests)
    const audioOverlay = document.getElementById('audio-start-overlay');

    setTimeout(() => {
        if (loadingScreen) {
            loadingScreen.style.opacity = '0';
            loadingScreen.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 300);
        }

        // If audio overlay exists, show it after hiding loading screen
        if (audioOverlay) {
            audioOverlay.style.display = 'flex';
            audioOverlay.style.opacity = '0';
            audioOverlay.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                audioOverlay.style.opacity = '1';
            }, 50);
        }
    }, 2000);
});
</script>
