<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }
}
