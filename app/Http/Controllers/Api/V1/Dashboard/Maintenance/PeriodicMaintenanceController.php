<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Enums\Maintenance\MaintenanceType;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PeriodicMaintenance\AllPeriodicMaintenanceCollection;
use App\Models\Anagraphic;
use App\Models\AnagraphicAddress;
use App\Models\CalendarEvent;
use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use App\Models\ReportProductBarcode;
use App\Utils\PaginateCollection;
use Carbon\Carbon;
use GuzzleHttp\Promise\Create;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class PeriodicMaintenanceController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }
    //     public function index(Request $request)
    // {

    //     $filters = $request->filter ?? [];

    //     $now = now();
    //     $nextMonth = $now->copy()->addMonth();

    //     $startAt = isset($filters['startAt']) ? Carbon::parse($filters['startAt'])->startOfDay() : null;
    //     $endAt = isset($filters['endAt']) ? Carbon::parse($filters['endAt'])->endOfDay() : null;
    //     $endedFilter = $filters['endedMaintenance'] ?? null;

    //     // Step 1: Get all latest calendar events for each type
    //     $events = DB::table('calendar_events')
    //         ->selectRaw('*, ROW_NUMBER() OVER (PARTITION BY product_barcode, maintenance_type ORDER BY start_at DESC) AS rn')
    //         ->whereIn('maintenance_type', [
    //             MaintenanceType::INSTALLATION->value,
    //             MaintenanceType::MAINTANANCE->value,
    //             MaintenanceType::CONTROL->value
    //         ])
    //         ->get()
    //         ->where('rn', 1); // only latest per type

    //     // Step 2: Group by product_barcode
    //     $grouped = $events->groupBy('product_barcode');

    //     // Step 3: Load all clients and addresses once
    //     $clientGuids = $events->pluck('client_guid')->unique()->filter();
    //     $clients = Anagraphic::whereIn('guid', $clientGuids)->get()->keyBy('guid');
    //     $addresses = AnagraphicAddress::whereIn('anagraphic_guid', $clientGuids)->get()->keyBy('anagraphic_guid');

    //     $results = [];

    //     foreach ($grouped as $barcode => $records) {
    //         $installation = $records->firstWhere('maintenance_type', MaintenanceType::INSTALLATION->value);
    //         $maintenance = $records->firstWhere('maintenance_type', MaintenanceType::MAINTANANCE->value);
    //         $control = $records->firstWhere('maintenance_type', MaintenanceType::CONTROL->value);

    //         // Periodic Maintenance
    //         if ($maintenance || $installation) {
    //             $results[] = $this->getNextMaintenanceDate(
    //                 $maintenance ?? $installation,
    //                 MaintenanceType::MAINTANANCE->value,
    //                 3,
    //                 $installation?->start_at,
    //                 $clients,
    //                 $addresses
    //             );
    //         }

    //         // Control Maintenance
    //         if ($control || $installation) {
    //             $results[] = $this->getNextMaintenanceDate(
    //                 $control ?? $installation,
    //                 MaintenanceType::CONTROL->value,
    //                 6,
    //                 $installation?->start_at,
    //                 $clients,
    //                 $addresses
    //             );
    //         }
    //     }

    //     return ApiResponse::success(
    //         new AllPeriodicMaintenanceCollection(
    //             PaginateCollection::paginate(collect($results), $request->pageSize ?? 10000)
    //         )
    //     );
    // }


    // private function getNextMaintenanceDate($maintenance, $maintenanceType, $intervalMonths, $installDate = null, $clients = [], $addresses = [])
    // {
    //     $now = now();
    //     $nextMonth = $now->copy()->addMonth();
    //     $startDate = Carbon::parse($maintenance->start_at);

    //     if ($maintenance->is_done == 1) {
    //         $startDate = Carbon::parse($maintenance->end_at)
    //             ->addMonths($intervalMonths)
    //             ->setTime(0, 0, 0);
    //     }

    //     $statusColor = 0; // expired
    //     if ($startDate->gt($now)) {
    //         $statusColor = $startDate->lte($nextMonth) ? 1 : 2;
    //     }

    //     // History (you can cache this too if needed)
    //     $history = CalendarEvent::where('product_barcode', $maintenance->product_barcode)
    //         ->orderBy('start_at')
    //         ->get()
    //         ->map(fn($item) => [
    //             'maintenanceType' => $item->maintenance_type,
    //             'maintenanceDate' => $item->start_at->format('d/m/Y'),
    //         ])
    //         ->toArray();

    //     $client = $clients[$maintenance->client_guid] ?? null;
    //     $address = $addresses[$maintenance->client_guid] ?? null;

    //     return [
    //         'maintenanceType' => $maintenanceType,
    //         'productBarcode' => $maintenance->product_barcode,
    //         'productCode' => $maintenance->product_barcode,
    //         'productDescription' => trim($maintenance->description) . ' - ' . $maintenance->product_barcode,
    //         'maintenanceDate' => $startDate->format('d/m/Y'),
    //         'clientName' => $client?->regione_sociale ?? '',
    //         'clientAddress' => $address
    //             ? trim("{$address->address} {$address->city} ({$address->province})")
    //             : '',
    //         'statusColor' => $statusColor,
    //         'installationDate' => $installDate
    //             ? Carbon::parse($installDate)->format('d/m/Y')
    //             : '',
    //         'maintenanceHistory' => $history,
    //     ];
    // }

