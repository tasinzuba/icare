<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestSectionController extends Controller
{
    /**
     * Display a listing of the test sections.
     */
    public function index(): View
    {
        $sections = TestSection::withCount('testSets')->get();
        
        return view('admin.sections.index', compact('sections'));
    }

    /**
     * Show the form for creating a new test section.
     */
    public function create(): View
    {
        return view('admin.sections.create');
    }

    /**
     * Store a newly created test section in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|unique:test_sections,name',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
        ]);
        
        TestSection::create($request->only(['name', 'description', 'time_limit']));
        
        return redirect()->route('admin.sections.index')
            ->with('success', 'Test section created successfully.');
    }

    /**
     * Display the specified test section.
     */
    public function show(TestSection $section): View
    {
        $section->load('testSets');
        
        return view('admin.sections.show', compact('section'));
    }

    /**
     * Show the form for editing the specified test section.
     */
    public function edit(TestSection $section): View
    {
        return view('admin.sections.edit', compact('section'));
    }

    /**
     * Update the specified test section in storage.
     */
    public function update(Request $request, TestSection $section): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|unique:test_sections,name,' . $section->id,
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
        ]);
        
        $section->update($request->only(['name', 'description', 'time_limit']));
        
        return redirect()->route('admin.sections.index')
            ->with('success', 'Test section updated successfully.');
    }

    /**
     * Remove the specified test section from storage.
     */
    public function destroy(TestSection $section): RedirectResponse
    {
        // Check if this section has associated test sets
        if ($section->testSets()->exists()) {
            return redirect()->route('admin.sections.index')
                ->with('error', 'Cannot delete section with associated test sets.');
        }
        
        $section->delete();
        
        return redirect()->route('admin.sections.index')
            ->with('success', 'Test section deleted successfully.');
    }
}