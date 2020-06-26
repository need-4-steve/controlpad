UPDATE ce_receipts SET commissionable='true' WHERE commissionable='false' AND wholesale_date::DATE >= '2017-7-31' AND wholesale_date::DATE <='2017-9-1';
