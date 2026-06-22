@extends('layouts.app')

@section('title', 'Data Supplier (suppliers)')

@section('actions')
<a href="{{ route('suppliers.create') }}" class="btn btn-fs-primary"><i class="fa-solid fa-plus"></i> Tambah Supplier</a>
@endsection

@section('content')
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        FSAlert.sukses("{{ session('success') }}");
    });
</script>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Nama Supplier</th>
                        <th>Email</th>
                        <th>No. Telp</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $c)
                    <tr>
                        <td class="ps-4 font-mono">{{ $c->code }}</td>
                        <td class="fw-bold">{{ $c->name }}</td>
                        <td>{{ $c->email ?? '-' }}</td>
                        <td>{{ $c->phone ?? '-' }}</td>
                        <td>
                            @if($c->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('suppliers.edit', $c->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-edit"></i> Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data Supplier.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
