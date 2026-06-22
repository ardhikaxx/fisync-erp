@extends('layouts.app')

@section('title', 'Laba Rugi (Income Statement)')

@section('content')
<div class="row">
    <div class="col-12 col-xl-10 mx-auto">
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.income_statement') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-fs-primary w-100"><i class="fa-solid fa-filter"></i> Tampilkan Laporan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div class="text-center mb-5">
                    <h3 class="fw-bold mb-1" style="letter-spacing: -0.02em;">LAPORAN LABA RUGI</h3>
                    <p class="text-muted mb-0">Periode: {{ date('d F Y', strtotime($startDate)) }} s/d {{ date('d F Y', strtotime($endDate)) }}</p>
                </div>

                <!-- Pendapatan -->
                <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">PENDAPATAN</h5>
                <table class="table table-borderless table-sm mb-4">
                    <tbody>
                        @forelse($revenueData as $row)
                        <tr>
                            <td class="ps-4">{{ $row['account']->account_code }} - {{ $row['account']->account_name }}</td>
                            <td class="text-end font-mono">Rp {{ number_format($row['balance'], 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="ps-4 text-muted fst-italic">Tidak ada transaksi pendapatan.</td>
                            <td class="text-end font-mono">Rp 0,00</td>
                        </tr>
                        @endforelse
                        <tr class="border-top fw-bold">
                            <td class="text-end">Total Pendapatan</td>
                            <td class="text-end font-mono text-primary" style="width: 200px;">Rp {{ number_format($totalRevenue, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Beban -->
                <h5 class="fw-bold text-danger border-bottom pb-2 mb-3">BEBAN OPERASIONAL</h5>
                <table class="table table-borderless table-sm mb-4">
                    <tbody>
                        @forelse($expenseData as $row)
                        <tr>
                            <td class="ps-4">{{ $row['account']->account_code }} - {{ $row['account']->account_name }}</td>
                            <td class="text-end font-mono">Rp {{ number_format($row['balance'], 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="ps-4 text-muted fst-italic">Tidak ada transaksi beban.</td>
                            <td class="text-end font-mono">Rp 0,00</td>
                        </tr>
                        @endforelse
                        <tr class="border-top fw-bold">
                            <td class="text-end">Total Beban Operasional</td>
                            <td class="text-end font-mono text-danger" style="width: 200px;">(Rp {{ number_format($totalExpense, 2, ',', '.') }})</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Laba Bersih -->
                <div class="p-3 mt-4 rounded-3 d-flex justify-content-between align-items-center {{ $netIncome >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }}">
                    <h5 class="mb-0 fw-bold">{{ $netIncome >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</h5>
                    <h4 class="mb-0 fw-bold font-mono">Rp {{ number_format(abs($netIncome), 2, ',', '.') }}</h4>
                </div>
            </div>
            
            <div class="card-footer bg-white border-top py-3 text-end">
                <button class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak PDF</button>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body { background: white; }
        .fs-sidebar, .fs-topbar, .card-footer, form { display: none !important; }
        .fs-content-body { padding: 0 !important; margin: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@endsection
