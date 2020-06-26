<?php

namespace App\Models\Traits;

use App\Models\History;
use Auth;
use Request;

trait HistoryTrait
{
    public static function saveHistory(
        $model,
        $action,
        $diff = array('before' => null, 'after' => null)
    ) {

        $userId = 0;
        $before_json = json_encode($diff['before']);
        $after_json = json_encode($diff['after']);

        if (Auth::check()) {
            // Get the current active/logged in user
            $user = Auth::user();
            $userId = $user->id;
        } else {
            /*
             *  Not logged in.  Can modify this section as another layer of security.
             */
        }

        // create history record
        $history = new History;
        $history->action = $action;
        $history->user_id = $userId;
        $history->before = $before_json;
        $history->after = $after_json;
        $history->historable_type = get_class($model);
        $history->historable_id = $model->id;
        if (!empty($model->name)) {
            $history->model_name = $model->name;
        } else {
            $history->model_name = null;
        }
        $history->ip = Request::getClientIp();
        $history->save();
    }

    /*
     * This 'magic method' is called just like it was boot() on a base model.
     */
    public static function bootHistoryTrait()
    {
        static::created(function ($model) {
            static::saveHistory($model, "Create", static::getDiff($model));
        });

        static::updated(function ($model) {
            static::saveHistory($model, "Update", static::getDiff($model));
        });

        static::deleted(function ($model) {
            static::saveHistory($model, "Delete", static::getDiff($model));
        });
    }

    // Helper function to get just the diff changes
    protected static function getDiff($model)
    {
        $changed = $model->getDirty();
        if (array_key_exists('updated_at', $changed)) {
            unset($changed['updated_at']);
        }
        $before = array_intersect_key($model->getOriginal(), $changed);
        $after = $changed;
        return compact('before', 'after');
    }

    public function history()
    {
        return $this->morphMany('App\Models\History', 'historable');
    }
}
