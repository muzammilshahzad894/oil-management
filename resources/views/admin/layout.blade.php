<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Oil Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            color: #2d3748;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 1.5rem 1.25rem;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-logo {
            width: 45px;
            height: 45px;
            background: var(--primary-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .sidebar-title {
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            white-space: nowrap;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 0.25rem 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
        }
        
        .sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-gradient);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .sidebar-menu a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            padding-left: 1.5rem;
        }
        
        .sidebar-menu a.active {
            color: white;
            background: rgba(102, 126, 234, 0.2);
            padding-left: 1.5rem;
        }
        
        .sidebar-menu a.active::before {
            transform: scaleY(1);
        }
        
        .sidebar-menu a i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
            text-align: center;
        }
        
        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        /* Top Header */
        .top-header {
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-left h4 {
            margin: 0;
            font-weight: 600;
            color: #2d3748;
            font-size: 1.5rem;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-menu-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: #f7fafc;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #2d3748;
        }
        
        .user-menu-btn:hover {
            background: #edf2f7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .user-info {
            text-align: left;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: #718096;
            margin: 0;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0.5rem;
            margin-top: 0.5rem;
            min-width: 200px;
        }
        
        .dropdown-item {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .dropdown-item:hover {
            background: #f7fafc;
            transform: translateX(5px);
        }
        
        .dropdown-item i {
            width: 20px;
            color: #667eea;
        }
        
        /* Content Area */
        .content-area {
            padding: 2rem;
            padding-right: 2rem;
        }
        
        @media (max-width: 992px) {
            .content-area {
                padding: 1rem;
            }
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 0 !important;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .card-header i {
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 0.625rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-light {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-light:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 8px;
        }
        
        .btn-info, .btn-warning, .btn-danger, .btn-success {
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-info:hover, .btn-warning:hover, .btn-danger:hover, .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-secondary {
            background: #718096;
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #4a5568;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(113, 128, 150, 0.3);
        }
        
        /* Tables */
        .table {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead {
            background: var(--primary-gradient);
            color: white;
        }
        
        .table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e2e8f0;
        }
        
        .table tbody tr:hover {
            background: #f7fafc;
        }
        
        /* Badges */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        /* Alerts */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid #22c55e;
        }
        
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            color: white;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0.5rem 0;
        }
        
        .stat-card .stat-label {
            color: #718096;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Forms */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-label {
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }
        
        /* Remove number input spinners */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        input[type=number] {
            -moz-appearance: textfield;
        }
        
        /* Action buttons - smaller and better looking */
        .btn-sm {
            padding: 0.375rem 0.625rem;
            font-size: 0.75rem;
            line-height: 1.2;
            border-radius: 6px;
            min-width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-sm i {
            font-size: 0.875rem;
        }
        
        /* Back button alignment */
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-secondary i {
            line-height: 1;
        }
        
        /* Modal z-index fix */
        .modal {
            z-index: 1060 !important;
        }
        
        .modal-backdrop {
            z-index: 1050 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
        
        .modal-dialog {
            z-index: 1061 !important;
        }
        
        .modal-content {
            z-index: 1062 !important;
            position: relative;
        }
        
        /* Ensure modals appear above everything */
        body.modal-open {
            overflow: hidden;
        }
        
        .modal.show {
            display: block !important;
        }
        
        /* Date input styling */
        input[type="date"] {
            cursor: pointer;
            position: relative;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: auto;
            height: auto;
            color: transparent;
            background: transparent;
        }
        
        input[type="date"]::-webkit-inner-spin-button,
        input[type="date"]::-webkit-clear-button {
            z-index: 1;
        }
        
        /* Ensure filter buttons same height */
        .row.align-items-end .btn {
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Fixed height for searchable dropdowns */
        #customer_dropdown,
        #brand_dropdown {
            max-height: 250px !important;
            height: auto;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 9999 !important;
            position: absolute !important;
        }
        
        .dropdown-menu {
            max-height: 250px !important;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 9999 !important;
        }
        
        /* Ensure dropdowns appear above cards */
        .position-relative {
            z-index: 1;
        }
        
        .position-relative #customer_dropdown,
        .position-relative #brand_dropdown {
            z-index: 9999 !important;
        }
        
        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-wrapper {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block !important;
            }
            
            .content-area {
                padding: 1rem;
            }
        }
        
        @media (min-width: 993px) {
            .mobile-menu-btn {
                display: none !important;
            }
        }
        
        .mobile-menu-btn {
            background: var(--primary-gradient);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
        
        /* Loading Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .content-area > * {
            animation: fadeIn 0.3s ease;
        }
        
        /* List Group Improvements */
        .list-group-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px !important;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .list-group-item:hover {
            background: #f7fafc;
            transform: translateX(5px);
        }
        
        /* Modal Improvements */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem;
        }
        
        /* Fix modal blinking issue */
        .modal {
            pointer-events: auto;
        }
        
        .modal-backdrop {
            pointer-events: none;
        }
        
        .modal-dialog {
            pointer-events: auto;
        }
        
        .modal-content {
            pointer-events: auto;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #718096;
            font-size: 1.1rem;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-oil-can"></i>
            </div>
            <h4 class="sidebar-title">Oil Management</h4>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.brands.index') }}" class="{{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i>
                    <span>Brands</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.inventory.index') }}" class="{{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.sales.index') }}" class="{{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sales</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.customer') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <div class="top-header">
            <div class="header-left d-flex align-items-center gap-3">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <h4>@yield('title', 'Dashboard')</h4>
            </div>
            
            <div class="header-right">
                <div class="user-dropdown">
                    <a href="#" class="user-menu-btn" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <p class="user-name">{{ Auth::user()->name }}</p>
                            <p class="user-role">Administrator</p>
                        </div>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                                <i class="fas fa-user"></i>
                                <span>Profile Settings</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Mobile menu toggle
            $('#mobileMenuBtn').on('click', function() {
                $('#sidebar, #sidebarOverlay').toggleClass('show');
            });
            
            $('#sidebarOverlay').on('click', function() {
                $('#sidebar, #sidebarOverlay').removeClass('show');
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').each(function() {
                    const bsAlert = new bootstrap.Alert(this);
                    bsAlert.close();
                });
            }, 5000);
            
            // Common form loading indicator
            $('form').on('submit', function() {
                const $submitBtn = $(this).find('button[type="submit"], #submitBtn');
                if ($submitBtn.length) {
                    $submitBtn.prop('disabled', true);
                    $submitBtn.find('.spinner-border').removeClass('d-none');
                    const $btnText = $submitBtn.find('.btn-text');
                    if ($btnText.length) {
                        $btnText.text($btnText.data('loading-text') || 'Saving...');
                    }
                }
            });
            
            // Reinitialize tooltips after AJAX content updates
            $(document).on('DOMSubtreeModified', function() {
                $('[data-bs-toggle="tooltip"]').each(function() {
                    if (!$(this).attr('data-bs-original-title')) {
                        new bootstrap.Tooltip(this);
                    }
                });
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>
