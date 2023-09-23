<?php

namespace Tests\Feature\Entry;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** @var string $endpoint */
    protected $endpoint = '/api/entries';

    /** @var array $headers */
    protected $headers = [
        'Accept' => 'application/json',
    ];

    /**
     * 事項詳細取得 正常系テスト
     * レスポンスデータが正しく格納されていること
     * GET /entries/{id} -> 200
     */
    public function test_show_200_fillResponseData(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection $entry */
        $entries = Entry::factory(3)->sequence(
            [
                'id' => 1,
                'parent_entry_id' => 2,
                'post_user_id' => $user->id,
                'is_publish' => true,
            ],
            [
                'id' => 2,
                'parent_entry_id' => 3,
                'post_user_id' => $user->id,
                'is_publish' => true,
            ],
            [
                'id' => 3,
                'post_user_id' => $user->id,
                'is_publish' => true,
            ],
        )->create();

        /** @var string $url */
        $url = $this->endpoint . '/' . $entries[0]->id;

        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data', [
            'id' => $entries[0]->id,
            'title' => $entries[0]->title,
            'subtitle' => $entries[0]->subtitle,
            'body' => $entries[0]->body,
            'parent_entry_id' => $entries[0]->parent_entry_id,
            'post_user_id' => $entries[0]->post_user_id,
            'is_publish' => $entries[0]->is_publish,
            'updated_at' => $entries[0]->updated_at,
            'parents' => [
                [
                    'id' => $entries[1]->id,
                    'title' => $entries[1]->title,
                    'depth' => -1,
                ],
                [
                    'id' => $entries[2]->id,
                    'title' => $entries[2]->title,
                    'depth' => -2,
                ],
            ],
        ]);
    }

    /**
     * 事項詳細取得 異常系テスト
     * 存在しないIDが指定された場合はエラーが返されること
     * GET /entries/{id} -> 404|405
     *
     * @dataProvider notFoundDataProvider
     * @param string $url
     */
    public function test_show_404_notFound($url): void
    {
        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(404);
    }

    public function notFoundDataProvider(): array
    {
        return [
            [
                'url' => $this->endpoint . '/X',
            ],
            [
                'url' => $this->endpoint . '/999',
            ],
        ];
    }
}
