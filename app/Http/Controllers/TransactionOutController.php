<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\GoodsOut;
use App\Models\GoodsIn;
use App\Models\Customer;
use App\Models\Item;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TransactionOutController extends Controller
{
    public function index(): View
    {
        $in_status = Item::where('active', 'true')->count();
        $customers = Customer::all();

        $stockSummary = [
            'total_items' => Item::where('active', 'true')->count(),
            'low_stock_items' => 0,
            'out_of_stock_items' => 0
        ];

        $items = Item::where('active', 'true')->get();
        foreach ($items as $item) {
            $currentStock = GoodsOut::calculateCurrentStock($item->id);
            if ($currentStock == 0) {
                $stockSummary['out_of_stock_items']++;
            } elseif ($currentStock <= 3) {
                $stockSummary['low_stock_items']++;
            }
        }

        return view('admin.master.transaksi.keluar', compact('customers', 'in_status', 'stockSummary'));
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $goodsouts = GoodsOut::with(['item.unit', 'user', 'customer'])->latest();

            if ($request->start_date) {
                $goodsouts->where('date_out', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $goodsouts->where('date_out', '<=', $request->end_date);
            }

            if ($request->ajax()) {
                return DataTables::of($goodsouts)
                    ->addColumn('quantity_formatted', function ($data) {
                        $unit = $data->item->unit->name ?? 'Unit';
                        return '<span class="badge badge-warning">' . number_format($data->quantity) . ' ' . $unit . '</span>';
                    })
                    ->addColumn('date_out_formatted', function ($data) {
                        return Carbon::parse($data->date_out)->format('d M Y');
                    })
                    ->addColumn('kode_barang', function ($data) {
                        return $data->item ? $data->item->code : '-';
                    })
                    ->addColumn('customer_name', function ($data) {
                        return $data->customer->name ?? 'Unknown Customer';
                    })
                    ->addColumn('item_name', function ($data) {
                        return $data->item->name ?? 'Unknown Item';
                    })
                    ->addColumn('user_name', function ($data) {
                        return $data->user->name ?? 'Unknown User';
                    })
                    ->addColumn('current_stock', function ($data) {
                        $currentStock = GoodsOut::calculateCurrentStock($data->item_id);
                        $unit = $data->item->unit->name ?? 'Unit';
                        $class = $currentStock <= 0 ? 'text-danger' : ($currentStock <= 3 ? 'text-warning' : 'text-success');
                        return '<span class="' . $class . '">' . number_format($currentStock) . ' ' . $unit . '</span>';
                    })
                    ->addColumn('stock_impact', function ($data) {
                        $currentStock = GoodsOut::calculateCurrentStock($data->item_id);
                        $stockBefore = $currentStock + $data->quantity;
                        return '<small class="text-muted">' . number_format($stockBefore) . ' → ' . number_format($currentStock) . '</small>';
                    })
                    ->addColumn('tindakan', function ($data) {
                        $button = "<button class='ubah btn btn-success btn-sm m-1' id='" . $data->id . "' title='Edit'>";
                        $button .= "<i class='fas fa-edit'></i> " . __("edit") . "</button>";
                        $button .= "<button class='hapus btn btn-danger btn-sm m-1' id='" . $data->id . "' title='Delete'>";
                        $button .= "<i class='fas fa-trash'></i> " . __("delete") . "</button>";
                        $button .= "<button class='detail btn btn-info btn-sm m-1' id='" . $data->id . "' title='Detail'>";
                        $button .= "<i class='fas fa-eye'></i> " . __("detail") . "</button>";
                        return $button;
                    })
                    ->rawColumns(['quantity_formatted', 'current_stock', 'stock_impact', 'tindakan'])
                    ->make(true);
            }

            return response()->json([
                'success' => false,
                'message' => 'This endpoint requires AJAX request'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error in transaction out list: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading data: ' . $e->getMessage()], 500);
        }
    }

    public function save(Request $request): JsonResponse
{
    DB::beginTransaction();

    try {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required|numeric|min:1|max:999999',
            'date_out' => 'required|date|before_or_equal:' . now()->format('Y-m-d'),
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|string|max:255'
        ]);

        Log::info('Validation passed', $validated); // Tambahkan log untuk debugging

        $item = Item::find($validated['item_id']);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        // Jika invoice_number tidak disediakan, buat secara otomatis
        if (empty($validated['invoice_number'])) {
            $validated['invoice_number'] = 'BRGKLR-' . time();
        }

        $stockCheck = GoodsOut::validateStockAvailability($validated['item_id'], $validated['quantity']);
        if (!$stockCheck['is_available']) {
            return response()->json(['success' => false, 'message' => $stockCheck['message'], 'stock_info' => $stockCheck], 400);
        }

        $goodsOut = GoodsOut::create([
            'item_id' => $validated['item_id'],
            'user_id' => $validated['user_id'],
            'quantity' => $validated['quantity'],
            'invoice_number' => $validated['invoice_number'],
            'date_out' => $validated['date_out'],
            'customer_id' => $validated['customer_id']
        ]);

        $newStock = GoodsOut::calculateCurrentStock($item->id);

        Log::info('Goods out transaction created', [
            'goods_out_id' => $goodsOut->id,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_code' => $item->code,
            'quantity_removed' => $validated['quantity'],
            'new_stock' => $newStock,
            'customer' => Customer::find($validated['customer_id'])->name ?? 'Unknown',
            'user_id' => Auth::id()
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => __("saved successfully"),
            'data' => [
                'goods_out_id' => $goodsOut->id,
                'item_name' => $item->name,
                'item_code' => $item->code,
                'invoice_number' => $goodsOut->invoice_number,
                'quantity_removed' => $validated['quantity'],
                'new_stock' => $newStock,
                'unit' => $item->unit->name ?? 'Unit'
            ]
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        Log::error('Validation failed', ['errors' => $e->errors()]);
        return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error saving goods out: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Error saving data: ' . $e->getMessage()], 500);
    }
}

    public function detail(Request $request): JsonResponse
    {
        try {
            $id = $request->id;
            $data = GoodsOut::with(['customer', 'user', 'item.category', 'item.unit'])->find($id);

            if (!$data) {
                return response()->json(['success' => false, 'message' => 'Data not found'], 404);
            }

            $item = $data->item;
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }

            $currentStock = GoodsOut::calculateCurrentStock($item->id);

            $responseData = [
                'id' => $data->id,
                'user_id' => $data->user_id,
                'customer_id' => $data->customer_id,
                'date_out' => $data->date_out,
                'quantity' => $data->quantity,
                'invoice_number' => $data->invoice_number, // Pastikan invoice_number dikembalikan
                'item_id' => $data->item_id,
                'kode_barang' => $item->code,
                'nama_barang' => $item->name,
                'satuan_barang' => $item->unit->name ?? 'Unit',
                'jenis_barang' => $item->category->name ?? 'Category',
                'current_stock' => $currentStock,
                'customer_name' => $data->customer->name ?? 'Unknown',
                'user_name' => $data->user->name ?? 'Unknown',
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at
            ];

            return response()->json(['success' => true, 'data' => $responseData], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching goods out detail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'id' => 'required|exists:goods_out,id',
                'item_id' => 'required|exists:items,id',
                'user_id' => 'required|exists:users,id',
                'quantity' => 'required|numeric|min:1|max:999999',
                'date_out' => 'required|date|before_or_equal:' . now()->format('Y-m-d'),
                'customer_id' => 'required|exists:customers,id',
                'invoice_number' => 'required|string|max:255'
            ]);

            $goodsOut = GoodsOut::find($validated['id']);
            if (!$goodsOut) {
                return response()->json(['success' => false, 'message' => __("data not found")], 404);
            }

            $oldItemId = $goodsOut->item_id;
            $oldQuantity = $goodsOut->quantity;

            // Jika invoice_number tidak disediakan, gunakan yang lama
            if (empty($validated['invoice_number'])) {
                $validated['invoice_number'] = $goodsOut->invoice_number;
            }

            $tempAvailableStock = GoodsOut::calculateCurrentStock($validated['item_id']) + 
                                 ($oldItemId == $validated['item_id'] ? $oldQuantity : 0);

            if ($validated['quantity'] > $tempAvailableStock) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock. Available: {$tempAvailableStock}, Requested: {$validated['quantity']}"
                ], 400);
            }

            $goodsOut->user_id = $validated['user_id'];
            $goodsOut->customer_id = $validated['customer_id'];
            $goodsOut->date_out = $validated['date_out'];
            $goodsOut->quantity = $validated['quantity'];
            $goodsOut->item_id = $validated['item_id'];
            $goodsOut->invoice_number = $validated['invoice_number'];

            $status = $goodsOut->save();

            if (!$status) {
                return response()->json(['success' => false, 'message' => __("data failed to change")], 400);
            }

            $newStock = GoodsOut::calculateCurrentStock($goodsOut->item_id);
            $item = Item::find($goodsOut->item_id);

            Log::info('Goods out transaction updated', [
                'goods_out_id' => $goodsOut->id,
                'old_item_id' => $oldItemId,
                'new_item_id' => $goodsOut->item_id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $goodsOut->quantity,
                'item_code' => $item->code,
                'new_stock' => $newStock,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __("data changed successfully"),
                'data' => [
                    'goods_out_id' => $goodsOut->id,
                    'item_name' => $item->name,
                    'item_code' => $item->code,
                    'invoice_number' => $goodsOut->invoice_number, // Pastikan invoice_number dikembalikan
                    'new_quantity' => $goodsOut->quantity,
                    'new_stock' => $newStock,
                    'unit' => $item->unit->name ?? 'Unit'
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating goods out: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating data: ' . $e->getMessage()], 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $id = $request->id;
            $goodsOut = GoodsOut::with('item')->find($id);

            if (!$goodsOut) {
                return response()->json(['success' => false, 'message' => __("data not found")], 404);
            }

            $itemId = $goodsOut->item_id;
            $itemName = $goodsOut->item->name ?? 'Unknown';
            $itemCode = $goodsOut->item->code ?? 'Unknown';
            $quantity = $goodsOut->quantity;

            $status = $goodsOut->delete();

            if (!$status) {
                return response()->json(['success' => false, 'message' => __("data failed to delete")], 400);
            }

            $newStock = GoodsOut::calculateCurrentStock($itemId);

            Log::info('Goods out transaction deleted', [
                'goods_out_id' => $id,
                'item_id' => $itemId,
                'item_name' => $itemName,
                'item_code' => $itemCode,
                'quantity_restored' => $quantity,
                'new_stock' => $newStock,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __("data deleted successfully"),
                'data' => [
                    'item_name' => $itemName,
                    'item_code' => $itemCode,
                    'quantity_restored' => $quantity,
                    'new_stock' => $newStock
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting goods out: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error deleting data: ' . $e->getMessage()], 500);
        }
    }

    public function getCurrentStock(Request $request): JsonResponse
    {
        try {
            $itemId = $request->item_id;
            $dateOut = $request->date_out ?? now()->format('Y-m-d');

            if (!$itemId) {
                return response()->json(['success' => false, 'message' => 'Item ID is required'], 400);
            }

            $item = Item::with(['unit', 'category'])->find($itemId);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }

            $currentStock = GoodsOut::calculateCurrentStock($itemId);

            return response()->json([
                'success' => true,
                'item_id' => $itemId,
                'item_name' => $item->name,
                'item_code' => $item->code,
                'date_out' => $dateOut,
                'available_stock' => $currentStock,
                'unit' => $item->unit->name ?? 'Unit',
                'category' => $item->category->name ?? 'Category',
                'status' => $currentStock <= 0 ? 'out_of_stock' : ($currentStock <= 3 ? 'low_stock' : 'normal'),
                'last_updated' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting current stock: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error calculating stock: ' . $e->getMessage()], 500);
        }
    }

    public function checkStockAvailability(Request $request): JsonResponse
    {
        try {
            $itemId = $request->item_id;
            $quantity = $request->quantity;
            $dateOut = $request->date_out ?? now()->format('Y-m-d');

            if (!$itemId || !$quantity) {
                return response()->json(['success' => false, 'message' => 'Item ID and quantity are required'], 400);
            }

            $stockCheck = GoodsOut::validateStockAvailability($itemId, $quantity);
            $item = Item::find($itemId);

            return response()->json([
                'success' => true,
                'item_name' => $item->name ?? 'Unknown',
                'item_code' => $item->code ?? 'Unknown',
                'unit' => $item->unit->name ?? 'Unit',
                'validation' => $stockCheck
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking stock availability: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error checking stock availability: ' . $e->getMessage()], 500);
        }
    }

    public function getAvailableItems(Request $request): JsonResponse
    {
        try {
            $items = Item::with(['unit', 'category', 'brand'])
                ->where('active', 'true')
                ->orderBy('name')
                ->get();

            $availableItems = [];
            foreach ($items as $item) {
                $currentStock = GoodsOut::calculateCurrentStock($item->id);
                if ($currentStock > 0) {
                    $availableItems[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'code' => $item->code,
                        'current_stock' => $currentStock,
                        'unit' => $item->unit->name ?? 'Unit',
                        'category' => $item->category->name ?? 'Category',
                        'brand' => $item->brand->name ?? 'Brand',
                        'status' => $currentStock <= 3 ? 'low_stock' : 'available'
                    ];
                }
            }

            $total_available = count($availableItems);

            return response()->json([
                'success' => true,
                'items' => $availableItems,
                'total_available' => $total_available
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting available items: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error getting available items: ' . $e->getMessage()], 500);
        }
    }
}