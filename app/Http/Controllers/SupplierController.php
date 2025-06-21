<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    public function index(): View
    {
        return view('admin.master.supplier');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $suppliers = Supplier::latest()->get();
            
            if($request->ajax()){
                return DataTables::of($suppliers)
                    ->addColumn('tindakan', function($data){
                        // Hanya tampilkan action buttons untuk employee
                        if(Auth::user()->role->name == 'employee') {
                            $button = "<button class='ubah btn btn-success m-1' id='".$data->id."'><i class='fas fa-pen m-1'></i>".__("edit")."</button>";
                            $button .= "<button class='hapus btn btn-danger m-1' id='".$data->id."'><i class='fas fa-trash m-1'></i>".__("delete")."</button>";
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
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Model not found: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error in list suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

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

            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255'
            ]);

            $supplier = new Supplier();
            $supplier->name = $validated['name'];
            $supplier->phone_number = $validated['phone_number'];
            $supplier->address = $validated['address'];
            
            if($request->has('email') && !empty($validated['email'])){
                $supplier->email = $validated['email'];
            }
            if($request->has('website') && !empty($validated['website'])){
                $supplier->website = $validated['website'];
            }
            
            $status = $supplier->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("failed to save")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("saved successfully")
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error saving supplier: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:suppliers,id'
            ]);
            
            $supplier = Supplier::find($validated['id']);
            
            if(!$supplier) {
                return response()->json([
                    "success" => false,
                    "message" => "Supplier not found."
                ], 404);
            }
            
            return response()->json([
                "data" => $supplier
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error getting supplier detail: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

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

            $validated = $request->validate([
                'id' => 'required|integer|exists:suppliers,id',
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255'
            ]);
            
            $supplier = Supplier::find($validated['id']);
            
            if(!$supplier) {
                return response()->json([
                    "success" => false,
                    "message" => "Supplier not found."
                ], 404);
            }
            
            // Update data dengan hanya field yang diizinkan
            $supplier->fill($request->only(['name', 'phone_number', 'address', 'email', 'website']));
            $status = $supplier->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("data failed to change")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("data berhasil diubah")
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

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
                'id' => 'required|integer|exists:suppliers,id'
            ]);
            
            $supplier = Supplier::find($request->id);
            
            if(!$supplier) {
                return response()->json([
                    "success" => false,
                    "message" => "Supplier not found."
                ], 404);
            }
            
            $status = $supplier->delete();
            
            if(!$status){
                return response()->json([
                    "message" => __("data failed to delete")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("data deleted successfully")
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}