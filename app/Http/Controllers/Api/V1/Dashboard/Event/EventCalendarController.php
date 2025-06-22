<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Event;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Event\AllEventResource;
use App\Models\CalendarEvent;
use App\Models\ProCalendarEvent;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class EventCalendarController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    /*public function index(Request $request)
    {
        $filters = $request->filter ?? [];
        // $events = CalendarEvent::when($filters['maintenanceType']?? null, function ($query) use ($filters) {
        //     $query->where('maintenance_type', $filters['maintenanceType']);
        // })
        // ->when($filters['startAt'] && $filters['endAt'], function ($query) use ($filters) {
        //     $query->where('start_at', '>=', $filters['startAt'])
        //         ->where('end_at', '<=', $filters['endAt']);
        // })->when($filters['startAt'] && !$filters['endAt'], function ($query) use ($filters) {
        //     $query->where('start_at', '>=', $filters['startAt']);
        // })->when(!$filters['startAt'] && $filters['endAt'], function ($query) use ($filters) {
        //     $query->where('end_at', '<=', $filters['endAt']);
        // })
        // ->get();

                //dd($events->toArray());


        $events = ProCalendarEvent::all();

        dd($events->toArray());


        return ApiResponse::success(AllEventResource::collection($events));
    }*/

//     public function index(Request $request)
// {
//     $filters = $request->filter ?? [];

//     $maintenanceType = $filters['maintenanceType'] ?? null;

//     $startAt = $filters['startAt'] ?? null;
//     $endAt = $filters['endAt'] ?? null;

//     $mergedEvents = collect();

//     // === 1. Get calendar_events if maintenanceType is null or 1 or 2 ===
//     if (is_null($maintenanceType) || in_array($maintenanceType, [1, 2])) {
//         $calendarEvents = CalendarEvent::when(in_array($maintenanceType, [1, 2]), function ($query) use ($maintenanceType) {
//                 $query->where('maintenance_type', $maintenanceType);
//             })
//             ->when($startAt, fn($query) => $query->where('start_at', '>=', $startAt))
//             ->when($endAt, fn($query) => $query->where('end_at', '<=', $endAt))
//             ->get();

//         foreach ($calendarEvents as $event) {
//             $mergedEvents->push(new AllEventResource($event));
//         }
//     }

//     // === 2. Get external events if maintenanceType is null or 3 ===
//     if (is_null($maintenanceType) || $maintenanceType == 3) {
//         $externalEventsQuery = DB::connection('proMaintenances')
//             ->table('events')
//             ->join('maintenances', 'maintenances.guid', '=', 'events.maintenance_guid')
//             ->select(
//                 DB::raw('NULL as id'),
//                 'events.guid as guid',
//                 'events.maintenance_guid as maintenance_guid',
//                 'events.title',
//                 'events.description',
//                 'events.start_date as start_at',
//                 'events.end_date as end_at',
//                 'events.all_day as is_all_day',
//                 'events.type',
//                 'maintenances.anagraphic_guid as client_guid',
//                 'events.data_creazione as created_at',
//                 'events.versione as updated_at'
//             )
//             ->where('events.type', 1)
//             ->where('maintenances.status_guid', '66cb1c1b-693d-46a8-b1e7-4d925163467e');

//         if ($startAt) {
//             $externalEventsQuery->where('events.start_date', '>=', $startAt);
//         }

//         if ($endAt) {
//             $externalEventsQuery->where('events.end_date', '<=', $endAt);
//         }

//         $externalEvents = $externalEventsQuery->get();

//         foreach ($externalEvents as $event) {
//             $mergedEvents->push(new AllEventResource((object)[
//                 'id' => $event->id ?? $event->guid,
//                 'title' => $event->title,
//                 'description' => $event->description,
//                 'start_at' => $event->start_at,
//                 'end_at' => $event->end_at,
//                 'maintenance_type' => $event->maintenance_type ?? 3,
//                 'maintenance_guid' => $event->maintenance_guid,
//             ]));
//         }
//     }

//     return ApiResponse::success(AllEventResource::collection($mergedEvents));
// }

    public function index(Request $request)
    {
        $filters = $request->filter ?? [];

        $maintenanceType = $filters['maintenanceType'] ?? null;
        $startAt = $filters['startAt'] ?? null;
        $endAt = $filters['endAt'] ?? null;
        $now = now();

        $mergedEvents = collect();

        // === 1. Get calendar_events if maintenanceType is null or 1 or 2 ===
        if (is_null($maintenanceType) || in_array($maintenanceType, [1, 2])) {
            $calendarEvents = CalendarEvent::when(in_array($maintenanceType, [1, 2]), function ($query) use ($maintenanceType) {
                    $query->where('maintenance_type', $maintenanceType);
                })
                ->when($startAt, fn($query) => $query->where('start_at', '>=', $startAt))
                ->when($endAt, fn($query) => $query->where('end_at', '<=', $endAt))
                ->get();

            foreach ($calendarEvents as $event) {
                $startDate = \Carbon\Carbon::parse($event->start_at);
                $colorStatus = 0;

                if ($startDate->greaterThanOrEqualTo($now)) {
                    $colorStatus = $startDate->lessThanOrEqualTo($now->copy()->addDays(30)) ? 1 : 2;
                }

                $event->color_status = $colorStatus;

                $mergedEvents->push(new AllEventResource($event));
            }
        }

        // === 2. Get external events from proMaintenances if maintenanceType is null or 3 ===
        if (is_null($maintenanceType) || $maintenanceType == 3) {
            $externalEventsQuery = DB::connection('proMaintenances')
                ->table('events')
                ->join('maintenances', 'maintenances.guid', '=', 'events.maintenance_guid')
                ->select(
                    DB::raw('NULL as id'),
                    'events.guid as guid',
                    'events.maintenance_guid as maintenance_guid',
                    'events.title',
                    'events.description',
                    'events.start_date as start_at',
                    'events.end_date as end_at',
                    'events.all_day as is_all_day',
                    'events.type',
                    'maintenances.anagraphic_guid as client_guid',
                    'events.data_creazione as created_at',
                    'events.versione as updated_at'
                )
                ->where('events.type', 1)
                ->where('maintenances.status_guid', '66cb1c1b-693d-46a8-b1e7-4d925163467e');

            if ($startAt) {
                $externalEventsQuery->where('events.start_date', '>=', $startAt);
            }

            if ($endAt) {
                $externalEventsQuery->where('events.end_date', '<=', $endAt);
            }

            $externalEvents = $externalEventsQuery->get();

            foreach ($externalEvents as $event) {
                $startDate = \Carbon\Carbon::parse($event->start_at);
                $colorStatus = 0;

                if ($startDate->greaterThanOrEqualTo($now)) {
                    $colorStatus = $startDate->lessThanOrEqualTo($now->copy()->addDays(30)) ? 1 : 2;
                }

                $mergedEvents->push(new AllEventResource((object)[
                    'id' => $event->id ?? $event->guid,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start_at' => $event->start_at,
                    'end_at' => $event->end_at,
                    'maintenance_type' => $event->type ?? 3,
                    'maintenance_guid' => $event->maintenance_guid,
                    'color_status' => $colorStatus
                ]));
            }
        }

        return ApiResponse::success(AllEventResource::collection($mergedEvents));
    }



}
