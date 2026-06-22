<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
