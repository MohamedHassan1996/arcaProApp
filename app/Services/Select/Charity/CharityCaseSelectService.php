<?php

namespace App\Services\Select\Charity;

use App\Enums\Charity\CharityStatus;
use App\Models\CharityCase\CharityCase;

class CharityCaseSelectService{

    public function getAllCharityCases(){

        $charities = CharityCase::all(['id as value', 'name as label']);

        return $charities;

    }
}
