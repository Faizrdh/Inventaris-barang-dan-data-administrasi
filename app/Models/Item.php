<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Item extends Model
{
    use HasFactory;
    
    protected $table = 'items';
    
    protected $fillable = [
        'name',
        'image',
        'code',
        'quantity',
        'category_id',
        'brand_id',
        'unit_id',
        'active',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'active' => 'string', // Sesuai controller menggunakan string 'true'/'false'
    ];

    /**
     * Relasi ke Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke Unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi ke Brand
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Relasi ke GoodsIn (Transaksi Masuk)
     */
    public function goodsIns(): HasMany
    {
        return $this->hasMany(GoodsIn::class);
    }

    /**
     * Relasi ke GoodsOut (Transaksi Keluar)
     */
    public function goodsOuts(): HasMany
    {
        return $this->hasMany(GoodsOut::class);
    }

    /**
     * Method untuk menghitung current stock berdasarkan transaksi
     * Sesuai dengan calculateCurrentStock di controller
     */
    public function getCurrentStockAttribute()
    {
        try {
            // Total barang masuk
            $totalIn = $this->goodsIns()->sum('quantity') ?? 0;
            
            // Total barang keluar (jika ada tabel goods_out)
            $totalOut = 0;
            if (class_exists('App\Models\GoodsOut')) {
                $totalOut = $this->goodsOuts()->sum('quantity') ?? 0;
            }
            
            return $totalIn - $totalOut;
            
        } catch (\Exception $e) {
            Log::error('Error calculating stock for item ' . $this->id . ': ' . $e->getMessage());
            // Fallback ke quantity di tabel items
            return $this->quantity ?? 0;
        }
    }

    /**
     * Method untuk mendapatkan formatted quantity dengan unit
     * Sesuai dengan yang digunakan di controller
     */
    public function getFormattedCurrentStockAttribute()
    {
        $currentStock = $this->getCurrentStockAttribute();
        $unit = $this->unit->name ?? 'Unit';
        $class = $currentStock <= 0 ? 'text-danger' : ($currentStock <= 3 ? 'text-warning' : 'text-success');
        
        return [
            'value' => $currentStock,
            'formatted' => number_format($currentStock) . ' ' . $unit,
            'class' => $class
        ];
    }

    /**
     * Scope untuk item aktif
     */
    public function scopeActive($query)
    {
        return $query->where('active', 'true');
    }

    /**
     * Scope untuk pencarian berdasarkan nama atau kode
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        return $query;
    }

    /**
     * Scope untuk filter berdasarkan kategori
     */
    public function scopeByCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }
        return $query;
    }

    /**
     * Scope untuk filter berdasarkan brand
     */
    public function scopeByBrand($query, $brandId)
    {
        if ($brandId) {
            return $query->where('brand_id', $brandId);
        }
        return $query;
    }

    /**
     * Accessor untuk mendapatkan URL gambar
     */
    public function getImageUrlAttribute()
    {
        if (empty($this->image)) {
            return asset('default.png');
        }
        return asset('storage/barang/' . $this->image);
    }

    /**
     * Method untuk mendapatkan stock movements
     * Sesuai dengan getStockMovements di controller
     */
    public function getStockMovements()
    {
        $movements = [];
        
        // Ambil transaksi masuk
        $goodsIn = $this->goodsIns()
            ->with('supplier', 'user')
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
            $goodsOut = $this->goodsOuts()
                ->with('user')
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

        return $movements;
    }
}