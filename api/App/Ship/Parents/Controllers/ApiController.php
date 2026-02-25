<?php

declare(strict_types=1);

namespace App\Ship\Parents\Controllers;

use App\Ship\Parents\Transformers\ApiTransformer;
use App\Ship\Responses\ApiResponse;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;

abstract class ApiController extends Controller
{
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

        $data = $query->get()->transform(static fn (Model $model): array => $transformer->runTransformation($model));

        return new ApiResponse($data);
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
    public function getTransformer(string $transformerClass): ApiTransformer
    {
        /** @var \App\Ship\Parents\Transformers\ApiTransformer $transformer */
        return Container::getInstance()->make($transformerClass);
    }



}
