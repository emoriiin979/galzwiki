<?php

namespace Tests\Feature\Brief;

use App\Models\Brief;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @var string $endpoint */
    protected $endpoint = '/api/briefs';

    /** @var array $headers */
    protected $headers = [
        'Accept' => 'application/json',
    ];

    /**
     * データ登録 正常系テスト
     * コミット用データが正しく登録されること
     * POST /briefs -> 201
     */
    public function test_store_201()
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var array $commitData */
        $commitData = [
            'title' => 'EC2',
            'note' => 'Elastic Compute Cloud',
            'abstract' => 'Amazonが提供する計算資源を用いて...',
            'hands_on' => 'EC2の作成前に、VPCを準備します。VPCコンソールにアクセスし...',
            'parent_brief_id' => 1,
            'entry_user_id' => $user->id,
            'entry_at' => '2023-12-23 12:34:56',
            'is_publish' => true,
        ];

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->post($this->endpoint, $commitData, $this->headers);
        $this->post('logout');

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('briefs', $commitData);
    }

    /**
     * データ登録 異常系テスト
     * ログインしていない場合はエラーが返されること
     * POST /briefs -> 401
     */
    public function test_store_401()
    {
        // Act
        $response = $this->post($this->endpoint, [], $this->headers);

        // Assert
        $response->assertStatus(401);
    }

    /**
     * データ登録 異常系テスト
     * 不適切な形式のリクエストを与えたときにエラーが返されること
     * POST /briefs -> 422
     *
     * @dataProvider validationErrorDataProvider
     * @param array $commitData
     * @param array $errors
     */
    public function test_store_422_validationError($commitData, $errors)
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

    public function validationErrorDataProvider()
    {
        return [
            [
                'commitData' => [],
                'errors' => [
                    'title' => ['titleは必ず指定してください。'],
                    'abstract' => ['abstractは必ず指定してください。'],
                    'parent_brief_id' => ['parent brief idは必ず指定してください。'],
                    'entry_at' => ['entry atは必ず指定してください。'],
                    'entry_user_id' => ['entry user idは必ず指定してください。'],
                    'is_publish' => ['is publishは必ず指定してください。'],
                ],
            ],
            [
                'commitData' => [
                    'title' => 'nop',
                    'abstract' => 'nop',
                    'parent_brief_id' => 'X',
                    'entry_at' => '2023/12/23 12:34:56',
                    'entry_user_id' => 'X',
                    'is_publish' => 'X',
                ],
                'errors' => [
                    'parent_brief_id' => ['parent brief idは整数で指定してください。'],
                    'entry_at' => ['entry atはY-m-d H:i:s形式で指定してください。'],
                    'entry_user_id' => ['entry user idは整数で指定してください。'],
                    'is_publish' => ['is publishは、trueかfalseを指定してください。'],
                ],
            ],
        ];
    }

    /**
     * データ登録 異常系テスト
     * 登録済みのタイトルと重複していたときにエラーが返されること
     * POST /briefs -> 422
     */
    public function test_store_422_duplicatedTitle()
    {
        // Arrange
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Brief $brief */
        $brief = Brief::factory()->create([
            'entry_user_id' => $user->id,
        ]);

        /** @var array $commitData */
        $commitData = [
            'title' => $brief->title,
            'abstract' => 'nop',
            'parent_brief_id' => 1,
            'entry_at' => '2023-12-23 12:34:56',
            'entry_user_id' => $user->id,
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
