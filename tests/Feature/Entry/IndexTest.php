<?php

namespace Tests\Feature\Entry;

use App\Models\Entry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @var string $endpoint */
    protected $endpoint = '/api/entries';

    /** @var array $headers */
    protected $headers = [
        'Accept' => 'application/json',
    ];

    /**
     * テスト前処理
     */
    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2023, 12, 23, 12, 34, 56));
    }

    /**
     * 事項一覧取得 正常系テスト
     * レスポンスデータが正しく格納されていること
     * GET /entries -> 200
     */
    public function test_index_200_fillResponseData(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint;

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection $entries */
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

        // Act
        $defaultPerPage = config('galzwiki.per_page');
        config(['galzwiki.per_page' => 1]);
        $response = $this->get($url, $this->headers);
        config(['galzwiki.per_page' => $defaultPerPage]);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.0', [
            'id' => $entries[0]->id,
            'title' => $entries[0]->title,
            'subtitle' => $entries[0]->subtitle,
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
     * 事項一覧取得 正常系テスト
     * 未公開事項が取得できないこと
     * GET /entries -> 200
     */
    public function test_index_200_filterUnpublishEntry(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint;

        /** @var User $user */
        $user = User::factory()->create();

        Entry::factory()->create([
            'post_user_id' => $user->id,
            'is_publish' => false,
        ]);

        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data', []);
        $response->assertJsonPath('meta.total', 0);
    }

    /**
     * 事項一覧取得 正常系テスト
     * ログイン時は自分の未公開事項が取得できること
     * GET /entries -> 200
     */
    public function test_index_200_getMyEntry(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint;

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Entry $entry */
        $entry = Entry::factory()->create([
            'post_user_id' => $user->id,
            'is_publish' => false,
        ]);

        // Act
        $this->actingAs($user);
        $response = $this->get($url, $this->headers);
        $this->post('logout');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $entry->id);
        $response->assertJsonPath('meta.total', 1);
    }

    /**
     * 事項一覧取得 正常系テスト
     * 削除済み事項が取得できないこと
     * GET /entries -> 200
     */
    public function test_index_200_filterDeletedEntry(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint;

        /** @var User $user */
        $user = User::factory()->create();

        Entry::factory()->create([
            'post_user_id' => $user->id,
            'deleted_at' => '2023-12-23 12:34:56',
        ]);

        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data', []);
        $response->assertJsonPath('meta.total', 0);
    }

    /**
     * 事項一覧取得 正常系テスト
     * 単数キーワードで絞り込みできること
     * GET /entries -> 200
     */
    public function test_index_200_filterSingleKeyword(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint . '?keywords[0]=帝京';

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection $entries */
        $entries = Entry::factory(4)->sequence(
            [
                'title' => '帝京平成大学',
                'subtitle' => null,
                'body' => 'nop',
                'post_user_id' => $user->id,
            ],
            [
                'title' => 'nop2',
                'subtitle' => '帝京平成大学',
                'body' => 'nop',
                'post_user_id' => $user->id,
            ],
            [
                'title' => 'nop3',
                'subtitle' => null,
                'body' => '帝京平成大学はここがすごい！！',
                'post_user_id' => $user->id,
            ],
            [
                'title' => 'nop4',
                'subtitle' => null,
                'body' => 'nop',
                'post_user_id' => $user->id,
            ]
        )->create();

        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonMissing(['title' => $entries[3]->title]);
        $response->assertJsonPath('meta.total', 3);
    }

    /**
     * 事項一覧取得 正常系テスト
     * キーワードでAND検索できること
     * GET /entries -> 200
     */
    public function test_index_200_filterKeywordsByAndSearch(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint . '?keywords[0]=帝京&keywords[1]=大学';

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection $entries */
        $entries = Entry::factory(2)->sequence(
            [
                'title' => '東京大学',
                'subtitle' => null,
                'body' => 'これが帝京魂だ！！',
                'post_user_id' => $user->id,
            ],
            [
                'title' => 'nop2',
                'subtitle' => null,
                'body' => 'これが帝京魂だ！！',
                'post_user_id' => $user->id,
            ],
        )->create();

        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonMissing(['title' => $entries[1]->title]);
        $response->assertJsonPath('meta.total', 1);
    }

    /**
     * 事項一覧取得 正常系テスト
     * キーワードでOR検索できること
     * GET /entries -> 200
     */
    public function test_index_200_filterKeywordsByOrSearch(): void
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint . '?keywords[0]=帝京&keywords[1]=大学&operator=or';

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection $entries */
        $entries = Entry::factory(3)->sequence(
            [
                'title' => '東京大学',
                'subtitle' => null,
                'body' => 'これが帝京魂だ！！',
                'post_user_id' => $user->id,
            ],
            [
                'title' => 'nop2',
                'subtitle' => null,
                'body' => 'これが帝京魂だ！！',
                'post_user_id' => $user->id,
            ],
            [
                'title' => 'nop3',
                'subtitle' => null,
                'body' => 'nop',
                'post_user_id' => $user->id,
            ],
        )->create();

        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonMissing(['title' => $entries[2]->title]);
        $response->assertJsonPath('meta.total', 2);
    }

    /**
     * 事項一覧取得 異常系テスト
     * 不適切な形式のリクエストを与えたときにエラーが返されること
     * GET /entries -> 422
     *
     * @dataProvider validationErrorDataProvider
     * @param string $url
     * @param array $errors
     */
    public function test_index_422_validationError($url, $errors): void
    {
        // Act
        $response = $this->get($url, $this->headers);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonPath('errors', $errors);
    }

    public function validationErrorDataProvider(): array
    {
        return [
            [
                'url' => $this->endpoint . '?keywords=0',
                'errors' => [
                    'keywords' => ['keywordsは配列でなくてはなりません。'],
                ],
            ],
            [
                'url' => $this->endpoint . '?operator=NOT',
                'errors' => [
                    'operator' => ['operatorには「and」か「or」のいずれかを指定してください。'],
                ],
            ],
            [
                'url' => $this->endpoint . '?page=X',
                'errors' => [
                    'page' => ['pageは整数で指定してください。'],
                ],
            ],
        ];
    }
}
