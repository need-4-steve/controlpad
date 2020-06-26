?php
namespace App\Console\Commands;
use Exception;
use Throwable;
use Illuminate\Queue\Worker;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Debug\Exception\FatalThrowableError;
class ProcessQueueAndExit extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature =
        'queue:work-and-exit
        {connection? : The name of the queue connection to work}
        {--queue= : The names of the queues to work}
        {--daemon : Run the worker in daemon mode (Deprecated)}
        {--once : Only process the next job on the queue}
        {--delay=0 : Amount of time to delay failed jobs}
        {--force : Force the worker to run even in maintenance mode}
        {--memory=128 : The memory limit in megabytes}
        {--sleep=3 : Number of seconds to sleep when no job is available}
        {--timeout=60 : The number of seconds a child process can run}
        {--tries=0 : Number of times to attempt a job before logging it failed}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process each job on the queue and exit';
    /**
     * The queue worker instance.
     *
     * @var \Illuminate\Queue\Worker
     */
    protected $worker;
    /**
     * Create a new queue listen command.
     *
     * @param  \Illuminate\Queue\Worker $worker
     */
    public function __construct(Worker $worker)
    {
        parent::__construct();
        $this->worker = $worker;
    }
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // We'll listen to the processed and failed events so we can write information
        // to the console as jobs are processed, which will let the developer watch
        // which jobs are coming through a queue and be informed on its progress.
        $this->listenForEvents();
        $connection = Queue::connection($this->argument('connection'));
        // We need to get the right queue for the connection which is set in the queue
        // configuration file for the application. We will pull it based on the set
        // connection being run for the queue operation currently being executed.
        $queue = $this->getQueue($connection);
        $this->runWorker($connection, $queue);
    }
    /**
     * Run the worker instance.
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $connection
     * @param  string  $queue
     */
    protected function runWorker($connection, $queue)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->worker->setCache(
            $this->laravel['cache']->driver()
        );
        while ($job = $this->getNextJob($connection, $queue)) {
            $this->runJob($job, $connection, $this->gatherWorkerOptions());
        }
    }
    /**
     * Get the next job from the queue connection.
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $connection
     * @param  string $queue
     * @return Job|null
     * @internal param \Illuminate\Contracts\Queue\Queue $connection
     */
    protected function getNextJob($connection, $queue)
    {
        try {
            foreach (explode(',', $queue) as $queue) {
                if (! is_null($job = $connection->pop($queue))) {
                    return $job;
                }
            }
        } catch (Exception $e) {
            Log::error($e);
        } catch (Throwable $e) {
            Log::error(new FatalThrowableError($e));
        }
        return null;
    }
    /**
     * Gather all of the queue worker options as a single object.
     *
     * @return \Illuminate\Queue\WorkerOptions
     */
    protected function gatherWorkerOptions()
    {
        return new WorkerOptions(
            $this->option('delay'),
            $this->option('memory'),
            $this->option('timeout'),
            $this->option('sleep'),
            $this->option('tries'),
            $this->option('force')
        );
    }
    /**
     * Listen for the queue events in order to update the console output.
     *
     * @return void
     */
    protected function listenForEvents()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->laravel['events']->listen(JobProcessed::class, function ($event) {
            $this->writeOutput($event->job, false);
        });
        /** @noinspection PhpUndefinedMethodInspection */
        $this->laravel['events']->listen(
            JobFailed::class,
            function ($event) {
                $this->writeOutput($event->job, true);
                $this->logFailedJob($event);
            }
        );
    }
    /**
     * Write the status output for the queue worker.
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  bool  $failed
     * @return void
     */
    protected function writeOutput(Job $job, $failed)
    {
        if ($failed) {
            $this->output->writeln(
                '<error>['.date('c').'] Failed:</error> '.$job->resolveName()
            );
        } else {
            $this->output->writeln(
                '<info>['.date('c').'] Processed:</info> '.$job->resolveName()
            );
        }
    }
    /**
     * Store a failed job event.
     *
     * @param  JobFailed  $event
     * @return void
     */
    protected function logFailedJob(JobFailed $event)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->laravel['queue.failer']->log(
            $event->connectionName,
            $event->job->getQueue(),
            $event->job->getRawBody(),
            $event->exception
        );
    }
    /**
     * Get the queue name for the worker.
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $connection
     * @return string
     */
    protected function getQueue($connection)
    {
        $name = 'database';
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->option('queue') ?:
            $this->laravel['config']->get(
                "queue.connections.{$name}.queue",
                'default'
            );
    }
    /**
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  \Illuminate\Contracts\Queue\Queue  $connection
     * @param WorkerOptions $options
     */
    protected function runJob($job, $connection, WorkerOptions $options)
    {
        $name = 'database';
        try {
            $this->worker->process($name, $job, $options);
        } catch (Exception $e) {
            Log::error($e);
        } catch (Throwable $e) {
            Log::error(new FatalThrowableError($e));
        }
    }
}