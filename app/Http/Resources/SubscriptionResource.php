<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource  extends JsonResource
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
            'user_id'             => $this->user_id,
            'user_name'           => optional($this->user)->display_name,
            'package_id'          => $this->package_id,
            'package_name'        => optional($this->package)->name,
            'total_amount'        => $this->total_amount,
            'payment_type'        => $this->payment_type,
            'txn_id'              => $this->txn_id,
            'transaction_detail'  => $this->transaction_detail,
            'payment_status'      => $this->payment_status,
            'status'            => $this->status,
            'package_data'      => $this->package_data,
            'subscription_start_date'  => $this->subscription_start_date,
            'subscription_end_date'    => $this->subscription_end_date,
            'gateway_subscription_id'  => $this->gateway_subscription_id,
            'autopay_status'           => $this->autopay_status,
            'trial_start_at'           => $this->trial_start_at,
            'trial_ends_at'            => $this->trial_ends_at,
            'billing_starts_at'        => $this->billing_starts_at,
            'mandate_authorized_at'    => $this->mandate_authorized_at,
            'trial_remaining_days'     => $this->trial_ends_at && now()->lt($this->trial_ends_at) ? max(1, (int) ceil(now()->floatDiffInDays($this->trial_ends_at, false))) : 0,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
