<?php

namespace App\Console\Commands;

use App\Parser\ParserDemiart;
use App\Parser\ParserLaravelNews;
use Illuminate\Console\Command;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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
//            $ln = new ParserLaravelNews();
//            $ln->parse() ? $this->info("Finished parsing - LaravelNews") : $this->error("Failed parsing Laravel News");
            $demiart = new ParserDemiart();
            if ($demiart->parse()) {
                $this->info("\nFinished parsing - Demiart");
            } else {
                $this->error("\nFailed parsing Demiart");
            }
        } catch (\Exception $ex) {
            $this->warn($ex->getMessage());
        }
    }
}
