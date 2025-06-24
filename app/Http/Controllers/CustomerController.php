<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use App\Models\Customer;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('admin.master.customer');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $customers = Customer::latest()->get();
            
            if($request->ajax()){
                return DataTables::of($customers)
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
            Log::error('Error in customer list: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save(CreateCustomerRequest $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can add data.'
                ], 403);
            }

            $customer = new Customer();
            $customer->name = $request->name;
            $customer->phone_number = $request->phone_number;
            $customer->address = $request->address;
            
            $status = $customer->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("failed to save")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("saved successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error saving customer: ' . $e->getMessage());
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
                'id' => 'required|integer|exists:customers,id'
            ]);
            
            $customer = Customer::find($validated['id']);
            
            if(!$customer) {
                return response()->json([
                    "success" => false,
                    "message" => "Customer not found."
                ], 404);
            }
            
            return response()->json([
                "data" => $customer
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error getting customer detail: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateCustomerRequest $request): JsonResponse
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
                'id' => 'required|integer|exists:customers,id'
            ]);
            
            $customer = Customer::find($validated['id']);
            
            if(!$customer) {
                return response()->json([
                    "success" => false,
                    "message" => "Customer not found."
                ], 404);
            }
            
            $customer->fill($request->only(['name', 'phone_number', 'address']));
            $status = $customer->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("data failed to change")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("data changed successfully")
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
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
                'id' => 'required|integer|exists:customers,id'
            ]);
            
            $customer = Customer::find($request->id);
            
            if(!$customer) {
                return response()->json([
                    "success" => false,
                    "message" => "Customer not found."
                ], 404);
            }
            
            $status = $customer->delete();
            
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
            Log::error('Error deleting customer: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}