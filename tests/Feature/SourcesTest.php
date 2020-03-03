<?php

namespace Tests\Feature;

use App\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourcesTest extends TestCase
{

    use RefreshDatabase;

    /***
     * @var Source
     */
    private $source;

    protected function setUp(): void
    {
        parent::setUp();

        $this->source = factory(Source::class)->create([
            'name' => 'RusLaravelGroup'
        ]);
    }

    public function testQueriesPosts(): void
    {
        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                sources {
                    data {
                        id
                        name
                    }
                }
            }
        ');
        $title = $response->json('data.*.data.*.name');
        $this->assertContains('RusLaravelGroup', $title);
    }
}
