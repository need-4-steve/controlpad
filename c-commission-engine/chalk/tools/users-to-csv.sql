COPY (SELECT user_id, usertype FROM ce_users WHERE system_id='1' ORDER BY id) To '/tmp/chalk-users.csv' With CSV HEADER DELIMITER ',';
