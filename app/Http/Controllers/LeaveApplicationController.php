<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewLeaveApplicationMail;

class LeaveApplicationController extends Controller
{
    public function index(): View
    {
        return view('admin.master.cuti.leave-application');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            if (!$request->ajax()) {
                return $this->errorResponse('Invalid request. Expected an AJAX request.', 400);
            }

            $leaveApplications = LeaveApplication::with(['user', 'approver'])->latest();

            return DataTables::of($leaveApplications)
                ->addColumn('actions', fn($data) => $this->buildActionButtons($data))
                ->addColumn('status_label', fn($data) => $data->status_label)
                ->editColumn('application_date', fn($data) => $data->application_date?->format('Y-m-d'))
                ->editColumn('start_date', fn($data) => $data->start_date?->format('Y-m-d'))
                ->editColumn('end_date', fn($data) => $data->end_date?->format('Y-m-d'))
                ->rawColumns(['actions', 'status_label'])
                ->make(true);

        } catch (\Exception $e) {
            return $this->logAndReturnError('Error in leave application list', $e);
        }
    }

    public function save(Request $request): JsonResponse
{
    try {
        $validated = $this->validateLeaveApplication($request);
        
        $leaveApplication = new LeaveApplication($validated);
        $leaveApplication->status = 'pending';
        $leaveApplication->user_id = Auth::id();

        if ($request->hasFile('document')) {
            $leaveApplication->document_path = $leaveApplication->saveDocument($request->file('document'));
        }

        if (!$leaveApplication->save()) {
            return $this->errorResponse(__("Failed to save"), 400);
        }

        // Kirim email ke supervisor dengan validasi yang lebih baik
        $this->sendEmailToSupervisor($leaveApplication);

        return $this->successResponse(__("Saved successfully"));

    } catch (\Exception $e) {
        return $this->logAndReturnError('Error in save leave application', $e);
    }
}

// Method terpisah untuk mengirim email ke supervisor
private function sendEmailToSupervisor(LeaveApplication $leaveApplication): void
{
    try {
        $supervisorEmail = env('SUPERVISOR_EMAIL');
        
        if (!$supervisorEmail) {
            Log::warning('SUPERVISOR_EMAIL not configured in .env file');
            return;
        }

        if (!filter_var($supervisorEmail, FILTER_VALIDATE_EMAIL)) {
            Log::error('Invalid SUPERVISOR_EMAIL format: ' . $supervisorEmail);
            return;
        }

        Mail::to($supervisorEmail)->send(new NewLeaveApplicationMail($leaveApplication));
        Log::info('Email sent successfully to supervisor: ' . $supervisorEmail . ' for application: ' . $leaveApplication->code);
        
    } catch (\Exception $e) {
        Log::error('Failed to send email to supervisor: ' . $e->getMessage(), [
            'application_id' => $leaveApplication->id,
            'application_code' => $leaveApplication->code,
            'supervisor_email' => $supervisorEmail ?? 'not set'
        ]);
    }
}

    public function detail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['id' => 'required|integer']);
            
            $leaveApplication = LeaveApplication::with(['user', 'approver'])->find($validated['id']);

            if (!$leaveApplication) {
                return $this->errorResponse(__("Not found."), 404);
            }

            return response()->json(["data" => $leaveApplication]);

        } catch (\Exception $e) {
            return $this->logAndReturnError('Error in get leave application detail', $e);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateLeaveApplication($request, true);
            
            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return $this->errorResponse(__("Not found."), 404);
            }

            if (!$leaveApplication->canBeModified()) {
                return $this->errorResponse(__("Cannot edit processed application"), 400);
            }

            $leaveApplication->fill($validated);

            if ($request->hasFile('document')) {
                $leaveApplication->deleteDocument();
                $leaveApplication->document_path = $leaveApplication->saveDocument($request->file('document'));
            }

            if (!$leaveApplication->save()) {
                return $this->errorResponse(__("Failed to update"), 400);
            }

            return $this->successResponse(__("Updated successfully"));

        } catch (\Exception $e) {
            return $this->logAndReturnError('Error in update leave application', $e);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            $id = $request->input('id');
            
            if (!$id) {
                return $this->errorResponse(__("ID is required"), 400);
            }
            
            $leaveApplication = LeaveApplication::find($id);

            if (!$leaveApplication) {
                return $this->errorResponse(__("Not found."), 404);
            }

            if (!$leaveApplication->canBeModified()) {
                return $this->errorResponse(__("Cannot delete processed application"), 400);
            }

            $leaveApplication->deleteDocument();
            
            if (!$leaveApplication->delete()) {
                return $this->errorResponse(__("Failed to delete"), 400);
            }

            return $this->successResponse(__("Deleted successfully"));

        } catch (\Exception $e) {
            return $this->logAndReturnError('Error in delete leave application', $e);
        }
    }

    public function approve(Request $request): JsonResponse
    {
        return $this->processApplication($request, 'approved', __("Cuti berhasil disetujui"));
    }

    public function reject(Request $request): JsonResponse
    {
        return $this->processApplication($request, 'rejected', __("Cuti berhasil di tolak"));
    }

    // PERBAIKAN: Hapus duplikasi method validateLeaveApplication
    private function validateLeaveApplication(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'application_date' => 'required|date',
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|integer|min:1',
            'description' => 'required|string',
            'document' => 'nullable|file|mimes:pdf|max:5120',
        ];

        if ($isUpdate) {
            $rules['id'] = 'required|integer';
        } else {
            $rules['code'] = 'required|string|unique:leave_applications,code';
        }

        return $request->validate($rules);
    }

    private function buildActionButtons($data): string
    {
        $buttons = [];
        
        if ($data->canBeModified()) {
            $buttons[] = "<button class='edit btn btn-success btn-sm m-1' data-id='{$data->id}'><i class='fas fa-pen'></i> " . __("Edit") . "</button>";
            $buttons[] = "<button class='delete btn btn-danger btn-sm m-1' data-id='{$data->id}'><i class='fas fa-trash'></i> " . __("Delete") . "</button>";
        }
        
        $buttons[] = "<button class='detail btn btn-info btn-sm m-1' data-id='{$data->id}'><i class='fas fa-eye'></i> " . __("Detail") . "</button>";

        if ($data->document_path) {
            $documentUrl = asset('storage/' . $data->document_path);
            $buttons[] = "<a href='{$documentUrl}' target='_blank' class='btn btn-secondary btn-sm m-1'><i class='fas fa-file-pdf'></i> " . __("View Document") . "</a>";
        }

        return implode('', $buttons);
    }

    private function processApplication(Request $request, string $status, string $message): JsonResponse
    {
        try {
            $validated = $request->validate(['id' => 'required|integer']);
            
            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return $this->errorResponse(__("Not found."), 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return $this->errorResponse(__("Application already processed"), 400);
            }

            $leaveApplication->update([
                'status' => $status,
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return $this->successResponse($message);

        } catch (\Exception $e) {
            return $this->logAndReturnError("Error in {$status} leave application", $e);
        }
    }

    private function successResponse(string $message): JsonResponse
    {
        return response()->json(["message" => $message]);
    }

    private function errorResponse(string $message, int $code = 500): JsonResponse
    {
        return response()->json(["message" => $message], $code);
    }

    private function logAndReturnError(string $context, \Exception $e): JsonResponse
    {
        Log::error("{$context}: " . $e->getMessage());
        return $this->errorResponse('An error occurred: ' . $e->getMessage(), 500);
    }
}