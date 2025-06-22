<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Enums\Maintenance\MaintenanceType;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AnagraphicAddress;
use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use App\Models\ReportProductBarcode;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class PeriodicMaintenanceController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    // public function index(Request $request)
    // {
    //     $monthsOfPeriodicMaintenance = 3;

    //     $monthsOfControlledMaintenance = 6;

    //     $filters = $request->filter ?? [];

    //     $reportProductBarcodes = ReportProductBarcode::select('id', 'product_barcode', 'created_at', 'maintenance_report_id', 'maintenance_type') // type = 0,1,2
    //         ->orderByDesc('created_at')
    //         ->when($filters['maintenanceType'] ?? null, function ($query, $maintenanceType) {
    //             if($maintenanceType == 1){
    //                 $query->whereIn('maintenance_type', [0,1]);
    //             }else {
    //                 $query->where('maintenance_type', $maintenanceType);
    //             }

    //         })
    //         ->get()
    //         ->groupBy('product_barcode')
    //         ->map(function ($group) {
    //             // Get last CONTROL
    //             $control = $group->firstWhere('maintenance_type', 2);

    //             // Get last MAINTENANCE, or INSTALLATION if no maintenance found
    //             $maintenanceOrInstall = $group->firstWhere('maintenance_type', 1)
    //                 ?? $group->firstWhere('maintenance_type', 0);

    //             return collect([$maintenanceOrInstall, $control])->filter(); // remove nulls
    //         })
    //         ->flatten(1)
    //         ->values();

    //     $periodicMaintenances = [];

    //     foreach ($reportProductBarcodes as $index => $reportProductBarcode) {

    //         $maintenanceData = [];

    //         if($reportProductBarcode->maintenance_type == MaintenanceType::MAINTANANCE || $reportProductBarcode->maintenance_type == MaintenanceType::INSTALLATION){
    //             $nextMaintenanceDate = $reportProductBarcode->created_at->copy()->addMonths($monthsOfPeriodicMaintenance);
    //             $maintenanceData['maintenanceType'] = MaintenanceType::MAINTANANCE->value;
    //         }

    //         if($reportProductBarcode->maintenance_type == MaintenanceType::CONTROL){
    //             $nextMaintenanceDate = $reportProductBarcode->created_at->copy()->addMonths($monthsOfControlledMaintenance);
    //             $maintenanceData['maintenanceType'] = MaintenanceType::CONTROL->value;
    //         }

    //         $report = MaintenanceReport::find($reportProductBarcode->maintenance_report_id);

    //         $maintenance = Maintenance::with('anagraphic')->where('guid', $report->maintenance_guid)->first();

    //         if(isset($filters['endedMaintenance']) && $filters['endedMaintenance'] === "1" && $nextMaintenanceDate->lessThanOrEqualTo(now())) {
    //             $periodicMaintenances[] = [
    //                 ...$maintenanceData,
    //                 'productBarcode' => $reportProductBarcode->product_barcode,
    //                 'maintenanceDate' => $nextMaintenanceDate->format('d/m/Y'),
    //                 'clientName' => $maintenance?->anagraphic?->regione_sociale??''
    //             ];
    //         }elseif(isset($filters['endedMaintenance']) && $filters['endedMaintenance'] === "0" && $nextMaintenanceDate->greaterThanOrEqualTo(now())){

    //             $periodicMaintenances[] = [
    //                 ...$maintenanceData,
    //                 'productBarcode' => $reportProductBarcode->product_barcode,
    //                 'maintenanceDate' => $nextMaintenanceDate->format('d/m/Y'),
    //                 'clientName' => $maintenance?->anagraphic?->regione_sociale??''
    //             ];
    //         }elseif($filters['endedMaintenance'] === null) {
    //             $periodicMaintenances[] = [
    //                 ...$maintenanceData,
    //                 'productBarcode' => $reportProductBarcode->product_barcode,
    //                 'maintenanceDate' => $nextMaintenanceDate->format('d/m/Y'),
    //                 'clientName' => $maintenance?->anagraphic?->regione_sociale??''
    //             ];
    //         }

    //     }

    //     dd($periodicMaintenances);




    //     //return ApiResponse::success(new AllOperatorMaintenanceCollection($maintenances));
    // }

    public function index(Request $request)
    {
        $monthsOfPeriodicMaintenance = 3;
        $monthsOfControlledMaintenance = 6;

        $filters = $request->filter ?? [];

        $startAt = isset($filters['startAt']) ? \Carbon\Carbon::parse($filters['startAt'])->startOfDay() : null;
        $endAt = isset($filters['endAt']) ? \Carbon\Carbon::parse($filters['endAt'])->endOfDay() : null;
        $now = now();

        // Step 1: Get all relevant barcodes, grouped and filtered
        $reportProductBarcodes = ReportProductBarcode::select(
                'id', 'product_barcode', 'created_at', 'maintenance_report_id', 'maintenance_type'
            )
            ->orderByDesc('created_at')
            ->when($filters['maintenanceType'] ?? null, function ($query, $maintenanceType) {
                if ($maintenanceType == 1) {
                    $query->whereIn('maintenance_type', [0, 1]); // MAINTENANCE or INSTALLATION
                } else {
                    $query->where('maintenance_type', $maintenanceType);
                }
            })
            ->get()
            ->groupBy('product_barcode')
            ->map(function ($group) {
                $control = $group->firstWhere('maintenance_type', MaintenanceType::CONTROL->value);
                $maintenance = $group->firstWhere('maintenance_type', MaintenanceType::MAINTANANCE->value)
                    ?? $group->firstWhere('maintenance_type', MaintenanceType::INSTALLATION->value);

                return collect([$maintenance, $control])->filter(); // Remove nulls
            })
            ->flatten(1)
            ->values();



        // Step 2: Apply logic to calculate next dates, and filter
        $periodicMaintenances = [];

        foreach ($reportProductBarcodes as $reportProductBarcode) {
            $maintenanceType = $reportProductBarcode->maintenance_type;

            $nextMaintenanceDate = null;
            $typeValue = null;

            if (in_array($maintenanceType, [MaintenanceType::MAINTANANCE, MaintenanceType::INSTALLATION])) {
                $nextMaintenanceDate = $reportProductBarcode->created_at->copy()->addMonths($monthsOfPeriodicMaintenance);
                $typeValue = MaintenanceType::MAINTANANCE->value;


            } elseif ($maintenanceType === MaintenanceType::CONTROL) {
                $nextMaintenanceDate = $reportProductBarcode->created_at->copy()->addMonths($monthsOfControlledMaintenance);
                $typeValue = MaintenanceType::CONTROL->value;
            }
            // Apply startAt and endAt filters
            if (
                ($startAt && $nextMaintenanceDate->lt($startAt)) ||
                ($endAt && $nextMaintenanceDate->gt($endAt))
            ) {
                continue;
            }
            // Apply endedMaintenance filter
            $isExpired = $nextMaintenanceDate->lessThanOrEqualTo($now);
            $endedFilter = $filters['endedMaintenance'] ?? null;

            if (
                ($endedFilter === "1" && !$isExpired) ||
                ($endedFilter === "0" && $isExpired)
            ) {
                continue;
            }

            // Get related client info
            $report = MaintenanceReport::find($reportProductBarcode->maintenance_report_id);
            $maintenance = Maintenance::with('anagraphic')->where('guid', $report->maintenance_guid)->first();
            $address = AnagraphicAddress::where('guid', $maintenance->anagraphic_address_guid)->first();
            $addressFormatted = $address->address.' '.$address->cap.' '.$address->city.' ('.$address->province.')';


            $reportHistory = ReportProductBarcode::where('product_barcode', $reportProductBarcode->product_barcode)->orderBy('created_at', 'asc')->get();

            $maintenanceHistory = [];

            foreach ($reportHistory as $key => $reportHistoryItem) {
                $maintenanceHistory[] = [
                    'maintenanceType' => $reportHistoryItem->maintenance_type,
                    'maintenanceDate' => $reportHistoryItem->created_at->format('d/m/Y'),
                ];
            }

            $periodicMaintenances[] = [
                'maintenanceType' => $typeValue,
                'productBarcode' => $reportProductBarcode->product_barcode,
                'productCode' => "",
                'productDescription' => "",
                'maintenanceDate' => $nextMaintenanceDate->format('d/m/Y'),
                'clientName' => $maintenance?->anagraphic?->regione_sociale ?? '',
                'clientAddress' => $addressFormatted,
                'maintenanceHistory' => $maintenanceHistory
            ];
        }

        // Output the result (or return as response)
        return response()->json([
            'data' => $periodicMaintenances
        ]);
    }


}
