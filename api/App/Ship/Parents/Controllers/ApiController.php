<?php

declare(strict_types=1);

namespace App\Ship\Parents\Controllers;

use App\Ship\Parents\Transformers\ApiTransformer;
use App\Ship\Responses\ApiResponse;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

abstract class ApiController extends Controller
{
    private const string OPT_PAGINATION = 'paginate';

    private const string OPT_PAGINATION_PAGE = 'page';

    private const string OPT_PAGINATION_PER_PAGE = 'per_page';

    private const int DEFAULT_ITEMS_PER_PAGE = 5;

    private const int DEFAULT_PAGINATION_PAGE = 1;

    /**
     * @var \Illuminate\Http\Request|\Illuminate\Foundation\Application|mixed|mixed[]|object
     */
    protected Request $request;

    public function __construct()
    {
        $this->request = request();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $transformerClass
     *
     * @return \App\Ship\Responses\ApiResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function queryResponse(Builder $query, string $transformerClass): ApiResponse
    {
        $transformer = $this->getTransformer($transformerClass);
        $shouldPaginate = \filter_var($this->request->query(self::OPT_PAGINATION, false), FILTER_VALIDATE_BOOLEAN);

        if ($shouldPaginate) {
            $perPage = (int) $this->request->query(self::OPT_PAGINATION_PER_PAGE, self::DEFAULT_ITEMS_PER_PAGE);
            $page = (int) $this->request->query(self::OPT_PAGINATION_PAGE, self::DEFAULT_PAGINATION_PAGE);

            $paginator = $query->paginate(perPage: $perPage, page: $page);

            $items = Collection::make($paginator->items())
                ->transform(static fn (Model $model): array => $transformer->runTransformation($model));

            return new ApiResponse([
                ApiResponse::COLLECTION_DATA_KEY => $items,
                ApiResponse::PAGINATION_META_KEY => [
                    ApiResponse::PAGINATION_PAGE_KEY => $paginator->currentPage(),
                    ApiResponse::PAGINATION_TOTAL_PAGES_KEY => $paginator->lastPage(),
                    ApiResponse::PAGINATION_RECORDS_KEY => $paginator->total(),
                    ApiResponse::PAGINATION_PER_PAGE_KEY => $paginator->perPage(),
                ],
            ]);
        }

        $data = $query->get()
            ->transform(static fn (Model $model): array => $transformer->runTransformation($model));

        return new ApiResponse([
            ApiResponse::COLLECTION_DATA_KEY => $data,
        ]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $transformerClass
     *
     * @return \App\Ship\Responses\ApiResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function modelResponse(Model $model, string $transformerClass): ApiResponse
    {
        $transformer = $this->getTransformer($transformerClass);

        return new ApiResponse($transformer->runTransformation($model));
    }

    /**
     * @param int $status
     *
     * @return \App\Ship\Responses\ApiResponse
     */
    protected function emptyResponse(int $status = 204): ApiResponse
    {
        return new ApiResponse(null, $status);
    }

    /**
     * @param string $transformerClass
     *
     * @return \App\Ship\Parents\Transformers\ApiTransformer
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getTransformer(string $transformerClass): ApiTransformer
    {
        /** @var \App\Ship\Parents\Transformers\ApiTransformer $transformer */
        return Container::getInstance()->make($transformerClass);
    }
}
