<?php

namespace Tests\Feature\Brief;

use App\Models\Brief;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @var string $endpoint */
    protected $endpoint = '/api/briefs';

    /**
     * テスト前処理
     */
    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2023, 12, 23, 12, 34, 56));
    }

    /**
     * データ一覧取得 正常系テスト
     * レスポンスデータが正しく格納されていること
     * GET /briefs -> 200
     */
    public function test_index_200_fillResponseData()
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint;

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection<Brief> $briefs */
        $briefs = Brief::factory(2)->sequence(
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
                'is_publish' => true,
            ],
        )->create();

        // Act
        $defaultPerPage = config('galzwiki.per_page');
        config(['galzwiki.per_page' => 1]);
        $response = $this->get($url);
        config(['galzwiki.per_page' => $defaultPerPage]);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.0', [
            'id' => $briefs[0]->id,
            'title' => $briefs[0]->title,
            'note' => $briefs[0]->note,
            'entry_at' => $briefs[0]->entry_at,
            'is_publish' => $briefs[0]->is_publish,
            'parents' => [
                [
                    'id' => $briefs[1]->id,
                    'title' => $briefs[1]->title,
                    'depth' => -1,
                ],
            ],
        ]);
    }

    /**
     * データ一覧取得 正常系テスト
     * 投稿日時が過ぎていない記事が取得できないこと
     * GET /briefs -> 200
     */
    public function test_index_200_filterBeforePublishBrief()
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint;

        /** @var User $user */
        $user = User::factory()->create();

        Brief::factory()->create([
            'entry_user_id' => $user->id,
            'entry_at' => '2023-12-23 12:34:57',
            'is_publish' => true,
        ]);

        // Act
        $response = $this->get($url);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data', []);
        $response->assertJsonPath('meta.total', 0);
    }

    /**
     * データ一覧取得 正常系テスト
     * 未公開記事が取得できないこと
     * GET /briefs -> 200
     */
    public function test_index_200_filterUnpublishBrief()
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint;

        /** @var User $user */
        $user = User::factory()->create();

        Brief::factory()->create([
            'entry_user_id' => $user->id,
            'entry_at' => '2023-12-23 12:34:56',
            'is_publish' => false,
        ]);

        // Act
        $response = $this->get($url);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data', []);
        $response->assertJsonPath('meta.total', 0);
    }

    /**
     * データ一覧取得 正常系テスト
     * ログイン時は自分の未公開記事が取得できること
     * GET /briefs -> 200
     */
    public function test_index_200_getMyBrief()
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint;

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Brief $brief */
        $brief = Brief::factory()->create([
            'entry_user_id' => $user->id,
            'entry_at' => '2023-12-23 12:34:57',
            'is_publish' => false,
        ]);

        // Act
        $this->actingAs(User::find($user->id));
        $response = $this->get($url);
        $this->post('logout');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $brief->id);
        $response->assertJsonPath('meta.total', 1);
    }

    /**
     * データ一覧取得 正常系テスト
     * 削除済み記事が取得できないこと
     * GET /briefs -> 200
     */
    public function test_index_200_filterDeletedBrief()
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint;

        /** @var User $user */
        $user = User::factory()->create();

        Brief::factory()->create([
            'entry_user_id' => $user->id,
            'deleted_at' => '2023-12-23 12:34:56',
        ]);

        // Act
        $response = $this->get($url);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data', []);
        $response->assertJsonPath('meta.total', 0);
    }

    /**
     * データ一覧取得 正常系テスト
     * 単数キーワードで絞り込みできること
     * GET /briefs -> 200
     */
    public function test_index_200_filterSingleKeyword()
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint . '?keywords[0]=帝京';

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection<Brief> $briefs */
        $briefs = Brief::factory(5)->sequence(
            [
                'title' => '帝京平成大学',
                'note' => null,
                'abstract' => 'nop',
                'hands_on' => null,
                'entry_user_id' => $user->id,
            ],
            [
                'title' => 'nop2',
                'note' => '帝京平成大学',
                'abstract' => 'nop',
                'hands_on' => null,
                'entry_user_id' => $user->id,
            ],
            [
                'title' => 'nop3',
                'note' => null,
                'abstract' => '帝京平成大学はここがすごい！！',
                'hands_on' => null,
                'entry_user_id' => $user->id,
            ],
            [
                'title' => 'nop4',
                'note' => null,
                'abstract' => 'nop',
                'hands_on' => '帝京平成大学はここがすごい！！',
                'entry_user_id' => $user->id,
            ],
            [
                'title' => 'nop5',
                'note' => null,
                'abstract' => 'nop',
                'hands_on' => null,
                'entry_user_id' => $user->id,
            ]
        )->create();

        // Act
        $response = $this->get($url);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonMissing(['title' => $briefs[4]->title]);
        $response->assertJsonPath('meta.total', 4);
    }

    /**
     * データ一覧取得 正常系テスト
     * キーワードでAND検索できること
     * GET /briefs -> 200
     */
    public function test_index_200_filterKeywordsByAndSearch()
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint . '?keywords[0]=帝京&keywords[1]=大学';

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection<Brief> $briefs */
        $briefs = Brief::factory(2)->sequence(
            [
                'title' => '東京大学',
                'note' => null,
                'abstract' => 'これが帝京魂だ！！',
                'hands_on' => null,
                'entry_user_id' => $user->id,
            ],
            [
                'title' => 'nop2',
                'note' => null,
                'abstract' => 'これが帝京魂だ！！',
                'hands_on' => null,
                'entry_user_id' => $user->id,
            ],
        )->create();

        // Act
        $response = $this->get($url);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonMissing(['title' => $briefs[1]->title]);
        $response->assertJsonPath('meta.total', 1);
    }

    /**
     * データ一覧取得 正常系テスト
     * キーワードでOR検索できること
     * GET /briefs -> 200
     */
    public function test_index_200_filterKeywordsByOrSearch()
    {
        // Arrange
        /** @var string $url */
        $url = $this->endpoint . '?keywords[0]=帝京&keywords[1]=大学&operator=or';

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Collection<Brief> $briefs */
        $briefs = Brief::factory(3)->sequence(
            [
                'title' => '東京大学',
                'note' => null,
                'abstract' => 'これが帝京魂だ！！',
                'hands_on' => null,
                'entry_user_id' => $user->id,
            ],
            [
                'title' => 'nop2',
                'note' => null,
                'abstract' => 'これが帝京魂だ！！',
                'hands_on' => null,
                'entry_user_id' => $user->id,
            ],
            [
                'title' => 'nop3',
                'note' => null,
                'abstract' => 'nop',
                'hands_on' => null,
                'entry_user_id' => $user->id,
            ],
        )->create();

        // Act
        $response = $this->get($url);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonMissing(['title' => $briefs[2]->title]);
        $response->assertJsonPath('meta.total', 2);
    }

    /**
     * データ一覧取得 異常系テスト
     * 不適切な形式のリクエストを与えたときにエラーが返されること
     * GET /briefs -> 422
     *
     * @dataProvider index422DataProvider
     * @param string $url
     * @param array $messages
     */
    public function test_index_422($url, $messages)
    {
        // Act
        $response = $this->get($url);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonPath('errors', $messages);
    }

    public function index422DataProvider()
    {
        return [
            [
                'url' => $this->endpoint . '?keywords=0',
                'messages' => [
                    'keywords' => ['keywordsは配列でなくてはなりません。'],
                ],
            ],
            [
                'url' => $this->endpoint . '?operator=NOT',
                'messages' => [
                    'operator' => ['operatorには「and」か「or」のいずれかを指定してください。'],
                ],
            ],
            [
                'url' => $this->endpoint . '?page=X',
                'messages' => [
                    'page' => ['pageは整数で指定してください。'],
                ],
            ],
        ];
    }
}
