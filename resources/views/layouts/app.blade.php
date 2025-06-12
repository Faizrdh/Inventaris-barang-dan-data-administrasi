<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventaris Yankes</title>
     <link rel="icon" type="image/png" href="{{ asset('yankes.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('yankes.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('yankes.png') }}">
    <!-- Vite app.css -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('theme/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="{{asset('theme/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{asset('theme/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <!-- JQVMap -->
  <link rel="stylesheet" href="{{asset('theme/plugins/jqvmap/jqvmap.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('theme/dist/css/adminlte.min.css')}}">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{asset('theme/plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{asset('theme/plugins/daterangepicker/daterangepicker.css')}}">
  <!-- summernote -->
  <link rel="stylesheet" href="{{asset('theme/plugins/summernote/summernote-bs4.min.css')}}">
  <!-- sweetalert -->
  <link rel="stylesheet" href="{{asset('theme/alert/css/sweetalert2.css')}}">
  <!-- Animate.css for notifications -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  
  <!-- jQuery -->
  <script src="{{asset('theme/plugins/jquery/jquery.min.js')}}"></script>
  <!-- jQuery UI 1.11.4 -->
  <script src="{{asset('theme/plugins/jquery-ui/jquery-ui.min.js')}}"></script>
  <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
  <script>
    $.widget.bridge('uibutton', $.ui.button)
  </script>
  <!-- sweetalert -->
  <script src="{{asset('theme/alert/js/sweetalert2.js')}}"></script>
  
  <link rel="stylesheet" href="{{asset('theme/dist/css/switch.css')}}">
  <link rel="stylesheet" href="{{ asset("localizations/flags.css") }}">
  
  <style>
    
    /* CSS untuk Notifikasi */
    .navbar-badge {
        font-size: 0.6rem;
        font-weight: bold;
        min-width: 18px;
        height: 18px;
        line-height: 16px;
        text-align: center;
        border-radius: 50%;
        position: absolute;
        top: -8px;
        right: -8px;
    }

    /* Navbar alignment fixes */
    .navbar-nav .nav-item {
        display: flex;
        align-items: center;
    }

    .notification-bell-link {
        padding: 0.5rem 0.75rem !important;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 40px;
        position: relative;
    }

    .user-profile-container {
        padding: 5px 10px;
        border-radius: 25px;
        transition: background-color 0.3s ease;
        height: 40px;
        display: flex;
        align-items: center;
    }

    .user-profile-container:hover {
        background-color: rgba(0,0,0,0.05);
    }

    .notification-item {
        padding: 10px 15px !important;
        border-bottom: 1px solid #f4f4f4;
        transition: all 0.3s ease;
    }

    .notification-item:hover {
        background-color: #f8f9fa !important;
        text-decoration: none;
        transform: translateX(2px);
    }

    .urgent-notification {
        background-color: #fff5f5 !important;
        border-left: 4px solid #dc3545 !important;
    }

    .urgent-notification:hover {
        background-color: #ffeaea !important;
    }

    .notification-content {
        display: block;
        line-height: 1.4;
    }

    .notification-content strong {
        color: #495057;
        font-size: 0.9rem;
    }

    .notification-content small {
        display: block;
        margin-top: 2px;
    }

    #notification-dropdown {
        min-width: 320px;
        max-height: 450px;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .dropdown-menu-lg {
        min-width: 300px;
    }

    /* Enhanced badge animations */
    @keyframes pulse-notification {
        0% { 
            transform: scale(1); 
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        50% { 
            transform: scale(1.1); 
            box-shadow: 0 0 0 5px rgba(220, 53, 69, 0);
        }
        100% { 
            transform: scale(1); 
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    .badge.animate__pulse {
        animation: pulse-notification 1.5s infinite;
    }

    .badge-danger {
        background-color: #dc3545 !important;
    }

    .badge-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }

    .badge-info {
        background-color: #17a2b8 !important;
    }

    /* Icon colors */
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #17a2b8 !important; }
    .text-success { color: #28a745 !important; }

    /* Loading and error states */
    .notification-loading {
        text-align: center;
        color: #6c757d;
        font-style: italic;
    }

    /* Notification bell icon hover effect */
    #notification-toggle {
        position: relative;
        transition: all 0.3s ease;
    }

    #notification-toggle:hover {
        transform: rotate(15deg);
        color: #007bff !important;
    }

    /* Smooth dropdown animation */
    .dropdown-menu {
        animation: dropdownFadeIn 0.3s ease-out;
    }

    @keyframes dropdownFadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Stock dashboard widget animations */
    .small-box {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .small-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .animate__pulse {
        animation: pulse-notification 1s infinite;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .user-profile-container .info {
            display: none;
        }
        
        #notification-dropdown {
            min-width: 280px;
            max-width: 95vw;
        }
    }

    /* Fix untuk AdminLTE navbar */
    .main-header.navbar .navbar-nav .nav-link {
        height: auto;
        padding: 0.5rem 0.75rem;
    }

    .main-header.navbar .navbar-nav .nav-item {
        display: flex;
        align-items: center;
    }

    /* User info text styling */
    .user-profile-container .info span {
        font-size: 0.9rem;
        white-space: nowrap;
    }

    /* Profile image consistent sizing */
    .user-profile-container .image img {
        width: 35px !important;
        height: 35px !important;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">
 <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img  src="{{asset('loading.gif')}}" alt="loading" height="60" width="60">
  </div>

    @include('layouts.navbar')
    @include('layouts.sidebar')
        <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold">@yield('title')</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

         <!-- Main content -->
        <section class="content text-capitalize">
            @yield('content')
        </section>
    </div>
    <!-- /.content-wrapper -->
    @include('layouts.footer')
</div>
<!-- ./wrapper -->

<!-- Bootstrap 4 -->
<script src="{{asset('theme/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- ChartJS -->
<script src="{{asset('theme/plugins/chart.js/Chart.min.js')}}"></script>
<!-- Sparkline -->
<script src="{{asset('theme/plugins/sparklines/sparkline.js')}}"></script>
<!-- JQVMap -->
<script src="{{asset('theme/plugins/jqvmap/jquery.vmap.min.js')}}"></script>
<script src="{{asset('theme/plugins/jqvmap/maps/jquery.vmap.usa.js')}}"></script>
<!-- jQuery Knob Chart -->
<script src="{{asset('theme/plugins/jquery-knob/jquery.knob.min.js')}}"></script>
<!-- daterangepicker -->
<script src="{{asset('theme/plugins/moment/moment.min.js')}}"></script>
<script src="{{asset('theme/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{asset('theme/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>
<!-- Summernote -->
<script src="{{asset('theme/plugins/summernote/summernote-bs4.min.js')}}"></script>
<!-- overlayScrollbars -->
<script src="{{asset('theme/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('theme/dist/js/adminlte.js')}}"></script>

<script>
  function changeLanguage(lang) {
    let url = new URL(window.location.href);
    url.searchParams.set("lang", lang);
    window.location.href = url.toString();
  }
  
  $(document).ready(async () => {
    let languages = await (await fetch("{{ url(asset('localizations/languages.json')) }}")).json();
    for (let code in languages) {
      let native = languages[code].nameNative;
      let english = languages[code].nameEnglish;

      $("#lang-dropdown").append(`
        <li onclick="changeLanguage('${ code }')" class="d-flex align-items-center justify-content-start gap-2 px-2">
          <div class="lang-icon lang-icon-${ code }"></div>
          <span class="ml-2 text-uppercase" style="font-size: .8rem" data-text="${ english }">${ code }</span>
        </li>
      `);
    }

    // Initialize Notification System
    loadNotifications();
    setInterval(loadNotifications, 30000); // Auto refresh every 30 seconds
    
    $('#notification-toggle').on('click', function(e) {
        e.preventDefault();
        loadNotifications();
    });
  });

  // Notification System Functions
  let lastNotificationCount = 0;
  
  function loadNotifications() {
    $.ajax({
        url: '/notifications',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Check if there are new notifications
                if (response.total_count > lastNotificationCount && lastNotificationCount > 0) {
                    playNotificationSound();
                    showNotificationToast(response.notifications[0]); // Show latest notification
                }
                
                lastNotificationCount = response.total_count;
                updateNotificationUI(response.notifications, response.total_count);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading notifications:', error);
            // Show error state in UI
            $('#notification-items').html(`
                <a href="#" class="dropdown-item text-center text-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Error loading notifications
                </a>
            `);
        }
    });
  }

  function updateNotificationUI(notifications, totalCount) {
    const badge = $('#notification-count');
    const header = $('#notification-header');
    
    if (totalCount > 0) {
        badge.text(totalCount).show();
        header.text(totalCount + ' Notifications');
        
        // Different badge colors based on notification types
        badge.removeClass('badge-warning badge-danger badge-info');
        const hasOutOfStock = notifications.some(n => n.type === 'out_of_stock');
        const hasLowStock = notifications.some(n => n.type === 'low_stock');
        
        if (hasOutOfStock) {
            badge.addClass('badge-danger animate__animated animate__pulse animate__infinite');
        } else if (hasLowStock) {
            badge.addClass('badge-warning animate__animated animate__pulse animate__infinite');
        } else {
            badge.addClass('badge-info');
        }
    } else {
        badge.hide();
        header.text('No Notifications');
        badge.removeClass('animate__animated animate__pulse animate__infinite badge-warning badge-danger badge-info');
    }
    
    const container = $('#notification-items');
    container.empty();
    
    if (notifications.length === 0) {
        container.html(`
            <a href="#" class="dropdown-item text-center text-muted">
                <i class="fas fa-check-circle mr-2 text-success"></i>All clear! No notifications
            </a>
        `);
    } else {
        notifications.slice(0, 5).forEach(function(notification, index) {
            const iconClass = notification.icon;
            const isUrgent = notification.type === 'out_of_stock';
            const itemClass = isUrgent ? 'notification-item urgent-notification' : 'notification-item';
            
            const notificationHtml = `
                <a href="${notification.url}" class="dropdown-item ${itemClass}" data-id="${notification.id}" data-type="${notification.type}">
                    <div class="d-flex align-items-start">
                        <i class="${iconClass} mr-2 mt-1"></i>
                        <div class="notification-content flex-grow-1">
                            <strong class="d-block">${notification.title}</strong>
                            <small class="text-muted d-block">${notification.message}</small>
                            <small class="text-info d-block mt-1">
                                <i class="fas fa-clock mr-1"></i>${notification.time}
                            </small>
                        </div>
                        ${isUrgent ? '<i class="fas fa-exclamation text-danger ml-2"></i>' : ''}
                    </div>
                </a>
                ${index < notifications.slice(0, 5).length - 1 ? '<div class="dropdown-divider"></div>' : ''}
            `;
            container.append(notificationHtml);
        });
        
        if (notifications.length > 5) {
            container.append(`
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item text-center text-primary">
                    <i class="fas fa-ellipsis-h mr-2"></i>View ${notifications.length - 5} more notifications
                </a>
            `);
        }
    }
  }

  function playNotificationSound() {
    try {
        // Create notification sound
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
        oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (e) {
        console.log('Audio notification not available:', e);
    }
  }

  function showNotificationToast(notification) {
    // Create toast notification using SweetAlert2 (since it's already loaded)
    if (typeof Swal !== 'undefined') {
        const iconType = notification.type === 'out_of_stock' ? 'error' : 
                        notification.type === 'low_stock' ? 'warning' : 'info';
        
        Swal.fire({
            title: notification.title,
            text: notification.message,
            icon: iconType,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    }
  }

  $(document).on('click', '.notification-item', function(e) {
    const notificationId = $(this).data('id');
    const notificationType = $(this).data('type');
    const url = $(this).attr('href');
    
    // Mark as read
    $.ajax({
        url: '/notifications/mark-read',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            notification_id: notificationId,
            type: notificationType
        }
    });

    // Close dropdown
    $('.dropdown-menu').removeClass('show');
    
    // Add loading state to notification item
    $(this).find('i').first().removeClass().addClass('fas fa-spinner fa-spin text-primary');
    
    // Navigate to URL
    if (url && url !== '#') {
        window.location.href = url;
    } else {
        // Fallback navigation based on type
        let fallbackUrl = '/admin/laporan/stok';
        if (notificationType === 'out_of_stock' || notificationType === 'low_stock') {
            fallbackUrl += '?filter=' + notificationType;
        }
        window.location.href = fallbackUrl;
    }
  });

  // Handle "See All Notifications" click
  $(document).on('click', '.dropdown-footer', function(e) {
    e.preventDefault();
    const url = $(this).attr('href');
    
    // Add query parameter to show notification view
    const finalUrl = url + (url.includes('?') ? '&' : '?') + 'view=notifications';
    window.location.href = finalUrl;
  });

  // Auto-hide notification badge when dropdown is opened
  $('#notification-toggle').on('click', function() {
    setTimeout(() => {
        const badge = $('#notification-count');
        if (badge.is(':visible')) {
            badge.removeClass('animate__pulse animate__infinite');
        }
    }, 1000);
  });

  // Add notification categories filter
  function filterNotifications(type) {
    const container = $('#notification-items');
    const allNotifications = container.find('.notification-item');
    
    if (type === 'all') {
        allNotifications.show();
    } else {
        allNotifications.hide();
        allNotifications.filter(`[data-type="${type}"]`).show();
    }
  }

  // Keyboard navigation for notifications
  $(document).on('keydown', function(e) {
    if ($('#notification-dropdown').hasClass('show')) {
        const items = $('#notification-items .notification-item:visible');
        let current = items.filter('.active');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (current.length === 0) {
                items.first().addClass('active');
            } else {
                current.removeClass('active');
                const next = current.next('.notification-item:visible');
                if (next.length > 0) {
                    next.addClass('active');
                } else {
                    items.first().addClass('active');
                }
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (current.length === 0) {
                items.last().addClass('active');
            } else {
                current.removeClass('active');
                const prev = current.prev('.notification-item:visible');
                if (prev.length > 0) {
                    prev.addClass('active');
                } else {
                    items.last().addClass('active');
                }
            }
        } else if (e.key === 'Enter' && current.length > 0) {
            e.preventDefault();
            current.click();
        } else if (e.key === 'Escape') {
            $('#notification-dropdown').removeClass('show');
        }
    }
  });

  // Add visual feedback for active notification
  $(document).on('mouseenter', '.notification-item', function() {
    $(this).addClass('active').siblings().removeClass('active');
  });

  $(document).on('mouseleave', '#notification-items', function() {
    $('.notification-item').removeClass('active');
  });
</script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!-- <script src="{{asset('theme/dist/js/pages/dashboard.js')}}"></script> -->
<script src="//cdn.jsdelivr.net/npm/eruda"></script>
<script>eruda.init();</script>
</body>
</html>