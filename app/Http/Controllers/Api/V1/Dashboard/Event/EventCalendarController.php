<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Event;

use App\Enums\Maintenance\MaintenanceType;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Event\AllEventResource;
use App\Models\CalendarEvent;
use App\Models\ProCalendarEvent;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EventCalendarController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }
    public function index(Request $request)
    {
    $filters = $request->filter ?? [];

    $maintenanceType = $filters['maintenanceType'] ?? null;
    $startAt = $filters['startAt'] ?? null;
    $endAt = $filters['endAt'] ?? null;

    // Convert start/end to Carbon if present
    $startAt = $startAt ? Carbon::parse($startAt)->startOfDay() : null;
    $endAt = $endAt ? Carbon::parse($endAt)->endOfDay() : null;

    $events = collect();
    $maintenances = collect();

    // 🔧 Logic:
    // maintenanceType:
    // - 1 → get CalendarEvent where maintenance_type in [0, 1]
    // - 2 → get CalendarEvent where maintenance_type = 2
    // - 3 → get only ProCalendarEvent (maintenances)

    if ($maintenanceType == 1 || $maintenanceType === null) {
        $events = CalendarEvent::whereIn('maintenance_type', [MaintenanceType::INSTALLATION->value, MaintenanceType::MAINTANANCE->value])
            ->when($startAt, fn($q) => $q->where('start_at', '>=', $startAt))
            ->when($endAt, fn($q) => $q->where('start_at', '<=', $endAt))
            ->get();
    }

    if ($maintenanceType == 2) {
        $events = CalendarEvent::where('maintenance_type', MaintenanceType::CONTROL->value)
            ->when($startAt, fn($q) => $q->where('start_at', '>=', $startAt))
            ->when($endAt, fn($q) => $q->where('start_at', '<=', $endAt))
            ->get();
    }

    if ($maintenanceType == 3) {
        $maintenances = ProCalendarEvent::when($startAt, fn($q) => $q->where('start_at', '>=', $startAt))
            ->when($endAt, fn($q) => $q->where('start_at', '<=', $endAt))
            ->get();
    }

    // Merge both collections
    $merged = $events->merge($maintenances);

    return ApiResponse::success(AllEventResource::collection($merged));


    }



}
