<?php

namespace App\Repositories\Eloquent\V0;

use App\Repositories\Interfaces\TaxInvoiceInterface;
use App\Models\TaxInvoice;
use DB;
use CPCommon\Pid\Pid;

class TaxInvoiceRepository implements TaxInvoiceInterface
{

    public function filterMerchant($builder, $merchantId)
    {
        return $builder->where('merchant_id', $merchantId);
    }

    public function __construct()
    {
        $this->filterMap = [
            'merchant_id' => function ($builder, $merchantId) {
                return $builder->where('merchant_id', $merchantId);
            },
            'type' => function ($builder, $type) {
                return $builder->where('type', $type);
            },
            'tax_connection_id' => function ($builder, $taxConnectionId) {
                return $builder->where('tax_connection_id', $taxConnectionId);
            },
            'committed' => function ($builder, $committed) {
                if (filter_var($committed, FILTER_VALIDATE_BOOLEAN)) {
                    return $builder->whereNotNull('committed_at');
                } else {
                    return $builder->whereNull('committed_at');
                }
            },
            'order_pid' => function ($builder, $orderId) {
                return $builder->where('order_pid', $orderId);
            },
            'reference_id' => function ($builder, $referenceId) {
                return $builder->where('reference_id', $referenceId);
            },
            'per_page' => function ($builder, $perPage) {
                return $builder;
            }
        ];
    }

    public function index($params)
    {
        $builder = TaxInvoice::select('*');
        foreach ($params as $key => $value) {
            $builder = $this->filterMap[$key]($builder, $value);
        }
        return $builder->paginate(isset($params['per_page']) ? $params['per_page'] : 15);
    }

    public function create($taxInvoice, $saveRequest = false)
    {
        $newInvoice = new TaxInvoice;
        $newInvoice->pid = Pid::create();
        $newInvoice->tax_connection_id = $taxInvoice['tax_connection_id'];
        $newInvoice->merchant_id = $taxInvoice['merchant_id'];
        $newInvoice->reference_id = (isset($taxInvoice['reference_id']) ? $taxInvoice['reference_id'] : null);
        $newInvoice->order_pid = (isset($taxInvoice['order_pid']) ? $taxInvoice['order_pid'] : null);
        $newInvoice->subtotal = $taxInvoice['subtotal'];
        if (isset($taxInvoice['discount'])) {
            $newInvoice->discount = $taxInvoice['discount'];
        }
        if (isset($taxInvoice['shipping'])) {
            $newInvoice->shipping = $taxInvoice['shipping'];
        }
        $newInvoice->tax = $taxInvoice['tax'];
        $newInvoice->type = $taxInvoice['type'];
        $newInvoice->origin_pid = (isset($taxInvoice['origin_pid']) ? $taxInvoice['origin_pid'] : null);
        $newInvoice->committed_at = (isset($taxInvoice['committed_at']) ? $taxInvoice['committed_at'] : null);
        if ($saveRequest) {
            $newInvoice->request = $taxInvoice;
        }
        $newInvoice->save();
        return $newInvoice;
    }

    public function update($pid, $taxInvoice)
    {
        TaxInvoice::where('pid', $pid)->update($taxInvoice);
    }

    public function show($pid)
    {
        return TaxInvoice::where('pid', $pid)->first();
    }

    public function getTotalRefunded($pid)
    {
        return TaxInvoice::where('origin_pid', $pid)->whereIn('type', ['refund','refund-full'])->sum('subtotal');
    }
}
