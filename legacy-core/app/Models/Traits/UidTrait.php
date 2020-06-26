<?php

namespace App\Models\Traits;

trait UidTrait
{
    /*
     * This 'magic method' is called just like it was boot() on a base model.
     */
    public static function bootUidTrait()
    {
        // first time we save a new model, create a uid
        static::creating(function ($model) {
            $model->uid = $model->generateNewUid();
        });

        // when we save an  updated model, if there is no uid create one
        static::updating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = $model->generateNewUid();
            }
        });
    }

    /**
     * Generate new uid.
     *
     * @return String
     */
    public function generateNewUid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
