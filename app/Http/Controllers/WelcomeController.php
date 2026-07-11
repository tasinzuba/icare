<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Leaderboard feature removed
        $topPerformers = collect();

        return view('welcome', compact('topPerformers'));
    }
}
