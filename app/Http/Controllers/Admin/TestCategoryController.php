<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestCategory;
use App\Models\TestSet;
use App\Models\TestSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TestCategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $categories = TestCategory::withCount(['testSets', 'activeTestSets'])
            ->ordered()
            ->paginate(20);

        return view('admin.test-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $icons = [
            'academic-cap' => 'Academic Cap',
            'briefcase' => 'Briefcase',
            'clipboard-list' => 'Clipboard List',
            'book-open' => 'Book Open',
            'light-bulb' => 'Light Bulb',
            'puzzle' => 'Puzzle',
        ];

        return view('admin.test-categories.create', compact('icons'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:test_categories,slug',
            'description' => 'nullable|string',
            'icon' => 'required|string',
            'color' => 'required|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->has('is_active');

        TestCategory::create($validated);

        return redirect()->route('admin.test-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(TestCategory $testCategory)
    {
        $testCategory->load(['testSets.section']);
        
        // Get test sets by section
        $testSetsBySection = $testCategory->testSets()
            ->with('section')
            ->get()
            ->groupBy('section.name');

        return view('admin.test-categories.show', compact('testCategory', 'testSetsBySection'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(TestCategory $testCategory)
    {
        $icons = [
            'academic-cap' => 'Academic Cap',
            'briefcase' => 'Briefcase',
            'clipboard-list' => 'Clipboard List',
            'book-open' => 'Book Open',
            'light-bulb' => 'Light Bulb',
            'puzzle' => 'Puzzle',
        ];

        return view('admin.test-categories.edit', compact('testCategory', 'icons'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, TestCategory $testCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('test_categories', 'slug')->ignore($testCategory->id),
            ],
            'description' => 'nullable|string',
            'icon' => 'required|string',
            'color' => 'required|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->has('is_active');

        $testCategory->update($validated);

        return redirect()->route('admin.test-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(TestCategory $testCategory)
    {
        if ($testCategory->testSets()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated test sets.');
        }

        $testCategory->delete();

        return redirect()->route('admin.test-categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus(TestCategory $testCategory)
    {
        $testCategory->update([
            'is_active' => !$testCategory->is_active,
        ]);

        return back()->with('success', 'Category status updated successfully.');
    }

    /**
     * Show form to add test sets to category.
     */
    public function manageTestSets(TestCategory $testCategory)
    {
        $sections = TestSection::with(['testSets' => function ($query) {
            $query->where('active', true)->orderBy('title');
        }])->get();

        $assignedTestSetIds = $testCategory->testSets()->pluck('test_sets.id')->toArray();

        return view('admin.test-categories.manage-test-sets', compact('testCategory', 'sections', 'assignedTestSetIds'));
    }

    /**
     * Update test sets for a category.
     */
    public function updateTestSets(Request $request, TestCategory $testCategory)
    {
        $validated = $request->validate([
            'test_sets' => 'array',
            'test_sets.*' => 'exists:test_sets,id',
        ]);

        $testCategory->testSets()->sync($validated['test_sets'] ?? []);

        return redirect()->route('admin.test-categories.show', $testCategory)
            ->with('success', 'Test sets updated successfully.');
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:test_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['categories'] as $categoryData) {
            TestCategory::where('id', $categoryData['id'])
                ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
