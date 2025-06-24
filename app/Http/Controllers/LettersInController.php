<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\LettersIn;
use App\Models\Letter;
use App\Models\CategoryLetter;
use App\Models\SenderLetter;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LettersInController extends Controller
{
    public function index(): View
    {
        try {
            $categoryLetters = CategoryLetter::select(['id', 'name'])->get();
            // PERBAIKAN: Hapus referensi ke name, hanya ambil id dan from_department
            $senderLetters = SenderLetter::select(['id', 'from_department'])->get();
            
            return view('admin.master.transaksisurat.masuk', compact('categoryLetters', 'senderLetters'));
        } catch (\Exception $e) {
            Log::error('Error in index: ' . $e->getMessage());
            return view('admin.master.transaksisurat.masuk')->with([
                'categoryLetters' => collect(), 
                'senderLetters' => collect()
            ]);
        }
    }

    public function list(Request $request): JsonResponse
    {
        try {
            if ($request->ajax()) {
                $query = LettersIn::select([
                    'id',
                    'received_date',
                    'from_department',
                    'sender_name',
                    'letter_id',
                    'sender_letter_id',
                    'category_letter_id',
                    'notes',
                    'file_name',
                    'file_path',
                    'file_size',
                    'file_type'
                ])->with([
                    'letter:id,code,name',
                    // PERBAIKAN: Hapus referensi ke name
                    'senderLetter:id,from_department',
                    'categoryLetter:id,name'
                ]);

                return DataTables::of($query)
                    ->addColumn('received_date_formatted', function ($data) {
                        return $data->formatted_received_date;
                    })
                    ->addColumn('letter_code', function ($data) {
                        return $data->letter_code;
                    })
                    ->addColumn('letter_name', function ($data) {
                        return $data->letter_name;
                    })
                    ->addColumn('sender_name_display', function ($data) {
                        return $data->sender_name_display;
                    })
                    ->addColumn('department_name_display', function ($data) {
                        return $data->department_name_display;
                    })
                    ->addColumn('category_name', function ($data) {
                        return $data->category_name;
                    })
                    ->addColumn('detail_surat', function ($data) {
                        // Hanya employee yang bisa melihat action buttons
                        if (Auth::user()->role->name !== 'employee') {
                            return '<span class="text-muted">Akses terbatas untuk admin</span>';
                        }

                        $html = '<div class="file-info-container">';
                        
                        // Detail Surat Header
                        $html .= '<div class="mb-2"><strong>Detail Surat</strong></div>';
                        
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
                        } else {
                            $html .= '
                                <div class="file-details mb-3">
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-file-slash"></i> Tidak Ada File
                                    </span>
                                </div>';
                        }
                        
                        // Action Buttons - dengan warna hijau dan merah
                        $html .= '<div class="mb-2"><strong>Action</strong></div>';
                        $html .= '<div class="btn-group-actions">';
                        
                        // Edit Button - HIJAU (btn-success)
                        $html .= '
                            <button type="button" class="btn btn-sm btn-success ubah me-1 mb-1" id="' . $data->id . '" title="Edit">
                                <i class="fas fa-edit"></i> ubah
                            </button>';
                        
                        // File Action Buttons (jika ada file)
                        if ($data->hasFile() && $data->file_path) {
                            $fileUrl = asset('storage/' . $data->file_path);
                            
                            // Download Button  
                            $html .= '
                                <a href="' . $fileUrl . '" download="' . htmlspecialchars($data->file_name) . '" class="btn btn-sm btn-info me-1 mb-1" title="Download">
                                    <i class="fas fa-download"></i> download
                                </a>';
                            
                            // View Button
                            $html .= '
                                <a href="' . $fileUrl . '" target="_blank" class="btn btn-sm btn-primary me-1 mb-1" title="View File">
                                    <i class="fas fa-eye"></i> view
                                </a>';
                        }
                        
                        // Delete Button - MERAH (btn-danger)
                        $html .= '
                            <button type="button" class="btn btn-sm btn-danger hapus me-1 mb-1" id="' . $data->id . '" title="Hapus">
                                <i class="fas fa-trash"></i> hapus
                            </button>';
                        
                        $html .= '</div>'; // Close btn-group-actions
                        $html .= '</div>'; // Close file-info-container
                        
                        return $html;
                    })
                    ->rawColumns(['detail_surat'])
                    ->make(true);
            }

            return response()->json(['message' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            Log::error('Error in LettersIn list: ' . $e->getMessage());
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
     * Get list of letters for selection modal (optimized)
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
                    // PERBAIKAN: Hapus referensi ke name
                    'senderLetter:id,from_department'
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
                        // PERBAIKAN: Gunakan from_department sebagai sender name
                        return $data->senderLetter ? $data->senderLetter->from_department : ($data->from_department ?? '-');
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
                        // PERBAIKAN: Gunakan from_department sebagai sender name
                        $senderName = $data->senderLetter ? $data->senderLetter->from_department : ($data->from_department ?? '');
                        $department = $data->department_name;
                        
                        return '<button type="button" class="btn btn-sm btn-primary pilih-letter" 
                                        data-id="' . $data->id . '" 
                                        data-code="' . $data->code . '" 
                                        data-name="' . htmlspecialchars($data->name) . '"
                                        data-sender-id="' . ($data->sender_letter_id ?? '') . '"
                                        data-sender-name="' . htmlspecialchars($senderName) . '"
                                        data-department="' . htmlspecialchars($department) . '"
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
     * Get letter details by code (optimized)
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
                // PERBAIKAN: Hapus referensi ke name
                'senderLetter:id,from_department'
            ])->where('code', $code)->first();
            
            if ($letter) {
                // PERBAIKAN: Gunakan from_department sebagai sender name
                $senderName = $letter->senderLetter ? $letter->senderLetter->from_department : '';
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $letter->id,
                        'code' => $letter->code,
                        'name' => $letter->name,
                        'category_name' => $letter->categoryLetter ? $letter->categoryLetter->name : null,
                        'sender_id' => $letter->sender_letter_id,
                        'sender_name' => $senderName,
                        'from_department' => $letter->department_name,
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
                'message' => 'Access denied. Only employees can create letters in.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'letter_id' => 'required|exists:letters,id',
                'sender_letter_id' => 'nullable|exists:sender_letters,id',
                'category_letter_id' => 'required|exists:category_letters,id',
                'received_date' => 'required|date',
                'from_department' => 'nullable|string|max:255',
                'sender_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'file_name' => 'nullable|string|max:255',
                'file_path' => 'nullable|string|max:500',
                'file_size' => 'nullable|integer|min:0',
                'file_type' => 'nullable|string|max:100'
            ]);

            $letterIn = LettersIn::create(array_merge($validated, [
                'user_id' => Auth::id()
            ]));

            Log::info('Letter in created by employee', [
                'letter_in_id' => $letterIn->id,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Surat masuk berhasil disimpan',
                'data' => $letterIn
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
            $letterIn = LettersIn::select([
                'id', 'letter_id', 'sender_letter_id', 'category_letter_id',
                'received_date', 'from_department', 'sender_name', 'notes',
                'file_name', 'file_path', 'file_size', 'file_type'
            ])->with([
                'letter:id,code,name',
                // PERBAIKAN: Hapus referensi ke name
                'senderLetter:id,from_department',
                'categoryLetter:id,name'
            ])->find($request->id);

            if (!$letterIn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $letterIn->id,
                    'letter_id' => $letterIn->letter_id,
                    'sender_letter_id' => $letterIn->sender_letter_id,
                    'category_letter_id' => $letterIn->category_letter_id,
                    'received_date' => $letterIn->received_date->format('Y-m-d'),
                    'from_department' => $letterIn->from_department,
                    'sender_name' => $letterIn->sender_name,
                    'notes' => $letterIn->notes,
                    'letter_code' => $letterIn->letter_code,
                    'letter_name' => $letterIn->letter_name,
                    'file_name' => $letterIn->file_name,
                    'file_path' => $letterIn->file_path,
                    'file_size' => $letterIn->file_size,
                    'file_type' => $letterIn->file_type,
                    'has_file' => $letterIn->hasFile()
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
                'message' => 'Access denied. Only employees can update letters in.'
            ], 403);
        }

        try {
            $letterIn = LettersIn::find($request->id);
            
            if (!$letterIn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ], 404);
            }

            $validated = $request->validate([
                'letter_id' => 'required|exists:letters,id',
                'sender_letter_id' => 'nullable|exists:sender_letters,id',
                'category_letter_id' => 'required|exists:category_letters,id',
                'received_date' => 'required|date',
                'from_department' => 'nullable|string|max:255',
                'sender_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'file_name' => 'nullable|string|max:255',
                'file_path' => 'nullable|string|max:500',
                'file_size' => 'nullable|integer|min:0',
                'file_type' => 'nullable|string|max:100'
            ]);

            $letterIn->update($validated);

            Log::info('Letter in updated by employee', [
                'letter_in_id' => $letterIn->id,
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
            $letterIn = LettersIn::find($request->id);
            
            if (!$letterIn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ], 404);
            }

            $letterIn->delete();

            Log::info('Letter in deleted', [
                'letter_in_id' => $request->id,
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