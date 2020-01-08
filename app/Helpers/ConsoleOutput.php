<?php

namespace App\Helpers1;

use Illuminate\Console\Command;

class ConsoleOutput
{
    /** @var Command */
    public static $runningCommand;
    public $count = 0;

    public function setOutput(Command $runningCommand)
    {
        $this->count++;
        static::$runningCommand = $runningCommand;
    }

    public static function __callStatic(string $method, $arguments)
    {
        if (!static::$runningCommand) {
            return;
        }
        static::$runningCommand->$method(...$arguments);
    }
}
