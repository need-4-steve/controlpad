<?php

namespace App\Repositories\Eloquent\V0;

use App\Repositories\Interfaces\ExampleInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use DB;

class ExampleRepository extends Repository implements ExampleInterface
{
    public function __construct($model)
    {
        $this->model = $model;
        $this->paramsTable = [];
    }
}
