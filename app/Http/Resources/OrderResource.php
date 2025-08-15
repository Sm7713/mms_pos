<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return array(
            "id"=> $this->id,
            "date"=> $this->date,
            "total_price"=>$this->total_price,
            "is_executed"=>$this->is_executed,
            "type"=>$this->type,
            'sell_point_f_name'=>$this->sellPoint['user']['F_name'],
            'sell_point_l_name'=>$this->sellPoint['user']['L_name']
        );
    }
}
