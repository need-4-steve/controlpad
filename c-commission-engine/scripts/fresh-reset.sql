DELETE FROM ce_batches;
DELETE FROM ce_breakdown;
DELETE FROM ce_checkmatch;
DELETE FROM ce_checkpoint;
DELETE FROM ce_commissions;
DELETE FROM ce_grandtotals;
DELETE FROM ce_ledger;
DELETE FROM ce_ranks;
DELETE FROM ce_userstats_month;
DELETE FROM ce_userstats_month_legs;
DELETE FROM ce_userstats_month_lvl1;
DELETE FROM ce_userstats_total;
DELETE FROM ce_userstats_total_legs;
DELETE FROM ce_userstats_total_lvl1;

DELETE FROM ce_users WHERE id <= 25000;
