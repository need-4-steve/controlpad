/etc/ceapi/stop.sh
dropdb ringbomb-live
createdb ringbomb-live
/var/www/c-commission-engine/ceapi ringbomb-live init
/etc/ceapi/start.sh
/var/www/c-commission-engine/ceapi ringbomb-live resetapikey 1
