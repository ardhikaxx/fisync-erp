@extends('layouts.app')

@section('title', isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan')

@section('content')
<div class="row">
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ isset($customer) ? route('customers.update', $customer->id) : route('customers.store') }}" method="POST">
                    @csrf
                    @if(isset($customer))
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $customer->code ?? '') }}" required>
                                <label for="code">Kode Pelanggan <span class="text-danger">*</span></label>
                            </div>
                            @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-8">
                            <div class="form-floating">
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}" required>
                                <label for="name">Nama Pelanggan <span class="text-danger">*</span></label>
                            </div>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}">
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}">
                                <label for="phone">No. Telepon</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea name="address" id="address" class="form-control" style="height: 100px">{{ old('address', $customer->address ?? '') }}</textarea>
                                <label for="address">Alamat Lengkap</label>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">Status Aktif</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('customers.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-fs-primary"><i class="fa-solid fa-save"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
