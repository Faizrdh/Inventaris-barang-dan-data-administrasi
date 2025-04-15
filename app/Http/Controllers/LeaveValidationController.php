<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveValidationController extends Controller
{
    // **
    //  * Constructor untuk middleware
    //  */
  
    /**
     * Menampilkan halaman validasi cuti.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
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
            // Ambil data leave application status pending
            $leaveApplications = LeaveApplication::where('status', 'pending')
                                                 ->latest()
                                                 ->get();

            // Pastikan response diberikan dalam kondisi AJAX
            if ($request->ajax()) {
                return DataTables::of($leaveApplications)
                    ->addColumn('tindakan', function ($data) {
                        // Membuat tombol aksi untuk DataTables
                        $button = "<button class='validasi btn btn-success m-1' id='" . $data->id . "'><i class='fas fa-check m-1'></i> " . __("validate") . "</button>";
                        $button .= "<button class='tolak btn btn-danger m-1' id='" . $data->id . "'><i class='fas fa-times m-1'></i> " . __("reject") . "</button>";
                        $button .= "<button class='detail btn btn-info m-1' id='" . $data->id . "'><i class='fas fa-eye m-1'></i> " . __("detail") . "</button>";
                        
                        if ($data->file_izin) {
                            $button .= "<a href='" . url('storage/' . $data->file_izin) . "' target='_blank' class='btn btn-secondary m-1'><i class='fas fa-file-pdf m-1'></i> " . __("view") . "</a>";
                        }
                        return $button;
                    })
                    ->rawColumns(['tindakan'])
                    ->make(true);
            }

            // Jika bukan AJAX, tetap harus ada response yang dikembalikan
            return response()->json([
                'message' => 'Invalid request. Expected an AJAX request.'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error in leave validation list: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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
            $validated = $request->validate([
                'id' => 'required|integer'
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("not found.")], 404);
            }

            return response()->json(["data" => $leaveApplication])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in get leave validation detail: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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
            $validated = $request->validate([
                'id' => 'required|integer',
                'catatan' => 'nullable|string|max:255'
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("not found.")], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("application already processed")], 400);
            }

            $leaveApplication->status = 'approved';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->catatan_validator = $request->catatan;

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json(["message" => __("failed to approve")], 400);
            }

            return response()->json(["message" => __("application approved successfully")])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in approve leave application: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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
            $validated = $request->validate([
                'id' => 'required|integer',
                'catatan' => 'required|string|max:255'
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("not found.")], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("application already processed")], 400);
            }

            $leaveApplication->status = 'rejected';
            $leaveApplication->approved_by = Auth::id();
            $leaveApplication->approved_at = now();
            $leaveApplication->catatan_validator = $request->catatan;

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json(["message" => __("failed to reject")], 400);
            }

            return response()->json(["message" => __("application rejected successfully")])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in reject leave application: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}