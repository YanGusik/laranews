<?php

namespace App\Parser;

use App\Helpers\ConsoleOutput;
use App\Post;
use App\Source;
use App\Interfaces\ParseSiteInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ParserDemiart extends BaseParser implements ParseSiteInterface
{
    protected $crawler;
    private const URL = "https://laravel.demiart.ru";
    private const URI = "https://laravel.demiart.ru";

    public function __construct()
    {
        $this->crawler = new Crawler(null, self::URI);
    }

    /***
     * Parsing new Post
     *
     * @throws \Exception
     */
    public function parse(): bool
    {
        Log::info("Start Parsing " . self::URI);

        $html = file_get_contents(self::URI);

        $countNewPost = 0;

        $this->crawler->addHtmlContent($html, 'UTF-8');

        $posts = $this->crawler->filter('main#main article');

        Log::info("Number of posts found (Maybe not unique): {$posts->count()}");

        $source = Source::whereName('Demiart')->firstOrFail();

        $bar = app(ConsoleOutput::class)->getOutput()->createProgressBar($posts->count());

        if ($posts->count() > 0):
            $bar->start();
            $posts->each(function (Crawler $node) use (&$arr, &$countNewPost, $source, &$bar) {
                $bar->advance();
                $nodes = new Nodes();
                $nodes->title = $node->filter("div.card > div.card__body > header > h2 > a");
                $nodes->image = $node->filter("div.card > a.card__media > img");
                $nodes->link = $node->filter("div.card > a.card__media");
                $nodes->description = $node->filter("div.card > div.card__body > div.card__content > p");
                $nodes->date = $node->filter("div.card > div.card__body > footer.card__footer > span.posted-on > a > time");

                $this->CheckNodes($nodes);

                $title = $nodes->title->text();
                $image = $nodes->image->attr('src');
                $link = $nodes->link->attr('href');
                $description = $nodes->description->text();
                $date = Carbon::parse($nodes->date->attr('datetime'));

                if ($this->CheckPost($link)):
                    $post = [];
                    $post['image'] = $image;
                    $post['date'] = $date;
                    $post['title'] = $title;
                    $post['link'] = $link;
                    $post['description'] = $description;
                    $this->SavePost($post, $source);
                    $countNewPost++;
                endif;
            });
            $bar->finish();
        else:
            throw new \Exception("Posts not found");
        endif;

        Log::info("Number of New Post: {$countNewPost}");

        return true;

    }
}
