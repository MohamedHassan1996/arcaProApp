<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Maintenance;

use App\Enums\Maintenance\MaintenanceType;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\ReportProductBarcode;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

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
        // $monthsOfPeriodicMaintenance = 3;
        // $monthsOfControlledMaintenance = 6;

        //$filters = $request->filter ?? [];

                // $startAt = isset($filters['startAt']) ? Carbon::parse($filters['startAt'])->startOfDay() : null;
        // $endAt = isset($filters['endAt']) ? Carbon::parse($filters['endAt'])->endOfDay() : null;
        // $endedFilter = $filters['endedMaintenance'] ?? null;

        $now = now(config('app.timezone'));
        $inThirtyDays = $now->copy()->addDays(30);


        $totalCount = CalendarEvent::whereIn('maintenance_type', [
            MaintenanceType::MAINTANANCE->value,
            MaintenanceType::CONTROL->value
        ])->count('product_barcode');

        $expiredMaintenanceCount = CalendarEvent::where('maintenance_type', MaintenanceType::MAINTANANCE->value)->where('is_done', 0)->where('start_at', '<', $now)->count();

        $expiredControlCount = CalendarEvent::where('maintenance_type', MaintenanceType::CONTROL->value)->where('is_done', 0)->where('start_at', '<', $now)->count();
        $upcomingMaintenanceCount = CalendarEvent::where('maintenance_type', MaintenanceType::MAINTANANCE->value)
            ->where('is_done', 0)
            ->whereBetween('start_at', [$now, $inThirtyDays])
            ->count();


        $upcomingControlCount = CalendarEvent::select('product_barcode')
            ->where('maintenance_type', MaintenanceType::CONTROL->value)
            ->where('is_done', 0)
            ->whereBetween('start_at', [$now, $inThirtyDays])
            ->count();

//         $upcomingControlCount = CalendarEvent::select('product_barcode')
//     ->where('maintenance_type', MaintenanceType::MAINTANANCE->value)
//     ->where('is_done', 0)
//     ->whereBetween('start_at', [$now, $inThirtyDays])
//     ->groupBy('product_barcode')
//     ->havingRaw('COUNT(*) > 1')
//     ->ddRawSql();

//    dd($upcomingControlCount);

// $toDelete = DB::select("
//     SELECT id FROM (
//         SELECT id,
//                ROW_NUMBER() OVER (
//                    PARTITION BY product_barcode, maintenance_type, start_at
//                    ORDER BY id ASC
//                ) AS rn
//         FROM calendar_events
//         WHERE maintenance_type = ?
//           AND is_done = 0
//     ) AS duplicates
//     WHERE rn > 1
// ", [MaintenanceType::MAINTANANCE->value]);


        return ApiResponse::success([
            'totalMaintenanceCount' => $totalCount,
            'expiredMaintenanceCount' => $expiredMaintenanceCount,
            'expiredControlCount' => $expiredControlCount,
            'upcomingMaintenanceCount' => $upcomingMaintenanceCount,
            'upcomingControlCount' => $upcomingControlCount,
        ]);
    }
}
