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
}
