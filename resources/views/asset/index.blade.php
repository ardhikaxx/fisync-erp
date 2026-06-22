@extends('layouts.app')

@section('title', 'Aset Tetap (Fixed Assets)')

@section('actions')
<form action="{{ route('assets.depreciation') }}" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-warning" onclick="return confirm('Jalankan penyusutan untuk bulan ini?')">
        <i class="fa-solid fa-cogs"></i> Jalankan Penyusutan
    </button>
</form>
<a href="{{ route('assets.create') }}" class="btn btn-fs-primary">
    <i class="fa-solid fa-plus"></i> Tambah Aset Baru
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
                        <th style="padding-left: 1.5rem;">Kode Aset</th>
                        <th>Nama Aset</th>
                        <th>Tanggal Perolehan</th>
                        <th class="text-end">Harga Perolehan</th>
                        <th class="text-center">Umur (Thn)</th>
                        <th class="text-center" style="padding-right: 1.5rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr>
                        <td style="padding-left: 1.5rem;" class="font-mono fw-bold">{{ $asset->asset_code }}</td>
                        <td class="fw-bold">{{ $asset->asset_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($asset->acquisition_date)->format('d/m/Y') }}</td>
                        <td class="text-end font-mono">Rp {{ number_format($asset->cost_basis, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $asset->useful_life_months / 12 }}</td>
                        <td class="text-center" style="padding-right: 1.5rem;">
                            @if($asset->status == 'active')
                                <span class="fs-badge fs-badge-success">Aktif</span>
                            @else
                                <span class="fs-badge fs-badge-secondary">Terjual/Disposal</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data aset tetap.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($assets->hasPages())
    <div class="card-footer bg-white border-top-0 pt-3 pb-3">
        {{ $assets->links() }}
    </div>
    @endif
</div>
@endsection
