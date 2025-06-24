<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Letter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'date_received',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'category_letter_id',
        'sender_letter_id',
        'from_department'
    ];

    protected $casts = [
        'date_received' => 'datetime',
        'file_size' => 'integer',
        'category_letter_id' => 'integer',
        'sender_letter_id' => 'integer',
        'user_id' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relasi ke CategoryLetter
     */
    public function categoryLetter(): BelongsTo
    {
        return $this->belongsTo(CategoryLetter::class);
    }

    /**
     * Relasi ke SenderLetter
     */
    public function senderLetter(): BelongsTo
    {
        return $this->belongsTo(SenderLetter::class);
    }

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check apakah letter memiliki file
     */
    public function hasFile(): bool
    {
        return !empty($this->file_name) && !empty($this->file_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown size';
        }
        
        $bytes = (int) $this->file_size;
        if ($bytes === 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get file extension
     */
    public function getFileExtensionAttribute(): string
    {
        if (!$this->file_name) {
            return '';
        }
        return strtoupper(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }

    /**
     * Get file icon based on file type
     */
    public function getFileIconAttribute(): string
    {
        if (!$this->file_type) {
            return 'fas fa-file text-secondary';
        }
        
        $icons = [
            'application/pdf' => 'fas fa-file-pdf text-danger',
            'application/msword' => 'fas fa-file-word text-primary',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fas fa-file-word text-primary',
            'image/jpeg' => 'fas fa-file-image text-success',
            'image/jpg' => 'fas fa-file-image text-success',
            'image/png' => 'fas fa-file-image text-success',
        ];
        
        return $icons[$this->file_type] ?? 'fas fa-file text-secondary';
    }

    /**
     * Get upload date formatted
     */
    public function getUploadDateAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : '-';
    }

    /**
     * Get sender name (from relationship or direct field)
     */
    public function getSenderNameAttribute(): ?string
    {
        if ($this->relationLoaded('senderLetter') && $this->senderLetter) {
            return $this->senderLetter->destination;
        }
        
        // Load relation jika belum di-load
        if ($this->sender_letter_id && !$this->relationLoaded('senderLetter')) {
            $this->load('senderLetter');
            return $this->senderLetter?->destination;
        }
        
        return null;
    }

    /**
     * Get department name (prioritas dari relasi, fallback ke field langsung)
     */
    public function getDepartmentNameAttribute(): ?string
    {
        // Prioritas dari relasi sender_letter
        if ($this->relationLoaded('senderLetter') && $this->senderLetter?->from_department) {
            return $this->senderLetter->from_department;
        }
        
        // Load relation jika belum di-load dan ada sender_letter_id
        if ($this->sender_letter_id && !$this->relationLoaded('senderLetter')) {
            $this->load('senderLetter');
            if ($this->senderLetter?->from_department) {
                return $this->senderLetter->from_department;
            }
        }
        
        // Fallback ke field langsung
        return $this->from_department;
    }
}