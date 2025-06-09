<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Letter;
use App\Models\CategoryLetter;
use App\Models\SenderLetter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class LetterController extends Controller
{
    public function index(): View
    {
        $jenissurat = CategoryLetter::all();
        $senderletters = SenderLetter::all();
        return view('admin.master.surat.satuan-surat', compact('jenissurat', 'senderletters'));
    }

    public function list(Request $request): JsonResponse
    {
        if (!$request->ajax()) {
            return $this->errorResponse(__("Invalid request type"));
        }

        $letters = Letter::with(['categoryLetter', 'senderLetter'])->latest()->get();

        return DataTables::of($letters)
            ->addColumn('category_letter_name', fn($data) => $data->categoryLetter?->name ?? '-')
            ->addColumn('sender_name', fn($data) => $data->sender_name)
            ->addColumn('from_department', fn($data) => $data->department_name)
            ->addColumn('file_info', fn($data) => $this->generateFileInfo($data))
            ->addColumn('tindakan', fn($data) => $this->generateActionButtons($data))
            ->rawColumns(['file_info', 'tindakan'])
            ->make(true);
    }

    public function save(Request $request): JsonResponse
    {
        $validator = $this->validateLetterRequest($request);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $data = $this->prepareLetterData($request);
        $data = $this->handleFileUpload($request, $data);

        Letter::create($data);
        
        return $this->successResponse(__("saved successfully"));
    }

    public function update(Request $request): JsonResponse
    {
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
        
        return $this->successResponse(__("data changed successfully"));
    }

    public function detail(Request $request): JsonResponse
    {
        $letter = Letter::with(['categoryLetter', 'senderLetter'])->find($request->id);
        
        if (!$letter) {
            return $this->errorResponse(__("Letter not found"), 404);
        }
        
        $letter->category_letter_name = $letter->categoryLetter?->name ?? '';
        $letter->sender_letter_name = $letter->sender_name;
        $letter->from_department_display = $letter->department_name;
        
        return response()->json(['data' => $letter]);
    }

    public function detailByCode(Request $request): JsonResponse
    {
        $letter = Letter::with(['categoryLetter', 'senderLetter'])
            ->where("code", $request->code)
            ->first();
        
        if ($letter) {
            $letter->category_letter_name = $letter->categoryLetter?->name ?? '';
            $letter->sender_letter_name = $letter->sender_name;
            $letter->from_department_display = $letter->department_name;
        }
        
        return response()->json(['data' => $letter]);
    }

    public function downloadFile(Request $request): BinaryFileResponse|JsonResponse
    {
        $letter = Letter::find($request->id);
        
        if (!$letter || !$letter->hasFile() || !Storage::disk('public')->exists($letter->file_path)) {
            return $this->errorResponse(__("File not found"), 404);
        }

        $filePath = Storage::disk('public')->path($letter->file_path);
        return response()->download($filePath, $letter->file_name);
    }

    public function viewFile(Request $request): Response|JsonResponse
    {
        $letter = Letter::find($request->id);
        
        if (!$letter || !$letter->hasFile() || !Storage::disk('public')->exists($letter->file_path)) {
            return $this->errorResponse(__("File not found"), 404);
        }

        $filePath = Storage::disk('public')->path($letter->file_path);
        return response()->file($filePath);
    }

    public function deleteFile(Request $request): JsonResponse
    {
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
        
        return $this->successResponse(__("File deleted successfully"));
    }

    public function delete(Request $request): JsonResponse
    {
        $letter = Letter::find($request->id);
        
        if (!$letter) {
            return $this->errorResponse(__("Letter not found"), 404);
        }

        // Soft delete - file akan tetap ada untuk recovery
        $letter->delete();
        
        return $this->successResponse(__("data deleted successfully"));
    }

    // Helper Methods
    private function validateLetterRequest(Request $request, $id = null): \Illuminate\Validation\Validator
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
            'sender_letter_id' => $request->sender_letter_id,
            'from_department' => $request->from_department,
        ];

        // Auto-fill department from sender if selected
        if ($request->sender_letter_id) {
            $senderLetter = SenderLetter::find($request->sender_letter_id);
            if ($senderLetter) {
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
    }

    private function removeFileFromStorage(Letter $letter): void
    {
        if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) {
            Storage::disk('public')->delete($letter->file_path);
        }
    }

    private function generateFileInfo($data): string
    {
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
                        <strong>' . htmlspecialchars($data->file_name) . '</strong>
                    </div>
                    <div class="file-meta text-muted small">
                        <div>Ukuran: ' . $data->formatted_file_size . '</div>
                        <div>Tipe: ' . $data->file_extension . '</div>
                        <div>Upload: ' . $data->upload_date . '</div>
                    </div>
                </div>
            </div>';
    }

    private function generateActionButtons($data): string
    {
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
    }

    private function successResponse(string $message): JsonResponse
    {
        return response()->json(['message' => $message]);
    }

    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}