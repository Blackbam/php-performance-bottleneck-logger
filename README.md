# PHP Performance Bottleneck Logger

Find bottlenecks in your PHP application and log or track them as required. Very easy to use.

## DISCLAIMER

Only for developers. Not yet verified.

## Usage

With default parameters and composer:

```
"autoload": {
    "files": ["vendor/blackbam/php-performance-bottleneck-logger/PerformanceBottleneckLogger.php"]
}
```

Parameters:

```
float $trackMinimumMicroTime = 5.0; // track all above this microseconds value (1.0 is one second)
bool $enableLogDefault = true; // wether to log by default or use the the shutdown closure instead
?Closure $shutdown = null; // pass a closure here which is called if a requests takes longer than the tracking minimum time
string $logLocation = "/var/logs/performance_bottleneck.log"; // path to your desired log file
string $logSep = "----------------------------------------"; // seperator within the logs
```

With custom parameters put the following to the first file loaded in your PHP application (adjust params to your needs):

```php
PerformanceBottleneckLogger::start($trackMinimumMicroTime, $enableLogDefault, $shutdown, $logLocation, $logSep);
```