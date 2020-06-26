/* Clear all rankrules, commrules, etc */
DELETE FROM ce_rankrules;
DELETE FROM ce_commrules;
DELETE FROM ce_checkpoint;

/* Fix system vals */
UPDATE ce_systems set altcore='0' WHERE id='1';
UPDATE ce_systems SET minpay='5' WHERE id='1';
UPDATE ce_systems SET psq_limit='0' WHERE id='1';

/* Handle migration problem */
//INSERT INTO ce_migrations (version, label) VALUES ('1.27', 'Add poolshares to users table. Add poolshare index');

/* ALL Rank Rules */

/* Rank 1 */
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '1', '23', '0', '0', false, 0, 0);
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '1', '21', '0', '0', false, 0, 0);

/* Rank 2 */
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '2', '23', '0', '0', false, 1, 0);
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '2', '21', '2', '0', false, 1, 0);

/* Rank 3 */
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '3', '23', '0', '0', false, 2, 0);
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '3', '21', '3', '0', false, 2, 0);

/* Rank 4 */
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '4', '23', '50', '0', false, 3, 0);
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '4', '21', '4', '0', false, 3, 0);

/* Rank 5 */
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '5', '23', '50', '0', false, 4, 0);
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '5', '21', '5', '0', false, 4, 0);

/* Rank 6 */
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '6', '23', '50', '0', false, 5, 0);
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '6', '21', '6', '0', false, 5, 0);

/* Rank 7 */
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '7', '23', '100', '0', false, 6, 0);
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '7', '21', '7', '0', false, 6, 0);

/* Rank 8 */
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '8', '23', '100', '0', false, 7, 0);
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '8', '21', '8', '0', false, 7, 0);

/* Rank 9 */
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '9', '23', '100', '0', false, 8, 0);
INSERT INTO ce_rankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '9', '21', '9', '0', false, 8, 0);

/* All Commission Rules */

/* Rank 1 */
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '1', '1', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '1', '1', '10', '5');

/* Rank 2 */
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '2', '1', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '2', '2', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '2', '1', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '2', '2', '10', '5');

/* Rank 3 */
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '3', '1', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '3', '2', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '3', '3', '5', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '3', '1', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '3', '2', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '3', '3', '10', '5');

/* Rank 4 */
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '4', '1', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '4', '2', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '4', '3', '5', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '4', '4', '7', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '4', '1', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '4', '2', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '4', '3', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '4', '4', '10', '5');

/* Rank 5 */
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '1', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '2', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '3', '5', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '4', '7', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '5', '3', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '1', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '2', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '3', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '4', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '5', '5', '10', '5');

/* Rank 6 */
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '1', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '2', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '3', '5', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '4', '7', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '5', '3', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '6', '3', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '1', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '2', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '3', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '4', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '5', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '6', '6', '10', '5');

/* Rank 7 */
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '1', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '2', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '3', '5', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '4', '7', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '5', '3', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '6', '3', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '7', '6', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '1', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '2', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '3', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '4', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '5', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '6', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '7', '7', '10', '5');

/* Rank 8 */
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '1', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '2', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '3', '5', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '4', '7', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '5', '3', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '6', '3', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '7', '6', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '8', '2', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '1', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '2', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '3', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '4', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '5', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '6', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '7', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '8', '8', '10', '5');

/* Rank 9 */
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '1', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '2', '10', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '3', '5', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '4', '7', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '5', '3', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '6', '3', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '7', '6', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '8', '2', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '9', '2', '1');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '1', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '2', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '3', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '4', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '5', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '6', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '7', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '8', '10', '5');
INSERT INTO ce_commrules(system_id, rank, generation, percent, inv_type) VALUES (1, '9', '9', '10', '5');

/* CM Rank 1 */ 
INSERT INTO ce_cmrankrules (system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES ('1', '1', '1', '0', '0', 'false', '0', '0');

/* CM Rank 2 */ 
INSERT INTO ce_cmrankrules (system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES ('1', '1', '1', '0', '0', 'false', '1', '0');
INSERT INTO ce_cmrankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '2', '11', '15000', '0', 'false', '1', '7500');

/* CM Rank 3 */ 
INSERT INTO ce_cmrankrules (system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES ('1', '1', '1', '50', '0', 'false', '2', '0');
INSERT INTO ce_cmrankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '3', '11', '50000', '0', 'false', '2', '25000');

/* CM Rank 4 */ 
INSERT INTO ce_cmrankrules (system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES ('1', '1', '1', '100', '0', 'false', '3', '0');
INSERT INTO ce_cmrankrules(system_id, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg) VALUES (1, '4', '11', '200000', '0', 'false', '3', '100000');

/* Check Match commission rules */
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '1', '1', '20');
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '2', '1', '20');
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '2', '2', '10');
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '3', '1', '20');
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '3', '2', '10');
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '3', '3', '5');
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '4', '1', '20');
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '4', '2', '10');
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '4', '3', '5');
INSERT INTO ce_cmcommrules (system_id, rank, generation, percent) VALUES ('1', '4', '4', '5');

/* Add fast start bonus receipts */
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1483432', '50', '2018-4-2', 'true', '5', 'faststartbonus'); 
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1484748', '50', '2018-4-2', 'true', '5', 'faststartbonus'); 
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1485532', '50', '2018-4-2', 'true', '5', 'faststartbonus'); 
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1483489', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1484818', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1484744', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1485681', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1485579', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1485020', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1485805', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1483269', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1485399', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1483283', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1485688', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1485585', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1487976', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1487975', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1487973', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1487972', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1486723', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1486080', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1487649', '50', '2018-4-2', 'true', '5', 'faststartbonus');
INSERT INTO ce_receipts(system_id, receipt_id, user_id, wholesale_price, wholesale_date, commissionable, inv_type, metadata_onadd) VALUES (1, 0, '1487302', '50', '2018-4-2', 'true', '5', 'faststartbonus');