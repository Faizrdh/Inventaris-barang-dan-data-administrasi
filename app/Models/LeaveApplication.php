<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class LeaveApplication extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'leave_applications';
   
    protected $fillable = [
        'code', 'name', 'employee_id', 'email', 'application_date', 'leave_type',
        'start_date', 'end_date', 'total_days', 'description', 'status',
        'document_path', 'user_id', 'approved_by', 'approved_at', 'catatan_validator'
    ];
    
    protected $casts = [
        'application_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    // Business Logic Methods
    public function calculateTotalDays(): int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }
        return 0;
    }
    
    public function saveDocument($file): ?string
    {
        return $file ? $file->store('leave_documents', 'public') : null;
    }
    
    public function deleteDocument(): void
    {
        if ($this->document_path && Storage::disk('public')->exists($this->document_path)) {
            Storage::disk('public')->delete($this->document_path);
        }
    }
    
    public function canBeModified(): bool
    {
        return $this->status === 'pending';
    }
    
    // Accessors untuk DataTables
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            'approved' => '<span class="badge badge-success"><i class="fas fa-check"></i> Approved</span>',
            'rejected' => '<span class="badge badge-danger"><i class="fas fa-times"></i> Rejected</span>',
            'processed' => '<span class="badge badge-info"><i class="fas fa-cog"></i> Processed</span>',
        ];
       
        return $labels[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->getStatusLabelAttribute();
    }

    // Accessor untuk format tanggal
    public function getApplicationDateFormattedAttribute(): string
    {
        return $this->application_date ? $this->application_date->format('Y-m-d') : '-';
    }

    public function getStartDateFormattedAttribute(): string
    {
        return $this->start_date ? $this->start_date->format('Y-m-d') : '-';
    }

    public function getEndDateFormattedAttribute(): string
    {
        return $this->end_date ? $this->end_date->format('Y-m-d') : '-';
    }

    public function getApprovedAtFormattedAttribute(): string
    {
        return $this->approved_at ? $this->approved_at->format('Y-m-d H:i') : '-';
    }

    // Accessor untuk approver name
    public function getApproverNameAttribute(): string
    {
        return $this->approver ? $this->approver->name : '-';
    }

    // Accessor untuk status text
    public function getStatusTextAttribute(): string
    {
        $statusTexts = [
            'pending' => 'Pending',
            'approved' => 'Approved', 
            'rejected' => 'Rejected',
            'processed' => 'Processed'
        ];

        return $statusTexts[$this->status] ?? 'Unknown';
    }
}