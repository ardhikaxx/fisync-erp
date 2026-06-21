<?php

namespace App\Observers;

use App\Models\Audit\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    public function created($model)
    {
        $this->log('create', $model);
    }

    public function updated($model)
    {
        $this->log('update', $model);
    }

    public function deleted($model)
    {
        $this->log('delete', $model);
    }

    protected function log($action, $model)
    {
        if ($model instanceof AuditTrail) return;

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id ?? 0,
            'old_values' => $action !== 'create' ? json_encode($model->getOriginal()) : null,
            'new_values' => $action !== 'delete' ? json_encode($model->getAttributes()) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
