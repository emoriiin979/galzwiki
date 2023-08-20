<?php

namespace App\Services;

use App\Models\Brief;
use App\Repositories\Brief\BriefRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BriefService
{
    protected $briefRepos;

    public function __construct(
        BriefRepositoryInterface $briefRepos
    ) {
        $this->briefRepos = $briefRepos;
    }

    /**
     * データ一覧取得
     *
     * @param array $params
     * @return LengthAwarePaginator<Brief>
     */
    public function index(array $params): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<Brief> $briefs */
        $briefs = $this->briefRepos->findByParamsWithPaginator($params);

        // 親記事情報を抽出データにセット
        $briefs->through(function ($brief) {
            /** @var Collection<Brief> $parents */
            $parents = $this->briefRepos->findParentsRecursively($brief['id']);
            
            $brief->parents = $parents;

            return $brief;
        });

        return $briefs;
    }

    /**
     * データ登録
     *
     * @param array $commitData
     * @return void
     */
    public function store(array $commitData): void
    {
        $this->briefRepos->store($commitData);
    }

    /**
     * 個別データ取得
     *
     * @param integer $id
     * @return Brief
     */
    public function show(int $id): Brief
    {
        /** @var Brief $brief */
        $brief = $this->briefRepos->findById($id);

        /** @var Collection<Brief> $parents */
        $parents = $this->briefRepos->findParentsRecursively($id);

        $brief->parents = $parents;

        return $brief;
    }

    /**
     * データ更新
     *
     * @param array $commitData
     * @return void
     */
    public function update(array $commitData): void
    {
        $this->briefRepos->update($commitData);
    }

    /**
     * データ削除
     *
     * @param integer $id
     * @return void
     */
    public function delete(int $id): void
    {
        $this->briefRepos->delete($id);
    }
}
