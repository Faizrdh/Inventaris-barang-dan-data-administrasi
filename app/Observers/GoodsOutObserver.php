<?php

namespace App\Observers;

use App\Models\GoodsOut;
use App\Models\Item;
use Illuminate\Support\Facades\Log;

class GoodsOutObserver
{
    /**
     * Handle the GoodsOut "created" event.
     */
    public function created(GoodsOut $goodsOut): void
    {
        try {
            $this->updateItemStock($goodsOut->item_id);
            
            Log::info('Stock updated after goods out created', [
                'goods_out_id' => $goodsOut->id,
                'item_id' => $goodsOut->item_id,
                'quantity_removed' => $goodsOut->quantity
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating stock after goods out created: ' . $e->getMessage(), [
                'goods_out_id' => $goodsOut->id,
                'item_id' => $goodsOut->item_id
            ]);
        }
    }

    /**
     * Handle the GoodsOut "updated" event.
     */
    public function updated(GoodsOut $goodsOut): void
    {
        try {
            // Check if item_id or quantity changed
            if ($goodsOut->isDirty(['item_id', 'quantity'])) {
                $originalItemId = $goodsOut->getOriginal('item_id');
                
                // Update original item stock if item changed
                if ($originalItemId && $originalItemId !== $goodsOut->item_id) {
                    $this->updateItemStock($originalItemId);
                }
                
                // Update current item stock
                $this->updateItemStock($goodsOut->item_id);
                
                Log::info('Stock updated after goods out modified', [
                    'goods_out_id' => $goodsOut->id,
                    'old_item_id' => $originalItemId,
                    'new_item_id' => $goodsOut->item_id,
                    'new_quantity' => $goodsOut->quantity
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating stock after goods out updated: ' . $e->getMessage(), [
                'goods_out_id' => $goodsOut->id
            ]);
        }
    }

    /**
     * Handle the GoodsOut "deleted" event.
     */
    public function deleted(GoodsOut $goodsOut): void
    {
        try {
            $this->updateItemStock($goodsOut->item_id);
            
            Log::info('Stock updated after goods out deleted', [
                'goods_out_id' => $goodsOut->id,
                'item_id' => $goodsOut->item_id,
                'quantity_restored' => $goodsOut->quantity
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating stock after goods out deleted: ' . $e->getMessage(), [
                'goods_out_id' => $goodsOut->id
            ]);
        }
    }

    /**
     * Update item stock based on all transactions
     */
    private function updateItemStock($itemId): void
    {
        if (!$itemId) return;

        try {
            $item = Item::find($itemId);
            if (!$item) return;

            // Calculate total goods in
            $totalGoodsIn = \App\Models\GoodsIn::where('item_id', $itemId)->sum('quantity');
            
            // Calculate total goods out
            $totalGoodsOut = GoodsOut::where('item_id', $itemId)->sum('quantity');
            
            // Calculate current stock
            $currentStock = $totalGoodsIn - $totalGoodsOut;
            
            // Ensure stock is not negative
            $currentStock = max(0, $currentStock);
            
            // Update item quantity
            $oldQuantity = $item->quantity;
            $item->quantity = $currentStock;
            $item->active = $currentStock > 0 ? 'true' : 'false';
            $item->save();

            Log::info('Item stock updated from goods out', [
                'item_id' => $itemId,
                'item_name' => $item->name,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $currentStock,
                'total_in' => $totalGoodsIn,
                'total_out' => $totalGoodsOut
            ]);

        } catch (\Exception $e) {
            Log::error('Error in updateItemStock from goods out: ' . $e->getMessage(), [
                'item_id' => $itemId
            ]);
        }
    }
}