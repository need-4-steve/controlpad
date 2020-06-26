<?php

namespace App\Repositories\Interfaces;

interface TaxInvoiceInterface
{
    public function index($params);
    public function create($taxInvoice);
    public function update($id, $taxInvoice);
    public function show($pid);
}
