<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FullTest;
use App\Models\TestSet;
use App\Models\TestSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FullTestController extends Controller
{
    /**
     * Display a listing of full tests.
     */
    public function index()
    {
        $fullTests = FullTest::with('testSets')
            ->orderBy('order_number')
            ->paginate(10);
        
        return view('admin.full-tests.index', compact('fullTests'));
    }

    /**
     * Show the form for creating a new full test.
     */
    public function create()
    {
        $testSets = TestSet::with('section')
            ->where('active', true)
            ->get()
            ->groupBy(fn($item) => strtolower($item->section->name));

        return view('admin.full-tests.create', compact('testSets'));
    }

    /**
     * Store a newly created full test.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_premium' => 'nullable|boolean',
            'is_for_offline' => 'nullable|boolean',
            'is_for_online' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'listening_test_set_id' => 'nullable|exists:test_sets,id',
            'reading_test_set_id' => 'nullable|exists:test_sets,id',
            'writing_test_set_id' => 'nullable|exists:test_sets,id',
            'speaking_test_set_id' => 'nullable|exists:test_sets,id',
        ]);

        // Ensure at least one visibility option is selected
        if (!$request->has('is_for_offline') && !$request->has('is_for_online')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please make this test available to your branch/offline students.');
        }

        // Validate minimum 3 sections
        $selectedSections = array_filter([
            $validated['listening_test_set_id'] ?? null,
            $validated['reading_test_set_id'] ?? null,
            $validated['writing_test_set_id'] ?? null,
            $validated['speaking_test_set_id'] ?? null,
        ]);

        if (count($selectedSections) < 3) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please select at least 3 sections to create a full test.');
        }

        DB::beginTransaction();

        try {
            // Create full test
            $fullTest = FullTest::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'is_premium' => $validated['is_premium'] ?? false,
                'is_for_offline' => $request->has('is_for_offline'),
                'is_for_online' => $request->has('is_for_online'),
                'active' => $validated['active'] ?? true,
                'order_number' => FullTest::max('order_number') + 1
            ]);
            
            // Attach test sets (only non-null ones)
            $sections = [
                'listening' => $validated['listening_test_set_id'] ?? null,
                'reading' => $validated['reading_test_set_id'] ?? null,
                'writing' => $validated['writing_test_set_id'] ?? null,
                'speaking' => $validated['speaking_test_set_id'] ?? null,
            ];
            
            $order = 1;
            foreach ($sections as $type => $testSetId) {
                if ($testSetId) {
                    $fullTest->testSets()->attach($testSetId, [
                        'section_type' => $type,
                        'order_number' => $order++
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.full-tests.index')
                ->with('success', 'Full test created successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create full test. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified full test.
     */
    public function show(FullTest $fullTest)
    {
        $fullTest->load('testSets.section', 'attempts.user');
        
        return view('admin.full-tests.show', compact('fullTest'));
    }

    /**
     * Show the form for editing the specified full test.
     */
    public function edit(FullTest $fullTest)
    {
        $testSets = TestSet::with('section')
            ->where('active', true)
            ->get()
            ->groupBy(fn($item) => strtolower($item->section->name));

        $fullTest->load('testSets');

        return view('admin.full-tests.edit', compact('fullTest', 'testSets'));
    }

    /**
     * Update the specified full test.
     */
    public function update(Request $request, FullTest $fullTest)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_premium' => 'nullable|boolean',
            'is_for_offline' => 'nullable|boolean',
            'is_for_online' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'listening_test_set_id' => 'nullable|exists:test_sets,id',
            'reading_test_set_id' => 'nullable|exists:test_sets,id',
            'writing_test_set_id' => 'nullable|exists:test_sets,id',
            'speaking_test_set_id' => 'nullable|exists:test_sets,id',
        ]);

        // Ensure at least one visibility option is selected
        if (!$request->has('is_for_offline') && !$request->has('is_for_online')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please make this test available to your branch/offline students.');
        }

        // Validate minimum 3 sections
        $selectedSections = array_filter([
            $validated['listening_test_set_id'] ?? null,
            $validated['reading_test_set_id'] ?? null,
            $validated['writing_test_set_id'] ?? null,
            $validated['speaking_test_set_id'] ?? null,
        ]);

        if (count($selectedSections) < 3) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please select at least 3 sections to update the full test.');
        }

        DB::beginTransaction();

        try {
            // Log the request data for debugging
            \Log::info('FullTest Update Request', [
                'has_is_for_offline' => $request->has('is_for_offline'),
                'has_is_for_online' => $request->has('is_for_online'),
                'is_for_offline_value' => $request->input('is_for_offline'),
                'is_for_online_value' => $request->input('is_for_online'),
            ]);

            // Update full test
            $fullTest->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'is_premium' => $validated['is_premium'] ?? false,
                'is_for_offline' => $request->has('is_for_offline'),
                'is_for_online' => $request->has('is_for_online'),
                'active' => $validated['active'] ?? true,
            ]);

            \Log::info('FullTest After Update', [
                'id' => $fullTest->id,
                'is_for_offline' => $fullTest->is_for_offline,
                'is_for_online' => $fullTest->is_for_online,
            ]);
            
            // Sync test sets
            $fullTest->testSets()->detach();
            
            $sections = [
                'listening' => $validated['listening_test_set_id'] ?? null,
                'reading' => $validated['reading_test_set_id'] ?? null,
                'writing' => $validated['writing_test_set_id'] ?? null,
                'speaking' => $validated['speaking_test_set_id'] ?? null,
            ];
            
            $order = 1;
            foreach ($sections as $type => $testSetId) {
                if ($testSetId) {
                    $fullTest->testSets()->attach($testSetId, [
                        'section_type' => $type,
                        'order_number' => $order++
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.full-tests.index')
                ->with('success', 'Full test updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update full test. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified full test.
     */
    public function destroy(FullTest $fullTest)
    {
        DB::beginTransaction();
        
        try {
            // Check if there are any attempts
            if ($fullTest->attempts()->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete this full test because it has ' . $fullTest->attempts()->count() . ' student attempts. Please deactivate it instead.');
            }
            
            // Get test title for success message
            $testTitle = $fullTest->title;
            
            // Detach all test sets before deleting
            $fullTest->testSets()->detach();
            
            // Delete the full test
            $fullTest->delete();
            
            DB::commit();
            
            return redirect()->route('admin.full-tests.index')
                ->with('success', 'Full test "' . $testTitle . '" has been deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Failed to delete full test. Please try again or contact support if the problem persists.');
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(FullTest $fullTest)
    {
        $fullTest->update([
            'active' => !$fullTest->active
        ]);
        
        return redirect()->back()
            ->with('success', 'Full test status updated successfully.');
    }

    /**
     * Reorder full tests.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:full_tests,id'
        ]);
        
        foreach ($validated['ids'] as $order => $id) {
            FullTest::where('id', $id)->update(['order_number' => $order + 1]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Display all full test attempts for a specific user.
     */
    public function userAttempts(Request $request, $userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        
        $query = \App\Models\FullTestAttempt::with(['fullTest', 'user'])
            ->where('user_id', $user->id);
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Filter by full test
        if ($request->has('full_test') && $request->full_test !== '') {
            $query->where('full_test_id', $request->full_test);
        }
        
        $attempts = $query->latest()->paginate(15);
        
        // Get full tests for filtering
        $fullTests = FullTest::orderBy('title')->get();
        
        // Get stats for this user
        $stats = [
            'total_attempts' => $user->fullTestAttempts()->count(),
            'completed_attempts' => $user->fullTestAttempts()->where('status', 'completed')->count(),
            'in_progress_attempts' => $user->fullTestAttempts()->where('status', 'in_progress')->count(),
            'abandoned_attempts' => $user->fullTestAttempts()->where('status', 'abandoned')->count(),
            'average_overall_score' => $user->fullTestAttempts()
                ->where('status', 'completed')
                ->whereNotNull('overall_band_score')
                ->avg('overall_band_score'),
            'best_overall_score' => $user->fullTestAttempts()
                ->where('status', 'completed')
                ->whereNotNull('overall_band_score')
                ->max('overall_band_score'),
        ];
        
        return view('admin.full-tests.user-attempts', compact('attempts', 'user', 'fullTests', 'stats'));
    }

    /**
     * Show detailed view of a full test attempt with all sections and answers.
     */
    public function showAttempt(\App\Models\FullTestAttempt $fullTestAttempt)
    {
        // Load all necessary relationships
        $fullTestAttempt->load([
            'fullTest',
            'user',
            'sectionAttempts.studentAttempt' => function($query) {
                $query->with([
                    'testSet.section',
                    'testSet.questions' => function($q) {
                        $q->where('question_type', '!=', 'passage')
                          ->orderBy('order_number');
                    },
                    'answers.question',
                    'answers.selectedOption',
                    'answers.speakingRecording',
                    'humanEvaluationRequest.humanEvaluation'
                ]);
            }
        ]);

        return view('admin.full-tests.show-attempt', compact('fullTestAttempt'));
    }

    /**
     * Update section score for a full test attempt.
     */
    public function updateScore(Request $request, \App\Models\FullTestAttempt $fullTestAttempt)
    {
        $validated = $request->validate([
            'section' => 'required|in:listening,reading,writing,speaking',
            'score' => 'required|numeric|min:0|max:9',
        ]);

        $section = $validated['section'];
        $score = (float) $validated['score'];

        // Update the section score
        $fullTestAttempt->updateSectionScore($section, $score);

        return redirect()->route('admin.full-test-attempts.show', $fullTestAttempt)
            ->with('success', ucfirst($section) . ' score updated successfully to ' . number_format($score, 1));
    }
}
