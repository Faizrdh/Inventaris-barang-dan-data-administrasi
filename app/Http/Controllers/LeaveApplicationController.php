<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveApplicationController extends Controller
{
    /**
     * Display the leave application page.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        return view('admin.master.cuti.leave-application');
    }

    /**
     * Display list of leave applications in DataTables format.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
   // PERBAIKAN di LeaveApplicationController.php - method list()

public function list(Request $request): JsonResponse
{
    try {
        // Get latest leave applications
        $leaveApplications = LeaveApplication::with(['user', 'approver'])->latest();

        // Ensure response is given in AJAX condition
        if ($request->ajax()) {
            return DataTables::of($leaveApplications)
                ->addColumn('actions', function ($data) {
                    // Create action buttons for DataTables
                    $button = "<button class='edit btn btn-success btn-sm m-1' data-id='" . $data->id . "'><i class='fas fa-pen'></i> " . __("Edit") . "</button>";
                    $button .= "<button class='delete btn btn-danger btn-sm m-1' data-id='" . $data->id . "'><i class='fas fa-trash'></i> " . __("Delete") . "</button>";
                    $button .= "<button class='detail btn btn-info btn-sm m-1' data-id='" . $data->id . "'><i class='fas fa-eye'></i> " . __("Detail") . "</button>";
                    
                    if ($data->document_path) {
                        // PERBAIKAN: Generate URL yang benar untuk document
                        $documentUrl = asset('storage/' . $data->document_path);
                        $button .= "<a href='" . $documentUrl . "' target='_blank' class='btn btn-secondary btn-sm m-1'><i class='fas fa-file-pdf'></i> " . __("View Document") . "</a>";
                    }
                    return $button;
                })
                ->addColumn('status_label', function ($data) {
                    // Add status label according to application status
                    if ($data->status == 'pending') {
                        return "<span class='badge badge-warning'>" . __("Pending") . "</span>";
                    } elseif ($data->status == 'approved') {
                        return "<span class='badge badge-success'>" . __("Approved") . "</span>";
                    } else {
                        return "<span class='badge badge-danger'>" . __("Rejected") . "</span>";
                    }
                })
                // PERBAIKAN: Format tanggal ke format date saja (YYYY-MM-DD)
                ->editColumn('application_date', function ($data) {
                    return $data->application_date ? $data->application_date->format('Y-m-d') : '';
                })
                ->editColumn('start_date', function ($data) {
                    return $data->start_date ? $data->start_date->format('Y-m-d') : '';
                })
                ->editColumn('end_date', function ($data) {
                    return $data->end_date ? $data->end_date->format('Y-m-d') : '';
                })
                ->rawColumns(['actions', 'status_label'])
                ->make(true);
        }

        // If not AJAX, still must return a response
        return response()->json([
            'message' => 'Invalid request. Expected an AJAX request.'
        ], 400);
    } catch (\Exception $e) {
        Log::error('Error in leave application list: ' . $e->getMessage());
        return response()->json([
            'message' => 'An error occurred: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Store a new leave application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        try {
            // Manual validation
            $validated = $request->validate([
                'code' => 'required|string|unique:leave_applications,code',
                'name' => 'required|string|max:255',
                'employee_id' => 'required|string|max:255',
                'application_date' => 'required|date',
                'leave_type' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'total_days' => 'required|integer|min:1',
                'description' => 'required|string',
                'document' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
            ]);

            $leaveApplication = new LeaveApplication();
            $leaveApplication->code = $request->code;
            $leaveApplication->name = $request->name;
            $leaveApplication->employee_id = $request->employee_id;
            $leaveApplication->application_date = $request->application_date;
            $leaveApplication->leave_type = $request->leave_type;
            $leaveApplication->start_date = $request->start_date;
            $leaveApplication->end_date = $request->end_date;
            $leaveApplication->total_days = $request->total_days;
            $leaveApplication->description = $request->description;
            $leaveApplication->status = 'pending';
            $leaveApplication->user_id = Auth::id();

            // Handle file upload
            if ($request->hasFile('document')) {
                $path = $request->file('document')->store('leave_documents', 'public');
                $leaveApplication->document_path = $path;
            }

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json(["message" => __("Failed to save")])->setStatusCode(400);
            }

            return response()->json(["message" => __("Saved successfully")])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in save leave application: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leave application details.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer'
            ]);

            $leaveApplication = LeaveApplication::with(['user', 'approver'])->find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("Not found.")], 404);
            }

            return response()->json(["data" => $leaveApplication])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in get leave application detail: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update leave application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            // Manual validation
            $validated = $request->validate([
                'id' => 'required|integer',
                'name' => 'required|string|max:255',
                'employee_id' => 'required|string|max:255',
                'application_date' => 'required|date',
                'leave_type' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'total_days' => 'required|integer|min:1',
                'description' => 'required|string',
                'document' => 'nullable|file|mimes:pdf|max:5120',
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("Not found.")], 404);
            }

            // Check if application can still be edited (still pending)
            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("Cannot edit processed application")])->setStatusCode(400);
            }

            $leaveApplication->name = $request->name;
            $leaveApplication->employee_id = $request->employee_id;
            $leaveApplication->application_date = $request->application_date;
            $leaveApplication->leave_type = $request->leave_type;
            $leaveApplication->start_date = $request->start_date;
            $leaveApplication->end_date = $request->end_date;
            $leaveApplication->total_days = $request->total_days;
            $leaveApplication->description = $request->description;

            // Handle file upload
            if ($request->hasFile('document')) {
                // Delete old file if exists
                if ($leaveApplication->document_path) {
                    Storage::disk('public')->delete($leaveApplication->document_path);
                }

                $path = $request->file('document')->store('leave_documents', 'public');
                $leaveApplication->document_path = $path;
            }

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json(["message" => __("Failed to update")])->setStatusCode(400);
            }

            return response()->json(["message" => __("Updated successfully")])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in update leave application: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete leave application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer'
            ]);
            
            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("Not found.")], 404);
            }

            // Check if application can still be deleted (still pending)
            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("Cannot delete processed application")])->setStatusCode(400);
            }

            // Delete file if exists
            if ($leaveApplication->document_path) {
                Storage::disk('public')->delete($leaveApplication->document_path);
            }

            $status = $leaveApplication->delete();

            if (!$status) {
                return response()->json(["message" => __("Failed to delete")])->setStatusCode(400);
            }

            return response()->json(["message" => __("Deleted successfully")])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in delete leave application: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve leave application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer'
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("Not found.")], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("Application already processed")])->setStatusCode(400);
            }

            $leaveApplication->status = 'approved';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->save();

            return response()->json(["message" => __("Application approved successfully")])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in approve leave application: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject leave application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer'
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("Not found.")], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("Application already processed")])->setStatusCode(400);
            }

            $leaveApplication->status = 'rejected';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->save();

            return response()->json(["message" => __("Application rejected successfully")])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in reject leave application: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}