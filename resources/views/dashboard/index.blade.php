@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="fs-kpi-card">
            <div class="fs-kpi-icon icon-primary"><i class="fa-solid fa-wallet"></i></div>
            <div class="fs-kpi-content">
                <span class="fs-kpi-label">Total Kas & Bank</span>
                <span class="fs-kpi-value" style="font-size: 1.4rem;">Rp {{ number_format($totalKasBank, 0, ',', '.') }}</span>
                <span class="fs-kpi-trend text-success"><i class="fa-solid fa-arrow-up"></i> Real-time</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="fs-kpi-card">
            <div class="fs-kpi-icon icon-warning"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            <div class="fs-kpi-content">
                <span class="fs-kpi-label">Piutang Belum Tertagih</span>
                <span class="fs-kpi-value" style="font-size: 1.4rem;">Rp {{ number_format($piutangUnpaid, 0, ',', '.') }}</span>
                <span class="fs-kpi-trend text-warning"><i class="fa-solid fa-clock"></i> Menunggu Pembayaran</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="fs-kpi-card">
            <div class="fs-kpi-icon icon-danger"><i class="fa-solid fa-money-bill-transfer"></i></div>
            <div class="fs-kpi-content">
                <span class="fs-kpi-label">Hutang Belum Dibayar</span>
                <span class="fs-kpi-value" style="font-size: 1.4rem;">Rp {{ number_format($hutangUnpaid, 0, ',', '.') }}</span>
                <span class="fs-kpi-trend text-danger"><i class="fa-solid fa-exclamation-circle"></i> Perlu Perhatian</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="fs-kpi-card">
            <div class="fs-kpi-icon icon-success"><i class="fa-solid fa-chart-line"></i></div>
            <div class="fs-kpi-content">
                <span class="fs-kpi-label">Laba Bersih Bulan Ini</span>
                <span class="fs-kpi-value" style="font-size: 1.4rem;">Rp {{ number_format($labaBersih, 0, ',', '.') }}</span>
                <span class="fs-kpi-trend text-success"><i class="fa-solid fa-arrow-up"></i> Perkiraan</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0" style="color: var(--fs-text-primary); letter-spacing: -0.3px;">Arus Kas (Kasaran)</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <canvas id="cashflowChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color: var(--fs-text-primary); letter-spacing: -0.3px;">Invoice Jatuh Tempo</h5>
                <span class="badge bg-danger-subtle text-danger rounded-pill px-3">{{ $recentInvoices->count() }}</span>
            </div>
            <div class="card-body px-4 pb-4">
                @if($recentInvoices->count() > 0)
                    <div class="d-flex flex-column gap-3">
                        @foreach($recentInvoices as $inv)
                        <div class="p-3 rounded-3" style="background: #fdfdfd; border: 1px solid rgba(13,115,119,0.05); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.03)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary-soft text-primary fw-semibold" style="font-size: 0.75rem;">{{ $inv->invoice_number }}</span>
                                <span class="fw-bold" style="font-family: var(--fs-font-mono); font-size: 1.05rem; color: var(--fs-text-primary);">Rp {{ number_format($inv->balance_due, 0, ',', '.') }}</span>
                            </div>
                            <div class="fw-semibold text-truncate mb-1" style="font-size: 0.95rem; color: var(--fs-text-secondary); max-width: 90%;">
                                {{ $inv->customer->name }}
                            </div>
                            <div class="d-flex align-items-center text-danger" style="font-size: 0.8rem; font-weight: 500;">
                                <i class="fa-regular fa-clock me-1"></i> Jatuh Tempo: {{ \Carbon\Carbon::parse($inv->due_date)->translatedFormat('d M Y') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted py-4">
                        <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="fa-solid fa-check fs-2"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: var(--fs-text-primary);">Semua Aman!</h6>
                        <p class="text-center" style="font-size: 0.9rem;">Tidak ada invoice yang mendekati jatuh tempo.</p>
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
