<?php

namespace Tests\Feature\Brief;

use App\Models\Brief;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** @var string $endpoint */
    protected $endpoint = '/api/briefs';

    /** @var array $headers */
    protected $headers = [
        'Accept' => 'application/json',
    ];

    /**
     * 記事詳細取得 正常系テスト
     * レスポンスデータが正しく格納されていること
     * GET /briefs/{id} -> 200
     */
    public function test_show_200_fillResponseData(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection $brief */
        $briefs = Brief::factory(3)->sequence(
            [
                'id' => 1,
                'entry_user_id' => $user->id,
                'entry_at' => '2023-12-23 12:34:56',
                'parent_brief_id' => 2,
                'is_publish' => true,
            ],
            [
                'id' => 2,
                'entry_user_id' => $user->id,
                'entry_at' => '2023-12-23 12:34:56',
                'parent_brief_id' => 3,
                'is_publish' => true,
            ],
            [
                'id' => 3,
                'entry_user_id' => $user->id,
                'entry_at' => '2023-12-23 12:34:56',
                'is_publish' => true,
            ],
        )->create();

        /** @var string $url */
        $url = $this->endpoint . '/' . $briefs[0]->id;

        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data', [
            'id' => $briefs[0]->id,
            'title' => $briefs[0]->title,
            'note' => $briefs[0]->note,
            'abstract' => $briefs[0]->abstract,
            'entry_user_id' => $briefs[0]->entry_user_id,
            'entry_at' => $briefs[0]->entry_at,
            'is_publish' => $briefs[0]->is_publish,
            'parents' => [
                [
                    'id' => $briefs[1]->id,
                    'title' => $briefs[1]->title,
                    'depth' => -1,
                ],
                [
                    'id' => $briefs[2]->id,
                    'title' => $briefs[2]->title,
                    'depth' => -2,
                ],
            ],
        ]);
    }

    /**
     * 記事詳細取得 異常系テスト
     * 存在しないIDが指定された場合はエラーが返されること
     * GET /briefs/{id} -> 404
     */
    public function test_show_404_notFound(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint . '/999';

        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(404);
    }
}
