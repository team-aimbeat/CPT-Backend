<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
            'id'            => $this->id,
            'age'           => $this->age,
            'height'        => $this->height,
            'height_unit'   => $this->height_unit,
            'weight'        => $this->weight,
            'weight_unit'   => $this->weight_unit,
            'address'       => $this->address,
            'user_id'       => $this->user_id,
            'bmi'           => $this->bmi,
            'bmr'           => $this->bmr,
            'ideal_weight'  => $this->ideal_weight,
            'goal'          => $this->goal,
            'workout_mode'  => $this->workout_mode,
            'workout_level'  => $this->workout_level,
            'workout_days'  => $this->workout_days,
            'workout_time'  => $this->workout_time,
            'equipment_ids'  => $this->equipment_ids,
            'has_injury'  => $this->has_injury,
            'injury_info'  => $this->injury_info,
            'injury_ids'  => $this->injury_ids,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
