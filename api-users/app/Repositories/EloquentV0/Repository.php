<?php

namespace App\Repositories\EloquentV0;

class Repository
{
    public function getParams($query, $params)
    {
        foreach ($params as $param => $value) {
            try {
                if (key_exists($param, $this->paramsTable)) {
                    $this->paramsTable[$param]($query, $value, $params);
                }
            } catch (\Exception $e) {
            }
        }
        return $query;
    }
}
