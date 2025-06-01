<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LettersOut extends Model
{
    use HasFactory;
    
    protected $table = 'letters_out';

    protected $fillable = [
        'letter_id',
        'user_id',
        'sent_date',
        'perihal',
        'tujuan',        // ← KOLOM BARU
        'keterangan',
        'notes',
        'file_name',
        'file_path',
        'file_size',
        'file_type'
    ];

    protected $casts = [
        'sent_date' => 'date',
        'file_size' => 'integer'
    ];

    // Relationships
    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getFormattedSentDateAttribute(): string
    {
        return $this->sent_date ? $this->sent_date->format('d/m/Y') : '-';
    }

    public function getLetterCodeAttribute(): string
    {
        return $this->letter ? $this->letter->code : '-';
    }

    public function getLetterNameAttribute(): string
    {
        return $this->letter ? $this->letter->name : '-';
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return '-';
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileIconAttribute(): string
    {
        if (!$this->file_type) return 'fas fa-file text-secondary';
        
        $icons = [
            'application/pdf' => 'fas fa-file-pdf text-danger',
            'application/msword' => 'fas fa-file-word text-primary',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fas fa-file-word text-primary',
            'application/vnd.ms-excel' => 'fas fa-file-excel text-success',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fas fa-file-excel text-success',
            'image/jpeg' => 'fas fa-file-image text-info',
            'image/jpg' => 'fas fa-file-image text-info',
            'image/png' => 'fas fa-file-image text-info',
            'image/gif' => 'fas fa-file-image text-info',
            'text/plain' => 'fas fa-file-alt text-secondary'
        ];
        
        return $icons[$this->file_type] ?? 'fas fa-file text-secondary';
    }

    public function hasFile(): bool
    {
        return !empty($this->file_name) && !empty($this->file_path);
    }

    // Scopes
    public function scopeWithFile($query)
    {
        return $query->whereNotNull('file_name')->whereNotNull('file_path');
    }

    public function scopeWithoutFile($query)
    {
        return $query->where(function($q) {
            $q->whereNull('file_name')->orWhereNull('file_path');
        });
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sent_date', [$startDate, $endDate]);
    }
}