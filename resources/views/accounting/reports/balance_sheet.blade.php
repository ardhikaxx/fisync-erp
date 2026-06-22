@extends('layouts.app')

@section('title', 'Neraca (Balance Sheet)')

@section('content')
<div class="row">
    <div class="col-12 col-xl-10 mx-auto">
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.balance_sheet') }}" class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Per Tanggal (As of Date)</label>
                        <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate }}">
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
                    <h3 class="fw-bold mb-1" style="letter-spacing: -0.02em;">NERACA KEUANGAN</h3>
                    <p class="text-muted mb-0">Per Tanggal: {{ date('d F Y', strtotime($asOfDate)) }}</p>
                </div>

                <div class="row">
                    <!-- Left Column: Assets -->
                    <div class="col-md-6 border-end">
                        <h5 class="fw-bold text-success border-bottom pb-2 mb-3">ASET (AKTIVA)</h5>
                        <table class="table table-borderless table-sm mb-4">
                            <tbody>
                                @forelse($assetData as $row)
                                <tr>
                                    <td>{{ $row['account']->account_code }} - {{ $row['account']->account_name }}</td>
                                    <td class="text-end font-mono">Rp {{ number_format($row['balance'], 2, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-muted fst-italic">Tidak ada data aset.</td>
                                    <td class="text-end font-mono">Rp 0,00</td>
                                </tr>
                                @endforelse
                                <tr class="border-top fw-bold">
                                    <td>Total Aset</td>
                                    <td class="text-end font-mono text-success fs-6">Rp {{ number_format($totalAssets, 2, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Right Column: Liabilities & Equity -->
                    <div class="col-md-6">
                        <h5 class="fw-bold text-danger border-bottom pb-2 mb-3">KEWAJIBAN (PASIVA)</h5>
                        <table class="table table-borderless table-sm mb-4">
                            <tbody>
                                @forelse($liabilityData as $row)
                                <tr>
                                    <td>{{ $row['account']->account_code }} - {{ $row['account']->account_name }}</td>
                                    <td class="text-end font-mono">Rp {{ number_format($row['balance'], 2, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-muted fst-italic">Tidak ada data kewajiban.</td>
                                    <td class="text-end font-mono">Rp 0,00</td>
                                </tr>
                                @endforelse
                                <tr class="border-top fw-bold">
                                    <td>Total Kewajiban</td>
                                    <td class="text-end font-mono text-danger">Rp {{ number_format($totalLiabilities, 2, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4">EKUITAS (MODAL)</h5>
                        <table class="table table-borderless table-sm mb-4">
                            <tbody>
                                @foreach($equityData as $row)
                                <tr>
                                    <td>{{ $row['account']->account_code }} - {{ $row['account']->account_name }}</td>
                                    <td class="text-end font-mono">Rp {{ number_format($row['balance'], 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                <!-- Add Current Net Income to Equity -->
                                <tr>
                                    <td class="fst-italic text-muted">Laba/Rugi Tahun Berjalan</td>
                                    <td class="text-end font-mono fst-italic text-muted">Rp {{ number_format($currentNetIncome, 2, ',', '.') }}</td>
                                </tr>
                                <tr class="border-top fw-bold">
                                    <td>Total Ekuitas</td>
                                    <td class="text-end font-mono text-primary">Rp {{ number_format($totalEquity, 2, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="bg-light p-2 rounded text-end border-top mt-5">
                            <span class="fw-bold">Total Kewajiban & Ekuitas:</span>
                            <span class="fw-bold font-mono fs-5 ms-3 {{ abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($totalLiabilities + $totalEquity, 2, ',', '.') }}
                            </span>
                        </div>
                        @if(abs($totalAssets - ($totalLiabilities + $totalEquity)) >= 0.01)
                            <div class="text-danger text-end mt-1 small"><i class="fa-solid fa-triangle-exclamation"></i> Peringatan: Neraca tidak seimbang (Unbalanced)!</div>
                        @endif
                    </div>
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
