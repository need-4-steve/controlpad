<?php

namespace CPCommon\Log;

use Throwable;
use Monolog\Handler\AbstractProcessingHandler;
use Rollbar\Rollbar;
use Monolog\Logger;

class RollbarHandler extends AbstractProcessingHandler
{
    protected $levelMap = [
        Logger::DEBUG     => 'debug',
        Logger::INFO      => 'info',
        Logger::NOTICE    => 'info',
        Logger::WARNING   => 'warning',
        Logger::ERROR     => 'error',
        Logger::CRITICAL  => 'critical',
        Logger::ALERT     => 'critical',
        Logger::EMERGENCY => 'critical',
    ];

    private $isSetup = false;

    public function __construct()
    {
        parent::__construct(env('ROLLBAR_LEVEL', 'error'));
    }

    private function setup()
    {
        if (!$this->isSetup) {
            // Setup one time during the first write
            Rollbar::init(
                [
                    'access_token' => env('ROLLBAR_TOKEN'),
                    'environment' => strtolower(env('APP_ENV', 'local')),
                    'scrub_fields' => ['Apikey', 'Authorization', 'password', 'secret']
                ]
            );
            $this->isSetup = true;
        }
    }

    protected function write(array $record)
    {
        $this->setup();
        $user = isset(app('request')->user) ? app('request')->user : null;
        $context = array_merge(
            $record['context'],
            $record['extra'],
            [
                'level' => $this->levelMap[$record['level']],
                'monolog_level' => $record['level_name'],
                'channel' => $record['channel'],
                'datetime' => $record['datetime']->format('U'),
                'user' => $user
            ]
        );

        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $exception = $context['exception'];
            unset($context['exception']);
            Rollbar::report_exception($exception, $context);
        } else {
            Rollbar::report_message(
                $record['message'],
                $context['level'],
                $context
            );
        }
        $this->hasRecords = true;
    }

    public function flush()
    {
        if ($this->hasRecords) {
            Rollbar::flush();
            $this->hasRecords = false;
        }
    }

    public function close()
    {
        Rollbar::flush();
    }
}
