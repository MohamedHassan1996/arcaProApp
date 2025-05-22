<?php

namespace App\Filters\Maintenance;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterMaintenance implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where('codice', 'like', '%' . $value . '%');
    }
}
