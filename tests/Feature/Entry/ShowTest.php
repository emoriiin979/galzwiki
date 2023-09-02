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
     * 記事詳細取得 正常系テスト
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
                'post_at' => '2023-12-23 12:34:56',
                'is_publish' => true,
            ],
            [
                'id' => 2,
                'parent_entry_id' => 3,
                'post_user_id' => $user->id,
                'post_at' => '2023-12-23 12:34:56',
                'is_publish' => true,
            ],
            [
                'id' => 3,
                'post_user_id' => $user->id,
                'post_at' => '2023-12-23 12:34:56',
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
            'post_user_id' => $entries[0]->post_user_id,
            'post_at' => $entries[0]->post_at,
            'is_publish' => $entries[0]->is_publish,
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
     * 記事詳細取得 異常系テスト
     * 存在しないIDが指定された場合はエラーが返されること
     * GET /entries/{id} -> 404|405
     *
     * @dataProvider notFoundDataProvider
     * @param string $url
     * @param int $errorCode
     */
    public function test_show_404_notFound($url, $errorCode): void
    {
        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus($errorCode);
    }

    public function notFoundDataProvider(): array
    {
        return [
            [
                'url' => $this->endpoint . '/X',
                'errorCode' => 405,
            ],
            [
                'url' => $this->endpoint . '/999',
                'errorCode' => 404,
            ],
        ];
    }
}
