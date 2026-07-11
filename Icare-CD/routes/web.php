<?php

use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\StudentAttemptController;
use App\Http\Controllers\Admin\TestSectionController;
use App\Http\Controllers\Admin\TestSetController;
use App\Http\Controllers\AIEvaluationController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\BranchLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\ListeningTestController;
use App\Models\TestSet;
use App\Http\Controllers\Student\ReadingTestController;
use App\Http\Controllers\Student\ResultController;
use App\Http\Controllers\Student\SpeakingTestController;
use App\Http\Controllers\Student\TestController;
use App\Http\Controllers\Student\WritingTestController;
use App\Http\Controllers\Student\WritingPracticeController;
use Illuminate\Support\Facades\Route;

// Legal Pages Routes
Route::get('/privacy-policy', function () {
    return view('legal.privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('legal.terms-of-service');
})->name('terms-of-service');

Route::get('/cookie-policy', function () {
    return view('legal.cookie-policy');
})->name('cookie-policy');

// Desktop App Session Bridge — converts API token to web session
Route::get('/desktop/session-bridge', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token');
    $redirect = $request->query('redirect', '/student/test/full-test');

    if (!$token) {
        abort(401, 'Token required');
    }

    // Find the personal access token
    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
    if (!$accessToken) {
        abort(401, 'Invalid token');
    }

    $user = $accessToken->tokenable;
    if (!$user) {
        abort(401, 'User not found');
    }

    // Log user into web session
    \Illuminate\Support\Facades\Auth::login($user);
    $request->session()->regenerate();

    return redirect($redirect);
})->name('desktop.session-bridge');

// Home route
Route::get('/', [App\Http\Controllers\WelcomeController::class, 'index'])->name('welcome');
Route::get('/home', [HomeController::class, 'index'])->name('home');

// About Page
Route::get('/about', function () {
    return view('about');
})->name('about');

// Contact Page
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Help Center Page
Route::get('/help-center', function () {
    return view('help-center');
})->name('help-center');

// IELTS Score Calculator
Route::get('/score-calculator', function () {
    return view('score-calculator');
})->name('score-calculator');

Route::middleware(['web'])->group(function () {

    // AUTHENTICATION ROUTES (Guest only)
    Route::middleware(['guest'])->group(function () {
        // Student Login Routes
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);

        // Admin Login Routes
        Route::get('/auth/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/auth/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');

        // Branch Login Routes
        Route::get('/branch/login', [BranchLoginController::class, 'showLoginForm'])->name('branch.login');
        Route::post('/branch/login', [BranchLoginController::class, 'login'])->name('branch.login.submit');

        // Offline Student Login Routes
        Route::get('/offline/login', [\App\Http\Controllers\Auth\OfflineStudentLoginController::class, 'showLoginForm'])->name('offline.login');
        Route::post('/offline/login', [\App\Http\Controllers\Auth\OfflineStudentLoginController::class, 'login'])->name('offline.login.submit');

        // Registration disabled — students are provisioned top-down (admin → branch → student).
        // The 'register' route name is kept resolvable but redirects to login.
        Route::get('/register', fn () => redirect()->route('login')
            ->with('error', 'Public registration is disabled. Please contact your branch to enroll.'))
            ->name('register');

        // OTP Verification Routes
        Route::get('/verify-otp', [OtpVerificationController::class, 'show'])->name('auth.verify.otp');
        Route::post('/verify-otp', [OtpVerificationController::class, 'verify'])->name('auth.otp.verify');
        Route::post('/resend-otp', [OtpVerificationController::class, 'resend'])->name('auth.otp.resend');

        Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
    });

    // Logout Route (authenticated only)
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

    // Banned User Routes
    Route::middleware(['auth'])->prefix('banned')->name('banned.')->group(function () {
        Route::get('/', [App\Http\Controllers\BannedController::class, 'index'])->name('index');
    });

    // Audio streaming route
    Route::get('/audio/stream/{recording}', [App\Http\Controllers\AudioStreamController::class, 'stream'])->name('audio.stream')->middleware('auth');
    
    Route::middleware(['auth', \App\Http\Middleware\CheckBanned::class])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/profile/verify-email-change', [ProfileController::class, 'verifyEmailChange'])->name('profile.verify-email-change');
        Route::post('/profile/resend-email-otp', [ProfileController::class, 'resendEmailChangeOtp'])->name('profile.resend-email-otp');
        Route::post('/profile/cancel-email-change', [ProfileController::class, 'cancelEmailChange'])->name('profile.cancel-email-change');
    });

    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar')->middleware('auth');

});

