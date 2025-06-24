<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'icon',
        'url',
        'is_read',
        'read_at',
        'expires_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for notifications of specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for notifications that haven't expired
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Get time ago formatted
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Create stock notification
     */
    public static function createStockNotification($item, $type = 'low_stock')
    {
        $titles = [
            'low_stock' => 'Stok Rendah',
            'out_of_stock' => 'Stok Habis'
        ];

        $messages = [
            'low_stock' => "Item '{$item->name}' tersisa {$item->quantity} unit",
            'out_of_stock' => "Item '{$item->name}' sudah habis!"
        ];

        $icons = [
            'low_stock' => 'fas fa-exclamation-triangle text-warning',
            'out_of_stock' => 'fas fa-times-circle text-danger'
        ];

        // Get all admin users
        $adminUsers = \App\Models\User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->orWhere('role_id', 1)->get();

        foreach ($adminUsers as $admin) {
            // Check if notification already exists for this item and user
            $exists = self::where('user_id', $admin->id)
                         ->where('type', $type)
                         ->where('data->item_id', $item->id)
                         ->where('created_at', '>', now()->subHours(6)) // Only check last 6 hours
                         ->exists();

            if (!$exists) {
                self::create([
                    'user_id' => $admin->id,
                    'type' => $type,
                    'title' => $titles[$type],
                    'message' => $messages[$type],
                    'data' => [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'quantity' => $item->quantity
                    ],
                    'icon' => $icons[$type],
                    'url' => '/laporan/stok?item_id=' . $item->id,
                    'expires_at' => now()->addDays(7)
                ]);
            }
        }
    }

    /**
     * Create leave application notification
     */
    public static function createLeaveNotification($leaveApplication, $type = 'new_leave')
    {
        $titles = [
            'new_leave' => 'Pengajuan Cuti Baru',
            'leave_approved' => 'Cuti Disetujui',
            'leave_rejected' => 'Cuti Ditolak',
            'leave_processed' => 'Cuti Diproses'
        ];

        $icons = [
            'new_leave' => 'fas fa-calendar-plus text-info',
            'leave_approved' => 'fas fa-check-circle text-success',
            'leave_rejected' => 'fas fa-times-circle text-danger',
            'leave_processed' => 'fas fa-clock text-warning'
        ];

        if ($type === 'new_leave') {
            // Notify all admins about new leave application
            $adminUsers = \App\Models\User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->orWhere('role_id', 1)->get();

            foreach ($adminUsers as $admin) {
                self::create([
                    'user_id' => $admin->id,
                    'type' => $type,
                    'title' => $titles[$type],
                    'message' => "Pengajuan cuti dari {$leaveApplication->name} perlu validasi",
                    'data' => [
                        'leave_id' => $leaveApplication->id,
                        'applicant_name' => $leaveApplication->name,
                        'leave_type' => $leaveApplication->leave_type
                    ],
                    'icon' => $icons[$type],
                    'url' => '/leave-validation?leave_id=' . $leaveApplication->id,
                    'expires_at' => now()->addDays(30)
                ]);
            }
        } else {
            // Notify the applicant about status change
            if ($leaveApplication->user_id) {
                $statusMessages = [
                    'leave_approved' => "Pengajuan cuti Anda ({$leaveApplication->leave_type}) telah disetujui",
                    'leave_rejected' => "Pengajuan cuti Anda ({$leaveApplication->leave_type}) telah ditolak",
                    'leave_processed' => "Pengajuan cuti Anda ({$leaveApplication->leave_type}) sedang diproses"
                ];

                self::create([
                    'user_id' => $leaveApplication->user_id,
                    'type' => $type,
                    'title' => $titles[$type],
                    'message' => $statusMessages[$type],
                    'data' => [
                        'leave_id' => $leaveApplication->id,
                        'leave_type' => $leaveApplication->leave_type,
                        'status' => $leaveApplication->status
                    ],
                    'icon' => $icons[$type],
                    'url' => '/leave-application?leave_id=' . $leaveApplication->id,
                    'expires_at' => now()->addDays(7)
                ]);
            }
        }
    }

    /**
     * Clean up old notifications
     */
    public static function cleanup()
    {
        // Delete expired notifications
        self::where('expires_at', '<', now())->delete();
        
        // Delete read notifications older than 30 days
        self::where('is_read', true)
            ->where('read_at', '<', now()->subDays(30))
            ->delete();
    }
}