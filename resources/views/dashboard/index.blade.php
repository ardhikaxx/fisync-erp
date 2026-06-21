@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="fs-kpi-card">
            <div class="fs-kpi-icon bg-primary-soft"><i class="fa-solid fa-wallet"></i></div>
            <div class="fs-kpi-content">
                <span class="fs-kpi-label">Total Kas & Bank</span>
                <span class="fs-kpi-value">Rp {{ number_format($totalKasBank, 0, ',', '.') }}</span>
                <span class="fs-kpi-trend text-success"><i class="fa-solid fa-arrow-up"></i> Real-time</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="fs-kpi-card">
            <div class="fs-kpi-icon bg-warning-subtle text-warning"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            <div class="fs-kpi-content">
                <span class="fs-kpi-label">Piutang Belum Tertagih</span>
                <span class="fs-kpi-value">Rp {{ number_format($piutangUnpaid, 0, ',', '.') }}</span>
                <span class="fs-kpi-trend text-warning"><i class="fa-solid fa-clock"></i> Menunggu Pembayaran</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="fs-kpi-card">
            <div class="fs-kpi-icon bg-danger-subtle text-danger"><i class="fa-solid fa-money-bill-transfer"></i></div>
            <div class="fs-kpi-content">
                <span class="fs-kpi-label">Hutang Belum Dibayar</span>
                <span class="fs-kpi-value">Rp {{ number_format($hutangUnpaid, 0, ',', '.') }}</span>
                <span class="fs-kpi-trend text-danger"><i class="fa-solid fa-exclamation-circle"></i> Perlu Perhatian</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="fs-kpi-card">
            <div class="fs-kpi-icon bg-success-subtle text-success"><i class="fa-solid fa-chart-line"></i></div>
            <div class="fs-kpi-content">
                <span class="fs-kpi-label">Laba Bersih Bulan Ini</span>
                <span class="fs-kpi-value">Rp {{ number_format($labaBersih, 0, ',', '.') }}</span>
                <span class="fs-kpi-trend text-success"><i class="fa-solid fa-arrow-up"></i> Perkiraan</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Arus Kas (Kasaran)</h5>
            </div>
            <div class="card-body">
                <canvas id="cashflowChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Invoice Jatuh Tempo</h5>
            </div>
            <div class="card-body">
                @if($recentInvoices->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentInvoices as $inv)
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center border-bottom">
                            <div>
                                <h6 class="mb-1 fw-bold">{{ $inv->invoice_number }}</h6>
                                <small class="text-muted">{{ $inv->customer->name }} &bull; Jatuh Tempo: <span class="text-danger">{{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}</span></small>
                            </div>
                            <span class="fw-bold font-mono text-danger">Rp {{ number_format($inv->balance_due, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fa-solid fa-check-circle fs-1 text-success mb-3"></i>
                        <p>Tidak ada invoice yang mendekati jatuh tempo.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('cashflowChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Arus Kas Masuk',
                    data: {!! json_encode($cashIn) !!},
                    borderColor: '#1E8E5A',
                    backgroundColor: 'rgba(30, 142, 90, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Arus Kas Keluar',
                    data: {!! json_encode($cashOut) !!},
                    borderColor: '#D32F4E',
                    backgroundColor: 'rgba(211, 47, 78, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
</script>
@endsection
