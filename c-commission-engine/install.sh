# Prepare for logging
mkdir /var/log/ceapi

# install the settings files
mkdir /etc/ceapi
mkdir /etc/ceapi/includes
mkdir /var/spool/ceapi
cp settings/includes/inc.settings-top.php /etc/ceapi/includes/inc.settings-top.php
cp settings/includes/inc.settings-bottom.php /etc/ceapi/includes/inc.settings-bottom.php
cp /etc/ceapi/inc.settings.php /etc/ceapi/inc.settings.php.bak
cp settings/test-live.ini.example /etc/ceapi/test-live.ini
cp settings/test-sim1.ini.example /etc/ceapi/test-sim1.ini
cp settings/test-sim2.ini.example /etc/ceapi/test-sim2.ini
cp settings/test-sim3.ini.example /etc/ceapi/test-sim3.ini
cp settings/inc.settings.php.example /etc/ceapi/inc.settings.php.new
cp settings/start.sh.example /etc/ceapi/start.sh
cp settings/stop.sh.example /etc/ceapi/stop.sh
cp settings/status.sh.example /etc/ceapi/status.sh
cp settings/restart.sh.example /etc/ceapi/restart.sh
echo "Settings files copied to /etc/ceapi";
echo "Please configure now";
