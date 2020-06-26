<?php namespace App\Models;

use Eloquent;
use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;
use Sofa\Eloquence\Eloquence;

class TaxInvoice extends Eloquent
{
    use EnabledTrait;
    use HistoryTrait;
    use Eloquence;

    // Add your validation rules here
    public static $rules = [
    ];

    protected $table = 'tax_invoice';
    protected $fillable = array(
        'transaction_id',
        'transaction_date',
        'sale_date',
        'currency_code',
        'taxable_id',
        'tax_class',
        'tax_direction',
        'gross_amount',
        'total_tax_amount',
        'commit_response'
    );

    protected $searchableColumns = [
        'gross_amount',
        'total_tax_amount',
    ];

    /****************************
     * Relationships
     ****************************/

    /****************************
     * Attributes
     ****************************/
}
