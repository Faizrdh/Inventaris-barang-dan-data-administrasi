<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenderLetter extends Model
{
    use HasFactory;

    protected $table = 'sender_letters';
    
    protected $fillable = [
        'name',
        'from_department', 
        'destination'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}