<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveStatusUpdateMail;

class LeaveValidationController extends Controller
{
    public function index(): View
    {
        return view('admin.master.cuti.leave-validation');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['message' => 'Invalid request'], 400);
            }

            $leaveApplications = LeaveApplication::with(['user', 'approver'])->latest();

            return DataTables::of($leaveApplications)
                ->addColumn('tindakan', fn($data) => $this->buildValidationActionButtons($data))
                ->addColumn('status_badge', fn($data) => $this->getStatusBadge($data->status))
                ->addColumn('application_date_formatted', fn($data) => $data->application_date?->format('Y-m-d'))
                ->addColumn('start_date_formatted', fn($data) => $data->start_date?->format('Y-m-d'))
                ->addColumn('end_date_formatted', fn($data) => $data->end_date?->format('Y-m-d'))
                ->addColumn('approved_at_formatted', fn($data) => $data->approved_at?->format('Y-m-d H:i'))
                ->addColumn('approver_name', fn($data) => $data->approver?->name ?? '-')
                ->rawColumns(['tindakan', 'status_badge'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Error in leave validation list: ' . $e->getMessage());
            return response()->json(['message' => 'Error occurred'], 500);
        }
    }

    // Method untuk mendapatkan status badge
    private function getStatusBadge($status): string
    {
        $badges = [
            'pending' => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            'approved' => '<span class="badge badge-success"><i class="fas fa-check"></i> Approved</span>',
            'rejected' => '<span class="badge badge-danger"><i class="fas fa-times"></i> Rejected</span>',
            'processed' => '<span class="badge badge-info"><i class="fas fa-cog"></i> Processed</span>',
        ];
       
        return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    // Method untuk detail
    public function detail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['id' => 'required|integer']);
            
            $leaveApplication = LeaveApplication::with(['user', 'approver'])->find($validated['id']);

            if (!$leaveApplication) {
                return response()->json([
                    "success" => false,
                    "message" => __("Leave application not found.")
                ], 404);
            }

            return response()->json([
                "success" => true,
                "data" => $leaveApplication
            ]);

        } catch (\Exception $e) {
            Log::error('Error in get leave application detail: ' . $e->getMessage());
            return response()->json([
                "success" => false,
                "message" => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request): JsonResponse
    {
        try {
            // Cek role admin
            if(Auth::user()->role->name != 'admin' && Auth::user()->role_id !== 1){
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $validated = $request->validate([
                'id' => 'required|integer',
                'catatan_validator' => 'nullable|string|max:255'
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json([
                    "success" => false,
                    "message" => __("Leave application not found.")
                ], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json([
                    "success" => false,
                    "message" => __("Application already processed")
                ], 400);
            }

            $oldStatus = $leaveApplication->status;
            
            $leaveApplication->status = 'approved';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->catatan_validator = $request->catatan_validator;

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json([
                    "success" => false,
                    "message" => __("Failed to approve application")
                ], 400);
            }

            // Kirim email notifikasi ke pegawai dengan validasi yang lebih baik
            $this->sendStatusUpdateEmail($leaveApplication, $oldStatus);

            return response()->json([
                "success" => true,
                "message" => __("Leave application approved successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error in approve leave application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'Terjadi kesalahan saat menyetujui pengajuan',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function sendStatusUpdateEmail(LeaveApplication $leaveApplication, string $oldStatus): void
    {
        try {
            if (!$leaveApplication->email) {
                Log::warning('No email address found for leave application: ' . $leaveApplication->code);
                return;
            }

            if (!filter_var($leaveApplication->email, FILTER_VALIDATE_EMAIL)) {
                Log::error('Invalid email format for leave application: ' . $leaveApplication->email);
                return;
            }

            Mail::to($leaveApplication->email)->send(new LeaveStatusUpdateMail($leaveApplication, $oldStatus));
            Log::info('Status update email sent successfully', [
                'email' => $leaveApplication->email,
                'application_code' => $leaveApplication->code,
                'new_status' => $leaveApplication->status,
                'old_status' => $oldStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send status update email: ' . $e->getMessage(), [
                'email' => $leaveApplication->email,
                'application_id' => $leaveApplication->id,
                'application_code' => $leaveApplication->code
            ]);
        }
    }

    public function reject(Request $request): JsonResponse
    {
        try {
            // Cek role admin
            if(Auth::user()->role->name != 'admin' && Auth::user()->role_id !== 1){
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $validated = $request->validate([
                'id' => 'required|integer',
                'catatan_validator' => 'required|string|max:255'
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json([
                    "success" => false,
                    "message" => __("Leave application not found.")
                ], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json([
                    "success" => false,
                    "message" => __("Application already processed")
                ], 400);
            }

            $oldStatus = $leaveApplication->status;

            $leaveApplication->status = 'rejected';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->catatan_validator = $request->catatan_validator;

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json([
                    "success" => false,
                    "message" => __("Failed to reject application")
                ], 400);
            }

            // Kirim email notifikasi ke pegawai
            $this->sendStatusUpdateEmail($leaveApplication, $oldStatus);

            return response()->json([
                "success" => true,
                "message" => __("Pengajuan cuti berhasil di setujui")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error in reject leave application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'Terjadi kesalahan saat menolak pengajuan',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function process(Request $request): JsonResponse
    {
        try {
            // Cek role admin
            if(Auth::user()->role->name != 'admin' && Auth::user()->role_id !== 1){
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $validated = $request->validate([
                'id' => 'required|integer',
                'catatan_validator' => 'nullable|string|max:255'
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json([
                    "success" => false,
                    "message" => __("Leave application not found.")
                ], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json([
                    "success" => false,
                    "message" => __("Application already processed")
                ], 400);
            }

            $oldStatus = $leaveApplication->status;

            $leaveApplication->status = 'processed';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->catatan_validator = $request->catatan_validator;

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json([
                    "success" => false,
                    "message" => __("Failed to process application")
                ], 400);
            }

            // Kirim email notifikasi ke pegawai
            $this->sendStatusUpdateEmail($leaveApplication, $oldStatus);

            return response()->json([
                "success" => true,
                "message" => __("Leave application processed successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error in process leave application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'Terjadi kesalahan saat memproses pengajuan',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function buildValidationActionButtons($data): string
    {
        $buttons = [];
        
        // Hanya tampilkan action buttons jika status masih pending
        if ($data->status === 'pending') {
            $buttons[] = "<button class='approve btn btn-success btn-sm m-1' data-id='{$data->id}'><i class='fas fa-check'></i> " . __("Approve") . "</button>";
            $buttons[] = "<button class='reject btn btn-danger btn-sm m-1' data-id='{$data->id}'><i class='fas fa-times'></i> " . __("Reject") . "</button>";
            $buttons[] = "<button class='process btn btn-info btn-sm m-1' data-id='{$data->id}'><i class='fas fa-cog'></i> " . __("Process") . "</button>";
        }
        
        // Detail button selalu ada
        $buttons[] = "<button class='detail btn btn-secondary btn-sm m-1' data-id='{$data->id}'><i class='fas fa-eye'></i> " . __("Detail") . "</button>";

        // Document button jika ada document
        if ($data->document_path) {
            $documentUrl = asset('storage/' . $data->document_path);
            $buttons[] = "<a href='{$documentUrl}' target='_blank' class='btn btn-outline-primary btn-sm m-1'><i class='fas fa-file-pdf'></i> " . __("Document") . "</a>";
        }

        return implode('', $buttons);
    }
}