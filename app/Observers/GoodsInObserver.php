<?php

namespace App\Observers;

use App\Models\GoodsIn;
use App\Models\Item;
use Illuminate\Support\Facades\Log;

class GoodsInObserver
{
    /**
     * Handle the GoodsIn "created" event.
     */
    public function created(GoodsIn $goodsIn): void
    {
        try {
            $this->updateItemStock($goodsIn->item_id);
            
            Log::info('Stock updated after goods in created', [
                'goods_in_id' => $goodsIn->id,
                'item_id' => $goodsIn->item_id,
                'quantity_added' => $goodsIn->quantity
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating stock after goods in created: ' . $e->getMessage(), [
                'goods_in_id' => $goodsIn->id,
                'item_id' => $goodsIn->item_id
            ]);
        }
    }

    /**
     * Handle the GoodsIn "updated" event.
     */
    public function updated(GoodsIn $goodsIn): void
    {
        try {
            // Check if item_id or quantity changed
            if ($goodsIn->isDirty(['item_id', 'quantity'])) {
                $originalItemId = $goodsIn->getOriginal('item_id');
                
                // Update original item stock if item changed
                if ($originalItemId && $originalItemId !== $goodsIn->item_id) {
                    $this->updateItemStock($originalItemId);
                }
                
                // Update current item stock
                $this->updateItemStock($goodsIn->item_id);
                
                Log::info('Stock updated after goods in modified', [
                    'goods_in_id' => $goodsIn->id,
                    'old_item_id' => $originalItemId,
                    'new_item_id' => $goodsIn->item_id,
                    'new_quantity' => $goodsIn->quantity
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating stock after goods in updated: ' . $e->getMessage(), [
                'goods_in_id' => $goodsIn->id
            ]);
        }
    }

    /**
     * Handle the GoodsIn "deleted" event.
     */
    public function deleted(GoodsIn $goodsIn): void
    {
        try {
            $this->updateItemStock($goodsIn->item_id);
            
            Log::info('Stock updated after goods in deleted', [
                'goods_in_id' => $goodsIn->id,
                'item_id' => $goodsIn->item_id,
                'quantity_removed' => $goodsIn->quantity
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating stock after goods in deleted: ' . $e->getMessage(), [
                'goods_in_id' => $goodsIn->id
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
            $totalGoodsIn = GoodsIn::where('item_id', $itemId)->sum('quantity');
            
            // Calculate total goods out
            $totalGoodsOut = \App\Models\GoodsOut::where('item_id', $itemId)->sum('quantity');
            
            // Calculate current stock
            $currentStock = $totalGoodsIn - $totalGoodsOut;
            
            // Ensure stock is not negative
            $currentStock = max(0, $currentStock);
            
            // Update item quantity
            $oldQuantity = $item->quantity;
            $item->quantity = $currentStock;
            $item->active = $currentStock > 0 ? 'true' : 'false';
            $item->save();

            Log::info('Item stock updated', [
                'item_id' => $itemId,
                'item_name' => $item->name,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $currentStock,
                'total_in' => $totalGoodsIn,
                'total_out' => $totalGoodsOut
            ]);

        } catch (\Exception $e) {
            Log::error('Error in updateItemStock: ' . $e->getMessage(), [
                'item_id' => $itemId
            ]);
        }
    }
}