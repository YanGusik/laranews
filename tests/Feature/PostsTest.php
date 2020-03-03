<?php

namespace Tests\Feature;

use App\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostsTest extends TestCase
{

    use RefreshDatabase;

    /***
     * @var Post
     */
    private $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->post = factory(Post::class)->create([
            'title' => 'Laravel 6 Is HERE',
            'description' => 'Its a magic'
        ]);
    }

    public function testQueriesPosts(): void
    {
        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                posts {
                    data {
                        id
                        title
                    }
                }
            }
        ');
        $title = $response->json('data.*.data.*.title');
        $this->assertContains('Laravel 6 Is HERE', $title);
    }

}
