<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\LettersIn;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReportLettersInController extends Controller
{
    public function index(): View
    {
        // Hapus pengecekan role admin - semua user dapat mengakses
        return view('admin.master.laporansurat.surat-masuk');
    }

    public function list(Request $request): JsonResponse
    {
        try {

            if($request->ajax()) {
                try {
                    if(empty($request->start_date) && empty($request->end_date)) {
                        $lettersIn = LettersIn::with('letter', 'senderLetter', 'categoryLetter', 'user');
                    } else {
                        $lettersIn = LettersIn::with('letter', 'senderLetter', 'categoryLetter', 'user')
                                    ->whereBetween('received_date', [$request->start_date, $request->end_date]);
                    }
                    
                    $lettersIn = $lettersIn->latest('received_date')->get();
                    
                    return DataTables::of($lettersIn)
                        ->addColumn('received_date', function($data) {
                            try {
                                return $data->received_date ? 
                                    Carbon::parse($data->received_date)->format('d F Y') : 
                                    Carbon::parse($data->created_at)->format('d F Y');
                            } catch (\Exception $e) {
                                return 'Format tanggal tidak valid';
                            }
                        })
                        ->addColumn('letter_code', function($data) {
                            try {
                                return $data->letter ? $data->letter->code : 'Kode tidak tersedia';
                            } catch (\Exception $e) {
                                return 'Error mendapatkan kode';
                            }
                        })
                        ->addColumn('letter_name', function($data) {
                            try {
                                return $data->letter ? $data->letter->name : 'Nama surat tidak tersedia';
                            } catch (\Exception $e) {
                                return 'Error mendapatkan nama surat';
                            }
                        })
                        ->addColumn('category_name', function($data) {
                            try {
                                return $data->categoryLetter ? $data->categoryLetter->name : 'Kategori tidak tersedia';
                            } catch (\Exception $e) {
                                return 'Error mendapatkan kategori';
                            }
                        })
                        ->addColumn('sender_name', function($data) {
                            try {
                                return $data->sender_name_display;
                            } catch (\Exception $e) {
                                return 'Error mendapatkan pengirim';
                            }
                        })
                        ->addColumn('from_department', function($data) {
                            try {
                                return $data->department_name_display;
                            } catch (\Exception $e) {
                                return 'Error mendapatkan departemen';
                            }
                        })
                        ->addColumn('file_status', function($data) {
                            try {
                                return $data->hasFile() ? 'Ada File' : 'Tidak Ada File';
                            } catch (\Exception $e) {
                                return 'Error status file';
                            }
                        })
                        ->addColumn('received_by', function($data) {
                            try {
                                return $data->user ? $data->user->name : 'User tidak tersedia';
                            } catch (\Exception $e) {
                                return 'Error mendapatkan user';
                            }
                        })
                        ->make(true);
                        
                } catch (\Exception $e) {
                    Log::error('Error saat memproses data letters: ' . $e->getMessage());
                    return response()->json([
                        'error' => true,
                        'message' => 'Terjadi kesalahan saat memproses data',
                        'details' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }
            }

            return response()->json(['message' => 'Request harus melalui AJAX'], 400);
            
        } catch (\Exception $e) {
            Log::error('Error pada controller list: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan pada server',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}