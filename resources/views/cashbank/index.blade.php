@extends('layouts.app')

@section('title', 'Transaksi Kas & Bank')

@section('actions')
<a href="{{ route('cashbank.create') }}" class="btn btn-fs-primary">
    <i class="fa-solid fa-plus"></i> Transaksi Baru
</a>
@endsection

@section('content')

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        FSAlert.sukses('{{ session('success') }}');
    });
</script>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-fs mb-0">
                <thead>
                    <tr>
                        <th style="padding-left: 1.5rem;">Tanggal</th>
                        <th>No. Transaksi</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th class="text-end" style="padding-right: 1.5rem;">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                    <tr>
                        <td style="padding-left: 1.5rem;" class="fw-bold">{{ \Carbon\Carbon::parse($trx->transaction->transaction_date)->format('d M Y') }}</td>
                        <td class="font-mono fw-bold text-dark">{{ $trx->transaction->transaction_number }}</td>
                        <td>
                            @if($trx->type == 'cash_in')
                                <span class="fs-badge fs-badge-success"><i class="fa-solid fa-arrow-down"></i> Kas Masuk</span>
                            @else
                                <span class="fs-badge fs-badge-danger"><i class="fa-solid fa-arrow-up"></i> Kas Keluar</span>
                            @endif
                        </td>
                        <td>{{ $trx->transactionCategory->name ?? '-' }}</td>
                        <td>{{ Str::limit($trx->transaction->description, 50) }}</td>
                        <td class="text-end font-mono fw-bold {{ $trx->type == 'cash_in' ? 'text-success' : 'text-danger' }}" style="padding-right: 1.5rem;">
                            {{ $trx->type == 'cash_in' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi Kas & Bank.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer bg-white border-top-0 pt-3 pb-3">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection
