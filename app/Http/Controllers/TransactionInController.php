<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\GoodsIn;
use App\Models\Supplier;
use App\Models\Item;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TransactionInController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::all();
        
        return view('admin.master.transaksi.masuk', compact('suppliers'));
    }

    public function list(Request $request): JsonResponse
    {
        $goodsins = GoodsIn::with(['item.unit', 'user', 'supplier'])->latest()->get();
        
        if($request->ajax()){
            return DataTables::of($goodsins)
                ->addColumn('quantity_formatted', function($data) {
                    $unit = $data->item->unit->name ?? 'Unit';
                    return '<span class="badge badge-success">' . number_format($data->quantity) . ' ' . $unit . '</span>';
                })
                ->addColumn('date_received_formatted', function($data) {
                    return Carbon::parse($data->date_received)->format('d M Y');
                })
                ->addColumn('kode_barang', function($data) {
                    return $data->item->code ?? '-';
                })
                ->addColumn('supplier_name', function($data) {
                    return $data->item->name ?? 'Unknown Item';
                })
                ->addColumn('item_name', function($data) {
                    return $data->item->name ?? 'Unknown Item';
                })
                ->addColumn('user_name', function($data) {
                    return $data->user->name ?? 'Unknown User';
                })
                ->addColumn('current_stock', function($data) {
                    $currentStock = $this->calculateCurrentStock($data->item_id);
                    $unit = $data->item->unit->name ?? 'Unit';
                    $class = $currentStock <= 0 ? 'text-danger' : ($currentStock <= 3 ? 'text-warning' : 'text-success');
                    
                    return '<span class="' . $class . '">' . number_format($currentStock) . ' ' . $unit . '</span>';
                })
                ->addColumn('tindakan', function($data) {
                    $button = "<button class='ubah btn btn-success btn-sm m-1' id='" . $data->id . "'>";
                    $button .= "<i class='fas fa-edit'></i> " . __("edit") . "</button>";
                    $button .= "<button class='hapus btn btn-danger btn-sm m-1' id='" . $data->id . "'>";
                    $button .= "<i class='fas fa-trash'></i> " . __("delete") . "</button>";
                    
                    return $button;
                })
                ->rawColumns(['quantity_formatted', 'current_stock', 'tindakan'])
                ->make(true);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Request harus menggunakan AJAX'
        ], 400); // Bad Request
    }

    public function save(Request $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Validasi request
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'date_received' => 'required|date|before_or_equal:today',
                'quantity' => 'required|numeric|min:1|max:999999',
                'invoice_number' => 'required|string|max:255',
                'item_id' => 'required|exists:items,id'
            ]);

            // Check if item exists and is valid
            $item = Item::find($request->item_id);
            if (!$item) {
                return response()->json([
                    "message" => "Item not found"
                ])->setStatusCode(404);
            }

            // Create goods in record
            $goodsIn = new GoodsIn();
            $goodsIn->user_id = $request->user_id;
            $goodsIn->supplier_id = $request->supplier_id;
            $goodsIn->date_received = $request->date_received;
            $goodsIn->quantity = $request->quantity;
            $goodsIn->invoice_number = $request->invoice_number;
            $goodsIn->item_id = $request->item_id;
            
            $status = $goodsIn->save();

            if(!$status){
                return response()->json([
                    "message" => __("failed to save")
                ])->setStatusCode(400);
            }

            // Update item to active
            $item->active = "true";
            $item->save();

            Log::info('Goods in transaction created', [
                'goods_in_id' => $goodsIn->id,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'quantity_added' => $request->quantity,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                "message" => __("saved successfully")
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving goods in: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error saving data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request): JsonResponse
    {
        $id = $request->id;
        $data = GoodsIn::with(['supplier', 'user', 'item.category', 'item.unit'])->find($id);
        
        if(!$data) {
            return response()->json([
                'message' => 'Data not found'
            ], 404);
        }
        
        $item = $data->item;
        if(!$item) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }
        
        // Get current stock
        $currentStock = $this->calculateCurrentStock($item->id);
        
        // Prepare response data
        $responseData = [
            'id' => $data->id,
            'user_id' => $data->user_id,
            'supplier_id' => $data->supplier_id,
            'date_received' => $data->date_received,
            'quantity' => $data->quantity,
            'invoice_number' => $data->invoice_number,
            'item_id' => $data->item_id,
            
            // Item details
            'kode_barang' => $item->code,
            'nama_barang' => $item->name,
            'satuan_barang' => $item->unit->name ?? 'Unit',
            'jenis_barang' => $item->category->name ?? 'Category',
            'current_stock' => $currentStock,
            
            // Related details
            'supplier_name' => $data->supplier->name ?? 'Unknown',
            'user_name' => $data->user->name ?? 'Unknown',
            
            // Timestamps
            'created_at' => $data->created_at,
            'updated_at' => $data->updated_at
        ];
        
        return response()->json([
            "data" => $responseData
        ])->setStatusCode(200);
    }

    public function update(Request $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Validasi request
            $request->validate([
                'id' => 'required|exists:goods_in,id',
                'user_id' => 'required|exists:users,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'date_received' => 'required|date|before_or_equal:today',
                'quantity' => 'required|numeric|min:1|max:999999',
                'invoice_number' => 'required|string|max:255',
                'item_id' => 'required|exists:items,id'
            ]);

            $id = $request->id;
            $data = GoodsIn::find($id);
            
            if(!$data){
                return response()->json([
                    "message" => __("data not found")
                ])->setStatusCode(404);
            }

            // Store old values for logging
            $oldItemId = $data->item_id;
            $oldQuantity = $data->quantity;

            // Update the record
            $data->fill($request->all());
            $status = $data->save();

            if(!$status){
                return response()->json([
                    "message" => __("data failed to change")
                ])->setStatusCode(400);
            }

            Log::info('Goods in transaction updated', [
                'goods_in_id' => $data->id,
                'old_item_id' => $oldItemId,
                'new_item_id' => $data->item_id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $data->quantity,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                "message" => __("data changed successfully")
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating goods in: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $id = $request->id;
            $goodsIn = GoodsIn::with('item')->find($id);

            if(!$goodsIn) {
                return response()->json([
                    "message" => __("data not found")
                ])->setStatusCode(404);
            }

            // Store info for logging before deletion
            $itemId = $goodsIn->item_id;
            $itemName = $goodsIn->item->name ?? 'Unknown';
            $quantity = $goodsIn->quantity;

            $status = $goodsIn->delete();

            if(!$status){
                return response()->json([
                    "message" => __("data failed to delete")
                ])->setStatusCode(400);
            }

            Log::info('Goods in transaction deleted', [
                'goods_in_id' => $id,
                'item_id' => $itemId,
                'item_name' => $itemName,
                'quantity_removed' => $quantity,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                "message" => __("data deleted successfully")
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting goods in: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hitung stock saat ini berdasarkan transaksi masuk dan keluar
     */
    private function calculateCurrentStock($itemId)
    {
        try {
            // Total barang masuk
            $totalIn = GoodsIn::where('item_id', $itemId)->sum('quantity') ?? 0;
            
            // Total barang keluar (jika ada tabel goods_out)
            $totalOut = 0;
            if (class_exists('App\Models\GoodsOut')) {
                $totalOut = \App\Models\GoodsOut::where('item_id', $itemId)->sum('quantity') ?? 0;
            }
            
            return $totalIn - $totalOut;
            
        } catch (\Exception $e) {
            Log::error('Error calculating stock for item ' . $itemId . ': ' . $e->getMessage());
            // Fallback ke quantity di tabel items
            $item = Item::find($itemId);
            return $item ? $item->quantity : 0;
        }
    }

    /**
     * Get real-time stock info for item
     */
    public function getItemStock(Request $request): JsonResponse
    {
        try {
            $itemId = $request->item_id;
            
            if (!$itemId) {
                return response()->json([
                    'error' => 'Item ID is required'
                ], 400);
            }

            $item = Item::with(['unit', 'category'])->find($itemId);
            if (!$item) {
                return response()->json([
                    'error' => 'Item not found'
                ], 404);
            }

            $currentStock = $this->calculateCurrentStock($itemId);

            return response()->json([
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'current_stock' => $currentStock,
                    'unit' => $item->unit->name ?? 'Unit',
                    'category' => $item->category->name ?? 'Category'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting item stock: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error getting item stock: ' . $e->getMessage()
            ], 500);
        }
    }
}