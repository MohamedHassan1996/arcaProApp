<?php

namespace App\Filters\Invoice;

use App\Enums\Stock\OutStockType;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterSaleInvoice implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where(function ($query) use ($value) {
            $query->where('out_stock_number', 'like', '%' . $value . '%')->where('type', OutStockType::SALE_INVOICE->value);
        });
    }
}
