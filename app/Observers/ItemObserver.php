<?php

namespace App\Observers;

use App\Models\Item;
use Illuminate\Support\Facades\Log;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        try {
            Log::info('New item created', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'item_code' => $item->code,
                'initial_quantity' => $item->quantity
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ItemObserver created: ' . $e->getMessage(), [
                'item_id' => $item->id
            ]);
        }
    }

    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        try {
            // Check if quantity was changed
            if ($item->isDirty('quantity')) {
                $oldQuantity = $item->getOriginal('quantity');
                $newQuantity = $item->quantity;
                
                Log::info('Item quantity changed', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'difference' => $newQuantity - $oldQuantity
                ]);

                // Create notifications based on new quantity
                $this->handleStockNotifications($item, $oldQuantity, $newQuantity);
            }

            // Check if active status changed
            if ($item->isDirty('active')) {
                $oldActive = $item->getOriginal('active');
                $newActive = $item->active;
                
                Log::info('Item active status changed', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'old_active' => $oldActive,
                    'new_active' => $newActive
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in ItemObserver updated: ' . $e->getMessage(), [
                'item_id' => $item->id
            ]);
        }
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        try {
            Log::info('Item deleted', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'final_quantity' => $item->quantity
            ]);

            // Clean up related notifications
            $this->cleanupItemNotifications($item);
                
        } catch (\Exception $e) {
            Log::error('Error in ItemObserver deleted: ' . $e->getMessage(), [
                'item_id' => $item->id
            ]);
        }
    }

    /**
     * Handle stock notifications based on quantity changes
     */
    private function handleStockNotifications($item, $oldQuantity, $newQuantity)
    {
        try {
            // Check if Notification model exists
            if (!class_exists('\App\Models\Notification')) {
                return; // Skip if notification system not implemented
            }

            $notificationClass = '\App\Models\Notification';

            // Create notifications based on new quantity
            if ($newQuantity <= 0 && $oldQuantity > 0) {
                // Item just went out of stock
                $this->createStockNotification($item, 'out_of_stock');
            } elseif ($newQuantity <= 3 && $newQuantity > 0 && $oldQuantity > 3) {
                // Item just became low stock
                $this->createStockNotification($item, 'low_stock');
            }

            // Clean up notifications when stock improves
            if ($newQuantity > 0 && $oldQuantity <= 0) {
                // Remove out of stock notifications
                $notificationClass::where('type', 'out_of_stock')
                    ->where('data->item_id', $item->id)
                    ->delete();
            }

            if ($newQuantity > 3 && $oldQuantity <= 3) {
                // Remove low stock notifications
                $notificationClass::where('type', 'low_stock')
                    ->where('data->item_id', $item->id)
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::error('Error handling stock notifications: ' . $e->getMessage(), [
                'item_id' => $item->id
            ]);
        }
    }

    /**
     * Create stock notification
     */
    private function createStockNotification($item, $type)
    {
        try {
            // Check if Notification model exists
            if (!class_exists('\App\Models\Notification')) {
                return; // Skip if notification system not implemented
            }

            $notificationClass = '\App\Models\Notification';

            $titles = [
                'low_stock' => 'Stok Rendah',
                'out_of_stock' => 'Stok Habis'
            ];

            $messages = [
                'low_stock' => "Item '{$item->name}' tersisa {$item->quantity} unit",
                'out_of_stock' => "Item '{$item->name}' sudah habis!"
            ];

            $icons = [
                'low_stock' => 'fas fa-exclamation-triangle text-warning',
                'out_of_stock' => 'fas fa-times-circle text-danger'
            ];

            // Get all admin users
            $adminUsers = \App\Models\User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->orWhere('role_id', 1)->get();

            foreach ($adminUsers as $admin) {
                // Check if notification already exists for this item and user
                $exists = $notificationClass::where('user_id', $admin->id)
                             ->where('type', $type)
                             ->where('data->item_id', $item->id)
                             ->where('created_at', '>', now()->subHours(6)) // Only check last 6 hours
                             ->exists();

                if (!$exists) {
                    $notificationClass::create([
                        'user_id' => $admin->id,
                        'type' => $type,
                        'title' => $titles[$type],
                        'message' => $messages[$type],
                        'data' => [
                            'item_id' => $item->id,
                            'item_name' => $item->name,
                            'quantity' => $item->quantity
                        ],
                        'icon' => $icons[$type],
                        'url' => '/laporan/stok?item_id=' . $item->id,
                        'expires_at' => now()->addDays(7)
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error creating stock notification: ' . $e->getMessage(), [
                'item_id' => $item->id,
                'type' => $type
            ]);
        }
    }

    /**
     * Clean up item notifications
     */
    private function cleanupItemNotifications($item)
    {
        try {
            // Check if Notification model exists
            if (!class_exists('\App\Models\Notification')) {
                return; // Skip if notification system not implemented
            }

            $notificationClass = '\App\Models\Notification';
            
            // Remove related notifications when item is deleted
            $notificationClass::whereIn('type', ['low_stock', 'out_of_stock'])
                ->where('data->item_id', $item->id)
                ->delete();
                
        } catch (\Exception $e) {
            Log::error('Error cleaning up item notifications: ' . $e->getMessage(), [
                'item_id' => $item->id
            ]);
        }
    }
}