<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $connection = 'proMaintenances';

    public function getFullNameAttribute()
    {
        if($this->firstname && $this->lastname) {
            return $this->firstname . ' ' . $this->lastname;
        }elseif($this->first_name) {
            return $this->firstname;
        }elseif($this->lastname) {
            return $this->lastname;
        }
    }
}
