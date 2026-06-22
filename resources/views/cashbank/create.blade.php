@extends('layouts.app')

@section('title', 'Transaksi Kas & Bank Baru')

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
                <form action="{{ route('cashbank.store') }}" method="POST" id="cashBankForm">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-2">Jenis Transaksi <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="transaction_type" id="typeIn" value="in" {{ old('transaction_type') == 'in' ? 'checked' : '' }} required>
                                    <label class="form-check-label fw-bold text-success" for="typeIn">
                                        <i class="fa-solid fa-arrow-down"></i> Kas Masuk
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="transaction_type" id="typeOut" value="out" {{ old('transaction_type') == 'out' ? 'checked' : '' }} required>
                                    <label class="form-check-label fw-bold text-danger" for="typeOut">
                                        <i class="fa-solid fa-arrow-up"></i> Kas Keluar
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" name="transaction_date" id="tDate" class="form-control" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                <label for="tDate">Tanggal Transaksi <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="branch_id" id="bId" class="form-select" required>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                    @endforeach
                                </select>
                                <label for="bId">Cabang <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="cash_account_id" id="cAccId" class="form-select" required>
                                    <option value="">Pilih Akun Kas/Bank...</option>
                                    @foreach($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('cash_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                                <label for="cAccId">Akun Kas / Bank <span class="text-danger">*</span></label>
                            </div>
                            <small class="text-muted d-block mt-1" id="cashAccountDesc">Akun yang akan bertambah/berkurang.</small>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="offset_account_id" id="oAccId" class="form-select" required>
                                    <option value="">Pilih Akun Lawan...</option>
                                    @foreach($offsetAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('offset_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                                <label for="oAccId">Akun Lawan <span class="text-danger">*</span></label>
                            </div>
                            <small class="text-muted d-block mt-1">Akun asal/tujuan dana.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1">Nominal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" class="form-control font-mono fs-5 fw-bold text-end" value="{{ old('amount') }}" step="0.01" min="0.01" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="reference_number" id="refNum" class="form-control" value="{{ old('reference_number') }}" placeholder="Ex: BKK-001">
                                <label for="refNum">No. Referensi (Opsional)</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <textarea name="description" id="descId" class="form-control" style="height: 100px" required placeholder="Catatan transaksi...">{{ old('description') }}</textarea>
                                <label for="descId">Keterangan <span class="text-danger">*</span></label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('cashbank.index') }}" class="btn btn-light border">Batal</a>
                        <button type="button" class="btn btn-fs-primary" id="btnSubmitCB"><i class="fa-solid fa-save"></i> Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('cashBankForm');
        const btnSubmit = document.getElementById('btnSubmitCB');
        
        btnSubmit.addEventListener('click', function() {
            if (form.checkValidity()) {
                FSAlert.konfirmasiPosting(() => {
                    form.submit();
                });
            } else {
                form.reportValidity();
            }
        });
        
        // Dynamically update helper text based on type
        const typeRadios = document.querySelectorAll('input[name="transaction_type"]');
        const helperText = document.getElementById('cashAccountDesc');
        
        typeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'in') {
                    helperText.innerHTML = '<span class="text-success"><i class="fa-solid fa-arrow-down"></i> Kas Masuk:</span> Akun ini akan <b>didebit</b> (bertambah).';
                } else {
                    helperText.innerHTML = '<span class="text-danger"><i class="fa-solid fa-arrow-up"></i> Kas Keluar:</span> Akun ini akan <b>dikredit</b> (berkurang).';
                }
            });
        });
    });
</script>
@endsection
