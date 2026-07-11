<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvatarTeacher;
use App\Models\Question;
use App\Services\Avatar\AvatarGeneratorService;
use App\Services\Avatar\ElevenLabsService;
use App\Traits\HandlesFileUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AvatarTeacherController extends Controller
{
    use HandlesFileUploads;

    /**
     * Display a listing of avatar teachers.
     */
    public function index()
    {
        $teachers = AvatarTeacher::withCount([
            'questions as total_questions',
            'questions as ready_avatars' => function ($query) {
                $query->where('avatar_status', 'ready');
            },
            'questions as pending_avatars' => function ($query) {
                $query->whereIn('avatar_status', ['pending', 'generating_audio', 'generating_video']);
            },
            'questions as failed_avatars' => function ($query) {
                $query->where('avatar_status', 'failed');
            },
        ])->orderBy('is_default', 'desc')->orderBy('name')->get();

        return view('admin.avatar-teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new avatar teacher.
     */
    public function create()
    {
        // Get available voices from ElevenLabs (including custom voices)
        $elevenLabsService = new ElevenLabsService();
        $voicesResult = $elevenLabsService->getVoices(true);
        $voices = $voicesResult['success'] ? $voicesResult['voices'] : [];
        $groupedVoices = $voicesResult['success'] ? $voicesResult['grouped'] : [];

        return view('admin.avatar-teachers.create', compact('voices', 'groupedVoices'));
    }

    /**
     * Store a newly created avatar teacher.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'elevenlabs_voice_id' => 'required|string|max:100',
            'voice_name' => 'nullable|string|max:100',
            'gender' => 'required|in:male,female',
            'accent' => 'required|in:british,american,australian,neutral',
            'is_default' => 'boolean',
        ]);

        // Upload photo
        $uploadResult = $this->uploadFile(
            $request->file('photo'),
            'avatar-teachers'
        );

        if (!$uploadResult['success']) {
            return back()->withErrors(['photo' => 'Failed to upload photo'])->withInput();
        }

        // Create teacher
        $teacher = AvatarTeacher::create([
            'name' => $validated['name'],
            'photo_url' => $uploadResult['url'],
            'photo_path' => $uploadResult['path'],
            'elevenlabs_voice_id' => $validated['elevenlabs_voice_id'],
            'voice_name' => $validated['voice_name'] ?? null,
            'gender' => $validated['gender'],
            'accent' => $validated['accent'],
            'is_active' => true,
            'is_default' => $request->boolean('is_default'),
        ]);

        // If set as default, unset others
        if ($teacher->is_default) {
            AvatarTeacher::where('id', '!=', $teacher->id)->update(['is_default' => false]);
        }

        return redirect()->route('admin.avatar-teachers.index')
            ->with('success', 'Avatar teacher created successfully!');
    }

    /**
     * Show the form for editing an avatar teacher.
     */
    public function edit(AvatarTeacher $avatarTeacher)
    {
        // Get available voices from ElevenLabs (including custom voices)
        $elevenLabsService = new ElevenLabsService();
        $voicesResult = $elevenLabsService->getVoices(true);
        $voices = $voicesResult['success'] ? $voicesResult['voices'] : [];
        $groupedVoices = $voicesResult['success'] ? $voicesResult['grouped'] : [];

        return view('admin.avatar-teachers.edit', compact('avatarTeacher', 'voices', 'groupedVoices'));
    }

    /**
     * Update the specified avatar teacher.
     */
    public function update(Request $request, AvatarTeacher $avatarTeacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'elevenlabs_voice_id' => 'required|string|max:100',
            'voice_name' => 'nullable|string|max:100',
            'gender' => 'required|in:male,female',
            'accent' => 'required|in:british,american,australian,neutral',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'elevenlabs_voice_id' => $validated['elevenlabs_voice_id'],
            'voice_name' => $validated['voice_name'] ?? null,
            'gender' => $validated['gender'],
            'accent' => $validated['accent'],
            'is_active' => $request->boolean('is_active'),
            'is_default' => $request->boolean('is_default'),
        ];

        // Upload new photo if provided
        if ($request->hasFile('photo')) {
            $uploadResult = $this->uploadFile(
                $request->file('photo'),
                'avatar-teachers'
            );

            if ($uploadResult['success']) {
                // Delete old photo
                if ($avatarTeacher->photo_path) {
                    $this->deleteFile($avatarTeacher->photo_path, 'r2');
                }

                $updateData['photo_url'] = $uploadResult['url'];
                $updateData['photo_path'] = $uploadResult['path'];
            }
        }

        $avatarTeacher->update($updateData);

        // If set as default, unset others
        if ($avatarTeacher->is_default) {
            AvatarTeacher::where('id', '!=', $avatarTeacher->id)->update(['is_default' => false]);
        }

        return redirect()->route('admin.avatar-teachers.index')
            ->with('success', 'Avatar teacher updated successfully!');
    }

    /**
     * Remove the specified avatar teacher.
     */
    public function destroy(AvatarTeacher $avatarTeacher)
    {
        // Check if teacher has questions
        if ($avatarTeacher->questions()->exists()) {
            return back()->with('error', 'Cannot delete teacher with associated questions. Remove avatar from questions first.');
        }

        // Delete photo
        if ($avatarTeacher->photo_path) {
            $this->deleteFile($avatarTeacher->photo_path, 'r2');
        }

        $avatarTeacher->delete();

        return redirect()->route('admin.avatar-teachers.index')
            ->with('success', 'Avatar teacher deleted successfully!');
    }

    /**
     * Preview voice (returns audio URL for testing).
     */
    public function previewVoice(Request $request)
    {
        $request->validate([
            'voice_id' => 'required|string',
            'text' => 'nullable|string|max:200',
        ]);

        $elevenLabsService = new ElevenLabsService();
        $text = $request->input('text', 'Hello, I am your IELTS speaking examiner. How are you today?');

        $result = $elevenLabsService->generateSpeech($text, $request->voice_id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        // Return audio as base64 for preview
        return response()->json([
            'success' => true,
            'audio' => base64_encode($result['audio_content']),
            'content_type' => 'audio/mpeg',
        ]);
    }

    /**
     * Set teacher as default.
     */
    public function setDefault(AvatarTeacher $avatarTeacher)
    {
        $avatarTeacher->setAsDefault();

        return back()->with('success', "{$avatarTeacher->name} is now the default teacher.");
    }

    /**
     * Toggle teacher active status.
     */
    public function toggleActive(AvatarTeacher $avatarTeacher)
    {
        $avatarTeacher->update(['is_active' => !$avatarTeacher->is_active]);

        $status = $avatarTeacher->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "{$avatarTeacher->name} has been {$status}.");
    }

    /**
     * Generate avatar videos for pending questions.
     */
    public function generateAvatars(AvatarTeacher $avatarTeacher)
    {
        // Get pending questions for this teacher
        $pendingQuestions = Question::where('avatar_teacher_id', $avatarTeacher->id)
            ->whereIn('avatar_status', ['pending', 'failed'])
            ->get();

        if ($pendingQuestions->isEmpty()) {
            return back()->with('info', 'No pending questions found for this teacher.');
        }

        // Use AvatarGeneratorService to queue generation jobs
        $generatorService = new AvatarGeneratorService();
        $queued = $generatorService->generateBulk($pendingQuestions, $avatarTeacher);

        Log::info('Avatar generation started', [
            'teacher_id' => $avatarTeacher->id,
            'teacher_name' => $avatarTeacher->name,
            'queued_count' => $queued,
        ]);

        return back()->with('success', "{$queued} avatar videos queued for generation. This may take a few minutes.");
    }

    /**
     * Retry failed avatar generations for a teacher.
     */
    public function retryFailed(AvatarTeacher $avatarTeacher)
    {
        $failedQuestions = Question::where('avatar_teacher_id', $avatarTeacher->id)
            ->where('avatar_status', 'failed')
            ->get();

        if ($failedQuestions->isEmpty()) {
            return back()->with('info', 'No failed avatars to retry.');
        }

        $generatorService = new AvatarGeneratorService();
        $queued = $generatorService->generateBulk($failedQuestions, $avatarTeacher);

        return back()->with('success', "{$queued} failed avatars queued for retry.");
    }

    /**
     * Get generation progress for a teacher (AJAX endpoint).
     */
    public function getProgress(AvatarTeacher $avatarTeacher)
    {
        $questions = Question::where('avatar_teacher_id', $avatarTeacher->id)
            ->select('id', 'content', 'avatar_status', 'avatar_error', 'avatar_video_url', 'updated_at')
            ->orderBy('id')
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'content' => \Str::limit(strip_tags($q->content), 50),
                    'status' => $q->avatar_status,
                    'error' => $q->avatar_error,
                    'video_url' => $q->avatar_video_url,
                    'updated_at' => $q->updated_at->diffForHumans(),
                ];
            });

        $stats = [
            'total' => $questions->count(),
            'ready' => $questions->where('status', 'ready')->count(),
            'pending' => $questions->where('status', 'pending')->count(),
            'generating_audio' => $questions->where('status', 'generating_audio')->count(),
            'generating_video' => $questions->where('status', 'generating_video')->count(),
            'failed' => $questions->where('status', 'failed')->count(),
        ];

        $stats['in_progress'] = $stats['pending'] + $stats['generating_audio'] + $stats['generating_video'];
        $stats['percentage'] = $stats['total'] > 0 ? round(($stats['ready'] / $stats['total']) * 100) : 0;
        $stats['is_complete'] = $stats['in_progress'] === 0;

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'questions' => $questions,
        ]);
    }
}
