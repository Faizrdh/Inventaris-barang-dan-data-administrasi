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
        try {
            $goodsins = GoodsIn::with(['item.unit', 'user', 'supplier'])->latest();
            
            // Filter by date range if provided
            if ($request->start_date) {
                $goodsins->where('date_received', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $goodsins->where('date_received', '<=', $request->end_date);
            }
            
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
                    return $data->supplier->name ?? 'Unknown Supplier';
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
                    $button = "<button class='ubah btn btn-success btn-sm m-1' id='" . $data->id . "' title='Edit'>";
                    $button .= "<i class='fas fa-edit'></i> " . __("edit") . "</button>";
                    $button .= "<button class='hapus btn btn-danger btn-sm m-1' id='" . $data->id . "' title='Delete'>";
                    $button .= "<i class='fas fa-trash'></i> " . __("delete") . "</button>";
                    $button .= "<button class='detail btn btn-info btn-sm m-1' id='" . $data->id . "' title='Detail'>";
                    $button .= "<i class='fas fa-eye'></i> " . __("detail") . "</button>";
                    
                    return $button;
                })
                ->rawColumns(['quantity_formatted', 'current_stock', 'tindakan'])
                ->make(true);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'This endpoint requires AJAX request'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Error in transaction in list: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error loading data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Enhanced validation - Tanpa price
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'date_received' => 'required|date|before_or_equal:today',
                'quantity' => 'required|numeric|min:1|max:999999',
                'invoice_number' => 'required|string|max:255',
                'item_id' => 'required|exists:items,id'
            ]);

            // Check if item exists and is valid
            $item = Item::find($validated['item_id']);
            if (!$item) {
                return response()->json([
                    'message' => 'Item not found'
                ], 404);
            }

            // Create goods in record - Tanpa price
            $goodsIn = GoodsIn::create([
                'user_id' => $validated['user_id'],
                'supplier_id' => $validated['supplier_id'],
                'date_received' => $validated['date_received'],
                'quantity' => $validated['quantity'],
                'invoice_number' => $validated['invoice_number'],
                'item_id' => $validated['item_id']
            ]);

            // Update item to active
            $item->active = "true";
            $item->save();

            // Get updated stock info
            $newStock = $this->calculateCurrentStock($item->id);

            Log::info('Goods in transaction created', [
                'goods_in_id' => $goodsIn->id,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'quantity_added' => $validated['quantity'],
                'new_stock' => $newStock,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __("saved successfully"),
                'data' => [
                    'goods_in_id' => $goodsIn->id,
                    'item_name' => $item->name,
                    'quantity_added' => $validated['quantity'],
                    'new_stock' => $newStock,
                    'unit' => $item->unit->name ?? 'Unit'
                ]
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving goods in: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request): JsonResponse
    {
        try {
            $id = $request->id;
            $data = GoodsIn::with(['supplier', 'user', 'item.category', 'item.unit'])->find($id);
            
            if(!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ], 404);
            }
            
            $item = $data->item;
            if(!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }
            
            // Get current stock
            $currentStock = $this->calculateCurrentStock($item->id);
            
            // Prepare response data - Tanpa price
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
                'success' => true,
                'data' => $responseData
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching goods in detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Enhanced validation - Tanpa price
            $validated = $request->validate([
                'id' => 'required|exists:goods_in,id',
                'user_id' => 'required|exists:users,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'date_received' => 'required|date|before_or_equal:today',
                'quantity' => 'required|numeric|min:1|max:999999',
                'invoice_number' => 'required|string|max:255',
                'item_id' => 'required|exists:items,id'
            ]);

            $goodsIn = GoodsIn::find($validated['id']);
            if(!$goodsIn) {
                return response()->json([
                    'success' => false,
                    'message' => __("data not found")
                ])->setStatusCode(404);
            }

            // Store old values for logging
            $oldItemId = $goodsIn->item_id;
            $oldQuantity = $goodsIn->quantity;

            // Update the record - Tanpa price
            $goodsIn->user_id = $validated['user_id'];
            $goodsIn->supplier_id = $validated['supplier_id'];
            $goodsIn->date_received = $validated['date_received'];
            $goodsIn->quantity = $validated['quantity'];
            $goodsIn->invoice_number = $validated['invoice_number'];
            $goodsIn->item_id = $validated['item_id'];
            
            $status = $goodsIn->save();

            if(!$status){
                return response()->json([
                    'success' => false,
                    'message' => __("data failed to change")
                ])->setStatusCode(400);
            }

            // Get updated stock for response
            $newStock = $this->calculateCurrentStock($goodsIn->item_id);
            $item = Item::find($goodsIn->item_id);

            Log::info('Goods in transaction updated', [
                'goods_in_id' => $goodsIn->id,
                'old_item_id' => $oldItemId,
                'new_item_id' => $goodsIn->item_id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $goodsIn->quantity,
                'new_stock' => $newStock,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __("data changed successfully"),
                'data' => [
                    'goods_in_id' => $goodsIn->id,
                    'item_name' => $item->name,
                    'new_quantity' => $goodsIn->quantity,
                    'new_stock' => $newStock,
                    'unit' => $item->unit->name ?? 'Unit'
                ]
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating goods in: ' . $e->getMessage());
            return response()->json([
                'success' => false,
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
                    'success' => false,
                    'message' => __("data not found")
                ])->setStatusCode(404);
            }

            // Store info for logging before deletion
            $itemId = $goodsIn->item_id;
            $itemName = $goodsIn->item->name ?? 'Unknown';
            $quantity = $goodsIn->quantity;

            $status = $goodsIn->delete();

            if(!$status){
                return response()->json([
                    'success' => false,
                    'message' => __("data failed to delete")
                ])->setStatusCode(400);
            }

            // Get updated stock after deletion
            $newStock = $this->calculateCurrentStock($itemId);

            Log::info('Goods in transaction deleted', [
                'goods_in_id' => $id,
                'item_id' => $itemId,
                'item_name' => $itemName,
                'quantity_removed' => $quantity,
                'new_stock' => $newStock,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __("data deleted successfully"),
                'data' => [
                    'item_name' => $itemName,
                    'quantity_removed' => $quantity,
                    'new_stock' => $newStock
                ]
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting goods in: ' . $e->getMessage());
            return response()->json([
                'success' => false,
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
                'success' => true,
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