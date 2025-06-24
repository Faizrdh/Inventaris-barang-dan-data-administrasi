<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class GoodsIn extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = "goods_in";
    
    protected $fillable = [
        'item_id',
        'user_id',
        'quantity',
        'date_received',
        'invoice_number',
        'supplier_id',
    ];

    protected $casts = [
        'date_received' => 'date',
        'quantity' => 'decimal:2', // Ubah ke decimal untuk presisi yang lebih baik
    ];

    protected $dates = [
        'date_received',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relasi ke Item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('date_received', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date_received', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Scope untuk filter berdasarkan supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }
        return $query;
    }

    /**
     * Scope untuk filter berdasarkan item
     */
    public function scopeByItem($query, $itemId)
    {
        if ($itemId) {
            $query->where('item_id', $itemId);
        }
        return $query;
    }

    /**
     * Accessor untuk format tanggal - sesuai dengan controller
     */
    public function getFormattedDateReceivedAttribute()
    {
        return $this->date_received ? $this->date_received->format('d M Y') : '-';
    }

    /**
     * Accessor untuk format quantity dengan unit - sesuai dengan controller
     */
    public function getFormattedQuantityAttribute()
    {
        $unit = $this->item && $this->item->unit ? $this->item->unit->name : 'Unit';
        return number_format($this->quantity) . ' ' . $unit;
    }

    /**
     * Method untuk mendapatkan current stock item ini
     * Sesuai dengan yang ada di controller
     */
    public function getCurrentStockAttribute()
    {
        if (!$this->item_id) {
            return 0;
        }

        try {
            // Total barang masuk
            $totalIn = self::where('item_id', $this->item_id)->sum('quantity') ?? 0;
            
            // Total barang keluar (jika ada tabel goods_out)
            $totalOut = 0;
            if (class_exists('App\Models\GoodsOut')) {
                $totalOut = \App\Models\GoodsOut::where('item_id', $this->item_id)->sum('quantity') ?? 0;
            }
            
            return $totalIn - $totalOut;
            
        } catch (\Exception $e) {
            Log::error('Error calculating stock for item ' . $this->item_id . ': ' . $e->getMessage());
            // Fallback ke quantity di tabel items
            $item = Item::find($this->item_id);
            return $item ? $item->quantity : 0;
        }
    }
}