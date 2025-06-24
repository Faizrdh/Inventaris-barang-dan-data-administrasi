<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\GoodsIn;
use App\Models\GoodsOut;
use App\Models\Item;
use App\Services\StockService;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportStockController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(): View
    {
        // Pastikan hanya admin yang bisa akses
        if(Auth::user()->role->name !== 'admin' && Auth::user()->role_id !== 1) {
            abort(403, 'Unauthorized access');
        }
        
        // Get stock summary for dashboard
        $stockSummary = $this->stockService->getStockSummary();
        
        return view('admin.master.laporan.stok', compact('stockSummary'));
    }

    public function list(Request $request): JsonResponse
    {
        try {
            // Pastikan hanya admin yang bisa akses
            if(Auth::user()->role->name !== 'admin' && Auth::user()->role_id !== 1) {
                return response()->json([
                    'error' => 'Unauthorized access'
                ], 403);
            }

            if($request->ajax()) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                
                // Ambil semua item yang aktif dengan real-time stock
                $items = Item::with(['unit', 'category'])->where('active', 'true')->get();
                
                $stockData = [];
                
                foreach($items as $item) {
                    // Use StockService untuk perhitungan yang akurat
                    $stockInfo = $this->stockService->calculateStockForPeriod(
                        $item->id, 
                        $startDate, 
                        $endDate
                    );
                    
                    // Get real-time current stock
                    $currentStock = $this->stockService->calculateCurrentStock($item->id);
                    
                    $stockData[] = [
                        'id' => $item->id,
                        'kode_barang' => $item->code,
                        'nama_barang' => $item->name,
                        'category' => $item->category->name ?? '-',
                        'stok_awal' => $stockInfo['initial_stock'],
                        'jumlah_masuk' => $stockInfo['incoming'],
                        'jumlah_keluar' => $stockInfo['outgoing'],
                        'total' => $stockInfo['final_stock'],
                        'current_stock' => $currentStock,
                        'unit' => $item->unit->name ?? '-',
                        'status' => $this->getStockStatus($currentStock),
                        'last_updated' => now()->format('Y-m-d H:i:s')
                    ];
                }
                
                return DataTables::of($stockData)
                    ->addColumn('stok_awal_formatted', function($data) {
                        return number_format($data['stok_awal']) . ' ' . $data['unit'];
                    })
                    ->addColumn('jumlah_masuk_formatted', function($data) {
                        return '<span class="text-success">' . number_format($data['jumlah_masuk']) . ' ' . $data['unit'] . '</span>';
                    })
                    ->addColumn('jumlah_keluar_formatted', function($data) {
                        return '<span class="text-warning">' . number_format($data['jumlah_keluar']) . ' ' . $data['unit'] . '</span>';
                    })
                    ->addColumn('total_formatted', function($data) {
                        $class = $data['total'] <= 0 ? 'text-danger' : ($data['total'] <= 3 ? 'text-warning' : 'text-success');
                        $icon = $data['total'] <= 0 ? 'fas fa-times-circle' : ($data['total'] <= 3 ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle');
                        
                        return '<span class="' . $class . '">
                                    <i class="' . $icon . ' mr-1"></i>' . 
                                    number_format($data['total']) . ' ' . $data['unit'] . 
                                '</span>';
                    })
                    ->addColumn('current_stock_formatted', function($data) {
                        $class = $data['current_stock'] <= 0 ? 'text-danger' : ($data['current_stock'] <= 3 ? 'text-warning' : 'text-success');
                        $badge = $data['current_stock'] <= 0 ? 'badge-danger' : ($data['current_stock'] <= 3 ? 'badge-warning' : 'badge-success');
                        
                        return '<span class="badge ' . $badge . '">' . number_format($data['current_stock']) . ' ' . $data['unit'] . '</span>';
                    })
                    ->addColumn('status_badge', function($data) {
                        $status = $data['status'];
                        $badges = [
                            'out_of_stock' => '<span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Habis</span>',
                            'low_stock' => '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Rendah</span>',
                            'normal' => '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Normal</span>',
                            'high' => '<span class="badge badge-info"><i class="fas fa-arrow-up mr-1"></i>Tinggi</span>'
                        ];
                        
                        return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
                    })
                    ->addColumn('actions', function($data) {
                        return '
                            <button class="btn btn-sm btn-info view-movements" data-id="' . $data['id'] . '" title="View Movements">
                                <i class="fas fa-history"></i>
                            </button>
                            <button class="btn btn-sm btn-primary refresh-stock" data-id="' . $data['id'] . '" title="Refresh Stock">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        ';
                    })
                    ->rawColumns(['jumlah_masuk_formatted', 'jumlah_keluar_formatted', 'total_formatted', 'current_stock_formatted', 'status_badge', 'actions'])
                    ->make(true);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'This endpoint requires AJAX request'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Error in stock report list: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error generating stock report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock movements for specific item
     */
    public function getMovements(Request $request): JsonResponse
    {
        try {
            $itemId = $request->item_id;
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            if (!$itemId) {
                return response()->json([
                    'error' => 'Item ID is required'
                ], 400);
            }

            $item = Item::find($itemId);
            if (!$item) {
                return response()->json([
                    'error' => 'Item not found'
                ], 404);
            }

            $movements = $this->stockService->getStockMovements($itemId, $startDate, $endDate);
            $currentStock = $this->stockService->calculateCurrentStock($itemId);

            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'current_stock' => $currentStock,
                    'unit' => $item->unit->name ?? ''
                ],
                'movements' => $movements,
                'total_movements' => count($movements)
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting stock movements: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error getting stock movements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh stock for specific item
     */
    public function refreshStock(Request $request): JsonResponse
    {
        try {
            $itemId = $request->item_id;

            if (!$itemId) {
                return response()->json([
                    'error' => 'Item ID is required'
                ], 400);
            }

            $item = Item::find($itemId);
            if (!$item) {
                return response()->json([
                    'error' => 'Item not found'
                ], 404);
            }

            // Recalculate stock
            $oldQuantity = $item->quantity;
            $newQuantity = $this->stockService->calculateCurrentStock($itemId);
            
            $item->quantity = $newQuantity;
            $item->active = $newQuantity > 0 ? 'true' : 'false';
            $item->save();

            Log::info('Stock manually refreshed', [
                'item_id' => $itemId,
                'item_name' => $item->name,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'difference' => $newQuantity - $oldQuantity
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error refreshing stock: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error refreshing stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate all stock
     */
    public function recalculateAllStock(): JsonResponse
    {
        try {
            // Check admin permission
            if(Auth::user()->role->name !== 'admin' && Auth::user()->role_id !== 1) {
                return response()->json([
                    'error' => 'Unauthorized access'
                ], 403);
            }

            $result = $this->stockService->recalculateAllStock();

            Log::info('All stock recalculated', [
                'updated' => $result['updated'],
                'errors' => $result['errors'],
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Stock recalculated for {$result['updated']} items",
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error recalculating all stock: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error recalculating stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time stock summary
     */
    public function getSummary(): JsonResponse
    {
        try {
            $summary = $this->stockService->getStockSummary();
            $lowStockItems = $this->stockService->getLowStockItems();
            $outOfStockItems = $this->stockService->getOutOfStockItems();

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'low_stock_items' => array_slice($lowStockItems, 0, 10), // Limit to 10
                'out_of_stock_items' => array_slice($outOfStockItems, 0, 10), // Limit to 10
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting stock summary: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error getting stock summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock status based on quantity
     */
    private function getStockStatus($quantity): string
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        } elseif ($quantity <= 3) {
            return 'low_stock';
        } elseif ($quantity <= 10) {
            return 'normal';
        } else {
            return 'high';
        }
    }

    /**
     * Export stock report
     */
    public function export(Request $request)
    {
        // TODO: Implementasi export PDF/Excel dengan data real-time
        // Bisa menggunakan library seperti maatwebsite/excel atau dompdf
        
        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $format = $request->format ?? 'excel'; // excel, pdf, csv
            
            // Get stock data
            $items = Item::with(['unit', 'category'])->where('active', 'true')->get();
            $stockData = [];
            
            foreach($items as $item) {
                $stockInfo = $this->stockService->calculateStockForPeriod(
                    $item->id, 
                    $startDate, 
                    $endDate
                );
                
                $stockData[] = [
                    'kode_barang' => $item->code,
                    'nama_barang' => $item->name,
                    'kategori' => $item->category->name ?? '-',
                    'stok_awal' => $stockInfo['initial_stock'],
                    'barang_masuk' => $stockInfo['incoming'],
                    'barang_keluar' => $stockInfo['outgoing'],
                    'stok_akhir' => $stockInfo['final_stock'],
                    'satuan' => $item->unit->name ?? '-',
                    'status' => $this->getStockStatus($stockInfo['final_stock'])
                ];
            }
            
            // Return response berdasarkan format
            return response()->json([
                'success' => true,
                'message' => 'Export functionality will be implemented',
                'data_count' => count($stockData),
                'format' => $format
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error exporting stock report: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error exporting report: ' . $e->getMessage()
            ], 500);
        }
    }
}