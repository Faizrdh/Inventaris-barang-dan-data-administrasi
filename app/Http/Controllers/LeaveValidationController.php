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

class LeaveValidationController extends Controller
{
    /**
     * Menampilkan halaman validasi cuti.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Cek role admin seperti pada ReportGoodsInController
        if(Auth::user()->role->name != 'admin' && Auth::user()->role_id !== 1){
            abort(403, 'Akses tidak diizinkan untuk halaman ini.');
        }
        
        return view('admin.master.cuti.leave-validation');
    }

    /**
     * Menampilkan list pengajuan cuti yang perlu divalidasi dalam format DataTables.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            // Cek role admin seperti pada ReportGoodsInController
            if(Auth::user()->role->name != 'admin' && Auth::user()->role_id !== 1){
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Ambil data leave application dengan status pending, approved, rejected, processed
            $leaveApplications = LeaveApplication::with(['user', 'approver'])
                                                 ->whereIn('status', ['pending', 'approved', 'rejected', 'processed'])
                                                 ->latest()
                                                 ->get();

            // Pastikan response diberikan dalam kondisi AJAX
            if ($request->ajax()) {
                return DataTables::of($leaveApplications)
                    ->addColumn('user_name', function ($data) {
                        try {
                            return $data->user ? $data->user->name : 'User tidak ditemukan';
                        } catch (\Exception $e) {
                            return 'Error mendapatkan user';
                        }
                    })
                    ->addColumn('application_date_formatted', function ($data) {
                        try {
                            return Carbon::parse($data->application_date)->format('d F Y');
                        } catch (\Exception $e) {
                            return 'Format tanggal tidak valid';
                        }
                    })
                    ->addColumn('start_date_formatted', function ($data) {
                        try {
                            return Carbon::parse($data->start_date)->format('d F Y');
                        } catch (\Exception $e) {
                            return 'Format tanggal tidak valid';
                        }
                    })
                    ->addColumn('end_date_formatted', function ($data) {
                        try {
                            return Carbon::parse($data->end_date)->format('d F Y');
                        } catch (\Exception $e) {
                            return 'Format tanggal tidak valid';
                        }
                    })
                    ->addColumn('status_badge', function ($data) {
                        if ($data->status === 'pending') {
                            // Dropdown untuk status pending dengan warna
                            return '<select class="form-control form-control-sm status-dropdown colored-dropdown" data-id="' . $data->id . '" style="width: 150px; background-color: #fff3cd; border-color: #ffeaa7; color: #856404;">
                                        <option value="pending" selected style="background-color: #fff3cd; color: #856404;">🟡 Pending</option>
                                        <option value="approved" style="background-color: #d4edda; color: #155724;">✅ Approve</option>
                                        <option value="rejected" style="background-color: #f8d7da; color: #721c24;">❌ Reject</option>
                                        <option value="processed" style="background-color: #d1ecf1; color: #0c5460;">⏳ Process</option>
                                    </select>';
                        } else {
                            // Badge untuk status yang sudah diproses
                            $statusLabels = [
                                'approved' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Approved</span>',
                                'rejected' => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejected</span>',
                                'processed' => '<span class="badge badge-info"><i class="fas fa-clock"></i> Processed</span>',
                            ];
                            return $statusLabels[$data->status] ?? '<span class="badge badge-secondary">Unknown</span>';
                        }
                    })
                    ->addColumn('approver_name', function ($data) {
                        try {
                            if ($data->approved_by && $data->approver) {
                                return $data->approver->name;
                            }
                            return '-';
                        } catch (\Exception $e) {
                            return 'Error mendapatkan approver';
                        }
                    })
                    ->addColumn('approved_at_formatted', function ($data) {
                        try {
                            if ($data->approved_at) {
                                return Carbon::parse($data->approved_at)->format('d F Y H:i');
                            }
                            return '-';
                        } catch (\Exception $e) {
                            return 'Format tanggal tidak valid';
                        }
                    })
                    ->addColumn('tindakan', function ($data) {
                        $button = '';
                        
                        // Tombol detail selalu ada
                        $button .= "<button class='detail btn btn-info btn-sm m-1' data-id='" . $data->id . "'><i class='fas fa-eye'></i> " . __("Detail") . "</button>";
                        
                        // Tambahkan link file jika ada
                        if ($data->document_path) {
                            $button .= "<a href='" . asset('storage/' . $data->document_path) . "' target='_blank' class='btn btn-secondary btn-sm m-1'><i class='fas fa-file-pdf'></i> " . __("View File") . "</a>";
                        }
                        
                        return $button;
                    })
                    ->rawColumns(['status_badge', 'tindakan'])
                    ->make(true);
            }

            // Jika bukan AJAX, tetap harus ada response yang dikembalikan
            return response()->json([
                'success' => false,
                'message' => 'Invalid request. Expected an AJAX request.'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Error in leave validation list: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat memproses data',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mengambil detail pengajuan cuti.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request): JsonResponse
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
                'id' => 'required|integer'
            ]);

            $leaveApplication = LeaveApplication::with(['user', 'approver'])->find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("Leave application not found.")], 404);
            }

            return response()->json([
                "success" => true,
                "data" => $leaveApplication
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error in get leave validation detail: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat mengambil detail data',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Memvalidasi (menyetujui) pengajuan cuti.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                return response()->json(["message" => __("Leave application not found.")], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("Application already processed")], 400);
            }

            $leaveApplication->status = 'approved';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->catatan_validator = $request->catatan_validator;

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json(["message" => __("Failed to approve application")], 400);
            }

            return response()->json([
                "success" => true,
                "message" => __("Leave application approved successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error in approve leave application: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat menyetujui pengajuan',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Menolak pengajuan cuti.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                return response()->json(["message" => __("Leave application not found.")], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("Application already processed")], 400);
            }

            $leaveApplication->status = 'rejected';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->catatan_validator = $request->catatan_validator;

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json(["message" => __("Failed to reject application")], 400);
            }

            return response()->json([
                "success" => true,
                "message" => __("Leave application rejected successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error in reject leave application: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat menolak pengajuan',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Memproses pengajuan cuti.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                return response()->json(["message" => __("Leave application not found.")], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("Application already processed")], 400);
            }

            $leaveApplication->status = 'processed';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->catatan_validator = $request->catatan_validator;

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json(["message" => __("Failed to process application")], 400);
            }

            return response()->json([
                "success" => true,
                "message" => __("Leave application processed successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error in process leave application: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat memproses pengajuan',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}