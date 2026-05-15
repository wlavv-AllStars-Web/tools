<?php

namespace App\Traits;

use App\Models\modules\logs\logs;

trait PrestashopModelLogger{
    
    protected static function bootPrestashopModelLogger(){
        
        static::created(function ($model) { $model->writeModelLog('created'); });
        static::updated(function ($model) { $model->writeModelLog('updated'); });
        static::deleted(function ($model) { $model->writeModelLog('deleted'); });
    }

    protected function writeModelLog(string $action, array $extra = []): void{

        if (method_exists($this, 'disableModelLogging') && $this->disableModelLogging() === true) return;

        $context = method_exists($this, 'logContext') ? $this->logContext() : [];
    
        $descriptionParts = [
            'model=' . static::class,
            'table=' . $this->getTable(),
            'action=' . $action,
            'id=' . ($this->getKey() ?? 'null'),
        ];
    
        $mergedExtra = array_merge($context, $extra);
    
        foreach ($mergedExtra as $key => $value) {
            $descriptionParts[] = $key . '=' . (is_scalar($value) || is_null($value) ? (string) $value : json_encode($value));
        }
    
        $request = app()->runningInConsole() ? null : request();
    
        logs::create([
            'user_id'     => auth()->id() ?? 0,
            'action'      => 'model_' . $action,
            'module'      => 'prestashop',
            'route'       => $request ? $request->path() : 'console',
            'method'      => $request ? $request->method() : 'CLI',
            'ip_address'  => $request ? $request->ip() : null,
            'user_agent'  => $request ? $request->userAgent() : null,
            'severity'    => 'info',
            'description' => implode(' | ', $descriptionParts),
            'created_at'  => now(),
        ]);
    }

    public function logCustomAction(string $action, array $extra = []): void{
        
        $this->writeModelLog($action, $extra);
    }
}