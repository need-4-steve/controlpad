<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\ReturnStatusRepository;

class ReturnStatusController extends Controller
{

    public function __construct(ReturnStatusRepository $returnStatusRepo)
    {
        $this->returnStatusRepo = $returnStatusRepo;
    }

    /**
    * This gets all the status for returns.
    *
    * @return array|return status objects
    */
    public function getAll()
    {
        return $this->returnStatusRepo->all();
    }
}
