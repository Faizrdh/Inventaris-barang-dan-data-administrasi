<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\GoodsIn;
use App\Models\GoodsOut;
use App\Models\Item;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportStockController extends Controller
{
    public function index(): View
    {
        // Hapus pengecekan role admin - semua user dapat mengakses
        return view('admin.master.laporan.stok');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            // Hapus pengecekan role admin - semua user dapat mengakses

            if($request->ajax()) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                
                // Ambil semua item yang aktif
                $items = Item::with(['unit', 'category'])->where('active', 'true')->get();
                
                $stockData = [];
                
                foreach($items as $item) {
                    // Perhitungan stock sederhana
                    $stockInfo = $this->calculateStockForPeriod($item->id, $startDate, $endDate);
                    
                    $stockData[] = [
                        'id' => $item->id,
                        'kode_barang' => $item->code,
                        'nama_barang' => $item->name,
                        'category' => $item->category->name ?? '-',
                        'stok_awal' => $stockInfo['initial_stock'],
                        'jumlah_masuk' => $stockInfo['incoming'],
                        'jumlah_keluar' => $stockInfo['outgoing'],
                        'total' => $stockInfo['final_stock'],
                        'unit' => $item->unit->name ?? '-',
                        'status' => $this->getStockStatus($stockInfo['final_stock']),
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
                    ->rawColumns(['jumlah_masuk_formatted', 'jumlah_keluar_formatted', 'total_formatted', 'status_badge'])
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
     * Calculate stock for specific period - simplified version
     */
    private function calculateStockForPeriod($itemId, $startDate = null, $endDate = null)
    {
        try {
            $item = Item::find($itemId);
            if (!$item) {
                return [
                    'initial_stock' => 0,
                    'incoming' => 0,
                    'outgoing' => 0,
                    'final_stock' => 0
                ];
            }

            // current stock
            $currentStock = $item->quantity ?? 0;

            $incomingQuery = GoodsIn::where('item_id', $itemId);
            if ($startDate && $endDate) {
                $incomingQuery->whereBetween('date_received', [$startDate, $endDate]);
            }
            $incoming = $incomingQuery->sum('quantity') ?? 0;

            $outgoingQuery = GoodsOut::where('item_id', $itemId);
            if ($startDate && $endDate) {
                $outgoingQuery->whereBetween('date_out', [$startDate, $endDate]);
            }
            $outgoing = $outgoingQuery->sum('quantity') ?? 0;

            // hitung jumlah stok
            $initialStock = $startDate && $endDate ? 
                max(0, $currentStock - $incoming + $outgoing) : 
                max(0, $currentStock - $incoming + $outgoing);

            // Final stock calculation
            $finalStock = $initialStock + $incoming - $outgoing;

            return [
                'initial_stock' => $initialStock,
                'incoming' => $incoming,
                'outgoing' => $outgoing,
                'final_stock' => max(0, $finalStock)
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating stock for item ' . $itemId . ': ' . $e->getMessage());
            return [
                'initial_stock' => 0,
                'incoming' => 0,
                'outgoing' => 0,
                'final_stock' => 0
            ];
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
     * Grafik method - placeholder
     */
    public function grafik()
    {
        // Placeholder untuk grafik method jika diperlukan
        return response()->json([
            'success' => true,
            'message' => 'Grafik functionality will be implemented'
        ]);
    }
}