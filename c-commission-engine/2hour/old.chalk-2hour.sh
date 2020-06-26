pg_dump chalkcouture > /tmp/chalk-2hour-dump.sql
psql -d chalkcouture-sim -f /var/www/c-commission-engine/2hour/purge.sql
psql -d chalkcouture-sim -f /tmp/chalk-2hour-dump.sql
rm /tmp/chalk-2hour-dump.sql
/var/www/c-commission-engine/ceapi sim commrun 1 2017-11-1 2017-11-30
#/var/www/c-commission-engine/ceapi sim commrun 2 2017-11-1 2017-11-30
