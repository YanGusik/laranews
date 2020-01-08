<?php

namespace App\Helpers;

use Illuminate\Console\Command;

class ConsoleOutput
{
    /** @var Command */
    public static $runningCommand;

    public function setOutput(Command $runningCommand)
    {
        static::$runningCommand = $runningCommand;
    }

    public function bar()
    {
        return (static::$runningCommand)->getOutput();
    }

    public static function __callStatic(string $method, $arguments = null)
    {
        if (!static::$runningCommand) {
            return;
        }
        static::$runningCommand->$method(...$arguments);
    }
}
