<?php

declare(strict_types=1);

namespace GreenElephpant\CodeBench\Test;

use GreenElephpant\CodeBench\CodeBench;
use PHPUnit\Framework\TestCase;

final class CodeBenchTest extends TestCase
{
    public function testBenchmarkToArray(): void
    {
        $result = CodeBench::benchmarkToArray([
            'func1' => function () {
                usleep(1);
            },
            'func2' => function () {
                usleep(2);
            }
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('func1', $result);
        $this->assertArrayHasKey('timestamp', $result['func1']);
        $this->assertArrayHasKey('execution_time', $result['func1']);
        $this->assertArrayHasKey('memory_usage', $result['func1']);
        $this->assertArrayHasKey('memory_peak_usage', $result['func1']);

        $this->assertArrayHasKey('func2', $result);
        $this->assertArrayHasKey('timestamp', $result['func2']);
        $this->assertArrayHasKey('execution_time', $result['func2']);
        $this->assertArrayHasKey('memory_usage', $result['func2']);
        $this->assertArrayHasKey('memory_peak_usage', $result['func2']);
    }

    public function testLoggerCallable(): void
    {
        $expectedLog = [];

        CodeBench::$loggerCallable = function(string $text) use (&$expectedLog) {
            $expectedLog[] = $text;
        };

        CodeBench::benchmark([
            'func1' => function () {
                usleep(1);
            }
        ]);

        CodeBench::$loggerCallable = null;

        $this->assertEquals('func1', $expectedLog[0]);
    }

    public function testStartAndStop(): void
    {
        $expectedLog = [];

        CodeBench::$loggerCallable = function(string $text) use (&$expectedLog) {
            $expectedLog[] = $text;
        };

        CodeBench::start();
        usleep(1000); // Simulate some processing time
        CodeBench::stop();

        $this->assertEquals('Stopwatch', $expectedLog[0]);
        $this->assertStringContainsString('Time (seconds):',$expectedLog[1]);
        $this->assertStringContainsString('Memory (MB):',$expectedLog[2]);
    }
}
