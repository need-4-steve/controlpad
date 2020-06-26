<?php
namespace Helper;

use Carbon\Carbon;

class DataHelper extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $dbManager = $this->getModule('Db');
        $dbManager->_initialize();
        $date = Carbon::now()->addDays(60)->toDateTimeString();
        $query = "UPDATE subscription_user SET subscription_id = 1, user_id = 106, ends_at = '".$date."' WHERE user_id = 106 ";
        $dbManager->driver->executeQuery($query, []);
    }

    public function _afterSuite()
    {
        // Tear down after test suite
    }
}
