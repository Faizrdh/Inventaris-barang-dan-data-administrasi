<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\LettersOut;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReportLettersOutController extends Controller
{
    public function index(): View
    {
        return view('admin.master.laporansurat.surat-keluar');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            if($request->ajax()) {
                try {
                    if(empty($request->start_date) && empty($request->end_date)) {
                        $lettersOut = LettersOut::with('letter', 'user');
                    } else {
                        $lettersOut = LettersOut::with('letter', 'user')
                                    ->whereBetween('sent_date', [$request->start_date, $request->end_date]);
                    }
                    
                    $lettersOut = $lettersOut->latest('sent_date')->get();
                    
                    return DataTables::of($lettersOut)
                        ->addColumn('sent_date', function($data) {
                            try {
                                return $data->sent_date ? 
                                    Carbon::parse($data->sent_date)->format('d F Y') : 
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
                        ->addColumn('perihal', function($data) {
                            try {
                                return $data->perihal ?? 'Perihal tidak tersedia';
                            } catch (\Exception $e) {
                                return 'Error mendapatkan perihal';
                            }
                        })
                        ->addColumn('tujuan', function($data) {
                            try {
                                return $data->tujuan ?? 'Tujuan tidak tersedia';
                            } catch (\Exception $e) {
                                return 'Error mendapatkan tujuan';
                            }
                        })
                        ->addColumn('keterangan', function($data) {
                            try {
                                return $data->keterangan ?? 'Keterangan tidak tersedia';
                            } catch (\Exception $e) {
                                return 'Error mendapatkan keterangan';
                            }
                        })
                        ->addColumn('file_status', function($data) {
                            try {
                                return $data->hasFile() ? 'Ada File' : 'Tidak Ada File';
                            } catch (\Exception $e) {
                                return 'Error status file';
                            }
                        })
                        ->addColumn('sent_by', function($data) {
                            try {
                                return $data->user ? $data->user->name : 'User tidak tersedia';
                            } catch (\Exception $e) {
                                return 'Error mendapatkan user';
                            }
                        })
                        ->make(true);
                        
                } catch (\Exception $e) {
                    Log::error('Error saat memproses data letters out: ' . $e->getMessage());
                    return response()->json([
                        'error' => true,
                        'message' => 'Terjadi kesalahan saat memproses data',
                        'details' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }
            }

            return response()->json(['message' => 'Request harus melalui AJAX'], 400);
            
        } catch (\Exception $e) {
            Log::error('Error pada controller list letters out: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan pada server',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}