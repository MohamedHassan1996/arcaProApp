<?php

namespace App\Services\Select\Charity;

use App\Enums\Charity\CharityStatus;
use App\Models\Charity\Charity;

class CharitySelectService{

    public function getAllCharities(){

        $charities = Charity::where('is_active', CharityStatus::ACTIVE)->get(['id as value', 'name as label']);

        return $charities;

    }
}