//     public function index(Request $request)
// {
//     $filters = $request->filter ?? [];

//     $now = now();
//     $nextMonth = $now->copy()->addMonth();

//     $endedFilter = $filters['endedMaintenance'] ?? null;
//     $maintenanceType = $filters['maintenanceType'] ?? null;

//     // Base query with latest records by product_barcode and maintenance_type
//     $baseQuery = DB::table('calendar_events')
//         ->selectRaw('
//             *,
//             ROW_NUMBER() OVER (PARTITION BY product_barcode, maintenance_type ORDER BY start_at DESC) AS rn
//         ')
//         ->whereIn('maintenance_type', [
//             MaintenanceType::INSTALLATION->value,
//             MaintenanceType::MAINTANANCE->value,
//             MaintenanceType::CONTROL->value,
//         ]);

//     dd($endedFilter);
//     // Convert to collection to simulate SQL window function filtering
//     $events = $baseQuery->get()->where('rn', 1);

//     // Preload clients and addresses
//     $clientGuids = $events->pluck('client_guid')->unique()->filter();
//     $clients = Anagraphic::whereIn('guid', $clientGuids)->get()->keyBy('guid');
//     $addresses = AnagraphicAddress::whereIn('anagraphic_guid', $clientGuids)->get()->keyBy('anagraphic_guid');

//     $results = [];

//     foreach ($events->groupBy('product_barcode') as $barcode => $records) {
//         $installation = $records->firstWhere('maintenance_type', MaintenanceType::INSTALLATION->value);
//         $maintenance = $records->firstWhere('maintenance_type', MaintenanceType::MAINTANANCE->value);
//         $control = $records->firstWhere('maintenance_type', MaintenanceType::CONTROL->value);

//         // Prepare next dates
//         if ($maintenance || $installation) {
//             $maintenanceResult = $this->getNextMaintenanceDate(
//                 $maintenance ?? $installation,
//                 MaintenanceType::MAINTANANCE->value,
//                 3,
//                 $installation?->start_at,
//                 $clients,
//                 $addresses
//             );

//             if ($this->filterByEndCondition($maintenanceResult['maintenanceDate'], $endedFilter, $now, $nextMonth)) {
//                 $results[] = $maintenanceResult;
//             }
//         }

//         if ($control || $installation) {
//             $controlResult = $this->getNextMaintenanceDate(
//                 $control ?? $installation,
//                 MaintenanceType::CONTROL->value,
//                 6,
//                 $installation?->start_at,
//                 $clients,
//                 $addresses
//             );

