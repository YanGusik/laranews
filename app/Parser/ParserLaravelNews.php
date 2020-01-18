<?php

namespace App\Parser;

use App\Source;
use App\Helpers\ConsoleOutput;
use App\Parser\Interfaces\ParseHtmlInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class ParserLaravelNews extends BaseParser implements ParseHtmlInterface
{
    // consts
    private const URL = "https://laravel-news.com";
    private const URI = "https://laravel-news.com/category/news";

    // statistics var
    private $page = 1; // Current Page
    private $countParsed = 0; // Count All Parsed Post
    private $countNew = 0; // Count New Post (unique)
    private $countOld = 0; // Count Old Post (non unique)

    private $crawler;
    private $consoleOutput;
    private $source;

    /***
     * ParserLaravelNews constructor.
     * @param Crawler $crawler
     * @param ConsoleOutput $consoleOutput
     * @uses \App\Source Model
     */
    public function __construct(Crawler $crawler, ConsoleOutput $consoleOutput)
    {
        parent::__construct($consoleOutput);
        $this->crawler = $crawler;
        $this->consoleOutput = $consoleOutput;
        $this->source = Source::whereName('Laravel News')->firstOrFail(); // TODO: REFACTORING THIS
    }

    /***
     * Parses posts on a page
     * @return Collection
     */
    public function parsePage(): Collection
    {
        try {
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
        } catch (\Exception $exception) {
            return new Collection();
        }
    }

    /***
     * Parses nodes
     * @param Crawler $node
     * @return Nodes
     */
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

    /***
     * Transforms nodes into posts
     * @param Nodes $nodes
     * @return ExternalPost
     */
    public function parsePost(Nodes $nodes): ExternalPost
    {
        $title = $nodes->title->text();
        $image = $nodes->image->attr('data-cfsrc');
        $link = self::URL . $nodes->link->attr('href');
        $description = $nodes->description->text();
        $date = Carbon::parse($nodes->date->text());
        return new ExternalPost($title, $image, $link, $description, $date);
    }

    /***
     * Starts an automatic parser of all pages as necessary and adds data to the database
     * @return void
     */
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

    /***
     * Return Global statistics
     * @return array
     */
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
