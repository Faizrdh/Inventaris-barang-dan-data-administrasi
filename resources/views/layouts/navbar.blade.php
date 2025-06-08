<!-- SUPER SIMPLE WORKING NAVBAR -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
  </ul>
 
  <ul class="navbar-nav ml-auto d-flex align-items-center">
    <!-- SIMPLE Notifications -->
    <li class="nav-item dropdown mr-3">
      <a class="nav-link position-relative" data-toggle="dropdown" href="#" id="notif-bell" role="button">
        <i class="far fa-bell fa-lg"></i>
        <span class="badge badge-danger navbar-badge d-none" id="notif-count">0</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notif-dropdown">
        <div class="dropdown-item dropdown-header d-flex justify-content-between">
          <span id="notif-header">Loading...</span>
          <button type="button" class="btn btn-sm btn-outline-primary" id="mark-all-btn">
            <i class="fas fa-check-double"></i> Mark All
          </button>
        </div>
        <div class="dropdown-divider"></div>
        <div id="notif-items">
          <div class="dropdown-item text-center py-3">
            <i class="fas fa-spinner fa-spin mr-2"></i>Loading notifications...
          </div>
        </div>
        <div class="dropdown-divider"></div>
        <div class="dropdown-footer text-center bg-light">
          <a href="/laporan/stok" class="btn btn-sm btn-info mr-2">
            <i class="fas fa-chart-line mr-1"></i>Stock Report
          </a>
          <a href="/leave-validation" class="btn btn-sm btn-warning">
            <i class="fas fa-calendar-check mr-1"></i>Leave Validation
          </a>
        </div>
      </div>
    </li>
   
    <!-- User Profile -->
    <li class="nav-item dropdown">
      <div class="d-flex align-items-center" style="cursor: pointer;" data-toggle="dropdown">
        <div class="info font-weight-bold mr-2">
          <span class="d-block" style="color:#495057;">{{Auth::user()->name}}</span>
        </div>
        <div class="image">
          <img src="{{ empty(Auth::user()->image) ? asset('user.png'):asset('storage/profile/'.Auth::user()->image)}}"
               class="img-circle elevation-2"
               style="width:35px;height:35px;object-fit:cover;"
               alt="User Image">
        </div>
      </div>
      <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
        <a href="/settings/profile" class="dropdown-item">
          <i class="fas fa-user mr-2"></i> Profile
        </a>
        <div class="dropdown-divider"></div>
        <a href="/logout" class="dropdown-item">
          <i class="fas fa-sign-out-alt mr-2"></i> LogOut
        </a>
      </div>
    </li>
  </ul>
