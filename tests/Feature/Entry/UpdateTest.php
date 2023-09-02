<?php

namespace Tests\Feature\Entry;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @var string $endpoint */
    protected $endpoint = '/api/entries';

    /** @var array $headers */
    protected $headers = [
        'Accept' => 'application/json',
    ];

    /**
     * 事項更新 正常系テスト
     * コミット用データの通りに正しく更新されること
     * PATCH /entries/{id} -> 204
     */
    public function test_update_204(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Entry $entry */
        $entry = Entry::factory()->create([
            'title' => '更新前',
            'subtitle' => '更新前',
            'body' => '更新前',
            'post_user_id' => $user->id,
            'is_publish' => false,
        ]);

        /** @var array $commitData */
        $commitData = [
            'title' => '更新後',
            'subtitle' => '更新後',
            'body' => '更新後',
            'is_publish' => true,
            'updated_at' => $entry->updated_at,
        ];

        /** @var string $url */
        $url = $this->endpoint . '/' . $entry->id;

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->patch($url, $commitData, $this->headers);
        $this->post('logout');

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseHas('entries', $commitData);
    }

    /**
     * 事項更新 異常系テスト
     * ログインしていない場合はエラーが返されること
     * PATCH /entries/{id} -> 401
     */
    public function test_update_401(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint . '/1';

        // Act
        $response = $this->patch($url, [], $this->headers);

        // Assert
        $response->assertStatus(401);
    }

    /**
     * 事項更新 異常系テスト
     * 存在しないIDが指定された場合はエラーが返されること
     * PATCH /entries/{id} -> 404|405
     *
     * @dataProvider notFoundDataProvider
     * @param string $url
     * @param int $errorCode
     */
    public function test_update_404_notFound($url, $errorCode): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var array $commitData */
        $commitData = [
            'title' => 'nop',
            'body' => 'nop',
            'is_publish' => true,
            'updated_at' => '2023-12-23 12:34:56',
        ];

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->patch($url, $commitData, $this->headers);
        $this->post('logout');

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

    /**
     * 事項更新 異常系テスト
     * 更新対象データの更新日時が変更されていたときにエラーが返されること
     * PATCH /entries/{id} -> 409
     */
    public function test_update_409_conflictData(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Entry $entry */
        $entry = Entry::factory()->create([
            'post_user_id' => $user->id,
        ]);

        /** @var array $commitData */
        $commitData = [
            'title' => 'nop',
            'body' => 'nop',
            'is_publish' => true,
            'updated_at' => '2023-12-23 12:34:55',
        ];

        /** @var string $url */
        $url = $this->endpoint . '/' . $entry->id;

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->patch($url, $commitData, $this->headers);
        $this->post('logout');

        // Assert
        $response->assertStatus(409);
    }

    /**
     * 事項更新 異常系テスト
     * 不適切な形式のリクエストを与えたときにエラーが返されること
     * PATCH /entries/{id} -> 422
     *
     * @dataProvider validationErrorDataProvider
     * @param array $commitData
     * @param array $errors
     */
    public function test_update_422_validationError($commitData, $errors): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var string $url */
        $url = $this->endpoint . '/1';

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->patch($url, $commitData, $this->headers);
        $this->post('logout');

        // Assert
        $response->assertStatus(422);
        $response->assertJsonPath('errors', $errors);
    }

    public function validationErrorDataProvider(): array
    {
        return [
            [
                'commitData' => [],
                'errors' => [
                    'title' => ['titleは必ず指定してください。'],
                    'body' => ['bodyは必ず指定してください。'],
                    'is_publish' => ['is publishは必ず指定してください。'],
                    'updated_at' => ['updated atは必ず指定してください。'],
                ],
            ],
            [
                'commitData' => [
                    'title' => 'nop',
                    'body' => 'nop',
                    'is_publish' => 'X',
                    'updated_at' => '2023/12/23 12:34:56',
                ],
                'errors' => [
                    'is_publish' => ['is publishは、trueかfalseを指定してください。'],
                    'updated_at' => ['updated atはY-m-d H:i:s形式で指定してください。'],
                ],
            ],
        ];
    }

    /**
     * 事項更新 異常系テスト
     * 他事項のタイトルと重複していたときにエラーが返されること
     * POST /entries -> 422
     */
    public function test_update_422_duplicatedTitle(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection $entries */
        $entries = Entry::factory(2)->create([
            'post_user_id' => $user->id,
        ]);

        /** @var array $commitData_1 */
        $commitData_1 = [
            'title' => $entries[0]->title,
            'body' => 'nop',
            'is_publish' => true,
            'updated_at' => $entries[0]->updated_at,
        ];

        /** @var array $commitData_2 */
        $commitData_2 = [
            'title' => $entries[1]->title,
            'body' => 'nop',
            'is_publish' => true,
            'updated_at' => $entries[0]->updated_at,
        ];

        /** @var string $url */
        $url = $this->endpoint . '/' . $entries[0]->id;

        // Act
        $this->actingAs(User::find($user->id));
        $response_1 = $this->patch($url, $commitData_1, $this->headers);
        $response_2 = $this->patch($url, $commitData_2, $this->headers);
        $this->post('logout');

        // Assert
        $response_1->assertStatus(204);
        $response_2->assertStatus(422);
        $response_2->assertJsonPath('errors', [
            'title' => ['titleの値は既に存在しています。'],
        ]);
    }
}
