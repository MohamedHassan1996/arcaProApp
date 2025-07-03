<?php

namespace App\Http\Resources\Event;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $now = now();
        $nextMonth = $now->copy()->addMonth();
        $startDate = Carbon::parse($this['start_at'] ?? $this['start_date']);

        $statusColor = 0;
        if ($startDate->gt($now)) {
            $statusColor = $startDate->lte($nextMonth) ? 1 : 2;
        }


        $startAt = $this['start_at'] ?? $this['start_date'];
        $endAt = $this['end_at'] ?? $this['end_date'];

        return [
            'eventId' => isset($this['id']) ? (string)$this['id'] : $this['guid'],
            'title' => $this['title'],
            'description' => $this['description'],
            'startAt' => $startAt? Carbon::parse($startAt)->format('Y-m-d H:i') : null,
            'endAt' => $endAt
                ? Carbon::parse($endAt)->addMinutes(2)->format('Y-m-d H:i')
                : Carbon::parse($startAt)->addMinutes(2)->format('Y-m-d H:i'),
            'maintenanceType' => $this['maintenance_type'] ?? 3,
            'maintenanceGuid' => $this['guid'] ?? $this['title'],
            'statusColor' => $statusColor
        ];
    }
}
