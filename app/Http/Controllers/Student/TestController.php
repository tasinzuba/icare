<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TestSection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestController extends Controller
{
    /**
     * Display a listing of the available test sections.
     */
    public function index(): View
    {
        $sections = TestSection::all();
        
        return view('student.test.index', compact('sections'));
    }
}