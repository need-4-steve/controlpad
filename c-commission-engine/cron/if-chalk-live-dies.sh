# This is currently in a cron running every minute

CHECKIN_DATE=$(date "+%Y-%m-%d %H:%M:%S")

# Only handle chalk api services for now 
CHALK_LIVE=$(curl -sS --connect-timeout 30 http://localhost:8080/ -o - | wc -c);

if [ $CHALK_LIVE -ne "146" ]; then
	echo "* RESTARTING CHALK_LIVE API!"
	/etc/ceapi/stop.sh
	/etc/ceapi/start.sh
	echo "SYSTEM RESTARDED       : $CHECKIN_DATE"
	exit;
fi

echo "EVERYTHING OK: $CHECKIN_DATE"
