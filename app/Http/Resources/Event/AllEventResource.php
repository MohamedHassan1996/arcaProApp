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

            $startDate = Carbon::parse($this->start_at);

            $statusColor = 0;
            if ($startDate->gt($now)) {
                $statusColor = $startDate->lte($nextMonth) ? 1 : 2;
            }


        return [
            'eventId' => (string)$this->id,
            'title' => $this->title,
            'description' => $this->description,
            'startAt' => $this->start_at? Carbon::parse($this->start_at)->format('Y-m-d H:i') : null,
            'endAt' => $this->end_at
                ? Carbon::parse($this->end_at)->addMinutes(2)->format('Y-m-d H:i')
                : Carbon::parse($this->start_at)->addMinutes(2)->format('Y-m-d H:i'),
            'maintenanceType' => $this->maintenance_type,
            'maintenanceGuid' => $this->maintenance_guid?? $this->title,
            'statusColor' => $statusColor
        ];
    }
}
