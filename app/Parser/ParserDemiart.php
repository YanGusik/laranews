<?php

namespace App\Service;

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
     *
     */
    public function parse() : bool
    {
        Log::info("Start Parsing " . self::URI);

        $html = file_get_contents(self::URI);

        $countNewPost = 0;

        $this->crawler->addHtmlContent($html, 'UTF-8');

        $posts = $this->crawler->filter('main#main article');

        Log::info("Number of posts found (Maybe not unique): {$posts->count()}");


        $source = Source::whereName('Demiart')->firstOrFail();

        if ($posts->count() > 0):
            $posts->each(function (Crawler $node, $i) use (&$arr, &$countNewPost, $source) {
                $imageNode = $node->filter("div.card > a.card__media > img");
                $linkNode = $node->filter("div.card > a.card__media");
                $titleNode = $node->filter("div.card > div.card__body > header > h2 > a");
                $descriptionNode = $node->filter("div.card > div.card__body > div.card__content > p");
                $dateNode = $node->filter("div.card > div.card__body > footer.card__footer > span.posted-on > a > time");

                if ($imageNode->count() == 0 || $dateNode->count() == 0 || $linkNode->count() == 0 || $descriptionNode->count() == 0 || $titleNode->count() == 0):
                    throw new \Exception("Node not found ");
                endif;

                $image = $imageNode->attr('src');
                $title = $titleNode->text();
                $date = Carbon::parse($dateNode->attr('datetime'));
                $description = $descriptionNode->text();
                $link = $linkNode->attr('href');


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
        else:
            throw new \Exception("Posts not found");
        endif;

        Log::info("Number of New Post: {$countNewPost}");

        return true;

    }


}
