<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SellPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        return array(
            'id'=>$this->id,
            'f_name'=>$this->F_name,
            'm_name'=>$this->M_name,
            'l_name'=>$this->L_name,
            'is_active'=>$this->sellPoint['is_active'],
            'max_amount'=>$this->sellPoint['max_amount'],
        );
    }
}
