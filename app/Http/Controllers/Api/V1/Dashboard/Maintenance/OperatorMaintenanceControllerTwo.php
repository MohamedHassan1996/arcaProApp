<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Enums\Maintenance\MaintenanceStatus;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Filters\Maintenance\FilterMaintenance;
use App\Filters\Maintenance\FilterMaintenanceDate;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Maintenance\AllOperatorMaintenanceCollection;
use App\Http\Resources\Maintenance\OperatorMaintenanceResource;
use App\Models\Employee;
use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\MaintenanceReport;
use App\Models\ProParameterValue;
use App\Models\Vehicle;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function PHPSTORM_META\map;

class OperatorMaintenanceControllerTwo extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    public function index(Request $request)
    {
        $authUser = Auth::user();
        $userRole = auth()->user()->roles()->first()->name;

       // Get all reported maintenance GUIDs
        $maintenanceReportsIds = MaintenanceReport::pluck('maintenance_guid')->toArray();

        // Get maintenance GUIDs for specific intervention types that have product_guids
        $maintenanceDetails = MaintenanceDetail::whereIn('tipo_intervento_guid', [
                '28e1c7d1-3a11-4660-8e6c-66dab6e17ec5',
                'fa7202e8-65a4-49b4-83f5-39784ca1f22f',
                'e7740d9b-551f-416f-954c-a648c281d436'
            ])
            ->where('product_guids', '!=', '')
            ->whereNotNull('product_guids')
            ->get();

        $maintenanceDetailsIds = [];

        foreach ($maintenanceDetails as $key => $value) {
            $exploded = explode(',', $value->product_guids);

            $productGuids = DB::connection('proMaintenances')->table('anagraphic_product_codes')->whereIn('guid', $exploded)->pluck('guid')->toArray();

            if (count($productGuids) > 0) {
                $maintenanceDetailsIds[] = $value->maintenance_guid;
            }

        }

        // Exclude those that already have reports
        $allNotMaintenanceReportsIds = array_values(array_diff($maintenanceDetailsIds, $maintenanceReportsIds));


        $maintenances = QueryBuilder::for(Maintenance::query()->from('maintenances'))
            ->allowedFilters([
                AllowedFilter::custom('search', new FilterMaintenance()),
                AllowedFilter::custom('date', new FilterMaintenanceDate()),
                AllowedFilter::exact('client', 'anagraphic_guid'),
                AllowedFilter::exact('status', 'status_guid'),
                AllowedFilter::exact('importance', 'importance_guid')
            ])
            ->select([
                'maintenances.guid',
                'maintenances.operatori_guids',
                'maintenances.capo_guids',
                'maintenances.mezzo_guids',
                'maintenances.codice',
                'maintenances.start_date',
                'maintenances.leave_hour',
                'maintenances.distance',
                'maintenances.arrive_hour',
                'maintenances.person_numbers',
                'maintenances.assistenza_client',
                'maintenances.anagraphic_guid',
                'maintenances.status_guid',
                'maintenances.importance_guid',
                'maintenances.anagraphic_address_guid',
                'maintenances.contract_guid',
                'maintenances.dependant_guid',
                'maintenances.dependant_phone_guid',
            ])
            ->when($userRole == 'operator', function ($query) use ($authUser) {
                return $query->where('maintenances.operatori_guids', 'like', '%' . $authUser->operator_guid . '%');
            })
            ->when($userRole == 'operator', function ($query) {
                return $query->where('maintenances.status_guid', MaintenanceStatus::PROGRAMMATO);
            })
            ->when($userRole == 'admin', function ($query) use ($authUser) {
                return $query->where('maintenances.status_guid', request('filter[status_guid]'));
            })
            ->leftJoin('anagraphics', 'maintenances.anagraphic_guid', '=', 'anagraphics.guid')
            ->leftJoin('parameter_values as importances', 'maintenances.importance_guid', '=', 'importances.guid')
            ->leftJoin('parameter_values as status', 'maintenances.status_guid', '=', 'status.guid')
            ->leftJoin('anagraphic_addresses', 'maintenances.anagraphic_address_guid', '=', 'anagraphic_addresses.guid')
            ->leftJoin('dependants', 'maintenances.dependant_guid', '=', 'dependants.guid')
            ->leftJoin('anagraphic_phones', 'maintenances.dependant_phone_guid', '=', 'anagraphic_phones.guid')
            ->leftJoin('parameter_values as contracts', 'maintenances.contract_guid', '=', 'contracts.guid')
            ->addSelect([
                'anagraphics.regione_sociale',
                'importances.parameter_value as importance',
                'status.parameter_value as status',
                'anagraphic_addresses.address',
                'anagraphic_addresses.cap',
                'anagraphic_addresses.city',
                'anagraphic_addresses.province',
                'dependants.nome',
                'dependants.cognome',
                'anagraphic_phones.phone as referencePhone',
                'contracts.parameter_value as contractName'
            ])
            ->whereNull('maintenances.deleted_at')
            ->whereIn('maintenances.guid', $allNotMaintenanceReportsIds)
            ->orderByRaw('start_date IS NULL, start_date DESC')
            ->paginate($request->pageSize ?? 100000);

            $maintenances->getCollection()->transform(function ($maintenance) {
                $getParameterValues = function ($guids) {
                    return $guids
                        ? ProParameterValue::whereIn('guid', explode('##', $guids))->pluck('parameter_value')->toArray()
                        : null;
                };

                $capoGuids = !empty($maintenance->capo_guids)
                    ? explode('##', $maintenance->capo_guids)
                    : [];

                $operatorGuids = !empty($maintenance->operatori_guids)
                    ? explode('##', $maintenance->operatori_guids)
                    : [];

                $mezzoGuids = !empty($maintenance->mezzo_guids)
                    ? explode('##', $maintenance->mezzo_guids)
                    : [];

                $maintenance->capos = !empty($capoGuids)
                    ? Employee::select('firstname', 'lastname')
                        ->whereIn('guid', $capoGuids)
                        ->get()
                        ->map(fn($e) => trim("{$e->firstname} {$e->lastname}"))
                        ->toArray()
                    : [];

                $maintenance->operators = !empty($operatorGuids)
                    ? Employee::select('firstname', 'lastname')
                        ->whereIn('guid', $operatorGuids)
                        ->get()
                        ->map(fn($e) => trim("{$e->firstname} {$e->lastname}"))
                        ->toArray()
                    : [];
            $maintenance->vehicles = !empty($mezzoGuids)
                ? Vehicle::selectRaw('GROUP_CONCAT(description SEPARATOR ", ") as descriptions')
                    ->whereIn('guid', $mezzoGuids)
                    ->value('descriptions')
                : "";
        $maintenanceDetails = DB::connection('proMaintenances')->table('maintenance_details')
            ->leftJoin('parameter_values as intervento', 'maintenance_details.tipo_intervento_guid', '=', 'intervento.guid')
            ->select(
                'maintenance_details.*',
                'intervento.parameter_value as intervento'
            )
            ->where('maintenance_guid', $maintenance->guid)
            ->whereNull('maintenance_details.deleted_at')
            ->get();

        $detailsData = [];
    $productBarCodes = [];

foreach ($maintenanceDetails as $detail) {
    // Step 1: Get product GUIDs (assuming '##' separated)
    $productGuids = explode('##', $detail->product_guids ?? '');

    // Step 2: Get product codes from DB
    $productCodesData = DB::connection('proMaintenances')->table('anagraphic_product_codes')
        ->whereIn('guid', $productGuids)
        ->get();

    $detailProductBarCodes = [];

    $productCodes = [];

    foreach($productCodesData as $productCode){
        $detailProductBarCodes[] = [
            'productGuid' => $productCode->guid,
            'productBarCode' => $productCode->barcode
        ];

        $productCodes[] = $productCode->barcode;
    }
    // Step 3: Join codes or default to "-"
    $productCodice = count($productCodes) ? implode(', ', $productCodes) : '-';

    $productDesc = count($productCodes) ? "Tipo nastro: {$productCodice}" : "";

    $productBarCodes = array_merge($productBarCodes, $detailProductBarCodes);

    $detailsData[] = [
        'guid'         => $detail->guid,
        'intervento'   => $detail->intervento,
        'product'      => $productDesc,
        'materiale'    => $getParameterValues($detail->materiale_guids),
        'attivita'     => $getParameterValues($detail->attivita_guids),
        'mezziOpera'   => $getParameterValues($detail->mezzi_opera_guids),
        'noteCantiere' => $getParameterValues($detail->note_cantiere_guids),
        'rifPosizione' => $detail->rif_pos,
        'dettagliLavoro' => $detail->dettagli_lavoro,
        'noteSpedizione' => $detail->note_spedizione,
    ];
}

            $maintenance->details = $detailsData;
            $maintenance->productBarCodes = $productBarCodes;

                // Optional: eager load brief maintenance details count or summary if needed

                return $maintenance;
            });


        return ApiResponse::success(new AllOperatorMaintenanceCollection($maintenances));
    }

    public function show($guid){
        // Fetch main maintenance data
    $maintenance = DB::connection('proMaintenances')->table('maintenances')
        ->leftJoin('anagraphics', 'maintenances.anagraphic_guid', '=', 'anagraphics.guid')
        ->leftJoin('parameter_values as importances', 'maintenances.importance_guid', '=', 'importances.guid')
        ->leftJoin('parameter_values as status', 'maintenances.status_guid', '=', 'status.guid')
        ->leftJoin('anagraphic_addresses', 'maintenances.anagraphic_address_guid', '=', 'anagraphic_addresses.guid')
        ->leftJoin('dependants', 'maintenances.dependant_guid', '=', 'dependants.guid')
        ->leftJoin('anagraphic_phones', 'maintenances.dependant_phone_guid', '=', 'anagraphic_phones.guid')
        ->leftJoin('parameter_values as contracts', 'maintenances.contract_guid', '=', 'contracts.guid')
        ->select(
            'maintenances.*',
            'anagraphics.regione_sociale',
            'importances.parameter_value as importance',
            'status.parameter_value as status',
            'anagraphic_addresses.address',
            'dependants.nome',
            'dependants.cognome',
            'anagraphic_phones.phone as referencePhone',
            'contracts.parameter_value as contractName'
        )
        ->where('maintenances.guid', $guid)
        ->whereNull('maintenances.deleted_at')
        ->first();

    if (!$maintenance) {
        return ApiResponse::error('Maintenance not found.', [], HttpStatusCode::NOT_FOUND);
    }

    // Helper closure to get parameter values
    $getParameterValues = function ($guids) {
        return $guids
            ? ProParameterValue::whereIn('guid', explode('##', $guids))->pluck('parameter_value')->toArray()
            : null;
    };

    // Capos
    $maintenanceCapos = Employee::select(DB::raw("CONCAT(firstname, ' ', lastname) as full_name"))
        ->whereIn('guid', explode('##', $maintenance->capo_guids ?? ''))
        ->pluck('full_name')
        ->toArray();
    $maintenance->capos = $maintenanceCapos;

    // Operators
    $maintenanceOperators = Employee::select(DB::raw("CONCAT(firstname, ' ', lastname) as full_name"))
        ->whereIn('guid', explode('##', $maintenance->operatori_guids ?? ''))
        ->pluck('full_name')
        ->toArray();
    $maintenance->operators = $maintenanceOperators;

    // Vehicles
    $maintenance->vehicles = Vehicle::selectRaw('GROUP_CONCAT(description SEPARATOR ", ") as descriptions')
        ->whereIn('guid', explode('##', $maintenance->mezzo_guids ?? ''))
        ->value('descriptions');

    // Maintenance details
    $maintenanceDetails = DB::connection('proMaintenances')->table('maintenance_details')
        ->leftJoin('parameter_values as intervento', 'maintenance_details.tipo_intervento_guid', '=', 'intervento.guid')
        ->leftJoin('products', 'maintenance_details.product_guid', '=', 'products.guid')
        ->select(
            'maintenance_details.*',
            'intervento.parameter_value as intervento',
            'products.codice as productCodice'
        )
        ->where('maintenance_guid', $maintenance->guid)
        ->whereNull('maintenance_details.deleted_at')
        ->get();

    $detailsData = [];
    foreach ($maintenanceDetails as $detail) {
        $productCodice = $detail->productCodice ?? '-';
        $larghezza = $detail->larghezza ?? '-';
        $sviluppo = $detail->sviluppo ?? '-';

        $productDesc = "Tipo nastro: {$productCodice}";
        $detailsData[] = [
            'guid'         => $detail->guid,
            'intervento'   => $detail->intervento,
            'product'      => $productDesc,
            'materiale'    => $getParameterValues($detail->materiale_guids),
            'attivita'     => $getParameterValues($detail->attivita_guids),
            'mezziOpera'   => $getParameterValues($detail->mezzi_opera_guids),
            'rifPosizione' => $detail->rif_pos,
            'noteCantiere' => $getParameterValues($detail->note_cantiere_guids),
            'dettagliLavoro' => $detail->dettagli_lavoro,
            'noteSpedizione' => $detail->note_spedizione,
        ];
    }

    $maintenance->details = $detailsData;

    return ApiResponse::success(new OperatorMaintenanceResource($maintenance));


    }
}
