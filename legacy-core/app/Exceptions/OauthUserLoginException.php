<?php

namespace App\Exceptions;

use Psr\Log\AbstractLogger;
use Monolog\Logger as Monolog;
use Exception;

class OauthUserLoginException extends Exception
{

    protected $message;     // exception message
    protected $code;        // user defined exception code
    protected $file;        // source filename of exception
    protected $line;        // source line of exception
    private $string;        // __toString cache
    private $array;         // __toArray cache
    private $trace;         // backtrace
    private $previous;      // previous exception if nested exception

    public function __construct(string $message = 'Unknown OauthUserLogin exception', int $code = 0, Exception $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function __toArray()
    {
        return [
            'code'     => $this->code,
            'message'  => $this->message,
            'file'     => $this->file,
            'line'     => $this->line,
            'previous' => $this->previous,
            'trace'    => $this->trace,
        ];
    }
}
