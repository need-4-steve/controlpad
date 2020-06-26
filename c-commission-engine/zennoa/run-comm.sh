/var/www/c-commission-engine/ceapi zennoa-live commrun 1 2018-3-1 2018-3-31
/var/www/c-commission-engine/ceapi zennoa-live commrun 2 2018-3-1 2018-3-31
/var/www/c-commission-engine/ceapi zennoa-live commrun 3 2018-3-1 2018-3-31

psql -d zennoa-live -c 'DELETE FROM ce_achvbonus';

/var/www/c-commission-engine/ceapi zennoa-live commrun 1 2018-4-1 2018-4-30
/var/www/c-commission-engine/ceapi zennoa-live commrun 2 2018-4-1 2018-4-30
/var/www/c-commission-engine/ceapi zennoa-live commrun 3 2018-4-1 2018-4-30

psql -d zennoa-live -c 'DELETE FROM ce_achvbonus';

/var/www/c-commission-engine/ceapi zennoa-live commrun 1 2018-5-1 2018-5-31
/var/www/c-commission-engine/ceapi zennoa-live commrun 2 2018-5-1 2018-5-31
/var/www/c-commission-engine/ceapi zennoa-live commrun 3 2018-5-1 2018-5-31

> /tmp/zennoa-live.sql
pg_dump zennoa-live > /tmp/zennoa-live.sql

/etc/ceapi/stop.sh
dropdb zennoa-sim1
createdb zennoa-sim1
dropdb zennoa-sim2
createdb zennoa-sim2
/etc/ceapi/start.sh

psql -d zennoa-sim1 -f /tmp/zennoa-live.sql 
psql -d zennoa-sim2 -f /tmp/zennoa-live.sql 
