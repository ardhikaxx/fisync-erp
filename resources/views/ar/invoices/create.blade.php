@extends('layouts.app')

@section('title', 'Buat Invoice Baru (AR)')

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
                <form action="{{ route('ar.invoices.store') }}" method="POST" id="invoiceForm">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <select id="customer_id" name="customer_id" class="form-select" required>
                                    <option value="">Pilih Pelanggan...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                <label for="customer_id">Pelanggan (Customer) <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input id="invoice_date" type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                                <label for="invoice_date">Tanggal Terbit <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input id="due_date" type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                                <label for="due_date">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select id="branch_id" name="branch_id" class="form-select" required>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                    @endforeach
                                </select>
                                <label for="branch_id">Cabang <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select id="revenue_account_id" name="revenue_account_id" class="form-select" required>
                                    <option value="">Pilih Akun Pendapatan...</option>
                                    @foreach($revenueAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('revenue_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                                <label for="revenue_account_id">Akun Pendapatan <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Detail Tagihan</h6>
                            <label class="form-label fw-bold">Total Nilai Invoice <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light text-success fw-bold">Rp</span>
                                <input type="number" name="amount" class="form-control font-mono fs-4 fw-bold text-end" value="{{ old('amount') }}" step="0.01" min="1" required placeholder="0">
                            </div>
                            <small class="text-muted mt-2 d-block">
                                * Catatan: Untuk versi sederhana ini, input dilakukan secara gelondongan (total). Sistem akan otomatis menjurnal: Debit (Piutang) & Kredit (Pendapatan).
                            </small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('ar.invoices.index') }}" class="btn btn-light border">Batal</a>
                        <button type="button" class="btn btn-fs-primary" id="btnSubmitInv"><i class="fa-solid fa-save"></i> Simpan & Posting Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('invoiceForm');
        const btnSubmit = document.getElementById('btnSubmitInv');
        
        btnSubmit.addEventListener('click', function() {
            if (form.checkValidity()) {
                FSAlert.konfirmasiPosting(() => {
                    form.submit();
                });
            } else {
                form.reportValidity();
            }
        });
    });
</script>
@endsection
