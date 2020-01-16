<?php

namespace App\Parser;

use App\Helpers\ConsoleOutput;
use App\Interfaces\ParseSiteInterface;
use App\Post;
use App\Source;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class ParserLaravelNews extends BaseParser
{
    private const URL = "https://laravel-news.com";
    private const URI = "https://laravel-news.com/category/news";

    private $page = 1; // Current Page
    private $countParsed = 0; // Count All Parsed Post
    private $countNew = 0; // Count New Post (unique)
    private $countOld = 0; // Count Old Post (non unique)

    private $crawler;
    private $log;
    private $consoleOutput;
    private $source;

    /***
     * ParserLaravelNews constructor.
     * @param Crawler $crawler
     * @param ConsoleOutput $consoleOutput
     */
    public function __construct(Crawler $crawler, ConsoleOutput $consoleOutput)
    {
        parent::__construct($consoleOutput);
//        $this->log = $log;
        $this->crawler = $crawler;
        $this->consoleOutput = $consoleOutput;
        $this->source = Source::whereName('Laravel News')->firstOrFail(); // TODO: REFACTORING THIS
    }

    public function parsePage(): Collection
    {
        $html = file_get_contents(self::URI . "?page={$this->page}");
        $this->crawler->addHtmlContent($html, 'UTF-8');
        $postsNodes = $this->crawler->filter('div[class="w-full md:w-3/5 lg:w-2/3 px-5 mb-6"] div.card');
        $posts = new Collection();
        if ($postsNodes->count() > 1):
            $postsNodes->each(function (Crawler $node, $i) use (&$posts) {
                $nodes = $this->parseNodes($node);
                try {
                    $this->CheckNodes($nodes);
                    $post = $this->parsePost($nodes);
                    $posts->add($post);
                } catch (\Exception $exception) {
                    return;
                }
            });
        endif;
        $this->crawler->clear();
        return $posts;
    }

    public function parseNodes(Crawler $node): Nodes
    {
        $nodes = new Nodes();
        $nodes->title = $node->filter("div.post__content > h2 > a");
        $nodes->image = $node->filter("div.post__image > a > img");
        $nodes->link = $node->filter("div.post__content > h2 > a");
        $nodes->description = $node->filter("div.post__content > p");
        $nodes->date = $node->filter("div.post__content > span > span")->last();
        return $nodes;
    }

    public function parsePost(Nodes $nodes): ExternalPost
    {
        $title = $nodes->title->text();
        $image = $nodes->image->attr('data-cfsrc');
        $link = self::URL . $nodes->link->attr('href');
        $description = $nodes->description->text();
        $date = Carbon::parse($nodes->date->text());
        return new ExternalPost($title, $image, $link, $description, $date);
    }

    public function parse(): void
    {
        do {
            $data = $this->parsePage();
            $this->countParsed += $data->count();
            $this->countNew += $this->CountNewPosts($data);
            $this->countOld = $this->countParsed - $this->countNew;
            $this->page++;
            $this->SavePosts($data, $this->source);
        } while ($this->CheckPosts($this->parsePage()));

    }

    public function dump(): array
    {
        return [
            'Кол-во страниц' => $this->page - 1,
            'Кол-во распарсеных постов' => $this->countParsed,
            'Кол-во новых постов' => $this->countNew,
            'Кол-во старых постов' => $this->countOld
        ];
    }
}
