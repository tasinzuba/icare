<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchPackagePrice;
use App\Models\OfflinePackage;
use Illuminate\Http\Request;

class OfflinePackageController extends Controller
{
    /**
     * Display a listing of offline packages
     */
    public function index(Request $request)
    {
        $query = OfflinePackage::with('branch');

        // Filter by branch
        if ($request->filled('branch_id')) {
            if ($request->branch_id === 'global') {
                $query->whereNull('branch_id');
            } else {
                $query->where('branch_id', $request->branch_id);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $packages = $query->orderBy('display_order')
            ->orderBy('price')
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::where('active', true)->orderBy('name')->get();

        // Stats
        $totalPackages = OfflinePackage::count();
        $activePackages = OfflinePackage::where('is_active', true)->count();
        $globalPackages = OfflinePackage::whereNull('branch_id')->count();

        return view('admin.offline-packages.index', compact(
            'packages',
            'branches',
            'totalPackages',
            'activePackages',
            'globalPackages'
        ));
    }

    /**
     * Show the form for creating a new package
     */
    public function create()
    {
        $branches = Branch::where('active', true)->orderBy('name')->get();
        return view('admin.offline-packages.create', compact('branches'));
    }

    /**
     * Store a newly created package
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'full_tests_allowed' => 'required|integer|min:0|max:100',
            'section_tests_allowed' => 'required|integer|min:0|max:500',
            'validity_days' => 'required|integer|min:1|max:365',
            'price' => 'required|numeric|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        OfflinePackage::create([
            'name' => $request->name,
            'description' => $request->description,
            'full_tests_allowed' => $request->full_tests_allowed,
            'section_tests_allowed' => $request->section_tests_allowed,
            'validity_days' => $request->validity_days,
            'price' => $request->price,
            'branch_id' => $request->branch_id ?: null,
            'is_active' => $request->boolean('is_active', true),
            'display_order' => $request->display_order ?? 0,
        ]);

        return redirect()->route('admin.offline-packages.index')
            ->with('success', 'Offline package created successfully!');
    }

    /**
     * Show the form for editing package
     */
    public function edit(OfflinePackage $offlinePackage)
    {
        $branches = Branch::where('active', true)->orderBy('name')->get();
        $offlinePackage->load('branchPrices.branch');

        return view('admin.offline-packages.edit', compact('offlinePackage', 'branches'));
    }

    /**
     * Update package
     */
    public function update(Request $request, OfflinePackage $offlinePackage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'full_tests_allowed' => 'required|integer|min:0|max:100',
            'section_tests_allowed' => 'required|integer|min:0|max:500',
            'validity_days' => 'required|integer|min:1|max:365',
            'price' => 'required|numeric|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $offlinePackage->update([
            'name' => $request->name,
            'description' => $request->description,
            'full_tests_allowed' => $request->full_tests_allowed,
            'section_tests_allowed' => $request->section_tests_allowed,
            'validity_days' => $request->validity_days,
            'price' => $request->price,
            'branch_id' => $request->branch_id ?: null,
            'is_active' => $request->boolean('is_active', true),
            'display_order' => $request->display_order ?? 0,
        ]);

        return redirect()->route('admin.offline-packages.index')
            ->with('success', 'Offline package updated successfully!');
    }

    /**
     * Toggle package status
     */
    public function toggleStatus(OfflinePackage $offlinePackage)
    {
        $offlinePackage->update(['is_active' => !$offlinePackage->is_active]);

        return back()->with('success', 'Package status updated!');
    }

    /**
     * Remove package
     */
    public function destroy(OfflinePackage $offlinePackage)
    {
        $offlinePackage->delete();

        return redirect()->route('admin.offline-packages.index')
            ->with('success', 'Offline package deleted successfully!');
    }

    /**
     * Show branch pricing management for a package
     */
    public function branchPricing(OfflinePackage $offlinePackage)
    {
        // Only global packages can have branch-specific pricing
        if ($offlinePackage->branch_id) {
            return back()->with('error', 'Branch-specific packages cannot have custom branch pricing.');
        }

        $branches = Branch::where('active', true)->orderBy('name')->get();
        $offlinePackage->load('branchPrices');

        // Create a map of existing prices
        $existingPrices = $offlinePackage->branchPrices->keyBy('branch_id');

        return view('admin.offline-packages.branch-pricing', compact('offlinePackage', 'branches', 'existingPrices'));
    }

    /**
     * Update branch pricing for a package
     */
    public function updateBranchPricing(Request $request, OfflinePackage $offlinePackage)
    {
        $request->validate([
            'branch_prices' => 'array',
            'branch_prices.*.branch_id' => 'required|exists:branches,id',
            'branch_prices.*.custom_price' => 'nullable|numeric|min:0',
            'branch_prices.*.is_available' => 'boolean',
        ]);

        // Delete existing prices for this package
        $offlinePackage->branchPrices()->delete();

        // Create new prices
        if ($request->has('branch_prices')) {
            foreach ($request->branch_prices as $branchPrice) {
                if (isset($branchPrice['custom_price']) || isset($branchPrice['is_available'])) {
                    BranchPackagePrice::create([
                        'package_id' => $offlinePackage->id,
                        'branch_id' => $branchPrice['branch_id'],
                        'custom_price' => $branchPrice['custom_price'] ?? $offlinePackage->price,
                        'is_available' => $branchPrice['is_available'] ?? true,
                    ]);
                }
            }
        }

        return redirect()->route('admin.offline-packages.index')
            ->with('success', 'Branch pricing updated successfully!');
    }
}
