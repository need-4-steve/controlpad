<?php namespace App\Http\Controllers\Api\V1;

use Response;
use App\Models\History;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\HistoryRepository;
use Auth;

class HistoryController extends Controller
{
    protected $historyRepo;

    public function __construct(HistoryRepository $historyRepo)
    {
        $this->historyRepo = $historyRepo;
    }

    public function getIndex()
    {
        if (auth()->user()->hasRole(['Superadmin'])) {
            $histories = $this->historyRepo->getIndex();
            $histories->transform(function ($value, $key) {
                return $this->fixData($value);
            });
            return response()->json($histories, 200);
        }
        return $this->createResponse(true, 403, 'Unauthorized', []);
    }

    public function getModel($model)
    {
        if (Auth::user()->hasRole(['Superadmin'])) {
            $histories = History::with('username')
                ->where('historable_type', 'App\\Models\\' . $model)
                ->get();

            $histories = $this->historyRepo->fixData($histories);

            return Response::make($histories);
        }
        return $this->createResponse(true, 403, 'Unauthorized', []);
    }
    /**
    * I did not find this method called anywhere beside the tests.
    */
    public function getId($model, $id)
    {
        if (Auth::user()->hasRole(['Superadmin'])) {
            $histories = History::with('username')
                ->where('historable_type', 'App\\Models\\' . $model)
                ->where('historable_id', '=', $id)
                ->get();

            $histories = $this->historyRepo->fixData($histories);

            return Response::make($histories);
        }
        return $this->createResponse(true, 403, 'Unauthorized', []);
    }

    /**
    * This mesthod is necessary for formatting history for the
    * getIndex method.
    */
    private function fixData($history)
    {
        $history->before = json_decode($history->before);
        $history->after = json_decode($history->after);
        $history->historable_type = substr($history->historable_type, 11);
        $history_list = $history->toArray();
        return $history;
    }
}
