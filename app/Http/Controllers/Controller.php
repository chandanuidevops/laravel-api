<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomJsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    protected function paginate(object $collection, int $length, int $perPage, array $options = [], $is_slick = true)
    {
        $options['messages'] = $options['messages'] ?? null;
        if (!array_key_exists('all', $options)) {
            $options['all'] = false;
        }

        $total = count($collection);
        if ($options['all'] === false || $options['all'] === 'false') {
            $page = Paginator::resolveCurrentPage();
            if ($is_slick == false) {
                $currentPageResults = $collection;
            } else {
                $currentPageResults = $collection->slice(($page - 1) * $perPage, $perPage)->values();
            }

            $collection = new LengthAwarePaginator(
                $currentPageResults,
                $length,
                $perPage,
                $page,
                array_merge($options, ['path' => Paginator::resolveCurrentPath()])
            );
            $total = $collection->total();
        }

        return CustomJsonResponse::response(
            Response::HTTP_OK,
            $collection,
            $options['messages'],
            $total
        );
    }
}
