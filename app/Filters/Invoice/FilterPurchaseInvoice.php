<?php

namespace App\Filters\Invoice;

use App\Enums\Stock\InStockType;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterPurchaseInvoice implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where(function ($query) use ($value) {
            $query->where('in_stock_number', 'like', '%' . $value . '%')
                ->orWhere('supplier_in_stock_number', 'like', '%' . $value . '%')
                ->where('type', InStockType::PURCHASE_INVOICE->value);
        });
    }
}
