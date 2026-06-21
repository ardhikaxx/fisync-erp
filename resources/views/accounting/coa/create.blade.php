@extends('layouts.app')

@section('title', 'Tambah Akun COA')

@section('content')
<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-body p-4">
                <form action="{{ route('coa.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kode Akun <span class="text-danger">*</span></label>
                            <input type="text" name="account_code" class="form-control font-mono" placeholder="Ex: 1-1130" value="{{ old('account_code') }}" required>
                            @error('account_code') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nama Akun <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" placeholder="Ex: Bank BRI" value="{{ old('account_name') }}" required>
                            @error('account_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipe Akun <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-select" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="asset" {{ old('account_type') == 'asset' ? 'selected' : '' }}>Aset</option>
                                <option value="liability" {{ old('account_type') == 'liability' ? 'selected' : '' }}>Kewajiban</option>
                                <option value="equity" {{ old('account_type') == 'equity' ? 'selected' : '' }}>Ekuitas</option>
                                <option value="revenue" {{ old('account_type') == 'revenue' ? 'selected' : '' }}>Pendapatan</option>
                                <option value="expense" {{ old('account_type') == 'expense' ? 'selected' : '' }}>Beban</option>
                            </select>
                            @error('account_type') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Saldo Normal <span class="text-danger">*</span></label>
                            <select name="normal_balance" class="form-select" required>
                                <option value="">-- Pilih Saldo Normal --</option>
                                <option value="debit" {{ old('normal_balance') == 'debit' ? 'selected' : '' }}>Debit</option>
                                <option value="credit" {{ old('normal_balance') == 'credit' ? 'selected' : '' }}>Kredit</option>
                            </select>
                            @error('normal_balance') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Induk Akun (Opsional)</label>
                            <select name="parent_id" class="form-select">
                                <option value="">-- Tidak ada (Ini adalah akun utama) --</option>
                                @foreach($parentAccounts as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->account_code }} - {{ $parent->account_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih jika akun ini adalah sub-akun dari akun grup tertentu.</small>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="isPostableSwitch" name="is_postable" checked>
                                <label class="form-check-label fw-bold" for="isPostableSwitch">Akun dapat diposting (Postable)</label>
                            </div>
                            <small class="text-muted d-block mb-3">Matikan jika akun ini hanya digunakan sebagai header/grup.</small>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="isActiveSwitch" name="is_active" checked>
                                <label class="form-check-label fw-bold" for="isActiveSwitch">Akun Aktif</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('coa.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-fs-primary"><i class="fa-solid fa-save"></i> Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
