<?php

namespace App\Http\Resources\VehicleStock;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllVehicleStockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'vehicleStockGuid' => $this->guid,
            'codice' => $this->codice??"",
            'description' => $this->descrizione??"",
            'quantity' => $this->quantita??0
        ];
    }
}
