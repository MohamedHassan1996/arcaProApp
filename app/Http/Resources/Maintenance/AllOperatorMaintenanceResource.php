<?php

namespace App\Http\Resources\Maintenance;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllOperatorMaintenanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'maintenanceGuid' => $this->guid,
            'status' => $this->status->parameter_value??"",
            'importance' => $this->importance->parameter_value??"",
            'codice' => $this->codice??"",
            'startDate' => Carbon::parse($this->start_date)->format('d/m/Y'),
            'leaveHour' => Carbon::parse($this->leave_hour)->format('H:i'),
            'clientName' => $this->anagraphic->regione_sociale,
        ];
    }
}
