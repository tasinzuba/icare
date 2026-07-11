<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BannedController extends Controller
{
    /**
     * Display the banned page
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isBanned()) {
            return redirect()->route('dashboard');
        }

        $latestAppeal = null;

        return view('banned.index', compact('user', 'latestAppeal'));
    }

    /**
     * Submit an appeal (feature removed).
     */
    public function appeal(Request $request)
    {
        return back()->with('error', 'Appeal submissions are no longer available.');
    }
}
