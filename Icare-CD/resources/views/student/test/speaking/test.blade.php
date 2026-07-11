{{-- resources/views/student/test/speaking/test.blade.php --}}
{{-- Updated: All Problems Fixed for Main Exam --}}
<x-test-layout>
    <x-slot:title>IELTS Speaking Test</x-slot>

    <x-slot:meta>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </x-slot:meta>

    <x-test-loading-screen />

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body, html {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            height: 100vh;
            overflow: hidden;
        }

        /* ==================== ANTI-CHEAT VIDEO PROTECTION ==================== */
        video {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            pointer-events: auto;
        }

        video::-webkit-media-controls-enclosure {
            display: none !important;
        }

        video::-webkit-media-controls {
            display: none !important;
        }

        /* ==================== RECORDING STATE STYLES ==================== */
        /* Full screen recording indicator with progress border */
        .recording-active {
            position: relative;
        }

        /* Single progress bar at bottom - like video player */
        .recording-progress-bar {
            position: fixed;
            bottom: 70px; /* Above bottom nav */
            left: 0;
            height: 6px;
            width: 100%;
            background: #374151;
            z-index: 9999;
            pointer-events: none;
        }

        .recording-progress-fill {
            height: 100%;
            width: 100%;
            background: #22c55e;
            transition: width 0.5s linear, background 0.3s ease;
            transform-origin: left;
        }

        /* Top Bar Recording Indicator */
        .top-bar-recording {
            display: none;
            align-items: center;
            gap: 8px;
            background: #22c55e;
            padding: 4px 12px;
            border-radius: 20px;
            transition: background 0.5s ease;
        }

        .top-bar-recording.active {
            display: flex;
        }

        .top-bar-recording-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .top-bar-recording-text {
            color: white;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .top-bar-recording-time {
            color: white;
            font-size: 13px;
            font-weight: 700;
            font-feature-settings: 'tnum';
        }

        /* ==================== CUE CARD PHASE STYLES ==================== */
        /* Preparation Phase */
        .cue-card-container.prep-phase {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(16, 185, 129, 0.3);
            box-shadow: 0 4px 24px rgba(16, 185, 129, 0.12);
        }

        .cue-card-container.prep-phase .phase-indicator {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .cue-card-container.prep-phase .prep-timer {
            background: #10b981;
        }

        /* Recording Phase */
        .cue-card-container.recording-phase {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(239, 68, 68, 0.3);
            box-shadow: 0 4px 24px rgba(239, 68, 68, 0.15);
        }

        .cue-card-container.recording-phase .phase-indicator {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .cue-card-container.recording-phase .prep-timer {
            background: #ef4444;
        }

        .phase-indicator {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            padding: 8px 24px;
            border-radius: 24px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            white-space: nowrap;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
        }




        /* ==================== NEXT QUESTION CONFIRM MODAL ==================== */
        .confirm-next-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10001;
        }

        .confirm-next-modal.active {
            display: flex;
        }

        .confirm-next-content {
            background: #1f1f1f;
            padding: 28px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            border: 1px solid #374151;
            animation: modal-pop 0.2s ease-out;
        }

        @keyframes modal-pop {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .confirm-next-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 16px;
            color: #9ca3af;
        }

        .confirm-next-title {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .confirm-next-message {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .confirm-next-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .confirm-next-btn {
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .confirm-next-btn.primary {
            background: #374151;
            color: white;
        }

        .confirm-next-btn.primary:hover {
            background: #4b5563;
        }

        .confirm-next-btn.secondary {
            background: #111111;
            color: #e5e7eb;
            border: 1px solid #374151;
        }

        .confirm-next-btn.secondary:hover {
            background: #1f1f1f;
        }

        /* ==================== PART COMPLETE MODAL (ENHANCED) ==================== */
        .part-modal-content {
            background: #1f1f1f;
            padding: 32px;
            border-radius: 12px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            border: 1px solid #374151;
        }

        .part-modal-title {
            font-size: 22px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .part-modal-subtitle {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 8px;
        }

        .part-modal-next-info {
            background: #111111;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 14px;
            color: #e5e7eb;
            border: 1px solid #374151;
        }

        .part-modal-countdown {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .part-modal-countdown span {
            font-weight: 600;
            color: #ffffff;
            font-size: 18px;
        }

        .part-modal-btn {
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background: #374151;
            color: white;
            transition: all 0.2s;
        }

        .part-modal-btn:hover {
            background: #4b5563;
        }

        .part-modal-btn:disabled {
            background: #1f1f1f;
            color: #6b7280;
            cursor: not-allowed;
        }

        /* ==================== REVIEW SCREEN ==================== */
        .review-modal-content {
            background: #1f1f1f;
            padding: 28px;
            border-radius: 12px;
            border: 1px solid #374151;
            max-width: 500px;
            width: 95%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .review-title {
            font-size: 20px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 8px;
            text-align: center;
        }

        .review-subtitle {
            font-size: 14px;
            color: #9ca3af;
            text-align: center;
            margin-bottom: 20px;
        }

        .review-summary {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 20px;
            padding: 16px;
            background: #111111;
            border-radius: 10px;
            border: 1px solid #374151;
        }

        .review-stat {
            text-align: center;
        }

        .review-stat-number {
            font-size: 28px;
            font-weight: 600;
        }

        .review-stat-number.recorded {
            color: #10b981;
        }

        .review-stat-number.not-recorded {
            color: #f87171;
        }

        .review-stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }


        .review-buttons {
            display: flex;
            gap: 12px;
        }

        .review-btn {
            flex: 1;
            padding: 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .review-btn.submit {
            background: #374151;
            color: white;
        }

        .review-btn.submit:hover {
            background: #4b5563;
        }

        .review-btn.cancel {
            background: #111111;
            color: #e5e7eb;
            border: 1px solid #374151;
        }

        .review-btn.cancel:hover {
            background: #1f1f1f;
        }

        /* ==================== EXISTING STYLES ==================== */
        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: #1a1a1a;
            color: white;
            height: 48px;
            position: relative;
            z-index: 100;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .user-info svg { width: 16px; height: 16px; }

        .timer-wrapper {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .top-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-controls button {
            padding: 5px 12px;
            background: #e5e5e5;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            color: #333;
        }

        /* Main Content */
        .main-content {
            height: calc(100vh - 48px - 56px);
            overflow: hidden;
        }

        .main-content form {
            height: 100%;
        }

        /* Question Card */
        .question-card {
            display: none;
            height: 100%;
            flex-direction: column;
        }

        .question-card.active { display: flex; }

        /* Main Layout */
        .main-layout {
            flex: 1;
            display: flex;
            padding: 16px 20px;
            gap: 20px;
            overflow: hidden;
        }

        /* Left Sidebar */
        .left-sidebar {
            width: 200px;
            flex-shrink: 0;
            padding-top: 8px;
        }

        .part-title {
            font-size: 18px;
            font-weight: 600;
            color: #111;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .question-number {
            font-size: 15px;
            color: #666;
        }

        /* Center Content */
        .center-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            align-self: stretch;
        }

        /* Avatar Container */
        .avatar-container {
            position: relative;
            max-width: 580px;
            width: 100%;
        }

        .avatar-container video {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 8px;
            object-fit: cover;
            background: #f5f5f5;
        }

        .avatar-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 16px 12px 10px;
            border-radius: 0 0 6px 6px;
        }

        .avatar-badge-text {
            background: #1a1a1a;
            color: white;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Right Sidebar */
        .right-sidebar {
            width: 240px;
            flex-shrink: 0;
        }

        .pip-avatar {
            position: relative;
            border-radius: 6px;
            overflow: hidden;
        }

        .pip-avatar video {
            width: 100%;
            height: auto;
            display: block;
        }

        .pip-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 12px 10px 8px;
        }

        .pip-badge-text {
            background: #1a1a1a;
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        /* Cue Card */
        .cue-card-container {
            background: white;
            border-radius: 20px;
            padding: 40px 36px 36px;
            max-width: 560px;
            width: 100%;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }

        .cue-card-label {
            font-size: 13px;
            color: #9ca3af;
            text-align: center;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .cue-card-topic {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 24px;
            text-align: center;
            line-height: 1.5;
        }

        .cue-card-points {
            list-style: none;
            padding: 0;
            margin: 0;
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px 20px;
        }

        .cue-card-points li {
            padding: 10px 0 10px 28px;
            position: relative;
            font-size: 15px;
            color: #374151;
            line-height: 1.5;
            border-bottom: 1px solid #e5e7eb;
        }

        .cue-card-points li:last-child {
            border-bottom: none;
        }

        .cue-card-points li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 50%;
        }

        .prep-timer {
            background: #1f2937;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            position: absolute;
            top: 16px;
            right: 16px;
            font-feature-settings: 'tnum';
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .start-speaking-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 24px;
            align-self: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .start-speaking-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        /* Recording Box in Bottom Nav */
        .recording-box {
            flex: 1;
            background: #e8e8e8;
            border-radius: 6px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0.4;
            transition: all 0.3s ease;
            max-width: 400px;
        }

        .recording-box.active {
            opacity: 1;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .rec-indicator {
            width: 10px;
            height: 10px;
            background: #999;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .recording-box.active .rec-indicator {
            background: #ef4444;
            animation: pulse-rec 1s infinite;
        }

        @keyframes pulse-rec {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
        }

        .recording-label {
            font-size: 13px;
            color: #666;
            font-weight: 500;
        }

        .recording-box.active .recording-label {
            color: #ef4444;
            font-weight: 600;
        }

        .wave-visualizer {
            display: flex;
            align-items: center;
            gap: 2px;
            height: 20px;
            flex: 1;
        }

        .wave-bar {
            width: 3px;
            background: #ccc;
            border-radius: 2px;
            transition: height 0.05s ease;
        }

        .recording-box.active .wave-bar {
            background: #ef4444;
        }

        .recording-time {
            font-size: 14px;
            font-weight: 700;
            color: #333;
            font-feature-settings: 'tnum';
            min-width: 45px;
        }

        .recording-box.active .recording-time {
            color: #111;
        }

        .next-btn {
            background: #374151;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .next-btn:hover { background: #1f2937; }
        .next-btn:disabled { opacity: 0.5; cursor: not-allowed; background: #9ca3af; }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f5f5f5;
            border-top: 1px solid #e0e0e0;
            padding: 10px 20px;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bottom-nav-inner {
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 700px;
            width: 100%;
        }

        .submit-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .submit-btn:hover { background: #059669; }

        /* Countdown Overlay */
        .countdown-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            z-index: 10;
        }

        .countdown-text {
            font-size: 16px;
            color: #9ca3af;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .countdown-number {
            font-size: 72px;
            font-weight: 800;
            color: white;
            animation: countPop 0.3s ease-out;
            text-shadow: 0 0 30px rgba(255,255,255,0.3);
        }

        @keyframes countPop {
            0% { transform: scale(0.5); opacity: 0; }
            70% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Text Fallback */
        .text-question-box {
            background: white;
            border-radius: 8px;
            padding: 32px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .text-question-content {
            font-size: 18px;
            line-height: 1.5;
            color: #111;
            margin-bottom: 20px;
        }

        .read-timer {
            width: 70px;
            height: 70px;
            margin: 0 auto;
            position: relative;
        }

        .read-timer svg { transform: rotate(-90deg); }

        .timer-circle-bg {
            fill: none;
            stroke: #e5e5e5;
            stroke-width: 5;
        }

        .timer-circle-progress {
            fill: none;
            stroke: #333;
            stroke-width: 5;
            stroke-linecap: round;
            transition: stroke-dashoffset 0.5s ease;
        }

        .timer-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20px;
            font-weight: 700;
            color: #111;
        }

        /* Modal Base */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: #1f1f1f;
            padding: 24px;
            border-radius: 12px;
            max-width: 360px;
            border: 1px solid #374151;
            width: 90%;
            text-align: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #ffffff;
        }

        .modal-message {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .modal-btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }

        .modal-btn.primary { background: #374151; color: white; }
        .modal-btn.primary:hover { background: #4b5563; }
        .modal-btn.secondary { background: #111111; color: #e5e7eb; border: 1px solid #374151; }
        .modal-btn.secondary:hover { background: #1f1f1f; }

        .hidden { display: none !important; }

        /* Responsive */
        @media (max-width: 1024px) {
            .left-sidebar { width: 160px; }
            .right-sidebar { width: 180px; }
            .part-title { font-size: 16px; }
            .question-number { font-size: 14px; }
            .avatar-container { max-width: 400px; }
        }

        @media (max-width: 768px) {
            .main-layout {
                flex-direction: column;
                padding: 12px;
                gap: 12px;
            }

            .left-sidebar {
                width: 100%;
                padding-top: 0;
                display: flex;
                align-items: baseline;
                gap: 8px;
            }

            .part-title { font-size: 15px; margin-bottom: 0; }
            .question-number { font-size: 13px; }

            .right-sidebar {
                width: 100%;
                order: -1;
            }

            .pip-avatar {
                max-width: 160px;
                margin: 0 auto;
            }

            .avatar-container { max-width: 100%; }

            .cue-card-container {
                padding: 32px 24px 24px;
                min-height: auto;
                border-radius: 16px;
            }

            .cue-card-topic { font-size: 16px; }
            .cue-card-points { padding: 12px 16px; }
            .cue-card-points li { font-size: 14px; padding: 8px 0 8px 24px; }

            .bottom-nav {
                padding: 8px 12px;
            }

            .bottom-nav-inner {
                flex-wrap: wrap;
                gap: 8px;
                justify-content: center;
            }

            .recording-box {
                max-width: none;
                flex: 1;
                min-width: 200px;
            }

            .main-content {
                height: calc(100vh - 48px - 70px);
            }

            .get-ready-text {
                font-size: 40px;
                letter-spacing: 4px;
            }
        }

        @media (max-width: 480px) {
            .top-bar { padding: 8px 12px; }
            .user-info { font-size: 11px; }
            .top-controls button { padding: 4px 8px; font-size: 11px; }
            .bottom-nav { padding: 6px 12px; }
        }
    </style>


    <!-- Recording Progress Bar -->
    <div id="recording-progress-bar" class="recording-progress-bar hidden">
        <div class="recording-progress-fill" id="recording-progress-fill"></div>
    </div>


    <!-- Top Bar -->
    <div class="top-bar">
        <div class="user-info">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ auth()->user()->name }} - BI {{ str_pad(auth()->id(), 6, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="timer-wrapper">
            <x-test-timer
                :attempt="$attempt"
                auto-submit-form-id="speaking-form"
                position="integrated"
                :warning-time="300"
                :danger-time="120"
            />
        </div>

        <div class="top-controls">
            <!-- Recording Indicator in Top Bar -->
            <div id="top-bar-recording" class="top-bar-recording">
                <div class="top-bar-recording-dot"></div>
                <span class="top-bar-recording-text">REC</span>
                <span id="top-bar-rec-time" class="top-bar-recording-time">00:00</span>
            </div>
            <button id="help-button">Help ?</button>
            <button>Hide</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <form id="speaking-form" action="{{ route('student.speaking.submit', $attempt) }}" method="POST">
            @csrf

            @foreach ($testSet->questions->sortBy('order_number') as $index => $question)
                <div class="question-card {{ $index === 0 ? 'active' : '' }}"
                     id="card-{{ $question->id }}"
                     data-question-id="{{ $question->id }}"
                     data-question-index="{{ $index }}"
                     data-part="{{ $question->part_number }}">

                    @if($question->part_number == 2)
                        {{-- PART 2: CUE CARD --}}
                        <div class="main-layout">
                            <div class="left-sidebar">
                                <div class="part-title">Part 2: Cue Card</div>
                            </div>

                            <div class="center-content">
                                <div class="cue-card-container" id="cue-card-{{ $question->id }}">
                                    <!-- Phase Indicator -->
                                    <div class="phase-indicator" id="phase-indicator-{{ $question->id }}">PREPARATION TIME</div>

                                    <span class="prep-timer" id="prep-timer-{{ $question->id }}">01:00</span>

                                    @if($question->form_structure && isset($question->form_structure['fields']))
                                        <div class="cue-card-topic">{!! strip_tags($question->content) !!}</div>
                                        <ul class="cue-card-points">
                                            @foreach($question->form_structure['fields'] as $point)
                                                <li>{{ $point['label'] }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="cue-card-label">Cue Card</div>
                                        <div class="cue-card-topic">{!! strip_tags($question->content) !!}</div>
                                    @endif

                                    <button type="button" class="start-speaking-btn hidden" id="start-btn-{{ $question->id }}" onclick="startRecordingPhase({{ $question->id }})">
                                        Start Speaking Now
                                    </button>
                                </div>
                            </div>

                            <div class="right-sidebar">
                                @if($question->hasAvatarVideo())
                                    <div class="pip-avatar">
                                        <video id="pip-video-{{ $question->id }}" playsinline preload="auto"
                                               controlslist="nodownload nofullscreen noremoteplayback"
                                               disablePictureInPicture
                                               oncontextmenu="return false;">
                                            <source src="{{ $question->avatar_video_url }}" type="video/mp4">
                                        </video>
                                        <div class="pip-badge">
                                            <span class="pip-badge-text">CD IELTS EXAMINER</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- PART 1 & 3 --}}
                        <div class="main-layout">
                            <div class="left-sidebar">
                                <div class="part-title">Part {{ $question->part_number }}: {{ $question->part_number == 1 ? 'Question & Answers' : 'Discussion' }}</div>
                                <div class="question-number">Question {{ $index + 1 }}</div>
                            </div>

                            <div class="center-content">
                                @if($question->hasAvatarVideo())
                                    <div class="avatar-container">
                                        <video id="avatar-video-{{ $question->id }}" playsinline preload="auto"
                                               controlslist="nodownload nofullscreen noremoteplayback"
                                               disablePictureInPicture
                                               oncontextmenu="return false;">
                                            <source src="{{ $question->avatar_video_url }}" type="video/mp4">
                                        </video>
                                        <div class="avatar-badge">
                                            <span class="avatar-badge-text">CD IELTS EXAMINER</span>
                                        </div>

                                        <div class="countdown-overlay hidden" id="countdown-{{ $question->id }}">
                                            <div class="countdown-text">Recording starts in</div>
                                            <div class="countdown-number" id="countdown-num-{{ $question->id }}">3</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-question-box" id="text-section-{{ $question->id }}">
                                        <div class="text-question-content">{!! $question->content !!}</div>
                                        <div class="read-timer" id="read-timer-{{ $question->id }}">
                                            <svg width="70" height="70">
                                                <circle cx="35" cy="35" r="30" class="timer-circle-bg"></circle>
                                                <circle cx="35" cy="35" r="30" class="timer-circle-progress"
                                                        id="timer-progress-{{ $question->id }}"
                                                        stroke-dasharray="188.5" stroke-dashoffset="0"></circle>
                                            </svg>
                                            <div class="timer-text" id="timer-text-{{ $question->id }}">{{ $question->read_time ?? 5 }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="right-sidebar"></div>
                        </div>
                    @endif

                </div>
            @endforeach

            <button type="submit" id="submit-button" class="hidden">Submit</button>
        </form>
    </div>

    <!-- Bottom Navigation with Recording -->
    <div class="bottom-nav">
        <div class="bottom-nav-inner">
            <!-- Recording Box -->
            <div class="recording-box" id="global-recording-box">
                <div class="rec-indicator"></div>
                <span class="recording-label">Recording</span>
                <div class="wave-visualizer" id="global-wave-container">
                    @for($i = 0; $i < 30; $i++)
                        <div class="wave-bar" style="height: 3px;"></div>
                    @endfor
                </div>
                <span class="recording-time" id="global-recording-time">00:00</span>
            </div>

            <!-- Next Button -->
            <button type="button" class="next-btn" id="global-next-btn" onclick="confirmNextQuestion()" disabled>
                Next Question
            </button>

            <!-- Submit Button -->
            <button type="button" id="submit-test-btn" class="submit-btn">Submit Test</button>
        </div>
    </div>

    <!-- Confirm Next Question Modal -->
    <div id="confirm-next-modal" class="confirm-next-modal">
        <div class="confirm-next-content">
            <svg class="confirm-next-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="confirm-next-title">Move to Next Question?</div>
            <div class="confirm-next-message">
                Your recording will be saved and you <strong>cannot re-record</strong> this answer.<br><br>
                Are you sure you want to continue?
            </div>
            <div class="confirm-next-buttons">
                <button class="confirm-next-btn secondary" onclick="cancelNextQuestion()">Keep Recording</button>
                <button class="confirm-next-btn primary" onclick="proceedToNext()">Save & Continue</button>
            </div>
        </div>
    </div>

    <!-- Part Complete Modal (Enhanced) -->
    <div id="part-complete-modal" class="modal-overlay" style="display: none;">
        <div class="part-modal-content">
            <div class="part-modal-title">Part <span id="completed-part">1</span> Complete!</div>
            <div class="part-modal-subtitle">Great job! Take a moment to prepare.</div>
            <div class="part-modal-next-info">
                <strong>Next:</strong> Part <span id="next-part-number">2</span> - <span id="next-part-name">Cue Card</span>
            </div>
            <div class="part-modal-countdown">
                Continue available in <span id="part-countdown">5</span> seconds
            </div>
            <button class="part-modal-btn" id="continue-part-btn" onclick="continueToPart()" disabled>
                Please wait...
            </button>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="review-modal" class="modal-overlay" style="display: none;">
        <div class="review-modal-content">
            <div class="review-title">Review Your Answers</div>
            <div class="review-subtitle">Please review before submitting</div>

            <div class="review-summary">
                <div class="review-stat">
                    <div class="review-stat-number recorded" id="review-recorded-count">0</div>
                    <div class="review-stat-label">Recorded</div>
                </div>
                <div class="review-stat">
                    <div class="review-stat-number not-recorded" id="review-not-recorded-count">0</div>
                    <div class="review-stat-label">Not Recorded</div>
                </div>
            </div>

            <div class="review-buttons">
                <button class="review-btn cancel" onclick="closeReviewModal()">Cancel</button>
                <button class="review-btn submit" onclick="finalSubmit()">Submit Test</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        history.pushState(null, null, location.href);
        window.addEventListener('popstate', function(event) {
            history.pushState(null, null, location.href);
            showReviewModal();
        });
    })();
    </script>

    @php
        $questionsData = $testSet->questions->sortBy('order_number')->values()->map(function($q, $idx) {
            return [
                'id' => $q->id,
                'index' => $idx,
                'part_number' => $q->part_number,
                'content' => strip_tags(substr($q->content, 0, 80)) . '...',
                'read_time' => $q->read_time ?? ($q->part_number == 2 ? 60 : 5),
                'max_response_time' => $q->max_response_time ?? ($q->part_number == 2 ? 120 : 45),
                'pause_before_record' => $q->pause_before_record ?? 3,
                'has_avatar' => $q->hasAvatarVideo(),
            ];
        });
    @endphp

    <script>
    const questions = @json($questionsData);
    const attemptId = {{ $attempt->id }};
    const totalQuestions = {{ $testSet->questions->count() }};

    let currentIndex = 0;
    let recordingsCompleted = 0;
    let recordingDurations = {}; // Track duration per question
    let mediaRecorder = null;
    let audioChunks = [];
    let timers = {};
    let recordingStartTime = null;
    let audioContext = null;
    let analyser = null;
    let currentRecordingQuestionId = null;
    let testStarted = false;
    let isRecording = false;
    let currentStream = null;

    // Storage key for this attempt
    const storageKey = `speaking_progress_${attemptId}`;

    // Part names for display
    const partNames = {
        1: 'Question & Answers',
        2: 'Cue Card',
        3: 'Discussion'
    };

    // ==================== RECORDING STATE MANAGEMENT ====================
    function setRecordingState(active) {
        const mainContent = document.getElementById('main-content');
        const topBarRec = document.getElementById('top-bar-recording');
        const progressBar = document.getElementById('recording-progress-bar');

        if (active) {
            mainContent.classList.add('recording-active');
            topBarRec.classList.add('active');
            progressBar.classList.remove('hidden');
            // Reset progress to full
            updateRecordingProgress(1.0);
            topBarRec.style.background = '#22c55e';
        } else {
            mainContent.classList.remove('recording-active');
            topBarRec.classList.remove('active');
            progressBar.classList.add('hidden');
            topBarRec.style.background = '';
        }
    }

    // ==================== RECORDING PROGRESS BAR ====================
    function updateRecordingProgress(progress) {
        // progress: 1.0 = full time, 0.0 = no time left
        const progressFill = document.getElementById('recording-progress-fill');
        const topBarRec = document.getElementById('top-bar-recording');

        // Update width (smooth like video player)
        progressFill.style.width = (progress * 100) + '%';

        // Color: Green → Yellow → Red based on progress
        let color;
        if (progress > 0.33) {
            color = '#22c55e'; // Green
        } else if (progress > 0.15) {
            color = '#eab308'; // Yellow
        } else {
            color = '#ef4444'; // Red
        }

        progressFill.style.background = color;

        // Update top bar recording indicator color
        if (topBarRec) {
            topBarRec.style.background = color;
        }
    }



    // ==================== CUE CARD PHASE STYLES ====================
    function setCueCardPhase(questionId, phase) {
        const cueCard = document.getElementById(`cue-card-${questionId}`);
        const phaseIndicator = document.getElementById(`phase-indicator-${questionId}`);

        if (!cueCard || !phaseIndicator) return;

        if (phase === 'prep') {
            cueCard.classList.add('prep-phase');
            cueCard.classList.remove('recording-phase');
            phaseIndicator.textContent = 'PREPARATION TIME';
        } else if (phase === 'recording') {
            cueCard.classList.remove('prep-phase');
            cueCard.classList.add('recording-phase');
            phaseIndicator.textContent = 'SPEAKING NOW';
        }
    }

    // ==================== PROGRESS MANAGEMENT ====================
    function restoreProgress() {
        try {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                const data = JSON.parse(saved);
                currentIndex = data.currentIndex || 0;
                recordingsCompleted = data.recordingsCompleted || 0;
                recordingDurations = data.recordingDurations || {};

                document.querySelectorAll('.question-card').forEach((card, idx) => {
                    card.classList.remove('active');
                    if (idx === currentIndex) card.classList.add('active');
                    if (idx < currentIndex) card.classList.add('completed');
                });
            }
        } catch (e) {
            console.log('Could not restore progress:', e);
        }
    }

    function saveProgress() {
        try {
            localStorage.setItem(storageKey, JSON.stringify({
                currentIndex: currentIndex,
                recordingsCompleted: recordingsCompleted,
                recordingDurations: recordingDurations,
                timestamp: Date.now()
            }));
        } catch (e) {
            console.log('Could not save progress:', e);
        }
    }

    function clearProgress() {
        try {
            localStorage.removeItem(storageKey);
        } catch (e) {}
    }

    // ==================== INITIALIZATION ====================
    document.addEventListener('DOMContentLoaded', function() {
        restoreProgress();

        const loadingScreen = document.querySelector('.test-loading-screen, [data-loading-screen]');

        if (loadingScreen) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' &&
                        (loadingScreen.style.display === 'none' || loadingScreen.classList.contains('hidden'))) {
                        observer.disconnect();
                        startTest();
                    }
                });
            });

            observer.observe(loadingScreen, { attributes: true, attributeFilter: ['style', 'class'] });

            setTimeout(() => {
                if (!testStarted) {
                    observer.disconnect();
                    startTest();
                }
            }, 3000);
        } else {
            setTimeout(startTest, 500);
        }

        document.getElementById('submit-test-btn').addEventListener('click', showReviewModal);
    });

    function startTest() {
        if (testStarted) return;
        testStarted = true;
        initializeQuestion(currentIndex);
    }

    function initializeQuestion(index) {
        if (index >= questions.length) {
            showReviewModal();
            return;
        }

        const question = questions[index];
        const questionId = question.id;

        // Reset states
        setRecordingState(false);

        if (question.part_number === 2) {
            setCueCardPhase(questionId, 'prep');
            if (question.has_avatar) {
                playPIPVideo(questionId);
            } else {
                startPrepTimer(questionId);
            }
        } else {
            if (question.has_avatar) {
                playAvatarVideo(questionId);
            } else {
                startReadingTimer(questionId);
            }
        }
    }

    function playAvatarVideo(questionId) {
        const video = document.getElementById(`avatar-video-${questionId}`);
        if (!video) {
            showGetReadyAndCountdown(questionId);
            return;
        }

        video.play().catch(err => {
            console.error('Video play failed:', err);
            showGetReadyAndCountdown(questionId);
        });

        video.onended = function() {
            setTimeout(() => {
                showGetReadyAndCountdown(questionId);
            }, 500);
        };
    }

    function playPIPVideo(questionId) {
        const video = document.getElementById(`pip-video-${questionId}`);
        if (video) {
            video.play().catch(err => console.log('PIP video autoplay blocked'));
            video.onended = function() {
                startPrepTimer(questionId);
            };
        } else {
            startPrepTimer(questionId);
        }
    }

    function showGetReadyAndCountdown(questionId) {
        startCountdown(questionId);
    }

    function startCountdown(questionId) {
        const question = questions[currentIndex];
        const countdown = document.getElementById(`countdown-${questionId}`);
        const countNum = document.getElementById(`countdown-num-${questionId}`);

        if (!countdown || !countNum) {
            startRecordingPhase(questionId);
            return;
        }

        countdown.classList.remove('hidden');
        let count = question.pause_before_record || 3;
        countNum.textContent = count;

        const interval = setInterval(() => {
            count--;
            if (count > 0) {
                countNum.textContent = count;
                countNum.style.animation = 'none';
                countNum.offsetHeight;
                countNum.style.animation = 'countPop 0.3s ease-out';
                playBeep(400 + (3 - count) * 100);
            } else {
                clearInterval(interval);
                countdown.classList.add('hidden');
                startRecordingPhase(questionId);
            }
        }, 1000);

        timers.countdown = interval;
    }

    function startPrepTimer(questionId) {
        const question = questions[currentIndex];
        let timeLeft = question.read_time || 60;
        const timerEl = document.getElementById(`prep-timer-${questionId}`);
        const startBtn = document.getElementById(`start-btn-${questionId}`);

        setCueCardPhase(questionId, 'prep');

        const updateDisplay = () => {
            const mins = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            if (timerEl) timerEl.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        };

        updateDisplay();

        const interval = setInterval(() => {
            timeLeft--;
            updateDisplay();

            if (timeLeft <= 0) {
                clearInterval(interval);
                showGetReadyAndStartRecording(questionId);
            } else if (timeLeft <= 10 && startBtn) {
                startBtn.classList.remove('hidden');
            }
        }, 1000);

        timers.prep = interval;

        setTimeout(() => {
            if (startBtn) startBtn.classList.remove('hidden');
        }, Math.max(0, (question.read_time - 10)) * 1000);
    }

    function showGetReadyAndStartRecording(questionId) {
        startRecordingPhase(questionId);
    }

    function startReadingTimer(questionId) {
        const question = questions[currentIndex];
        let timeLeft = question.read_time || 5;
        const total = timeLeft;
        const circumference = 2 * Math.PI * 30;

        const interval = setInterval(() => {
            timeLeft--;

            const progress = timeLeft / total;
            const offset = circumference * (1 - progress);

            const progressEl = document.getElementById(`timer-progress-${questionId}`);
            const textEl = document.getElementById(`timer-text-${questionId}`);

            if (progressEl) progressEl.style.strokeDashoffset = offset;
            if (textEl) textEl.textContent = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(interval);
                showGetReadyAndStartRecording(questionId);
            }
        }, 1000);

        timers.reading = interval;
    }

    // ==================== RECORDING ====================
    window.startRecordingPhase = async function(questionId) {
        if (timers.prep) clearInterval(timers.prep);
        if (timers.reading) clearInterval(timers.reading);

        const question = questions[currentIndex];

        // Update cue card phase if Part 2
        if (question.part_number === 2) {
            setCueCardPhase(questionId, 'recording');
            const timerEl = document.getElementById(`prep-timer-${questionId}`);
            if (timerEl) timerEl.textContent = formatTime(question.max_response_time || 120);
        }

        const countdown = document.getElementById(`countdown-${questionId}`);
        if (countdown) countdown.classList.add('hidden');

        const startBtn = document.getElementById(`start-btn-${questionId}`);
        if (startBtn) startBtn.classList.add('hidden');

        // Activate recording UI
        const recordingBox = document.getElementById('global-recording-box');
        if (recordingBox) recordingBox.classList.add('active');

        const nextBtn = document.getElementById('global-next-btn');
        if (nextBtn) nextBtn.disabled = false;

        // Set full screen recording state
        setRecordingState(true);

        try {
            await startRecording(questionId);
        } catch (error) {
            console.error('Recording failed:', error);
            alert('Could not access microphone. Please check permissions.');
        }
    }

    async function startRecording(questionId) {
        currentRecordingQuestionId = questionId;
        isRecording = true;

        currentStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(currentStream);
        audioChunks = [];

        mediaRecorder.ondataavailable = (e) => audioChunks.push(e.data);

        mediaRecorder.onstop = async () => {
            isRecording = false;
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            await uploadRecording(currentRecordingQuestionId, audioBlob);
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }
        };

        mediaRecorder.start();
        recordingStartTime = Date.now();

        updateRecordingTimer();
        startWaveVisualizer(currentStream);

        const question = questions[currentIndex];
        const maxTime = question.max_response_time || 45;

        // Auto-move to next when time ends
        timers.maxRecord = setTimeout(() => {
            proceedToNext();
        }, maxTime * 1000);
    }

    function updateRecordingTimer() {
        const question = questions[currentIndex];
        const maxTime = question.max_response_time || 45;

        const update = () => {
            if (!recordingStartTime) return;
            const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
            const remaining = Math.max(0, maxTime - elapsed);
            const progress = remaining / maxTime;
            const mins = Math.floor(elapsed / 60);
            const secs = elapsed % 60;

            // Update progress bar
            updateRecordingProgress(progress);

            // Update bottom recording time
            const el = document.getElementById('global-recording-time');
            if (el) el.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

            // Update top bar recording time
            const topEl = document.getElementById('top-bar-rec-time');
            if (topEl) topEl.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

            // Update cue card timer if Part 2
            if (question.part_number === 2) {
                const prepTimer = document.getElementById(`prep-timer-${question.id}`);
                if (prepTimer) prepTimer.textContent = formatTime(remaining);
            }
        };

        update();
        timers.recordingTime = setInterval(update, 1000);
    }

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    function startWaveVisualizer(stream) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
        analyser = audioContext.createAnalyser();
        const source = audioContext.createMediaStreamSource(stream);
        source.connect(analyser);
        analyser.fftSize = 128;
        analyser.smoothingTimeConstant = 0.85;

        const dataArray = new Uint8Array(analyser.frequencyBinCount);
        const waveContainer = document.getElementById('global-wave-container');
        const bars = waveContainer ? waveContainer.querySelectorAll('.wave-bar') : [];

        const update = () => {
            if (!analyser) return;
            analyser.getByteFrequencyData(dataArray);


            bars.forEach((bar, i) => {
                const dataIndex = Math.floor(i * dataArray.length / bars.length);
                const value = dataArray[dataIndex] || 0;
                const height = Math.max(3, (value / 255) * 18);
                bar.style.height = height + 'px';
            });

            timers.volume = requestAnimationFrame(update);
        };

        update();
    }

    // ==================== NEXT QUESTION CONFIRMATION ====================
    window.confirmNextQuestion = function() {
        document.getElementById('confirm-next-modal').classList.add('active');
    }

    window.cancelNextQuestion = function() {
        document.getElementById('confirm-next-modal').classList.remove('active');
    }

    window.proceedToNext = async function() {
        document.getElementById('confirm-next-modal').classList.remove('active');

        const nextBtn = document.getElementById('global-next-btn');
        nextBtn.disabled = true;
        nextBtn.textContent = 'Saving...';

        // Calculate duration
        if (recordingStartTime) {
            const duration = Math.floor((Date.now() - recordingStartTime) / 1000);
            recordingDurations[questions[currentIndex].id] = duration;
        }

        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
        }

        clearAllTimers();
        setRecordingState(false);

        const questionId = questions[currentIndex].id;
        const card = document.getElementById(`card-${questionId}`);
        card.classList.add('completed');

        recordingsCompleted++;
        recordingStartTime = null;

        saveProgress();

        // Reset recording box
        const recordingBox = document.getElementById('global-recording-box');
        if (recordingBox) recordingBox.classList.remove('active');
        document.getElementById('global-recording-time').textContent = '00:00';
        document.getElementById('top-bar-rec-time').textContent = '00:00';
        nextBtn.textContent = 'Next Question';

        const currentPart = questions[currentIndex].part_number;
        const nextIndex = currentIndex + 1;

        // Wait a moment for upload to process
        await new Promise(resolve => setTimeout(resolve, 500));

        if (nextIndex < questions.length && questions[nextIndex].part_number !== currentPart) {
            showPartCompleteModal(currentPart, questions[nextIndex].part_number);
        } else {
            transitionToNext();
        }
    }

    // ==================== PART COMPLETE MODAL ====================
    function showPartCompleteModal(completedPart, nextPart) {
        document.getElementById('completed-part').textContent = completedPart;
        document.getElementById('next-part-number').textContent = nextPart;
        document.getElementById('next-part-name').textContent = partNames[nextPart] || 'Questions';

        const continueBtn = document.getElementById('continue-part-btn');
        const countdownEl = document.getElementById('part-countdown');
        continueBtn.disabled = true;
        continueBtn.textContent = 'Please wait...';

        document.getElementById('part-complete-modal').style.display = 'flex';

        let countdown = 5;
        countdownEl.textContent = countdown;

        const interval = setInterval(() => {
            countdown--;
            countdownEl.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(interval);
                continueBtn.disabled = false;
                continueBtn.textContent = 'Continue to Part ' + nextPart;
            }
        }, 1000);

        timers.partCountdown = interval;
    }

    window.continueToPart = function() {
        if (timers.partCountdown) clearInterval(timers.partCountdown);
        document.getElementById('part-complete-modal').style.display = 'none';
        transitionToNext();
    }

    function clearAllTimers() {
        Object.keys(timers).forEach(key => {
            if (key === 'volume') {
                cancelAnimationFrame(timers[key]);
            } else {
                clearInterval(timers[key]);
                clearTimeout(timers[key]);
            }
        });
        timers = {};

        if (audioContext) {
            audioContext.close();
            audioContext = null;
            analyser = null;
        }
    }

    function transitionToNext() {
        const currentCard = document.getElementById(`card-${questions[currentIndex].id}`);
        currentCard.classList.remove('active');

        currentIndex++;
        saveProgress();

        if (currentIndex < questions.length) {
            const nextCard = document.getElementById(`card-${questions[currentIndex].id}`);
            nextCard.classList.add('active');
            initializeQuestion(currentIndex);
        } else {
            showReviewModal();
        }
    }

    // ==================== REVIEW MODAL ====================
    function showReviewModal() {
        const notRecorded = totalQuestions - recordingsCompleted;

        document.getElementById('review-recorded-count').textContent = recordingsCompleted;
        document.getElementById('review-not-recorded-count').textContent = notRecorded;

        document.getElementById('review-modal').style.display = 'flex';
    }

    window.closeReviewModal = function() {
        document.getElementById('review-modal').style.display = 'none';
    }

    window.finalSubmit = function() {
        if (window.UniversalTimer) window.UniversalTimer.stop();
        clearProgress();
        document.getElementById('submit-button').click();
    }

    // ==================== UPLOAD ====================
    async function uploadRecording(questionId, audioBlob) {
        const formData = new FormData();
        formData.append('recording', audioBlob, 'recording.webm');

        try {
            const response = await fetch(
                `{{ route('student.speaking.record', ['attempt' => $attempt->id, 'question' => ':qid']) }}`.replace(':qid', questionId),
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                }
            );

            const data = await response.json();
            console.log('Recording saved:', questionId, data.success, data.storage || 'R2');

            if (!data.success) {
                console.error('Upload failed:', data.message);
                // Could implement retry logic here
            }
        } catch (error) {
            console.error('Upload error:', error);
            // Could implement retry logic here
        }
    }

    // ==================== ANTI-CHEAT PROTECTION ====================
    // Only enable in production (not in local/debug mode)
    @if(!config('app.debug'))
    // Block keyboard shortcuts for download/view source
    document.addEventListener('keydown', function(e) {
        // Block Ctrl+S (save), Ctrl+U (view source)
        if ((e.ctrlKey || e.metaKey) && ['s', 'u'].includes(e.key.toLowerCase())) {
            e.preventDefault();
            return false;
        }
        // Block F12 (dev tools)
        if (e.key === 'F12') {
            e.preventDefault();
            return false;
        }
    }, true);

    // Block right-click on entire page
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // Block drag on videos/images
    document.addEventListener('dragstart', function(e) {
        if (e.target.tagName === 'VIDEO' || e.target.tagName === 'IMG') {
            e.preventDefault();
            return false;
        }
    });
    @endif
    </script>
    @endpush
</x-test-layout>
