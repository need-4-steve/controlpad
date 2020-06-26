<?php
namespace App\Services\Commission;

interface CommissionServiceInterface { 
    /**
     * Puts an Order in a queue to be sent to the commission engine.
     *
     * @param Order $order
     */
    public function queOrder($order);
    /**
     * Sends data about New Orders to the Commission Engine.
     * */
    public function commitNewOrders(); 
    /**
     * Sends data about an Order to the Commission Engine.
     * withTrashed() needs to be used because of backfill and records in a model might have been deleted.
     *
     * @param Order $order
     * @param bool $initialize used for backfill
     * @return array $receipts returns headers foreach orderline that was sent
     */
    public function addReceipt($order, $initialize = false);
    /**
     * Cancel an Order that has been sent to the Commission Engine
     *
     * @param String $orderReceiptId
     * @return GuzzleHttp\Client $response returns a Guzzle Response
     */
    public function cancelOrder($order);
    /**
     * Puts a User in a queue to be sent to the commission engine.
     *
     * @param Order $order
     */
    public function queUser($user);
    /**
     * Sends data about a User to the Commission Engine.
     *
     * @param User $user
     * @param bool $initialize used for backfill
     * @return array $commResponse
     */
    public function addUser($user, $initialize = false);
    /**
     * Checks to see if the user is already in the commission engine.
     *
     * @param User $user
     * @return bool
     */
    public function findUser($user);
} 