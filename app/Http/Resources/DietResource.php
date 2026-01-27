<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DietResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user_id = auth()->id() ?? null;
        return [
            'id'               => $this->id,
            'title'            => $this->translation_title ?? $this->title,
            'variety'            => $this->variety,
            'diet_image'         => $this->diet_image,
            'diet_image_url'     => $this->diet_image_url,
            'is_featured'      => $this->is_featured,
            'status'           => $this->status,
            'ingredients'      => $this->translation_ingredients ?? $this->ingredients,
            'description'      => $this->translation_description ?? $this->description,
            'is_premium'       => $this->is_premium,
            'categorydiet_id'  => $this->categorydiet_id,
            'categorydiet_title'  => optional($this->categorydiet)->title,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
           
        ];
    }
}
