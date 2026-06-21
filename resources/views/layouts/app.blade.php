<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - FINSYNC ERP</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/finsync.css') }}">
</head>
<body>

<div class="fs-wrapper">
    <!-- Sidebar -->
    <aside class="fs-sidebar shadow-sm">
        <div class="fs-sidebar-brand">
            <i class="fa-solid fa-chart-line text-success"></i> FINSYNC <span class="fs-badge fs-badge-success ms-2" style="font-size:0.6rem;">PRO</span>
        </div>
        
        <div class="fs-sidebar-menu mt-4">
            <span class="fs-sidebar-heading">Menu Utama</span>
            <a href="{{ route('dashboard') }}" class="fs-sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-home"></i> Dashboard
            </a>
            
            <span class="fs-sidebar-heading mt-4">Keuangan</span>
            <a href="{{ route('cashbank.index') }}" class="fs-sidebar-item {{ request()->routeIs('cashbank.*') ? 'active' : '' }}">
                <i class="fa-solid fa-wallet"></i> Kas & Bank
            </a>
            <a href="{{ route('journals.index') }}" class="fs-sidebar-item {{ request()->routeIs('journals.*') ? 'active' : '' }}">
                <i class="fa-solid fa-book-open"></i> Jurnal Umum
            </a>
            <a href="{{ route('ar.invoices.index') }}" class="fs-sidebar-item {{ request()->routeIs('ar.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar"></i> Piutang (AR)
            </a>
            <a href="{{ route('ap.invoices.index') }}" class="fs-sidebar-item {{ request()->routeIs('ap.*') ? 'active' : '' }}">
                <i class="fa-solid fa-money-bill-transfer"></i> Hutang (AP)
            </a>
            <a href="{{ route('assets.index') }}" class="fs-sidebar-item {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                <i class="fa-solid fa-building"></i> Aset Tetap
            </a>
            <a href="{{ route('budgets.index') }}" class="fs-sidebar-item {{ request()->routeIs('budgets.*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Anggaran
            </a>

            <span class="fs-sidebar-heading mt-4">Master Data</span>
            <a href="{{ route('coa.index') }}" class="fs-sidebar-item {{ request()->routeIs('coa.*') ? 'active' : '' }}">
                <i class="fa-solid fa-sitemap"></i> Chart of Accounts
            </a>
            <a href="#" class="fs-sidebar-item">
                <i class="fa-solid fa-users"></i> Kontak (Pelanggan/Supplier)
            </a>
            
            <span class="fs-sidebar-heading mt-4">Sistem</span>
            <a href="#" class="fs-sidebar-item text-danger mt-auto" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="fs-main-content">
        <!-- Topbar -->
        <nav class="fs-topbar d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">@yield('title')</h4>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Selamat datang kembali, kelola data Anda dengan mudah.</p>
            </div>
            <div class="d-flex align-items-center gap-4">
                <button class="btn btn-light rounded-circle shadow-sm" style="width: 42px; height: 42px; position: relative; border: none;">
                    <i class="fa-solid fa-bell text-secondary"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </button>
                <div class="d-flex align-items-center gap-3">
                    <div class="d-none d-md-block text-end">
                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ auth()->user()->name ?? 'Administrator' }}</div>
                        <div class="text-secondary" style="font-size: 0.75rem;">{{ auth()->user()->email ?? 'admin@gmail.com' }}</div>
                    </div>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=0F172A&color=fff&rounded=true" alt="User" width="42" class="shadow-sm">
                </div>
            </div>
        </nav>

        <!-- Actions Row -->
        @hasSection('actions')
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div></div>
            <div>
                @yield('actions')
            </div>
        </div>
        @endif

        <!-- Page Content -->
        @yield('content')

    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/fs-alert.js') }}"></script>
</body>
</html>
