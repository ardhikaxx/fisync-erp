@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')

<!-- Quick Actions -->
<div class="d-flex flex-wrap gap-3 mb-4">
    <a href="{{ route('cashbank.index') }}" class="btn btn-fs-primary shadow-sm rounded-pill px-4 py-2 fw-semibold">
        <i class="fa-solid fa-plus me-2"></i>Catat Pengeluaran
    </a>
    <a href="{{ route('journals.index') }}" class="btn btn-fs-outline rounded-pill px-4 py-2 fw-semibold" style="background: #fff;">
        <i class="fa-solid fa-book me-2"></i>Buat Jurnal Umum
    </a>
    <a href="{{ route('ar.invoices.index') }}" class="btn btn-fs-outline rounded-pill px-4 py-2 fw-semibold" style="background: #fff;">
        <i class="fa-solid fa-file-invoice me-2"></i>Buat Invoice Baru
    </a>
</div>

<!-- KPI Cards -->
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

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
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
            <div class="card-body px-4 pb-4 overflow-auto" style="max-height: 300px;">
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

<div class="row g-4">
    <!-- Recent Transactions -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color: var(--fs-text-primary); letter-spacing: -0.3px;">Aktivitas Transaksi Terakhir</h5>
                <a href="{{ route('journals.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Lihat Semua</a>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="text-secondary" style="font-size: 0.85rem; font-weight: 600;">
                            <tr>
                                <th class="border-0 pb-3">Tanggal</th>
                                <th class="border-0 pb-3">No. Referensi</th>
                                <th class="border-0 pb-3">Keterangan</th>
                                <th class="border-0 pb-3 text-end">Total</th>
                                <th class="border-0 pb-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody style="border-top: 2px solid #f0f2f5;">
                            @forelse($recentTransactions as $trx)
                            <tr>
                                <td class="py-3">
                                    <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}</span>
                                </td>
                                <td class="py-3 font-mono text-primary fw-semibold">{{ $trx->reference_number }}</td>
                                <td class="py-3">
                                    <div class="text-truncate fw-medium text-dark" style="max-width: 250px;">{{ $trx->description }}</div>
                                </td>
                                <td class="py-3 text-end font-mono fw-bold">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                                <td class="py-3 text-center">
                                    @if($trx->status == 'posted')
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3">Posted</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning rounded-pill px-3">Draft</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada transaksi bulan ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses and Budget -->
    <div class="col-12 col-lg-4">
        <!-- Budget Progress -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark">Penggunaan Anggaran</h6>
                    <span class="fw-bold text-primary">{{ $budgetPercentage }}%</span>
                </div>
                <div class="progress mb-2" style="height: 10px; border-radius: 10px; background-color: #E6F6F6;">
                    <div class="progress-bar {{ $budgetPercentage > 80 ? 'bg-danger' : 'bg-primary' }}" role="progressbar" style="width: {{ $budgetPercentage }}%; border-radius: 10px;" aria-valuenow="{{ $budgetPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 0.8rem;">
                    <span>Terpakai: Rp {{ number_format($budgetUsed, 0, ',', '.') }}</span>
                    <span>Total: Rp {{ number_format($totalBudget, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Expense Breakdown Chart -->
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0" style="color: var(--fs-text-primary); letter-spacing: -0.3px;">Komposisi Pengeluaran</h6>
            </div>
            <div class="card-body px-4 pb-4">
                @if(count($expenseData) > 0)
                    <canvas id="expenseChart" height="250"></canvas>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted py-5">
                        <i class="fa-solid fa-chart-pie fs-1 text-light mb-3"></i>
                        <p class="mb-0">Belum ada pengeluaran bulan ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cashflow Line Chart
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
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Expense Donut Chart
        @if(count($expenseData) > 0)
        const ctxExpense = document.getElementById('expenseChart').getContext('2d');
        new Chart(ctxExpense, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($expenseLabels) !!},
                datasets: [{
                    data: {!! json_encode($expenseData) !!},
                    backgroundColor: [
                        '#0D7377', '#1E8E5A', '#E89A1C', '#D32F4E', '#2C7EC9'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }
                        }
                    }
                }
            }
        });
        @endif
    });
</script>
@endsection
