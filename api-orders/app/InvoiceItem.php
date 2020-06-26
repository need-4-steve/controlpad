<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'invoice_item';

    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'invoice_id',
        'quantity'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'invoice_id',
        'id'
    ];
}
