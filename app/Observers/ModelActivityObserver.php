<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLogger;

class ModelActivityObserver
{
    public function created(Model $model)
    {
        ActivityLogger::log('create', class_basename($model), $model->getKey());
    }

    public function updated(Model $model)
    {
        ActivityLogger::log('update', class_basename($model), $model->getKey());
    }

    public function deleted(Model $model)
    {
        ActivityLogger::log('delete', class_basename($model), $model->getKey());
    }
}

