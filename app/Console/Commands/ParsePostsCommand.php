<?php

namespace App\Console\Commands;

use App\Service\ParserDemiart;
use App\Service\ParserLaravelNews;
use Illuminate\Console\Command;

class ParsePostsCommand extends Command
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

        try
        {
            $ln = new ParserLaravelNews();
            $ln->parse() ? $this->info("Finished parsing - LaravelNews") : $this->error("Failed parsing Laravel News");

            $dm = new ParserDemiart();
            $dm->parse() ? $this->info("Finished parsing - Demiart") : $this->error("Failed parsing Demiart");
        }
        catch (\Exception $ex)
        {
            $this->warn($ex->getMessage());
        }
    }
}
