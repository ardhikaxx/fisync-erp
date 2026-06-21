@extends('layouts.app')

@section('title', 'Tambah Aset Tetap')

@section('content')

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        FSAlert.gagal('{{ $errors->first() }}');
    });
</script>
@endif

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-body p-4">
                <form action="{{ route('assets.store') }}" method="POST" id="assetForm">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kode Aset <span class="text-danger">*</span></label>
                            <input type="text" name="asset_code" class="form-control font-mono" value="{{ old('asset_code', 'AST-'.date('Ymd').'-'.rand(10,99)) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Aset <span class="text-danger">*</span></label>
                            <input type="text" name="asset_name" class="form-control" value="{{ old('asset_name') }}" required placeholder="Ex: Mobil Operasional Xenia">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Perolehan <span class="text-danger">*</span></label>
                            <input type="date" name="acquisition_date" class="form-control" value="{{ old('acquisition_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Harga Perolehan (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="acquisition_cost" class="form-control font-mono" value="{{ old('acquisition_cost') }}" min="0" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Umur Ekonomis (Tahun) <span class="text-danger">*</span></label>
                            <input type="number" name="useful_life_years" class="form-control" value="{{ old('useful_life_years') }}" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nilai Sisa / Residu (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="salvage_value" class="form-control font-mono" value="{{ old('salvage_value', 0) }}" min="0" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Metode Penyusutan <span class="text-danger">*</span></label>
                            <select name="depreciation_method" class="form-select" required>
                                <option value="straight_line">Garis Lurus (Straight Line)</option>
                                <option value="declining_balance">Saldo Menurun (Declining Balance)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cabang <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Pemetaan Akun Jurnal (Mapping)</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">Akun Aset</label>
                            <select name="asset_account_id" class="form-select form-select-sm" required>
                                <option value="">Pilih...</option>
                                @foreach($assetAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">Akun Akumulasi Penyusutan</label>
                            <select name="accumulated_depreciation_account_id" class="form-select form-select-sm" required>
                                <option value="">Pilih...</option>
                                @foreach($assetAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">Akun Beban Penyusutan</label>
                            <select name="depreciation_expense_account_id" class="form-select form-select-sm" required>
                                <option value="">Pilih...</option>
                                @foreach($expenseAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('assets.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-fs-primary"><i class="fa-solid fa-save"></i> Simpan Aset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
