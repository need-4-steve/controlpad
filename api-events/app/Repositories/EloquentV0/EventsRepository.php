<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\EventsRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use App\Event;
use Carbon\Carbon;

class EventsRepository implements EventsRepositoryInterface
{
    public function standardSelectAndJoin($additionalSelects = [])
    {
        $standardSelects = [
            'id',
            'sponsor_id',
            'host_id',
            'name',
            'description',
            'img',
            'location',
            'host_name',
            'sale_start',
            'sale_end',
            'status',
            'items_limit',
            'items_purchased',
            'date',
            'product_ids'
        ];
        $selects = array_merge($standardSelects, $additionalSelects);
        $events = Event::select($selects);
        return $events;
    }

    private function handleStandardParamsAndPaginate(Builder $query, array $params)
    {
        $inOrder = 'asc';
        $perPage = 25;
        if (isset($params['per_page'])) {
            $perPage = $params['per_page'];
        }
        if (isset($params['in_order'])) {
            $inOrder = $params['in_order'];
        }
        if (isset($params['sponsor_id'])) {
            $query->where('sponsor_id', $params['sponsor_id']);
        }
        if (isset($params['host_id'])) {
            $query->where('host_id', $params['host_id']);
        }
        if (isset($params['status']) && $params['status'] == 'open') {
            $query->where('status', 'open');
        } elseif (isset($params['status']) && $params['status'] == 'closed') {
            $query->where('status', 'closed');
        } else {
            $query->where('sale_start', '<=', Carbon::now('UTC'))
                ->where('sale_end', '>', Carbon::now('UTC'))
                ->where('status', 'open');
        }
        if (isset($params['search_term']) && $params['search_term'] !== '') {
            $query->whereRaw('MATCH(name, description) AGAINST ("'.$params['search_term'].'")');
        }
        if (isset($params['sort_by'])) {
            $query->orderBy($params['sort_by'], $inOrder);
        }
        return $query->paginate($perPage);
    }

    public function index($params)
    {
        $events = $this->standardSelectAndJoin();
        $events = $this->handleStandardParamsAndPaginate($events, $params);
        return $events;
    }
    public function show($id)
    {
        return Event::find($id);
    }

    public function create($params)
    {
        if (!isset($params['product_ids']) && $params['product_ids'] === null) {
            unset($params['product_ids']);
        }
        return Event::create($params);

    }

    public function edit($id, $ownerId, $params)
    {
        if (!isset($params['product_ids']) && $params['product_ids'] === null) {
            $params['product_ids'] = null;
        } else {
            $params['product_ids'] = json_encode($params['product_ids']);
        }

        return Event::where('id', $id)->where('sponsor_id', $ownerId)->update($params);
    }

    public function delete($id, $ownerId)
    {
        return Event::where('sponsor_id', $ownerId)->where('id', $id)->delete();
    }
}
