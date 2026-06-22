@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1" style="color: var(--fs-text-primary); letter-spacing: -0.5px;">Audit Trail & Log Aktivitas</h4>
                <p class="text-muted mb-0" style="font-size: 0.95rem;">Pantau seluruh aktivitas pengguna di dalam sistem untuk keperluan keamanan.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th class="px-4 py-3 text-secondary fw-semibold border-bottom-0" style="font-size: 0.85rem; width: 20%;">WAKTU</th>
                                <th class="px-4 py-3 text-secondary fw-semibold border-bottom-0" style="font-size: 0.85rem; width: 25%;">PENGGUNA</th>
                                <th class="px-4 py-3 text-secondary fw-semibold border-bottom-0" style="font-size: 0.85rem; width: 40%;">AKTIVITAS</th>
                                <th class="px-4 py-3 text-secondary fw-semibold border-bottom-0" style="font-size: 0.85rem; width: 15%;">IP ADDRESS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-medium text-dark" style="font-size: 0.95rem;">{{ $log->created_at->translatedFormat('d M Y') }}</div>
                                    <div class="text-muted" style="font-size: 0.85rem;"><i class="fa-regular fa-clock me-1"></i>{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; font-weight: 600;">
                                            {{ substr($log->user->name ?? '?', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $log->user->name ?? 'Sistem' }}</div>
                                            <div class="text-muted" style="font-size: 0.8rem;">{{ $log->user->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge 
                                        @if($log->action == 'create' || $log->action == 'login') bg-success-subtle text-success
                                        @elseif($log->action == 'update') bg-warning-subtle text-warning
                                        @elseif($log->action == 'delete' || $log->action == 'logout') bg-danger-subtle text-danger
                                        @else bg-primary-soft text-primary @endif 
                                        rounded-pill px-3 mb-1" style="font-size: 0.75rem;">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                    <div class="text-dark" style="font-size: 0.95rem;">{{ $log->description }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-muted" style="font-size: 0.9rem;">{{ $log->ip_address ?? '-' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="fa-solid fa-clipboard-list fs-1"></i></div>
                                    <h6 class="fw-bold text-dark">Belum ada aktivitas tercatat</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Log aktivitas akan muncul di sini saat pengguna mulai menggunakan sistem.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
            <div class="card-footer bg-white border-top-0 py-3 px-4 rounded-bottom-4">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