// Authenticated routes with role-based dashboard
Route::middleware(['auth', \App\Http\Middleware\CheckBanned::class])->group(function () {
    // Dashboard route
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Branch staff goes to branch dashboard
        if ($user->isBranchStaff()) {
            return redirect()->route('branch.dashboard');
        }

        if ($user->is_admin || $user->role_id) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->student_type === 'offline') {
            return redirect()->route('offline.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    })->name('dashboard');

    // Offline Student routes
    Route::middleware([\App\Http\Middleware\OfflineStudentAccess::class])->prefix('offline')->name('offline.')->group(function () {
        Route::get('/', [App\Http\Controllers\OfflineStudent\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/results', fn() => redirect()->route('student.results'))->name('results');
    });

    // AI Evaluation routes
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/evaluate/writing', [AIEvaluationController::class, 'evaluateWriting'])->name('evaluation.writing');
        Route::post('/evaluate/speaking', [AIEvaluationController::class, 'evaluateSpeaking'])->name('evaluation.speaking');
        // Progressive speaking evaluation (one recording at a time to prevent timeout)
        Route::post('/evaluate/speaking/status', [AIEvaluationController::class, 'getEvaluationStatus'])->name('evaluation.speaking.status');
        Route::post('/evaluate/speaking/single', [AIEvaluationController::class, 'evaluateSingleRecording'])->name('evaluation.speaking.single');
        Route::post('/evaluate/speaking/finalize', [AIEvaluationController::class, 'finalizeEvaluation'])->name('evaluation.speaking.finalize');
        Route::get('/evaluation/{attempt}', [AIEvaluationController::class, 'getEvaluation'])->name('evaluation.get');
        Route::post('/explain-answer', [AIEvaluationController::class, 'getAnswerExplanation'])->name('explain.answer');
    });

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\NotificationController::class, 'show'])->name('show');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });

    // Student routes
    Route::middleware(['role:student', 'offline.restrict'])->prefix('student')->name('student.')->group(function () {
        // User Onboarding routes (no onboarding middleware - these are the onboarding pages themselves)
        Route::prefix('onboarding')->name('onboarding.')->group(function () {
            Route::get('/', [App\Http\Controllers\Student\UserOnboardingController::class, 'index'])->name('index');
            Route::post('/store', [App\Http\Controllers\Student\UserOnboardingController::class, 'store'])->name('store');
            Route::get('/skip', [App\Http\Controllers\Student\UserOnboardingController::class, 'skip'])->name('skip');
        });

        // Dashboard route with onboarding check
        Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->middleware('onboarding')->name('dashboard');
        Route::get('/dashboard/progress-data', [App\Http\Controllers\Student\DashboardController::class, 'progressData'])->name('dashboard.progress-data');
        Route::post('/goals/update-exam-type', [App\Http\Controllers\Student\DashboardController::class, 'updateExamType'])->name('goals.update-exam-type');

        // AI Tutor (Coming Soon)
        Route::get('/ai-tutor', function () {
            return view('student.ai-tutor.index');
        })->name('ai-tutor.index');

        Route::prefix('test')->group(function () {
            // Test Session Management Routes (keep-alive, emergency save, etc.)
            Route::prefix('session')->name('test.session.')->group(function () {
                Route::post('/keep-alive', [App\Http\Controllers\Student\TestSessionController::class, 'keepAlive'])->name('keep-alive');
                Route::get('/check-auth', [App\Http\Controllers\Student\TestSessionController::class, 'checkAuth'])->name('check-auth');
                Route::post('/emergency-save', [App\Http\Controllers\Student\TestSessionController::class, 'emergencySave'])->name('emergency-save');
                Route::get('/status', [App\Http\Controllers\Student\TestSessionController::class, 'status'])->name('status');
            });

            Route::prefix('full-test')->name('full-test.')->group(function () {
                Route::get('/', [App\Http\Controllers\Student\FullTestController::class, 'index'])->name('index');
                Route::get('/{fullTest}/onboarding', [App\Http\Controllers\Student\FullTestController::class, 'onboarding'])->name('onboarding');
                // Rate limited: 1 request per 3 seconds to prevent double-click
                Route::post('/{fullTest}/start', [App\Http\Controllers\Student\FullTestController::class, 'start'])
                    ->middleware('throttle:test-start')
                    ->name('start');
                Route::get('/attempt/{fullTestAttempt}/continue', [App\Http\Controllers\Student\FullTestController::class, 'continueTest'])->name('continue');
                Route::get('/attempt/{fullTestAttempt}/section/{section}', [App\Http\Controllers\Student\FullTestController::class, 'section'])->name('section');
                Route::get('/attempt/{fullTestAttempt}/section-completed/{section}', [App\Http\Controllers\Student\FullTestController::class, 'sectionCompleted'])->name('section-completed');
                // Rate limited: 1 request per 2 seconds for section completion
                Route::post('/attempt/{fullTestAttempt}/complete-section', [App\Http\Controllers\Student\FullTestController::class, 'completeSection'])
                    ->middleware('throttle:section-complete')
                    ->name('complete-section');
                Route::get('/attempt/{fullTestAttempt}/results', [App\Http\Controllers\Student\FullTestController::class, 'results'])->name('results');
                Route::post('/attempt/{fullTestAttempt}/abandon', [App\Http\Controllers\Student\FullTestController::class, 'abandon'])->name('abandon');
                Route::get('/attempt/{fullTestAttempt}/request-evaluation', [App\Http\Controllers\Student\FullTestController::class, 'requestEvaluation'])->name('request-evaluation');
                Route::post('/attempt/{fullTestAttempt}/submit-evaluation', [App\Http\Controllers\Student\FullTestController::class, 'submitEvaluationRequest'])->name('submit-evaluation');
                Route::get('/attempt/{fullTestAttempt}/evaluation-details', [App\Http\Controllers\Student\FullTestController::class, 'evaluationDetails'])->name('evaluation-details');
            });

            // Centralized Onboarding Controller
            $onboardingController = App\Http\Controllers\Student\OnboardingController::class;

            Route::prefix('listening')->name('listening.')->group(function () use ($onboardingController) {
                Route::get('/', [ListeningTestController::class, 'index'])->name('index');
                Route::middleware(['usage.limit:mock_test'])->group(function () use ($onboardingController) {
                    // Centralized onboarding routes
                    Route::get('/confirm/{testSet}', fn(TestSet $testSet) => app($onboardingController)->confirmDetails('listening', $testSet))->name('onboarding.confirm-details');
                    Route::get('/sound-check/{testSet}', fn(TestSet $testSet) => app($onboardingController)->equipmentCheck('listening', $testSet))->name('onboarding.sound-check');
                    Route::get('/instructions/{testSet}', fn(TestSet $testSet) => app($onboardingController)->instructions('listening', $testSet))->name('onboarding.instructions');
                    // Rate limited: 1 request per 3 seconds to prevent double-click
                    Route::get('/start/{testSet}', [ListeningTestController::class, 'start'])
                        ->middleware('throttle:test-start')
                        ->name('start');
                });
                // Rate limited: 1 request per 5 seconds to prevent double submission
                Route::post('/submit/{attempt}', [ListeningTestController::class, 'submit'])
                    ->middleware('throttle:test-submit')
                    ->name('submit');
                // Server-side auto-save routes (for data safety)
                Route::post('/auto-save/{attempt}', [ListeningTestController::class, 'autoSave'])->name('auto-save');
                Route::get('/draft-answers/{attempt}', [ListeningTestController::class, 'getDraftAnswers'])->name('draft-answers');
            });

            Route::prefix('reading')->name('reading.')->group(function () use ($onboardingController) {
                Route::get('/', [ReadingTestController::class, 'index'])->name('index');
                Route::middleware(['usage.limit:mock_test'])->group(function () use ($onboardingController) {
                    // Centralized onboarding routes
                    Route::get('/confirm/{testSet}', fn(TestSet $testSet) => app($onboardingController)->confirmDetails('reading', $testSet))->name('onboarding.confirm-details');
                    Route::get('/instructions/{testSet}', fn(TestSet $testSet) => app($onboardingController)->instructions('reading', $testSet))->name('onboarding.instructions');
                    // Rate limited: 1 request per 3 seconds to prevent double-click
                    Route::get('/start/{testSet}', [ReadingTestController::class, 'start'])
                        ->middleware('throttle:test-start')
                        ->name('start');
                });
                // Rate limited: 1 request per 5 seconds to prevent double submission
                Route::post('/submit/{attempt}', [ReadingTestController::class, 'submit'])
                    ->middleware('throttle:test-submit')
                    ->name('submit');
                // Server-side auto-save routes (for data safety)
                Route::post('/auto-save/{attempt}', [ReadingTestController::class, 'autoSave'])->name('auto-save');
                Route::get('/draft-answers/{attempt}', [ReadingTestController::class, 'getDraftAnswers'])->name('draft-answers');
            });

            Route::prefix('writing')->name('writing.')->group(function () use ($onboardingController) {
                Route::get('/', [WritingTestController::class, 'index'])->name('index');
                Route::middleware(['usage.limit:mock_test'])->group(function () use ($onboardingController) {
                    // Centralized onboarding routes
                    Route::get('/confirm/{testSet}', fn(TestSet $testSet) => app($onboardingController)->confirmDetails('writing', $testSet))->name('onboarding.confirm-details');
                    Route::get('/instructions/{testSet}', fn(TestSet $testSet) => app($onboardingController)->instructions('writing', $testSet))->name('onboarding.instructions');
                    // Rate limited: 1 request per 3 seconds to prevent double-click
                    Route::get('/start/{testSet}', [WritingTestController::class, 'start'])
                        ->middleware('throttle:test-start')
                        ->name('start');
                });
                // Rate limited: 1 request per 5 seconds to prevent double submission
                Route::post('/autosave/{attempt}', [WritingTestController::class, 'autosave'])->name('autosave');
                Route::post('/submit/{attempt}', [WritingTestController::class, 'submit'])
                    ->middleware('throttle:test-submit')
                    ->name('submit');
                // Server-side auto-save routes (for data safety)
                Route::post('/auto-save/{attempt}', [WritingTestController::class, 'saveDraft'])->name('auto-save');
                Route::get('/draft-answers/{attempt}', [WritingTestController::class, 'getDraftAnswers'])->name('draft-answers');
            });

            // Writing Practice Routes (Question-wise practice)
            Route::prefix('writing-practice')->name('writing-practice.')->group(function () {
                Route::get('/', [WritingPracticeController::class, 'index'])->name('index');
                Route::get('/task1', [WritingPracticeController::class, 'task1'])->name('task1');
                Route::get('/task2', [WritingPracticeController::class, 'task2'])->name('task2');
                Route::middleware(['usage.limit:mock_test'])->group(function () {
                    Route::get('/question/{question}', [WritingPracticeController::class, 'practiceQuestion'])->name('question');
                    Route::post('/autosave/{attempt}/{question}', [WritingPracticeController::class, 'autosave'])->name('autosave');
                    Route::post('/submit/{attempt}', [WritingPracticeController::class, 'submit'])
                        ->middleware('throttle:test-submit')
                        ->name('submit');
                });
            });

            Route::prefix('speaking')->name('speaking.')->group(function () use ($onboardingController) {
                Route::get('/', [SpeakingTestController::class, 'index'])->name('index');
                Route::middleware(['usage.limit:mock_test'])->group(function () use ($onboardingController) {
                    // Centralized onboarding routes
                    Route::get('/confirm/{testSet}', fn(TestSet $testSet) => app($onboardingController)->confirmDetails('speaking', $testSet))->name('onboarding.confirm-details');
                    Route::get('/mic-check/{testSet}', fn(TestSet $testSet) => app($onboardingController)->equipmentCheck('speaking', $testSet))->name('onboarding.microphone-check');
                    Route::get('/instructions/{testSet}', fn(TestSet $testSet) => app($onboardingController)->instructions('speaking', $testSet))->name('onboarding.instructions');
                    // Rate limited: 1 request per 3 seconds to prevent double-click
                    Route::get('/start/{testSet}', [SpeakingTestController::class, 'start'])
                        ->middleware('throttle:test-start')
                        ->name('start');
                });
                
                Route::post('/record/{attempt}/{question}', [SpeakingTestController::class, 'record'])->name('record');
                
                // Rate limited: 1 request per 5 seconds to prevent double submission
                Route::post('/submit/{attempt}', [SpeakingTestController::class, 'submit'])
                    ->middleware('throttle:test-submit')
                    ->name('submit');
                // Server-side auto-save routes (for data safety)
                Route::post('/auto-save/{attempt}', [SpeakingTestController::class, 'autoSave'])->name('auto-save');
                Route::get('/draft-answers/{attempt}', [SpeakingTestController::class, 'getDraftAnswers'])->name('draft-answers');
            });

            Route::get('/results', [ResultController::class, 'index'])->name('results');
            Route::get('/results/{attempt}', [ResultController::class, 'show'])->name('results.show');
            Route::post('/results/{attempt}/retake', [ResultController::class, 'retake'])->name('results.retake');
        });

        Route::prefix('human-evaluation')->name('evaluation.')->group(function () {
            Route::get('/{attempt}/teachers', [App\Http\Controllers\Student\HumanEvaluationController::class, 'showTeachers'])->name('teachers');
            Route::post('/{attempt}/request', [App\Http\Controllers\Student\HumanEvaluationController::class, 'requestEvaluation'])->name('request');
            Route::get('/{attempt}/status', [App\Http\Controllers\Student\HumanEvaluationController::class, 'status'])->name('status');
            Route::get('/{attempt}/result', [App\Http\Controllers\Student\HumanEvaluationController::class, 'viewResult'])->name('result');
        });

    });

    // Teacher routes
    Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Teacher\EvaluationController::class, 'dashboard'])->name('dashboard');
        Route::patch('/toggle-availability', [App\Http\Controllers\Teacher\EvaluationController::class, 'toggleAvailability'])->name('toggle-availability');

        // Teacher Profile Routes
        Route::get('/profile', [App\Http\Controllers\Teacher\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [App\Http\Controllers\Teacher\ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [App\Http\Controllers\Teacher\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/avatar', [App\Http\Controllers\Teacher\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
        Route::delete('/profile/avatar', [App\Http\Controllers\Teacher\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

        // Student Results
        Route::prefix('student-results')->name('student-results.')->group(function () {
            Route::get('/', [App\Http\Controllers\Teacher\StudentResultController::class, 'index'])->name('index');
            Route::get('/{studentAttempt}', [App\Http\Controllers\Teacher\StudentResultController::class, 'show'])->name('show');
        });

        Route::prefix('evaluations')->name('evaluations.')->group(function () {
            Route::get('/pending', [App\Http\Controllers\Teacher\EvaluationController::class, 'pending'])->name('pending');
            Route::get('/completed', [App\Http\Controllers\Teacher\EvaluationController::class, 'completed'])->name('completed');
            Route::post('/{evaluationRequest}/claim', [App\Http\Controllers\Teacher\EvaluationController::class, 'claim'])->name('claim');
            Route::get('/{evaluationRequest}', [App\Http\Controllers\Teacher\EvaluationController::class, 'show'])->name('show');
            Route::post('/{evaluationRequest}/submit', [App\Http\Controllers\Teacher\EvaluationController::class, 'submit'])->name('submit');
        });
    });

    // Admin routes - WITH PERMISSION MIDDLEWARE
    Route::middleware(['admin.access'])->prefix('admin')->name('admin.')->group(function () {

        // Dashboard - No permission required (all admin users)
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/quick-stats', [App\Http\Controllers\Admin\DashboardController::class, 'quickStats'])->name('dashboard.quick-stats');
        Route::get('/test-tinymce', function () { return view('admin.test-tinymce'); })->name('test-tinymce');

        // Admin Profile Routes
        Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/avatar', [App\Http\Controllers\Admin\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
        Route::delete('/profile/avatar', [App\Http\Controllers\Admin\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

        // TEST MANAGEMENT - tests permissions
        Route::middleware(['permission:tests.view,tests.create,tests.edit,tests.delete'])->group(function () {
            Route::resource('sections', TestSectionController::class);
            Route::resource('test-sets', TestSetController::class);
            Route::get('/test-sets/{testSet}/preview', [TestSetController::class, 'preview'])->name('test-sets.preview');
            Route::resource('full-tests', App\Http\Controllers\Admin\FullTestController::class);
            Route::patch('/full-tests/{fullTest}/toggle-status', [App\Http\Controllers\Admin\FullTestController::class, 'toggleStatus'])->name('full-tests.toggle-status');
            Route::post('/full-tests/reorder', [App\Http\Controllers\Admin\FullTestController::class, 'reorder'])->name('full-tests.reorder');
            Route::get('/full-tests/user/{userId}/attempts', [App\Http\Controllers\Admin\FullTestController::class, 'userAttempts'])->name('full-tests.user-attempts');
            Route::get('/full-test-attempts/{fullTestAttempt}', [App\Http\Controllers\Admin\FullTestController::class, 'showAttempt'])->name('full-test-attempts.show');
            Route::patch('/full-test-attempts/{fullTestAttempt}/update-score', [App\Http\Controllers\Admin\FullTestController::class, 'updateScore'])->name('full-test-attempts.update-score');

            Route::prefix('test-sets/{testSet}')->name('test-sets.')->group(function () {
                Route::get('/part-audios', [App\Http\Controllers\Admin\TestPartAudioController::class, 'index'])->name('part-audios');
                Route::post('/part-audios', [App\Http\Controllers\Admin\TestPartAudioController::class, 'upload'])->name('part-audios.upload');
                Route::delete('/part-audios/{partNumber}', [App\Http\Controllers\Admin\TestPartAudioController::class, 'destroy'])->name('part-audios.destroy');
                Route::get('/check-part-audio/{partNumber}', function ($testSetId, $partNumber) {
                    $testSet = \App\Models\TestSet::findOrFail($testSetId);
                    $fullAudio = $testSet->partAudios()->where('part_number', 0)->exists();
                    $partAudio = $testSet->partAudios()->where('part_number', $partNumber)->exists();
                    return response()->json(['hasAudio' => $fullAudio || $partAudio, 'isFullAudio' => $fullAudio, 'hasPartAudio' => $partAudio]);
                })->name('check-part-audio');
            });

            Route::prefix('test-categories')->name('test-categories.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\TestCategoryController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\Admin\TestCategoryController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Admin\TestCategoryController::class, 'store'])->name('store');
                Route::get('/{testCategory}', [App\Http\Controllers\Admin\TestCategoryController::class, 'show'])->name('show');
                Route::get('/{testCategory}/edit', [App\Http\Controllers\Admin\TestCategoryController::class, 'edit'])->name('edit');
                Route::put('/{testCategory}', [App\Http\Controllers\Admin\TestCategoryController::class, 'update'])->name('update');
                Route::delete('/{testCategory}', [App\Http\Controllers\Admin\TestCategoryController::class, 'destroy'])->name('destroy');
                Route::patch('/{testCategory}/toggle-status', [App\Http\Controllers\Admin\TestCategoryController::class, 'toggleStatus'])->name('toggle-status');
                Route::get('/{testCategory}/manage-test-sets', [App\Http\Controllers\Admin\TestCategoryController::class, 'manageTestSets'])->name('manage-test-sets');
                Route::post('/{testCategory}/update-test-sets', [App\Http\Controllers\Admin\TestCategoryController::class, 'updateTestSets'])->name('update-test-sets');
                Route::post('/reorder', [App\Http\Controllers\Admin\TestCategoryController::class, 'reorder'])->name('reorder');
            });
        });

        // QUESTIONS - questions permissions
        Route::middleware(['permission:questions.view,questions.create,questions.edit,questions.delete'])->group(function () {
            Route::prefix('questions')->name('questions.')->group(function () {
                Route::get('/', [QuestionController::class, 'index'])->name('index');
                Route::get('/create', [QuestionController::class, 'create'])->name('create');
                Route::post('/', [QuestionController::class, 'store'])->name('store');
                Route::get('/{question}', [QuestionController::class, 'show'])->name('show');
                Route::get('/{question}/edit', [QuestionController::class, 'edit'])->name('edit');
                Route::put('/{question}', [QuestionController::class, 'update'])->name('update');
                Route::delete('/{question}', [QuestionController::class, 'destroy'])->name('destroy');
                Route::get('/ajax/test-set/{testSetId}', [QuestionController::class, 'ajaxTestSet'])->name('ajax.test-set');
                Route::get('/reading/{testSet}/questions', [QuestionController::class, 'createReadingQuestion'])->name('reading.questions');
                Route::get('/reading/{testSet}/passage', [QuestionController::class, 'createReadingPassage'])->name('reading.passage');
                Route::get('/reading/{testSet}/markers', [QuestionController::class, 'getPassageMarkers'])->name('reading.markers');
                Route::post('/upload-image', [App\Http\Controllers\Admin\ImageUploadController::class, 'upload'])->name('upload.image');
                Route::post('/{question}/duplicate', [QuestionController::class, 'duplicate'])->name('duplicate');
                Route::get('/bulk-import/{testSet}', [QuestionController::class, 'bulkImportForm'])->name('bulk-import');
                Route::post('/bulk-import/{testSet}', [QuestionController::class, 'bulkImport'])->name('bulk-import.process');
                Route::post('/reorder', [QuestionController::class, 'reorder'])->name('reorder');
                Route::get('/test-set/{testSet}/part/{part}', [QuestionController::class, 'getByPart'])->name('get-by-part');
            });
        });

        // ATTEMPTS - attempts permissions
        Route::middleware(['permission:attempts.view,attempts.evaluate,attempts.delete,attempts.export'])->group(function () {
            Route::delete('/attempts/bulk-destroy', [StudentAttemptController::class, 'bulkDestroy'])->name('attempts.bulk-destroy');
            Route::resource('attempts', StudentAttemptController::class);
            Route::get('/attempts/{attempt}/evaluate', [StudentAttemptController::class, 'evaluateForm'])->name('attempts.evaluate-form');
            Route::post('/attempts/{attempt}/evaluate', [StudentAttemptController::class, 'evaluate'])->name('attempts.evaluate');
        });

        // OFFLINE PACKAGES (Branch Students enrollment packages)
        Route::prefix('offline-packages')->name('offline-packages.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\OfflinePackageController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\OfflinePackageController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\OfflinePackageController::class, 'store'])->name('store');
            Route::get('/{offlinePackage}/edit', [App\Http\Controllers\Admin\OfflinePackageController::class, 'edit'])->name('edit');
            Route::put('/{offlinePackage}', [App\Http\Controllers\Admin\OfflinePackageController::class, 'update'])->name('update');
            Route::delete('/{offlinePackage}', [App\Http\Controllers\Admin\OfflinePackageController::class, 'destroy'])->name('destroy');
            Route::patch('/{offlinePackage}/toggle-status', [App\Http\Controllers\Admin\OfflinePackageController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{offlinePackage}/branch-pricing', [App\Http\Controllers\Admin\OfflinePackageController::class, 'branchPricing'])->name('branch-pricing');
            Route::post('/{offlinePackage}/branch-pricing', [App\Http\Controllers\Admin\OfflinePackageController::class, 'updateBranchPricing'])->name('update-branch-pricing');
        });

        // TEACHERS - teachers permissions
        Route::middleware(['permission:teachers.view,teachers.create,teachers.edit,teachers.delete'])->group(function () {
            Route::prefix('teachers')->name('teachers.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\TeacherController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\Admin\TeacherController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Admin\TeacherController::class, 'store'])->name('store');
                Route::get('/{teacher}', [App\Http\Controllers\Admin\TeacherController::class, 'show'])->name('show');
                Route::get('/{teacher}/edit', [App\Http\Controllers\Admin\TeacherController::class, 'edit'])->name('edit');
                Route::put('/{teacher}', [App\Http\Controllers\Admin\TeacherController::class, 'update'])->name('update');
                Route::delete('/{teacher}', [App\Http\Controllers\Admin\TeacherController::class, 'destroy'])->name('destroy');
                Route::patch('/{teacher}/toggle-availability', [App\Http\Controllers\Admin\TeacherController::class, 'toggleAvailability'])->name('toggle-availability');
            });
        });

        // AVATAR TEACHERS - teachers permissions (reusing teachers permission)
        Route::middleware(['permission:teachers.view,teachers.create,teachers.edit,teachers.delete'])->group(function () {
            Route::prefix('avatar-teachers')->name('avatar-teachers.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'store'])->name('store');
                Route::get('/{avatarTeacher}/edit', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'edit'])->name('edit');
                Route::put('/{avatarTeacher}', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'update'])->name('update');
                Route::delete('/{avatarTeacher}', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'destroy'])->name('destroy');
                Route::patch('/{avatarTeacher}/set-default', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'setDefault'])->name('set-default');
                Route::patch('/{avatarTeacher}/toggle-active', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'toggleActive'])->name('toggle-active');
                Route::post('/preview-voice', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'previewVoice'])->name('preview-voice');
                Route::post('/{avatarTeacher}/generate', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'generateAvatars'])->name('generate');
                Route::post('/{avatarTeacher}/retry-failed', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'retryFailed'])->name('retry-failed');
                Route::get('/{avatarTeacher}/progress', [App\Http\Controllers\Admin\AvatarTeacherController::class, 'getProgress'])->name('progress');
            });
        });

        // SETTINGS - settings permissions
        Route::middleware(['permission:settings.view,settings.edit'])->group(function () {
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/website', [App\Http\Controllers\Admin\WebsiteSettingController::class, 'index'])->name('website');
                Route::post('/website', [App\Http\Controllers\Admin\WebsiteSettingController::class, 'update'])->name('website.update');
                Route::delete('/website/logo', [App\Http\Controllers\Admin\WebsiteSettingController::class, 'removeLogo'])->name('website.remove-logo');
                Route::delete('/website/dark-logo', [App\Http\Controllers\Admin\WebsiteSettingController::class, 'removeDarkModeLogo'])->name('website.remove-dark-logo');
                Route::delete('/website/favicon', [App\Http\Controllers\Admin\WebsiteSettingController::class, 'removeFavicon'])->name('website.remove-favicon');
            });

        });

        // USERS - users permissions
        Route::middleware(['permission:users.view,users.create,users.edit,users.delete'])->group(function () {
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
                Route::get('/system', [App\Http\Controllers\Admin\UserController::class, 'systemUsers'])->name('system');
                Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('create');

                // Bulk Import
                Route::prefix('import')->name('import.')->group(function () {
                    Route::get('/', [App\Http\Controllers\Admin\UserImportController::class, 'importForm'])->name('form');
                    Route::post('/preview', [App\Http\Controllers\Admin\UserImportController::class, 'importPreview'])->name('preview');
                    Route::post('/process', [App\Http\Controllers\Admin\UserImportController::class, 'importProcess'])->name('process');
                    Route::post('/packages', [App\Http\Controllers\Admin\UserImportController::class, 'getPackages'])->name('packages');
                    Route::get('/template', [App\Http\Controllers\Admin\UserImportController::class, 'downloadTemplate'])->name('template');
                    Route::get('/export-results', [App\Http\Controllers\Admin\UserImportController::class, 'exportImportResults'])->name('export-results');
                });
                Route::post('/', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
                Route::get('/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
                Route::get('/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('edit');
                Route::put('/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
                Route::delete('/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy');
                Route::get('/{user}/ban', [App\Http\Controllers\Admin\UserController::class, 'showBanForm'])->name('ban-form');
                Route::post('/{user}/ban', [App\Http\Controllers\Admin\UserController::class, 'ban'])->name('ban');
                Route::post('/{user}/unban', [App\Http\Controllers\Admin\UserController::class, 'unban'])->name('unban');
                Route::post('/{user}/verify-email', [App\Http\Controllers\Admin\UserController::class, 'verifyEmail'])->name('verify-email');
            });

            Route::prefix('roles')->name('roles.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\Admin\RoleController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('store');
                Route::get('/{role}', [App\Http\Controllers\Admin\RoleController::class, 'show'])->name('show');
                Route::get('/{role}/edit', [App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('edit');
                Route::put('/{role}', [App\Http\Controllers\Admin\RoleController::class, 'update'])->name('update');
                Route::delete('/{role}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('destroy');
            });

            // BRANCHES - Branch Management
            Route::prefix('branches')->name('branches.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\BranchController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\Admin\BranchController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Admin\BranchController::class, 'store'])->name('store');
                Route::get('/{branch}', [App\Http\Controllers\Admin\BranchController::class, 'show'])->name('show');
                Route::get('/{branch}/edit', [App\Http\Controllers\Admin\BranchController::class, 'edit'])->name('edit');
                Route::put('/{branch}', [App\Http\Controllers\Admin\BranchController::class, 'update'])->name('update');
                Route::delete('/{branch}', [App\Http\Controllers\Admin\BranchController::class, 'destroy'])->name('destroy');
                Route::patch('/{branch}/toggle-status', [App\Http\Controllers\Admin\BranchController::class, 'toggleStatus'])->name('toggle-status');
                // AI Credit Management
                Route::get('/{branch}/credits', [App\Http\Controllers\Admin\BranchController::class, 'credits'])->name('credits');
                Route::post('/{branch}/credits', [App\Http\Controllers\Admin\BranchController::class, 'addCredits'])->name('add-credits');
                // Staff Management
                Route::get('/{branch}/add-staff', [App\Http\Controllers\Admin\BranchController::class, 'addStaffForm'])->name('add-staff');
                Route::post('/{branch}/add-existing-staff', [App\Http\Controllers\Admin\BranchController::class, 'addExistingStaff'])->name('add-existing-staff');
                Route::post('/{branch}/create-staff', [App\Http\Controllers\Admin\BranchController::class, 'createStaff'])->name('create-staff');
                Route::patch('/{branch}/staff/{staff}', [App\Http\Controllers\Admin\BranchController::class, 'updateStaff'])->name('update-staff');
                Route::delete('/{branch}/staff/{staff}', [App\Http\Controllers\Admin\BranchController::class, 'removeStaff'])->name('remove-staff');
            });
        });

    });

    // =====================================================
    // BRANCH ADMIN ROUTES
    // =====================================================
    Route::prefix('branch-admin')->name('branch.')->middleware(['auth', 'branch.access'])->group(function () {

        // Dashboard
        Route::get('/', [App\Http\Controllers\Branch\DashboardController::class, 'index'])->name('dashboard');

        // Batch Management
        Route::prefix('batches')->name('batches.')->group(function () {
            Route::get('/', [App\Http\Controllers\Branch\BatchController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Branch\BatchController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Branch\BatchController::class, 'store'])->name('store');
            Route::get('/{batch}', [App\Http\Controllers\Branch\BatchController::class, 'show'])->name('show');
            Route::get('/{batch}/edit', [App\Http\Controllers\Branch\BatchController::class, 'edit'])->name('edit');
            Route::put('/{batch}', [App\Http\Controllers\Branch\BatchController::class, 'update'])->name('update');
            Route::delete('/{batch}', [App\Http\Controllers\Branch\BatchController::class, 'destroy'])->name('destroy');
        });

        // Students Management
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [App\Http\Controllers\Branch\StudentController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Branch\StudentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Branch\StudentController::class, 'store'])->name('store');

            // Bulk Import (must be before /{enrollment} routes)
            Route::get('/import', [App\Http\Controllers\Branch\StudentController::class, 'importForm'])->name('import.form');
            Route::post('/import/preview', [App\Http\Controllers\Branch\StudentController::class, 'importPreview'])->name('import.preview');
            Route::post('/import/process', [App\Http\Controllers\Branch\StudentController::class, 'importProcess'])->name('import.process');
            Route::get('/import/template', [App\Http\Controllers\Branch\StudentController::class, 'downloadTemplate'])->name('import.template');
            Route::get('/import/export-results', [App\Http\Controllers\Branch\StudentController::class, 'exportImportResults'])->name('import.export-results');

            // Dynamic enrollment routes (must be after static routes like /import, /create)
            Route::get('/{enrollment}', [App\Http\Controllers\Branch\StudentController::class, 'show'])->name('show');
            Route::get('/{enrollment}/edit', [App\Http\Controllers\Branch\StudentController::class, 'edit'])->name('edit');
            Route::put('/{enrollment}', [App\Http\Controllers\Branch\StudentController::class, 'update'])->name('update');
            Route::delete('/{enrollment}', [App\Http\Controllers\Branch\StudentController::class, 'destroy'])->name('destroy');
            Route::post('/{enrollment}/extend', [App\Http\Controllers\Branch\StudentController::class, 'extend'])->name('extend');
            Route::post('/{enrollment}/payment', [App\Http\Controllers\Branch\StudentController::class, 'recordPayment'])->name('payment');
            Route::get('/{enrollment}/renew', [App\Http\Controllers\Branch\StudentController::class, 'renewForm'])->name('renew.form');
            Route::post('/{enrollment}/renew', [App\Http\Controllers\Branch\StudentController::class, 'renew'])->name('renew');
            Route::put('/{enrollment}/update-tests', [App\Http\Controllers\Branch\StudentController::class, 'updateTests'])->name('update-tests');
            Route::post('/{enrollment}/reset-password', [App\Http\Controllers\Branch\StudentController::class, 'resetPassword'])->name('reset-password');
        });

        // Tests / Results
        Route::prefix('tests')->name('tests.')->group(function () {
            Route::get('/', [App\Http\Controllers\Branch\TestController::class, 'index'])->name('index');
            Route::get('/today', [App\Http\Controllers\Branch\TestController::class, 'today'])->name('today');
            Route::get('/results', [App\Http\Controllers\Branch\TestController::class, 'results'])->name('results');
            Route::get('/attempt/{attempt}', [App\Http\Controllers\Branch\TestController::class, 'showAttempt'])->name('show-attempt');
        });

        // Reports (Admin only)
        Route::prefix('reports')->name('reports.')->middleware(['branch.access:admin'])->group(function () {
            Route::get('/', [App\Http\Controllers\Branch\ReportController::class, 'index'])->name('index');
            Route::get('/daily', [App\Http\Controllers\Branch\ReportController::class, 'daily'])->name('daily');
            Route::get('/monthly', [App\Http\Controllers\Branch\ReportController::class, 'monthly'])->name('monthly');
            Route::get('/students', [App\Http\Controllers\Branch\ReportController::class, 'students'])->name('students');
            Route::get('/export', [App\Http\Controllers\Branch\ReportController::class, 'export'])->name('export');
        });

        // Payments
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [App\Http\Controllers\Branch\PaymentController::class, 'index'])->name('index');
            Route::get('/due', [App\Http\Controllers\Branch\PaymentController::class, 'due'])->name('due');
            Route::post('/{enrollment}', [App\Http\Controllers\Branch\PaymentController::class, 'store'])->name('store');
            Route::get('/{enrollment}/history', [App\Http\Controllers\Branch\PaymentController::class, 'history'])->name('history');
        });

    });
});
