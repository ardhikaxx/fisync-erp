<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $guarded = [];

    public function fiscalYear()
    {
        return $this->belongsTo(\App\Models\Accounting\FiscalYear::class, 'fiscal_year_id');
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Accounting\ChartOfAccount::class, 'account_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
}
