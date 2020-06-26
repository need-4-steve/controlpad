<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\State;

class StateController extends Controller
{
    /**
     * Get index of states.
     *
     * @return Response
     */
    public function getIndex()
    {
        return response()->json(State::all(), HTTP_SUCCESS);
    }
}
