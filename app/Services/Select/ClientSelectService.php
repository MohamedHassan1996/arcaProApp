<?php

namespace App\Services\Select;

use App\Models\Anagraphic;

class ClientSelectService
{
    public function getAllClients()
    {
        return Anagraphic::where('fk_tanagrafica', 1)->get(['guid as value', 'regione_sociale as label']);
    }
}
