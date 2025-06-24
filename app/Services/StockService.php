<?php

namespace App\Services;

use App\Models\Item;
use App\Models\GoodsIn;
use App\Models\GoodsOut;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockService
{
    /**
     * Recalculate and update stock for all items
     */
    public function recalculateAllStock(): array
    {
        $updated = 0;
        $errors = 0;
        $results = [];

        try {
            $items = Item::all();
            
            foreach ($items as $item) {
                try {
                    $oldQuantity = $item->quantity;
                    $newQuantity = $this->calculateCurrentStock($item->id);
                    
                    $item->quantity = $newQuantity;
                    $item->active = $newQuantity > 0 ? 'true' : 'false';
                    $item->save();
                    
                    $results[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'difference' => $newQuantity - $oldQuantity
                    ];
                    
                    $updated++;
                    
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error recalculating stock for item ' . $item->id . ': ' . $e->getMessage());
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error in recalculateAllStock: ' . $e->getMessage());
        }

        return [
            'updated' => $updated,
            'errors' => $errors,
            'results' => $results
        ];
    }

    /**
     * Calculate current stock for specific item
     */
    public function calculateCurrentStock($itemId): int
    {
        try {
            // Total goods in
            $totalIn = GoodsIn::where('item_id', $itemId)->sum('quantity');
            
            // Total goods out
            $totalOut = GoodsOut::where('item_id', $itemId)->sum('quantity');
            
            // Current stock
            $currentStock = $totalIn - $totalOut;
            
            return max(0, $currentStock);
            
        } catch (\Exception $e) {
            Log::error('Error calculating current stock for item ' . $itemId . ': ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate stock for specific date range
     */
    public function calculateStockForPeriod($itemId, $startDate = null, $endDate = null): array
    {
        try {
            // Initial stock (before start date)
            $initialStock = 0;
            if ($startDate) {
                $totalInBefore = GoodsIn::where('item_id', $itemId)
                    ->where('date_received', '<', $startDate)
                    ->sum('quantity');
                    
                $totalOutBefore = GoodsOut::where('item_id', $itemId)
                    ->where('date_out', '<', $startDate)
                    ->sum('quantity');
                    
                $initialStock = $totalInBefore - $totalOutBefore;
            }

            // Incoming in period
            $incomingQuery = GoodsIn::where('item_id', $itemId);
            if ($startDate) {
                $incomingQuery->where('date_received', '>=', $startDate);
            }
            if ($endDate) {
                $incomingQuery->where('date_received', '<=', $endDate);
            }
            $incoming = $incomingQuery->sum('quantity');

            // Outgoing in period
            $outgoingQuery = GoodsOut::where('item_id', $itemId);
            if ($startDate) {
                $outgoingQuery->where('date_out', '>=', $startDate);
            }
            if ($endDate) {
                $outgoingQuery->where('date_out', '<=', $endDate);
            }
            $outgoing = $outgoingQuery->sum('quantity');

            // Final stock
            $finalStock = $initialStock + $incoming - $outgoing;

            return [
                'initial_stock' => max(0, $initialStock),
                'incoming' => $incoming,
                'outgoing' => $outgoing,
                'final_stock' => max(0, $finalStock)
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating stock for period: ' . $e->getMessage());
            return [
                'initial_stock' => 0,
                'incoming' => 0,
                'outgoing' => 0,
                'final_stock' => 0
            ];
        }
    }

    /**
     * Get stock movements for specific item
     */
    public function getStockMovements($itemId, $startDate = null, $endDate = null): array
    {
        try {
            $movements = [];

            // Get goods in movements
            $goodsInQuery = GoodsIn::with(['supplier', 'user'])
                ->where('item_id', $itemId);
            
            if ($startDate) {
                $goodsInQuery->where('date_received', '>=', $startDate);
            }
            if ($endDate) {
                $goodsInQuery->where('date_received', '<=', $endDate);
            }
            
            $goodsInList = $goodsInQuery->orderBy('date_received', 'desc')->get();

            foreach ($goodsInList as $goodsIn) {
                $movements[] = [
                    'id' => $goodsIn->id,
                    'type' => 'in',
                    'date' => $goodsIn->date_received,
                    'quantity' => $goodsIn->quantity,
                    'reference' => $goodsIn->invoice_number,
                    'partner' => $goodsIn->supplier->name ?? 'Unknown Supplier',
                    'user' => $goodsIn->user->name ?? 'Unknown User',
                    'description' => 'Barang Masuk dari ' . ($goodsIn->supplier->name ?? 'Unknown')
                ];
            }

            // Get goods out movements
            $goodsOutQuery = GoodsOut::with(['customer', 'user'])
                ->where('item_id', $itemId);
            
            if ($startDate) {
                $goodsOutQuery->where('date_out', '>=', $startDate);
            }
            if ($endDate) {
                $goodsOutQuery->where('date_out', '<=', $endDate);
            }
            
            $goodsOutList = $goodsOutQuery->orderBy('date_out', 'desc')->get();

            foreach ($goodsOutList as $goodsOut) {
                $movements[] = [
                    'id' => $goodsOut->id,
                    'type' => 'out',
                    'date' => $goodsOut->date_out,
                    'quantity' => $goodsOut->quantity,
                    'reference' => $goodsOut->invoice_number,
                    'partner' => $goodsOut->customer->name ?? 'Unknown Customer',
                    'user' => $goodsOut->user->name ?? 'Unknown User',
                    'description' => 'Barang Keluar ke ' . ($goodsOut->customer->name ?? 'Unknown')
                ];
            }

            // Sort by date descending
            usort($movements, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return $movements;

        } catch (\Exception $e) {
            Log::error('Error getting stock movements: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems($threshold = 3): array
    {
        try {
            return Item::where('quantity', '>', 0)
                ->where('quantity', '<=', $threshold)
                ->where('active', 'true')
                ->with(['unit', 'category'])
                ->orderBy('quantity', 'asc')
                ->get()
                ->toArray();
                
        } catch (\Exception $e) {
            Log::error('Error getting low stock items: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get out of stock items
     */
    public function getOutOfStockItems(): array
    {
        try {
            return Item::where('quantity', '<=', 0)
                ->where('active', 'true')
                ->with(['unit', 'category'])
                ->orderBy('name', 'asc')
                ->get()
                ->toArray();
                
        } catch (\Exception $e) {
            Log::error('Error getting out of stock items: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get stock summary
     */
    public function getStockSummary(): array
    {
        try {
            $summary = DB::table('items')
                ->selectRaw('
                    COUNT(*) as total_items,
                    SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                    SUM(CASE WHEN quantity > 0 AND quantity <= 3 THEN 1 ELSE 0 END) as low_stock,
                    SUM(CASE WHEN quantity > 3 THEN 1 ELSE 0 END) as normal_stock,
                    SUM(quantity) as total_quantity
                ')
                ->where('active', 'true')
                ->first();

            return [
                'total_items' => $summary->total_items ?? 0,
                'out_of_stock' => $summary->out_of_stock ?? 0,
                'low_stock' => $summary->low_stock ?? 0,
                'normal_stock' => $summary->normal_stock ?? 0,
                'total_quantity' => $summary->total_quantity ?? 0
            ];

        } catch (\Exception $e) {
            Log::error('Error getting stock summary: ' . $e->getMessage());
            return [
                'total_items' => 0,
                'out_of_stock' => 0,
                'low_stock' => 0,
                'normal_stock' => 0,
                'total_quantity' => 0
            ];
        }
    }

    /**
     * Validate stock availability for transaction
     */
    public function validateStockAvailability($itemId, $quantity, $date = null): array
    {
        try {
            $date = $date ?? now()->format('Y-m-d');
            $availableStock = $this->calculateAvailableStockForDate($itemId, $date);
            
            $isAvailable = $availableStock >= $quantity;
            
            return [
                'is_available' => $isAvailable,
                'available_stock' => $availableStock,
                'requested_quantity' => $quantity,
                'shortage' => $isAvailable ? 0 : ($quantity - $availableStock),
                'message' => $isAvailable 
                    ? 'Stock sufficient' 
                    : "Insufficient stock. Available: {$availableStock}, Requested: {$quantity}"
            ];

        } catch (\Exception $e) {
            Log::error('Error validating stock availability: ' . $e->getMessage());
            return [
                'is_available' => false,
                'available_stock' => 0,
                'requested_quantity' => $quantity,
                'shortage' => $quantity,
                'message' => 'Error checking stock availability'
            ];
        }
    }

    /**
     * Calculate available stock for specific date
     */
    private function calculateAvailableStockForDate($itemId, $date): int
    {
        try {
            // Total goods in up to the date
            $totalIn = GoodsIn::where('item_id', $itemId)
                ->where('date_received', '<=', $date)
                ->sum('quantity');

            // Total goods out up to the date
            $totalOut = GoodsOut::where('item_id', $itemId)
                ->where('date_out', '<=', $date)
                ->sum('quantity');

            return max(0, $totalIn - $totalOut);

        } catch (\Exception $e) {
            Log::error('Error calculating available stock for date: ' . $e->getMessage());
            return 0;
        }
    }
}