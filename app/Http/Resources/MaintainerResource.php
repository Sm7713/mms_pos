<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaintainerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'id'=>$this->id,
            'f_name'=>$this->F_name,
            'm_name'=>$this->M_name,
            'l_name'=>$this->L_name,
            'is_active'=>$this->Maintainer['is_active'],
            // 'connection_type'=>$this->Subscriber['connection_type'],
        ];
    }
}
