UPDATE ce_receipts_filter SET disabled='true' WHERE inv_type='1';
UPDATE ce_commrules SET inv_type='5';
UPDATE ce_rankrules SET qualify_type='25';
UPDATE ce_rankrules SET rulegroup='7' WHERE id='8';