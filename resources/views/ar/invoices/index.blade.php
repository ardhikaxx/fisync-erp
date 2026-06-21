@extends('layouts.app')

@section('title', 'Invoice Piutang (AR)')

@section('actions')
<a href="{{ route('ar.invoices.create') }}" class="btn btn-fs-primary">
    <i class="fa-solid fa-plus"></i> Buat Invoice Baru
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

<div class="card border-0 shadow-sm" style="border-radius: 14px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-fs mb-0">
                <thead>
                    <tr>
                        <th style="padding-left: 1.5rem;">Nomor Invoice</th>
                        <th>Pelanggan</th>
                        <th>Tanggal Terbit</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-end">Sisa Tagihan</th>
                        <th class="text-center" style="padding-right: 1.5rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td style="padding-left: 1.5rem;" class="font-mono fw-bold text-dark">{{ $inv->invoice_number }}</td>
                        <td class="fw-bold">{{ $inv->customer->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d/m/Y') }}</td>
                        <td class="{{ \Carbon\Carbon::parse($inv->due_date)->isPast() && $inv->balance_due > 0 ? 'text-danger fw-bold' : '' }}">
                            {{ \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') }}
                        </td>
                        <td class="text-end font-mono">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                        <td class="text-end font-mono fw-bold text-danger">Rp {{ number_format($inv->balance_due, 0, ',', '.') }}</td>
                        <td class="text-center" style="padding-right: 1.5rem;">
                            @if($inv->status == 'paid')
                                <span class="fs-badge fs-badge-success">Lunas</span>
                            @elseif($inv->status == 'partial')
                                <span class="fs-badge fs-badge-warning">Sebagian</span>
                            @elseif($inv->status == 'posted')
                                <span class="fs-badge fs-badge-info">Diposting</span>
                            @else
                                <span class="fs-badge fs-badge-secondary">{{ ucfirst($inv->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada invoice piutang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer bg-white border-top-0 pt-3 pb-3">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endsection
