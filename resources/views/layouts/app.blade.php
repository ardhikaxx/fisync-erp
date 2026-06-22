<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - FINSYNC ERP</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/finsync.css') }}">
</head>
<body>

<div class="fs-wrapper">
    <!-- Sidebar -->
    <aside class="fs-sidebar">
        <div class="fs-sidebar-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div class="brand-text">
                <span class="brand-title">FINSYNC</span>
                <span class="brand-subtitle">Enterprise ERP</span>
            </div>
        </div>
        
        <div class="fs-sidebar-menu mt-2">
            <span class="fs-sidebar-heading">Menu Utama</span>
            <a href="{{ route('dashboard') }}" class="fs-sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            
            <span class="fs-sidebar-heading mt-3">Keuangan</span>
            <a href="{{ route('cashbank.index') }}" class="fs-sidebar-item {{ request()->routeIs('cashbank.*') ? 'active' : '' }}">
                <i class="fa-solid fa-building-columns"></i> Kas & Bank
            </a>
            <a href="{{ route('journals.index') }}" class="fs-sidebar-item {{ request()->routeIs('journals.*') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i> Buku Besar (GL)
            </a>
            <a href="{{ route('ar.invoices.index') }}" class="fs-sidebar-item {{ request()->routeIs('ar.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar"></i> Piutang (AR)
            </a>
            <a href="{{ route('ap.invoices.index') }}" class="fs-sidebar-item {{ request()->routeIs('ap.*') ? 'active' : '' }}">
                <i class="fa-solid fa-money-bill-transfer"></i> Hutang (AP)
            </a>
            <a href="{{ route('assets.index') }}" class="fs-sidebar-item {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                <i class="fa-solid fa-warehouse"></i> Aset Tetap
            </a>
            <a href="{{ route('budgets.index') }}" class="fs-sidebar-item {{ request()->routeIs('budgets.*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Anggaran
            </a>

            <span class="fs-sidebar-heading mt-3">Laporan (Reports)</span>
            <a href="{{ route('reports.general_ledger') }}" class="fs-sidebar-item {{ request()->routeIs('reports.general_ledger') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i> Buku Besar
            </a>
            <a href="{{ route('reports.income_statement') }}" class="fs-sidebar-item {{ request()->routeIs('reports.income_statement') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar"></i> Laba Rugi
            </a>
            <a href="{{ route('reports.balance_sheet') }}" class="fs-sidebar-item {{ request()->routeIs('reports.balance_sheet') ? 'active' : '' }}">
                <i class="fa-solid fa-scale-balanced"></i> Neraca
            </a>

            <span class="fs-sidebar-heading mt-3">Master Data</span>
            <a href="{{ route('coa.index') }}" class="fs-sidebar-item {{ request()->routeIs('coa.*') ? 'active' : '' }}">
                <i class="fa-solid fa-sitemap"></i> Chart of Accounts
            </a>
            <a href="{{ route('customers.index') }}" class="fs-sidebar-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> Pelanggan (Customers)
            </a>
            <a href="{{ route('suppliers.index') }}" class="fs-sidebar-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="fa-solid fa-truck-field"></i> Supplier (Vendor)
            </a>
            
            <span class="fs-sidebar-heading mt-3">Sistem</span>
            <a href="{{ route('roles.index') }}" class="fs-sidebar-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users-gear"></i> Hak Akses (Role)
            </a>
            <a href="{{ route('periods.index') }}" class="fs-sidebar-item {{ request()->routeIs('periods.*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check"></i> Tutup Buku
            </a>
            <a href="#" class="fs-sidebar-item text-danger mt-auto" onclick="event.preventDefault(); FSAlert.konfirmasiLogout(() => document.getElementById('logout-form').submit());">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="fs-main-content">
        <!-- Topbar -->
        <nav class="fs-topbar">
            <!-- Left space for branch switcher/search later -->
            <div class="d-flex align-items-center">
                <!-- Search can go here -->
            </div>
            
            <div class="d-flex align-items-center gap-4">
                <div class="position-relative cursor-pointer">
                    <i class="fa-solid fa-bell text-secondary fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </div>
                <div class="d-flex align-items-center gap-3" style="cursor: pointer;">
                    <div class="d-none d-md-block text-end">
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ auth()->user()->name ?? 'Administrator' }}</div>
                        <div class="text-secondary" style="font-size: 0.75rem;">{{ auth()->user()->email ?? 'Finance Dept' }}</div>
                    </div>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=E8F2FB&color=2C7EC9&rounded=true" alt="User" width="38" class="shadow-sm">
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <div class="fs-page-header">
            <div>
                <h4>@yield('title')</h4>
                <p>@yield('subtitle', 'Kelola data ' . strtolower(View::getSection('title', 'modul')) . ' secara akurat.')</p>
            </div>
            @hasSection('actions')
            <div class="d-flex gap-2">
                @yield('actions')
            </div>
            @endif
        </div>

        <!-- Content Body -->
        <div class="fs-content-body">
            @yield('content')
        </div>

    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/fs-alert.js') }}"></script>
</body>
</html>
