<?php

namespace Blackbam\PHPPerformanceBottleneckLogger;

use CisTools\StringArtist;
use CisTools\Url;
use Closure;
use DateTime;
use JsonException;

/**
 * To get the actual execution time this has to be called directly when the script starts.
 */
class PerformanceBottleneckLogger
{

    protected static float $start;

    protected static float $trackMinimumMicroTime;
    protected static bool $enableDefaultLog;
    protected static Closure $shutdown;
    protected static string $logLocation;
    protected static string $logSep;

    public static function start(
        float $trackMinimumMicroTime = 5.0,
        bool $enableLogDefault = true,
        ?Closure $shutdown = null,
        string $logLocation = "/var/logs/performance_bottleneck.log",
        string $logSep = "----------------------------------------"
    ): void {
        static::$start = microtime(true);
        static::$trackMinimumMicroTime = $trackMinimumMicroTime;
        static::$enableDefaultLog = $enableLogDefault;
        static::$logLocation = $logLocation;
        static::$logSep = $logSep;
        is_null($shutdown) ? (static::$shutdown = static function () { /* */ }) : static::$shutdown = $shutdown;
        register_shutdown_function([self::class, 'executeShutdown']);
    }

    /**
     * @return void
     */
    public static function executeShutdown(): void
    {
        if (self::shallTrackRequest()) {
            if (self::$enableDefaultLog) {
                self::log();
            }
            if (!is_null(self::$shutdown)) {
                $func = self::$shutdown;
                $func();
            }
        }
    }

    /**
     * @return float
     */
    public static function getShutdownExecutionTime(): float
    {
        return microtime(true) - static::$start;
    }

    /**
     * @return bool
     */
    public static function shallTrackRequest(): bool
    {
        return static::getShutdownExecutionTime() > static::$trackMinimumMicroTime;
    }

    /**
     * @param Closure $shutdown
     * @return void
     */
    public static function setShutdownCallback(Closure $shutdown): void
    {
        self::$shutdown = $shutdown;
    }

    /**
     * @return void
     */
    protected static function log(): void
    {
        $errorFile = self::$logLocation;
        $error = [];
        $error[] = "Start: " . (DateTime::createFromFormat('U.u', microtime(true)))->format("m-d-Y H:i:s.u");
        $error[] = "Seconds: " . microtime(true) - self::$start;
        $error[] = static::getCallInfo();
        $error[] = static::$logSep . PHP_EOL;
        file_put_contents($errorFile, implode(PHP_EOL, $error), FILE_APPEND | LOCK_EX);
    }

    /**
     * Get information of a request for profiling purposes
     *
     * @return string
     */
    protected static function getCallInfo(): string
    {
        $res = "URL:" . Url::getCurrent() . PHP_EOL;
        $res .= "Request Method: " . $_SERVER['REQUEST_METHOD'] . PHP_EOL;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = json_encode($_POST, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $data = "Can not be logged.";
            }
            $res .= "DATA: " . StringArtist::limitWords($data, 5000) . PHP_EOL;
        }

        return $res;
    }
}
