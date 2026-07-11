<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers
     */
    public function index()
    {
        $teachers = Teacher::with('user')
            ->withCount('evaluationRequests')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.teachers.index', compact('teachers'));
    }
    
    /**
     * Show the form for creating a new teacher
     */
    public function create()
    {
        return view('admin.teachers.create');
    }
    
    /**
     * Store a newly created teacher
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'specialization' => 'required|array|min:1',
            'experience_years' => 'required|integer|min:0',
            'evaluation_price_tokens' => 'required|integer|min:1',
            'qualifications' => 'nullable|array',
            'languages' => 'nullable|array',
            'profile_description' => 'nullable|string|max:1000'
        ]);
        
        DB::transaction(function () use ($request) {
            // Create user account for teacher
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'is_admin' => false,
            ]);
            
            // Create teacher profile
            Teacher::create([
                'user_id' => $user->id,
                'specialization' => $request->specialization,
                'experience_years' => $request->experience_years,
                'evaluation_price_tokens' => $request->evaluation_price_tokens,
                'qualifications' => $request->qualifications,
                'languages' => $request->languages,
                'profile_description' => $request->profile_description,
            ]);
        });
        
        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher added successfully!');
    }
    
    /**
     * Display the specified teacher
     */
    public function show(Teacher $teacher)
    {
        $teacher->load([
            'user',
            'evaluationRequests' => function ($query) {
                $query->latest()->with('studentAttempt.testSet.section', 'student');
            }
        ]);
        
        // Get statistics
        $stats = [
            'total_evaluations' => $teacher->evaluationRequests()->count(),
            'completed_evaluations' => $teacher->evaluationRequests()->where('status', 'completed')->count(),
            'pending_evaluations' => $teacher->evaluationRequests()->where('status', 'pending')->count(),
            'in_progress_evaluations' => $teacher->evaluationRequests()->where('status', 'in_progress')->count(),
            'average_rating' => $teacher->rating,
            'total_earnings' => $teacher->evaluationRequests()->sum('tokens_used')
        ];
        
        return view('admin.teachers.show', compact('teacher', 'stats'));
    }
    
    /**
     * Show the form for editing teacher
     */
    public function edit(Teacher $teacher)
    {
        return view('admin.teachers.edit', compact('teacher'));
    }
    
    /**
     * Update teacher
     */
    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'specialization' => 'required|array|min:1',
            'experience_years' => 'required|integer|min:0',
            'evaluation_price_tokens' => 'required|integer|min:1',
            'qualifications' => 'nullable|array',
            'languages' => 'nullable|array',
            'profile_description' => 'nullable|string|max:1000',
            'is_available' => 'boolean'
        ]);
        
        $teacher->update($request->all());
        
        return redirect()->route('admin.teachers.show', $teacher)
            ->with('success', 'Teacher updated successfully!');
    }
    
    /**
     * Toggle teacher availability
     */
    public function toggleAvailability(Teacher $teacher)
    {
        $teacher->update(['is_available' => !$teacher->is_available]);
        
        return back()->with('success', 'Teacher availability updated!');
    }
    
    /**
     * Remove teacher
     */
    public function destroy(Teacher $teacher)
    {
        // Check if teacher has pending evaluations
        if ($teacher->evaluationRequests()->whereIn('status', ['pending', 'in_progress'])->exists()) {
            return back()->with('error', 'Cannot remove teacher with pending evaluations!');
        }
        
        $teacher->delete();
        
        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher removed successfully!');
    }
}
