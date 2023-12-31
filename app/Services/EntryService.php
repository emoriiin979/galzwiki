<?php

namespace App\Services;

use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class EntryService
{
    /** @var array $SEARCH_TARGETS */
    private static $SEARCH_TARGETS = [
        'title',
        'subtitle',
        'body',
    ];
    
    /** @var Entry $entry */
    protected $model;

    public function __construct(Entry $model) {
        $this->model = $model;
    }

    /**
     * 記事一覧取得(Paginator)
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function fetchByParamsWithPaginator(array $params): LengthAwarePaginator
    {
        /** @var Builder $query */
        $query = $this->buildQueryForFetchByParams($params);

        /** @var LengthAwarePaginator $entries */
        $entries = $query->paginate(config('galzwiki.per_page'))->appends($params);

        return $entries;
    }

    /**
     * 記事一覧取得用クエリ作成
     *
     * @param array $params
     * @return Builder
     */
    private function buildQueryForFetchByParams(array $params): Builder
    {
        /** @var Builder $query */
        $query = $this->model->query();

        $query->where(function ($query) {
            // 公開中の事項のみ取得
            $query->where('entries.is_publish', 1);

            // ログイン中は自分が投稿した全記事を取得
            if (auth()->user()) {
                $query->orWhere('entries.post_user_id', auth()->user()->id);
            }
        });

        // キーワード検索
        if ($keywords = Arr::get($params, 'keywords')) {
            $method = $params['operator'] === 'and' ? 'where' : 'orWhere';
            $query->where(function ($query) use ($keywords, $method) {
                foreach ($keywords as $keyword) {
                    $query->$method(function ($query) use ($keyword) {
                        foreach (self::$SEARCH_TARGETS as $target) {
                            $query->orWhere($target, 'like', '%' . $keyword . '%');
                        }
                    });
                }
            });
        }

        $query->orderBy('entries.updated_at', 'desc');
        
        return $query;
    }

    /**
     * 記事詳細取得
     *
     * @param int $id
     * @return Entry
     */
    public function fetchById($id): Entry
    {
        /** @var Entry $entry */
        $entry = $this->model->findOrFail($id);

        if (
            ! $entry->is_publish
            && (
                ! auth()->user()
                || $entry->post_user_id !== auth()->user()->id
            )
        ) {
            abort(404);
        }

        return $entry;
    }

    /**
     * 記事登録
     *
     * @param array $commitData
     * @return void
     */
    public function store(array $commitData): void
    {
        $this->model->create($commitData);
    }

    /**
     * 記事更新
     *
     * @param array $commitData
     * @return void
     */
    public function update(array $commitData): void
    {
        /** @var Entry $existData */
        $existData = $this->model->findOrFail($commitData['id']);

        // 更新日時が一致しない場合はエラー
        if ($existData->updated_at !== $commitData['updated_at']) {
            $message = <<<EOT
                別ユーザーによってデータが更新されています。
                ページを読み込み直して再度更新処理を実行してください。
            EOT;
            throw new ConflictHttpException($message);
        }

        $existData->fill($commitData)->save();
    }

    /**
     * 記事削除
     *
     * @param integer $id
     * @return void
     */
    public function delete(int $id): void
    {
        /** @var Entry $existData */
        $existData = $this->model->findOrFail($id);

        $existData->delete();
    }
}
