<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome - IELTS Journey</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css'])

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            min-height: 100vh;
            color: #1a1a1a;
        }

        .onboarding-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .onboarding-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05),
                        0 10px 15px -3px rgba(0, 0, 0, 0.05),
                        0 20px 25px -5px rgba(0, 0, 0, 0.03);
            width: 100%;
            max-width: 540px;
            overflow: hidden;
            position: relative;
        }

        .onboarding-header {
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: #C8102E;
            letter-spacing: -0.5px;
            margin-bottom: 1.5rem;
        }

        .progress-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .progress-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e5e5e5;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .progress-dot.active {
            background: #C8102E;
            width: 24px;
            border-radius: 4px;
        }

        .progress-dot.completed {
            background: #C8102E;
        }

        .step-indicator {
            font-size: 0.75rem;
            color: #888;
            margin-top: 0.75rem;
        }

        .onboarding-content {
            padding: 2rem;
            min-height: 400px;
            position: relative;
        }

        .step {
            position: absolute;
            top: 2rem;
            left: 2rem;
            right: 2rem;
            opacity: 0;
            transform: translateX(30px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
            visibility: hidden;
        }

        .step.active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: auto;
            visibility: visible;
        }

        .step.exit {
            opacity: 0;
            transform: translateX(-30px);
        }

        .step-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .step-subtitle {
            font-size: 0.9375rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .option-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .option-card {
            border: 2px solid #e8e8e8;
            border-radius: 16px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
        }

        .option-card:hover {
            border-color: #C8102E;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(200, 16, 46, 0.1);
        }

        .option-card.selected {
            border-color: #C8102E;
            background: linear-gradient(135deg, rgba(200, 16, 46, 0.03) 0%, rgba(200, 16, 46, 0.06) 100%);
        }

        .option-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #f8f8f8;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            transition: all 0.3s ease;
        }

        .option-card.selected .option-icon {
            background: #C8102E;
        }

        .option-icon svg {
            width: 24px;
            height: 24px;
            stroke: #666;
            transition: all 0.3s ease;
        }

        .option-card.selected .option-icon svg {
            stroke: #fff;
        }

        .option-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
        }

        .option-desc {
            font-size: 0.8125rem;
            color: #888;
            line-height: 1.4;
        }

        .band-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .band-option {
            padding: 1rem 0.5rem;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.125rem;
            font-weight: 600;
            color: #333;
        }

        .band-option:hover {
            border-color: #C8102E;
            transform: translateY(-2px);
        }

        .band-option.selected {
            border-color: #C8102E;
            background: #C8102E;
            color: #fff;
        }

        .band-option.popular {
            position: relative;
        }

        .band-option.popular::after {
            content: 'Popular';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.625rem;
            font-weight: 500;
            background: #C8102E;
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .info-text {
            font-size: 0.8125rem;
            color: #888;
            text-align: center;
            margin-top: 1rem;
        }

        .timeline-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .timeline-option {
            padding: 1.25rem 1rem;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .timeline-option:hover {
            border-color: #C8102E;
            transform: translateY(-2px);
        }

        .timeline-option.selected {
            border-color: #C8102E;
            background: linear-gradient(135deg, rgba(200, 16, 46, 0.03) 0%, rgba(200, 16, 46, 0.06) 100%);
        }

        .timeline-icon {
            margin-bottom: 0.5rem;
        }

        .timeline-icon svg {
            width: 20px;
            height: 20px;
            stroke: #888;
        }

        .timeline-option.selected .timeline-icon svg {
            stroke: #C8102E;
        }

        .timeline-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        .date-picker-wrapper {
            margin-top: 1rem;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .date-picker-wrapper.show {
            opacity: 1;
            max-height: 80px;
        }

        .date-picker-wrapper label {
            font-size: 0.8125rem;
            color: #666;
            display: block;
            margin-bottom: 0.5rem;
        }

        .date-picker-wrapper input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .date-picker-wrapper input:focus {
            outline: none;
            border-color: #C8102E;
        }

        .summary-card {
            background: #fafafa;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-label {
            font-size: 0.875rem;
            color: #666;
        }

        .summary-value {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        .summary-value.highlight {
            color: #C8102E;
        }

        .onboarding-footer {
            padding: 1.5rem 2rem 2rem;
            display: flex;
            gap: 1rem;
        }

        .btn {
            flex: 1;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #666;
        }

        .btn-secondary:hover {
            background: #eee;
        }

        .btn-primary {
            background: #C8102E;
            color: #fff;
        }

        .btn-primary:hover {
            background: #a50d26;
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .skip-link {
            text-align: center;
            padding: 1rem;
        }

        .skip-link a {
            font-size: 0.875rem;
            color: #888;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .skip-link a:hover {
            color: #C8102E;
        }

        /* Welcome step special styling */
        .welcome-content {
            text-align: center;
            padding: 2rem 0;
        }

        .welcome-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, #C8102E 0%, #a50d26 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .welcome-icon svg {
            width: 40px;
            height: 40px;
            stroke: #fff;
        }

        .welcome-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.75rem;
        }

        .welcome-subtitle {
            font-size: 1rem;
            color: #666;
            line-height: 1.6;
            max-width: 360px;
            margin: 0 auto;
        }

        /* Animation for cards */
        @keyframes cardPulse {
            0%, 100% { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
            50% { box-shadow: 0 4px 6px -1px rgba(200, 16, 46, 0.1), 0 10px 15px -3px rgba(200, 16, 46, 0.1); }
        }

        .option-card.selected {
            animation: cardPulse 2s ease-in-out;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .onboarding-container {
                padding: 1rem;
            }

            .onboarding-card {
                border-radius: 20px;
            }

            .onboarding-header,
            .onboarding-content,
            .onboarding-footer {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .step {
                left: 1.5rem;
                right: 1.5rem;
            }

            .option-grid {
                grid-template-columns: 1fr;
            }

            .band-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 0.5rem;
            }

            .band-option {
                padding: 0.75rem 0.25rem;
                font-size: 1rem;
            }

            .timeline-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="onboarding-container">
        <div class="onboarding-card">
            <div class="onboarding-header">
                <div class="logo">IELTS Journey</div>
                <div class="progress-bar">
                    <div class="progress-dot" id="dot-1"></div>
                    <div class="progress-dot" id="dot-2"></div>
                    <div class="progress-dot" id="dot-3"></div>
                    <div class="progress-dot" id="dot-4"></div>
                </div>
                <div class="step-indicator" id="step-indicator">Step 1 of 4</div>
            </div>

            <div class="onboarding-content">
                <!-- Step 1: Welcome -->
                <div class="step active" id="step-1">
                    <div class="welcome-content">
                        <div class="welcome-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>
                        </div>
                        <h1 class="welcome-title">Welcome, {{ explode(' ', auth()->user()->name)[0] }}</h1>
                        <p class="welcome-subtitle">Let's personalize your IELTS preparation journey. This will only take a minute.</p>
                    </div>
                </div>

                <!-- Step 2: Exam Type -->
                <div class="step" id="step-2">
                    <h2 class="step-title">Which IELTS are you preparing for?</h2>
                    <p class="step-subtitle">Select the exam type that matches your goal.</p>

                    <div class="option-grid">
                        <div class="option-card" data-value="academic" onclick="selectExamType(this)">
                            <div class="option-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                </svg>
                            </div>
                            <div class="option-title">Academic</div>
                            <div class="option-desc">For university admission & professional registration</div>
                        </div>

                        <div class="option-card" data-value="general" onclick="selectExamType(this)">
                            <div class="option-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                            </div>
                            <div class="option-title">General Training</div>
                            <div class="option-desc">For immigration & work visa applications</div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Target Band -->
                <div class="step" id="step-3">
                    <h2 class="step-title">What's your target band score?</h2>
                    <p class="step-subtitle">Choose the overall band score you want to achieve.</p>

                    <div class="band-grid">
                        <div class="band-option" data-value="5.5" onclick="selectBand(this)">5.5</div>
                        <div class="band-option" data-value="6.0" onclick="selectBand(this)">6.0</div>
                        <div class="band-option" data-value="6.5" onclick="selectBand(this)">6.5</div>
                        <div class="band-option popular" data-value="7.0" onclick="selectBand(this)">7.0</div>
                        <div class="band-option" data-value="7.5" onclick="selectBand(this)">7.5</div>
                        <div class="band-option" data-value="8.0" onclick="selectBand(this)">8.0</div>
                        <div class="band-option" data-value="8.5" onclick="selectBand(this)">8.5</div>
                        <div class="band-option" data-value="9.0" onclick="selectBand(this)">9.0</div>
                    </div>

                    <p class="info-text">Most universities require Band 6.5 to 7.5</p>
                </div>

                <!-- Step 4: Timeline -->
                <div class="step" id="step-4">
                    <h2 class="step-title">When is your IELTS exam?</h2>
                    <p class="step-subtitle">This helps us create a personalized study plan for you.</p>

                    <div class="timeline-grid">
                        <div class="timeline-option" data-value="1_month" onclick="selectTimeline(this)">
                            <div class="timeline-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <div class="timeline-title">Within 1 month</div>
                        </div>

                        <div class="timeline-option" data-value="1_3_months" onclick="selectTimeline(this)">
                            <div class="timeline-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <div class="timeline-title">1-3 months</div>
                        </div>

                        <div class="timeline-option" data-value="3_6_months" onclick="selectTimeline(this)">
                            <div class="timeline-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <div class="timeline-title">3-6 months</div>
                        </div>

                        <div class="timeline-option" data-value="not_sure" onclick="selectTimeline(this)">
                            <div class="timeline-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <div class="timeline-title">Not sure yet</div>
                        </div>
                    </div>

                    <div class="date-picker-wrapper" id="date-picker">
                        <label>Or select a specific date</label>
                        <input type="date" id="exam-date" min="{{ date('Y-m-d', strtotime('+1 day')) }}" onchange="selectCustomDate(this)">
                    </div>
                </div>

                <!-- Step 5: Summary -->
                <div class="step" id="step-5">
                    <h2 class="step-title">You're all set!</h2>
                    <p class="step-subtitle">Here's a summary of your IELTS journey setup.</p>

                    <div class="summary-card">
                        <div class="summary-item">
                            <span class="summary-label">Exam Type</span>
                            <span class="summary-value" id="summary-type">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Target Band</span>
                            <span class="summary-value highlight" id="summary-band">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Exam Date</span>
                            <span class="summary-value" id="summary-date">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Days Remaining</span>
                            <span class="summary-value highlight" id="summary-days">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="onboarding-footer">
                <button class="btn btn-secondary" id="btn-back" onclick="goBack()" style="display: none;">Back</button>
                <button class="btn btn-primary" id="btn-next" onclick="goNext()">Get Started</button>
            </div>

            <div class="skip-link">
                <a href="{{ route('student.onboarding.skip') }}">Skip for now</a>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 5;

        // Form data
        const formData = {
            exam_type: null,
            target_band: null,
            exam_timeline: null,
            exam_date: null
        };

        function updateProgress() {
            for (let i = 1; i <= totalSteps; i++) {
                const dot = document.getElementById(`dot-${i}`);
                dot.classList.remove('active', 'completed');

                if (i < currentStep) {
                    dot.classList.add('completed');
                } else if (i === currentStep) {
                    dot.classList.add('active');
                }
            }

            document.getElementById('step-indicator').textContent = `Step ${currentStep} of ${totalSteps}`;

            // Update back button visibility
            document.getElementById('btn-back').style.display = currentStep > 1 ? 'block' : 'none';

            // Update next button text
            const nextBtn = document.getElementById('btn-next');
            if (currentStep === 1) {
                nextBtn.textContent = 'Get Started';
            } else if (currentStep === totalSteps) {
                nextBtn.textContent = 'Go to Dashboard';
            } else {
                nextBtn.textContent = 'Continue';
            }

            // Enable/disable next button based on selection
            updateNextButtonState();
        }

        function updateNextButtonState() {
            const nextBtn = document.getElementById('btn-next');
            let isValid = true;

            if (currentStep === 2 && !formData.exam_type) {
                isValid = false;
            } else if (currentStep === 3 && !formData.target_band) {
                isValid = false;
            } else if (currentStep === 4 && !formData.exam_timeline) {
                isValid = false;
            }

            nextBtn.disabled = !isValid;
        }

        function showStep(stepNum) {
            // Hide all steps
            document.querySelectorAll('.step').forEach(step => {
                step.classList.remove('active');
                step.classList.add('exit');
            });

            // Show target step after a brief delay
            setTimeout(() => {
                document.querySelectorAll('.step').forEach(step => {
                    step.classList.remove('exit');
                });
                document.getElementById(`step-${stepNum}`).classList.add('active');
            }, 150);
        }

        function goNext() {
            if (currentStep === totalSteps) {
                // Submit form
                submitOnboarding();
                return;
            }

            currentStep++;
            showStep(currentStep);
            updateProgress();

            // Update summary on last step
            if (currentStep === totalSteps) {
                updateSummary();
            }
        }

        function goBack() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
                updateProgress();
            }
        }

        function selectExamType(element) {
            document.querySelectorAll('#step-2 .option-card').forEach(card => {
                card.classList.remove('selected');
            });
            element.classList.add('selected');
            formData.exam_type = element.dataset.value;
            updateNextButtonState();
        }

        function selectBand(element) {
            document.querySelectorAll('.band-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            element.classList.add('selected');
            formData.target_band = element.dataset.value;
            updateNextButtonState();
        }

        function selectTimeline(element) {
            document.querySelectorAll('.timeline-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            element.classList.add('selected');
            formData.exam_timeline = element.dataset.value;

            // Show date picker for "custom" or hide for others
            const datePicker = document.getElementById('date-picker');
            if (element.dataset.value === 'custom') {
                datePicker.classList.add('show');
            } else {
                datePicker.classList.remove('show');
                formData.exam_date = null;
            }

            updateNextButtonState();
        }

        function selectCustomDate(input) {
            if (input.value) {
                formData.exam_timeline = 'custom';
                formData.exam_date = input.value;

                // Deselect other options
                document.querySelectorAll('.timeline-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
            }
            updateNextButtonState();
        }

        function updateSummary() {
            // Exam type
            const examTypeText = formData.exam_type === 'academic' ? 'Academic' : 'General Training';
            document.getElementById('summary-type').textContent = examTypeText;

            // Target band
            document.getElementById('summary-band').textContent = 'Band ' + formData.target_band;

            // Calculate exam date
            let examDate;
            if (formData.exam_timeline === 'custom' && formData.exam_date) {
                examDate = new Date(formData.exam_date);
            } else {
                const now = new Date();
                switch(formData.exam_timeline) {
                    case '1_month':
                        examDate = new Date(now.setMonth(now.getMonth() + 1));
                        break;
                    case '1_3_months':
                        examDate = new Date(now.setMonth(now.getMonth() + 2));
                        break;
                    case '3_6_months':
                        examDate = new Date(now.setMonth(now.getMonth() + 4));
                        break;
                    default:
                        examDate = new Date(now.setMonth(now.getMonth() + 3));
                }
            }

            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('summary-date').textContent = examDate.toLocaleDateString('en-US', options);

            // Days remaining
            const today = new Date();
            const diffTime = examDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            document.getElementById('summary-days').textContent = diffDays + ' days';
        }

        function submitOnboarding() {
            const nextBtn = document.getElementById('btn-next');
            nextBtn.disabled = true;
            nextBtn.textContent = 'Please wait...';

            fetch('{{ route("student.onboarding.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Something went wrong. Please try again.');
                    nextBtn.disabled = false;
                    nextBtn.textContent = 'Go to Dashboard';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong. Please try again.');
                nextBtn.disabled = false;
                nextBtn.textContent = 'Go to Dashboard';
            });
        }

        // Initialize
        updateProgress();
    </script>
</body>
</html>
