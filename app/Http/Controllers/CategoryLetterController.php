<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;
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
        $categoryLetters = CategoryLetter::latest()->get();
        
        if ($request->ajax()) {
            return DataTables::of($categoryLetters)
                ->addColumn('tindakan', function($data) {
                    $button = "<button class='ubah btn btn-success m-1' id='".$data->id."'><i class='fas fa-pen m-1'></i>Edit</button>";
                    $button .= "<button class='hapus btn btn-danger m-1' id='".$data->id."'><i class='fas fa-trash m-1'></i>Delete</button>";
                    return $button;
                })
                ->rawColumns(['tindakan'])
                ->make(true);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Request harus menggunakan AJAX'
        ], 400);
    }

    /**
     * Save new category letter
     */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
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
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while saving'
            ], 500);
        }
    }

    /**
     * Get detail of category letter
     */
    public function detail(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:category_letters,id'
        ]);

        try {
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
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching data'
            ], 500);
        }
    }

    /**
     * Update category letter
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:category_letters,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            $id = $request->id;
            $data = CategoryLetter::find($id);
            
            if (!$data) {
                return response()->json([
                    'message' => 'jenis surat tidak dapat ditemukan'
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
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating'
            ], 500);
        }
    }

    /**
     * Delete category letter
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:category_letters,id'
        ]);

        try {
            $id = $request->id;
            $categoryLetter = CategoryLetter::find($id);
            
            if (!$categoryLetter) {
                return response()->json([
                    'message' => 'jenis surat tidak dapat ditemukan'
                ], 404);
            }
            
            $status = $categoryLetter->delete();
            
            if (!$status) {
                return response()->json([
                    'message' => 'Failed to delete category letter'
                ], 400);
            }
            
            return response()->json([
                'message' => 'jenis surat berhasil dihapus'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting'
            ], 500);
        }
    }
}