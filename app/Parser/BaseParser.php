<?php


namespace App\Service;

use App\Post;
use App\Source;

class BaseParser
{
    /***
     * Check for uniqueness of a post in the database
     * @param string $link
     * @return bool
     */
    protected function CheckPost(string $link): bool
    {
        return Post::whereLink($link)->doesntExist();
    }

    /***
     * Check that on this site, all elements are present
     */
    protected function CheckNodes()
    {

    }

    /***
     * Save Post
     * @param $data
     * @param Source $source
     */
    protected function SavePost($data, Source $source): void
    {
        $post = new Post();
        $post->image = $data['image'];
        $post->date = $data['date'];
        $post->title = $data['date'];
        $post->source()->associate($source);
        $post->link = $data['link'];
        $post->description = $data['description'];
        $post->save();
    }

}
