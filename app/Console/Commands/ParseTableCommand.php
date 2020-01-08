<?php

namespace App\Console\Commands;

use App\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;


class ParseTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all parsed posts';

    protected $posts;
    protected $headers = ['id', 'title', 'source', 'datetime'];

    /**
     * Create a new command instance.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        parent::__construct();
        $this->posts = $post::with('source')->orderBy('date')->get()->map(function ($post) {
            return [
              'id' => $post->id,
              'title' => $post->title,
              'source' => $post->sourcename,
              'date' => Carbon::parse($post->date)->diffForHumans()
            ];
        })->toArray();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->table($this->headers, $this->posts);
    }
}
