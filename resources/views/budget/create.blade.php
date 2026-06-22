@extends('layouts.app')

@section('title', 'Buat Anggaran (Budget)')

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
                <form action="{{ route('budgets.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select id="fiscal_year_id" name="fiscal_year_id" class="form-select" required>
                                    @foreach($fiscalYears as $fy)
                                        <option value="{{ $fy->id }}">{{ $fy->year }}</option>
                                    @endforeach
                                </select>
                                <label for="fiscal_year_id">Tahun Fiskal <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select id="cost_center_id" name="cost_center_id" class="form-select" required>
                                    @foreach($costCenters as $cc)
                                        <option value="{{ $cc->id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                                    @endforeach
                                </select>
                                <label for="cost_center_id">Pusat Biaya (Cost Center) <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-floating">
                                <select id="account_id" name="account_id" class="form-select" required>
                                    <option value="">Pilih Akun Beban...</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                                <label for="account_id">Akun Beban <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-12 mt-4">
                            <label class="form-label fw-bold mb-1">Total Anggaran Tahunan <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light text-primary fw-bold">Rp</span>
                                <input type="number" name="annual_budget" class="form-control font-mono fs-4 fw-bold text-end" value="{{ old('annual_budget') }}" step="0.01" min="1" required placeholder="0">
                            </div>
                            <small class="text-muted mt-2 d-block">
                                * Anggaran akan didistribusikan secara merata (dibagi 12) ke setiap bulan secara otomatis.
                            </small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('budgets.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-fs-primary"><i class="fa-solid fa-save"></i> Simpan Anggaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
