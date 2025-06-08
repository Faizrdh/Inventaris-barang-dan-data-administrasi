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
    public function index():View
    {
        $jenisbarang = Category::all();
        $satuan = Unit::all();
        $merk = Brand::all();
        return view('admin.master.barang.index',compact('jenisbarang','satuan','merk'));
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $items = Item::with('category', 'unit', 'brand')->latest();

            if ($request->ajax()) {
                return DataTables::of($items)
                    ->addColumn('img', function ($data) {
                        if (empty($data->image)) {
                            return "<img src='" . asset('default.png') . "' style='width:100%;max-width:80px;height:80px;object-fit:cover;padding:1px;border:1px solid #ddd;border-radius:4px'/>";
                        }
                        return "<img src='" . asset('storage/barang/' . $data->image) . "' style='width:100%;max-width:80px;height:80px;object-fit:cover;padding:1px;border:1px solid #ddd;border-radius:4px'/>";
                    })
                    ->addColumn('category_name', function ($data) {
                        return $data->category->name ?? '-';
                    })
                    ->addColumn('unit_name', function ($data) {
                        return $data->unit->name ?? '-';
                    })
                    ->addColumn('brand_name', function ($data) {
                        return $data->brand->name ?? '-';
                    })
                    ->addColumn('quantity_formatted', function ($data) {
                        // Hitung stock real-time berdasarkan transaksi
                        $currentStock = $this->calculateCurrentStock($data->id);
                        $unit = $data->unit->name ?? 'Unit';
                        $class = $currentStock <= 0 ? 'text-danger' : ($currentStock <= 3 ? 'text-warning' : 'text-success');
                        
                        return '<span class="' . $class . '">' . number_format($currentStock) . ' ' . $unit . '</span>';
                    })
                    // HAPUS kolom price karena sudah tidak digunakan
                    ->addColumn('tindakan', function ($data) {
                        // Untuk modal pilih barang
                        if (request()->has('for_modal')) {
                            $button = "<button class='pilih-data-barang btn btn-success btn-sm' data-id='" . $data->id . "' title='Pilih'>";
                            $button .= "<i class='fas fa-check'></i> Pilih</button>";
                            return $button;
                        }
                        
                        // Untuk halaman master barang
                        $button = "<button class='ubah btn btn-success m-1' id='" . $data->id . "'><i class='fas fa-pen m-1'></i>" . __("edit") . "</button>";
                        $button .= "<button class='hapus btn btn-danger m-1' id='" . $data->id . "'><i class='fas fa-trash m-1'></i>" . __("delete") . "</button>";
                        return $button;
                    })
                    ->rawColumns(['img', 'quantity_formatted', 'tindakan'])
                    ->make(true);
            }

            return response()->json([
                "message" => __("Invalid request type"),
                "data" => $items->get()
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            Log::error('Error in item list: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error loading items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        try {
            // Validasi input - HAPUS validasi price
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|unique:items,code',
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'required|exists:brands,id',
                'unit_id' => 'required|exists:units,id',
                'quantity' => 'nullable|numeric|min:0', // Buat nullable karena akan dihitung dari transaksi
                'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048'
            ]);

            $data = [
                'name' => $request->name,
                'code' => $request->code,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'unit_id' => $request->unit_id,
                'quantity' => $request->quantity ?? 0, // Default 0, akan diupdate dari transaksi
                'active' => 'true'
                // HAPUS: 'price' - sudah tidak diperlukan
            ];

            // Handle upload image
            if ($request->file('image') != null) {
                $image = $request->file('image');
                $image->storeAs('public/barang/', $image->hashName());
                $data['image'] = $image->hashName();
            }

            Item::create($data);

            return response()->json([
                "message" => __("saved successfully")
            ])->setStatusCode(200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error saving item: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error saving data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request): JsonResponse
    {
        try {
            $id = $request->id;
            $data = Item::with('category','unit','brand')->find($id);
            
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // Hitung current stock
            $currentStock = $this->calculateCurrentStock($data->id);

            $responseData = [
                'id' => $data->id,
                'code' => $data->code,
                'name' => $data->name,
                'category_id' => $data->category_id,
                'unit_id' => $data->unit_id,
                'brand_id' => $data->brand_id,
                'quantity' => $data->quantity,
                'current_stock' => $currentStock,
                'image' => $data->image,
                'active' => $data->active,
                'category_name' => $data->category->name ?? '-',
                'unit_name' => $data->unit->name ?? '-',
                'brand_name' => $data->brand->name ?? '-',
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at
                // HAPUS: 'price' - sudah tidak diperlukan
            ];
            
            return response()->json([
                "success" => true,
                "data" => $responseData
            ])->setStatusCode(200);

        } catch (\Exception $e) {
            Log::error('Error fetching item detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detailByCode(Request $request): JsonResponse
    {
        try {
            $code = $request->code;
            
            if (!$code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code is required'
                ], 400);
            }

            $data = Item::with('category','unit','brand')
                ->where("code", $code)
                ->where('active', 'true')
                ->first();
            
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // Hitung current stock
            $currentStock = $this->calculateCurrentStock($data->id);

            $responseData = [
                'id' => $data->id,
                'code' => $data->code,
                'name' => $data->name,
                'category_id' => $data->category_id,
                'unit_id' => $data->unit_id,
                'brand_id' => $data->brand_id,
                'quantity' => $data->quantity,
                'current_stock' => $currentStock,
                'image' => $data->image,
                'active' => $data->active,
                'category_name' => $data->category->name ?? '-',
                'unit_name' => $data->unit->name ?? '-',
                'brand_name' => $data->brand->name ?? '-'
                // HAPUS: 'price' - sudah tidak diperlukan
            ];
            
            return response()->json([
                "success" => true,
                "data" => $responseData
            ])->setStatusCode(200);

        } catch (\Exception $e) {
            Log::error('Error fetching item by code: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            // Validasi input - HAPUS validasi price
            $request->validate([
                'id' => 'required|exists:items,id',
                'name' => 'required|string|max:255',
                'code' => 'required|string|unique:items,code,' . $request->id,
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'required|exists:brands,id',
                'unit_id' => 'required|exists:units,id',
                'quantity' => 'nullable|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048'
            ]);

            $id = $request->id;
            $item = Item::find($id);
            
            if (!$item) {
                return response()->json([
                    "message" => __("data not found")
                ])->setStatusCode(404);
            }

            $data = [
                'name' => $request->name,
                'code' => $request->code,
                'quantity' => $request->quantity ?? $item->quantity,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'unit_id' => $request->unit_id
                // HAPUS: 'price' - sudah tidak diperlukan
            ];

            // Handle upload image
            if ($request->file('image') != null) {
                // Hapus gambar lama jika ada
                if ($item->image) {
                    Storage::delete('public/barang/' . $item->image);
                }
                
                $image = $request->file('image');
                $image->storeAs('public/barang/', $image->hashName());
                $data['image'] = $image->hashName();
            }

            $item->fill($data);
            $item->save();

            return response()->json([
                "message" => __("data changed successfully")
            ])->setStatusCode(200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating item: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            $id = $request->id;
            $item = Item::find($id);
            
            if (!$item) {
                return response()->json([
                    "message" => __("data not found")
                ])->setStatusCode(404);
            }

            // Check jika item masih digunakan dalam transaksi
            $hasTransactions = GoodsIn::where('item_id', $id)->exists() || 
                              (class_exists('App\Models\GoodsOut') && GoodsOut::where('item_id', $id)->exists());
            
            if ($hasTransactions) {
                return response()->json([
                    "message" => "Cannot delete item. Item is still used in transactions."
                ])->setStatusCode(400);
            }

            // Hapus gambar jika ada
            if ($item->image) {
                Storage::delete('public/barang/' . $item->image);
            }
            
            $status = $item->delete();
            
            if (!$status) {
                return response()->json([
                    "message" => __("data failed to delete")
                ])->setStatusCode(400);
            }

            return response()->json([
                "message" => __("data deleted successfully")
            ])->setStatusCode(200);

        } catch (\Exception $e) {
            Log::error('Error deleting item: ' . $e->getMessage());
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
                $totalOut = GoodsOut::where('item_id', $itemId)->sum('quantity') ?? 0;
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
     * Get stock movements for item (optional method untuk debugging)
     */
    public function getStockMovements(Request $request): JsonResponse
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

            $movements = [];
            
            // Ambil transaksi masuk
            $goodsIn = GoodsIn::with('supplier', 'user')
                ->where('item_id', $itemId)
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

            // Jika ada transaksi keluar
            if (class_exists('App\Models\GoodsOut')) {
                $goodsOut = GoodsOut::with('user')
                    ->where('item_id', $itemId)
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
            usort($movements, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

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
                ],
                'movements' => $movements
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting stock movements: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error getting stock movements: ' . $e->getMessage()
            ], 500);
        }
    }
}