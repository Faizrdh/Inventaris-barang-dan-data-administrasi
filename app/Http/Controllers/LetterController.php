<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Letter;
use App\Models\CategoryLetter;
use App\Models\SenderLetter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class LetterController extends Controller
{
    public function index(): View
    {
        try {
            $jenissurat = CategoryLetter::all();
            $senderletters = SenderLetter::all();
            
            // Debug: Log jumlah data
            Log::info('Data loaded - Jenis Surat: ' . $jenissurat->count() . ', Sender Letters: ' . $senderletters->count());
            
            // Debug: Log sample data
            if ($senderletters->count() > 0) {
                Log::info('Sample Sender Letter: ', [
                    'id' => $senderletters->first()->id,
                    'destination' => $senderletters->first()->destination,
                    'from_department' => $senderletters->first()->from_department
                ]);
            }
            
            return view('admin.master.surat.satuan-surat', compact('jenissurat', 'senderletters'));
        } catch (\Exception $e) {
            Log::error('Error loading index page: ' . $e->getMessage());
            
            // Fallback jika ada error
            $jenissurat = collect();
            $senderletters = collect();
            
            return view('admin.master.surat.satuan-surat', compact('jenissurat', 'senderletters'))
                ->with('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }
    }

    public function list(Request $request): JsonResponse
    {
        try {
            if (!$request->ajax()) {
                return $this->errorResponse(__("Invalid request type"));
            }

            $letters = Letter::with(['categoryLetter', 'senderLetter'])->latest();

            return DataTables::of($letters)
                ->addColumn('category_letter_name', function($data) {
                    return $data->categoryLetter?->name ?? '-';
                })
                ->addColumn('from_department', function($data) {
                    return $data->department_name ?? '-';
                })
                ->addColumn('file_info', function($data) {
                    return $this->generateFileInfo($data);
                })
                ->addColumn('tindakan', function($data) {
                    return $this->generateActionButtons($data);
                })
                ->rawColumns(['file_info', 'tindakan'])
                ->make(true);
                
        } catch (\Exception $e) {
            Log::error('DataTables Error: ' . $e->getMessage());
            return $this->errorResponse('Terjadi kesalahan saat memuat data: ' . $e->getMessage(), 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only employees can add data.'
                ], 403);
            }

            $validator = $this->validateLetterRequest($request);
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $data = $this->prepareLetterData($request);
            $data = $this->handleFileUpload($request, $data);
            $data['user_id'] = Auth::id();

            Letter::create($data);
            
            return $this->successResponse(__("Data berhasil disimpan"));
            
        } catch (\Exception $e) {
            Log::error('Save Letter Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menyimpan data: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only employees can edit data.'
                ], 403);
            }

            $letter = Letter::find($request->id);
            if (!$letter) {
                return $this->errorResponse(__("Letter not found"), 404);
            }

            $validator = $this->validateLetterRequest($request, $letter->id);
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $data = $this->prepareLetterData($request);
            $data = $this->handleFileUpload($request, $data, $letter);

            $letter->update($data);
            
            return $this->successResponse(__("Data berhasil diperbarui"));
            
        } catch (\Exception $e) {
            Log::error('Update Letter Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memperbarui data: ' . $e->getMessage(), 500);
        }
    }

    public function detail(Request $request): JsonResponse
    {
        try {
            $request->validate(['id' => 'required|integer|exists:letters,id']);
            
            $letter = Letter::with(['categoryLetter', 'senderLetter'])->find($request->id);
            
            if (!$letter) {
                return $this->errorResponse(__("Letter not found"), 404);
            }
            
            // Add computed attributes
            $letter->category_letter_name = $letter->categoryLetter?->name ?? '';
            $letter->sender_letter_name = $letter->sender_name;
            $letter->from_department_display = $letter->department_name;
            
            return response()->json(['data' => $letter]);
            
        } catch (\Exception $e) {
            Log::error('Get Letter Detail Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil detail data: ' . $e->getMessage(), 500);
        }
    }

    public function detailByCode(Request $request): JsonResponse
    {
        try {
            $request->validate(['code' => 'required|string']);
            
            $letter = Letter::with(['categoryLetter', 'senderLetter'])
                ->where("code", $request->code)
                ->first();
            
            if ($letter) {
                $letter->category_letter_name = $letter->categoryLetter?->name ?? '';
                $letter->sender_letter_name = $letter->sender_name;
                $letter->from_department_display = $letter->department_name;
            }
            
            return response()->json(['data' => $letter]);
            
        } catch (\Exception $e) {
            Log::error('Get Letter by Code Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil data: ' . $e->getMessage(), 500);
        }
    }

    public function downloadFile(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $request->validate(['id' => 'required|integer|exists:letters,id']);
            
            $letter = Letter::find($request->id);
            
            if (!$letter || !$letter->hasFile() || !Storage::disk('public')->exists($letter->file_path)) {
                return $this->errorResponse(__("File not found"), 404);
            }

            $filePath = Storage::disk('public')->path($letter->file_path);
            return response()->download($filePath, $letter->file_name);
            
        } catch (\Exception $e) {
            Log::error('Download File Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengunduh file: ' . $e->getMessage(), 500);
        }
    }

    public function viewFile(Request $request): Response|JsonResponse
    {
        try {
            $request->validate(['id' => 'required|integer|exists:letters,id']);
            
            $letter = Letter::find($request->id);
            
            if (!$letter || !$letter->hasFile() || !Storage::disk('public')->exists($letter->file_path)) {
                return $this->errorResponse(__("File not found"), 404);
            }

            $filePath = Storage::disk('public')->path($letter->file_path);
            return response()->file($filePath);
            
        } catch (\Exception $e) {
            Log::error('View File Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menampilkan file: ' . $e->getMessage(), 500);
        }
    }

    public function deleteFile(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only employees can delete files.'
                ], 403);
            }

            $request->validate(['id' => 'required|integer|exists:letters,id']);
            
            $letter = Letter::find($request->id);
            
            if (!$letter) {
                return $this->errorResponse(__("Letter not found"), 404);
            }

            $this->removeFileFromStorage($letter);

            $letter->update([
                'file_name' => null,
                'file_path' => null,
                'file_size' => null,
                'file_type' => null
            ]);
            
            return $this->successResponse(__("File berhasil dihapus"));
            
        } catch (\Exception $e) {
            Log::error('Delete File Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menghapus file: ' . $e->getMessage(), 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only employees can delete data.'
                ], 403);
            }

            $request->validate(['id' => 'required|integer|exists:letters,id']);
            
            $letter = Letter::find($request->id);
            
            if (!$letter) {
                return $this->errorResponse(__("Letter not found"), 404);
            }

            // Soft delete - file akan tetap ada untuk recovery
            $letter->delete();
            
            return $this->successResponse(__("Data berhasil dihapus"));
            
        } catch (\Exception $e) {
            Log::error('Delete Letter Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menghapus data: ' . $e->getMessage(), 500);
        }
    }

    // Helper Methods
    private function validateLetterRequest(Request $request, int $id = null): \Illuminate\Validation\Validator
    {
        $codeRule = $id ? "required|string|max:255|unique:letters,code,{$id}" : 'required|string|max:255|unique:letters,code';
        
        return Validator::make($request->all(), [
            'code' => $codeRule,
            'name' => 'required|string|max:255',
            'category_letter_id' => 'required|exists:category_letters,id',
            'sender_letter_id' => 'nullable|exists:sender_letters,id',
            'from_department' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
        ]);
    }

    private function prepareLetterData(Request $request): array
    {
        $data = [
            'code' => $request->code,
            'name' => $request->name,
            'category_letter_id' => $request->category_letter_id,
            'sender_letter_id' => $request->sender_letter_id ?: null,
            'from_department' => $request->from_department,
        ];

        // Auto-fill department from sender if selected
        if ($request->sender_letter_id) {
            $senderLetter = SenderLetter::find($request->sender_letter_id);
            if ($senderLetter && $senderLetter->from_department) {
                $data['from_department'] = $senderLetter->from_department;
            }
        }

        return $data;
    }

    private function handleFileUpload(Request $request, array $data, Letter $letter = null): array
    {
        if (!$request->hasFile('file')) {
            return $data;
        }

        try {
            // Remove old file if updating
            if ($letter) {
                $this->removeFileFromStorage($letter);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('letters', $fileName, 'public');
            
            return array_merge($data, [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientMimeType()
            ]);
            
        } catch (\Exception $e) {
            Log::error('File Upload Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function removeFileFromStorage(Letter $letter): void
    {
        try {
            if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) {
                Storage::disk('public')->delete($letter->file_path);
            }
        } catch (\Exception $e) {
            Log::error('Remove File Error: ' . $e->getMessage());
        }
    }

    private function generateFileInfo($data): string
    {
        try {
            if (!$data->hasFile()) {
                return '<div class="text-center">
                            <span class="badge badge-secondary">Tidak ada file</span>
                            <div class="text-muted small mt-1">Belum ada file yang diupload</div>
                        </div>';
            }

            return '
                <div class="file-info-container">
                    <div class="d-flex align-items-center mb-2">
                        <i class="' . $data->file_icon . ' me-2" style="font-size: 1.2em;"></i>
                        <span class="badge badge-success">Ada File</span>
                    </div>
                    <div class="file-details">
                        <div class="file-name mb-1">
                            <strong>' . htmlspecialchars($data->file_name ?? 'Unknown') . '</strong>
                        </div>
                        <div class="file-meta text-muted small">
                            <div>Ukuran: ' . $data->formatted_file_size . '</div>
                            <div>Tipe: ' . $data->file_extension . '</div>
                            <div>Upload: ' . $data->upload_date . '</div>
                        </div>
                    </div>
                </div>';
                
        } catch (\Exception $e) {
            Log::error('Generate File Info Error: ' . $e->getMessage());
            return '<div class="text-center text-danger">Error loading file info</div>';
        }
    }

    private function generateActionButtons($data): string
    {
        try {
            $userRole = Auth::user()->role->name ?? 'staff';
            
            // Hanya tampilkan action buttons untuk employee
            if ($userRole !== 'employee') {
                return '';
            }

            $buttons = "<button class='ubah btn btn-success btn-sm m-1' id='{$data->id}' title='Edit'>
                            <i class='fas fa-pen'></i> " . __("edit") . "
                        </button>";
            
            if ($data->hasFile()) {
                $buttons .= "<button class='download btn btn-info btn-sm m-1' id='{$data->id}' title='Download File'>
                                <i class='fas fa-download'></i> " . __("download") . "
                            </button>
                            <button class='view btn btn-primary btn-sm m-1' id='{$data->id}' title='Lihat File'>
                                <i class='fas fa-eye'></i> " . __("view") . "
                            </button>";
            }
            
            $buttons .= "<button class='hapus btn btn-danger btn-sm m-1' id='{$data->id}' title='Hapus'>
                            <i class='fas fa-trash'></i> " . __("delete") . "
                        </button>";
                        
            return $buttons;
            
        } catch (\Exception $e) {
            Log::error('Generate Action Buttons Error: ' . $e->getMessage());
            return '<span class="text-danger">Error loading actions</span>';
        }
    }

    private function successResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}