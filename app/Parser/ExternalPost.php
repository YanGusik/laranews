<?php

namespace App\Parser;

use App\Post;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class ExternalPost implements Arrayable
{
    /***
     * @var string
     */
    private $title;

    /***
     * @var string
     */
    private $image;

    /***
     * @var string
     */
    private $link;

    /***
     * @var string
     */
    private $description;

    /***
     * @var Carbon
     */
    private $date;

    /***
     * ExternalPost constructor.
     * @param string $title
     * @param string $image
     * @param string $link
     * @param string $description
     * @param Carbon $date
     */
    public function __construct(string $title, string $image, string $link, string $description, Carbon $date)
    {
        $this->title = $title;
        $this->image = $image;
        $this->link = $link;
        $this->description = $description;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /***
     * @param Post $post
     * @return Post
     */
    public function fill(Post $post) : Post
    {
        $post->title = $this->title;
        $post->image = $this->image;
        $post->link = $this->link;
        $post->description = $this->description;
        $post->date = $this->date;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return [
          'title' => $this->title,
          'image' => $this->image,
          'link' => $this->link,
          'description' => $this->description,
          'date' => $this->date,
        ];
    }
}
