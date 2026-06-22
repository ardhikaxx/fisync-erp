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
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('assets.store') }}" method="POST" id="assetForm">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input id="asset_code" type="text" name="asset_code" class="form-control font-mono fw-bold" value="{{ old('asset_code', 'AST-'.date('Ymd').'-'.rand(10,99)) }}" required>
                                <label for="asset_code">Kode Aset <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input id="asset_name" type="text" name="asset_name" class="form-control" value="{{ old('asset_name') }}" required placeholder="Ex: Mobil Operasional">
                                <label for="asset_name">Nama Aset <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input id="acquisition_date" type="date" name="acquisition_date" class="form-control" value="{{ old('acquisition_date', date('Y-m-d')) }}" required>
                                <label for="acquisition_date">Tanggal Perolehan <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1">Harga Perolehan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="acquisition_cost" class="form-control font-mono text-end" value="{{ old('acquisition_cost') }}" min="0" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input id="useful_life_years" type="number" name="useful_life_years" class="form-control text-end" value="{{ old('useful_life_years') }}" min="1" required>
                                <label for="useful_life_years">Umur Ekonomis (Tahun) <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1">Nilai Sisa / Residu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="salvage_value" class="form-control font-mono text-end" value="{{ old('salvage_value', 0) }}" min="0" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select id="depreciation_method" name="depreciation_method" class="form-select" required>
                                    <option value="straight_line">Garis Lurus (Straight Line)</option>
                                    <option value="declining_balance">Saldo Menurun (Declining Balance)</option>
                                </select>
                                <label for="depreciation_method">Metode Penyusutan <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select id="branch_id" name="branch_id" class="form-select" required>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                                    @endforeach
                                </select>
                                <label for="branch_id">Cabang <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Pemetaan Akun Jurnal (Mapping)</h6>
                        </div>

                        <div class="col-md-4">
                            <div class="form-floating">
                                <select id="asset_account_id" name="asset_account_id" class="form-select" required>
                                    <option value="">Pilih...</option>
                                    @foreach($assetAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                                <label for="asset_account_id">Akun Aset</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select id="accumulated_depreciation_account_id" name="accumulated_depreciation_account_id" class="form-select" required>
                                    <option value="">Pilih...</option>
                                    @foreach($assetAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                                <label for="accumulated_depreciation_account_id">Akun Akumulasi Penyusutan</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select id="depreciation_expense_account_id" name="depreciation_expense_account_id" class="form-select" required>
                                    <option value="">Pilih...</option>
                                    @foreach($expenseAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                                <label for="depreciation_expense_account_id">Akun Beban Penyusutan</label>
                            </div>
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
