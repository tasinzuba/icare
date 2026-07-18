<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestSection;
use App\Models\TestSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestSetController extends Controller
{
    /**
     * Display a listing of the test sets.
     */
    public function index(Request $request): View
    {
        $query = TestSet::with('section');
        
        // Filter by section
        if ($request->has('section')) {
            $query->whereHas('section', function ($q) use ($request) {
                $q->where('name', $request->section);
            });
        }
        
        $testSets = $query->latest()->paginate(15);
        
        $sections = TestSection::all();
        
        return view('admin.test-sets.index', compact('testSets', 'sections'));
    }

    /**
     * Show the form for creating a new test set.
     */
    public function create(): View
    {
        $sections = TestSection::all();
        $avatarTeachers = \App\Models\AvatarTeacher::where('is_active', true)->get();

        return view('admin.test-sets.create', compact('sections', 'avatarTeachers'));
    }

    /**
     * Store a newly created test set in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'section_id' => 'required|exists:test_sections,id',
            'active' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'is_for_offline' => 'nullable|boolean',
            'is_for_online' => 'nullable|boolean',
            'avatar_teacher_id' => 'nullable|exists:avatar_teachers,id',
            'writing_task_type' => 'nullable|in:task1,task2',
            'writing_category' => 'nullable|string|in:' . implode(',', array_keys(config('writing_categories', []))),
            'test_type' => 'nullable|in:academic,general',
            'time_limit_minutes' => 'nullable|integer|min:5|max:120',
        ]);

        // Ensure at least one visibility option is selected
        if (!$request->has('is_for_offline') && !$request->has('is_for_online')) {
            return back()->withInput()->withErrors([
                'is_for_offline' => 'Please make this test available to your branch/offline students.'
            ]);
        }

        // Reading module type (academic/general) is only meaningful for Reading sets.
        $isReading = \App\Models\TestSection::whereKey($request->section_id)->value('name') === 'reading';

        TestSet::create([
            'title' => $request->title,
            'section_id' => $request->section_id,
            'active' => $request->has('active'),
            'is_premium' => $request->has('is_premium'),
            'is_for_offline' => $request->has('is_for_offline'),
            'is_for_online' => $request->has('is_for_online'),
            'avatar_teacher_id' => $request->avatar_teacher_id ?: null,
            'writing_task_type' => $request->writing_task_type ?: null,
            'writing_category' => $request->writing_category ?: null,
            'time_limit_minutes' => $request->time_limit_minutes ?: null,
            'test_type' => $isReading ? ($request->input('test_type') ?: 'academic') : null,
        ]);

        return redirect()->route('admin.test-sets.index')
            ->with('success', 'Test set created successfully.');
    }

    /**
     * Display the specified test set.
     */
    public function show(TestSet $testSet): View
    {
        $testSet->load(['section', 'questions' => function ($query) {
            $query->orderBy('order_number');
        }, 'questions.options']);

        return view('admin.test-sets.show', compact('testSet'));
    }

    /**
     * Preview test set as a student would see it (read-only).
     */
    public function preview(TestSet $testSet): View
    {
        $testSet->load([
            'section',
            'questions' => function ($query) {
                $query->where('question_type', '!=', 'passage')
                      ->orderBy('part_number')
                      ->orderBy('order_number');
            },
            'questions.options',
            'questions.testSet.section',
        ]);

        // Group by part for display
        $questionsByPart = $testSet->questions->groupBy('part_number');

        return view('admin.test-sets.preview', compact('testSet', 'questionsByPart'));
    }

    /**
     * Show the form for editing the specified test set.
     */
    public function edit(TestSet $testSet): View
    {
        $testSet->load('section'); // Load section for avatar visibility check
        $sections = TestSection::all();
        $avatarTeachers = \App\Models\AvatarTeacher::where('is_active', true)->get();

        return view('admin.test-sets.edit', compact('testSet', 'sections', 'avatarTeachers'));
    }

    /**
     * Update the specified test set in storage.
     */
    public function update(Request $request, TestSet $testSet): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'section_id' => 'required|exists:test_sections,id',
            'active' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'is_for_offline' => 'nullable|boolean',
            'is_for_online' => 'nullable|boolean',
            'avatar_teacher_id' => 'nullable|exists:avatar_teachers,id',
            'writing_task_type' => 'nullable|in:task1,task2',
            'writing_category' => 'nullable|string|in:' . implode(',', array_keys(config('writing_categories', []))),
            'test_type' => 'nullable|in:academic,general',
            'time_limit_minutes' => 'nullable|integer|min:5|max:120',
        ]);

        // Ensure at least one visibility option is selected
        if (!$request->has('is_for_offline') && !$request->has('is_for_online')) {
            return back()->withInput()->withErrors([
                'is_for_offline' => 'Please make this test available to your branch/offline students.'
            ]);
        }

        $newAvatarTeacherId = $request->avatar_teacher_id ?: null;
        $oldAvatarTeacherId = $testSet->avatar_teacher_id;
        // Reading module type (academic/general) is only meaningful for Reading sets.
        $isReading = \App\Models\TestSection::whereKey($request->section_id)->value('name') === 'reading';

        $testSet->update([
            'title' => $request->title,
            'section_id' => $request->section_id,
            'active' => $request->has('active'),
            'is_premium' => $request->has('is_premium'),
            'is_for_offline' => $request->has('is_for_offline'),
            'is_for_online' => $request->has('is_for_online'),
            'avatar_teacher_id' => $newAvatarTeacherId,
            'writing_task_type' => $request->writing_task_type ?: null,
            'writing_category' => $request->writing_category ?: null,
            'time_limit_minutes' => $request->time_limit_minutes ?: null,
            'test_type' => $isReading ? ($request->input('test_type') ?: 'academic') : null,
        ]);

        // Auto-assign avatar teacher to all questions in this test set (Speaking only)
        $questionsUpdated = 0;
        if ($newAvatarTeacherId && $newAvatarTeacherId != $oldAvatarTeacherId) {
            // Assign to questions that don't have avatar or have different teacher
            $questionsUpdated = $testSet->questions()
                ->where(function ($query) use ($newAvatarTeacherId) {
                    $query->whereNull('avatar_teacher_id')
                          ->orWhere('avatar_teacher_id', '!=', $newAvatarTeacherId);
                })
                ->update([
                    'avatar_teacher_id' => $newAvatarTeacherId,
                    'avatar_status' => 'pending',
                    'avatar_audio_url' => null,
                    'avatar_video_url' => null,
                    'avatar_duration' => null,
                    'avatar_error' => null,
                ]);
        } elseif (!$newAvatarTeacherId && $oldAvatarTeacherId) {
            // Avatar teacher removed - clear from all questions
            $questionsUpdated = $testSet->questions()
                ->whereNotNull('avatar_teacher_id')
                ->update([
                    'avatar_teacher_id' => null,
                    'avatar_status' => 'none',
                    'avatar_audio_url' => null,
                    'avatar_video_url' => null,
                    'avatar_duration' => null,
                    'avatar_error' => null,
                ]);
        }

        $message = 'Test set updated successfully.';
        if ($questionsUpdated > 0) {
            $message .= " {$questionsUpdated} question(s) updated with new avatar teacher.";
        }

        return redirect()->route('admin.test-sets.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified test set from storage.
     */
    public function destroy(TestSet $testSet): RedirectResponse
    {
        // Check if this test set has associated questions
        if ($testSet->questions()->exists()) {
            return redirect()->route('admin.test-sets.index')
                ->with('error', 'Cannot delete test set with associated questions.');
        }
        
        // Check if this test set has associated student attempts
        if ($testSet->attempts()->exists()) {
            return redirect()->route('admin.test-sets.index')
                ->with('error', 'Cannot delete test set with associated student attempts.');
        }
        
        $testSet->delete();
        
        return redirect()->route('admin.test-sets.index')
            ->with('success', 'Test set deleted successfully.');
    }
}