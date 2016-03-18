<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 *
 * A basic logger implementation that writes to standard output and error.
 *
 * @package producer/producer
 *
 */
class Stdlog implements LoggerInterface
{
    /**
     *
     * The stdout file handle.
     *
     * @var resource
     *
     */
    protected $stdout;

    /**
     *
     * The stderr file handle.
     *
     * @var resource
     *
     */
    protected $stderr;

    /**
     *
     * Constructor.
     *
     * @param resource $stdout Write to stdout on this handle.
     *
     * @param resource $stderr Write to stderr on this handle; if not provided,
     * write to stdout instead.
     *
     */
    public function __construct($stdout, $stderr = null)
    {
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        if (! $this->stderr) {
            $this->stderr = $this->stdout;
        }
    }

    /**
     *
     * System is unusable.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     *
     * Action must be taken immediately.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     *
     * Critical conditions.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     *
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     *
     * Exceptional occurrences that are not errors.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     *
     * Normal but significant events.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     *
     * Interesting events.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     *
     * Detailed debug information.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     *
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function log($level, $message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        $message = strtr($message, $replace) . PHP_EOL;
        fwrite($this->getHandle($level), $message);
    }

    /**
     *
     * Gets the handle to use for the log level.
     *
     * @param mixed $level
     *
     * @return resource
     *
     */
    protected function getHandle($level)
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
                return $this->stderr;
            default:
                return $this->stdout;
        }
    }
}
