<?php

namespace Tests\Feature\Entry;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @var string $endpoint */
    protected $endpoint = '/api/entries';

    /** @var array $headers */
    protected $headers = [
        'Accept' => 'application/json',
    ];

    /**
     * 事項登録 正常系テスト
     * コミット用データが正しく登録されること
     * POST /entries -> 201
     */
    public function test_store_201(): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var array $commitData */
        $commitData = [
            'title' => 'EC2',
            'subtitle' => 'Elastic Compute Cloud',
            'body' => 'Amazonが提供する計算資源を用いて...',
            'parent_entry_id' => 1,
            'post_user_id' => $user->id,
            'is_publish' => true,
        ];

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->post($this->endpoint, $commitData, $this->headers);
        $this->post('logout');

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('entries', $commitData);
    }

    /**
     * 事項登録 異常系テスト
     * ログインしていない場合はエラーが返されること
     * POST /entries -> 401
     */
    public function test_store_401(): void
    {
        // Act
        $response = $this->post($this->endpoint, [], $this->headers);

        // Assert
        $response->assertStatus(401);
    }

    /**
     * 事項登録 異常系テスト
     * 不適切な形式のリクエストを与えたときにエラーが返されること
     * POST /entries -> 422
     *
     * @dataProvider validationErrorDataProvider
     * @param array $commitData
     * @param array $errors
     */
    public function test_store_422_validationError($commitData, $errors): void
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->post($this->endpoint, $commitData, $this->headers);
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
                    'parent_entry_id' => ['parent entry idは必ず指定してください。'],
                    'post_user_id' => ['post user idは必ず指定してください。'],
                    'is_publish' => ['is publishは必ず指定してください。'],
                ],
            ],
            [
                'commitData' => [
                    'title' => 'nop',
                    'body' => 'nop',
                    'parent_entry_id' => 'X',
                    'post_user_id' => 'X',
                    'is_publish' => 'X',
                ],
                'errors' => [
                    'parent_entry_id' => ['parent entry idは整数で指定してください。'],
                    'post_user_id' => ['post user idは整数で指定してください。'],
                    'is_publish' => ['is publishは、trueかfalseを指定してください。'],
                ],
            ],
        ];
    }

    /**
     * 事項登録 異常系テスト
     * 登録済みのタイトルと重複していたときにエラーが返されること
     * POST /entries -> 422
     */
    public function test_store_422_duplicatedTitle(): void
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
            'title' => $entry->title,
            'body' => 'nop',
            'parent_entry_id' => 1,
            'post_user_id' => $user->id,
            'is_publish' => true,
        ];

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->post($this->endpoint, $commitData, $this->headers);
        $this->post('logout');

        // Assert
        $response->assertStatus(422);
        $response->assertJsonPath('errors', [
            'title' => ['titleの値は既に存在しています。'],
        ]);
    }
}
