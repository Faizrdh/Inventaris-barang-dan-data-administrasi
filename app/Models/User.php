<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
    
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image',
        'username',
        'password',
        'role_id'
    ];

    // * Check if user is administrator
  // Tambahkan metode ini di model User.php jika yang existing tidak berfungsi
public function getIsAdminAttribute()
{
    return $this->role_id === 1;
}

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this -> belongsTo(Role::class);
    }

    public function goodsIns(): HasMany
    {
        return $this -> hasMany(GoodsIn::class);
    }

    public function goodsOuts():HasMany
    {
        return $this -> hasMany(GoodsOut::class);
    }
}
