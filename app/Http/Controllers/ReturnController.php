<?php

namespace App\Http\Controllers;

use App\Models\ItemReturn;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class ReturnController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        $items = Item::where('active', 'true')->get(); // Only active items
        $returns = ItemReturn::with(['customer', 'item'])->orderBy('created_at', 'desc')->get();
        
        return view('admin.master.returns', compact('customers', 'items', 'returns'));
    }

    public function list(Request $request): JsonResponse
    {
        $returns = ItemReturn::with(['customer', 'item'])->latest()->get();
        
        if($request->ajax()){
            return DataTables::of($returns)
                ->addColumn('tindakan', function($data){
                    $button = "<button class='ubah btn btn-success m-1' id='".$data->id."'><i class='fas fa-pen m-1'></i>".__("edit")."</button>";
                    $button .= "<button class='hapus btn btn-danger m-1' id='".$data->id."'><i class='fas fa-trash m-1'></i>".__("delete")."</button>";
                    return $button;
                })
                ->addColumn('customer_name', function($data){
                    return $data->customer->name ?? 'N/A';
                })
                ->addColumn('item_name', function($data){
                    return $data->item->name ?? 'Item not found';
                })
                ->editColumn('return_date', function($data){
                    return \Carbon\Carbon::parse($data->return_date)->format('d-m-Y');
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
        // Pastikan hanya employee yang bisa menambah data
        if (Auth::user()->role->name !== 'employee') {
            return response()->json(
                ["message" => __("Unauthorized access")]
            )->setStatusCode(403);
        }

        $validator = Validator::make($request->all(), [
            'borrower_id' => 'required|exists:customers,id',
            'item_code' => 'required|exists:items,code',
            'return_date' => 'required|date',
            'status' => 'required|in:Baik,Rusak',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ["message" => "Validation failed: " . $validator->errors()->first()]
            )->setStatusCode(400);
        }

        // Check if item exists
        $item = Item::where('code', $request->item_code)->first();
        if (!$item) {
            return response()->json(
                ["message" => __("Item not found")]
            )->setStatusCode(404);
        }

        // Create return record
        $itemReturn = new ItemReturn();
        $itemReturn->borrower_id = $request->borrower_id;
        $itemReturn->item_code = $request->item_code;
        $itemReturn->return_date = $request->return_date;
        $itemReturn->status = $request->status;
        $status = $itemReturn->save();

        if(!$status){
            return response()->json(
                ["message" => __("failed to save")]
            )->setStatusCode(400);
        }

        return response()->json([
            "message" => __("saved successfully")
        ])->setStatusCode(200);
    }

    public function detail(Request $request): JsonResponse
    {
        $id = $request->id;
        $data = ItemReturn::with(['customer', 'item'])->find($id);
        return response()->json(
            ["data" => $data]
        )->setStatusCode(200);
    }

    public function update(Request $request): JsonResponse
    {
        // Pastikan hanya employee yang bisa mengupdate data
        if (Auth::user()->role->name !== 'employee') {
            return response()->json(
                ["message" => __("Unauthorized access")]
            )->setStatusCode(403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'borrower_id' => 'required|exists:customers,id',
            'item_code' => 'required|exists:items,code',
            'return_date' => 'required|date',
            'status' => 'required|in:Baik,Rusak',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ["message" => "Validation failed: " . $validator->errors()->first()]
            )->setStatusCode(400);
        }

        $id = $request->id;
        $data = ItemReturn::find($id);
        $data->fill($request->all());
        $status = $data->save();

        if(!$status){
            return response()->json(
                ["message" => __("data failed to change")]
            )->setStatusCode(400);
        }

        return response()->json([
            "message" => __("data changed successfully")
        ])->setStatusCode(200);
    }

    public function delete(Request $request): JsonResponse
    {
        // Pastikan hanya employee yang bisa menghapus data
        if (Auth::user()->role->name !== 'employee') {
            return response()->json(
                ["message" => __("Unauthorized access")]
            )->setStatusCode(403);
        }

        $id = $request->id;
        $itemReturn = ItemReturn::find($id);
        $status = $itemReturn->delete();

        if(!$status){
            return response()->json(
                ["message" => __("data failed to delete")]
            )->setStatusCode(400);
        }

        return response()->json([
            "message" => __("data deleted successfully")
        ])->setStatusCode(200);
    }

    // Untuk backward compatibility dengan route existing
    public function edit($id)
    {
        try {
            $return = ItemReturn::with('item')->findOrFail($id);
            $customers = Customer::all();
            $items = Item::where('active', 'true')->get();
            $returns = ItemReturn::with(['customer', 'item'])->orderBy('created_at', 'desc')->get();
            
            return view('admin.master.returns', compact('customers', 'items', 'returns', 'return'));
        } catch (\Exception $e) {
            return redirect()->route('return.index')
                ->with('error', 'Return record not found');
        }
    }
}