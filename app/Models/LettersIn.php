<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LettersIn extends Model
{
    use HasFactory;

    protected $table = 'letters_in';

    protected $fillable = [
        'letter_id',
        'sender_letter_id',
        'category_letter_id',
        'user_id',
        'received_date',
        'from_department',
        'sender_name',
        'notes',
        'file_name',
        'file_path',
        'file_size',
        'file_type'
    ];

    protected $casts = [
        'received_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke Letter
     */
    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    /**
     * Relasi ke SenderLetter
     */
    public function senderLetter(): BelongsTo
    {
        return $this->belongsTo(SenderLetter::class);
    }

    /**
     * Relasi ke CategoryLetter
     */
    public function categoryLetter(): BelongsTo
    {
        return $this->belongsTo(CategoryLetter::class);
    }

    /**
     * Relasi ke User (yang mencatat surat masuk)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted received date
     */
    public function getFormattedReceivedDateAttribute(): string
    {
        return $this->received_date ? Carbon::parse($this->received_date)->format('d F Y') : '-';
    }

    /**
     * Get sender name (prioritas dari field langsung, fallback ke relasi)
     */
    public function getSenderNameDisplayAttribute(): string
    {
        // Prioritas dari field langsung
        if ($this->sender_name) {
            return $this->sender_name;
        }
        
        // Fallback ke relasi letter -> sender_letter (HAPUS referensi name)
        if ($this->letter && $this->letter->senderLetter) {
            return $this->letter->senderLetter->from_department ?? 'Unknown Sender';
        }
        
        return '-';
    }

    /**
     * Get department name (prioritas dari field langsung, fallback ke relasi)
     */
    public function getDepartmentNameDisplayAttribute(): string
    {
        // Prioritas dari field langsung
        if ($this->from_department) {
            return $this->from_department;
        }
        
        // Fallback ke relasi sender_letter
        if ($this->senderLetter && $this->senderLetter->from_department) {
            return $this->senderLetter->from_department;
        }
        
        // Fallback ke relasi letter -> sender_letter
        if ($this->letter && $this->letter->senderLetter && $this->letter->senderLetter->from_department) {
            return $this->letter->senderLetter->from_department;
        }
        
        return '-';
    }

    /**
     * Get letter code from relation
     */
    public function getLetterCodeAttribute(): string
    {
        return $this->letter ? $this->letter->code : '-';
    }

    /**
     * Get letter name from relation
     */
    public function getLetterNameAttribute(): string
    {
        return $this->letter ? $this->letter->name : '-';
    }

    /**
     * Get category name from relation
     */
    public function getCategoryNameAttribute(): string
    {
        return $this->categoryLetter ? $this->categoryLetter->name : '-';
    }

    /**
     * Check apakah letters_in memiliki file
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
        $bytes = $this->file_size;
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
     * Get file status untuk ditampilkan
     */
    public function getFileStatusAttribute(): string
    {
        if ($this->hasFile()) {
            return '<span class="badge badge-success"><i class="fas fa-check"></i> Ada File</span>';
        }
        return '<span class="badge badge-secondary"><i class="fas fa-times"></i> Tidak Ada File</span>';
    }

    /**
     * Auto populate data dari letter saat saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto populate dari letter jika belum ada
            if ($model->letter_id) {
                $letter = Letter::find($model->letter_id);
                
                if ($letter) {
                    // Auto populate sender info jika belum ada
                    if (!$model->sender_name && !$model->from_department) {
                        if ($letter->senderLetter) {
                            // HAPUS referensi ke name, gunakan from_department sebagai fallback
                            $model->sender_name = $letter->senderLetter->from_department ?? 'Unknown Sender';
                            $model->from_department = $letter->senderLetter->from_department ?? $letter->from_department;
                        }
                    }
                    
                    // Auto populate file info dari letter
                    if ($letter->hasFile()) {
                        $model->file_name = $letter->file_name;
                        $model->file_path = $letter->file_path;
                        $model->file_size = $letter->file_size;
                        $model->file_type = $letter->file_type;
                    }
                }
            }
            
            // Auto populate dari sender_letter_id jika dipilih manual
            if ($model->sender_letter_id && (!$model->sender_name || !$model->from_department)) {
                $senderLetter = SenderLetter::find($model->sender_letter_id);
                
                if ($senderLetter) {
                    // HAPUS referensi ke name, gunakan from_department
                    if (!$model->sender_name) {
                        $model->sender_name = $senderLetter->from_department ?? 'Unknown Sender';
                    }
                    if (!$model->from_department) {
                        $model->from_department = $senderLetter->from_department;
                    }
                }
            }
        });
    }
}