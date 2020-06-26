<?php

namespace App\Repositories\Eloquent;

use App\Models\ReturnModel;
use App\Models\ReturnHistory;
use App\Models\Returnline;
use App\Repositories\Contracts\ReturnHistoryRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class ReturnHistoryRepository implements ReturnHistoryRepositoryContract
{
    use CommonCrudTrait;

    /**
     * Get the Return History for an Order.
     *
     * @method getHistoryByUser
     * @param  int              $orderId
     * @param  int              $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistoryByUser(int $orderId, int $userId)
    {
        $returns = ReturnModel::with('history.newStatus', 'history.oldStatus')->whereHas('history')->where('order_id', $orderId);
        $historys = $returns->get();
        foreach ($historys as $history) {
            if (auth()->user()->hasRole(['Rep']) && $history !== null) {
                $history->where('user_id', $userId);
            }
            $returnline = Returnline::where('return_id', $history->id)->first();
            $history->returnline= $returnline;
        }
        return $historys;
    }

    public function create($status, array $inputs = [])
    {
        $return = ReturnModel::find($inputs['return_id']);
        if (!isset($inputs['notes'])) {
            $inputs['notes'] = ' ';
        }
        $returnHistory = ReturnHistory::create([
            'return_id'     => $inputs['return_id'],
            'user_id'       => auth()->id(),
            'old_status_id' => $return->return_status_id,
            'new_status_id' => $status,
            'comments'      => $inputs['notes'],
        ]);
        return $returnHistory;
    }

    public function update(ReturnHistory $returnHistory, array $inputs = [])
    {
        $fields = [];

        foreach ($fields as $field) {
            $returnHistory->$field = array_get($inputs, $field, '');
        }

        $returnHistory->save();
        return $returnHistory;
    }
}
