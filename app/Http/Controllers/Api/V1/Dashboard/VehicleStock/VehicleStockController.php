<?php

namespace App\Http\Controllers\Api\V1\Dashboard\VehicleStock;

use App\Enums\Maintenance\MaintenanceStatus;
use App\Filters\Maintenance\FilterMaintenanceDate;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Maintenance\AllOperatorMaintenanceCollection;
use App\Http\Resources\VehicleStock\AllVehicleStockCollection;
use App\Models\Maintenance;
use App\Models\Stock;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
class VehicleStockController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    public function index(Request $request)
    {

        // $stock = QueryBuilder::for(Maintenance::class)
        //     ->allowedFilters([
        //         AllowedFilter::custom('date', new FilterMaintenanceDate()),
        //     ])
        //     ->select([
        //         'guid',
        //         'operatori_guids',
        //         'codice',
        //         'status_guid',
        //         'start_date',
        //         'arrive_hour',
        //         'anagraphic_guid',
        //         'status_guid',
        //         'importance_guid'
        //     ])
        //     ->where('operatori_guids', 'like', '%' . $authUser->operator_guid . '%')
        //     ->where('status_guid', MaintenanceStatus::PROGRAMMATO)
        //     ->whereNull('deleted_at')
        //     ->with(['anagraphic' => function ($query) {
        //         $query->select('guid', 'regione_sociale');
        //     }])
        //     ->orderByRaw('start_date IS NULL, start_date DESC')
        //     ->paginate($request->pageSize ?? 10);

        $stocks = Stock::select('guid', 'codice', 'descrizione', 'quantita')->where('codice_interno', 'componenti')->where('nome', 'FURGONE')->paginate(10);


        return ApiResponse::success(new AllVehicleStockCollection($stocks));
    }


}
