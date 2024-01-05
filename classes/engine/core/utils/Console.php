<?php

namespace JaxWilko\Game\Classes\Engine\Core\Utils;

use Closure;
use Weird\Messages\Events\Hint;
use function Termwind\render;

/**
 * @class Console
 * This class is used for the console output from the engine, it also supports writing to a custom stdout which is
 * used when the game server is being run in daemonized mode.
 */
class Console
{
    /**
     * @var resource file handle for outputting to
     */
    protected static mixed $handle = null;

    /**
     * @var array statistics gathered via a tick, used for debugging
     */
    protected static array $statistics = [];

    /**
     * @var bool print to STDOUT (without writing to the $handle)
     */
    protected static bool $stdOut = true;

    /**
     * @var bool disable / enable outputting
     */
    protected static bool $output = true;

    /**
     * Set an output handle (file pointer).
     *
     * @param resource $handle
     * @return void
     */
    public static function setOutputHandle(mixed $handle): void
    {
        static::$handle = $handle;
    }

    /**
     * Enable / disable STDOUT printing.
     *
     * @param bool $stdOut
     * @return void
     */
    public static function setStdOut(bool $stdOut): void
    {
        static::$stdOut = $stdOut;
    }

    /**
     * Enable / disable outputting.
     *
     * @param bool $enabled
     * @return void
     */
    public static function output(bool $enabled): void
    {
        static::$output = $enabled;
    }

    /**
     * Execute the callable without outputting any console messages.
     *
     * @param callable $callable
     * @return mixed
     */
    public static function withoutOutput(callable $callable): mixed
    {
        static::output(false);
        $result = $callable();
        static::output(true);

        return $result;
    }

    /**
     * Print to the console, supports printf args.
     *
     * @param string $message
     * @param mixed ...$args
     * @return void
     */
    public static function put(string $message, mixed ...$args): void
    {
        if (!static::$output) {
            return;
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        if (defined('WEIRD_SPAWNED_PROCESS')) {
            Hint::send([
                'file' => $backtrace['file'],
                'line' => $backtrace['line'],
                'message' => sprintf($message, ...$args)
            ]);
            return;
        }

        static::out(
            sprintf($message, ...$args),
            $backtrace['file'],
            $backtrace['line'],
        );
    }

    /**
     * Print mixed data to the console.
     *
     * @param mixed $data
     * @return void
     */
    public static function dump(mixed $data): void
    {
        if (!static::$output) {
            return;
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        static::outRaw([
            $backtrace['file'] . '@' . $backtrace['line'] => $data
        ]);
    }

    /**
     * Handler for Wierd's Hint system.
     *
     * @param mixed $message
     * @return void
     */
    public static function hintHandle(mixed $message): void
    {
        if (is_array($message)) {
            static::out(
                $message['message'],
                $message['file'] ?? 'Hint',
                $message['line'] ?? 0,
            );
            return;
        }

        static::out(
            $message,
            'Hint',
            0
        );
    }

    /**
     * Handler for Wierd's UnknownMessage system.
     *
     * @param mixed $message
     * @return void
     */
    public static function unknownHandle(mixed $message): void
    {
        static::out(
            'Console',
            'Unknown Message',
            0
        );

        static::outRaw($message);
    }

    /**
     * Returns statistics.
     *
     * @return array
     */
    public static function getStatistics(): array
    {
        return static::$statistics;
    }

    /**
     * Clears statistics.
     *
     * @return void
     */
    public static function clearStatistics(): void
    {
        static::$statistics = [];
    }

    /**
     * Pushes an array of statistics by key onto the internal statistics array.
     *
     * @param string $key
     * @param array $statistics
     * @return void
     */
    public static function addStatistics(string $key, array $statistics): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        static::$statistics[sprintf('%s@%s', $backtrace['file'], $backtrace['line'])][$key][] = $statistics;
    }

    /**
     * Generates a name for a callable.
     *
     * @param mixed $callable
     * @return string
     */
    public static function getCallableName(mixed $callable): string
    {
        if (is_string($callable)) {
            return trim($callable);
        } elseif (is_array($callable)) {
            if (is_object($callable[0])) {
                return sprintf("%s::%s", get_class($callable[0]), trim($callable[1]));
            } else {
                return sprintf("%s::%s", trim($callable[0]), trim($callable[1]));
            }
        } elseif ($callable instanceof Closure) {
            return 'closure';
        } else {
            return 'unknown';
        }
    }

    /**
     * Generates a console output message markup, then passes the markup to the write() method.
     *
     * @param string $str
     * @param string $file
     * @param int $line
     * @return void
     */
    protected static function out(string $str, string $file, int $line): void
    {
        list($date, $time) = explode('|', date('Y-m-d|H:i:s'));
        $fileName = str_before(basename($file), '.php');

        $markup = <<<HTML
            <div class="flex flex-row">
                <div>
                    <span class="text-yellow-600">[</span>
                    <span class="text-blue-600" title="$date $time">$time</span>
                    <span class="text-white px-1">|</span>
                    <span class="text-green-600" title="$file">$fileName</span>
                    <span class="text-white">:</span>
                    <span class="text-red-600">$line</span>
                    <span class="text-yellow-600">]</span>
                </div>
                <em class="ml-1">
                    $str
                </em>
            </div>
        HTML;

        static::write($markup);
    }

    /**
     * Generates a console output message with minimal markup, then passes the markup to the write() method.
     *
     * @param mixed $str
     * @return void
     */
    protected static function outRaw(mixed $str): void
    {
        if (!is_string($str)) {
            $str = print_r($str, true);
        }

        $markup = <<<HTML
            <div>
                <pre class="ml-1">$str</pre>
            </div>
        HTML;

        static::write($markup);
    }

    /**
     * Writes output to the file handle if set and to STDOUT if enabled.
     *
     * @param string $markup
     * @return void
     */
    protected static function write(string $markup): void
    {
        if (static::$handle) {
            fwrite(static::$handle, $markup);
        }

        if (static::$stdOut) {
            render($markup);
        }
    }
}
