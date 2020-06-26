<?php
namespace App\Models;

use Eloquent;

class Category extends Eloquent
{

    public static $rules = [
        'name' => 'required',
    ];

    protected $table = 'categories';

    protected $fillable = [
        'type',
        'level',
        'name',
        'parent_id',
        'placement',
        'show_on_store'
    ];

    /**
     * ***********************
     * Relationships
     **************************/
    public function product()
    {
        return $this->belongsToMany('App\Models\Product', 'product_category');
    }

    public function children()
    {
        return $this->hasMany('App\Models\Category', 'parent_id', 'id')->with('children')->orderBy('placement', 'ASC');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id', 'id');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }
}
