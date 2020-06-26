<?php

namespace App\Services;

interface WebhookServiceInterface
{
    public function getWebhook($id, $orgId);
    public function findWebhooks($event, $orgId, $active = true, $suspended = null);
    public function sendHooks($event);
}
