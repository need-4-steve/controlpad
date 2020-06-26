<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "read_host",
        "write_host",
        "db_name",
        "status",
        "org_id",
        'domain',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public static $updateRules = [
        "status" => "sometimes|string|in:Paid,Unpaid,Paused",
        "read_host" => "sometimes|string",
        "write_host" => "sometimes|string",
        "db_name" => "sometimes|string",
        "name" => "sometimes|string",
        "domain" => "required|string"
    ];

    public static $updateFields = [
        "name",
        "read_host",
        "write_host",
        "db_name",
        "status",
        'domain'
    ];
}
