<?php

namespace App\Filters\Maintenance;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterMaintenanceDate implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where('start_date', '<=', $value)
        ->where('end_date', '>=', $value);
    }
}
