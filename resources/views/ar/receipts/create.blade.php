@extends('layouts.app')

@section('title', 'Terima Pembayaran Piutang')

@section('content')

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        FSAlert.gagal('{{ $errors->first() }}');
    });
</script>
@endif

<div class="row">
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4 border-bottom pb-2">Detail Invoice #{{ $invoice->invoice_number }}</h6>
                <table class="table table-borderless table-sm mb-4">
                    <tr><td width="30%">Pelanggan</td><td class="fw-bold">: {{ $invoice->customer->name }}</td></tr>
                    <tr><td>Total Tagihan</td><td class="font-mono text-danger fw-bold">: Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td></tr>
                    <tr><td>Sisa Tagihan</td><td class="font-mono text-danger fw-bold">: Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</td></tr>
                </table>

                <form action="{{ route('ar.receipts.store', $invoice->id) }}" method="POST" id="receiptForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input id="receipt_date" type="date" name="receipt_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                <label for="receipt_date">Tanggal Penerimaan</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1">Nominal Diterima</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" class="form-control font-mono fw-bold" value="{{ $invoice->balance_due }}" max="{{ $invoice->balance_due }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select id="cash_account_id" name="cash_account_id" class="form-select" required>
                                    <option value="">Pilih Bank/Kas</option>
                                    @foreach($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                                <label for="cash_account_id">Masuk ke Rekening</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input id="reference_number" type="text" name="reference_number" class="form-control" placeholder="Referensi">
                                <label for="reference_number">No. Referensi / Bukti Transfer</label>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4">
                    <button type="button" class="btn btn-fs-primary w-100" id="btnSubmit"><i class="fa-solid fa-save"></i> Proses Pembayaran</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('btnSubmit').addEventListener('click', function() {
        if(document.getElementById('receiptForm').checkValidity()) {
            FSAlert.konfirmasiPosting(() => document.getElementById('receiptForm').submit());
        } else {
            document.getElementById('receiptForm').reportValidity();
        }
    });
</script>
@endsection
