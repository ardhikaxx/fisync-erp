@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('actions')
<a href="{{ route('coa.create') }}" class="btn btn-fs-primary">
    <i class="fa-solid fa-plus"></i> Tambah Akun
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
                        <th style="padding-left: 1.5rem;">Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Tipe</th>
                        <th>Saldo Normal</th>
                        <th>Status</th>
                        <th class="text-end" style="padding-right: 1.5rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $acc)
                    <tr>
                        <td style="padding-left: 1.5rem;" class="font-mono fw-bold text-dark">
                            <span style="padding-left: {{ ($acc->level - 1) * 20 }}px;">
                                {{ $acc->account_code }}
                            </span>
                        </td>
                        <td>
                            @if(!$acc->is_postable)
                                <span class="fw-bold text-dark">{{ $acc->account_name }}</span>
                            @else
                                {{ $acc->account_name }}
                            @endif
                        </td>
                        <td>
                            @php
                                $tipeMap = [
                                    'asset' => 'Aset',
                                    'liability' => 'Kewajiban',
                                    'equity' => 'Ekuitas',
                                    'revenue' => 'Pendapatan',
                                    'expense' => 'Beban',
                                ];
                            @endphp
                            {{ $tipeMap[$acc->account_type] ?? $acc->account_type }}
                        </td>
                        <td>
                            <span class="fs-badge {{ $acc->normal_balance == 'debit' ? 'fs-badge-info' : 'fs-badge-warning' }}">
                                {{ ucfirst($acc->normal_balance) }}
                            </span>
                        </td>
                        <td>
                            @if($acc->is_active)
                                <span class="fs-badge fs-badge-success">Aktif</span>
                            @else
                                <span class="fs-badge fs-badge-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end" style="padding-right: 1.5rem;">
                            <button class="btn btn-sm btn-light text-primary" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data Chart of Accounts.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
