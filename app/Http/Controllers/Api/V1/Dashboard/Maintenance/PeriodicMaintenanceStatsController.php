<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Enums\Maintenance\MaintenanceType;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ReportProductBarcode;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
class PeriodicMaintenanceStatsController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    public function index(Request $request)
    {
        $monthsOfPeriodicMaintenance = 3;
        $monthsOfControlledMaintenance = 5;
        $filters = $request->filter ?? [];

        $now = now();
        $inThirtyDays = now()->addDays(30);

        // STEP 1: Get all latest per barcode (same as yours)
        $reportProductBarcodes = ReportProductBarcode::select('id', 'product_barcode', 'created_at', 'maintenance_report_id', 'maintenance_type')
            ->orderByDesc('created_at')
            ->when($filters['maintenanceType'] ?? null, function ($query, $maintenanceType) {
                if ($maintenanceType == 1) {
                    $query->whereIn('maintenance_type', [0, 1]);
                } else {
                    $query->where('maintenance_type', $maintenanceType);
                }
            })
            ->get()
            ->groupBy('product_barcode')
            ->map(function ($group) {
                $control = $group->firstWhere('maintenance_type', 2);
                $maintenanceOrInstall = $group->firstWhere('maintenance_type', 1) ?? $group->firstWhere('maintenance_type', 0);
                return collect([$maintenanceOrInstall, $control])->filter();
            })
            ->flatten(1)
            ->values();

        // STEP 2: Initialize counters
        $totalCount = 0;
        $expiredMaintenanceCount = 0;
        $expiredControlCount = 0;
        $upcomingMaintenanceCount = 0;
        $upcomingControlCount = 0;

        foreach ($reportProductBarcodes as $barcode) {
            $totalCount++;

            $nextDate = match ($barcode->maintenance_type) {
                MaintenanceType::INSTALLATION, MaintenanceType::MAINTANANCE => $barcode->created_at->copy()->addMonths($monthsOfPeriodicMaintenance),
                MaintenanceType::CONTROL => $barcode->created_at->copy()->addMonths($monthsOfControlledMaintenance),
            };

            // Expired
            if ($nextDate->lessThanOrEqualTo($now)) {
                if (in_array($barcode->maintenance_type, [MaintenanceType::INSTALLATION, MaintenanceType::MAINTANANCE])) {
                    $expiredMaintenanceCount++;
                } elseif ($barcode->maintenance_type === MaintenanceType::CONTROL) {
                    $expiredControlCount++;
                }
            }

            // Coming within 30 days
            elseif ($nextDate->greaterThan($now) && $nextDate->lessThanOrEqualTo($inThirtyDays)) {
                if (in_array($barcode->maintenance_type, [MaintenanceType::INSTALLATION, MaintenanceType::MAINTANANCE])) {
                    $upcomingMaintenanceCount++;
                } elseif ($barcode->maintenance_type === MaintenanceType::CONTROL) {
                    $upcomingControlCount++;
                }
            }
        }

        // Return or pass to view
        return ApiResponse::success([
            'totalMaintenanceCount' => $totalCount,
            'expiredMaintenanceCount' => $expiredMaintenanceCount,
            'expiredControlCount' => $expiredControlCount,
            'upcomingMaintenanceCount' => $upcomingMaintenanceCount,
            'upcomingControlCount' => $upcomingControlCount,
        ]);
    }

}
