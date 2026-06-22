@extends('layouts.app')

@section('title', 'Profile Settings')
@section('subtitle', 'Kelola informasi akun dan kata sandi Anda.')

@section('content')
<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4 text-center">
                <div class="mb-4 position-relative d-inline-block">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=E8F2FB&color=2C7EC9&rounded=true&size=120" alt="User Avatar" class="rounded-circle shadow-sm border border-3 border-white">
                    <button class="btn btn-sm btn-fs-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 32px; height: 32px; padding: 0;">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                </div>
                <h4 class="fw-bold text-dark mb-1">{{ $user->name }}</h4>
                <p class="text-secondary mb-3">{{ $user->email }}</p>
                
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">Administrator</span>
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Active</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0 text-dark">Informasi Pribadi</h5>
            </div>
            <div class="card-body px-4 pb-4">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-secondary fw-semibold mb-1" style="font-size: 0.85rem;">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-user text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-secondary fw-semibold mb-1" style="font-size: 0.85rem;">Email Aktif</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-3 mt-5">Ubah Kata Sandi</h5>
                    <p class="text-muted" style="font-size: 0.85rem;">Kosongkan bagian ini jika Anda tidak ingin mengubah kata sandi Anda.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label text-secondary fw-semibold mb-1" style="font-size: 0.85rem;">Kata Sandi Saat Ini</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" name="current_password" placeholder="Masukkan kata sandi saat ini">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-secondary fw-semibold mb-1" style="font-size: 0.85rem;">Kata Sandi Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" name="new_password" placeholder="Minimal 8 karakter">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-secondary fw-semibold mb-1" style="font-size: 0.85rem;">Konfirmasi Kata Sandi Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" name="new_password_confirmation" placeholder="Ulangi kata sandi baru">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" class="btn btn-light me-2 rounded-pill px-4 fw-semibold">Batal</button>
                        <button type="submit" class="btn btn-fs-primary rounded-pill px-4 fw-semibold">
                            <i class="fa-solid fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
