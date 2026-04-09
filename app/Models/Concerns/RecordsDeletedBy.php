<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait RecordsDeletedBy
{
    protected static function bootRecordsDeletedBy(): void
    {
        static::deleting(function (Model $model): void {
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
            }
        });

        static::restoring(function (Model $model): void {
            $model->deleted_by = null;
        });
    }
}
