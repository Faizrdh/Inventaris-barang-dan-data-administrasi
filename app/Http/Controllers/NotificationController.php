<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    /**
     * Get all notifications - FIXED VERSION with better validation
     */
    public function getNotifications(): JsonResponse
    {
        try {
            $notifications = [];
            $userId = Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'success' => true,
                    'notifications' => [],
                    'total_count' => 0,
                    'unread_count' => 0
                ]);
            }

            // PERTAMA: Bersihkan notifikasi yang tidak valid
            $this->cleanupInvalidNotifications();

            // 1. CEK STOK HABIS & RENDAH (untuk admin) - IMPROVED
            if ($this->isAdmin()) {
                $notifications = array_merge($notifications, $this->getStockNotifications());
            }

            // 2. CEK PENGAJUAN CUTI BARU (untuk admin) - IMPROVED
            if ($this->isAdmin()) {
                $notifications = array_merge($notifications, $this->getLeaveValidationNotifications());
            }

            // 3. CEK STATUS CUTI USER (untuk user yang login)
            $notifications = array_merge($notifications, $this->getUserLeaveNotifications($userId));

            // Sort by priority and time
            usort($notifications, function($a, $b) {
                $priorityOrder = ['urgent' => 0, 'high' => 1, 'normal' => 2];
                $aPriority = $priorityOrder[$a['priority']] ?? 2;
                $bPriority = $priorityOrder[$b['priority']] ?? 2;
                
                if ($aPriority === $bPriority) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                }
                return $aPriority - $bPriority;
            });

            // Limit notifications
            $notifications = array_slice($notifications, 0, 20);

            // Calculate counts
            $readNotifications = Session::get('read_notifications', []);
            $unreadCount = 0;
            
            foreach ($notifications as &$notif) {
                $notif['is_read'] = in_array($notif['id'], $readNotifications);
                if (!$notif['is_read']) {
                    $unreadCount++;
                }
            }

            return response()->json([
                'success' => true,
                'notifications' => array_values($notifications),
                'total_count' => count($notifications),
                'unread_count' => $unreadCount,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
            
            return response()->json([
                'success' => true, // Return success to avoid JS errors
                'notifications' => [],
                'total_count' => 0,
                'unread_count' => 0,
                'error' => 'Error loading notifications: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * IMPROVED: Get stock notifications with STRICT validation
     */
    private function getStockNotifications(): array
    {
        $notifications = [];

        try {
            // STRICT VALIDATION: Hanya ambil item yang benar-benar valid
            $outOfStockItems = DB::table('items')
                ->where('quantity', '<=', 0)
                ->where('active', 'true')
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->where('name', 'NOT LIKE', '%deleted%') // Exclude marked as deleted
                ->whereNull('deleted_at') // If using soft deletes
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($outOfStockItems as $item) {
                // DOUBLE CHECK: Pastikan item masih ada dan valid
                $itemExists = DB::table('items')
                    ->where('id', $item->id)
                    ->where('active', 'true')
                    ->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->exists();
                
                if ($itemExists && !empty(trim($item->name))) {
                    $notifications[] = [
                        'id' => 'out_of_stock_' . $item->id,
                        'type' => 'out_of_stock',
                        'title' => 'Stok Habis!',
                        'message' => "Item '{$item->name}' sudah habis",
                        'icon' => 'fas fa-times-circle text-danger',
                        'priority' => 'urgent',
                        'time' => 'Urgent',
                        'url' => '/laporan/stok?search=' . urlencode($item->name),
                        'created_at' => now()->toISOString(),
                        'data' => [
                            'item_id' => $item->id, 
                            'item_name' => $item->name,
                            'redirect_type' => 'stock_report'
                        ]
                    ];
                } else {
                    // Log jika ada item yang tidak valid
                    Log::warning("Invalid item found in out of stock: ID {$item->id}, Name: '{$item->name}'");
                }
            }

            // STRICT VALIDATION untuk low stock
            $lowStockItems = DB::table('items')
                ->where('quantity', '>', 0)
                ->where('quantity', '<=', 3)
                ->where('active', 'true')
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->where('name', 'NOT LIKE', '%deleted%')
                ->whereNull('deleted_at')
                ->orderBy('quantity', 'asc')
                ->limit(5)
                ->get();

            foreach ($lowStockItems as $item) {
                // DOUBLE CHECK untuk low stock
                $itemExists = DB::table('items')
                    ->where('id', $item->id)
                    ->where('active', 'true')
                    ->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->exists();
                
                if ($itemExists && !empty(trim($item->name))) {
                    $notifications[] = [
                        'id' => 'low_stock_' . $item->id,
                        'type' => 'low_stock',
                        'title' => 'Stok Rendah',
                        'message' => "Item '{$item->name}' tersisa {$item->quantity} unit",
                        'icon' => 'fas fa-exclamation-triangle text-warning',
                        'priority' => 'high',
                        'time' => 'Sekarang',
                        'url' => '/laporan/stok?search=' . urlencode($item->name),
                        'created_at' => now()->toISOString(),
                        'data' => [
                            'item_id' => $item->id, 
                            'item_name' => $item->name, 
                            'quantity' => $item->quantity,
                            'redirect_type' => 'stock_report'
                        ]
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Error getting stock notifications: ' . $e->getMessage());
        }

        return $notifications;
    }

    /**
     * IMPROVED: Get leave validation notifications with better validation
     */
    private function getLeaveValidationNotifications(): array
    {
        $notifications = [];

        try {
            $pendingLeaves = DB::table('leave_applications')
                ->where('status', 'pending')
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->whereNull('deleted_at') // If using soft deletes
                ->orderBy('application_date', 'asc')
                ->limit(10)
                ->get();

            foreach ($pendingLeaves as $leave) {
                // Validasi leave application masih valid
                $leaveExists = DB::table('leave_applications')
                    ->where('id', $leave->id)
                    ->where('status', 'pending')
                    ->exists();
                
                if ($leaveExists && !empty(trim($leave->name))) {
                    $daysSinceApplication = Carbon::parse($leave->application_date)->diffInDays(now());
                    $isUrgent = $daysSinceApplication >= 3;

                    $notifications[] = [
                        'id' => 'pending_leave_' . $leave->id,
                        'type' => 'pending_leave',
                        'title' => 'Pengajuan Cuti Pending',
                        'message' => "Cuti {$leave->leave_type} dari {$leave->name}" . ($isUrgent ? " (sudah {$daysSinceApplication} hari)" : ''),
                        'icon' => $isUrgent ? 'fas fa-calendar-times text-danger' : 'fas fa-calendar-check text-warning',
                        'priority' => $isUrgent ? 'urgent' : 'high',
                        'time' => $isUrgent ? 'Urgent!' : Carbon::parse($leave->application_date)->diffForHumans(),
                        'url' => '/leave-validation?highlight=' . $leave->id,
                        'created_at' => $leave->application_date,
                        'data' => [
                            'leave_id' => $leave->id, 
                            'applicant_name' => $leave->name, 
                            'days_pending' => $daysSinceApplication,
                            'redirect_type' => 'leave_validation'
                        ]
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Error getting leave validation notifications: ' . $e->getMessage());
        }

        return $notifications;
    }

    /**
     * Get user's leave notifications (status updates) - UNCHANGED
     */
    private function getUserLeaveNotifications($userId): array
    {
        $notifications = [];

        try {
            // Recent status updates (last 7 days)
            $recentLeaves = DB::table('leave_applications')
                ->where('user_id', $userId)
                ->whereIn('status', ['approved', 'rejected', 'processed'])
                ->where('approved_at', '>=', now()->subDays(7))
                ->orderBy('approved_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentLeaves as $leave) {
                $statusConfig = [
                    'approved' => [
                        'title' => 'Cuti Disetujui ✅',
                        'message' => "Cuti {$leave->leave_type} Anda telah disetujui",
                        'icon' => 'fas fa-check-circle text-success',
                        'priority' => 'normal'
                    ],
                    'rejected' => [
                        'title' => 'Cuti Ditolak ❌',
                        'message' => "Cuti {$leave->leave_type} Anda ditolak",
                        'icon' => 'fas fa-times-circle text-danger',
                        'priority' => 'high'
                    ],
                    'processed' => [
                        'title' => 'Cuti Diproses ⏳',
                        'message' => "Cuti {$leave->leave_type} Anda sedang diproses",
                        'icon' => 'fas fa-clock text-info',
                        'priority' => 'normal'
                    ]
                ];

                $config = $statusConfig[$leave->status] ?? [
                    'title' => 'Update Cuti',
                    'message' => "Status cuti Anda: {$leave->status}",
                    'icon' => 'fas fa-info-circle text-info',
                    'priority' => 'normal'
                ];

                $notifications[] = [
                    'id' => 'leave_status_' . $leave->id,
                    'type' => 'leave_status',
                    'title' => $config['title'],
                    'message' => $config['message'],
                    'icon' => $config['icon'],
                    'priority' => $config['priority'],
                    'time' => Carbon::parse($leave->approved_at)->diffForHumans(),
                    'url' => '/leave-application?view=' . $leave->id,
                    'created_at' => $leave->approved_at,
                    'data' => [
                        'leave_id' => $leave->id, 
                        'status' => $leave->status,
                        'redirect_type' => 'leave_application'
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error getting user leave notifications: ' . $e->getMessage());
        }

        return $notifications;
    }

    /**
     * NEW: Method untuk membersihkan notifikasi yang tidak valid secara otomatis
     */
    private function cleanupInvalidNotifications(): void
    {
        try {
            $readNotifications = Session::get('read_notifications', []);
            $validNotifications = [];
            
            foreach ($readNotifications as $notifId) {
                $isValid = true;
                
                // Check stock notifications
                if (strpos($notifId, 'out_of_stock_') === 0 || strpos($notifId, 'low_stock_') === 0) {
                    $itemId = str_replace(['out_of_stock_', 'low_stock_'], '', $notifId);
                    $isValid = DB::table('items')
                        ->where('id', $itemId)
                        ->where('active', 'true')
                        ->whereNotNull('name')
                        ->where('name', '!=', '')
                        ->exists();
                }
                
                // Check leave notifications
                elseif (strpos($notifId, 'pending_leave_') === 0) {
                    $leaveId = str_replace('pending_leave_', '', $notifId);
                    $isValid = DB::table('leave_applications')
                        ->where('id', $leaveId)
                        ->exists();
                }
                
                // Check leave status notifications
                elseif (strpos($notifId, 'leave_status_') === 0) {
                    $leaveId = str_replace('leave_status_', '', $notifId);
                    $isValid = DB::table('leave_applications')
                        ->where('id', $leaveId)
                        ->exists();
                }
                
                if ($isValid) {
                    $validNotifications[] = $notifId;
                } else {
                    Log::info("Removed invalid notification: {$notifId}");
                }
            }
            
            // Update session dengan notifikasi yang valid saja
            Session::put('read_notifications', $validNotifications);
            
        } catch (\Exception $e) {
            Log::error('Error cleaning up invalid notifications: ' . $e->getMessage());
        }
    }

    /**
     * IMPROVED: Method untuk membersihkan notifikasi item yang sudah dihapus
     */
    public function cleanupDeletedItems(): JsonResponse
    {
        try {
            $this->cleanupInvalidNotifications();
            
            return response()->json([
                'success' => true,
                'message' => 'Cleaned up invalid notifications'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cleaning up notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to cleanup notifications'
            ], 500);
        }
    }

    // Semua method lainnya tetap sama...
    public function markAsRead(Request $request): JsonResponse
    {
        try {
            $notificationId = $request->input('notification_id');
            $markAll = $request->input('mark_all', false);

            $readNotifications = Session::get('read_notifications', []);

            if ($markAll) {
                // Get all current notification IDs and mark them as read
                $allNotifications = $this->getNotifications();
                $allIds = [];
                
                if ($allNotifications->getData()->success) {
                    foreach ($allNotifications->getData()->notifications as $notif) {
                        $allIds[] = $notif->id;
                    }
                }
                
                Session::put('read_notifications', array_unique(array_merge($readNotifications, $allIds)));
                
                return response()->json([
                    'success' => true,
                    'message' => 'All notifications marked as read'
                ]);
            } else {
                // Mark single notification as read
                if (!in_array($notificationId, $readNotifications)) {
                    $readNotifications[] = $notificationId;
                    Session::put('read_notifications', $readNotifications);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    public function clearRead(): JsonResponse
    {
        Session::forget('read_notifications');
        
        return response()->json([
            'success' => true,
            'message' => 'All read notifications cleared'
        ]);
    }

    public function getCounts(): JsonResponse
    {
        try {
            $result = $this->getNotifications();
            $data = $result->getData();

            return response()->json([
                'success' => true,
                'unread_count' => $data->unread_count ?? 0,
                'total_count' => $data->total_count ?? 0,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
                'total_count' => 0
            ]);
        }
    }

    public function getSummary(): JsonResponse
    {
        try {
            $summary = [];

            // Stock summary (for admins) - WITH VALIDATION
            if ($this->isAdmin()) {
                $stockSummary = DB::table('items')
                    ->selectRaw('
                        COUNT(*) as total_items,
                        SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                        SUM(CASE WHEN quantity > 0 AND quantity <= 3 THEN 1 ELSE 0 END) as low_stock,
                        SUM(CASE WHEN quantity > 3 THEN 1 ELSE 0 END) as normal_stock
                    ')
                    ->where('active', 'true')
                    ->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->where('name', 'NOT LIKE', '%deleted%') // ADDED
                    ->whereNull('deleted_at') // ADDED
                    ->first();

                $summary['stock'] = [
                    'total_items' => $stockSummary->total_items ?? 0,
                    'out_of_stock' => $stockSummary->out_of_stock ?? 0,
                    'low_stock' => $stockSummary->low_stock ?? 0,
                    'normal_stock' => $stockSummary->normal_stock ?? 0
                ];

                // Leave summary for admin
                $leaveSummary = DB::table('leave_applications')
                    ->selectRaw('
                        COUNT(*) as total_applications,
                        SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count,
                        SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_count
                    ')
                    ->whereNull('deleted_at') // ADDED
                    ->first();

                $summary['leave_admin'] = [
                    'total_applications' => $leaveSummary->total_applications ?? 0,
                    'pending_count' => $leaveSummary->pending_count ?? 0,
                    'approved_count' => $leaveSummary->approved_count ?? 0,
                    'rejected_count' => $leaveSummary->rejected_count ?? 0
                ];
            }

            // User's leave summary
            $userId = Auth::id();
            if ($userId) {
                $userLeaveSummary = DB::table('leave_applications')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at') // ADDED
                    ->selectRaw('
                        COUNT(*) as total_applications,
                        SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count,
                        SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_count
                    ')
                    ->first();

                $summary['leave_user'] = [
                    'total_applications' => $userLeaveSummary->total_applications ?? 0,
                    'pending_count' => $userLeaveSummary->pending_count ?? 0,
                    'approved_count' => $userLeaveSummary->approved_count ?? 0,
                    'rejected_count' => $userLeaveSummary->rejected_count ?? 0
                ];
            }

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get summary'
            ], 500);
        }
    }

    private function isAdmin(): bool
    {
        try {
            $user = Auth::user();
            if (!$user) return false;

            if (isset($user->role) && $user->role->name === 'admin') {
                return true;
            }

            if ($user->role_id == 1) {
                return true;
            }

            $roleCheck = DB::table('users')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->where('users.id', $user->id)
                ->where('roles.name', 'admin')
                ->exists();

            return $roleCheck;

        } catch (\Exception $e) {
            Log::error('Error checking admin status: ' . $e->getMessage());
            return false;
        }
    }

    public function test(): JsonResponse
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->getNotifications();
    }
}