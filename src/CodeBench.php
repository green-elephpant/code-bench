<?php

declare(strict_types=1);

namespace GreenElephpant\CodeBench;

class CodeBench
{
    /**
     * @var callable|null
     */
    public static $loggerCallable;

    /**
     * @param array<string, array<string|int, (float|int)>> $results
     */
    private static function outputResults(array $results): void
    {
        $referenceLabel = false;

        foreach ($results as $label => $result) {
            $isReference = false;

            if (!$referenceLabel) {
                $referenceLabel = $label;
                $isReference = true;
            }

            $deviationTimeToReference = $results[$referenceLabel]['execution_time'] > 0
                ? ($result['execution_time'] / $results[$referenceLabel]['execution_time'])
                : 0;
            $deviationMemoryToReference = $results[$referenceLabel]['memory_usage'] > 0
                ? ($result['memory_usage'] / $results[$referenceLabel]['memory_usage'])
                : 0;
            $deviationMemoryPeakToReference = $results[$referenceLabel]['memory_peak_usage'] > 0
                ? ($result['memory_peak_usage'] / $results[$referenceLabel]['memory_peak_usage'])
                : 0;

            self::output($label);
            $referenceText = ' (Reference)';
            $timesFormat = ' (=> %.2fx)';

            // Time
            $timeResult = sprintf('Time (seconds): %2.8f', $result['execution_time']);
            $timeResult .= $isReference ? $referenceText : sprintf($timesFormat, $deviationTimeToReference);
            self::output($timeResult);

            // Memory
            $memoryResult = sprintf('Memory (MB): %.8f', $result['memory_usage'] / 1000 / 1000);
            $memoryResult .= $isReference ? $referenceText : sprintf($timesFormat, $deviationMemoryToReference);
            self::output($memoryResult);

            // MemoryPeak
            $memoryPeakResult = sprintf('Memory Peak (MB): %.8f', $result['memory_peak_usage'] / 1000 / 1000);
            $memoryPeakResult .= $isReference ? $referenceText : sprintf($timesFormat, $deviationMemoryPeakToReference);
            self::output($memoryPeakResult);

            self::output();
        }
    }

    private static function output(string $text = ""): void
    {
        if (is_callable(self::$loggerCallable)) {
            call_user_func(self::$loggerCallable, $text);
        } else {
            print $text . PHP_EOL;
        }
    }

    /**
     * @param array<string|int, callable> $callbacks
     */
    public static function benchmark(
        array $callbacks,
        int $iterations = 1,
        bool $preRunCallable = true
    ): void {
        self::outputResults(
            self::benchmarkToArray($callbacks, $iterations, $preRunCallable)
        );
    }

    /**
     * @param array<string|int, callable> $callbacks
     * @return array<string, array<string, (float|int)>>
     */
    public static function benchmarkToArray(
        array $callbacks,
        int $iterations = 1,
        bool $preRunCallable = true
    ): array {
        $results = [];

        foreach ($callbacks as $label => $callback) {
            if (is_int($label)) {
                $label = sprintf('Function %d', ($label + 1));
            }

            $results[$label] = self::benchmarkCallable($callback, $iterations, $preRunCallable);
        }

        return $results;
    }

    /**
     * @return array<string, (float|int)>
     */
    private static function benchmarkCallable(
        callable $callable,
        int $iterations = 1,
        bool $preRunCallable = true
    ): array {
        if ($preRunCallable) {
            $callable();
        }

        $startMemoryUsage = memory_get_usage();

        if (function_exists('memory_reset_peak_usage')) {
            memory_reset_peak_usage();
        }

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $callable();
        }

        $endTime = microtime(true);
        $endMemoryUsage = memory_get_usage();

        if (function_exists('memory_reset_peak_usage')) {
            $memoryPeak = memory_get_peak_usage();
        } else {
            $memoryPeak = 0;
        }

        return [
            'timestamp' => time(),
            'execution_time' => ($endTime - $startTime) / $iterations,
            'memory_usage' => ($endMemoryUsage - $startMemoryUsage) / $iterations,
            'memory_peak_usage' => $memoryPeak
        ];
    }
}
