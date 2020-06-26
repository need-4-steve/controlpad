<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;

class TermsAcceptance extends Model
{
    use HistoryTrait;

    protected $table = 'terms_acceptance';
    protected $fillable = array(
        'user_id', 'updated_at'
    );

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
