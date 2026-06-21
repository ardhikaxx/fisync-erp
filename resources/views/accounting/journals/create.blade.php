@extends('layouts.app')

@section('title', 'Buat Jurnal Manual')

@section('content')

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        FSAlert.gagal('{{ $errors->first() }}');
    });
</script>
@endif

<form action="{{ route('journals.store') }}" method="POST" id="journalForm">
    @csrf
    
    <div class="row mb-4">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cabang <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mata Uang <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select" required>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ $currency->is_base_currency ? 'selected' : '' }}>{{ $currency->currency_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Keterangan Jurnal <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" placeholder="Contoh: Jurnal penyesuaian sewa dibayar dimuka" value="{{ old('description') }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-xl-4 mt-4 mt-xl-0">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: var(--fs-secondary); color: white;">
                <div class="card-body p-4 d-flex flex-column justify-content-center text-center">
                    <h6 class="text-white-50 fw-bold text-uppercase mb-1" style="letter-spacing: 1px;">Status Jurnal</h6>
                    <div id="balanceStatus">
                        <h3 class="fw-bold mb-3"><i class="fa-solid fa-circle-xmark text-danger"></i> Belum Balance</h3>
                        <p class="mb-0" style="font-size: var(--fs-text-sm);">Total Debit: <span class="font-mono" id="totalDebitLabel">Rp 0</span></p>
                        <p class="mb-0" style="font-size: var(--fs-text-sm);">Total Kredit: <span class="font-mono" id="totalCreditLabel">Rp 0</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Journal Lines -->
    <div class="card border-0 shadow-sm" style="border-radius: 14px;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Rincian Jurnal</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="addRowBtn"><i class="fa-solid fa-plus"></i> Tambah Baris</button>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-fs mb-0" id="journalTable">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Akun</th>
                            <th style="width: 25%;">Keterangan Baris (Opsional)</th>
                            <th style="width: 17%;" class="text-end">Debit</th>
                            <th style="width: 17%;" class="text-end">Kredit</th>
                            <th style="width: 6%;"></th>
                        </tr>
                    </thead>
                    <tbody id="journalBody">
                        <!-- Initial rows -->
                        @for($i=0; $i<2; $i++)
                        <tr class="journal-row">
                            <td>
                                <select name="entries[{{ $i }}][account_id]" class="form-select account-select" required>
                                    <option value="">Pilih Akun...</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="entries[{{ $i }}][description]" class="form-control form-control-sm" placeholder="Catatan opsional">
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="entries[{{ $i }}][debit]" class="form-control font-mono text-end debit-input" value="" step="0.01" min="0">
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="entries[{{ $i }}][credit]" class="form-control font-mono text-end credit-input" value="" step="0.01" min="0">
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-light text-danger remove-row" tabindex="-1"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('journals.index') }}" class="btn btn-light border">Batal</a>
                <button type="button" class="btn btn-fs-primary" id="btnSubmitJournal" disabled><i class="fa-solid fa-check-double"></i> Posting Jurnal</button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('journalBody');
    const addRowBtn = document.getElementById('addRowBtn');
    const form = document.getElementById('journalForm');
    const btnSubmit = document.getElementById('btnSubmitJournal');
    
    let rowCount = 2; // initial rows

    const accountOptions = `@foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>@endforeach`;

    // Calculate totals
    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('.debit-input').forEach(input => {
            totalDebit += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.credit-input').forEach(input => {
            totalCredit += parseFloat(input.value) || 0;
        });

        document.getElementById('totalDebitLabel').innerText = 'Rp ' + totalDebit.toLocaleString('id-ID');
        document.getElementById('totalCreditLabel').innerText = 'Rp ' + totalCredit.toLocaleString('id-ID');

        const isBalance = totalDebit === totalCredit && totalDebit > 0;
        const statusDiv = document.getElementById('balanceStatus');

        if (isBalance) {
            statusDiv.innerHTML = `
                <h3 class="fw-bold mb-3 text-success"><i class="fa-solid fa-check-circle"></i> Balance</h3>
                <p class="mb-0" style="font-size: var(--fs-text-sm);">Total Debit: <span class="font-mono text-success">Rp ${totalDebit.toLocaleString('id-ID')}</span></p>
                <p class="mb-0" style="font-size: var(--fs-text-sm);">Total Kredit: <span class="font-mono text-success">Rp ${totalCredit.toLocaleString('id-ID')}</span></p>
            `;
            btnSubmit.removeAttribute('disabled');
        } else {
            statusDiv.innerHTML = `
                <h3 class="fw-bold mb-3"><i class="fa-solid fa-circle-xmark text-danger"></i> Belum Balance</h3>
                <p class="mb-0" style="font-size: var(--fs-text-sm);">Total Debit: <span class="font-mono">Rp ${totalDebit.toLocaleString('id-ID')}</span></p>
                <p class="mb-0" style="font-size: var(--fs-text-sm);">Total Kredit: <span class="font-mono">Rp ${totalCredit.toLocaleString('id-ID')}</span></p>
            `;
            btnSubmit.setAttribute('disabled', 'disabled');
        }
    }

    // Add row
    addRowBtn.addEventListener('click', function() {
        const tr = document.createElement('tr');
        tr.className = 'journal-row';
        tr.innerHTML = `
            <td>
                <select name="entries[${rowCount}][account_id]" class="form-select account-select" required>
                    <option value="">Pilih Akun...</option>
                    ${accountOptions}
                </select>
            </td>
            <td>
                <input type="text" name="entries[${rowCount}][description]" class="form-control form-control-sm" placeholder="Catatan opsional">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="entries[${rowCount}][debit]" class="form-control font-mono text-end debit-input" step="0.01" min="0">
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="entries[${rowCount}][credit]" class="form-control font-mono text-end credit-input" step="0.01" min="0">
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-light text-danger remove-row" tabindex="-1"><i class="fa-solid fa-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(tr);
        rowCount++;
        
        // Add listeners to new inputs
        const newDebit = tr.querySelector('.debit-input');
        const newCredit = tr.querySelector('.credit-input');
        
        newDebit.addEventListener('input', function() {
            if(this.value) newCredit.value = '';
            calculateTotals();
        });
        
        newCredit.addEventListener('input', function() {
            if(this.value) newDebit.value = '';
            calculateTotals();
        });
    });

    // Remove row delegation
    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const rows = document.querySelectorAll('.journal-row');
            if (rows.length > 2) {
                e.target.closest('tr').remove();
                calculateTotals();
            } else {
                FSAlert.gagal('Minimal harus ada 2 baris jurnal.');
            }
        }
    });

    // Event listeners for existing inputs (mutual exclusion)
    document.querySelectorAll('.journal-row').forEach(row => {
        const debit = row.querySelector('.debit-input');
        const credit = row.querySelector('.credit-input');
        
        debit.addEventListener('input', function() {
            if(this.value) credit.value = '';
            calculateTotals();
        });
        
        credit.addEventListener('input', function() {
            if(this.value) debit.value = '';
            calculateTotals();
        });
    });

    // Submit form with SweetAlert confirmation
    btnSubmit.addEventListener('click', function() {
        FSAlert.konfirmasiPosting(() => {
            form.submit();
        });
    });
});
</script>
@endsection
