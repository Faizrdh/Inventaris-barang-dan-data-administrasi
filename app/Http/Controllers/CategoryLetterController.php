<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\CategoryLetter;

class CategoryLetterController extends Controller
{
    /**
     * Display the main view
     */
    public function index(): View
    {
         return view('admin.master.Surat.jenis-surat');
    }

    /**
     * Get data for DataTables
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $categoryLetters = CategoryLetter::latest()->get();
            
            if ($request->ajax()) {
                return DataTables::of($categoryLetters)
                    ->addColumn('tindakan', function($data) {
                        // Hanya tampilkan action buttons untuk employee
                        if(Auth::user()->role->name == 'employee') {
                            $button = "<button class='ubah btn btn-success m-1' id='".$data->id."'><i class='fas fa-pen m-1'></i>Edit</button>";
                            $button .= "<button class='hapus btn btn-danger m-1' id='".$data->id."'><i class='fas fa-trash m-1'></i>Delete</button>";
                            return $button;
                        }
                        // Jika bukan employee, return empty string
                        return '';
                    })
                    ->rawColumns(['tindakan'])
                    ->make(true);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Request harus menggunakan AJAX'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save new category letter
     */
    public function save(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can add data.'
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500'
            ]);

            $categoryLetter = new CategoryLetter();
            $categoryLetter->name = $request->name;
            
            if ($request->has('description')) {
                $categoryLetter->description = $request->description;
            }
            
            $status = $categoryLetter->save();
            
            if (!$status) {
                return response()->json([
                    'message' => 'Failed to save category letter'
                ], 400);
            }
            
            return response()->json([
                'message' => 'Berhasil menambahkan data jenis surat'
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detail of category letter
     */
    public function detail(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:category_letters,id'
            ]);

            $id = $request->id;
            $data = CategoryLetter::find($id);
            
            if (!$data) {
                return response()->json([
                    'message' => 'Jenis surat tidak ditemukan'
                ], 404);
            }
            
            return response()->json([
                'data' => $data
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update category letter
     */
    public function update(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can edit data.'
                ], 403);
            }

            $request->validate([
                'id' => 'required|integer|exists:category_letters,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500'
            ]);

            $id = $request->id;
            $data = CategoryLetter::find($id);
            
            if (!$data) {
                return response()->json([
                    'message' => 'Jenis surat tidak dapat ditemukan'
                ], 404);
            }
            
            $data->fill($request->only(['name', 'description']));
            $status = $data->save();
            
            if (!$status) {
                return response()->json([
                    'message' => 'Failed to update category letter'
                ], 400);
            }
            
            return response()->json([
                'message' => 'Jenis surat berhasil di perbarui'
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete category letter
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can delete data.'
                ], 403);
            }

            $request->validate([
                'id' => 'required|integer|exists:category_letters,id'
            ]);

            $id = $request->id;
            $categoryLetter = CategoryLetter::find($id);
            
            if (!$categoryLetter) {
                return response()->json([
                    'message' => 'Jenis surat tidak dapat ditemukan'
                ], 404);
            }
            
            $status = $categoryLetter->delete();
            
            if (!$status) {
                return response()->json([
                    'message' => 'Failed to delete category letter'
                ], 400);
            }
            
            return response()->json([
                'message' => 'Jenis surat berhasil dihapus'
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}