<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GoodsOut extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "goods_out";

    protected $fillable = [
        'item_id',
        'user_id',
        'customer_id',
        'quantity',
        'date_out',
        'invoice_number',
        'reference',
        'destination',
    ];

    protected $casts = [
        'date_out' => 'date',
        'quantity' => 'decimal:2',
    ];

    protected $dates = [
        'date_out',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('date_out', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date_out', '<=', $endDate);
        }
        return $query;
    }

    public function scopeByItem($query, $itemId)
    {
        if ($itemId) {
            $query->where('item_id', $itemId);
        }
        return $query;
    }

    public function getFormattedDateOutAttribute()
    {
        return $this->date_out ? $this->date_out->format('d M Y') : '-';
    }

    public function getFormattedQuantityAttribute()
    {
        $unit = $this->item && $this->item->unit ? $this->item->unit->name : 'Unit';
        return number_format($this->quantity) . ' ' . $unit;
    }

    public function getKodeBarangAttribute()
    {
        return $this->item ? $this->item->code : '-';
    }

    public static function calculateCurrentStock($itemId)
    {
        if (!$itemId) {
            return 0;
        }

        try {
            $totalIn = DB::table('goods_in')
                ->where('item_id', $itemId)
                ->whereNull('deleted_at')
                ->sum('quantity') ?? 0;

            $totalOut = DB::table('goods_out')
                ->where('item_id', $itemId)
                ->whereNull('deleted_at')
                ->sum('quantity') ?? 0;

            $currentStock = $totalIn - $totalOut;
            return max(0, $currentStock);

        } catch (\Exception $e) {
            Log::error('Error calculating current stock for item ' . $itemId . ': ' . $e->getMessage());
            try {
                $item = \App\Models\Item::find($itemId);
                return $item ? max(0, $item->quantity ?? 0) : 0;
            } catch (\Exception $fallbackError) {
                Log::error('Fallback error: ' . $fallbackError->getMessage());
                return 0;
            }
        }
    }

    public static function validateStockAvailability($itemId, $requestedQuantity, $excludeTransactionId = null)
    {
        try {
            $currentStock = self::calculateCurrentStock($itemId);

            if ($excludeTransactionId) {
                $oldTransaction = self::find($excludeTransactionId);
                if ($oldTransaction && $oldTransaction->item_id == $itemId) {
                    $currentStock += $oldTransaction->quantity;
                }
            }

            $isAvailable = $requestedQuantity <= $currentStock;

            return [
                'is_available' => $isAvailable,
                'current_stock' => $currentStock,
                'requested_quantity' => $requestedQuantity,
                'remaining_after_transaction' => $currentStock - $requestedQuantity,
                'message' => $isAvailable 
                    ? 'Stock available' 
                    : "Insufficient stock. Available: {$currentStock}, Requested: {$requestedQuantity}"
            ];

        } catch (\Exception $e) {
            Log::error('Error validating stock availability: ' . $e->getMessage());
            return [
                'is_available' => false,
                'current_stock' => 0,
                'requested_quantity' => $requestedQuantity,
                'remaining_after_transaction' => 0,
                'message' => 'Error checking stock availability: ' . $e->getMessage()
            ];
        }
    }

    public function getCurrentStockAttribute()
    {
        return self::calculateCurrentStock($this->item_id);
    }

    public static function getDetailedStockInfo($itemId)
    {
        try {
            $totalIn = DB::table('goods_in')
                ->where('item_id', $itemId)
                ->whereNull('deleted_at')
                ->sum('quantity') ?? 0;

            $totalOut = DB::table('goods_out')
                ->where('item_id', $itemId)
                ->whereNull('deleted_at')
                ->sum('quantity') ?? 0;

            $currentStock = $totalIn - $totalOut;
            $item = \App\Models\Item::with(['unit', 'category'])->find($itemId);

            return [
                'item_id' => $itemId,
                'item_name' => $item->name ?? 'Unknown',
                'item_code' => $item->code ?? 'Unknown',
                'unit' => $item->unit->name ?? 'Unit',
                'category' => $item->category->name ?? 'Category',
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'current_stock' => max(0, $currentStock),
                'status' => $currentStock <= 0 ? 'out_of_stock' : ($currentStock <= 3 ? 'low_stock' : 'normal'),
                'last_updated' => now()->toDateTimeString()
            ];

        } catch (\Exception $e) {
            Log::error('Error getting detailed stock info: ' . $e->getMessage());
            return [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'current_stock' => 0,
                'status' => 'error'
            ];
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($goodsOut) {
            Log::info('Goods Out Created', [
                'id' => $goodsOut->id,
                'item_id' => $goodsOut->item_id,
                'quantity' => $goodsOut->quantity,
                'new_stock' => self::calculateCurrentStock($goodsOut->item_id)
            ]);
        });

        static::updated(function ($goodsOut) {
            Log::info('Goods Out Updated', [
                'id' => $goodsOut->id,
                'item_id' => $goodsOut->item_id,
                'quantity' => $goodsOut->quantity,
                'new_stock' => self::calculateCurrentStock($goodsOut->item_id)
            ]);
        });

        static::deleted(function ($goodsOut) {
            Log::info('Goods Out Deleted', [
                'id' => $goodsOut->id,
                'item_id' => $goodsOut->item_id,
                'quantity' => $goodsOut->quantity,
                'new_stock' => self::calculateCurrentStock($goodsOut->item_id)
            ]);
        });
    }
}