</nav>

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- SIMPLE & WORKING SCRIPT -->
<script>
$(document).ready(function() {
    console.log('🚀 Simple Notification System Starting...');
    
    // AJAX Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let isDropdownOpen = false;
    let pollTimer = null;

    // Load notifications function
    function loadNotifications() {
        console.log('📡 Loading notifications...');
        
        $.ajax({
            url: '/notifications',  // Fixed URL - no /get suffix
            method: 'GET',
            timeout: 15000,
            success: function(response) {
                console.log('✅ Success:', response);
                updateUI(response);
            },
            error: function(xhr, status, error) {
                console.error('❌ Error:', xhr.status, error);
                handleError(xhr.status, error);
            }
        });
    }

    // Update UI with notifications
    function updateUI(data) {
        if (!data.success) {
            showError('Server error: ' + (data.error || 'Unknown error'));
            return;
        }

        const notifications = data.notifications || [];
        const unreadCount = data.unread_count || 0;
        
        console.log('📊 Notifications:', notifications.length, 'Unread:', unreadCount);

        // Update badge
        updateBadge(unreadCount);
        
        // Update header
        const headerText = unreadCount > 0 ? `${unreadCount} New` : `${notifications.length} Total`;
        $('#notif-header').text(headerText);

        // Update notifications list
        updateNotificationsList(notifications);
    }

    // Update badge count
    function updateBadge(count) {
        const $badge = $('#notif-count');
        
        if (count > 0 && !isDropdownOpen) {
            $badge.text(count > 99 ? '99+' : count).removeClass('d-none').show();
        } else {
            $badge.addClass('d-none').hide();
        }
    }

    // Update notifications list
    function updateNotificationsList(notifications) {
        const $container = $('#notif-items');
        
        if (notifications.length === 0) {
            $container.html(`
                <div class="dropdown-item text-center py-4">
                    <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0">No notifications</p>
                </div>
            `);
            return;
        }

        let html = '';
        notifications.forEach(function(notif) {
            const bgClass = notif.is_read ? 'bg-light' : 'bg-white';
            const borderColor = getNotificationColor(notif.type);
            const newBadge = notif.is_read ? '' : '<small class="badge badge-primary ml-auto">New</small>';
            
            html += `
                <div class="dropdown-item notif-item ${bgClass}" 
                     data-id="${notif.id}" 
                     data-url="${notif.url || '#'}" 
                     style="cursor: pointer; border-left: 4px solid ${borderColor}; min-height: 60px;">
                    <div class="d-flex">
                        <div class="mr-3 mt-1">
                            <i class="${notif.icon}" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong class="text-sm text-dark">${escapeHtml(notif.title)}</strong>
                                ${newBadge}
                            </div>
                            <p class="text-xs mb-1 text-muted">${escapeHtml(notif.message)}</p>
                            <small class="text-muted">${notif.time}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $container.html(html);
    }

    // Get notification color by type
    function getNotificationColor(type) {
        const colors = {
            'out_of_stock': '#dc3545',    // red
            'low_stock': '#ffc107',       // yellow  
            'pending_leave': '#17a2b8',   // info
            'leave_status': '#28a745'     // green
        };
        return colors[type] || '#007bff'; // default blue
    }

    // Handle errors
    function handleError(status, error) {
        let message = 'Connection error';
        
        if (status === 404) {
            message = 'Notification endpoint not found. Please check routes.';
        } else if (status === 500) {
            message = 'Server error. Please check logs.';
        } else if (status === 0) {
            message = 'Network error. Please check connection.';
        }
        
        showError(message);
    }

    // Show error in notifications
    function showError(message) {
        $('#notif-items').html(`
            <div class="dropdown-item text-center text-danger py-3">
                <i class="fas fa-exclamation-triangle mb-2"></i>
                <p class="mb-0">${message}</p>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="window.location.reload()">
                    Refresh Page
                </button>
            </div>
        `);
        $('#notif-header').text('Error');
    }

    // Mark notification as read
    function markAsRead(notificationId) {
        $.post('/notifications/mark-read', {
            notification_id: notificationId,
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            if (response.success) {
                console.log('✅ Marked as read:', notificationId);
                // Refresh notifications after short delay
                setTimeout(loadNotifications, 500);
            }
        }).fail(function(xhr) {
            console.error('❌ Error marking as read:', xhr);
        });
    }

    // Mark all as read
    function markAllAsRead() {
        const $btn = $('#mark-all-btn');
        const originalHtml = $btn.html();
        
        $btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        $.post('/notifications/mark-read', {
            mark_all: true,
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            if (response.success) {
                $btn.html('<i class="fas fa-check text-success"></i> Done');
                
                // Refresh after 1 second
                setTimeout(function() {
                    loadNotifications();
                    $btn.html(originalHtml).prop('disabled', false);
                }, 1000);
            }
        }).fail(function(xhr) {
            console.error('❌ Error marking all as read:', xhr);
            $btn.html('<i class="fas fa-times text-danger"></i> Error');
            
            setTimeout(function() {
                $btn.html(originalHtml).prop('disabled', false);
            }, 2000);
        });
    }

    // Event listeners
    $('#notif-bell').on('click', function(e) {
        e.preventDefault();
        
        if (!$('#notif-dropdown').hasClass('show')) {
            console.log('🔔 Opening notification dropdown...');
            isDropdownOpen = true;
            updateBadge(0); // Hide badge when opened
            loadNotifications();
        } else {
            isDropdownOpen = false;
        }
    });

    // Close dropdown detection
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.nav-item.dropdown').length) {
            isDropdownOpen = false;
        }
    });

    // Mark all button click
    $('#mark-all-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        markAllAsRead();
    });

    // Notification item click
    $(document).on('click', '.notif-item', function(e) {
        const notificationId = $(this).data('id');
        const url = $(this).data('url');
        
        console.log('🔔 Clicked notification:', notificationId, url);
        
        // Mark as read
        markAsRead(notificationId);
        
        // Navigate if URL provided
        if (url && url !== '#') {
            setTimeout(function() {
                window.location.href = url;
            }, 300);
        }
    });

    // Escape HTML to prevent XSS
    function escapeHtml(unsafe) {
        return $('<div>').text(unsafe || '').html();
    }

    // Start periodic polling
    function startPolling() {
        // Clear existing timer
        if (pollTimer) {
            clearInterval(pollTimer);
        }
        
        // Poll every 30 seconds
        pollTimer = setInterval(function() {
            if (!document.hidden) {
                loadNotifications();
            }
        }, 30000);
        
        console.log('🔄 Polling started (30s interval)');
    }

    // Page visibility change handler
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            loadNotifications();
        }
    });

    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        if (pollTimer) {
            clearInterval(pollTimer);
        }
    });

    // Initialize
    console.log('🔧 Initializing notification system...');
    loadNotifications();  // Initial load
    startPolling();       // Start periodic updates
    
    console.log('✅ Simple Notification System Ready!');
});
</script>

<!-- Simple CSS -->
<style>
#notif-count {
    position: absolute;
    top: -8px;
    right: -8px;
    min-width: 18px;
    height: 18px;
    line-height: 16px;
    font-size: 10px;
    border-radius: 50%;
    text-align: center;
    font-weight: bold;
}

#notif-dropdown {
    min-width: 350px;
    max-height: 400px;
    overflow-y: auto;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.notif-item {
    transition: background-color 0.2s ease;
    margin-bottom: 1px;
}

.notif-item:hover {
    background-color: #f8f9fa !important;
}

.dropdown-footer {
    padding: 10px;
}

/* Custom scrollbar */
#notif-dropdown::-webkit-scrollbar {
    width: 6px;
}

#notif-dropdown::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#notif-dropdown::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}
</style>