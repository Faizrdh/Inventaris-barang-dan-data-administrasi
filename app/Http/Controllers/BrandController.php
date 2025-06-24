<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateBrandRequest;
use App\Http\Requests\DetailBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Requests\DeleteBrandRequest;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;
use Illuminate\View\View;
use App\Models\Brand;
use Illuminate\Support\Facades\Auth;

// Representation Class Controller Brand
class BrandController extends Controller
{
    public function index(): View
    {
        return view('admin.master.barang.merk');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $brands = Brand::latest()->get();
           
            if($request->ajax()){
                return DataTables::of($brands)
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
           
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // save new brand
    public function save(CreateBrandRequest $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can add data.'
                ], 403);
            }

            $brands = new Brand();
            $brands->name = $request->name;
            if($request->has('description')){
                $brands->description = $request->description;
            }
            $status = $brands->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("failed to save")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("saved successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // get detail brand
    public function detail(DetailBrandRequest $request): JsonResponse
    {
        try {
            $id = $request->id;
            $data = Brand::find($id);
            
            if(!$data) {
                return response()->json([
                    "success" => false,
                    "message" => __("Data not found")
                ])->setStatusCode(404);
            }
            
            return response()->json([
                "data" => $data
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // update brand
    public function update(UpdateBrandRequest $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can edit data.'
                ], 403);
            }

            $id = $request->id;
            $data = Brand::find($id);
            
            if(!$data) {
                return response()->json([
                    "success" => false,
                    "message" => __("Data not found")
                ])->setStatusCode(404);
            }
            
            $data->fill($request->all());
            $status = $data->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("data failed to change")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("data changed successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // delete brand
    public function delete(DeleteBrandRequest $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can delete data.'
                ], 403);
            }

            $id = $request->id;
            $brands = Brand::find($id);
            
            if(!$brands) {
                return response()->json([
                    "success" => false,
                    "message" => __("Data not found")
                ])->setStatusCode(404);
            }
            
            $status = $brands->delete();
            
            if(!$status){
                return response()->json([
                    "message" => __("data failed to delete")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("data deleted successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}