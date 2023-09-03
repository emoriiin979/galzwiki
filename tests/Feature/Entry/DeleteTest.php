<?php

namespace Tests\Feature\Entry;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @var string $endpoint */
    protected $endpoint = '/api/entries';

    /** @var array $headers */
    protected $headers = [
        'Accept' => 'application/json',
    ];

    /**
     * 事項削除 正常系テスト
     * データが正常に削除されること
     * DELETE /entries/{id} -> 204
     */
    public function test_delete_204(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Entry $entry */
        $entry = Entry::factory()->create([
            'post_user_id' => $user->id,
        ]);

        /** @var string $url */
        $url = $this->endpoint . '/' . $entry->id;

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->delete($url, [], $this->headers);
        $this->post('logout');

        $response->assertStatus(204);
        $this->assertSoftDeleted($entry);
    }

    /**
     * 事項削除 異常系テスト
     * ログインしていない場合はエラーが返されること
     * PATCH /entries/{id} -> 401
     */
    public function test_delete_401(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint . '/1';

        // Act
        $response = $this->delete($url, [], $this->headers);

        // Assert
        $response->assertStatus(401);
    }

    /**
     * 事項削除 異常系テスト
     * 存在しないIDが指定された場合はエラーが返されること
     * PATCH /entries/{id} -> 404
     *
     * @dataProvider notFoundDataProvider
     * @param string $url
     */
    public function test_delete_404_notFound($url): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->delete($url, [], $this->headers);
        $this->post('logout');

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
