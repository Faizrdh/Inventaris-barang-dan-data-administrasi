<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\GoodsIn;
use App\Models\GoodsOut;

class ItemController extends Controller
{
    public function index(): View
    {
        $jenisbarang = Category::all();
        $satuan = Unit::all();
        $merk = Brand::all();
        return view('admin.master.barang.index', compact('jenisbarang', 'satuan', 'merk'));
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $items = Item::with('category', 'unit', 'brand')->latest();

            if ($request->ajax()) {
                return DataTables::of($items)
                    ->addColumn('img', fn($data) => $this->getImageHtml($data))
                    ->addColumn('category_name', fn($data) => $data->category->name ?? '-')
                    ->addColumn('unit_name', fn($data) => $data->unit->name ?? '-')
                    ->addColumn('brand_name', fn($data) => $data->brand->name ?? '-')
                    ->addColumn('quantity_formatted', fn($data) => $this->getFormattedStock($data))
                    ->addColumn('tindakan', fn($data) => $this->getActionButtons($data, $request))
                    ->rawColumns(['img', 'quantity_formatted', 'tindakan'])
                    ->make(true);
            }

            return response()->json([
                "message" => __("Invalid request type"),
                "data" => $items->get()
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error in item list: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading items: ' . $e->getMessage()], 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        try {
            $this->validateItemRequest($request);

            $data = [
                'name' => $request->name,
                'code' => $request->code,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'unit_id' => $request->unit_id,
                'quantity' => $request->quantity ?? 0,
                'active' => 'true'
            ];

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'));
            }

            Item::create($data);

            return response()->json(["message" => __("saved successfully")], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error saving item: ' . $e->getMessage());
            return response()->json(['message' => 'Error saving data: ' . $e->getMessage()], 500);
        }
    }

    public function detail(Request $request): JsonResponse
    {
        try {
            $item = Item::with('category', 'unit', 'brand')->findOrFail($request->id);
            
            $responseData = [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'category_id' => $item->category_id,
                'unit_id' => $item->unit_id,
                'brand_id' => $item->brand_id,
                'quantity' => $item->quantity,
                'current_stock' => $this->calculateCurrentStock($item->id),
                'image' => $item->image,
                'active' => $item->active,
                'category_name' => $item->category->name ?? '-',
                'unit_name' => $item->unit->name ?? '-',
                'brand_name' => $item->brand->name ?? '-',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            ];
            
            return response()->json(["success" => true, "data" => $responseData], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching item detail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()], 500);
        }
    }

    

    public function detailByCode(Request $request): JsonResponse
    {
        try {
            $request->validate(['code' => 'required']);
            
            $item = Item::with('category', 'unit', 'brand')
                ->where("code", $request->code)
                ->where('active', 'true')
                ->firstOrFail();

            $responseData = [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'category_id' => $item->category_id,
                'unit_id' => $item->unit_id,
                'brand_id' => $item->brand_id,
                'quantity' => $item->quantity,
                'current_stock' => $this->calculateCurrentStock($item->id),
                'image' => $item->image,
                'active' => $item->active,
                'category_name' => $item->category->name ?? '-',
                'unit_name' => $item->unit->name ?? '-',
                'brand_name' => $item->brand->name ?? '-'
            ];
            
            return response()->json(["success" => true, "data" => $responseData], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching item by code: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $this->validateItemRequest($request, $request->id);
            
            $item = Item::findOrFail($request->id);

            $data = [
                'name' => $request->name,
                'code' => $request->code,
                'quantity' => $request->quantity ?? $item->quantity,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'unit_id' => $request->unit_id
            ];

            if ($request->hasFile('image')) {
                $this->deleteImage($item->image);
                $data['image'] = $this->uploadImage($request->file('image'));
            }

            $item->update($data);

            return response()->json(["message" => __("data changed successfully")], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating item: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating data: ' . $e->getMessage()], 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            $item = Item::findOrFail($request->id);

            // Check if item is used in transactions
            if ($this->hasTransactions($item->id)) {
                return response()->json([
                    "message" => "Data ini tidak dapat dihapus dikarenakan digunakan pada data yang lain"
                ], 400);
            }

            $item->delete(); // Soft delete

            return response()->json(["message" => __("data deleted successfully")], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting item: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting data: ' . $e->getMessage()], 500);
        }
    }

    public function getStockMovements(Request $request): JsonResponse
    {
        try {
            $item = Item::with(['unit', 'category'])->findOrFail($request->item_id);
            $movements = $this->getItemStockMovements($item);

            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'current_stock' => $this->calculateCurrentStock($item->id),
                    'unit' => $item->unit->name ?? 'Unit',
                    'category' => $item->category->name ?? 'Category'
                ],
                'movements' => $movements
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting stock movements: ' . $e->getMessage());
            return response()->json(['error' => 'Error getting stock movements: ' . $e->getMessage()], 500);
        }
    }

    // Helper Methods
    private function validateItemRequest(Request $request, $id = null): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048'
        ];

        if ($id) {
            $rules['code'] = 'required|string|unique:items,code,' . $id . ',id,deleted_at,NULL';
        } else {
            $rules['code'] = 'required|string|unique:items,code,NULL,id,deleted_at,NULL';
        }

        $request->validate($rules);
    }

