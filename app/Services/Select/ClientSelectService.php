<?php

namespace App\Services\Select;

use App\Models\Anagraphic;

class ClientSelectService
{
    public function getAllClients()
    {
        return Anagraphic::where('codice_interno', 1)->all(['guid as value', 'ragione_sociale as label']);
    }
}
