<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StockService;
use Illuminate\Support\Facades\Log;

class RecalculateStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:recalculate 
                            {--item-id= : Recalculate stock for specific item ID}
                            {--force : Force recalculation without confirmation}
                            {--dry-run : Show what would be changed without actually updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate item stock based on goods in/out transactions';

    protected $stockService;

    public function __construct(StockService $stockService)
    {
        parent::__construct();
        $this->stockService = $stockService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting stock recalculation...');

        $itemId = $this->option('item-id');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        try {
            if ($itemId) {
                // Recalculate for specific item
                $this->recalculateSpecificItem($itemId, $force, $dryRun);
            } else {
                // Recalculate for all items
                $this->recalculateAllItems($force, $dryRun);
            }

        } catch (\Exception $e) {
            $this->error('Error during stock recalculation: ' . $e->getMessage());
            Log::error('Stock recalculation command error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function recalculateSpecificItem($itemId, $force, $dryRun)
    {
        $item = \App\Models\Item::find($itemId);
        
        if (!$item) {
            $this->error("Item with ID {$itemId} not found");
            return;
        }

        $this->info("Processing item: {$item->name} (ID: {$itemId})");

        $oldQuantity = $item->quantity;
        $newQuantity = $this->stockService->calculateCurrentStock($itemId);
        $difference = $newQuantity - $oldQuantity;

        $this->table(
            ['Field', 'Value'],
            [
                ['Item ID', $itemId],
                ['Item Name', $item->name],
                ['Item Code', $item->code],
                ['Current Quantity', $oldQuantity],
                ['Calculated Quantity', $newQuantity],
                ['Difference', $difference],
                ['Unit', $item->unit->name ?? 'N/A']
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN: No changes will be made');
            return;
        }

        if (!$force && !$this->confirm('Do you want to update this item stock?')) {
            $this->info('Stock update cancelled');
            return;
        }

        // Update the item
        $item->quantity = $newQuantity;
        $item->active = $newQuantity > 0 ? 'true' : 'false';
        $item->save();

        $this->info("✅ Stock updated for item: {$item->name}");
        
        Log::info('Stock recalculated via command', [
            'item_id' => $itemId,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'difference' => $difference
        ]);
    }

    private function recalculateAllItems($force, $dryRun)
    {
        if (!$force && !$dryRun && !$this->confirm('This will recalculate stock for ALL items. Continue?')) {
            $this->info('Operation cancelled');
            return;
        }

        $result = $this->stockService->recalculateAllStock();

        if ($dryRun) {
            $this->warn('DRY RUN: Showing what would be changed');
            
            // Show preview of changes
            $this->table(
                ['Item ID', 'Item Name', 'Current Stock', 'Calculated Stock', 'Difference'],
                array_map(function($item) {
                    return [
                        $item['item_id'],
                        $item['item_name'],
                        $item['old_quantity'],
                        $item['new_quantity'],
                        $item['difference']
                    ];
                }, array_slice($result['results'], 0, 20)) // Show first 20
            );

            if (count($result['results']) > 20) {
                $this->info('... and ' . (count($result['results']) - 20) . ' more items');
            }
        } else {
            // Show summary
            $this->info("Stock recalculation completed!");
            $this->info("Items updated: {$result['updated']}");
            $this->info("Errors: {$result['errors']}");

            // Show items with significant changes
            $significantChanges = array_filter($result['results'], function($item) {
                return abs($item['difference']) > 0;
            });

            if (!empty($significantChanges)) {
                $this->info("\nItems with stock changes:");
                $this->table(
                    ['Item Name', 'Old Stock', 'New Stock', 'Difference'],
                    array_map(function($item) {
                        $diff = $item['difference'];
                        $diffStr = $diff > 0 ? "+{$diff}" : (string)$diff;
                        return [
                            $item['item_name'],
                            $item['old_quantity'],
                            $item['new_quantity'],
                            $diffStr
                        ];
                    }, array_slice($significantChanges, 0, 10)) // Show first 10 changes
                );

                if (count($significantChanges) > 10) {
                    $this->info('... and ' . (count($significantChanges) - 10) . ' more items with changes');
                }
            }

            Log::info('All stock recalculated via command', [
                'updated' => $result['updated'],
                'errors' => $result['errors'],
                'total_changes' => count($significantChanges)
            ]);
        }
    }
}