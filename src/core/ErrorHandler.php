<?php

namespace blink\core;

use Psr\Log\LogLevel;

/**
 * Class ErrorHandler
 *
 * @package blink\core
 */
class ErrorHandler extends Object
{
    public $memoryReserveSize = 262144;
    public $exception;
    public $logger;
    public $discardExistingOutput;

    /**
     * Specifies the exception names that should not be reported to logger.
     *
     * @var array
     */
    public $notReport = [];

    private $_memoryReserve;

    public function init()
    {
        if (!$this->logger) {
            $this->logger = app('log');
        }

        $this->register();
    }

    /**
     * Register this error handler
     */
    public function register()
    {
        ini_set('display_errors', false);
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        if ($this->memoryReserveSize > 0) {
            $this->_memoryReserve = str_repeat('x', $this->memoryReserveSize);
        }
        register_shutdown_function([$this, 'handleFatalError']);
    }

    /**
     * Unregisters this error handler by restoring the PHP error and exception handlers.
     */
    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    public function handleException($exception)
    {
        $this->exception = $exception;

        // disable error capturing to avoid recursive errors while handling exceptions
        restore_error_handler();
        restore_exception_handler();

        try {
            $this->report($exception);
            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }
            //$this->renderException($exception);
        } catch (\Exception $e) {
            $this->report($exception);
            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }
        }

        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);

        $this->exception = null;
    }

    public function handleError($code, $message, $file, $line)
    {
        if (error_reporting() & $code) {
            // load ErrorException manually here because autoloading them will not work
            // when error occurs while autoloading a class
            if (!class_exists('blink\\core\\ErrorException', false)) {
                require_once(__DIR__ . '/ErrorException.php');
            }
            $exception = new ErrorException($message, $code, $code, $file, $line);

            // in case error appeared in __toString method we can't throw any exception
            $trace = debug_backtrace(0);
            array_shift($trace);
            foreach ($trace as $frame) {
                if ($frame['function'] === '__toString') {
                    $this->handleException($exception);
                    return true;
                }
            }

            throw $exception;
        }
        return false;
    }

    public function handleFatalError()
    {
        unset($this->_memoryReserve);

        // load ErrorException manually here because autoloading them will not work
        // when error occurs while autoloading a class
        if (!class_exists('blink\\core\\ErrorException', false)) {
            require_once(__DIR__ . '/ErrorException.php');
        }

        $error = error_get_last();

        if (ErrorException::isFatalError($error)) {
            $exception = new ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
            $this->exception = $exception;

            $this->report($exception);

            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }
            //$this->renderException($exception);

            // need to explicitly flush logs because exit() next will terminate the app immediately

            exit(1);
        }
    }

    protected function report($exception)
    {
        if (in_array(get_class($exception), $this->notReport, true)) {
            return;
        }

        $errorLevelMap = [
            E_ERROR             => LogLevel::CRITICAL,
            E_WARNING           => LogLevel::WARNING,
            E_PARSE             => LogLevel::ALERT,
            E_NOTICE            => LogLevel::NOTICE,
            E_CORE_ERROR        => LogLevel::CRITICAL,
            E_CORE_WARNING      => LogLevel::WARNING,
            E_COMPILE_ERROR     => LogLevel::ALERT,
            E_COMPILE_WARNING   => LogLevel::WARNING,
            E_USER_ERROR        => LogLevel::ERROR,
            E_USER_WARNING      => LogLevel::WARNING,
            E_USER_NOTICE       => LogLevel::NOTICE,
            E_STRICT            => LogLevel::NOTICE,
            E_RECOVERABLE_ERROR => LogLevel::ERROR,
            E_DEPRECATED        => LogLevel::NOTICE,
            E_USER_DEPRECATED   => LogLevel::NOTICE,
        ];

        if ($exception instanceof ErrorException) {
            $level = $errorLevelMap[$exception->getCode()];
            $this->logger->log($level, $exception);
        } else {
            $this->logger->error($exception);
        }
    }

    public function clearOutput()
    {
        // the following manual level counting is to deal with zlib.output_compression set to On
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }
}
