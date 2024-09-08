<p align="center">
	<img src="./docs/images/green-elephpant-logo.svg" alt="Green ElePHPant" width="400">
</p>
<h1 align="center">Green ElePHPant - CodeBench</h1>

The **Green ElePHPant** wants to help you to reduce the carbon emissions of your software.

One way of doing so is to optimize the performance. Less CPU, RAM and Storage means reduced energy consumption, fewer 
carbon emissions, and less hardware to be produced. And, last but not least, the users of your software will benefit
from a faster application.

`GreenElephpant\CodeBench` is a simple benchmark tool that can help you to improve the performance of your code by 
benchmarking several possible solutions and helps you to decide which code to chose.

## Key concept

`GreenElephpant\CodeBench` simply measures the execution time and memory consumption of callables, which contain 
the code you want to measure. 

Shout-out to [PhpBench](https://github.com/phpbench/phpbench), which is a much better solution if you need super 
accurate measurement, because it runs the code to be measured in isolation. It also is meant to be used as test suite
similar to PHPUnit and can thus be used to catch performance regressions during CI/CD, which makes it extremely useful .
However, `GreenElephpant\CodeBench` is your coding compadre if a quick and rough measurement is enough to decide 
between several options.

## Installation

Simply use composer to add `GreenElephpant\CodeBench` to your project:

`composer require green-elephpant/code-bench --dev` 

*Note:* we use `require-dev` here, since `GreenElephpant\CodeBench` should not be used in production code.

*Note:* for Laravel, you can use the package [CodeBench Laravel](https://github.com/green-elephpant/code-bench-laravel).

*Note:* `GreenElephpant\CodeBench` works with PHP 7.4+ to support most code bases. However, only since PHP 8.2 the 
function[memory_reset_peak_usage](https://www.php.net/manual/en/function.memory-reset-peak-usage.php) is available. With
earlier verions, you will not be able to monitor the peak memory usage with this tool.

## Configuration

`GreenElephpant\CodeBench` is designed to require no other dependencies to keep its footprint small, so there is
nothing much to do.

### Set Logger callable

By default, `GreenElephpant\CodeBench` uses `print` for logging. However, you can set a callable which takes the output
and you can log it where you want.

For example, in Laravel you could do

```php
CodeBench::$loggerCallable = function (string $text) {
    Log::debug($text);
};
```

to use whatever logger you have defined.

*Note*: the [CodeBench Laravel](https://github.com/green-elephpant/code-bench-laravel) package does that for you.

## Usage

### Basic Usage

In its purest form, the usage is as easy as the following function call:

```php
use GreenElephpant\CodeBench\CodeBench;

CodeBench::benchmark([
    function () {
        // The first option we want to test    
        date('D M d Y H:m:s');
    },
    function () {
        // The second option we want to test
        (new DateTime())->format('D M d Y H:m:s');
    }
]);
```

The result should be something similar to this:

```
Function 1
Time (seconds): 0.00000310 (Reference)
Memory (MB): 0.00000000 (Reference)
Memory Peak (MB): 0.00000000 (Reference)

Function 2
Time (seconds): 0.00000405 (=> 1.31x)
Memory (MB): 0.00000000 (=> 0.00x)
Memory Peak (MB): 0.00000000 (=> 0.00x)
```

What does the output mean?

* `Time (seconds)` tells us the time in seconds that the callable needed to execute. Since the example is just a simple 
function call, it's just a fraction of a second.
* `Memory (MB)` is the difference of the used memory before and after the callable was executed
* `Memory Peak (MB)` is the biggest amount of memory (aka the peak) the callable required during its execution. Since PHP
frees memory during the execution time, this measurement is much more important if you want to find out if the callable
can hit the configured PHP memory limit.

### Iterations

Results can vary between several executions of the callable. To balance out any outliers, we can run several iterations
and take the average. This behaviour is controlled by the `$iterations` parameter. The following example will run the 
each callable 100 times: 

```php
CodeBench::benchmark([
    function () {
        date('D M d Y H:m:s');
    },
    function () {
        (new DateTime())->format('D M d Y H:m:s');
    }
], 100);
```

### Pre-run callable

By default, `GreenElephpant\CodeBench` executes the callable one time before starting with the actual measurement. This
is to avoid side effects by e.g. cache warmups. To disable this behaviour, run the benchmark with `$preRunCallable` set 
to `false`:

```php
CodeBench::benchmark([
    function () {
        date('D M d Y H:m:s');
    },
    function () {
        (new DateTime())->format('D M d Y H:m:s');
    }
], 100, false);
```
