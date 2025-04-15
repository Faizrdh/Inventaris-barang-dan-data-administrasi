<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use App\Models\LeaveApplication;
use App\Http\Requests\CreateLeaveApplicationRequest;
use App\Http\Requests\UpdateLeaveApplicationRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveApplicationController extends Controller
{
    /**
     * Menampilkan halaman leave application.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        return view('admin.master.cuti.leave-application');
    }

    /**
     * Menampilkan list pengajuan cuti dalam format DataTables.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            // Ambil data leave application terbaru
            $leaveApplications = LeaveApplication::latest()->get();

            // Pastikan response diberikan dalam kondisi AJAX
            if ($request->ajax()) {
                return DataTables::of($leaveApplications)
                    ->addColumn('tindakan', function ($data) {
                        // Membuat tombol aksi untuk DataTables
                        $button = "<button class='ubah btn btn-success m-1' id='" . $data->id . "'><i class='fas fa-pen m-1'></i> " . __("edit") . "</button>";
                        $button .= "<button class='hapus btn btn-danger m-1' id='" . $data->id . "'><i class='fas fa-trash m-1'></i> " . __("delete") . "</button>";
                        $button .= "<button class='detail btn btn-info m-1' id='" . $data->id . "'><i class='fas fa-eye m-1'></i> " . __("detail") . "</button>";
                        
                        if ($data->dokumen) {
                            $button .= "<a href='" . url('storage/' . $data->dokumen) . "' target='_blank' class='btn btn-secondary m-1'><i class='fas fa-file-pdf m-1'></i> " . __("view") . "</a>";
                        }
                        return $button;
                    })
                    ->addColumn('status_label', function ($data) {
                        // Menambahkan label status sesuai dengan status pengajuan
                        if ($data->status == 'pending') {
                            return "<span class='badge badge-warning'>" . __("Pending") . "</span>";
                        } elseif ($data->status == 'approved') {
                            return "<span class='badge badge-success'>" . __("Approved") . "</span>";
                        } else {
                            return "<span class='badge badge-danger'>" . __("Rejected") . "</span>";
                        }
                    })
                    ->rawColumns(['tindakan', 'status_label'])
                    ->make(true);
            }

            // Jika bukan AJAX, tetap harus ada response yang dikembalikan
            return response()->json([
                'message' => 'Invalid request. Expected an AJAX request.'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error in leave application list: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan pengajuan cuti baru.
     *
     * @param \App\Http\Requests\CreateLeaveApplicationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        try {
            // Validasi manual untuk menggantikan CreateLeaveApplicationRequest
            $validated = $request->validate([
                'kode' => 'required',
                'nama' => 'required',
                'nip' => 'required',
                'tanggal_pengajuan' => 'required|date',
                'jenis_cuti' => 'required',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'total_hari' => 'required|integer|min:1',
                'keterangan' => 'required',
                'dokumen' => 'nullable|file|mimes:pdf|max:5120',
            ]);

            $leaveApplication = new LeaveApplication();
            $leaveApplication->kode = $request->kode;
            $leaveApplication->nama = $request->nama;
            $leaveApplication->nip = $request->nip;
            $leaveApplication->tanggal_pengajuan = $request->tanggal_pengajuan;
            $leaveApplication->jenis_cuti = $request->jenis_cuti;
            $leaveApplication->tanggal_mulai = $request->tanggal_mulai;
            $leaveApplication->tanggal_selesai = $request->tanggal_selesai;
            $leaveApplication->total_hari = $request->total_hari;
            $leaveApplication->keterangan = $request->keterangan;
            $leaveApplication->status = 'pending';
            $leaveApplication->user_id = Auth::id(); // Pastikan user_id tersedia

            // Handle file upload
            if ($request->hasFile('dokumen')) {
                $path = $request->file('dokumen')->store('cuti_files', 'public');
                $leaveApplication->dokumen = $path;
            }

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json(["message" => __("failed to save")])->setStatusCode(400);
            }

            return response()->json([ "message" => __("saved successfully") ])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in save leave application: ' . $e->getMessage());
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
            Log::error('Error in get leave application detail: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengupdate pengajuan cuti.
     *
     * @param \App\Http\Requests\UpdateLeaveApplicationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            // Validasi manual untuk menggantikan UpdateLeaveApplicationRequest
            $validated = $request->validate([
                'id' => 'required|integer',
                'nama' => 'required',
                'nip' => 'required',
                'tanggal_pengajuan' => 'required|date',
                'jenis_cuti' => 'required',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'total_hari' => 'required|integer|min:1',
                'keterangan' => 'required',
                'dokumen' => 'nullable|file|mimes:pdf|max:5120',
            ]);

            $leaveApplication = LeaveApplication::find($validated['id']);

            if (!$leaveApplication) {
                return response()->json(["message" => __("not found.")], 404);
            }

            // Cek apakah pengajuan masih bisa diedit (masih pending)
            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("cannot edit processed application")])->setStatusCode(400);
            }

            $leaveApplication->nama = $request->nama;
            $leaveApplication->nip = $request->nip;
            $leaveApplication->tanggal_pengajuan = $request->tanggal_pengajuan;
            $leaveApplication->jenis_cuti = $request->jenis_cuti;
            $leaveApplication->tanggal_mulai = $request->tanggal_mulai;
            $leaveApplication->tanggal_selesai = $request->tanggal_selesai;
            $leaveApplication->total_hari = $request->total_hari;
            $leaveApplication->keterangan = $request->keterangan;

            // Handle file upload
            if ($request->hasFile('dokumen')) {
                // Delete old file if exists
                if ($leaveApplication->dokumen) {
                    Storage::disk('public')->delete($leaveApplication->dokumen);
                }

                $path = $request->file('dokumen')->store('cuti_files', 'public');
                $leaveApplication->dokumen = $path;
            }

            $status = $leaveApplication->save();

            if (!$status) {
                return response()->json(["message" => __("data failed to change")])->setStatusCode(400);
            }

            return response()->json([ "message" => __("data changed successfully") ])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in update leave application: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus pengajuan cuti.
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
                return response()->json(["message" => __("not found.")], 404);
            }

            // Cek apakah pengajuan masih bisa dihapus (masih pending)
            if ($leaveApplication->status !== 'pending') {
                return response()->json(["message" => __("cannot delete processed application")])->setStatusCode(400);
            }

            // Delete file if exists
            if ($leaveApplication->dokumen) {
                Storage::disk('public')->delete($leaveApplication->dokumen);
            }

            $status = $leaveApplication->delete();

            if (!$status) {
                return response()->json(["message" => __("data failed to delete")])->setStatusCode(400);
            }

            return response()->json([ "message" => __("data deleted successfully") ])->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error('Error in delete leave application: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}