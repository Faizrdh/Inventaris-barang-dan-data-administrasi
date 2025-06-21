<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;
use Illuminate\View\View;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    public function index(): View
    {
        return view('admin.master.barang.satuan');
    }

    public function list(Request $request): JsonResponse
    {
        $units = Unit::latest()->get();
        
        if($request->ajax()){
            return DataTables::of($units)
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
    }

    public function save(Request $request): JsonResponse
    {
        // Cek apakah user adalah employee
        if(Auth::user()->role->name != 'employee') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Only employees can add data.'
            ], 403);
        }

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500'
        ]);

        $units = new Unit();
        $units->name = $request->name;
        if($request->has('description')){
            $units->description = $request->description;
        }
        $status = $units->save();
        
        if(!$status){
            return response()->json([
                "message" => __("failed to save")
            ])->setStatusCode(400);
        }
        
        return response()->json([
            "message" => __("saved successfully")
        ])->setStatusCode(200);
    }

    public function detail(Request $request): JsonResponse
    {
        // Validasi input
        $request->validate([
            'id' => 'required|integer|exists:units,id'
        ]);

        $id = $request->id;
        $data = Unit::find($id);
        
        if(!$data) {
            return response()->json([
                "message" => __("Data not found")
            ])->setStatusCode(404);
        }
        
        return response()->json([
            "data" => $data
        ])->setStatusCode(200);
    }

    public function update(Request $request): JsonResponse
    {
        // Cek apakah user adalah employee
        if(Auth::user()->role->name != 'employee') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Only employees can edit data.'
            ], 403);
        }

        // Validasi input
        $request->validate([
            'id' => 'required|integer|exists:units,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500'
        ]);

        $id = $request->id;
        $data = Unit::find($id);
        
        if(!$data) {
            return response()->json([
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
    }

    public function delete(Request $request): JsonResponse
    {
        // Cek apakah user adalah employee
        if(Auth::user()->role->name != 'employee') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Only employees can delete data.'
            ], 403);
        }

        // Validasi input
        $request->validate([
            'id' => 'required|integer|exists:units,id'
        ]);

        $id = $request->id;
        $units = Unit::find($id);
        
        if(!$units) {
            return response()->json([
                "message" => __("Data not found")
            ])->setStatusCode(404);
        }
        
        $status = $units->delete();
        
        if(!$status){
            return response()->json([
                "message" => __("data failed to delete")
            ])->setStatusCode(400);
        }
        
        return response()->json([
            "message" => __("data deleted successfully")
        ])->setStatusCode(200);
    }
}