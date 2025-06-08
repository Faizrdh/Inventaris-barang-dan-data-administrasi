<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'customers';
    
    protected $fillable = [
        
        'name',
        'email',
        'phone',
        'address',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relasi ke GoodsOut (Transaksi Keluar)
     */
    public function goodsOuts(): HasMany
    {
        return $this->hasMany(GoodsOut::class);
    }

    /**
     * Scope untuk customer aktif
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope untuk pencarian berdasarkan nama atau email
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }
        return $query;
    }

    /**
     * Accessor untuk mendapatkan total transaksi customer
     */
    public function getTotalTransactionsAttribute()
    {
        return $this->goodsOuts()->count();
    }

    /**
     * Accessor untuk mendapatkan total quantity yang dibeli customer
     */
    public function getTotalQuantityAttribute()
    {
        return $this->goodsOuts()->sum('quantity');
    }
}