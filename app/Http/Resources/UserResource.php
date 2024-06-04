<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->uuid, 
            'name' => $this->name, 
            'email' => $this->email, 
            'mobile' => $this->mobile, 
            'isActive' =>(boolean) $this->isActive, 
            'created_at' => $this->created_at->format('d-m-Y h:i:s'),   
            'updated_at' => $this->updated_at->format('d-m-Y h:i:s'),   

        ];
    }
}
