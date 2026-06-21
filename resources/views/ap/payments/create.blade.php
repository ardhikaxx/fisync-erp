@extends('layouts.app')

@section('title', 'Pembayaran Hutang Supplier')

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
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4 border-bottom pb-2">Detail Tagihan Supplier (Inv: {{ $invoice->invoice_number }})</h6>
                <table class="table table-borderless table-sm mb-4">
                    <tr><td width="30%">Supplier</td><td class="fw-bold">: {{ $invoice->supplier->name }}</td></tr>
                    <tr><td>Total Hutang</td><td class="font-mono text-danger fw-bold">: Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td></tr>
                </table>

                <form action="{{ route('ap.payments.store', $invoice->id) }}" method="POST" id="paymentForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Pembayaran</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Sumber Dana (Rekening)</label>
                            <select name="cash_account_id" class="form-select" required>
                                <option value="">Pilih Bank/Kas</option>
                                @foreach($cashAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">No. Referensi / Bukti Transfer</label>
                            <input type="text" name="reference_number" class="form-control">
                        </div>
                    </div>
                    <hr class="my-4">
                    <button type="button" class="btn btn-danger w-100 fw-bold" id="btnSubmit"><i class="fa-solid fa-save"></i> Proses Pembayaran Hutang</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('btnSubmit').addEventListener('click', function() {
        if(document.getElementById('paymentForm').checkValidity()) {
            FSAlert.konfirmasiPosting(() => document.getElementById('paymentForm').submit());
        } else {
            document.getElementById('paymentForm').reportValidity();
        }
    });
</script>
@endsection
