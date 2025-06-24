<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Role;

class EmployeeController extends Controller
{
    public function index(): View
    {   
        $roles = Role::where('name','employee')->get();
        if(Auth::user()->role->name == 'super_admin'){
            $roles = Role::all();
        }
        return view('admin.settings.employee',compact('roles'));
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $staff = User::with('role')->whereHas('role', function(Builder $builder){
                $builder = $builder->where('name', 'employee');
                if(Auth::user()->role->name != 'admin'){
                    $builder->orWhere('name', 'admin')
                           ->orWhere('name', 'super_admin');
                }
            })->latest()->get();
            
            if(Auth::user()->role->name == 'employee'){
                $id_staff = Role::where('name', 'employee')->first()->id;
                $staff = User::with('role')->where('role_id', $id_staff)->latest()->get();
            }
            
            if($request->ajax()){
                return DataTables::of($staff)
                    ->addColumn('role_name', function($data){
                        return $data->role->name;
                    })
                    ->addColumn('tindakan', function($data){
                        $button = "<button class='ubah btn btn-success m-1' id='".$data->id."'><i class='fas fa-pen m-1'></i>".__("edit")."</button>";
                        $button .= "<button class='hapus btn btn-danger m-1' id='".$data->id."'><i class='fas fa-trash m-1'></i>".__("delete")."</button>";
                        return $button;
                    })
                    ->rawColumns(['tindakan'])
                    ->make(true);
            }
            
            return response()->json([
                'success' => true,
                'data' => $staff
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => 'required|string|min:6',
                'role_id' => 'required|exists:roles,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $user = new User();
            $user->name = $request->name;
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->role_id = $request->role_id;
            $status = $user->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("failed to save")
                ], 400);
            }
            
            return response()->json([
                "message" => __("saved successfully")
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request): JsonResponse
    {
        try {
            $id = $request->id;
            $user = User::with('role')->find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            
            return response()->json([
                "data" => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:users,id',
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $request->id,
                'password' => 'nullable|string|min:6',
                'role_id' => 'required|exists:roles,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $id = $request->id;
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Update data user
            $user->name = $request->name;
            $user->username = $request->username;
            $user->role_id = $request->role_id;
            
            // Update password hanya jika diisi
            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }
            
            $status = $user->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("data failed to change")
                ], 400);
            }
            
            return response()->json([
                "message" => __("data changed successfully")
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            $id = $request->id;
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            
            $status = $user->delete();
            
            if(!$status){
                return response()->json([
                    "message" => __("data failed to delete")
                ], 400);
            }
            
            return response()->json([
                "message" => __("data deleted successfully")
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}