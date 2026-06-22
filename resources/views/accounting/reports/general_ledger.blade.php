@extends('layouts.app')

@section('title', 'Buku Besar (General Ledger)')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.general_ledger') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Pilih Akun</label>
                        <select name="account_id" class="form-select select2">
                            <option value="">-- Pilih Akun --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_code }} - {{ $acc->account_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-fs-primary w-100"><i class="fa-solid fa-filter"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedAccount)
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Mutasi Akun: {{ $selectedAccount->account_code }} - {{ $selectedAccount->account_name }}</h5>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Periode: {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">Tanggal</th>
                                <th width="15%">No. Transaksi</th>
                                <th>Keterangan</th>
                                <th class="text-end" width="15%">Debit</th>
                                <th class="text-end" width="15%">Kredit</th>
                                <th class="text-end" width="15%">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-info">
                                <td colspan="3" class="fw-bold text-end">Saldo Awal</td>
                                <td></td>
                                <td></td>
                                <td class="text-end fw-bold font-mono">Rp {{ number_format($openingBalance, 2, ',', '.') }}</td>
                            </tr>
                            
                            @php 
                                $runningBalance = $openingBalance; 
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp

                            @forelse($entries as $entry)
                                @php
                                    $totalDebit += $entry->debit_base;
                                    $totalCredit += $entry->credit_base;

                                    if ($selectedAccount->normal_balance == 'debit') {
                                        $runningBalance += ($entry->debit_base - $entry->credit_base);
                                    } else {
                                        $runningBalance += ($entry->credit_base - $entry->debit_base);
                                    }
                                @endphp
                                <tr>
                                    <td>{{ date('d/m/Y', strtotime($entry->transaction->transaction_date)) }}</td>
                                    <td>{{ $entry->transaction->transaction_number }}</td>
                                    <td>{{ $entry->description ?? $entry->transaction->description }}</td>
                                    <td class="text-end font-mono">{{ $entry->debit_base > 0 ? number_format($entry->debit_base, 2, ',', '.') : '-' }}</td>
                                    <td class="text-end font-mono">{{ $entry->credit_base > 0 ? number_format($entry->credit_base, 2, ',', '.') : '-' }}</td>
                                    <td class="text-end font-mono">Rp {{ number_format($runningBalance, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Tidak ada mutasi pada periode ini.</td>
                                </tr>
                            @endforelse

                            <tr class="table-light fw-bold">
                                <td colspan="3" class="text-end">Total Mutasi</td>
                                <td class="text-end font-mono text-primary">Rp {{ number_format($totalDebit, 2, ',', '.') }}</td>
                                <td class="text-end font-mono text-danger">Rp {{ number_format($totalCredit, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            <tr class="table-info fw-bold">
                                <td colspan="5" class="text-end">Saldo Akhir</td>
                                <td class="text-end font-mono fs-6">Rp {{ number_format($runningBalance, 2, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
            <div class="alert alert-info border-0 shadow-sm text-center py-5">
                <i class="fa-solid fa-book-open fa-3x mb-3 text-secondary"></i>
                <h5>Pilih Akun Buku Besar</h5>
                <p class="mb-0 text-muted">Silakan pilih akun dan rentang tanggal untuk melihat mutasi jurnal / buku besar.</p>
            </div>
        @endif
    </div>
</div>
@endsection
