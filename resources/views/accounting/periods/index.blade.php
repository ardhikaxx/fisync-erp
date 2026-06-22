@extends('layouts.app')

@section('title', 'Tutup Buku (Period End)')

@section('content')
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        FSAlert.sukses('{{ session('success') }}');
    });
</script>
@endif

<div class="row">
    <div class="col-12 col-md-8 mx-auto">
        <div class="card mb-4">
            <div class="card-body bg-light rounded d-flex align-items-center gap-3">
                <i class="fa-solid fa-lock text-warning fa-2x"></i>
                <div>
                    <h6 class="fw-bold mb-1">Informasi Tutup Buku</h6>
                    <p class="mb-0 text-muted" style="font-size: 0.85rem;">Menutup periode akuntansi akan mengunci semua jurnal transaksi di bulan tersebut. Pastikan proses rekonsiliasi dan penyusutan aset telah dilakukan sebelum menutup periode.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Tahun</th>
                                <th>Periode (Bulan)</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periods as $period)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $period->period_year }}</td>
                                <td class="fw-bold">{{ $period->period_name }}</td>
                                <td>{{ date('d/m/Y', strtotime($period->start_date)) }} - {{ date('d/m/Y', strtotime($period->end_date)) }}</td>
                                <td>
                                    @if($period->status == 'closed')
                                        <span class="badge bg-danger"><i class="fa-solid fa-lock"></i> Ditutup</span>
                                    @else
                                        <span class="badge bg-success"><i class="fa-solid fa-lock-open"></i> Aktif / Buka</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($period->status == 'open')
                                        <form action="{{ route('periods.close', $period->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menutup periode ini? Semua transaksi akan terkunci.')">
                                                Tutup Buku
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('periods.open', $period->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Membuka kembali periode ini bisa mengubah Laporan Keuangan historis. Lanjutkan?')">
                                                Buka Kembali
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data periode.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($periods->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $periods->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
