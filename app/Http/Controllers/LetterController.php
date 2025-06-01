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
        $letters = Letter::with(['categoryLetter', 'senderLetter'])->latest()->get();

        if ($request->ajax()) {
            return DataTables::of($letters)
                ->addColumn('category_letter_name', function ($data) {
                    return $data->categoryLetter ? $data->categoryLetter->name : '-';
                })
                ->addColumn('sender_name', function ($data) {
                    return $data->sender_name;
                })
                ->addColumn('from_department', function ($data) {
                    return $data->department_name;
                })
                ->addColumn('file_info', function ($data) {
                    if ($data->hasFile()) {
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
                    return '<div class="text-center">
                                <span class="badge badge-secondary">Tidak ada file</span>
                                <div class="text-muted small mt-1">Belum ada file yang diupload</div>
                            </div>';
                })
                ->addColumn('tindakan', function ($data) {
                    $button = "<button class='ubah btn btn-success btn-sm m-1' id='" . $data->id . "' title='Edit'><i class='fas fa-pen'></i> " . __("edit") . "</button>";
                    
                    if ($data->hasFile()) {
                        $button .= "<button class='download btn btn-info btn-sm m-1' id='" . $data->id . "' title='Download File'><i class='fas fa-download'></i> " . __("download") . "</button>";
                        $button .= "<button class='view btn btn-primary btn-sm m-1' id='" . $data->id . "' title='Lihat File'><i class='fas fa-eye'></i> " . __("view") . "</button>";
                    }
                    
                    $button .= "<button class='hapus btn btn-danger btn-sm m-1' id='" . $data->id . "' title='Hapus'><i class='fas fa-trash'></i> " . __("delete") . "</button>";
                    return $button;
                })
                ->rawColumns(['file_info', 'tindakan'])
                ->make(true);
        }

        return response()->json([
            "message" => __("Invalid request type"),
            "data" => $letters
        ])->setStatusCode(200);
    }

    public function save(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:letters,code',
            'name' => 'required|string|max:255',
            'category_letter_id' => 'required|exists:category_letters,id',
            'sender_letter_id' => 'nullable|exists:sender_letters,id',
            'from_department' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors()->first()
            ])->setStatusCode(422);
        }

        $data = [
            'code' => $request->code,
            'name' => $request->name,
            'category_letter_id' => $request->category_letter_id,
            'sender_letter_id' => $request->sender_letter_id,
            'from_department' => $request->from_department,
        ];

        // Jika sender_letter_id dipilih, ambil from_department dari sender_letters
        if ($request->sender_letter_id) {
            $senderLetter = SenderLetter::find($request->sender_letter_id);
            if ($senderLetter) {
                $data['from_department'] = $senderLetter->from_department;
            }
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('letters', $fileName, 'public');
            
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_path'] = $filePath;
            $data['file_size'] = $file->getSize();
            $data['file_type'] = $file->getClientMimeType();
        }

        Letter::create($data);
        
        return response()->json([
            "message" => __("saved successfully")
        ])->setStatusCode(200);
    }

    public function update(Request $request): JsonResponse
    {
        $id = $request->id;
        $letter = Letter::find($id);
        
        if (!$letter) {
            return response()->json([
                "message" => __("Letter not found")
            ])->setStatusCode(404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:letters,code,' . $id,
            'name' => 'required|string|max:255',
            'category_letter_id' => 'required|exists:category_letters,id',
            'sender_letter_id' => 'nullable|exists:sender_letters,id',
            'from_department' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors()->first()
            ])->setStatusCode(422);
        }

        $data = [
            'code' => $request->code,
            'name' => $request->name,
            'category_letter_id' => $request->category_letter_id,
            'sender_letter_id' => $request->sender_letter_id,
            'from_department' => $request->from_department,
        ];

        // Jika sender_letter_id dipilih, ambil from_department dari sender_letters
        if ($request->sender_letter_id) {
            $senderLetter = SenderLetter::find($request->sender_letter_id);
            if ($senderLetter) {
                $data['from_department'] = $senderLetter->from_department;
            }
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) {
                Storage::disk('public')->delete($letter->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('letters', $fileName, 'public');
            
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_path'] = $filePath;
            $data['file_size'] = $file->getSize();
            $data['file_type'] = $file->getClientMimeType();
        }

        $letter->fill($data);
        $letter->save();
        
        return response()->json([
            "message" => __("data changed successfully")
        ])->setStatusCode(200);
    }

    public function detail(Request $request): JsonResponse
    {
        $id = $request->id;
        $data = Letter::with(['categoryLetter', 'senderLetter'])->find($id);
        
        if (!$data) {
            return response()->json([
                "message" => __("Letter not found")
            ])->setStatusCode(404);
        }
        
        $data['category_letter_name'] = $data->categoryLetter ? $data->categoryLetter->name : '';
        $data['sender_letter_name'] = $data->sender_name;
        $data['from_department_display'] = $data->department_name;
        
        return response()->json([
            "data" => $data
        ])->setStatusCode(200);
    }

    public function detailByCode(Request $request): JsonResponse
    {
        $code = $request->code;
        $data = Letter::with(['categoryLetter', 'senderLetter'])->where("code", $code)->first();
        
        if ($data) {
            $data['category_letter_name'] = $data->categoryLetter ? $data->categoryLetter->name : '';
            $data['sender_letter_name'] = $data->sender_name;
            $data['from_department_display'] = $data->department_name;
        }
        
        return response()->json([
            "data" => $data
        ])->setStatusCode(200);
    }

    public function downloadFile(Request $request): BinaryFileResponse|JsonResponse
    {
        $id = $request->id;
        $letter = Letter::find($id);
        
        if (!$letter || !$letter->hasFile()) {
            return response()->json([
                "message" => __("File not found")
            ])->setStatusCode(404);
        }

        // Cek apakah file benar-benar ada di storage
        if (!Storage::disk('public')->exists($letter->file_path)) {
            return response()->json([
                "message" => __("File not found in storage")
            ])->setStatusCode(404);
        }

        // Cara yang benar untuk download file dari storage
        $filePath = Storage::disk('public')->path($letter->file_path);
        
        return response()->download($filePath, $letter->file_name);
    }

    public function viewFile(Request $request): Response|JsonResponse
    {
        $id = $request->id;
        $letter = Letter::find($id);
        
        if (!$letter || !$letter->hasFile()) {
            return response()->json([
                "message" => __("File not found")
            ])->setStatusCode(404);
        }

        // Cek apakah file benar-benar ada di storage
        if (!Storage::disk('public')->exists($letter->file_path)) {
            return response()->json([
                "message" => __("File not found in storage")
            ])->setStatusCode(404);
        }

        // Cara yang benar untuk view file dari storage
        $filePath = Storage::disk('public')->path($letter->file_path);
        
        return response()->file($filePath);
    }

    public function deleteFile(Request $request): JsonResponse
    {
        $id = $request->id;
        $letter = Letter::find($id);
        
        if (!$letter) {
            return response()->json([
                "message" => __("Letter not found")
            ])->setStatusCode(404);
        }

        if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) {
            Storage::disk('public')->delete($letter->file_path);
        }

        $letter->update([
            'file_name' => null,
            'file_path' => null,
            'file_size' => null,
            'file_type' => null
        ]);
        
        return response()->json([
            "message" => __("File deleted successfully")
        ])->setStatusCode(200);
    }

    public function delete(Request $request): JsonResponse
    {
        $id = $request->id;
        $letter = Letter::find($id);
        
        if (!$letter) {
            return response()->json([
                "message" => __("Letter not found")
            ])->setStatusCode(404);
        }

        // Delete file if exists before deleting the record
        if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) {
            Storage::disk('public')->delete($letter->file_path);
        }
        
        $status = $letter->delete();
        
        if (!$status) {
            return response()->json([
                "message" => __("data failed to delete")
            ])->setStatusCode(400);
        }
        
        return response()->json([
            "message" => __("data deleted successfully")
        ])->setStatusCode(200);
    }
}