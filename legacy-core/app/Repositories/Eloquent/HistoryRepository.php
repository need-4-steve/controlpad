<?php

namespace App\Repositories\Eloquent;

use App\Models\History;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class HistoryRepository
{
    use CommonCrudTrait;

    public function getIndex()
    {
        return History::with('username')->orderBy('updated_at', 'DESC')->paginate(25);
    }

    public function fixData($histories)
    {
        foreach ($histories as $history) {
            $history->before = json_decode($history->before);
            $history->after = json_decode($history->after);
            $history->historable_type = substr($history->historable_type, 11);
            $history_list = $history->toArray();
        }
        $histories = json_encode($histories, JSON_NUMERIC_CHECK);
        return $histories;
    }
}