//             if ($this->filterByEndCondition($controlResult['maintenanceDate'], $endedFilter, $now, $nextMonth)) {
//                 $results[] = $controlResult;
//             }
//         }
//     }

//     dd(count($results));

//     return ApiResponse::success(
//         new AllPeriodicMaintenanceCollection(
//             PaginateCollection::paginate(collect($results), $request->pageSize ?? 10000)
//         )
//     );
// }

// private function filterByEndCondition($maintenanceDate, $endedFilter, $now, $nextMonth)
// {
//     $parsedDate = Carbon::createFromFormat('d/m/Y', $maintenanceDate);

//     if ($endedFilter === '1') {
//         return $parsedDate->lt($now);
//     }

//     if ($endedFilter === '0') {
//         return $parsedDate->gte($now) && $parsedDate->lte($nextMonth);
//     }

//     return true; // No filter
// }


//     private function getNextMaintenanceDate($maintenance, $maintenanceType, $intervalMonths, $installDate = null, $clients = [], $addresses = [])
//     {
//         $now = now();
//         $nextMonth = $now->copy()->addMonth();
//         $startDate = Carbon::parse($maintenance->start_at);

//         if ($maintenance->is_done == 1) {
//             $startDate = Carbon::parse($maintenance->end_at)
//                 ->addMonths($intervalMonths)
//                 ->setTime(0, 0, 0);
//         }

//         $statusColor = 0; // expired
//         if ($startDate->gt($now)) {
//             $statusColor = $startDate->lte($nextMonth) ? 1 : 2;
//         }

//         // History (you can cache this too if needed)
//         $history = CalendarEvent::where('product_barcode', $maintenance->product_barcode)
//             ->orderBy('start_at')
//             ->get()
//             ->map(fn($item) => [
//                 'maintenanceType' => $item->maintenance_type,
//                 'maintenanceDate' => $item->start_at->format('d/m/Y'),
//             ])
//             ->toArray();

//         $client = $clients[$maintenance->client_guid] ?? null;
//         $address = $addresses[$maintenance->client_guid] ?? null;

//         return [
//             'maintenanceType' => $maintenanceType,
//             'productBarcode' => $maintenance->product_barcode,
//             'productCode' => $maintenance->product_barcode,
//             'productDescription' => trim($maintenance->description) . ' - ' . $maintenance->product_barcode,
//             'maintenanceDate' => $startDate->format('d/m/Y'),
//             'clientName' => $client?->regione_sociale ?? '',
//             'clientAddress' => $address
//                 ? trim("{$address->address} {$address->city} ({$address->province})")
//                 : '',
//             'statusColor' => $statusColor,
//             'installationDate' => $installDate
//                 ? Carbon::parse($installDate)->format('d/m/Y')
//                 : '',
//             'maintenanceHistory' => $history,
//         ];
//     }


