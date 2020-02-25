<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Pagination
{
    public function addPagination(Builder $query, array $params, int $per_page = null)
    {
        return $query
            ->paginate($per_page)
            ->appends($params);
    }
}
