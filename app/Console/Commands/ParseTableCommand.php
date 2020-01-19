<?php

namespace App\Console\Commands;

use App\Helpers\ConsoleOutput;
use App\Post;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;


class ParseTableCommand extends BaseCommand
{
    /**
     * The console command name!
     *
     * @var string
     */
    protected $name = 'parse:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all parsed posts';

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['Title', 'Image', 'Link', 'Description', 'Source', 'Date'];

    /**
     * The columns to display when using the "compact" flag.
     *
     * @var array
     */
    protected $compactColumns = ['title', 'link', 'source', 'date'];

    protected $post;

    /**
     * Create a new command instance.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        parent::__construct();
        $this->post = $post;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (empty($this->post::all())) {
            return $this->error("Your application doesn't have any posts") | false; // PHP Storm swears =)
        }

        if (empty($posts = $this->getPosts())) {
            return $this->error("Your application doesn't have any posts matching the given criteria.") | false;
        }

        $this->displayPosts($posts);
    }

    /**
     * Compile the posts into a displayable format.
     *
     * @return array
     */
    protected function getPosts()
    {
        $posts = collect($this->post->get())->map(function ($post) {
            return $this->getPostInformation($post);
        })->filter()->all();

        if ($sort = $this->option('sort')) {
            $posts = $this->sortPosts($sort, $posts);
        }

        if ($this->option('reverse')) {
            $posts = array_reverse($posts);
        }

        return $this->pluckColumns($posts);
    }

    /**
     * Remove unnecessary columns from the posts.
     *
     * @param  array  $posts
     * @return array
     */
    protected function pluckColumns(array $posts)
    {
        return array_map(function ($post) {
            return Arr::only($post, $this->getColumns());
        }, $posts);
    }

    /**
     * Get the column names to show (lowercase table headers).
     *
     * @return array
     */
    protected function getColumns()
    {
        $availableColumns = array_map('strtolower', $this->headers);

        if (filter_var( $this->option('compact'), FILTER_VALIDATE_BOOLEAN)) {
            return array_intersect($availableColumns, $this->compactColumns);
        }

        if ($columns = $this->option('columns')) {
            return array_intersect($availableColumns, $this->parseColumns($columns));
        }

        return $availableColumns;
    }

    /**
     * Parse the column list.
     *
     * @param  array  $columns
     * @return array
     */
    protected function parseColumns(array $columns)
    {
        $results = [];

        foreach ($columns as $i => $column) {
            if (Str::contains($column, ',')) {
                $results = array_merge($results, explode(',', $column));
            } else {
                $results[] = $column;
            }
        }

        return $results;
    }

    /**
     * Sort the posts by a given element.
     *
     * @param string $sort
     * @param array $posts
     * @return array
     */
    protected function sortPosts($sort, array $posts)
    {
        return Arr::sort($posts, function ($post) use ($sort) {
            return $post[$sort];
        });
    }

    /**
     * Get the post information for a given post.
     *
     * @param Post $post
     * @return array
     */
    protected function getPostInformation(Post $post)
    {
        return $this->filterPost([
            'title' => $post->title,
            'image' => $post->image,
            'link' => $post->link,
            'description' => $post->description,
            'source' => $post->source->name,
            'date' => $post->date,
        ]);
    }

    /**
     * Filter the post by title and description or source.
     *
     * @param  array  $posts
     * @return array|null|void
     */
    protected function filterPost(array $posts)
    {
        if (($this->option('title') && ! Str::contains($posts['title'], $this->option('title'))) ||
            $this->option('image') && ! Str::contains($posts['image'], $this->option('image')) ||
            $this->option('link') && ! Str::contains($posts['link'], $this->option('link')) ||
            $this->option('description') && ! Str::contains($posts['description'], $this->option('description')) ||
            $this->option('source') && ! Str::contains($posts['source'], $this->option('source')) ||
            $this->option('date') && ! Str::contains($posts['date'], $this->option('date'))) {
            return;
        }

        return $posts;
    }

    /**
     * Get the table headers for the visible columns.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return Arr::only($this->headers, array_keys($this->getColumns()));
    }

    public function displayPosts(array $posts)
    {
        $posts = array_slice($posts, 0, (int)$this->option('count'));
        if ($this->option('json')) {
            $this->line(json_encode(array_values($posts)));
            return;
        }

        $this->table($this->getHeaders(), $posts);

    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['columns', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Columns to include in the post table '],
            ['count', 'c', InputOption::VALUE_OPTIONAL, 'Only show method, URI and action columns (Examples: -c4 or --count=4)', 10],
            ['compact', null, InputOption::VALUE_OPTIONAL, 'Only show method, URI and action columns', true],
            ['json', null, InputOption::VALUE_NONE, 'Output the post list as JSON'],
            ['title', null, InputOption::VALUE_OPTIONAL, 'Filter the posts by title'],
            ['image', null, InputOption::VALUE_OPTIONAL, 'Filter the posts by image'],
            ['link', null, InputOption::VALUE_OPTIONAL, 'Filter the posts by link'],
            ['description', null, InputOption::VALUE_OPTIONAL, 'Filter the posts by description'],
            ['source', null, InputOption::VALUE_OPTIONAL, 'Filter the posts by source'],
            ['date', null, InputOption::VALUE_OPTIONAL, 'Filter the posts by date'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the posts'],
            ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (title, image, link, description, source, date) to sort by', 'date'],
        ];
    }

}
