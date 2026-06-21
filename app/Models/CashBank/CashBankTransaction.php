<?php

namespace App\Models\CashBank;

use Illuminate\Database\Eloquent\Model;

class CashBankTransaction extends Model
{
    protected $guarded = [];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function transactionCategory()
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }

    public function transaction()
    {
        return $this->belongsTo(\App\Models\Accounting\Transaction::class, 'transaction_id');
    }
}
