<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionReportController extends Controller
{
    /** Everything students have reported, newest first. */
    public function index(Request $request): View
    {
        $query = QuestionReport::with([
            'student:id,name,email',
            'question:id,test_set_id,question_type,order_number,part_number,content',
            'question.testSet:id,title,section_id',
            'question.testSet.section:id,name',
            'reviewer:id,name',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }

        if ($request->filled('section')) {
            $query->whereHas('question.testSet.section', fn ($q) => $q->where('name', $request->section));
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', $term)
                    ->orWhereHas('student', fn ($s) => $s->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhereHas('question.testSet', fn ($t) => $t->where('title', 'like', $term));
            });
        }

        $reports = $query->latest()->paginate(20)->withQueryString();

        // Counts for the status tabs, unaffected by the status filter itself so the tabs keep
        // showing where the rest of the queue is.
        $counts = QuestionReport::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.question-reports.index', [
            'reports' => $reports,
            'counts' => $counts,
            'total' => QuestionReport::count(),
        ]);
    }

    public function show(QuestionReport $questionReport): View
    {
        $questionReport->load([
            'student', 'reviewer', 'attempt',
            'question.testSet.section', 'question.options',
        ]);

        // Everyone else who flagged the same question — several reports on one question is the
        // strongest signal that the question really is wrong.
        $related = QuestionReport::with('student:id,name')
            ->where('question_id', $questionReport->question_id)
            ->where('id', '!=', $questionReport->id)
            ->latest()
            ->get();

        return view('admin.question-reports.show', compact('questionReport', 'related'));
    }

    public function update(Request $request, QuestionReport $questionReport): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(QuestionReport::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $questionReport->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report updated.');
    }

    public function destroy(QuestionReport $questionReport): RedirectResponse
    {
        $questionReport->delete();

        return redirect()->route('admin.question-reports.index')->with('success', 'Report deleted.');
    }
}
