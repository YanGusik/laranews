<?php

namespace Tests\Feature;

use App\Post;
use App\Source;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostsTest extends TestCase
{

    use RefreshDatabase;
//    use DatabaseMigrations;

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
                        source{
                        id,
                        name
                        }
                        description,
                        image,
                        link,
                        date,

                    }
                }
            }
        ');
        $title = $response->json('data.*.data.*.title');
        $this->assertContains('Laravel 6 Is HERE', $title);
    }

}
