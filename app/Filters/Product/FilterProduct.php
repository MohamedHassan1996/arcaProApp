<?php

namespace App\Filters\Product;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterProduct implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where(function ($query) use ($value) {
            $query->where('name', 'like', '%' . $value . '%')
                ->orWhere('serial_number', 'like', '%' . $value . '%')
                ->orWhere('bar_code', 'like', '%' . $value . '%');
        });
    }
}
