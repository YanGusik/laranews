<?php


namespace App\Parser;

use App\Helpers\ConsoleOutput;
use App\Post;
use App\Source;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Helper\ProgressBar;

class BaseParser
{

    /**
     * @var ConsoleOutput
     */
    private $consoleOutput;

    /**
     * BaseParser constructor.
     * @param ConsoleOutput $consoleOutput
     */
    public function __construct(ConsoleOutput $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }

    /***
     * Check for uniqueness of a post in the database
     * @param ExternalPost $post
     * @return bool
     */
    protected function CheckPost(ExternalPost $post): bool
    {
        return Post::whereLink($post->getLink())->doesntExist();
    }

    /***
     * Method checking the uniqueness of all posts in the collection
     * @param Collection $posts
     * @return bool
     */
    protected function CheckPosts(Collection $posts): bool
    {
        if ($posts->isEmpty())
            return false;
        return $posts->every(function (ExternalPost $post) {
            return $this->CheckPost($post);
        });
    }

    /***
     * Count unique post
     * @param Collection $posts
     * @return int|mixed
     */
    protected function CountNewPosts(Collection $posts): bool
    {
        return $posts->sum(function (ExternalPost $post) {
            return $this->CheckPost($post);
        });
    }

    /***
     * Check that on this site, all elements are present
     * @param Nodes $node
     * @throws Exception
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
            throw new Exception("Nodes not found: ". implode(', ', $errors));
    }

    /***
     * Save Post only unique
     * @param ExternalPost $data
     * @param Source $source
     */
    protected function SavePost(ExternalPost $data, Source $source): void
    {
        if (!$this->CheckPost($data))
            return;
        $post = new Post();
        $post->fill($data->toArray());
        $post->source()->associate($source);
        $post->save();
        $this->consoleOutput::info($data->getTitle());
    }

    /***
     * Save Many Post
     * @param Collection $data
     * @param Source $source
     */
    protected function SavePosts(Collection $data, Source $source): void
    {
        foreach ($data as $datum) {
            $this->SavePost($datum, $source);
        }
    }

}
