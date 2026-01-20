<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource  extends JsonResource
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
            'id'                  => $this->id,
            'package_type'        => $this->package_type,
            'name'                => $this->name,
            'duration'            => $this->duration,
            'duration_unit'       => $this->duration_unit,
            'price'               => $this->price,
            'description'         => $this->description,
            'status'              => $this->status,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}