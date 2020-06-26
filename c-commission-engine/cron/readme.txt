// This is the cron job program //
// Basically it checks if any batches need to be processed //
// Then it starts the process //
// And sends the payment information to the middleware //

To setup on a server run:
crontab -e

and add the following entry

// On commie for sim commission runs //
0      *       *       *       *       /var/www/c-commission-engine/2hour/run-affil-sims.php chalk-live >> /var/log/ceapi/cron.log
*	*	*	*	* /var/www/c-commission-engine/cron/if-chalk-live-dies.sh >> /tmp/deathcheck.txt

// On LIVE site //
*	*	*	*	* 	/var/www/c-commission-engine/cron/if-chalk-all-dies.sh >> /tmp/deathcheck.txt