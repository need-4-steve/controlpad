<?php

namespace CPCommon\Events;

use Illuminate\Contracts\Queue\Job;

class EventJob implements Job
{

    protected $deleted = false;
    protected $released = false;
    protected $tries = 0;
    protected $maxTries = null;
    protected $connectionName = null;
    protected $queue = null;
    protected $rawBody = null;

    public function __construct($rawBody, $tries, $connectionName, $queue, $maxTries)
    {
        $this->rawBody = $rawBody;
        $this->tries = $tries;
        $this->connectionName = $connectionName;
        $this->queue = $queue;
        $this->maxTries = $maxTries;
    }

     /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        $event = json_decode($this->rawBody);
        event('generic-event.' . $event->event, GenericEvent::fromObject($event));
    }

    public function hasFailed()
    {
        // TODO implement
        return false;
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return mixed
     */
    public function release($delay = 0)
    {
        // TODO implement delay
        $this->released = true;
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        $this->deleted = true;
    }

    /**
     * Determine if the job has been deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    public function isReleased()
    {
        return $this->released;
    }

    /**
     * Determine if the job has been deleted or released.
     *
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        return $this->deleted || $this->released;
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return $this->tries;
    }

    /**
     * Process an exception that caused the job to fail.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function failed($e)
    {
        app('log')->error($e);
        // TODO will the job need requeued?
    }

    /**
     * Get the number of times to attempt a job.
     *
     * @return int|null
     */
    public function maxTries()
    {
        return $this->maxTries;
    }

    /**
     * Get the number of seconds the job can run.
     *
     * @return int|null
     */
    public function timeout()
    {
        return null;
    }

    /**
     * Get the timestamp indicating when the job should timeout.
     *
     * @return int|null
     */
    public function timeoutAt()
    {
        return null;
    }

    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName()
    {
        return EventJob::class;
    }

    /**
     * Get the resolved name of the queued job class.
     *
     * Resolves the name of "wrapped" jobs such as class-based handlers.
     *
     * @return string
     */
    public function resolveName()
    {
        return $this->getName();
    }

    /**
     * Get the name of the connection the job belongs to.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Get the name of the queue the job belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }
}
