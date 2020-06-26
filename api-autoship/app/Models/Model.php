<?php

namespace App\Models;

use CPCommon\Pid\Pid;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Request;
use Sofa\Eloquence\Eloquence;

class Model extends EloquentModel
{
    use SoftDeletes;
    use Eloquence;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->pid = Pid::create();
        });
    }
}