    private function getImageHtml($item): string
    {
        $src = empty($item->image) ? asset('default.png') : asset('storage/barang/' . $item->image);
        return "<img src='{$src}' style='width:100%;max-width:80px;height:80px;object-fit:cover;padding:1px;border:1px solid #ddd;border-radius:4px'/>";
    }

    private function getFormattedStock($item): string
    {
        $currentStock = $this->calculateCurrentStock($item->id);
        $unit = $item->unit->name ?? 'Unit';
        $class = $currentStock <= 0 ? 'text-danger' : ($currentStock <= 3 ? 'text-warning' : 'text-success');
        
        return '<span class="' . $class . '">' . number_format($currentStock) . ' ' . $unit . '</span>';
    }

    private function getActionButtons($item, $request): string
    {
        if ($request->has('for_modal')) {
            return "<button class='pilih-data-barang btn btn-success btn-sm' data-id='{$item->id}' title='Pilih'>
                        <i class='fas fa-check'></i> Pilih
                    </button>";
        }
        
        return "<button class='ubah btn btn-success m-1' id='{$item->id}'>
                    <i class='fas fa-pen m-1'></i>" . __("edit") . "
                </button>
                <button class='hapus btn btn-danger m-1' id='{$item->id}' data-name='{$item->name}'>
                    <i class='fas fa-trash m-1'></i>" . __("delete") . "
                </button>";
    }

    private function uploadImage($file): string
    {
        $file->storeAs('public/barang/', $file->hashName());
        return $file->hashName();
    }

    private function deleteImage(?string $imageName): void
    {
        if ($imageName && Storage::exists('public/barang/' . $imageName)) {
            Storage::delete('public/barang/' . $imageName);
        }
    }

   private function calculateCurrentStock($itemId): int
{
    try {
        $item = Item::find($itemId);
        if (!$item) return 0;
        
        // Ambil stok awal dari field quantity
        $initialStock = $item->quantity ?? 0;
        
        // Hitung total transaksi masuk dan keluar
        $totalIn = GoodsIn::where('item_id', $itemId)->sum('quantity') ?? 0;
        $totalOut = 0;
        if (class_exists('App\Models\GoodsOut')) {
            $totalOut = GoodsOut::where('item_id', $itemId)->sum('quantity') ?? 0;
        }
        
        // Stok = Stok Awal + Total Masuk - Total Keluar
        return $initialStock + $totalIn - $totalOut;
        
    } catch (\Exception $e) {
        Log::error('Error calculating stock for item ' . $itemId . ': ' . $e->getMessage());
        $item = Item::find($itemId);
        return $item ? $item->quantity : 0;
    }
}

    private function hasTransactions($itemId): bool
    {
        return GoodsIn::where('item_id', $itemId)->exists() || 
               (class_exists('App\Models\GoodsOut') && GoodsOut::where('item_id', $itemId)->exists());
    }

    private function getItemStockMovements($item): array
    {
        $movements = [];
        
        // Get goods in transactions
        $goodsIn = GoodsIn::with('supplier', 'user')
            ->where('item_id', $item->id)
            ->orderBy('date_received', 'desc')
            ->get();
            
        foreach ($goodsIn as $gin) {
            $movements[] = [
                'type' => 'in',
                'date' => $gin->date_received,
                'quantity' => $gin->quantity,
                'reference' => $gin->invoice_number,
                'supplier' => $gin->supplier->name ?? '-',
                'user' => $gin->user->name ?? '-'
            ];
        }

        // Get goods out transactions
        if (class_exists('App\Models\GoodsOut')) {
            $goodsOut = GoodsOut::with('user')
                ->where('item_id', $item->id)
                ->orderBy('date_out', 'desc')
                ->get();
                
            foreach ($goodsOut as $gout) {
                $movements[] = [
                    'type' => 'out',
                    'date' => $gout->date_out,
                    'quantity' => $gout->quantity,
                    'reference' => $gout->reference ?? '-',
                    'destination' => $gout->destination ?? '-',
                    'user' => $gout->user->name ?? '-'
                ];
            }
        }

        // Sort by date descending
        usort($movements, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

        return $movements;
    }
}