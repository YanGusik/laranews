<?php

namespace App\Parser;

use App\Source;
use App\Parser\Nodes;
use App\Parser\BaseParser;
use App\Helpers\ConsoleOutput;
use App\Interfaces\ParseSiteInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ParserLaravelNews extends BaseParser implements ParseSiteInterface
{
    protected $crawler;
    private const URL = "https://laravel-news.com";
    private const URI = "https://laravel-news.com/category/news";

    public function __construct()
    {
        $this->crawler = new Crawler(null, self::URI);
    }

    /***
     *
     */
    public function parse() : bool
    {
        Log::info("Start Parsing " . self::URI);

        $html = file_get_contents(self::URI);

        $countNewPost = 0;

        $this->crawler->addHtmlContent($html, 'UTF-8');

        $posts = $this->crawler->filter('div[class="w-full md:w-3/5 lg:w-2/3 px-5 mb-6"] div.card');

        Log::info("Number of posts found (Maybe not unique): {$posts->count()}");

        $source = Source::whereName('Laravel News')->firstOrFail();

        $bar = app(ConsoleOutput::class)->getOutput()->createProgressBar($posts->count());

        if ($posts->count() > 0):
            $bar->start();
            $posts->each(function (Crawler $node, $i) use (&$arr, &$countNewPost, $source, &$bar) {
                $bar->advance();

                $nodes = new Nodes();
                $nodes->title = $node->filter("div.post__content > h2 > a");
                $nodes->image = $node->filter("div.post__image > a > img");
                $nodes->link = $node->filter("div.post__content > h2 > a");
                $nodes->description = $node->filter("div.post__content > p");
                $nodes->date = $node->filter("div.post__content > span > span")->last();

                $this->CheckNodes($nodes);

                $title = $nodes->title->text();
                $image =  $nodes->image->attr('data-cfsrc');
                $link = self::URL . $nodes->link->attr('href');
                $description = $nodes->description->text();
                $date = Carbon::parse($nodes->date->text());

                // dublicate code in ParserDemiart
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
