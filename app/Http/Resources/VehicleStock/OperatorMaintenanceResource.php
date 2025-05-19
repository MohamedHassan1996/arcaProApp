<?php

namespace App\Http\Resources\Maintenance;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperatorMaintenanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //dd($this);
        return [
            'maintenanceGuid' => $this->guid,
            'status' => $this->status??"",
            'importance' => $this->importance??"",
            'codice' => $this->codice??"",
            'startDate' => Carbon::parse($this->start_date)->format('d/m/Y'),
            'companyArriveAt' => Carbon::parse($this->leave_hour)->format('H:i'),
            'clientArriveAt' => Carbon::parse($this->arrive_hour)->format('H:i'),
            'clientName' => $this->regione_sociale,
            'operatorNumber' => $this->person_numbers??0,
            'distance' => $this->distance > 0 ? $this->distance ." + ". $this->distance : "0 + 0",
            'capos' => $this->capos,
            'operators' => $this->operators,
            'address' => $this->address,
            'referenceName' => $this->cognome != '' && $this->nome != '' ? $this->cognome . ' ' . $this->nome : ($this->nome != '' ? $this->nome : $this->cognome),
            'referencePhone' => $this->referencePhone,
            'assistenzaClient' => $this->assistenza_client? 'SI' : 'NO',
            'vehicles' => $this->vehicles,
            'contactName' => $this->contractName,
            'fineLavoro' => $this->fine_lavoro,
            'details' => $this->details
         ];
    }
}
