<?php

namespace App\Http\Controllers;

use App\Http\Resources\EntryCollection;
use App\Http\Resources\EntryResource;
use App\Http\Requests\EntryIndexRequest;
use App\Http\Requests\EntryStoreRequest;
use App\Http\Requests\EntryUpdateRequest;
use App\Services\EntryService;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class EntryController extends Controller
{
    /** @var EntryService $service */
    protected $service;

    public function __construct(EntryService $service)
    {
        $this->service = $service;
    }

    /**
     * 記事一覧の取得
     * 
     * @param EntryIndexRequest $request
     * @return EntryCollection
     */
    public function index(EntryIndexRequest $request): EntryCollection
    {
        /** @var array $params */
        $params = $request->only([
            'keywords',
            'operator',
        ]);

        if (auth()->user()) {
            $params['auth_user_id'] = auth()->user()->id;
        }

        /** @var LengthAwarePaginator $entries */
        $entries = $this->service->fetchByParamsWithPaginator($params);

        /** @var EntryCollection $resource */
        $resource = app()->make(EntryCollection::class, ['resource' => $entries]);

        return $resource;
    }

    /**
     * 記事の登録
     * 
     * @param EntryStoreRequest $request
     * @return Response
     */
    public function store(EntryStoreRequest $request): Response
    {
        $this->service->store($request->only([
            'title',
            'subtitle',
            'body',
            'parent_entry_id',
            'post_user_id',
            'post_at',
            'is_publish',
        ]));

        /** @var Response $response */
        $response = response()->noContent(Response::HTTP_CREATED);

        return $response;
    }

    /**
     * 記事詳細の取得
     * 
     * @param int $id
     * @return EntryResource
     */
    public function show(int $id): EntryResource
    {
        /** @var Entry $entry */
        $entry = $this->service->fetchById($id);

        /** @var EntryResource $resource */
        $resource = app()->make(EntryResource::class, ['resource' => $entry]);

        return $resource;
    }

    /**
     * 記事の更新
     * 
     * @param EntryUpdateRequest $request
     * @param int $id
     * @return Response
     */
    public function update(EntryUpdateRequest $request, int $id): Response
    {
        /** @var array $commitData */
        $commitData = $request->only([
            'title',
            'subtitle',
            'body',
            'parent_entry_id',
            'post_user_id',
            'post_at',
            'is_publish',
            'updated_at',
        ]) + ['id' => $id];

        $this->service->update($commitData);

        /** @var Response $response */
        $response = response()->noContent();

        return $response;
    }

    /**
     * 記事の削除
     * 
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response
    {
        $this->service->delete($id);

        /** @var Response $response */
        $response = response()->noContent();

        return $response;
    }
}
