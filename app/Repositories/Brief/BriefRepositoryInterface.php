<?php

namespace App\Repositories\Brief;

use App\Models\Brief;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

interface BriefRepositoryInterface
{
    /**
     * 検索条件でデータ一覧を抽出する
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findByParamsWithPaginator(array $params): LengthAwarePaginator;

    /**
     * IDで個別データを抽出する
     * 
     * @param int $id
     * @return Brief
     * @throws NotFoundHttpException
     */
    public function findById(int $id): Brief;

    /**
     * 親記事IDに紐づくデータを再帰的に抽出する
     *
     * @param int $id
     * @return array<Brief>
     */
    public function findParentsRecursively(int $id): array;

    /**
     * データを登録する
     * 
     * @param array $commitData
     * @return void
     * @throws ConflictHttpException
     */
    public function store(array $commitData): void;

    /**
     * データを更新する
     * 
     * @param array $commitData
     * @return void
     * @throws ConflictHttpException
     * @throws NotFoundHttpException
     */
    public function update(array $commitData): void;

    /**
     * データを削除する
     * 
     * @param int $id
     * @return void
     * @throws NotFoundHttpException
     */
    public function delete(int $id): void;
}
