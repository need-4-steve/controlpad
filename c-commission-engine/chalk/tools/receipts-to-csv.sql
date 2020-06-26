COPY (SELECT * FROM ce_receipts WHERE system_id='1' AND wholesale_date >= '2017-11-1' AND wholesale_date <= '2017-11-30') To '/tmp/chalk-receipts.csv' With CSV HEADER DELIMITER ',';
