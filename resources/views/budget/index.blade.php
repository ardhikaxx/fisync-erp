@extends('layouts.app')

@section('title', 'Anggaran (Budget)')

@section('actions')
<a href="{{ route('budgets.create') }}" class="btn btn-fs-primary">
    <i class="fa-solid fa-plus"></i> Buat Anggaran Baru
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
                        <th style="padding-left: 1.5rem;">Tahun</th>
                        <th>Akun Biaya</th>
                        <th>Pusat Biaya (Cost Center)</th>
                        <th class="text-end" style="padding-right: 1.5rem;">Total Anggaran Tahunan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($budgets as $budget)
                    <tr>
                        <td style="padding-left: 1.5rem;" class="fw-bold">{{ $budget->fiscalYear->year }}</td>
                        <td>{{ $budget->account->account_name }}</td>
                        <td>{{ $budget->costCenter->name }}</td>
                        <td class="text-end font-mono text-primary fw-bold" style="padding-right: 1.5rem;">Rp {{ number_format($budget->annual_budget, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data anggaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($budgets->hasPages())
    <div class="card-footer bg-white border-top-0 pt-3 pb-3">
        {{ $budgets->links() }}
    </div>
    @endif
</div>
@endsection
