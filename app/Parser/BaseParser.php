<?php


namespace App\Parser;

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
     * @param Nodes $node
     * @throws \Exception
     */
    protected function CheckNodes(Nodes $node): void
    {
        $errors = [];
        if ($node->title->count() == 0)
            $errors[] = 'title';
        if ($node->image->count() == 0)
            $errors[] = 'image';
        if ($node->link->count() == 0)
            $errors[] = 'link';
        if ($node->description->count() == 0)
            $errors[] = 'description';
        if ($node->date->count() == 0)
            $errors[] = 'date';
        if (count($errors) > 0)
            throw new \Exception("Nodes not found: ", implode(', ', $errors));
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
        Log::info("Add new post in bd: {$post->link}");
    }

}
