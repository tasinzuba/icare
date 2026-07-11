{{-- resources/views/student/test/listening/test.blade.php --}}
<x-test-layout>
    <x-slot:title>IELTS Listening Test</x-slot>
    
    <x-slot:meta>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <link rel="stylesheet" href="{{ asset('css/listening-test-fix.css') }}?v={{ time() }}">
    </x-slot:meta>
    
    <style>
        /* IELTS Listening Test - Minimal CSS */
        
        /* ========== BASE STYLES ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body, html {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            height: 100vh;
            overflow: hidden;
        }
        
        /* ========== HEADER STYLES ========== */
        .ielts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .ielts-header-left {
            display: flex;
            align-items: center;
        }
        
        .user-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #1a1a1a;
            color: white;
            height: 50px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        /* Timer Center Wrapper */
        .timer-center-wrapper {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .user-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        /* ========== MAIN CONTAINER ========== */
        .main-container {
            position: fixed;
            top: 50px; /* User bar height */
            left: 0;
            right: 0;
            bottom: 70px; /* Bottom nav height */
            background: white;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* ========== FIXED PART HEADER ========== */
        .part-header-container {
            background: white;
            padding: 20px 40px;
            z-index: 10;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        /* ========== SCROLLABLE CONTENT AREA ========== */
        .content-area {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 30px 40px 120px;
            position: relative;
            background: white;
        }
        
        /* ========== PART SECTIONS ========== */
        .part-section {
            margin-bottom: 40px;
            animation: fadeIn 0.3s ease-in-out;
        }
        
        .part-section:not(.active) {
            display: none;
        }
        
        /* ========== PART HEADER - CARD STYLE ========== */
        .part-header {
            background: #f0f0f0;
            padding: 16px 24px;
            margin: 0 0 30px 0;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        /* Hide original part headers in scrollable area */
        .content-area .part-header {
            display: none;
        }
        
        /* Style for cloned fixed header */
        .part-header-container .part-header {
            display: block;
            margin: 0;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background: #f0f0f0;
        }
        
        .part-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 4px;
        }
        
        .part-instruction {
            font-size: 13px;
            color: #4b5563;
            line-height: 1.5;
        }
        
        /* ========== QUESTION GROUP HEADERS ========== */
        .question-group-header {
            font-size: 15px;
            font-weight: 700;  /* Bold */
            color: #000;
            margin: 35px 0 15px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .question-instruction {
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 16px;
            font-weight: normal; /* Changed from 600 to normal so TinyMCE bold tags work properly */
            line-height: 1.6;
        }
        
        /* ========== QUESTION ITEMS - LEFT ALIGNED ========== */
        .question-item {
            padding: 8px 0;
            border-bottom: none;
            font-size: 15px;
            margin-bottom: 8px;
        }

        .question-item:last-child {
            border-bottom: none;
        }

        .question-content {
            display: block;
            text-align: left;
            margin-bottom: 10px;
        }
        
        .question-number {
            display: block;
            font-weight: 700;
            color: #000;
            font-size: 15px;
            margin-bottom: 4px;
            text-align: left;
        }
        
        .question-text {
            line-height: 1.6;
            color: #1f2937;
            font-size: 15px;
            font-weight: normal; /* Changed from 500 to normal so TinyMCE bold tags work properly */
            text-align: left;
        }
        
        /* Options will be styled by external CSS */
        
        /* ========== TINYMCE CONTENT STYLES ========== */
        /* Tables from TinyMCE Editor */
        .question-text table,
        .question-instruction table,
        .part-instruction table,
        .question-item table,
        .question-content table {
            width: auto !important;
            max-width: 100% !important;
            border-collapse: collapse !important;
            margin: 10px 0 !important;
            font-size: 14px !important;
            background: white !important;
            box-shadow: none !important;
        }

        .question-text table th,
        .question-instruction table th,
        .part-instruction table th,
        .question-item table th,
        .question-content table th {
            background-color: #f3f4f6 !important;
            padding: 8px 12px !important;
            text-align: left !important;
            font-weight: 700 !important;
            border: 1px solid #000000 !important;
            color: #000000 !important;
            font-size: 14px !important;
        }

        .question-text table td,
        .question-instruction table td,
        .part-instruction table td,
        .question-item table td,
        .question-content table td {
            padding: 6px 12px !important;
            border: 1px solid #000000 !important;
            color: #1f2937 !important;
            background: white !important;
            font-size: 14px !important;
        }

        /* Table cell alignment from TinyMCE */
        .question-text table td[style*="text-align: center"],
        .question-text table th[style*="text-align: center"] {
            text-align: center !important;
        }

        .question-text table td[style*="text-align: right"],
        .question-text table th[style*="text-align: right"] {
            text-align: right !important;
        }
        

        /* Lists from TinyMCE */
        .question-text ul,
        .question-text ol,
        .question-instruction ul,
        .question-instruction ol,
        .question-content ul,
        .question-content ol {
            margin: 10px 0 10px 20px !important;
            padding-left: 20px !important;
        }
        
        .question-text ul li,
        .question-text ol li,
        .question-instruction ul li,
        .question-instruction ol li,
        .question-content ul li,
        .question-content ol li {
            margin-bottom: 5px !important;
            line-height: 1.6 !important;
        }
        
        /* Images from TinyMCE - Minimal style, no shadow */
        .question-text img,
        .question-instruction img,
        .part-instruction img,
        .question-content img {
            max-width: 100% !important;
            height: auto !important;
            margin: 10px 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            display: block !important;
        }
        
        /* Bold, Italic, Underline from TinyMCE */
        .question-text strong,
        .question-text b,
        .question-instruction strong,
        .question-instruction b,
        .question-content strong,
        .question-content b,
        .part-instruction strong,
        .part-instruction b {
            font-weight: 700 !important;
            color: #000000 !important;
        }

        .question-text em,
        .question-text i,
        .question-instruction em,
        .question-instruction i,
        .question-content em,
        .question-content i,
        .part-instruction em,
        .part-instruction i {
            font-style: italic !important;
        }

        .question-text u,
        .question-instruction u,
        .question-content u,
        .part-instruction u {
            text-decoration: underline !important;
        }
        
        /* Paragraphs from TinyMCE */
        .question-text p,
        .question-instruction p,
        .question-content p {
            margin: 8px 0 !important;
            line-height: 1.6 !important;
        }
        
        /* Links from TinyMCE */
        .question-text a,
        .question-instruction a,
        .question-content a {
            color: #3b82f6 !important;
            text-decoration: underline !important;
        }
        
        .question-text a:hover,
        .question-instruction a:hover,
        .question-content a:hover {
            color: #2563eb !important;
        }
        
        /* Code blocks from TinyMCE */
        .question-text pre,
        .question-instruction pre,
        .question-content pre {
            background: #f3f4f6 !important;
            padding: 10px !important;
            border-radius: 4px !important;
            overflow-x: auto !important;
            margin: 10px 0 !important;
        }
        
        .question-text code,
        .question-instruction code,
        .question-content code {
            background: #f3f4f6 !important;
            padding: 2px 4px !important;
            border-radius: 3px !important;
            font-family: monospace !important;
            font-size: 13px !important;
        }
        
        /* ========== INPUT FIELDS ========== */
        .answer-input {
            margin-left: 47px;
        }
        
        .text-input, .select-input {
            width: 350px;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 0;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        
        .text-input:hover, .select-input:hover {
            border-color: #999;
            background: #fafafa;
        }
        
        .text-input:focus, .select-input:focus {
            outline: none;
            border-color: #333;
            background: white;
            box-shadow: none;
        }
        
        .text-input::placeholder {
            color: #999;
            font-style: italic;
        }
        
        /* ========== DISABLE ALL BROWSER ASSISTS FOR EXAM INTEGRITY ========== */
        input[type="text"],
        input[type="number"],
        textarea,
        select,
        .text-input,
        .inline-blank,
        .dropdown {
            /* Disable spell check */
            spellcheck: false;
            
            /* Disable autocomplete */
            autocomplete: off;
            
            /* Disable autocorrect (iOS) */
            autocorrect: off;
            
            /* Disable autocapitalize (iOS) */
            autocapitalize: off;
            
            /* Disable password managers */
            -webkit-credentials-auto-fill-button: none !important;
            
            /* Disable Chrome's autofill */
            -webkit-box-shadow: 0 0 0 1000px #f5f5f5 inset !important;
        }
        
        /* Override Chrome autofill background */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #f5f5f5 inset !important;
            -webkit-text-fill-color: #000 !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        
        /* ========== SPECIAL QUESTION TYPES STYLES ========== */
        /* Matching Questions - Official IELTS Style */
        .matching-container {
            user-select: none;
            margin-top: 30px;
            display: flex;
            gap: 40px;
        }
        
        .matching-left-section {
            flex: 1;
        }
        
        .matching-table {
            width: 100%;
        }
        
        .matching-row {
            display: grid;
            grid-template-columns: 40px 250px 180px;
            align-items: center;
            margin-bottom: 15px;
            gap: 15px;
        }
        
        .question-number-inline {
            font-weight: 700;
            background: #e8e8e8;
            padding: 4px 10px;
            border-radius: 3px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 32px;
        }
        
        .matching-question {
            font-size: 15px;
            font-weight: 500;
            color: #1f2937;
        }
        
        .drop-box {
            display: inline-flex !important;
            min-width: 120px !important;
            width: auto !important;
            height: 40px !important;
            border: 1px dashed #000000 !important;
            border-radius: 4px !important;
            line-height: 38px !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.2s !important;
            background: white !important;
            font-size: 14px !important;
            padding: 0 20px !important;
            cursor: pointer !important;
            margin: 0 4px !important;
            vertical-align: middle !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            color: #1f2937 !important;
            text-align: center !important;
        }
        
        .drop-box.drag-over {
            background: #f0fdf4 !important;
            border: 1px dashed #22c55e !important;
        }

        .drop-box.has-answer {
            min-width: auto !important;
            width: auto !important;
            padding: 0 12px !important;
            border: 1px solid #d1d5db !important;
            background: white !important;
            cursor: move !important;
            color: #1f2937 !important;
            font-weight: normal !important;
        }

        .drop-box .placeholder-text {
            color: #000000 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }
        
        .matching-right-section {
            width: 150px;
            flex-shrink: 0;
            margin-left: -180px;
            margin-right: 50px;
        }
        
        .matching-options-container {
            position: sticky;
            top: 100px;
        }
        
        .matching-options-title {
            display: none;
        }
        
        .matching-options-grid {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        /* Drag & Drop Question Styles */
        .drag-drop-question {
            background: none;
            border: none;
            box-shadow: none;
            padding: 0;
            margin-bottom: 20px;
        }
        
        /* Drag Drop Layout - Horizontal Options on Right */
        .drag-drop-layout {
            display: flex !important;
            gap: 40px !important;
            align-items: flex-start !important;
        }

        .draggable-options-container {
            flex: 1 !important;
        }

        .draggable-options-grid {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            gap: 12px !important;
            padding: 0 !important;
            background: none !important;
            border: none !important;
        }

        .draggable-option {
            min-width: 120px !important;
            padding: 10px 20px !important;
            background: white !important;
            border: 1px solid #d1d5db !important;
            border-radius: 4px !important;
            cursor: move !important;
            transition: all 0.2s !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            color: #1f2937 !important;
            text-align: center !important;
            user-select: none !important;
        }

        .draggable-option:hover:not(.placed) {
            background: #f9fafb !important;
            border-color: #9ca3af !important;
        }

        .draggable-option.dragging {
            opacity: 0.5 !important;
            cursor: grabbing !important;
        }

        .draggable-option.placed {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Form Completion - Official IELTS Style */
        .form-completion-container {
            margin-left: 40px;
            margin-top: 20px;
        }
        
        .form-wrapper {
            background: white;
            border: 2px solid #000;
            padding: 40px 50px;
            max-width: 650px;
            margin: 0;
            position: relative;
        }
        
        .form-title {
            text-align: center;
            font-weight: 700;
            margin-bottom: 40px;
            color: #000;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-field-row {
            display: grid;
            grid-template-columns: 36px 180px 1fr;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #000;
            font-size: 14px;
            text-align: left;
        }
        
        .form-question-number {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            background: #f0f0f0;
            border: 1px solid #999;
            padding: 6px 0;
            text-align: center;
            border-radius: 4px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-input {
            width: 100%;
            max-width: 300px;
            padding: 10px 14px;
            border: 1px solid #999;
            border-radius: 0;
            font-size: 14px;
            background: white;
            font-family: Arial, sans-serif;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #333;
            background: white;
            box-shadow: none;
        }
        
        .form-input::placeholder {
            color: #999;
            font-style: italic;
            font-size: 13px;
        }
        
        /* Diagram Labeling */
        .diagram-container {
            margin-left: 40px;
            margin-top: 20px;
        }
        
        .diagram-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .diagram-image {
            max-width: 100%;
            height: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        
        .diagram-hotspot {
            position: absolute;
            transform: translate(-50%, -50%);
            cursor: help;
            transition: transform 0.2s;
        }
        
        .diagram-hotspot:hover {
            transform: translate(-50%, -50%) scale(1.1);
        }
        
        .hotspot-marker {
            width: 32px;
            height: 32px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .diagram-answers {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }
        
        .diagram-answer-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        
        .diagram-label {
            display: inline-flex;
            width: 28px;
            height: 28px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            flex-shrink: 0;
        }
        
        .diagram-number {
            font-weight: 600;
            color: #6b7280;
            font-size: 13px;
            margin-right: 5px;
        }
        
        .diagram-input {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 13px;
        }
        
        /* ========== BOTTOM NAVIGATION ========== */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e0e0e0;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
            height: 70px;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
            flex: 1;
            gap: 16px;
        }
        
        .review-section {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        
        .review-check {
            margin-right: 8px;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        
        .review-label {
            font-size: 14px;
            font-weight: 500;
            color: #2c3e50;
            cursor: pointer;
            user-select: none;
        }
        
        .nav-section-container {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
        }
        
        .section-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .parts-nav {
            display: flex;
            gap: 6px;
            border-right: 1px solid #e0e0e0;
            padding-right: 16px;
            margin-right: 12px;
        }
        
        .part-btn {
            padding: 6px 12px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .part-btn:hover {
            background: #f8f9fa;
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .part-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        

        
        .nav-numbers {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            max-width: 600px;
        }
        
        .number-btn {
            width: 32px;
            height: 32px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .number-btn:hover {
            background: #f8f9fa;
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .number-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
            font-weight: 600;
        }
        
        .number-btn.answered {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .number-btn.flagged {
            position: relative;
            overflow: visible;
        }
        
        .number-btn.flagged::after {
            content: '';
            position: absolute;
            top: -3px;
            right: -3px;
            width: 10px;
            height: 10px;
            background: #f59e0b;
            border-radius: 50%;
            border: 2px solid white;
        }
        

        
        .number-btn.hidden-part {
            display: none;
        }
        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .btn-secondary {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }
        
        .notes-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            background-color: #ef4444;
            color: white;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
        }
        
        .submit-test-button {
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .submit-test-button:hover {
            background: #059669;
        }
        
        /* ========== MODAL STYLES ========== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 24px;
            border-radius: 8px;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 16px;
            color: #1a202c;
        }
        
        .modal-message {
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.5;
            color: #4a5568;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .modal-button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin: 0 6px;
            transition: all 0.2s ease;
        }
        
        .modal-button:hover {
            background: #2563eb;
        }
        
        .modal-button.secondary {
            background: #6b7280;
        }
        
        .modal-button.secondary:hover {
            background: #4b5563;
        }
        
        /* ========== HIGHLIGHT & NOTES STYLES ========== */
        .highlighted-text {
            background-color: #fde047;
            border-radius: 2px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            padding: 0 !important;
            margin: 0 !important;
            display: inline;
            line-height: inherit;
        }
        
        .highlighted-text:hover {
            background-color: #facc15;
        }
        
        .note-text {
            background-color: #fee2e2;
            border-bottom: 1px solid #dc2626;
            border-radius: 2px;
            cursor: pointer;
            padding: 0 !important;
            margin: 0 !important;
            display: inline;
            line-height: inherit;
        }
        
        /* Annotation Menu */
        #annotation-menu {
            position: fixed;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 6px;
            display: flex;
            gap: 6px;
            z-index: 99999;
        }
        
        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .main-container {
                bottom: 120px; /* Adjust for mobile bottom nav */
            }
            
            .content-area {
                padding: 20px 20px 120px;
            }
            
            .part-header-container {
                padding: 15px 20px;
            }
            
            .parts-nav {
                display: none;
            }
            
            .nav-numbers {
                max-width: 100%;
                gap: 3px;
            }
            
            .number-btn {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            
            .bottom-nav {
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }
            
            .nav-left {
                width: 100%;
                flex-direction: column;
                gap: 10px;
            }
            
            .submit-test-button {
                width: 100%;
            }
            
            /* Mobile options styling - handled by external CSS */
            
            /* Special types mobile */
            .matching-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
            
            .matching-lines {
                display: none;
            }
            
            .form-completion-container table {
                font-size: 13px;
            }
            
            .diagram-container img {
                max-width: 100% !important;
            }
        }
        
        /* ========== QUESTION NAVIGATION ARROWS ========== */
        .question-nav-arrows {
            position: fixed;
            bottom: 100px;
            right: 20px;
            display: flex;
            flex-direction: row;
            gap: 10px;
            z-index: 50;
        }

        /* ========== SCROLLBAR STYLING ========== */
        .content-area::-webkit-scrollbar {
            width: 8px;
        }

        .content-area::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .content-area::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .content-area::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .nav-arrow {
            width: 44px;
            height: 44px;
            background: #000000;
            border: none;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .nav-arrow:hover {
            background: #333333;
        }

        .nav-arrow:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: #666666;
        }

        .nav-arrow svg {
            color: #ffffff;
        }

        .nav-arrow:hover svg {
            color: #ffffff;
        }

        .nav-arrow:disabled svg {
            color: #cccccc;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .question-nav-arrows {
                bottom: 140px;
                right: 50%;
                transform: translateX(50%);
            }
        }
        
        /* ========== ANIMATIONS ========== */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* ========== PROFESSIONAL MULTIPLE CHOICE STYLES ========== */
        .options-list {
            margin: 24px 0 24px 47px;
        }
        
        .option-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            cursor: pointer;
            padding: 14px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            border: 1.5px solid #e5e7eb;
            background: white;
            position: relative;
        }
        
        .option-item:hover {
            background: #fafafa;
            border-color: #9ca3af;
            transform: translateX(4px);
        }
        
        .option-radio,
        .option-checkbox {
            margin-top: 3px;
            margin-right: 14px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            flex-shrink: 0;
            accent-color: #111827;
            position: relative;
        }
        
        /* Custom Radio Button */
        .option-radio {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            background: white;
            position: relative;
            transition: all 0.2s ease;
        }
        
        .option-radio:checked {
            border-color: #111827;
            background: #111827;
        }
        
        .option-radio:checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
        }
        
        /* Custom Checkbox */
        .option-checkbox {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            background: white;
            position: relative;
            transition: all 0.2s ease;
        }
        
        .option-checkbox:checked {
            border-color: #111827;
            background: #111827;
        }
        
        .option-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 14px;
            font-weight: bold;
            line-height: 1;
        }
        
        .option-label {
            flex: 1;
            font-size: 15px;
            line-height: 1.7;
            color: #374151;
            cursor: pointer;
            display: flex;
            align-items: baseline;
        }
        
        .option-label strong {
            font-weight: 600;
            margin-right: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 14px;
            color: #374151;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }
        
        /* Selected state */
        .option-item:has(input:checked) {
            background: #fafafa;
            border-color: #111827;
            border-width: 2px;
            padding: 13px 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .option-item:has(input:checked) .option-label {
            color: #111827;
            font-weight: 500;
        }
        
        .option-item:has(input:checked) .option-label strong {
            background: #111827;
            color: white;
            border-color: #111827;
        }
        
        /* Mobile responsive for options */
        @media (max-width: 768px) {
            .options-list {
                margin-left: 16px;
            }
            
            .option-item {
                padding: 12px;
                margin-bottom: 10px;
            }
            
            .option-label {
                font-size: 14px;
            }
        }
        
        /* ========== TOAST NOTIFICATION ANIMATIONS ========== */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        #toast-notification.error {
            background: #dc2626;
        }
        
        #toast-notification.warning {
            background: #f59e0b;
        }
        
        #toast-notification.success {
            background: #10b981;
        }
        
        #toast-notification.info {
            background: #3b82f6;
        }
        /* ========== MAKE ANSWER AREAS UNSELECTABLE ========== */
        .answer-input,
        .options-list,
        .option-item,
        .option-label,
        .single-choice-options,
        .single-choice-option-item,
        .single-choice-label,
        .matching-container,
        .form-completion-container,
        .diagram-answers,
        .drop-box,
        .draggable-option,
        input,
        select,
        textarea {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Allow text selection ONLY in question areas */
        .question-text,
        .part-instruction,
        .question-instruction,
        .question-group-header {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        
        /* Ensure input fields are still editable */
        input[type="text"],
        input[type="number"],
        textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
    </style>

    

    <!-- Fixed User Info Bar -->
    <div class="user-bar" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000; height: 50px;">
        <div class="user-info">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ auth()->user()->name }} - BI {{ str_pad(auth()->id(), 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        
        {{-- Integrated Timer Component - Center Position --}}
        {{-- Timer = Section time_limit + 2 min review (Computer-Based IELTS style) --}}
        {{-- Two-phase display: First shows main test time, then switches to "Review Time" --}}
        @php
            $sectionTimeLimit = $testSet->section->time_limit ?? 30;
            $reviewTimeMinutes = 2; // Auto-add 2 minutes for answer review
        @endphp
        <div class="timer-center-wrapper">
            <x-test-timer
                :attempt="$attempt"
                auto-submit-form-id="listening-form"
                position="integrated"
                :warning-time="300"
                :danger-time="60"
                :custom-duration="$sectionTimeLimit"
                :review-time="$reviewTimeMinutes"
            />
        </div>
        
        <div class="user-controls">
            <button class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm help-button" id="help-button">Help ?</button>
            <button class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm no-nav">Hide</button>
            <div class="flex items-center ml-2">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071a1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                <input type="range" min="0" max="100" value="75" class="ml-2 w-20" id="volume-slider">
            </div>
        </div>
    </div>

    <!-- Main Container with Fixed Part Header and Scrollable Content -->
    <div class="main-container">
        <!-- Fixed Part Header (will be updated dynamically) -->
        <div class="part-header-container" id="fixed-part-header">
            <!-- Part header will be cloned here -->
        </div>
        
        <!-- Scrollable Content Area -->
        <div class="content-area">
        <!-- Question Navigation Arrows -->
        <div class="question-nav-arrows">
            <button type="button" class="nav-arrow prev-arrow" id="prev-question-btn" title="Previous Question">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button type="button" class="nav-arrow next-arrow" id="next-question-btn" title="Next Question">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
        
        <form id="listening-form" action="{{ route('student.listening.submit', $attempt) }}" method="POST">
            @csrf
            
            @php
                $allQuestions = $testSet->questions->sortBy('order_number');
                $groupedQuestions = $allQuestions->groupBy('part_number');
                $currentQuestionNumber = 1;
                
                // Pre-calculate total questions including sub-questions
                $totalQuestionCount = 0;
                foreach ($allQuestions as $q) {
                    if ($q->question_type === 'fill_blanks') {
                        preg_match_all('/\[____(\d+)____\]/', $q->content, $matches);
                        $blankCount = count($matches[0]);
                        $totalQuestionCount += ($blankCount > 0 ? $blankCount : 1);
                    } elseif ($q->question_type === 'dropdown_selection') {
                        preg_match_all('/\[DROPDOWN_(\d+)\]/', $q->content, $matches);
                        $dropdownCount = count($matches[0]);
                        $totalQuestionCount += ($dropdownCount > 0 ? $dropdownCount : 1);
                    } elseif ($q->question_type === 'drag_drop') {
                        $dragDropData = $q->section_specific_data ?? [];
                        $dropZones = $dragDropData['drop_zones'] ?? [];
                        $dropZoneCount = count($dropZones);
                        $totalQuestionCount += ($dropZoneCount > 0 ? $dropZoneCount : 1);
                    } elseif ($q->question_type === 'multiple_choice') {
                        // For multiple choice, count correct answers as individual questions
                        $correctCount = $q->options->where('is_correct', true)->count();
                        $totalQuestionCount += ($correctCount > 1 ? $correctCount : 1);
                    } else {
                        $totalQuestionCount++;
                    }
                }
            @endphp
            
            @foreach ($groupedQuestions as $partNumber => $partQuestions)
                <div class="part-section {{ $loop->first ? 'active' : '' }}" data-part="{{ $partNumber }}">
                    <!-- Part Header (Hidden in content, will be cloned to fixed position) -->
                    <div class="part-header" data-part-number="{{ $partNumber }}">
                        <div class="part-title">Part {{ $partNumber }}</div>
                        <div class="part-instruction">Listen and answer questions {{ $partNumber == 1 ? '1-10' : ($partNumber == 2 ? '11-20' : ($partNumber == 3 ? '21-30' : '31-40')) }}.</div>
                    </div>

                    <!-- Questions -->
                    @php
                        $questionGroups = $partQuestions->groupBy('question_group');
                        $shownInstructions = [];
                    @endphp
                    
                    @foreach ($questionGroups as $groupName => $questions)
                        @if($groupName)
                            <div class="question-group-header">{{ $groupName }}</div>
                        @endif
                        
                        @foreach ($questions as $question)
                            @php
                                $displayNumber = $currentQuestionNumber;
                            @endphp
                            
                            {{-- Show instruction if not already shown --}}
                            @if($question->instructions && !in_array($question->instructions, $shownInstructions))
                                <div class="question-instruction">{!! $question->instructions !!}</div>
                                @php $shownInstructions[] = $question->instructions; @endphp
                            @endif
                            
                            {{-- Include the question render partial --}}
                            @include('student.test.listening.question-render', [
                                'question' => $question,
                                'displayNumber' => $displayNumber
                            ])
                            
                            @php
                                // Update current question number based on question type
                                if ($question->question_type === 'fill_blanks') {
                                    preg_match_all('/\[____(\d+)____\]/', $question->content, $matches);
                                    $blankCount = count($matches[0]);
                                    $currentQuestionNumber += ($blankCount > 0 ? $blankCount : 1);
                                } elseif ($question->question_type === 'dropdown_selection') {
                                    preg_match_all('/\[DROPDOWN_(\d+)\]/', $question->content, $matches);
                                    $dropdownCount = count($matches[0]);
                                    $currentQuestionNumber += ($dropdownCount > 0 ? $dropdownCount : 1);
                                } elseif ($question->question_type === 'drag_drop') {
                                    $dragDropData = $question->section_specific_data ?? [];
                                    $dropZones = $dragDropData['drop_zones'] ?? [];
                                    $dropZoneCount = count($dropZones);
                                    $currentQuestionNumber += ($dropZoneCount > 0 ? $dropZoneCount : 1);
                                } elseif ($question->question_type === 'multiple_choice') {
                                    // For multiple choice, count correct answers
                                    $correctCount = $question->options->where('is_correct', true)->count();
                                    $currentQuestionNumber += ($correctCount > 1 ? $correctCount : 1);
                                } else {
                                    $currentQuestionNumber++;
                                }
                            @endphp
                        @endforeach
                    @endforeach
                </div>
            @endforeach
            
            <button type="submit" id="submit-button" class="hidden">Submit</button>
        </form>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="nav-left">
            <div class="review-section">
                <input type="checkbox" id="review-checkbox" class="review-check">
                <label for="review-checkbox" class="review-label">Flag</label>
            </div>
            
            <div class="nav-section-container">
                <span class="section-label">Listening</span>
                
                {{-- Parts Navigation --}}
                <div class="parts-nav">
                    @foreach($groupedQuestions->keys() as $partNum)
                        <button type="button" class="part-btn {{ $loop->first ? 'active' : '' }}" data-part="{{ $partNum }}">
                            Part {{ $partNum }}
                        </button>
                    @endforeach
                </div>
                
                {{-- Question Numbers --}}
                <div class="nav-numbers">
                    @php 
                        $navQuestionNum = 1;
                        $questionIdMap = [];
                    @endphp
                    @foreach($allQuestions as $question)
                        @if($question->question_type === 'fill_blanks')
                            @php
                                preg_match_all('/\[____(\d+)____\]/', $question->content, $matches);
                                $blankCount = count($matches[0]);
                                $blankCount = $blankCount > 0 ? $blankCount : 1;
                            @endphp
                            @for($i = 1; $i <= $blankCount; $i++)
                                @php $questionIdMap[$navQuestionNum] = $question->id; @endphp
                                <div class="number-btn {{ $navQuestionNum == 1 ? 'active' : '' }}" 
                                     data-question="{{ $question->id }}"
                                     data-sub-index="{{ $i }}"
                                     data-display-number="{{ $navQuestionNum }}"
                                     data-part="{{ $question->part_number }}">
                                    {{ $navQuestionNum++ }}
                                </div>
                            @endfor
                        @elseif($question->question_type === 'dropdown_selection')
                            @php
                                preg_match_all('/\[DROPDOWN_(\d+)\]/', $question->content, $matches);
                                $dropdownCount = count($matches[0]);
                                $dropdownCount = $dropdownCount > 0 ? $dropdownCount : 1;
                            @endphp
                            @for($i = 1; $i <= $dropdownCount; $i++)
                                @php $questionIdMap[$navQuestionNum] = $question->id; @endphp
                                <div class="number-btn {{ $navQuestionNum == 1 ? 'active' : '' }}" 
                                     data-question="{{ $question->id }}"
                                     data-sub-index="{{ $i }}"
                                     data-display-number="{{ $navQuestionNum }}"
                                     data-part="{{ $question->part_number }}">
                                    {{ $navQuestionNum++ }}
                                </div>
                            @endfor
                        @elseif($question->question_type === 'drag_drop')
                            @php
                                // For drag_drop questions, show one button per drop zone
                                $dragDropData = $question->section_specific_data ?? [];
                                $dropZones = $dragDropData['drop_zones'] ?? [];
                                $dropZoneCount = count($dropZones);
                                $dropZoneCount = $dropZoneCount > 0 ? $dropZoneCount : 1;
                            @endphp
                            @for($i = 0; $i < $dropZoneCount; $i++)
                                @php $questionIdMap[$navQuestionNum] = $question->id; @endphp
                                <div class="number-btn {{ $navQuestionNum == 1 ? 'active' : '' }}" 
                                     data-question="{{ $question->id }}"
                                     data-zone-index="{{ $i }}"
                                     data-display-number="{{ $navQuestionNum }}"
                                     data-part="{{ $question->part_number }}">
                                    {{ $navQuestionNum++ }}
                                </div>
                            @endfor
                        @elseif($question->question_type === 'multiple_choice')
                            @php
                                // For multiple choice, show buttons based on correct answer count
                                $correctCount = $question->options->where('is_correct', true)->count();
                                $buttonCount = $correctCount > 1 ? $correctCount : 1;
                            @endphp
                            @for($i = 1; $i <= $buttonCount; $i++)
                                @php $questionIdMap[$navQuestionNum] = $question->id; @endphp
                                <div class="number-btn {{ $navQuestionNum == 1 ? 'active' : '' }}" 
                                     data-question="{{ $question->id }}"
                                     data-display-number="{{ $navQuestionNum }}"
                                     data-part="{{ $question->part_number }}">
                                    {{ $navQuestionNum++ }}
                                </div>
                            @endfor
                        @else
                            @php $questionIdMap[$navQuestionNum] = $question->id; @endphp
                            <div class="number-btn {{ $navQuestionNum == 1 ? 'active' : '' }}" 
                                 data-question="{{ $question->id }}"
                                 data-display-number="{{ $navQuestionNum }}"
                                 data-part="{{ $question->part_number }}">
                                {{ $navQuestionNum++ }}
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="nav-right">
            <button type="button" class="btn-secondary" id="notes-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Notes
                <span class="notes-badge" id="notes-count" style="display: none;">0</span>
            </button>
            
            <button type="button" class="btn-secondary" id="fullscreen-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
                Fullscreen
            </button>
            
            <button type="button" id="submit-test-btn" class="submit-test-button" onclick="console.log('Submit clicked'); document.getElementById('submit-modal').style.display='flex';">
                Submit Test
            </button>
        </div>
    </div>

    <!-- Submit Modal -->
    <div id="submit-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-title">Submit Test?</div>
            <div class="modal-message">
                Are you sure you want to submit your test? You cannot change your answers after submission.
            </div>
            <div class="modal-buttons">
                <button class="modal-button" id="confirm-submit-btn" onclick="if(window.UniversalTimer)window.UniversalTimer.stop(); document.getElementById('submit-button').click();">Yes, Submit</button>
                <button class="modal-button secondary" id="cancel-submit-btn" onclick="document.getElementById('submit-modal').style.display='none';">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Universal Loading Screen Component -->
    <x-test-loading-screen />

    <!-- Audio Start Overlay (shown after loading) -->
    <div id="audio-start-overlay" style="
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 99999;
        justify-content: center;
        align-items: center;
    ">
        <div class="audio-overlay-content">
            <!-- Headphone Icon (transparent/outline) -->
            <div class="audio-overlay-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="1.2">
                    <path d="M12 3C7.03 3 3 7.03 3 12v6c0 1.1.9 2 2 2h1c1.1 0 2-.9 2-2v-3c0-1.1-.9-2-2-2H5v-1c0-3.87 3.13-7 7-7s7 3.13 7 7v1h-1c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h1c1.1 0 2-.9 2-2v-6c0-4.97-4.03-9-9-9z"/>
                </svg>
            </div>

            <div class="audio-overlay-text">
                <p>You will be listening to an audio clip during this test. You will not be permitted to pause or rewind the audio while answering the questions.</p>
                <p>To continue, click Play.</p>
            </div>

            <button id="start-audio-btn" class="audio-play-btn">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                Play
            </button>
        </div>

        <style>
            .audio-overlay-content {
                text-align: center;
                color: white;
                max-width: 620px;
                width: 92%;
                padding: 30px 25px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .audio-overlay-icon {
                margin-bottom: 25px;
            }
            .audio-overlay-icon svg {
                width: 70px;
                height: 70px;
            }
            .audio-overlay-text {
                margin-bottom: 30px;
            }
            .audio-overlay-text p {
                font-size: 16px;
                color: #d1d5db;
                margin: 0 0 10px 0;
                line-height: 1.6;
            }
            .audio-overlay-text p:last-child {
                margin-bottom: 0;
            }
            .audio-play-btn {
                background: rgba(255, 255, 255, 0.1);
                color: white;
                border: 1px solid rgba(255, 255, 255, 0.3);
                padding: 12px 40px;
                font-size: 15px;
                font-weight: 500;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            .audio-play-btn:hover {
                background: rgba(255, 255, 255, 0.15);
                border-color: rgba(255, 255, 255, 0.5);
            }

            /* Responsive */
            @media (max-width: 480px) {
                .audio-overlay-content {
                    padding: 25px 15px;
                }
                .audio-overlay-icon svg {
                    width: 55px;
                    height: 55px;
                }
                .audio-overlay-icon {
                    margin-bottom: 20px;
                }
                .audio-overlay-text {
                    margin-bottom: 25px;
                }
                .audio-overlay-text p {
                    font-size: 14px;
                    margin-bottom: 8px;
                }
                .audio-play-btn {
                    padding: 10px 35px;
                    font-size: 14px;
                }
            }
        </style>
    </div>

    <!-- Toast Notification -->
    <div id="toast-notification" style="
        position: fixed;
        top: 70px;
        right: 20px;
        background: #1f2937;
        color: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: none;
        align-items: center;
        gap: 12px;
        z-index: 99999;
        min-width: 300px;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    ">
        <svg id="toast-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div style="flex: 1;">
            <div id="toast-message" style="font-size: 14px; line-height: 1.5;"></div>
        </div>
        <button onclick="hideToast()" style="
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 4px;
            opacity: 0.7;
            transition: opacity 0.2s;
        " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    <!-- Hidden Audio Elements -->
@php
    // Check if Full Audio exists (part_number = 0)
    $fullAudio = $testSet->partAudios()->where('part_number', 0)->first();
    $isFullAudio = (bool) $fullAudio;
@endphp

@if($isFullAudio)
    {{-- Full Audio Mode: Single audio element for entire test --}}
    <audio id="test-audio-full" preload="auto" style="display:none;" data-mode="full">
        <source src="{{ $fullAudio->audio_url }}" type="audio/mpeg">
        <source src="{{ $fullAudio->audio_url }}" type="audio/ogg">
        <source src="{{ $fullAudio->audio_url }}" type="audio/wav">
        Your browser does not support the audio element.
    </audio>
@else
    {{-- Individual Parts Mode: Separate audio for each part --}}
    @foreach($groupedQuestions->keys() as $partNumber)
        @php
            // Get part-specific audio
            $partAudio = $testSet->partAudios()->where('part_number', $partNumber)->first();
            $audioUrl = null;

            if ($partAudio) {
                // Use the audio_url accessor which handles CDN URLs
                $audioUrl = $partAudio->audio_url;
            } else {
                // Fallback: Find first question with audio in this part
                $firstQuestionWithAudio = $testSet->questions()
                    ->where('part_number', $partNumber)
                    ->where('use_part_audio', false)
                    ->whereNotNull('media_path')
                    ->first();

                if ($firstQuestionWithAudio) {
                    // Check if it has a CDN URL
                    if ($firstQuestionWithAudio->media_url) {
                        $audioUrl = $firstQuestionWithAudio->media_url;
                    } elseif ($firstQuestionWithAudio->storage_disk === 'r2') {
                        // Generate R2 URL
                        $baseUrl = rtrim(config('filesystems.disks.r2.url'), '/');
                        $audioUrl = $baseUrl . '/' . ltrim($firstQuestionWithAudio->media_path, '/');
                    } else {
                        // Local storage URL
                        $audioUrl = asset('storage/' . $firstQuestionWithAudio->media_path);
                    }
                }
            }
        @endphp

        @if($audioUrl)
            <audio id="test-audio-{{ $partNumber }}" preload="auto" style="display:none;" data-mode="part" data-part="{{ $partNumber }}">
                <source src="{{ $audioUrl }}" type="audio/mpeg">
                <source src="{{ $audioUrl }}" type="audio/ogg">
                <source src="{{ $audioUrl }}" type="audio/wav">
                Your browser does not support the audio element.
            </audio>
        @else
            <!-- No audio for this part -->
            <div id="no-audio-{{ $partNumber }}" style="display:none;"
                 data-message="No audio available for Part {{ $partNumber }}">
            </div>
        @endif
    @endforeach
@endif
    
    
    @push('scripts')
    {{-- Include Drag & Drop Handler --}}
    <script src="{{ asset('js/student/listening-drag-drop.js') }}"></script>
    
    <script>
    // ========== TOAST NOTIFICATION FUNCTIONS ==========
    let toastTimeout;
    
    function showToast(message, type = 'info') {
        const toast = document.getElementById('toast-notification');
        const messageEl = document.getElementById('toast-message');
        const icon = document.getElementById('toast-icon');
        
        // Clear any existing timeout
        if (toastTimeout) {
            clearTimeout(toastTimeout);
        }
        
        // Set message
        messageEl.textContent = message;
        
        // Remove all type classes
        toast.classList.remove('error', 'warning', 'success', 'info');
        
        // Add appropriate class and icon
        toast.classList.add(type);
        
        // Set appropriate icon based on type
        switch(type) {
            case 'error':
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                break;
            case 'warning':
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>';
                break;
            case 'success':
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                break;
            default:
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        }
        
        // Show toast
        toast.style.display = 'flex';
        toast.style.animation = 'slideIn 0.3s ease-out';
        
        // Auto hide after 5 seconds
        toastTimeout = setTimeout(() => {
            hideToast();
        }, 5000);
    }
    
    function hideToast() {
        const toast = document.getElementById('toast-notification');
        toast.style.animation = 'slideOut 0.3s ease-out';
        
        setTimeout(() => {
            toast.style.display = 'none';
        }, 300);
        
        if (toastTimeout) {
            clearTimeout(toastTimeout);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // ========== Disable Right Click, Copy, Select All ==========
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Disable Ctrl+A (Select All)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
                e.preventDefault();
                return false;
            }
        });
        
        // Disable Ctrl+C (Copy)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                e.preventDefault();
                return false;
            }
        });
        
        // Disable copy event
        document.addEventListener('copy', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Configuration
        const testConfig = {
            attemptId: {{ $attempt->id }},
            testSetId: {{ $testSet->id }},
            totalQuestions: {{ $totalQuestionCount }},
            isFullAudio: {{ $isFullAudio ? 'true' : 'false' }}
        };
        
        // Elements
        const form = document.getElementById('listening-form');
        const submitButton = document.getElementById('submit-button');
        const submitTestBtn = document.getElementById('submit-test-btn');
        const submitModal = document.getElementById('submit-modal');
        const confirmSubmitBtn = document.getElementById('confirm-submit-btn');
        const cancelSubmitBtn = document.getElementById('cancel-submit-btn');
        const answeredCountSpan = document.getElementById('answered-count');
        const reviewCheckbox = document.getElementById('review-checkbox');
        const notesBtn = document.getElementById('notes-btn');
        const notesCount = document.getElementById('notes-count');
        const volumeSlider = document.getElementById('volume-slider');
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        
        // Part Navigation
        const partButtons = document.querySelectorAll('.part-btn');
        const partSections = document.querySelectorAll('.part-section');
        const numberButtons = document.querySelectorAll('.number-btn');
        
        // Current Audio
        let currentAudio = null;
        let isAudioPlaying = false;
        let audioStarted = false;

        // ========== Continuous Audio Management ==========
        function setupContinuousAudio() {
            const allAudioElements = [];

            // Check if Full Audio mode
            if (testConfig.isFullAudio) {
                // Full Audio Mode: Single audio element
                const fullAudio = document.getElementById('test-audio-full');
                if (fullAudio) {
                    allAudioElements.push({
                        part: 'full',
                        element: fullAudio,
                        duration: 0,
                        isFullAudio: true
                    });
                    console.log('Audio Mode: Full Audio (single file for entire test)');
                }
            } else {
                // Individual Parts Mode: Separate audio for each part
                for (let i = 1; i <= 4; i++) {
                    const audio = document.getElementById(`test-audio-${i}`);
                    if (audio) {
                        allAudioElements.push({
                            part: i,
                            element: audio,
                            duration: 0,
                            isFullAudio: false
                        });
                    }
                }
                console.log(`Audio Mode: Individual Parts (${allAudioElements.length} audio files)`);
            }

            // Setup event listeners for seamless playback
            allAudioElements.forEach((audioObj, index) => {
                const audio = audioObj.element;

                // Get duration when metadata loads
                audio.addEventListener('loadedmetadata', function() {
                    audioObj.duration = audio.duration;
                });

                // Handle when audio ends
                audio.addEventListener('ended', function() {
                    if (audioObj.isFullAudio) {
                        // Full Audio Mode: Audio ended, timer continues for review time
                        console.log('Full audio completed - Review time now available');
                        isAudioPlaying = false;
                        audioStarted = false;
                    } else {
                        // Individual Parts Mode: Play next part if exists
                        console.log(`Part ${audioObj.part} audio ended`);

                        const nextAudioObj = allAudioElements[index + 1];
                        if (nextAudioObj) {
                            console.log(`Starting Part ${nextAudioObj.part} audio`);
                            currentAudio = nextAudioObj.element;

                            // Apply volume
                            if (volumeSlider) {
                                currentAudio.volume = volumeSlider.value / 100;
                            }

                            currentAudio.play().catch(e => {
                                console.error('Audio playback failed:', e);
                            });
                        } else {
                            // All parts completed - timer continues for review time
                            console.log('All audio parts completed - Review time now available');
                            isAudioPlaying = false;
                            audioStarted = false;
                        }
                    }
                });

                // Handle play/pause events
                audio.addEventListener('play', function() {
                    isAudioPlaying = true;
                });
                
                audio.addEventListener('pause', function() {
                    isAudioPlaying = false;
                });
            });
            
            return allAudioElements;
        }
        
        const audioSequence = setupContinuousAudio();
        
        // ========== Fullscreen Functionality ==========
        fullscreenBtn.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                // Enter fullscreen
                document.documentElement.requestFullscreen().catch(err => {
                    console.log(`Error attempting to enable fullscreen: ${err.message}`);
                });
                // Update button text and icon
                this.innerHTML = `
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V5m0 0h4m-4 0l5 5m-5 10v-4m0 4h4m-4 0l5-5m5-5v4m0-4h-4m4 0l-5 5m-5 5h4m0 0v4m0-4l-5-5"/>
                    </svg>
                    Exit Fullscreen
                `;
            } else {
                // Exit fullscreen
                document.exitFullscreen();
                // Update button text and icon
                this.innerHTML = `
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    Fullscreen
                `;
            }
        });
        
        // Update button when fullscreen changes (e.g., user presses ESC)
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                fullscreenBtn.innerHTML = `
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    Fullscreen
                `;
            }
        });
        
        // ========== Fixed Part Header Management ==========
        function updateFixedPartHeader(partNumber) {
            const fixedHeaderContainer = document.getElementById('fixed-part-header');
            const originalHeader = document.querySelector(`.part-header[data-part-number="${partNumber}"]`);
            
            if (originalHeader && fixedHeaderContainer) {
                // Clone the header
                const clonedHeader = originalHeader.cloneNode(true);
                clonedHeader.style.display = 'block';
                
                // Clear and append
                fixedHeaderContainer.innerHTML = '';
                fixedHeaderContainer.appendChild(clonedHeader);
            }
        }
        
        // Initialize with first part header
        updateFixedPartHeader('1');
        
        // ========== Part Navigation (Locking Removed) ==========
        
        partButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const targetPart = parseInt(this.dataset.part);
                
                // Update active button
                partButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Show target part
                partSections.forEach(section => {
                    section.classList.remove('active');
                    if (section.dataset.part === String(targetPart)) {
                        section.classList.add('active');
                    }
                });
                
                // Update fixed part header
                updateFixedPartHeader(String(targetPart));
                
                // Update number buttons visibility
                updateNumberButtonsVisibility(String(targetPart));
                
                // Play audio for this part
                playPartAudio(String(targetPart));
            });
        });
        
        // ========== Question Navigation ==========
        numberButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                
                // Update active button
                numberButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const questionId = this.dataset.question;
                const subIndex = this.dataset.subIndex;
                const zoneIndex = this.dataset.zoneIndex;
                const questionElement = document.getElementById(`question-${questionId}`);
                
                if (questionElement) {
                    // Switch to correct part if needed
                    const partNumber = this.dataset.part;
                    const currentActivePart = document.querySelector('.part-btn.active');
                    if (currentActivePart && currentActivePart.dataset.part !== partNumber) {
                        const partBtn = document.querySelector(`.part-btn[data-part="${partNumber}"]`);
                        if (partBtn && !partBtn.classList.contains('locked')) {
                            partBtn.click();
                            // Update fixed header when switching parts
                            updateFixedPartHeader(partNumber);
                        }
                    }
                    
                    // For drag_drop questions, scroll to specific drop zone
                    if (zoneIndex !== undefined) {
                        const dropZoneItem = questionElement.querySelector(`[data-zone-index="${zoneIndex}"]`);
                        if (dropZoneItem) {
                            dropZoneItem.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            
                            // Highlight the drop box briefly
                            const dropBox = dropZoneItem.querySelector('.drop-box');
                            if (dropBox) {
                                dropBox.style.transition = 'all 0.3s ease';
                                dropBox.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.5)';
                                setTimeout(() => {
                                    dropBox.style.boxShadow = '';
                                }, 1000);
                            }
                        }
                    } else {
                        // Regular scroll to question
                        questionElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                    
                    // Focus on specific sub-question input if applicable
                    if (subIndex !== undefined) {
                        const input = questionElement.querySelector(`input[name="answers[${questionId}_${subIndex}]"]`);
                        if (input) {
                            setTimeout(() => input.focus(), 300);
                        }
                    }
                }
                
                // Update review checkbox
                reviewCheckbox.checked = this.classList.contains('flagged');
            });
        });
        
        // ========== Review/Flag Functionality ==========
        reviewCheckbox.addEventListener('change', function() {
            const currentQuestion = document.querySelector('.number-btn.active');
            if (currentQuestion) {
                if (this.checked) {
                    currentQuestion.classList.add('flagged');
                } else {
                    currentQuestion.classList.remove('flagged');
                }
            }
        });
        
        // ========== Answer Tracking ==========
        document.querySelectorAll('input[type="radio"], input[type="checkbox"], input[type="text"], select').forEach(input => {
            input.addEventListener('change', function() {
                const questionNumber = this.dataset.questionNumber;
                
                // Handle different input types
                if (this.type === 'radio') {
                    // For radio buttons, mark the specific question number as answered
                    if (questionNumber) {
                        const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                        if (navButton) {
                            if (this.checked) {
                                navButton.classList.add('answered');
                            }
                        }
                    }
                } else if (this.type === 'checkbox') {
                    // For checkboxes (multiple choice), check the parent question
                    const questionId = this.name.match(/answers\[(\d+)\]/)?.[1];
                    if (questionId) {
                        // Find all checkboxes for this question
                        const allCheckboxes = document.querySelectorAll(`input[name="answers[${questionId}][]"]:checked`);
                        const checkedCount = allCheckboxes.length;
                        
                        // Find all number buttons for this question
                        const navButtons = document.querySelectorAll(`.number-btn[data-question="${questionId}"]`);
                        
                        if (navButtons.length > 0) {
                            // Mark buttons as answered based on how many checkboxes are selected
                            navButtons.forEach((btn, index) => {
                                if (index < checkedCount) {
                                    btn.classList.add('answered');
                                } else {
                                    btn.classList.remove('answered');
                                }
                            });
                        }
                    }
                } else if (this.type === 'text' || this.tagName.toLowerCase() === 'select') {
                    // For text/select inputs
                    if (questionNumber) {
                        const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                        if (navButton) {
                            if (this.value && this.value.trim()) {
                                navButton.classList.add('answered');
                            } else {
                                navButton.classList.remove('answered');
                            }
                        }
                    }
                }
                
                saveAllAnswers();
                updateAnswerCount();
            });
        });
        
        // ========== Audio Controls ==========
        // Note: Audio is started via handleStartAudio() when user clicks Play button
        // This function only handles part navigation (returns early if audio already playing)
        function playPartAudio(partNumber) {
            // If audio already started and playing, don't interrupt
            if (audioStarted && isAudioPlaying) {
                console.log('Audio already playing continuously, not interrupting');
                return;
            }

            // If audio hasn't started yet, do nothing - user must click Play button
            if (!audioStarted) {
                console.log('Audio not started yet - waiting for user to click Play');
                return;
            }
        }

        // ========== Audio Start Overlay (for autoplay blocked) ==========
        function showAudioStartOverlay() {
            const overlay = document.getElementById('audio-start-overlay');
            if (overlay) {
                overlay.style.display = 'flex';
            }
        }

        function hideAudioStartOverlay() {
            const overlay = document.getElementById('audio-start-overlay');
            if (overlay) {
                overlay.style.opacity = '0';
                overlay.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }
        }

        // Handle Start Audio button click (only Play button triggers audio)
        const startAudioBtn = document.getElementById('start-audio-btn');

        function handleStartAudio() {
            // Get the appropriate audio element
            let audioElement;
            if (testConfig.isFullAudio) {
                audioElement = document.getElementById('test-audio-full');
                console.log('Starting Full Audio playback');
            } else {
                audioElement = document.getElementById('test-audio-1');
                console.log('Starting Individual Parts playback from Part 1');
            }

            if (audioElement) {
                currentAudio = audioElement;

                // Set volume
                if (volumeSlider) {
                    audioElement.volume = volumeSlider.value / 100;
                }

                // Add error handling for audio playback issues
                audioElement.addEventListener('error', function(e) {
                    console.error('Audio error:', e);
                    const target = e.target;
                    let errorMsg = 'Audio playback error';

                    // Determine error type
                    if (target.error) {
                        switch(target.error.code) {
                            case target.error.MEDIA_ERR_ABORTED:
                                errorMsg = 'Audio playback aborted';
                                break;
                            case target.error.MEDIA_ERR_NETWORK:
                                errorMsg = 'Network error while loading audio';
                                break;
                            case target.error.MEDIA_ERR_DECODE:
                                errorMsg = 'Audio decoding error';
                                break;
                            case target.error.MEDIA_ERR_SRC_NOT_SUPPORTED:
                                errorMsg = 'Audio format not supported';
                                break;
                        }
                    }

                    showToast(`${errorMsg}. Please refresh the page.`, 'error');
                });

                // Play audio (this will work because user clicked)
                audioElement.play().then(() => {
                    audioStarted = true;
                    isAudioPlaying = true;
                    hideAudioStartOverlay();

                    // Also hide loading screen if still visible
                    const loadingScreen = document.getElementById('loading-screen');
                    if (loadingScreen) {
                        loadingScreen.style.display = 'none';
                    }

                    console.log('Audio started after user interaction');
                }).catch(e => {
                    console.error('Audio still failed:', e);
                    showToast('Unable to play audio. Please check your browser settings or try a different browser.', 'error');
                });
            }
        }

        if (startAudioBtn) {
            startAudioBtn.addEventListener('click', handleStartAudio);
        }

        // Volume control
        if (volumeSlider) {
            volumeSlider.addEventListener('input', function() {
                const volume = this.value / 100;
                
                // Apply to current audio if playing
                if (currentAudio) {
                    currentAudio.volume = volume;
                }
                
                // Apply to all audio elements for future playback
                audioSequence.forEach(audioObj => {
                    audioObj.element.volume = volume;
                });
            });
        }
        
        // ========== Submit Functionality ==========
        submitTestBtn.addEventListener('click', function() {
            // First clean up any incorrect answered states for drag-drop
            const dropBoxes = document.querySelectorAll('.drop-box');
            dropBoxes.forEach(box => {
                const questionNumber = box.dataset.questionNumber;
                const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                
                // Only mark answered if box really has an answer
                if (navButton) {
                    if (box.classList.contains('has-answer')) {
                        navButton.classList.add('answered');
                    } else {
                        navButton.classList.remove('answered');
                    }
                }
            });
            
            // Now update count
            updateAnswerCount();
            submitModal.style.display = 'flex';
        });
        
        confirmSubmitBtn.addEventListener('click', function() {
            if (window.UniversalTimer) {
                window.UniversalTimer.stop();
            }
            saveAllAnswers();

            // Clear localStorage after save (server will also clear draft_answers)
            try {
                localStorage.removeItem(`testAnswers_${testConfig.attemptId}`);
                console.log('localStorage cleared for attempt:', testConfig.attemptId);
            } catch (e) {
                console.warn('Could not clear localStorage:', e);
            }

            submitButton.click();
        });
        
        cancelSubmitBtn.addEventListener('click', function() {
            submitModal.style.display = 'none';
        });
        
        // ========== Helper Functions ==========
        function updateNumberButtonsVisibility(activePart) {
            numberButtons.forEach(btn => {
                if (btn.dataset.part === activePart) {
                    btn.classList.remove('hidden-part');
                } else {
                    btn.classList.add('hidden-part');
                }
            });
        }
        
        function updateAnswerCount() {
            const answeredCount = document.querySelectorAll('.number-btn.answered').length;
            answeredCountSpan.textContent = answeredCount;
        }
        
        // Track if server save is in progress
        let isSavingToServer = false;

        function saveAllAnswers() {
            const formData = new FormData(form);
            const answers = {};

            for (let [key, value] of formData.entries()) {
                if (key.startsWith('answers[') && value) {
                    answers[key] = value;
                }
            }

            // Save to localStorage (immediate backup)
            try {
                localStorage.setItem(`testAnswers_${testConfig.attemptId}`, JSON.stringify(answers));
            } catch (e) {
                console.warn('Could not save answers to localStorage:', e);
            }

            // Save to server (persistent, crash-safe)
            saveToServer(answers);
        }

        function saveToServer(answers) {
            if (!testConfig.attemptId || Object.keys(answers).length === 0 || isSavingToServer) {
                return;
            }

            isSavingToServer = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch(`/student/test/listening/auto-save/${testConfig.attemptId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ answers: answers }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`Server auto-save: ${data.answers_count} answers at ${data.saved_at}`);
                    showSaveIndicator('success');
                }
            })
            .catch(error => {
                console.error('Server auto-save error:', error);
                showSaveIndicator('error');
            })
            .finally(() => {
                isSavingToServer = false;
            });
        }

        function showSaveIndicator(status) {
            const existing = document.getElementById('save-indicator');
            if (existing) existing.remove();

            const indicator = document.createElement('div');
            indicator.id = 'save-indicator';
            indicator.style.cssText = `
                position: fixed; bottom: 20px; right: 20px;
                padding: 8px 16px; border-radius: 8px;
                font-size: 12px; font-weight: 500; z-index: 9999;
                opacity: 0; transition: opacity 0.3s ease;
                background: ${status === 'success' ? '#10b981' : '#ef4444'};
                color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            `;
            indicator.textContent = status === 'success' ? 'Saved' : 'Save failed';
            document.body.appendChild(indicator);

            setTimeout(() => indicator.style.opacity = '1', 10);
            setTimeout(() => {
                indicator.style.opacity = '0';
                setTimeout(() => indicator.remove(), 300);
            }, 2000);
        }

        function loadSavedAnswers() {
            // First try to load from server
            loadFromServer().then(serverAnswers => {
                if (serverAnswers && Object.keys(serverAnswers).length > 0) {
                    console.log('Loaded answers from server:', Object.keys(serverAnswers).length);
                    applyAnswers(serverAnswers);
                } else {
                    loadFromLocalStorage();
                }
            }).catch(error => {
                console.warn('Server load failed, using localStorage:', error);
                loadFromLocalStorage();
            });
        }

        function loadFromServer() {
            return new Promise((resolve, reject) => {
                fetch(`/student/test/listening/draft-answers/${testConfig.attemptId}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.answers) {
                        resolve(data.answers);
                    } else {
                        resolve(null);
                    }
                })
                .catch(error => reject(error));
            });
        }

        function loadFromLocalStorage() {
            try {
                const savedAnswers = localStorage.getItem(`testAnswers_${testConfig.attemptId}`);
                if (savedAnswers) {
                    const answers = JSON.parse(savedAnswers);
                    console.log('Loaded answers from localStorage:', Object.keys(answers).length);
                    applyAnswers(answers);
                }
            } catch (e) {
                console.error('Error loading from localStorage:', e);
            }
        }

        function applyAnswers(answers) {
            console.log('Applying answers:', Object.keys(answers).length);

            Object.keys(answers).forEach(key => {
                const value = answers[key];
                if (!value) return;

                // Check if it's a drag-drop answer (contains [zone_ or format answers[id_index])
                if (key.includes('[zone_') || /answers\[\d+_\d+\]/.test(key)) {
                    // Handle drag-drop questions
                    applyDragDropAnswer(key, value);
                    return;
                }

                const input = document.querySelector(`[name="${key}"]`);

                if (input) {
                    if (input.type === 'radio') {
                        const radio = document.querySelector(`[name="${key}"][value="${value}"]`);
                        if (radio) {
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change'));
                        }
                    } else if (input.type === 'checkbox') {
                        // Handle multiple choice checkboxes
                        if (Array.isArray(value)) {
                            value.forEach(v => {
                                const checkbox = document.querySelector(`[name="${key}"][value="${v}"]`);
                                if (checkbox) {
                                    checkbox.checked = true;
                                    checkbox.dispatchEvent(new Event('change'));
                                }
                            });
                        } else {
                            input.checked = true;
                            input.dispatchEvent(new Event('change'));
                        }
                    } else {
                        input.value = value;
                        input.dispatchEvent(new Event('change'));
                    }
                } else {
                    // Hidden input might not exist yet, create it
                    const form = document.getElementById('listening-form');
                    if (form) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = key;
                        hiddenInput.value = value;
                        form.appendChild(hiddenInput);
                    }
                }
            });

            // Re-initialize answer count for drag-drop questions
            setTimeout(() => {
                if (window.ListeningDragDrop && typeof window.ListeningDragDrop.initializeAnswerCount === 'function') {
                    window.ListeningDragDrop.initializeAnswerCount();
                }
                updateAnswerCount();
            }, 300);
        }

        // Apply drag-drop answer and update visual state
        function applyDragDropAnswer(inputName, value) {
            console.log('Applying drag-drop answer:', inputName, '=', value);

            // Parse the input name to find the question ID and zone
            // Format 1: answers[questionId][zone_X]
            // Format 2: answers[questionId_X] (matching questions)
            let match = inputName.match(/answers\[(\d+)\]\[zone_(\d+)\]/);
            let questionId, zoneNumber, isMatchingFormat = false;

            if (match) {
                questionId = match[1];
                zoneNumber = match[2];
            } else {
                // Try matching format: answers[questionId_X]
                match = inputName.match(/answers\[(\d+)_(\d+)\]/);
                if (match) {
                    questionId = match[1];
                    zoneNumber = match[2];
                    isMatchingFormat = true;
                } else {
                    console.warn('Could not parse drag-drop input name:', inputName);
                    return;
                }
            }

            // Find or create the hidden input
            let hiddenInput = document.querySelector(`input[name="${inputName}"]`);
            if (!hiddenInput) {
                const form = document.getElementById('listening-form');
                if (form) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = inputName;
                    form.appendChild(hiddenInput);
                }
            }
            if (hiddenInput) {
                hiddenInput.value = value;
            }

            // Find the drop box for this zone (try both selectors)
            let dropBox = document.querySelector(`.drop-box[data-question-id="${questionId}"][data-zone-number="${zoneNumber}"]`);
            if (!dropBox) {
                dropBox = document.querySelector(`.drop-box[data-question-id="${questionId}"][data-zone-index="${zoneNumber}"]`);
            }
            if (!dropBox && isMatchingFormat) {
                // For matching questions, try finding by index
                dropBox = document.querySelector(`.drop-box[data-question-id="${questionId}"][data-index="${zoneNumber}"]`);
            }

            if (dropBox) {
                // Clear existing content
                dropBox.innerHTML = '';
                dropBox.classList.add('has-answer');
                dropBox.setAttribute('draggable', 'true');

                // Add the answer text
                const answerSpan = document.createElement('span');
                answerSpan.className = 'answer-text';
                answerSpan.textContent = value;
                dropBox.appendChild(answerSpan);

                // Update navigation button
                const questionNumber = dropBox.dataset.questionNumber;
                if (questionNumber) {
                    const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                    if (navButton) {
                        navButton.classList.add('answered');
                    }
                }

                // Hide the placed option from options list
                const optionToHide = document.querySelector(`.draggable-option[data-option-value="${value}"]`);
                if (optionToHide) {
                    optionToHide.style.visibility = 'hidden';
                    optionToHide.style.opacity = '0';
                    optionToHide.classList.add('placed');
                }

                console.log('Restored drag-drop:', questionId, 'zone', zoneNumber, '=', value);
            } else {
                console.warn('Drop box not found for:', questionId, zoneNumber);
            }
        }
        
        // ========== Notes & Highlight System (Complete) ==========
        const AnnotationSystem = {
            init() {
                this.currentMenu = null;
                this.currentRange = null;
                this.noteModal = null;
                this.notesPanel = null;
                
                this.createNoteModal();
                this.createNotesPanel();
                this.setupAnnotationHandlers();
                this.restoreAnnotations();
                this.updateNotesCount();
            },
            
            createNoteModal() {
                const modal = document.createElement('div');
                modal.id = 'note-modal';
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.5);
                    display: none;
                    align-items: center;
                    justify-content: center;
                    z-index: 100000;
                `;
                
                modal.innerHTML = `
                    <div style="
                        background: white;
                        border-radius: 8px;
                        width: 90%;
                        max-width: 450px;
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                    ">
                        <div style="padding: 16px; border-bottom: 1px solid #e5e7eb;">
                            <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #111827;">Add Note</h3>
                            <p style="margin: 6px 0 0 0; font-size: 13px; color: #6b7280;" id="selected-text-preview"></p>
                        </div>
                        <div style="padding: 16px;">
                            <textarea 
                                id="note-textarea"
                                placeholder="Type your note here..."
                                style="
                                    width: 100%;
                                    min-height: 100px;
                                    padding: 10px;
                                    border: 1px solid #e5e7eb;
                                    border-radius: 6px;
                                    font-size: 14px;
                                    resize: vertical;
                                    font-family: inherit;
                                    box-sizing: border-box;
                                "
                            ></textarea>
                            <div style="margin-top: 6px; text-align: right; font-size: 12px; color: #9ca3af;">
                                <span id="char-count">0</span>/500
                            </div>
                        </div>
                        <div style="
                            padding: 12px 16px;
                            background: #f9fafb;
                            border-top: 1px solid #e5e7eb;
                            display: flex;
                            justify-content: flex-end;
                            gap: 10px;
                            border-radius: 0 0 8px 8px;
                        ">
                            <button id="close-note-modal-btn" style="
                                padding: 6px 16px;
                                border: 1px solid #e5e7eb;
                                background: white;
                                border-radius: 4px;
                                font-size: 13px;
                                cursor: pointer;
                                transition: all 0.2s;
                            ">Cancel</button>
                            <button id="save-note-btn" style="
                                padding: 6px 16px;
                                background: #3b82f6;
                                color: white;
                                border: none;
                                border-radius: 4px;
                                font-size: 13px;
                                cursor: pointer;
                                transition: all 0.2s;
                            ">Save Note</button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                this.noteModal = modal;
                
                // Setup event listeners
                const textarea = modal.querySelector('#note-textarea');
                const charCount = modal.querySelector('#char-count');
                textarea.addEventListener('input', () => {
                    const count = textarea.value.length;
                    charCount.textContent = count;
                    if (count > 500) {
                        textarea.value = textarea.value.substring(0, 500);
                        charCount.textContent = 500;
                    }
                });
                
                document.getElementById('close-note-modal-btn').addEventListener('click', () => {
                    this.closeNoteModal();
                });
                
                document.getElementById('save-note-btn').addEventListener('click', () => {
                    this.saveNote();
                });
            },
            
            createNotesPanel() {
                const panel = document.createElement('div');
                panel.id = 'notes-panel';
                panel.style.cssText = `
                    position: fixed;
                    top: 0;
                    right: -350px;
                    width: 350px;
                    height: 100%;
                    background: white;
                    box-shadow: -2px 0 4px rgba(0, 0, 0, 0.1);
                    transition: right 0.3s ease-out;
                    z-index: 99998;
                    display: flex;
                    flex-direction: column;
                    `;
                    panel.innerHTML = `
                <div style="
                    padding: 16px;
                    border-bottom: 1px solid #e5e7eb;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    background: #f8f9fa;
                ">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; flex: 1;">📝 Your Notes</h3>
                    <button id="close-notes-panel-btn" style="
                        background: none;
                        border: none;
                        font-size: 20px;
                        cursor: pointer;
                        color: #6b7280;
                        padding: 0;
                        width: 28px;
                        height: 28px;
                        border-radius: 4px;
                        transition: all 0.2s;
                    " onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='none'">×</button>
                </div>
                <div id="notes-list" style="
                    flex: 1;
                    overflow-y: auto;
                    padding: 12px;
                "></div>
            `;
            
            document.body.appendChild(panel);
            this.notesPanel = panel;
            
            document.getElementById('close-notes-panel-btn').addEventListener('click', () => {
                this.closeNotesPanel();
            });
        },
        
        setupAnnotationHandlers() {
                // FIXED: Allow annotation in more text areas (matching Reading test pattern)
                const ALLOWED_SELECTORS = [
                    '.question-text',        // Main question text
                    '.part-instruction',     // Part instructions
                    '.question-instruction', // Question instructions
                    '.question-instructions',// Instructions (alternate class)
                    '.question-group-header',// Group headers
                    '.question-content',     // Question content
                    '.question-item',        // Question items
                    '.part-section',         // Part sections
                    '.part-questions',       // Part questions container
                    '.questions-container',  // Questions container
                    '.listening-test-container', // Main container
                    // ADDED: More selectors for better coverage
                    '.options-list',         // Options container (text)
                    '.option-item',          // Option items (text)
                    '.option-label',         // Option labels (text)
                    '.option-text',          // Option text
                    '.radio-option',         // Radio option TEXT
                    '.checkbox-option',      // Checkbox option TEXT
                    '.single-choice-options',// Single choice container
                    '.matching-question',    // Matching questions text
                    '.form-label',           // Form labels
                    '.question-block',       // Question blocks
                    '.question-wrapper',     // Question wrappers
                    'p',                     // All paragraphs
                    'span',                  // All spans
                    'li',                    // List items
                    'td',                    // Table cells
                    'th',                    // Table headers
                    'main',                  // Main content
                ];
                
                // ONLY block actual input controls - NOT their text content
                const FORBIDDEN_SELECTORS = [
                    'input',                 // All input types
                    'select',                // Dropdown menus
                    'textarea',              // Text areas
                    'button',                // Buttons
                    '.drop-box',             // Drag drop zones (interactive)
                    '.draggable-option',     // Draggable items (interactive)
                ];
                
                // Only prevent text selection on actual input controls
                document.addEventListener('selectstart', (e) => {
                    const target = e.target;

                    // Check if selecting directly on a forbidden input element
                    const isForbidden = FORBIDDEN_SELECTORS.some(selector => {
                        try {
                            return target.matches(selector);
                        } catch (err) {
                            return false;
                        }
                    });

                    if (isForbidden) {
                        e.preventDefault();
                        return false;
                    }
                });
                
                document.addEventListener('mouseup', (e) => {
                    // Skip if clicking on annotation menu, note modal, or notes panel
                    if (e.target.closest('#annotation-menu') || 
                        e.target.closest('#note-modal') || 
                        e.target.closest('#notes-panel')) {
                    return;
                }
                    
                    // Skip if right click
                    if (e.button === 2) {
                        return;
                    }
                    
                    setTimeout(() => {
                        const selection = window.getSelection();
                        const selectedText = selection.toString().trim();
                        
                        if (selectedText && selectedText.length >= 3) {
                            const range = selection.getRangeAt(0);
                            const container = range.commonAncestorContainer;
                            const element = container.nodeType === 3 ? container.parentElement : container;
                            
                            // Check if selection is DIRECTLY on a forbidden element (actual input/button)
                            // Only check the element itself, not ancestors - to allow highlighting text near inputs
                            const isForbidden = FORBIDDEN_SELECTORS.some(selector => {
                                try {
                                    return element.matches(selector);
                                } catch (err) {
                                    return false;
                                }
                            });

                            if (isForbidden) {
                                this.hideMenu();
                                return;
                            }
                            
                            // VERY PERMISSIVE: Allow highlighting anywhere in the test page
                            // Only block if not on the page at all (which shouldn't happen)
                            const isInPage = element.closest('body') !== null;

                            if (!isInPage) {
                                this.hideMenu();
                                return;
                            }
                            
                            // Check for inline elements - silently skip if selection includes actual inputs
                            const hasInlineElements = range.cloneContents().querySelectorAll('input, select, textarea').length > 0;
                            if (hasInlineElements) {
                                this.hideMenu();
                                window.getSelection().removeAllRanges();
                                return;
                            }
                            
                            const rect = range.getBoundingClientRect();
                            this.currentRange = range;
                            this.showMenu(rect, selectedText);
                        } else {
                            this.hideMenu();
                        }
                    }, 10);
                });
        
        // Hide menu on scroll
        document.addEventListener('scroll', () => {
            this.hideMenu();
        }, true);
        },
        
        showMenu(rect, selectedText) {
            this.hideMenu();
            
            const menu = document.createElement('div');
            menu.id = 'annotation-menu';
            menu.style.cssText = `
                position: fixed;
                top: ${rect.top - 50}px;
                left: ${rect.left + (rect.width / 2) - 80}px;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                padding: 6px;
                display: flex;
                gap: 6px;
                z-index: 99999;
            `;
            
            // Create buttons with proper event handlers
            const noteBtn = document.createElement('button');
            noteBtn.style.cssText = `
                padding: 6px 12px;
                border: none;
                background: #3b82f6;
                color: white;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
                display: flex;
                align-items: center;
                gap: 4px;
                transition: all 0.2s;
            `;
            noteBtn.innerHTML = `
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Note
            `;
            // Hover effects
            noteBtn.onmouseover = () => { noteBtn.style.background = '#2563eb'; };
            noteBtn.onmouseout = () => { noteBtn.style.background = '#3b82f6'; };

            // FIXED: Prevent mousedown from clearing text selection
            noteBtn.onmousedown = (e) => { e.preventDefault(); e.stopPropagation(); };
            noteBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.showNoteModal();
            };

            const highlightBtn = document.createElement('button');
            highlightBtn.style.cssText = `
                padding: 6px 12px;
                border: none;
                background: #fbbf24;
                color: white;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
                display: flex;
                align-items: center;
                gap: 4px;
                transition: all 0.2s;
            `;
            highlightBtn.innerHTML = `
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Highlight
            `;
            // Hover effects
            highlightBtn.onmouseover = () => { highlightBtn.style.background = '#d97706'; };
            highlightBtn.onmouseout = () => { highlightBtn.style.background = '#fbbf24'; };

            // FIXED: Prevent mousedown from clearing text selection
            highlightBtn.onmousedown = (e) => { e.preventDefault(); e.stopPropagation(); };
            highlightBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.highlightText();
            };

            menu.appendChild(noteBtn);
            menu.appendChild(highlightBtn);
            document.body.appendChild(menu);
            this.currentMenu = menu;
        },
        
        hideMenu() {
            if (this.currentMenu) {
                this.currentMenu.remove();
                this.currentMenu = null;
            }
        },
        
        showNoteModal() {
            const selectedText = this.currentRange.toString();
            document.getElementById('selected-text-preview').textContent = 
                `"${selectedText.substring(0, 40)}${selectedText.length > 40 ? '...' : ''}"`;
            this.noteModal.style.display = 'flex';
            setTimeout(() => {
                document.getElementById('note-textarea').focus();
            }, 100);
            this.hideMenu();
        },
        
        closeNoteModal() {
            this.noteModal.style.display = 'none';
            document.getElementById('note-textarea').value = '';
            document.getElementById('char-count').textContent = '0';
        },
        
        saveNote() {
            const noteText = document.getElementById('note-textarea').value.trim();
            if (noteText && this.currentRange) {
                const selectedText = this.currentRange.toString();
                
                // Check if selection contains input elements
                const container = this.currentRange.commonAncestorContainer;
                const parentElement = container.nodeType === 3 ? container.parentElement : container;
                
                // Check if parent contains inputs
                if (parentElement.querySelector('input, select, textarea, .inline-blank, .dropdown, .drop-box')) {
                    showToast('Cannot add note to text that contains input fields. Please select only plain text.', 'warning');
                    this.closeNoteModal();
                    window.getSelection().removeAllRanges();
                    return;
                }
                
                // Apply note styling
                const span = document.createElement('span');
                span.className = 'note-text';
                span.textContent = selectedText;
                span.title = noteText;
                span.dataset.note = noteText;
                span.dataset.noteId = Date.now();
                
                // Add click handler
                span.onclick = () => this.showNoteTooltip(span, noteText);
                
                try {
                    this.currentRange.deleteContents();
                    this.currentRange.insertNode(span);
                } catch (error) {
                    console.error('Error applying note:', error);
                    showToast('Cannot add note to this selection. Please try selecting only plain text.', 'error');
                }
                
                // Save to localStorage
                this.saveAnnotation('note', selectedText, noteText);
                
                this.closeNoteModal();
                window.getSelection().removeAllRanges();
            }
        },
        
        highlightText() {
            if (this.currentRange) {
                // FIXED: Preserve original text including whitespace
                const originalText = this.currentRange.toString();
                const trimmedText = originalText.trim();

                // Don't highlight if empty or just spaces
                if (!trimmedText) {
                    this.hideMenu();
                    return;
                }

                // FIXED: Check if selection spans multiple block elements (prevents merging options)
                const startContainer = this.currentRange.startContainer;
                const endContainer = this.currentRange.endContainer;
                const startElement = startContainer.nodeType === 3 ? startContainer.parentElement : startContainer;
                const endElement = endContainer.nodeType === 3 ? endContainer.parentElement : endContainer;

                // Find the nearest block-level parent for both start and end
                // STRICT: Prioritize option classes to prevent multi-option selection
                const blockClasses = [
                    // Option-level classes (highest priority - each option is a block)
                    'ielts-option', 'option-item', 'radio-option', 'checkbox-option',
                    'single-choice-option', 'multiple-choice-option',
                    'matching-option', 'draggable-option', 'answer-option',
                    // Container classes
                    'drop-box', 'question-item', 'form-completion-row',
                    'part-question', 'listening-question', 'answer-row', 'option-label',
                    'form-group', 'input-group', 'question-block',
                    // Additional for safety
                    'ielts-options', 'options-list', 'question-content'
                ];
                const blockElements = ['LI', 'TD', 'TH', 'TR'];

                const findBlockParent = (el) => {
                    while (el && el.parentElement) {
                        // First check classes (prioritize option boundaries)
                        if (el.classList && blockClasses.some(cls => el.classList.contains(cls))) {
                            return el;
                        }
                        // Then check element types (but NOT DIV/P/LABEL to avoid false positives)
                        if (blockElements.includes(el.tagName)) {
                            return el;
                        }
                        el = el.parentElement;
                    }
                    return null;
                };

                const startBlock = findBlockParent(startElement);
                const endBlock = findBlockParent(endElement);

                // If selection spans different block elements, silently prevent (no warning)
                if (startBlock && endBlock && startBlock !== endBlock) {
                    this.hideMenu();
                    window.getSelection().removeAllRanges();
                    return;
                }

                // Create highlight span - preserve exact selection without modifying text
                const span = document.createElement('span');
                span.className = 'highlighted-text';
                span.title = 'Click to remove';
                span.style.cssText = 'background-color: #fde047; cursor: pointer; border-radius: 2px;';

                // Store for removal
                const textForRemoval = trimmedText;

                // Add click handler for DIRECT removal (NO confirmation)
                span.onclick = (e) => {
                    e.stopPropagation();
                    // Replace with original contents (preserves exact text)
                    const parent = span.parentNode;
                    while (span.firstChild) {
                        parent.insertBefore(span.firstChild, span);
                    }
                    parent.removeChild(span);
                    parent.normalize(); // Merge adjacent text nodes
                    this.removeAnnotation('highlight', textForRemoval);
                };

                // STRICT: Adjust range to exclude leading/trailing whitespace
                const adjustRangeToTrimmedText = (range) => {
                    const text = range.toString();
                    const leadingSpaces = text.match(/^\s*/)[0].length;
                    const trailingSpaces = text.match(/\s*$/)[0].length;

                    if (leadingSpaces > 0) {
                        try {
                            range.setStart(range.startContainer, range.startOffset + leadingSpaces);
                        } catch (e) {
                            // If adjustment fails, continue with original
                        }
                    }
                    if (trailingSpaces > 0 && trailingSpaces < text.length) {
                        try {
                            range.setEnd(range.endContainer, range.endOffset - trailingSpaces);
                        } catch (e) {
                            // If adjustment fails, continue with original
                        }
                    }
                    return range;
                };

                // Trim whitespace from selection
                adjustRangeToTrimmedText(this.currentRange);

                try {
                    // FIXED: Use surroundContents to wrap selection without changing text
                    this.currentRange.surroundContents(span);
                    this.saveAnnotation('highlight', trimmedText, 'yellow');
                } catch (error) {
                    // Fallback: Extract and insert if surroundContents fails
                    try {
                        const contents = this.currentRange.extractContents();
                        span.appendChild(contents);
                        this.currentRange.insertNode(span);
                        this.saveAnnotation('highlight', trimmedText, 'yellow');
                    } catch (e) {
                        console.error('Error applying highlight:', e);
                    }
                }

                window.getSelection().removeAllRanges();
                this.hideMenu();
            }
        },
        
        removeAnnotation(type, text) {
            let annotations = JSON.parse(localStorage.getItem(`annotations_${testConfig.attemptId}`) || '[]');
            annotations = annotations.filter(a => !(a.type === type && a.text === text));
            localStorage.setItem(`annotations_${testConfig.attemptId}`, JSON.stringify(annotations));
            
            if (type === 'note') {
                this.updateNotesCount();
            }
        },
        
        showNoteTooltip(element, noteText) {
            // Remove existing tooltip
            const existingTooltip = document.getElementById('note-tooltip');
            if (existingTooltip) existingTooltip.remove();
            
            const tooltip = document.createElement('div');
            tooltip.id = 'note-tooltip';
            tooltip.style.cssText = `
                position: absolute;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                padding: 10px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                max-width: 250px;
                z-index: 99999;
                font-size: 13px;
            `;
            
            tooltip.innerHTML = `
                <div style="color: #374151; margin-bottom: 6px;">${noteText}</div>
                <div style="font-size: 11px; color: #9ca3af;">Click outside to close</div>
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            tooltip.style.top = `${rect.bottom + window.scrollY + 4}px`;
            tooltip.style.left = `${rect.left + window.scrollX}px`;
            
            // Remove on click outside
            setTimeout(() => {
                document.addEventListener('click', function removeTooltip(e) {
                    if (!tooltip.contains(e.target) && e.target !== element) {
                        tooltip.remove();
                        document.removeEventListener('click', removeTooltip);
                    }
                });
            }, 100);
        },
        
        saveAnnotation(type, text, data) {
            const annotations = JSON.parse(localStorage.getItem(`annotations_${testConfig.attemptId}`) || '[]');
            annotations.push({
                type: type,
                text: text,
                data: data,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem(`annotations_${testConfig.attemptId}`, JSON.stringify(annotations));
            this.updateNotesCount();
        },
        
        restoreAnnotations() {
            const annotations = JSON.parse(localStorage.getItem(`annotations_${testConfig.attemptId}`) || '[]');
            
            annotations.forEach(annotation => {
                this.findAndStyleText(annotation.text, (span) => {
                    if (annotation.type === 'note') {
                        span.className = 'note-text';
                        span.dataset.note = annotation.data;
                        span.dataset.noteId = Date.now();
                        span.title = 'Click to view note';
                        span.onclick = () => this.showNoteTooltip(span, annotation.data);
                    } else if (annotation.type === 'highlight') {
                        span.className = 'highlighted-text';
                        span.title = 'Click to remove highlight';
                        span.onclick = (e) => {
                            e.stopPropagation();
                            if (confirm('Remove this highlight?')) {
                                const text = span.textContent;
                                span.style.transition = 'background-color 0.3s ease';
                                span.style.backgroundColor = 'transparent';
                                
                                setTimeout(() => {
                                    span.replaceWith(document.createTextNode(text));
                                    this.removeAnnotation('highlight', annotation.text);
                                }, 300);
                            }
                        };
                    }
                });
            });
            
            this.updateNotesCount();
        },
        
        findAndStyleText(searchText, styleCallback) {
            const container = document.querySelector('.content-area');
            const walker = document.createTreeWalker(
                container,
                NodeFilter.SHOW_TEXT,
                {
                    acceptNode: function(node) {
                        // Skip if parent is already highlighted or contains input/select elements
                        const parent = node.parentElement;
                        if (!parent) return NodeFilter.FILTER_REJECT;
                        
                        // Skip if parent is already a highlight or note
                        if (parent.classList.contains('note-text') || 
                            parent.classList.contains('highlighted-text')) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        
                        // Skip if parent contains input elements or is an input itself
                        if (parent.tagName === 'INPUT' || 
                            parent.tagName === 'SELECT' || 
                            parent.tagName === 'TEXTAREA' ||
                            parent.tagName === 'BUTTON' ||
                            parent.classList.contains('inline-blank') ||
                            parent.classList.contains('dropdown') ||
                            parent.classList.contains('drop-box') ||
                            parent.classList.contains('draggable-option')) {
                            return NodeFilter.FILTER_REJECT;
                        }

                        // Skip if parent contains inputs/selects as children
                        if (parent.querySelector('input, select, textarea, .inline-blank, .dropdown, .drop-box')) {
                            return NodeFilter.FILTER_REJECT;
                        }

                        return NodeFilter.FILTER_ACCEPT;
                    }
                },
                false
            );

            let node;
            while (node = walker.nextNode()) {
                const text = node.textContent;
                const index = text.indexOf(searchText);

                if (index !== -1) {
                    const parent = node.parentNode;

                    // Double check - don't modify if parent has inputs
                    if (parent.querySelector('input, select, textarea, .inline-blank, .dropdown')) {
                        continue;
                    }
                    
                    // Split the text node
                    const before = document.createTextNode(text.substring(0, index));
                    const after = document.createTextNode(text.substring(index + searchText.length));
                    
                    // Apply styling
                    const span = document.createElement('span');
                    span.textContent = searchText;
                    styleCallback(span);
                    
                    // Replace in DOM
                    parent.insertBefore(before, node);
                    parent.insertBefore(span, node);
                    parent.insertBefore(after, node);
                    parent.removeChild(node);
                    
                    break;
                }
            }
        },
        
        updateNotesCount() {
            const annotations = JSON.parse(localStorage.getItem(`annotations_${testConfig.attemptId}`) || '[]');
            const notesCount = annotations.filter(a => a.type === 'note').length;
            const countElement = document.getElementById('notes-count');
            
            if (countElement) {
                if (notesCount > 0) {
                    countElement.textContent = notesCount;
                    countElement.style.display = 'inline-flex';
                } else {
                    countElement.style.display = 'none';
                }
            }
        },
        
        openNotesPanel() {
            this.updateNotesList();
            this.notesPanel.style.right = '0';
        },
        
        closeNotesPanel() {
            this.notesPanel.style.right = '-350px';
        },
        
        updateNotesList() {
            const notesList = document.getElementById('notes-list');
            const annotations = JSON.parse(localStorage.getItem(`annotations_${testConfig.attemptId}`) || '[]');
            const notes = annotations.filter(a => a.type === 'note');
            
            if (notes.length === 0) {
                notesList.innerHTML = `
                    <div style="text-align: center; color: #9ca3af; padding: 30px;">
                        <div style="font-size: 36px; margin-bottom: 12px;">📝</div>
                        <p style="font-size: 14px; margin-bottom: 6px;">No notes yet!</p>
                        <p style="font-size: 12px; margin-top: 6px;">Select text and add notes to see them here.</p>
                    </div>
                `;
            } else {
                notesList.innerHTML = notes.map((note, index) => `
                    <div style="
                        background: #f9fafb;
                        border: 1px solid #e5e7eb;
                        border-radius: 6px;
                        padding: 12px;
                        margin-bottom: 10px;
                        position: relative;
                        transition: all 0.2s ease;
                    " data-note-index="${index}">
                        <button class="delete-note-btn" data-note-text="${encodeURIComponent(note.text)}" data-note-timestamp="${note.timestamp}" style="
                            position: absolute;
                            top: 8px;
                            right: 8px;
                            background: #fee2e2;
                            border: none;
                            border-radius: 3px;
                            padding: 3px 6px;
                            cursor: pointer;
                            color: #dc2626;
                            font-size: 11px;
                            transition: all 0.2s;
                        ">Delete</button>
                        <div style="
                            font-size: 12px;
                            color: #6b7280;
                            font-style: italic;
                            margin-bottom: 6px;
                            padding: 6px;
                            background: white;
                            border-radius: 3px;
                            margin-right: 50px;
                            border-left: 2px solid #3b82f6;
                        ">"${note.text.substring(0, 80)}${note.text.length > 80 ? '...' : ''}"</div>
                        <div style="font-size: 13px; color: #111827; line-height: 1.4; margin-bottom: 6px;">${note.data}</div>
                        <div style="
                            margin-top: 8px;
                            font-size: 11px;
                            color: #9ca3af;
                        ">📅 ${new Date(note.timestamp).toLocaleString()}</div>
                    </div>
                `).join('');
                
                // Add event listeners to delete buttons
                notesList.querySelectorAll('.delete-note-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const text = decodeURIComponent(btn.dataset.noteText);
                        const timestamp = btn.dataset.noteTimestamp;
                        this.deleteNote(text, timestamp);
                    });
                });
            }
        },
        
        deleteNote(text, timestamp) {
            if (confirm('Are you sure you want to delete this note?')) {
                let annotations = JSON.parse(localStorage.getItem(`annotations_${testConfig.attemptId}`) || '[]');
                annotations = annotations.filter(a => !(a.type === 'note' && a.text === text && a.timestamp === timestamp));
                localStorage.setItem(`annotations_${testConfig.attemptId}`, JSON.stringify(annotations));
                
                // Remove from DOM
                const noteElements = document.querySelectorAll('.note-text');
                noteElements.forEach(el => {
                    if (el.textContent === text) {
                        const parent = el.parentNode;
                        parent.replaceChild(document.createTextNode(text), el);
                    }
                });
                
                this.updateNotesList();
                this.updateNotesCount();
            }
        }
    };
    
    // Update notes button handler
    notesBtn.addEventListener('click', function() {
        AnnotationSystem.openNotesPanel();
    });
    
    // ========== Question Navigation Arrows ==========
    const prevQuestionBtn = document.getElementById('prev-question-btn');
    const nextQuestionBtn = document.getElementById('next-question-btn');
    let currentQuestionIndex = 0;
    const totalQuestionsNav = document.querySelectorAll('.number-btn').length;
    
    function updateArrowButtons() {
        prevQuestionBtn.disabled = currentQuestionIndex === 0;
        nextQuestionBtn.disabled = currentQuestionIndex === totalQuestionsNav - 1;
    }
    
    function navigateToQuestion(index) {
        if (index < 0 || index >= totalQuestionsNav) return;
        
        currentQuestionIndex = index;
        const targetButton = numberButtons[index];
        if (targetButton) {
            targetButton.click();
        }
            updateArrowButtons();
        }
        
        prevQuestionBtn.addEventListener('click', function() {
            navigateToQuestion(currentQuestionIndex - 1);
        });
        
        nextQuestionBtn.addEventListener('click', function() {
            navigateToQuestion(currentQuestionIndex + 1);
        });
        
        // Update current index when clicking number buttons
        numberButtons.forEach((button, index) => {
            button.addEventListener('click', function() {
                currentQuestionIndex = index;
                updateArrowButtons();
            });
        });
        
        // ========== Drag & Drop - Now handled by listening-drag-drop.js ==========
        function initializeDragAndDrop() {
            // New drag & drop system is automatically initialized
            // by listening-drag-drop.js file
            console.log('Drag & Drop initialized via listening-drag-drop.js');
            
            // Fallback for old matching questions if needed
            const draggableOptions = document.querySelectorAll('.draggable-option');
            const dropBoxes = document.querySelectorAll('.drop-box');
            
            console.log('Found elements:', { 
                draggableOptions: draggableOptions.length, 
                dropBoxes: dropBoxes.length 
            });
            
            // Setup draggable options
            draggableOptions.forEach(option => {
                option.addEventListener('dragstart', function(e) {
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', this.dataset.option);
                    e.dataTransfer.setData('option-letter', this.dataset.optionLetter);
                    e.dataTransfer.setData('full-text', this.innerHTML);
                    this.classList.add('dragging');
                });
                
                option.addEventListener('dragend', function() {
                    this.classList.remove('dragging');
                });
            });
            
            // Setup drop boxes
            dropBoxes.forEach(box => {
                box.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    this.classList.add('drag-over');
                });
                
                box.addEventListener('dragleave', function() {
                    this.classList.remove('drag-over');
                });
                
                box.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');
                    
                    const optionText = e.dataTransfer.getData('text/plain');
                    const fullText = e.dataTransfer.getData('full-text');
                    const questionId = this.dataset.questionId;
                    const index = this.dataset.index;
                    const questionNumber = this.dataset.questionNumber;
                    
                    // Check if box already has an answer
                    if (this.classList.contains('has-answer')) {
                        // Remove the old answer first
                        const oldAnswer = this.textContent.replace(/^[A-Z]\.\s/, '');
                        const oldOption = document.querySelector(`.draggable-option[data-option="${oldAnswer}"]`);
                        if (oldOption) {
                            oldOption.style.display = 'inline-block';
                            oldOption.classList.remove('placed');
                        }
                    }
                    
                    // Add new answer
                    this.innerHTML = fullText;
                    this.classList.add('has-answer');
                    
                    // Update hidden input
                    const hiddenInput = document.querySelector(`input[name="answers[${questionId}_${index}]"]`);
                    if (hiddenInput) {
                        hiddenInput.value = optionText;
                        
                        // Update navigation button
                        const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                        if (navButton) {
                            navButton.classList.add('answered');
                        }
                    }
                    
                    // Hide the dragged option
                    const sourceOption = document.querySelector(`.draggable-option[data-option="${optionText}"]`);
                    if (sourceOption) {
                        sourceOption.style.display = 'none';
                        sourceOption.classList.add('placed');
                    }
                    
                    // Make the answer draggable for removal
                    this.draggable = true;
                    setupAnswerDrag(this);
                    
                    saveAllAnswers();
                    updateAnswerCount();
                });
            });
        }
        
        function setupAnswerDrag(answerBox) {
            answerBox.addEventListener('dragstart', function(e) {
                if (!this.classList.contains('has-answer')) return;
                
                const answerText = this.textContent.replace(/^[A-Z]\.\s/, '');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('remove-answer', 'true');
                e.dataTransfer.setData('answer-text', answerText);
                this.style.opacity = '0.5';
            });
            
            answerBox.addEventListener('dragend', function(e) {
                this.style.opacity = '';
                
                // Always remove answer when dragged out
                if (this.classList.contains('has-answer')) {
                    const answerText = this.textContent.replace(/^[A-Z]\.\s/, '');
                    const questionNumber = this.dataset.questionNumber;
                    const questionId = this.dataset.questionId;
                    const index = this.dataset.index;
                    
                    // Clear the box
                    this.innerHTML = `<span class="placeholder-text">${questionNumber}</span>`;
                    this.classList.remove('has-answer');
                    this.draggable = false;
                    
                    // Clear hidden input
                    const hiddenInput = document.querySelector(`input[name="answers[${questionId}_${index}]"]`);
                    if (hiddenInput) {
                        hiddenInput.value = '';
                    }
                    
                    // Show the option again
                    const option = document.querySelector(`.draggable-option[data-option="${answerText}"]`);
                    if (option) {
                        option.style.display = 'inline-block';
                        option.classList.remove('placed');
                    }
                    
                    // Update navigation
                    const navButton = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
                    if (navButton) {
                        navButton.classList.remove('answered');
                    }
                    
                    saveAllAnswers();
                    updateAnswerCount();
                }
            });
        }
        
        // Initialize drag and drop on page load
        initializeDragAndDrop();
        
        // ========== Initialize ==========
        
        // Disable all browser assists on load
        document.querySelectorAll('input[type="text"], input[type="number"], textarea, select').forEach(input => {
            input.setAttribute('autocomplete', 'off');
            input.setAttribute('autocorrect', 'off');
            input.setAttribute('autocapitalize', 'off');
            input.setAttribute('spellcheck', 'false');
            
            // Disable right-click on input fields
            input.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                showToast('Right-click is disabled during the test', 'info');
                return false;
            });
            
            // Disable paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                showToast('Paste is not allowed during the test', 'warning');
                return false;
            });
            
            // Disable cut
            input.addEventListener('cut', function(e) {
                e.preventDefault();
                showToast('Cut is not allowed during the test', 'warning');
                return false;
            });
        });
        
        // Remove beforeunload handler when form is submitted
        const listeningForm = document.getElementById('listening-form');
        if (listeningForm) {
            listeningForm.addEventListener('submit', function(e) {
                window.removeEventListener('beforeunload', preventLeave);
                window.onbeforeunload = null;
                console.log('✅ beforeunload handler removed - form submitted');
            });
        }
        
        // Loading Screen → Audio Overlay Flow is now handled by the x-test-loading-screen component

        // Update initial visibility
        updateNumberButtonsVisibility('1');
        
        // Load saved answers
        loadSavedAnswers();
        
        // Initialize annotation system
        AnnotationSystem.init();
        
        // Initialize arrow buttons
        updateArrowButtons();
        
        // Periodically save answers
        setInterval(saveAllAnswers, 30000);
        
        // Update answer count
        updateAnswerCount();
    });
    </script>
    
    {{-- Prevent Back Navigation During Test --}}
    <script>
    // Prevent browser back button during test to prevent cheating
    (function() {
        // Push a dummy state to prevent going back
        history.pushState(null, null, location.href);

        window.addEventListener('popstate', function(event) {
            // Push state again to keep user on the test page
            history.pushState(null, null, location.href);

            // Show submit modal instead of allowing back navigation
            const submitModal = document.getElementById('submit-modal');
            if (submitModal) {
                submitModal.style.display = 'flex';
            }
        });

        // Also prevent on initial load
        window.addEventListener('load', function() {
            history.pushState(null, null, location.href);
        });
    })();
    </script>

    {{-- Disable Ctrl+F Find During Listening Test --}}
    <script>
    // ====================================
    // AGGRESSIVE Ctrl+F Find Disabler
    // ====================================
    
    // Method 1: keydown event (Primary)
    document.addEventListener('keydown', function(e) {
        // Cmd+F (Mac) or Ctrl+F (Windows)
        if ((e.ctrlKey === true || e.metaKey === true) && (e.key === 'f' || e.key === 'F')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.warn('❌ Find disabled - Cmd+F blocked');
            return false;
        }
    }, true);
    
    // Method 2: keyup event (Backup)
    document.addEventListener('keyup', function(e) {
        if ((e.ctrlKey === true || e.metaKey === true) && (e.key === 'f' || e.key === 'F')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.warn('❌ Find disabled - Cmd+F blocked (keyup)');
            return false;
        }
    }, true);
    
    // Method 3: Check for keyboard event with code
    document.addEventListener('keydown', function(e) {
        // F keyCode: 70, MetaLeft: 91, ControlLeft: 17
        if ((e.metaKey || e.ctrlKey) && e.keyCode === 70) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);
    
    // Method 4: Disable via window object
    window.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && (e.key === 'f' || e.keyCode === 70)) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    }, true);
    </script>
    @endpush
</x-test-layout>