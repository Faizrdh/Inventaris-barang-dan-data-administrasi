<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\LettersOut;
use App\Models\Letter;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LettersOutController extends Controller
{
    public function index(): View
    {
        try {
            return view('admin.master.transaksisurat.keluar');
        } catch (\Exception $e) {
            Log::error('Error in index: ' . $e->getMessage());
            return view('admin.master.transaksisurat.surat-keluar');
        }
    }

    public function list(Request $request): JsonResponse
    {
        try {
            if ($request->ajax()) {
                // UPDATE: Tambahkan kolom tujuan
                $query = LettersOut::select([
                    'id',
                    'sent_date',
                    'perihal',
                    'tujuan',         // ← KOLOM BARU
                    'keterangan',
                    'letter_id',
                    'notes',
                    'file_name',
                    'file_path',
                    'file_size',
                    'file_type'
                ])->with([
                    'letter:id,code,name'
                ]);

                return DataTables::of($query)
                    ->addColumn('sent_date_formatted', function ($data) {
                        return $data->formatted_sent_date;
                    })
                    ->addColumn('letter_code', function ($data) {
                        return $data->letter_code;
                    })
                    ->addColumn('letter_name', function ($data) {
                        return $data->letter_name;
                    })
                    ->addColumn('detail_file', function ($data) {
                        // Hanya employee yang bisa melihat action buttons
                        if (Auth::user()->role->name !== 'employee') {
                            return '<span class="text-muted">Akses terbatas untuk admin</span>';
                        }

                        $html = '<div class="file-info-container">';
                        
                        if ($data->hasFile()) {
                            // File Details
                            $fileSize = $data->file_size ? $data->formatted_file_size : 'Unknown size';
                            $fileType = $data->file_type ? strtoupper(pathinfo($data->file_name, PATHINFO_EXTENSION)) : 'Unknown';
                            $uploadDate = $data->updated_at ? $data->updated_at->format('d/m/Y H:i') : '-';
                            
                            $html .= '
                                <div class="file-details mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="' . $data->file_icon . ' me-2" style="font-size: 1.2em;"></i>
                                        <div class="flex-grow-1">
                                            <div class="file-name fw-bold">' . htmlspecialchars($data->file_name) . '</div>
                                        </div>
                                    </div>
                                    <div class="file-meta">
                                        <div><small class="text-muted">Ukuran: ' . $fileSize . '</small></div>
                                        <div><small class="text-muted">Tipe: ' . $fileType . '</small></div>
                                        <div><small class="text-muted">Upload: ' . $uploadDate . '</small></div>
                                    </div>
                                </div>';
                            
                            // Action Buttons untuk file
                            $fileUrl = asset('storage/' . $data->file_path);
                            $html .= '<div class="btn-group-actions mb-2">';
                            
                            // Download Button  
                            $html .= '
                                <a href="' . $fileUrl . '" download="' . htmlspecialchars($data->file_name) . '" class="btn btn-sm btn-info me-1 mb-1" title="Download">
                                    <i class="fas fa-download"></i> Download
                                </a>';
                            
                            // View Button
                            $html .= '
                                <a href="' . $fileUrl . '" target="_blank" class="btn btn-sm btn-primary me-1 mb-1" title="View File">
                                    <i class="fas fa-eye"></i> View
                                </a>';
                            
                            $html .= '</div>';
                        } else {
                            $html .= '
                                <div class="file-details mb-3">
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-file-slash"></i> Tidak Ada File
                                    </span>
                                </div>';
                        }
                        
                        // Action Buttons untuk data - dengan warna hijau dan merah
                        $html .= '<div class="btn-group-actions">';
                        
                        // Edit Button - HIJAU (btn-success)
                        $html .= '
                            <button type="button" class="btn btn-sm btn-success ubah me-1 mb-1" id="' . $data->id . '" title="Edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>';
                        
                        // Delete Button - MERAH (btn-danger)
                        $html .= '
                            <button type="button" class="btn btn-sm btn-danger hapus me-1 mb-1" id="' . $data->id . '" title="Hapus">
                                <i class="fas fa-trash"></i> Hapus
                            </button>';
                        
                        $html .= '</div>'; // Close btn-group-actions
                        $html .= '</div>'; // Close file-info-container
                        
                        return $html;
                    })
                    ->rawColumns(['detail_file'])
                    ->make(true);
            }

            return response()->json(['message' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            Log::error('Error in LettersOut list: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of letters for selection modal
     */
    public function listLetters(Request $request): JsonResponse
    {
        try {
            if ($request->ajax()) {
                $query = Letter::select([
                    'id',
                    'code', 
                    'name',
                    'category_letter_id',
                    'sender_letter_id',
                    'from_department',
                    'file_name',
                    'file_path',
                    'file_size',
                    'file_type'
                ])->with([
                    'categoryLetter:id,name',
                    'senderLetter:id,name,from_department'
                ]);

                // Filter search
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $query->where(function($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                    });
                }

                return DataTables::of($query)
                    ->addColumn('category_name', function ($data) {
                        return $data->categoryLetter ? $data->categoryLetter->name : '-';
                    })
                    ->addColumn('sender_name', function ($data) {
                        return $data->sender_name;
                    })
                    ->addColumn('from_department_display', function ($data) {
                        return $data->department_name;
                    })
                    ->addColumn('file_status', function ($data) {
                        if ($data->hasFile()) {
                            return '<span class="badge badge-success"><i class="fas fa-check"></i> Ada File</span>';
                        }
                        return '<span class="badge badge-secondary"><i class="fas fa-times"></i> Tidak Ada File</span>';
                    })
                    ->addColumn('file_info', function ($data) {
                        if ($data->hasFile()) {
                            return '
                                <div class="file-info-small">
                                    <div><i class="' . $data->file_icon . '"></i> ' . Str::limit($data->file_name, 20) . '</div>
                                    <small class="text-muted">' . $data->formatted_file_size . '</small>
                                </div>
                            ';
                        }
                        return '<span class="text-muted">-</span>';
                    })
                    ->addColumn('tindakan', function ($data) {
                        return '<button type="button" class="btn btn-sm btn-primary pilih-letter" 
                                        data-id="' . $data->id . '" 
                                        data-code="' . $data->code . '" 
                                        data-name="' . htmlspecialchars($data->name) . '"
                                        data-file-name="' . htmlspecialchars($data->file_name ?? '') . '"
                                        data-file-path="' . htmlspecialchars($data->file_path ?? '') . '"
                                        data-file-size="' . ($data->file_size ?? '') . '"
                                        data-file-type="' . htmlspecialchars($data->file_type ?? '') . '">
                                    <i class="fas fa-check"></i> Pilih
                                </button>';
                    })
                    ->rawColumns(['file_status', 'file_info', 'tindakan'])
                    ->make(true);
            }

            return response()->json(['message' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            Log::error('Error in listLetters: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get letter details by code
     */
    public function getLetterByCode(Request $request): JsonResponse
    {
        try {
            $code = $request->input('code');
            
            if (empty($code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code is required'
                ], 400);
            }

            $letter = Letter::select([
                'id', 'code', 'name', 'category_letter_id', 'sender_letter_id', 
                'from_department', 'file_name', 'file_path', 'file_size', 'file_type'
            ])->with([
                'categoryLetter:id,name',
                'senderLetter:id,name,from_department'
            ])->where('code', $code)->first();
            
            if ($letter) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $letter->id,
                        'code' => $letter->code,
                        'name' => $letter->name,
                        'file_name' => $letter->file_name,
                        'file_path' => $letter->file_path,
                        'file_size' => $letter->file_size,
                        'file_type' => $letter->file_type,
                        'has_file' => $letter->hasFile()
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Letter not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in getLetterByCode: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        // HANYA EMPLOYEE YANG BISA MENAMBAH DATA
        if (Auth::user()->role->name !== 'employee') {
            return response()->json([
                'success' => false, 
                'message' => 'Access denied. Only employees can create letters out.'
            ], 403);
        }

        try {
            // UPDATE: Tambahkan validasi untuk kolom tujuan
            $validated = $request->validate([
                'letter_id' => 'required|exists:letters,id',
                'sent_date' => 'required|date',
                'perihal' => 'required|string|max:255',
                'tujuan' => 'required|string|max:255',     // ← KOLOM BARU
                'keterangan' => 'nullable|string',
                'notes' => 'nullable|string',
                'file_name' => 'nullable|string|max:255',
                'file_path' => 'nullable|string|max:500',
                'file_size' => 'nullable|integer|min:0',
                'file_type' => 'nullable|string|max:100'
            ]);

            $letterOut = LettersOut::create(array_merge($validated, [
                'user_id' => Auth::id()
            ]));

            Log::info('Letter out created by employee', [
                'letter_out_id' => $letterOut->id,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Surat keluar berhasil disimpan',
                'data' => $letterOut
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request): JsonResponse
    {
        try {
            // UPDATE: Tambahkan kolom tujuan dalam select
            $letterOut = LettersOut::select([
                'id', 'letter_id', 'sent_date', 'perihal', 'tujuan', 'keterangan', 'notes',
                'file_name', 'file_path', 'file_size', 'file_type'
            ])->with([
                'letter:id,code,name'
            ])->find($request->id);

            if (!$letterOut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $letterOut->id,
                    'letter_id' => $letterOut->letter_id,
                    'sent_date' => $letterOut->sent_date->format('Y-m-d'),
                    'perihal' => $letterOut->perihal,
                    'tujuan' => $letterOut->tujuan,             // ← KOLOM BARU
                    'keterangan' => $letterOut->keterangan,
                    'notes' => $letterOut->notes,
                    'letter_code' => $letterOut->letter_code,
                    'letter_name' => $letterOut->letter_name,
                    'file_name' => $letterOut->file_name,
                    'file_path' => $letterOut->file_path,
                    'file_size' => $letterOut->file_size,
                    'file_type' => $letterOut->file_type,
                    'has_file' => $letterOut->hasFile()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        // HANYA EMPLOYEE YANG BISA UPDATE DATA
        if (Auth::user()->role->name !== 'employee') {
            return response()->json([
                'success' => false, 
                'message' => 'Access denied. Only employees can update letters out.'
            ], 403);
        }

        try {
            $letterOut = LettersOut::find($request->id);
            
            if (!$letterOut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ], 404);
            }

            // UPDATE: Tambahkan validasi untuk kolom tujuan
            $validated = $request->validate([
                'letter_id' => 'required|exists:letters,id',
                'sent_date' => 'required|date',
                'perihal' => 'required|string|max:255',
                'tujuan' => 'required|string|max:255',     // ← KOLOM BARU
                'keterangan' => 'nullable|string',
                'notes' => 'nullable|string',
                'file_name' => 'nullable|string|max:255',
                'file_path' => 'nullable|string|max:500',
                'file_size' => 'nullable|integer|min:0',
                'file_type' => 'nullable|string|max:100'
            ]);

            $letterOut->update($validated);

            Log::info('Letter out updated by employee', [
                'letter_out_id' => $letterOut->id,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $letterOut = LettersOut::find($request->id);
            
            if (!$letterOut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ], 404);
            }

            $letterOut->delete();

            Log::info('Letter out deleted', [
                'letter_out_id' => $request->id,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}