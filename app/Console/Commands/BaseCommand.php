<?php


namespace App\Console\Commands;


use App\Helpers\ConsoleOutput;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    public function run(InputInterface $input, OutputInterface $output): int
    {
//        (new ConsoleOutput())->setOutput($this); LOL 16 line === 17 line
        app(ConsoleOutput::class)->setOutput($this);
        return parent::run($input, $output);
    }
}
