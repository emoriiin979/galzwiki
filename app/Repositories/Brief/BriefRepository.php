<?php

namespace App\Repositories\Brief;

use App\Models\Brief;
use App\Repositories\Brief\BriefRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BriefRepository implements BriefRepositoryInterface
{
    /** @var array $SEARCH_TARGETS */
    private static $SEARCH_TARGETS = [
        'title',
        'note',
        'abstract',
        'hands_on',
    ];

    /** @var Brief $model */
    private $model;

    public function __construct(Brief $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritdoc
     */
    public function findByParamsWithPaginator(array $params): LengthAwarePaginator
    {
        /** @var string $now */
        $now = Carbon::now()->format('Y-m-d H:i:s');

        /** @var Builder $query */
        $query = $this->model->query();

        $query->where(function ($query) use ($params, $now) {
            // 公開中かつ投稿日が過ぎている記事のみ取得
            $query->where(function ($query) use ($now) {
                $query
                    ->where('briefs.entry_at', '<=', $now)
                    ->where('briefs.is_publish', 1);
            });

            // ログイン中は自分が投稿した全記事を取得
            if (isset($params['auth_user_id'])) {
                $query->orWhere('briefs.entry_user_id', $params['auth_user_id']);
            }
        });

        // キーワード検索
        if (isset($params['keywords'])) {
            $query->where(function ($query) use ($params) {
                foreach ($params['keywords'] as $keyword) {
                    $method = $params['operator'] === 'and' ? 'where' : 'orWhere';
                    $query->$method(function ($query) use ($keyword) {
                        foreach (self::$SEARCH_TARGETS as $target) {
                            $query->orWhere($target, 'like', '%' . $keyword . '%');
                        }
                    });
                }
            });
        }

        $query
            ->orderBy('briefs.entry_at', 'desc')
            ->orderBy('briefs.updated_at', 'desc');

        /** @var LengthAwarePaginator $briefs */
        $briefs = $query->paginate(config('galzwiki.per_page'))->appends($params);

        return $briefs;
    }

    /**
     * @inheritdoc
     */
    public function findById(int $id): Brief
    {
        /** @var Brief $brief */
        $brief = $this->model->find($id);

        // データが存在しない場合はエラー
        if (!$brief) {
            throw new NotFoundHttpException('データが存在しませんでした...');
        }

        return $brief;
    }

    /**
     * @inheritdoc
     */
    public function findParentsRecursively(int $id): array
    {
        /** @var string $sql */
        $sql = <<<EOT
            WITH RECURSIVE r AS (
                SELECT
                  briefs.id
                  , briefs.title
                  , briefs.parent_brief_id
                FROM briefs
                WHERE
                  briefs.id = ?
              UNION ALL
                SELECT
                  briefs.id
                  , briefs.title
                  , briefs.parent_brief_id
                FROM briefs, r
                WHERE
                  briefs.id = r.parent_brief_id
            )
            SELECT * FROM r WHERE r.id <> ?
        EOT;

        /** @var array<Brief> $parents */
        $parents = DB::select($sql, [$id, $id]);

        return $parents;
    }

    /**
     * @inheritdoc
     */
    public function store(array $commitData): void
    {
        // タイトルが重複している場合はエラー
        if (!!$this->model->where('title', $commitData['title'])->get()) {
            throw new ConflictHttpException('タイトルが重複しています...');
        }

        $this->model->create($commitData);
    }

    /**
     * @inheritdoc
     */
    public function update(array $commitData): void
    {
        /** @var Brief $existData */
        $existData = $this->model->find($commitData['id']);

        // データが存在しない場合はエラー
        if (!$existData) {
            throw new NotFoundHttpException('データが存在しませんでした...');
        }

        // 更新日時が一致しない場合はエラー
        if ($existData->updated_at !== $commitData['updated_at']) {
            $message = <<<EOT
                別ユーザーによってデータが更新されています...
                ページを読み込み直して再度更新処理を実行してください...
            EOT;
            throw new ConflictHttpException($message);
        }
        unset($commitData['updated_at']);

        $existData->fill($commitData)->save();
    }

    /**
     * @inheritdoc
     */
    public function delete($id): void
    {
        /** @var Brief $existData */
        $existData = $this->model->find($id);

        // データが存在しない場合はエラー
        if (!$existData) {
            throw new NotFoundHttpException('データが存在しませんでした...');
        }

        $existData->delete();
    }
}