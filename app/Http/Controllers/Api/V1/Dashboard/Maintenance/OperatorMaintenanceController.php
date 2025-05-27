<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Enums\Maintenance\MaintenanceStatus;
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
use App\Models\ProParameterValue;
use App\Models\Vehicle;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperatorMaintenanceController extends Controller implements HasMiddleware
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

        $maintenances = QueryBuilder::for(Maintenance::class)
            ->allowedFilters([
                AllowedFilter::custom('search', new FilterMaintenance()),
                AllowedFilter::custom('date', new FilterMaintenanceDate()),
                AllowedFilter::exact('client', 'anagraphic_guid'),
                AllowedFilter::exact('status', 'status_guid'),
                AllowedFilter::exact('importance', 'importance_guid')
            ])
            ->select([
                'guid',
                'operatori_guids',
                'codice',
                'start_date',
                'arrive_hour',
                'anagraphic_guid',
                'status_guid',
                'importance_guid'
            ])
            ->when($userRole == 'operator', function ($query) use ($authUser) {
                return $query->where('operatori_guids', 'like', '%' . $authUser->operator_guid . '%');
            })
            ->when($userRole == 'operator', function ($query) {
                return $query->where('status_guid', MaintenanceStatus::PROGRAMMATO);
            })
            ->when($userRole == 'admin', function ($query) use ($authUser) {
                return $query->where('status_guid', request('filter[status_guid]'));
            })
            ->whereNull('deleted_at')
            ->with(['anagraphic' => function ($query) {
                $query->select('guid', 'regione_sociale');
            }])
            ->orderByRaw('start_date IS NULL, start_date DESC')
            ->paginate($request->pageSize ?? 100000);

        return ApiResponse::success(new AllOperatorMaintenanceCollection($maintenances));
    }

    public function show($guid){


    //     $maintenance = DB::connection('proMaintenances')->table('maintenances')
    //         ->leftJoin('anagraphics', 'maintenances.anagraphic_guid', '=', 'anagraphics.guid')
    //         ->leftJoin('parameter_values as importances', 'maintenances.importance_guid', '=', 'importances.guid')
    //         ->leftJoin('parameter_values as status', 'maintenances.status_guid', '=', 'status.guid')
    //         ->leftJoin('anagraphic_addresses', 'maintenances.anagraphic_address_guid', '=', 'anagraphic_addresses.guid')
    //         ->leftJoin('dependants', 'maintenances.dependant_guid', '=', 'dependants.guid')
    //         ->leftJoin('anagraphic_phones', 'maintenances.dependant_phone_guid', '=', 'anagraphic_phones.guid')
    //         ->leftJoin('parameter_values as contracts', 'maintenances.contract_guid', '=', 'contracts.guid')

    //         ->select(
    //             'maintenances.*',
    //             'anagraphics.regione_sociale',
    //             'importances.parameter_value as importance',
    //             'status.parameter_value as status',
    //             'anagraphic_addresses.address',
    //             'dependants.nome',
    //             'dependants.cognome',
    //             'anagraphic_phones.phone as referencePhone',
    //             'contracts.parameter_value as contractName'
    //             )
    //         ->where('maintenances.guid', $guid)
    //         ->whereNull('maintenances.deleted_at')
    //         ->first();

    //         $maintenanceCapos = Employee::select('firstname', 'lastname')
    //             ->whereIn('guid', explode('##', $maintenance->capo_guids))
    //             ->get();

    //         if($maintenanceCapos->count() > 0) {
    //             $maintenance->capos =$maintenanceCapos->pluck('full_name')->toArray();
    //         }

    //         $maintenanceOperators = Employee::select('firstname', 'lastname')
    //             ->whereIn('guid', explode('##', $maintenance->operatori_guids))
    //             ->get();

    //         if($maintenanceOperators->count() > 0) {
    //             $maintenance->operators = $maintenanceCapos->pluck('full_name')->toArray();
    //         }

    //         $maintenance->vehicles = Vehicle::selectRaw('GROUP_CONCAT(description SEPARATOR ", ") as descriptions')
    // ->whereIn('guid', explode('##', $maintenance->mezzo_guids))
    // ->value('descriptions');

    //         $maintenanceDetails = DB::connection('proMaintenances')->table('maintenance_details')
    //             ->leftJoin('parameter_values as intervento', 'maintenance_details.tipo_intervento_guid', '=', 'intervento.guid')
    //             ->leftJoin('products', 'maintenance_details.product_guid', '=', 'products.guid')
    //             ->select('maintenance_details.*',
    //                 'intervento.parameter_value as intervento',
    //                 'products.codice as productCodice'
    //             )
    //             ->where('maintenance_guid', $maintenance->guid)
    //             ->whereNull('maintenance_details.deleted_at')
    //             ->get();

    //         $maintenanceDetailsData = [];

    //         foreach ($maintenanceDetails as $index =>$maintenanceDetail) {
    //             $maintenanceDetail->product = 'Tipo nastro ' . $maintenanceDetail->productCodice . '\n' . ' Larghezza:  ' . $maintenanceDetail->larghezza . 'Lunghezza: '. $maintenanceDetail->sviluppo;
    //             $maintenanceDetail->materiale = ProParameterValue::select('parameter_value')
    //             ->whereIn('guid', explode('##', $maintenanceDetail->materiale_guids))
    //             ->get();
    //             if($maintenanceDetail->materiale->count() > 0) {
    //                 $maintenanceDetail->materiale = $maintenanceDetail->materiale->pluck('parameter_value')->toArray();
    //             }else{
    //                 $maintenanceDetail->materiale = null;
    //             }

    //             $maintenanceDetail->attivita = ProParameterValue::select('parameter_value')
    //             ->whereIn('guid', explode('##', $maintenanceDetail->attivita_guids))
    //             ->get();
    //             if($maintenanceDetail->attivita->count() > 0) {
    //                 $maintenanceDetail->attivita = $maintenanceDetail->attivita->pluck('parameter_value')->toArray();
    //             }else{
    //                 $maintenanceDetail->attivita = null;
    //             }

    //             $maintenanceDetail->mezzi_opera = ProParameterValue::select('parameter_value')
    //             ->whereIn('guid', explode('##', $maintenanceDetail->mezzi_opera_guids))
    //             ->get();
    //             if($maintenanceDetail->mezzi_opera->count() > 0) {
    //                 $maintenanceDetail->mezzi_opera = $maintenanceDetail->mezzi_opera->pluck('parameter_value')->toArray();
    //             }else{
    //                 $maintenanceDetail->mezzi_opera = null;
    //             }

    //             $maintenanceDetailsData[] = [
    //                 'guid' => $maintenanceDetail->guid,
    //                 'intervento' => $maintenanceDetail->intervento,
    //                 'product' => $maintenanceDetail->product,
    //                 'materiale' => $maintenanceDetail->materiale,
    //                 'attivita' => $maintenanceDetail->attivita,
    //                 'mezziOpera' => $maintenanceDetail->mezzi_opera,
    //                 'rifPosizione' => $maintenanceDetail->rif_pos
    //             ];
    //         }

    //         $maintenance->details = $maintenanceDetailsData;

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
        return ApiResponse::error('Maintenance not found.', [], 404);
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

        $productDesc = "Tipo nastro: {$productCodice}" . PHP_EOL .
                       "Larghezza: {$larghezza} | Lunghezza: {$sviluppo}";

        $detailsData[] = [
            'guid'         => $detail->guid,
            'intervento'   => $detail->intervento,
            'product'      => $productDesc,
            'materiale'    => $getParameterValues($detail->materiale_guids),
            'attivita'     => $getParameterValues($detail->attivita_guids),
            'mezziOpera'   => $getParameterValues($detail->mezzi_opera_guids),
            'rifPosizione' => $detail->rif_pos,
        ];
    }

    $maintenance->details = $detailsData;

    return ApiResponse::success(new OperatorMaintenanceResource($maintenance));


    }
}
