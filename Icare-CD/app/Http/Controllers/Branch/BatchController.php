<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\FullTest;
use App\Models\TestSet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    private function getBranch()
    {
        return auth()->user()->getPrimaryBranch();
    }

    public function index(): View
    {
        $branch = $this->getBranch();

        $batches = Batch::forBranch($branch->id)
            ->withCount('enrollments')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('branch.batches.index', compact('batches', 'branch'));
    }

    public function create(): View
    {
        $fullTests = FullTest::where('is_for_offline', true)
            ->orderBy('order_number')
            ->get(['id', 'title', 'is_premium']);

        $sectionTests = TestSet::where('is_for_offline', true)
            ->with('section:id,name')
            ->orderBy('title')
            ->get(['id', 'title', 'section_id']);

        return view('branch.batches.create', compact('fullTests', 'sectionTests'));
    }

    public function store(Request $request)
    {
        $branch = $this->getBranch();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'full_tests_allowed' => 'required|integer|min:0|max:100',
            'validity_days' => 'required|integer|min:1|max:365',
            'section_limit_listening' => 'nullable|integer|min:0|max:100',
            'section_limit_reading' => 'nullable|integer|min:0|max:100',
            'section_limit_writing' => 'nullable|integer|min:0|max:100',
            'section_limit_speaking' => 'nullable|integer|min:0|max:100',
            'allowed_full_tests' => 'nullable|array',
            'allowed_full_tests.*' => 'exists:full_tests,id',
            'allowed_section_tests' => 'nullable|array',
            'allowed_section_tests.*' => 'exists:test_sets,id',
        ]);

        $sectionTestLimits = [
            'listening' => (int) ($validated['section_limit_listening'] ?? 0),
            'reading' => (int) ($validated['section_limit_reading'] ?? 0),
            'writing' => (int) ($validated['section_limit_writing'] ?? 0),
            'speaking' => (int) ($validated['section_limit_speaking'] ?? 0),
        ];

        Batch::create([
            'branch_id' => $branch->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'full_tests_allowed' => $validated['full_tests_allowed'],
            'section_test_limits' => $sectionTestLimits,
            'validity_days' => $validated['validity_days'],
            'allowed_full_tests' => $validated['allowed_full_tests'] ?? null,
            'allowed_section_tests' => $validated['allowed_section_tests'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('branch.batches.index')
            ->with('success', 'Batch "' . $validated['name'] . '" created successfully.');
    }

    public function show(Batch $batch): View
    {
        $branch = $this->getBranch();
        abort_if($batch->branch_id !== $branch->id, 403);

        $enrollments = $batch->enrollments()
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Load test names for display
        $fullTestNames = [];
        if ($batch->allowed_full_tests) {
            $fullTestNames = FullTest::whereIn('id', $batch->allowed_full_tests)->pluck('title', 'id');
        }
        $sectionTestNames = [];
        if ($batch->allowed_section_tests) {
            $sectionTestNames = TestSet::whereIn('id', $batch->allowed_section_tests)
                ->with('section:id,name')
                ->get()
                ->mapWithKeys(fn($t) => [$t->id => $t->title . ' (' . ucfirst($t->section->name ?? '') . ')']);
        }

        return view('branch.batches.show', compact('batch', 'enrollments', 'fullTestNames', 'sectionTestNames'));
    }

    public function edit(Batch $batch): View
    {
        $branch = $this->getBranch();
        abort_if($batch->branch_id !== $branch->id, 403);

        $fullTests = FullTest::where('is_for_offline', true)
            ->orderBy('order_number')
            ->get(['id', 'title', 'is_premium']);

        $sectionTests = TestSet::where('is_for_offline', true)
            ->with('section:id,name')
            ->orderBy('title')
            ->get(['id', 'title', 'section_id']);

        return view('branch.batches.edit', compact('batch', 'fullTests', 'sectionTests'));
    }

    public function update(Request $request, Batch $batch)
    {
        $branch = $this->getBranch();
        abort_if($batch->branch_id !== $branch->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'full_tests_allowed' => 'required|integer|min:0|max:100',
            'validity_days' => 'required|integer|min:1|max:365',
            'section_limit_listening' => 'nullable|integer|min:0|max:100',
            'section_limit_reading' => 'nullable|integer|min:0|max:100',
            'section_limit_writing' => 'nullable|integer|min:0|max:100',
            'section_limit_speaking' => 'nullable|integer|min:0|max:100',
            'allowed_full_tests' => 'nullable|array',
            'allowed_full_tests.*' => 'exists:full_tests,id',
            'allowed_section_tests' => 'nullable|array',
            'allowed_section_tests.*' => 'exists:test_sets,id',
            'sync_students' => 'nullable|boolean',
        ]);

        $sectionTestLimits = [
            'listening' => (int) ($validated['section_limit_listening'] ?? 0),
            'reading' => (int) ($validated['section_limit_reading'] ?? 0),
            'writing' => (int) ($validated['section_limit_writing'] ?? 0),
            'speaking' => (int) ($validated['section_limit_speaking'] ?? 0),
        ];

        $batch->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'full_tests_allowed' => $validated['full_tests_allowed'],
            'section_test_limits' => $sectionTestLimits,
            'validity_days' => $validated['validity_days'],
            'allowed_full_tests' => $validated['allowed_full_tests'] ?? null,
            'allowed_section_tests' => $validated['allowed_section_tests'] ?? null,
        ]);

        // Sync updated config to all active enrollments in this batch
        if ($request->boolean('sync_students')) {
            $totalSectionTests = array_sum($sectionTestLimits);
            $synced = $batch->enrollments()
                ->where('status', 'active')
                ->update([
                    'full_tests_allowed' => $batch->full_tests_allowed,
                    'section_test_limits' => json_encode($sectionTestLimits),
                    'section_tests_allowed' => $totalSectionTests,
                    'allowed_full_tests' => $batch->allowed_full_tests ? json_encode($batch->allowed_full_tests) : null,
                    'allowed_section_tests' => $batch->allowed_section_tests ? json_encode($batch->allowed_section_tests) : null,
                ]);

            return redirect()->route('branch.batches.show', $batch)
                ->with('success', "Batch updated and synced to {$synced} active students.");
        }

        return redirect()->route('branch.batches.show', $batch)
            ->with('success', 'Batch updated successfully. Active students were NOT synced.');
    }

    public function destroy(Batch $batch)
    {
        $branch = $this->getBranch();
        abort_if($batch->branch_id !== $branch->id, 403);

        // Archive instead of delete to preserve enrollment references
        $batch->update(['status' => 'archived']);

        return redirect()->route('branch.batches.index')
            ->with('success', 'Batch archived successfully.');
    }
}
