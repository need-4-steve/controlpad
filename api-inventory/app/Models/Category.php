<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'parent_id',
        'placement',
        'level'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'show_on_store',
        'pivot',
        'created_at',
        'updated_at',
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|unique:categories',
        'placement' => 'integer',
        'parent_id' => 'exists:categories,id'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id')->orderBy('placement', 'ASC')->orderBy('id', 'ASC');
    }
}
