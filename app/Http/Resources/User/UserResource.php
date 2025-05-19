<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'userId' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->phone??"",
            'address' => $this->address??"",
            'avatar' => $this->avatar,
            'email' => $this->email??"",
            'roleId' => $this->roles->first()->id,
            'isActive' => $this->is_active,
        ];
    }
}
