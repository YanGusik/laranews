<?php

namespace App\Console\Commands;

use App\Helpers\ConsoleOutput;
use App\Parser\ParserDemiart;
use App\Parser\ParserLaravelNews;
use App\Source;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class ParsePostsCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse posts from laravel news sites and add data to the database';
    /**
     * @var Crawler
     */
    private $crawler;
    /**
     * @var ConsoleOutput
     */
    private $consoleOutput;
    /**
     * @var Source
     */
    private $source;


    /**
     * Create a new command instance.
     *
     * @param Crawler $crawler
     * @param ConsoleOutput $consoleOutput
     * @param Source $source
     */
    public function __construct(Crawler $crawler, ConsoleOutput $consoleOutput, Source $source)
    {
        parent::__construct();
        $this->crawler = $crawler;
        $this->consoleOutput = $consoleOutput;
        $this->source = $source;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Start parsing");

        try {
            // 1
            $this->info("Laravel News:");
            $ln = new ParserLaravelNews($this->crawler, $this->consoleOutput);
            $ln->parse();

            foreach ($ln->dump() as $header => $value) {
                $this->info($header . ': ' . $value);
            }

            $this->info("\n");

            // 2
            $this->info("Demiart:");
            $dm = new ParserDemiart($this->crawler, $this->consoleOutput);
            $dm->parse();

            foreach ($dm->dump() as $header => $value) {
                $this->info($header . ': ' . $value);
            }

            $this->info("\n");

        } catch (\Exception $ex) {
            $this->warn($ex->getMessage());
        }
    }
}
