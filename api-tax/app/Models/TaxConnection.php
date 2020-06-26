<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;

class TaxConnection extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'credentials',
        'type',
        'merchant_id',
        'active',
        'sandbox'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'credentials'
    ];

    protected $casts = [
        'active' => 'boolean',
        'sandbox' => 'boolean'
    ];

    public static $updateFields = [
        'credentials',
        'active'
    ];

    public static $createRules = [
        'credentials' => 'required',
        'merchant_id' => 'required',
        'type' => 'required|in:mock,exactor,avalara,tax-jar,sovos',
        'active' => 'required|boolean',
        'sandbox' => 'required|boolean'
    ];

    public static $createFields = [
        'credentials',
        'type',
        'merchant_id',
        'active',
        'sandbox'
    ];

    public static $indexParams = [
        'merchant_id',
        'type',
        'active',
        'per_page'
    ];

    public function getCredentialsAttribute()
    {
        return json_decode(Crypt::decrypt(base64_decode($this->attributes['credentials'])));
    }

    public function setCredentialsAttribute($value)
    {
        $this->attributes['credentials'] = base64_encode(Crypt::encrypt(json_encode($value)));
    }

    public function getService(\App\Repositories\Interfaces\TaxInvoiceInterface $taxInvoiceInterface = null)
    {
        switch ($this->type) {
            case 'mock':
                return new \App\Services\Tax\MockTaxService($this, $taxInvoiceInterface);
            case 'exactor':
                return new \App\Services\Tax\ExactorTaxService($this, $taxInvoiceInterface);
            case 'avalara':
                return new \App\Services\Tax\AvalaraTaxService($this, $taxInvoiceInterface);
            case 'tax-jar':
                return new \App\Services\Tax\TaxJarTaxService($this, $taxInvoiceInterface);
            case 'sovos':
                return new \App\Services\Tax\SovosTaxService($this, $taxInvoiceInterface);
            default:
                return null;
        }
    }
}
