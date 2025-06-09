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
        'code', 'name', 'employee_id', 'application_date', 'leave_type',
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

    protected $dates = ['deleted_at'];

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

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'approved' => '<span class="badge badge-success">Approved</span>',
            'rejected' => '<span class="badge badge-danger">Rejected</span>',
            'processed' => '<span class="badge badge-info">Processed</span>',
        ];
        
        return $labels[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePendingValidation($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeValidated($query)
    {
        return $query->whereIn('status', ['approved', 'rejected', 'processed']);
    }

    public function scopeWithTrashed($query)
    {
        return $query->withTrashed();
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->onlyTrashed();
    }
}