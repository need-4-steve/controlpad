<?php

namespace App\Repositories\Eloquent;

use App\Models\ParcelTemplate;

class ParcelTemplateRepository
{
    public function all($userId, $eagerLoad = ['carrier'])
    {
        $parcels = ParcelTemplate::with($eagerLoad)
            ->where('user_id', $userId)
            ->get();
        return $parcels;
    }

    public function create($inputs, $userId)
    {
        $fields = [
            'name',
            'length',
            'width',
            'height',
            'distance_unit'
        ];
        $parcel = new ParcelTemplate;
        $parcel->user_id = $userId;
        foreach ($fields as $field) {
            $parcel->$field = array_get($inputs, $field);
        }
        $parcel = $this->toggleEnable($parcel);
        return $parcel;
    }

    public function delete($id)
    {
        return ParcelTemplate::destroy($id);
    }

    public function find($id, $eagerLoad = ['carrier'])
    {
        $parcel = ParcelTemplate::where('id', $id)
            ->with($eagerLoad)
            ->first();
        return $parcel;
    }

    public function index($userId, $eagerLoad = ['carrier'])
    {
        $parcels = ParcelTemplate::with($eagerLoad)
            ->where('user_id', $userId)
            ->orWhere('user_id', config('site.apex_user_id'))
            ->whereNull('disabled_at')
            ->where('show_rep', true)
            ->get();
        return $parcels;
    }

    public function toggleEnable($parcel)
    {
        if (isset($parcel->disabled_at)) {
            $parcel->disabled_at = null;
        } else {
            $parcel->disabled_at = date("Y-m-d H:i:s");
        }
        $parcel->save();
        return $parcel;
    }

    public function toggleRepEnable($parcel)
    {
        $parcel->show_rep = !$parcel->show_rep;
        $parcel->save();
        return $parcel;
    }

    public function update($parcel, $inputs)
    {
        $fields = [
            'name',
            'length',
            'width',
            'height',
            'distance_unit'
        ];
        foreach ($fields as $field) {
            $parcel->$field = array_get($inputs, $field);
        }
        $parcel->save();
        return $parcel;
    }
}
