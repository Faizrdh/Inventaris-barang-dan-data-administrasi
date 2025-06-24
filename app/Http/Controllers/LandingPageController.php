<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LandingPageController extends Controller
{
    /**
     * Display the landing page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $leaveApplications = [];
        
        try {
            // Get recent leave applications for public display
            // You can adjust the filter based on your business logic
            // Option 1: Show all recent applications
            $leaveApplications = LeaveApplication::with(['user', 'approver'])
                ->latest('created_at')
                ->take(10) // Increased to 10 for better display
                ->get();
            
            // Option 2: Show only approved applications (uncomment if needed)
            // $leaveApplications = LeaveApplication::with(['user', 'approver'])
            //     ->where('status', 'approved')
            //     ->latest('approved_at')
            //     ->take(10)
            //     ->get();
            
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error fetching leave applications for landing page: ' . $e->getMessage());
            
            // Leave empty array - blade template has fallback static data
            $leaveApplications = [];
        }
        
        return view('layouts.landing', compact('leaveApplications'));
    }
}