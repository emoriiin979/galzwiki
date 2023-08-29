<?php

namespace App\Http\Controllers;

use App\Http\Resources\BriefCollection;
use App\Http\Resources\BriefResource;
use App\Http\Requests\BriefIndexRequest;
use App\Http\Requests\BriefStoreRequest;
use App\Http\Requests\BriefUpdateRequest;
use App\Services\BriefService;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class BriefController extends Controller
{
    /** @var BriefService $service */
    protected $service;

    public function __construct(BriefService $service)
    {
        $this->service = $service;
    }

    /**
     * 記事一覧の取得
     * 
     * @param BriefIndexRequest $request
     * @return BriefCollection
     */
    public function index(BriefIndexRequest $request): BriefCollection
    {
        /** @var array $params */
        $params = $request->only([
            'keywords',
            'operator',
        ]);

        if (auth()->user()) {
            $params['auth_user_id'] = auth()->user()->id;
        }

        /** @var LengthAwarePaginator<Brief> $briefs */
        $briefs = $this->service->fetchByParamsWithPaginator($params);

        /** @var BriefCollection $resource */
        $resource = app()->make(BriefCollection::class, ['resource' => $briefs]);

        return $resource;
    }

    /**
     * 記事の登録
     * 
     * @param BriefStoreRequest $request
     * @return Response
     */
    public function store(BriefStoreRequest $request): Response
    {
        $this->service->store($request->only([
            'title',
            'note',
            'abstract',
            'hands_on',
            'parent_brief_id',
            'entry_user_id',
            'entry_at',
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
     * @return BriefResource
     */
    public function show(int $id): BriefResource
    {
        /** @var Brief $brief */
        $brief = $this->service->fetchById($id);

        /** @var BriefResource $resource */
        $resource = app()->make(BriefResource::class, ['resource' => $brief]);

        return $resource;
    }

    /**
     * 記事の更新
     * 
     * @param BriefUpdateRequest $request
     * @param int $id
     * @return Response
     */
    public function update(BriefUpdateRequest $request, int $id): Response
    {
        /** @var array $commitData */
        $commitData = $request->only([
            'title',
            'note',
            'abstract',
            'hands_on',
            'parent_page_id',
            'entry_user_id',
            'entry_at',
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
