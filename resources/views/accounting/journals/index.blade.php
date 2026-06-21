@extends('layouts.app')

@section('title', 'Buku Besar & Jurnal Umum')

@section('actions')
<a href="{{ route('journals.create') }}" class="btn btn-fs-primary">
    <i class="fa-solid fa-plus"></i> Jurnal Manual Baru
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
                        <th>Keterangan</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end" style="padding-right: 1.5rem;">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        <!-- Transaction Header -->
                        <tr class="bg-light">
                            <td style="padding-left: 1.5rem;" class="fw-bold">{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}</td>
                            <td class="font-mono fw-bold text-primary">{{ $trx->transaction_number }}</td>
                            <td class="fw-bold">{{ $trx->description }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($trx->journalEntries->sum('debit_base'), 0, ',', '.') }}</td>
                            <td class="text-end fw-bold" style="padding-right: 1.5rem;">Rp {{ number_format($trx->journalEntries->sum('credit_base'), 0, ',', '.') }}</td>
                        </tr>
                        <!-- Journal Lines -->
                        @foreach($trx->journalEntries as $entry)
                        <tr>
                            <td style="padding-left: 1.5rem;"></td>
                            <td><small class="font-mono text-muted">{{ $entry->account->account_code }}</small></td>
                            <td>
                                @if($entry->credit > 0)
                                    <span style="padding-left: 20px;">{{ $entry->account->account_name }}</span>
                                @else
                                    {{ $entry->account->account_name }}
                                @endif
                                
                                @if($entry->description)
                                    <br><small class="text-muted">{{ $entry->description }}</small>
                                @endif
                            </td>
                            <td class="text-end font-mono text-debit">
                                {{ $entry->debit > 0 ? 'Rp ' . number_format($entry->debit_base, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end font-mono text-credit" style="padding-right: 1.5rem;">
                                {{ $entry->credit > 0 ? 'Rp ' . number_format($entry->credit_base, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada transaksi jurnal.</td>
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
