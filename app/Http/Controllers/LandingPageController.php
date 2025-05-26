<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication; // Assuming you have this model
use Illuminate\Support\Facades\DB;

class LandingPageController extends Controller
{
    /**
     * Display the landing page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get recent approved leave applications - limited to 6 for display
        $leaveApplications = [];
        
        // Check if LeaveApplication model exists and the table is available
        try {
            if (class_exists('App\Models\LeaveApplication')) {
                $leaveApplications = LeaveApplication::latest()
                    ->take(6)
                    ->get();
            } else {
                // Fallback to raw query if model doesn't exist
                $leaveApplications = DB::table('leave_applications')
                    ->orderBy('created_at', 'desc')
                    ->limit(6)
                    ->get();
            }
        } catch (\Exception $e) {
            // In case of error (table doesn't exist), leave it empty
            // The view has static fallback data for this case
        }
        
        return view('layouts.landing', compact('leaveApplications'));
    }
}