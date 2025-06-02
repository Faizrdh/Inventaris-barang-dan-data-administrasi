<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LeaveApplication extends Model
{
    use HasFactory;

    // Using English table name
    protected $table = 'leave_applications';
    
    // Fillable columns for mass-assignment
    protected $fillable = [
        'code',
        'name',
        'employee_id',
        'application_date',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'description',
        'status',
        'document_path',
        'user_id',
        'approved_by',
        'approved_at',
    ];

    // Convert column data types to appropriate formats
    protected $casts = [
        'application_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Relation to User model for the user who submitted the application
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation to User model for approved leave applications
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calculate total days based on start and end dates
     */
    public function calculateTotalDays()
    {
        if ($this->start_date && $this->end_date) {
            $start = new \DateTime($this->start_date);
            $end = new \DateTime($this->end_date);
            $diff = $start->diff($end);
            return $diff->days + 1; // Total days including first day
        }

        return 0;
    }

    /**
     * Save document file when leave application is submitted
     *
     * @param \Illuminate\Http\UploadedFile|null $file
     * @return string|null
     */
    public function saveDocument($file)
    {
        if ($file) {
            return $file->store('leave_documents', 'public');
        }

        return null;
    }

    /**
     * Delete document file associated with leave application
     *
     * @return void
     */
    public function deleteDocument()
    {
        if ($this->document_path && Storage::disk('public')->exists($this->document_path)) {
            Storage::disk('public')->delete($this->document_path);
        }
    }

    /**
     * Get the status label with proper styling
     */
    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'approved' => '<span class="badge badge-success">Approved</span>',
            'rejected' => '<span class="badge badge-danger">Rejected</span>',
        ];

        return $statusLabels[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}