<?php

namespace App\Service;

use App\Post;
use App\Interfaces\ParseSiteInterface;
use App\Source;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ParserLaravelNews implements ParseSiteInterface
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

        if ($posts->count() > 0):
            $posts->each(function (Crawler $node, $i) use (&$arr, &$countNewPost, $source) {
                $imageNode = $node->filter("div.post__image > a > img");
                $dateNode = $node->filter("div.post__content > span > span")->last();
                $linkNode = $node->filter("div.post__content > h2 > a");
                $descriptionNode = $node->filter("div.post__content > p");

                if ($imageNode->count() == 0 || $dateNode->count() == 0 || $linkNode->count() == 0 || $descriptionNode->count() == 0):
                    throw new \Exception("Node not found");
                endif;

                $image = $imageNode->attr('data-cfsrc');
                $date = Carbon::parse($imageNode->text());
                $title = $linkNode->text();
                $link = self::URL . $linkNode->attr('href');
                $description = $descriptionNode->text();

                if ($this->CheckPost($link)):
                    $post = new Post();
                    $post->image = $image;
                    $post->date = $date;
                    $post->title = $title;
                    $post->source()->associate($source);
                    $post->link = $link;
                    $post->description = $description;
                    $post->save();
                    Log::info("Added post to the database: {$post->link}");
                    $countNewPost++;
                endif;
            });
        else:
            throw new \Exception("Posts not found");
        endif;

        Log::info("Number of New Post: {$countNewPost}");

        return true;

    }

    /***
     * Check for uniqueness of a post in the database
     * @param string $link
     * @return bool
     */
    private function CheckPost(string $link): bool
    {
        return !Post::whereLink($link)->exists();
    }
}