public function index(Request $request)
{
    $filters = $request->filter ?? [];

    $now = now();
    $nextMonth = $now->copy()->addMonth();

    $maintenanceTypeFilter = $filters['maintenanceType'] ?? null; // 1 or 2
    $endedFilter = $filters['endedMaintenance'] ?? null;          // 1 or 0
    $clientGuid = $filters['clientGuid'] ?? null;

    $startAtFilter = isset($filters['startAt']) ? Carbon::parse($filters['startAt'])->startOfDay() : null;
    $endAtFilter = isset($filters['endAt']) ? Carbon::parse($filters['endAt'])->endOfDay() : null;

    $barcodes = CalendarEvent::distinct('product_barcode')->when(
        $clientGuid,
        fn($query) => $query->where('client_guid', $clientGuid)
    )->pluck('product_barcode');

    $results = [];

    foreach ($barcodes as $barcode) {
        $installation = CalendarEvent::where('product_barcode', $barcode)
            ->where('maintenance_type', MaintenanceType::INSTALLATION->value)
            ->orderByDesc('start_at')
            ->first();

        // MAINTANANCE
        if ($maintenanceTypeFilter === null || $maintenanceTypeFilter == MaintenanceType::MAINTANANCE->value) {
            $maintenance = CalendarEvent::where('product_barcode', $barcode)
                ->where('maintenance_type', MaintenanceType::MAINTANANCE->value)
                ->orderByDesc('start_at')
                ->where('is_done', 0)
                ->first();

            if ($maintenance) {
                $eventDate = $maintenance->start_at;

                if (
                    $this->passesDateFilters($eventDate, $startAtFilter, $endAtFilter) &&
                    $this->passesEndedCondition($eventDate, $endedFilter)
                ) {
                    $results[] = $this->buildMaintenanceResult(
                        $maintenance,
                        MaintenanceType::MAINTANANCE->value,
                        3,
                        $installation
                    );
                }
            }
        }

        // CONTROL
        if ($maintenanceTypeFilter === null || $maintenanceTypeFilter == MaintenanceType::CONTROL->value) {
            $control = CalendarEvent::where('product_barcode', $barcode)
                ->where('maintenance_type', MaintenanceType::CONTROL->value)
                ->orderByDesc('start_at')
                ->where('is_done', 0)
                ->first();

            if ($control) {
                $eventDate = $control->start_at;

                if (
                    $this->passesDateFilters($eventDate, $startAtFilter, $endAtFilter) &&
                    $this->passesEndedCondition($eventDate, $endedFilter)
                ) {
                    $results[] = $this->buildMaintenanceResult(
                        $control,
                        MaintenanceType::CONTROL->value,
                        6,
                        $installation
                    );
                }
            }
        }
    }

    return ApiResponse::success(
        new AllPeriodicMaintenanceCollection(
            PaginateCollection::paginate(collect($results), $request->pageSize ?? 10000)
        )
    );
}

private function buildMaintenanceResult($event, $type, $intervalMonths, $installation = null)
{
    $now = now();
    $nextMonth = $now->copy()->addMonth();

    $startDate = Carbon::parse($event->start_at);

    $statusColor = 0;
    if ($startDate->gt($now)) {
        $statusColor = $startDate->lte($nextMonth) ? 1 : 2;
    }

    $history = CalendarEvent::where('product_barcode', $event->product_barcode)
        ->orderBy('start_at')
        ->get()
        ->map(fn($item) => [
            'maintenanceType' => $item->maintenance_type,
            'maintenanceDate' => $item->start_at->format('d/m/Y'),
        ])
        ->toArray();

    $client = Anagraphic::where('guid', $event->client_guid)->first();
    $address = AnagraphicAddress::where('anagraphic_guid', $event->client_guid)->first();

    return [
        'maintenanceType' => $type,
        'productBarcode' => $event->product_barcode,
        'productCode' => $event->product_barcode,
        'productDescription' => trim($event->description) . ' - ' . $event->product_barcode,
        'maintenanceDate' => $startDate->format('d/m/Y'),
        'clientName' => $client?->regione_sociale ?? '',
        'clientGuid' => $client->guid,
        'clientAddress' => $address
            ? trim("{$address->address} {$address->city} ({$address->province})")
            : '',
        'statusColor' => $statusColor,
        'installationDate' => $installation ? Carbon::parse($installation->start_at)->format('d/m/Y') : '',
        'maintenanceHistory' => $history,
    ];
}

private function passesDateFilters($date, $start = null, $end = null)
{
    $d = Carbon::parse($date);

    if ($start && $d->lt($start)) {
        return false;
    }

    if ($end && $d->gt($end)) {
        return false;
    }

    return true;
}

private function passesEndedCondition($date, $endedFilter)
{
    if ($endedFilter === null) return true;

    $d = Carbon::parse($date);
    $now = now();
    $nextMonth = $now->copy()->addMonth();

    if ($endedFilter == 1) return $d->lt($now);              // expired
    if ($endedFilter == 0) return $d->between($now, $nextMonth); // upcoming

    return true;
}


}
