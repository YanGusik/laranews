<?php

namespace App\Parser;

use App\Source;
use App\Helpers\ConsoleOutput;
use App\Parser\Interfaces\ParseHtmlInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class ParserDemiart extends BaseParser implements ParseHtmlInterface
{
    // consts
    private const URL = "https://laravel.demiart.ru";
    private const URI = "https://laravel.demiart.ru/page/";

    // statistics var
    private $page = 1; // Current Page
    private $countParsed = 0; // Count All Parsed Post
    private $countNew = 0; // Count New Post (unique)
    private $countOld = 0; // Count Old Post (non unique)

    private $crawler;
    private $consoleOutput;
    private $source;

    /***
     * ParserDemiart constructor.
     * @param Crawler $crawler
     * @param ConsoleOutput $consoleOutput
     * @uses \App\Source Model
     */
    public function __construct(Crawler $crawler, ConsoleOutput $consoleOutput)
    {
        parent::__construct($consoleOutput);
        $this->crawler = $crawler;
        $this->consoleOutput = $consoleOutput;
        $this->source = Source::whereName('Demiart')->firstOrFail(); // TODO: REFACTORING THIS
    }

    /***
     * Parses posts on a page
     * @return Collection
     */
    public function parsePage(): Collection
    {
        try {
            $html = file_get_contents(self::URI . $this->page);
            $this->crawler->addHtmlContent($html, 'UTF-8');
            $postsNodes = $this->crawler->filter('main#main article');
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
        $nodes->title = $node->filter("div.card > div.card__body > header > h2 > a");
        $nodes->image = $node->filter("div.card > a.card__media > img");
        $nodes->link = $node->filter("div.card > a.card__media");
        $nodes->description = $node->filter("div.card > div.card__body > div.card__content > p");
        $nodes->date = $node->filter("div.card > div.card__body > footer.card__footer > span.posted-on > a > time");
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
        $image = $nodes->image->attr('src');
        $link = $nodes->link->attr('href');
        $description = $nodes->description->text();
        $date = Carbon::parse($nodes->date->attr('datetime'));
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
