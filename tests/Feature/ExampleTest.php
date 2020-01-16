<?php

namespace Tests\Feature;

use App\Helpers\ConsoleOutput;
use App\Parser\ParserLaravelNews;
use App\Post;
use App\Source;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class ExampleTest extends TestCase
{
//    use DatabaseMigrations;
    use RefreshDatabase;


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

    public function setUp() : void
    {
        parent::setUp();

        $this->crawler = app(Crawler::class);
        $this->consoleOutput = app(ConsoleOutput::class);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
//        $parser = new ParserLaravelNews($this->crawler, $this->consoleOutput);
//        $parser->parse();
    }
}
