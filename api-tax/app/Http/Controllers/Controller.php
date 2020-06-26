<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Carbon\Carbon;

class Controller extends BaseController
{

    public function __construct()
    {
        $this->middleware('json', ['only' => ['create', 'store', 'edit']]);
    }
    /**
     * alters inputs with end_date and start_date to correctly formatted dates
     *
     * @param type var Description
     * @return return type
     */
    public function reformatDateStrings(array $params)
    {
        if (isset($params['start_date']) && isset($params['end_date'])) {
            $params['start_date'] = Carbon::parse($params['start_date'])->format('Y-m-d H:i:s');
            $params['end_date'] = Carbon::parse($params['end_date'])->format('Y-m-d H:i:s');
        }
        return $params;
    }
}
