<?php

namespace App\Data;

use Facebook\PersistentData\PersistentDataInterface;

class FacebookPersistantDataInterface implements PersistentDataInterface
{
    /**
    * @var string Prefix to use for session variables.
    */
    protected $sessionPrefix = 'FBRLH_';

    /**
    * @inheritdoc
    */
    public function get($key)
    {
        return session($this->sessionPrefix . $key);
    }

    /**
    * @inheritdoc
    */
    public function set($key, $value)
    {
        session([$this->sessionPrefix . $key => $value]);
    }
}
