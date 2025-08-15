<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayemntResource extends JsonResource
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
            "id"=>$this->id,
            "date"=> $this->date,
            "amount"=> $this->amount,
            "notice"=> $this->notice,
            "confirm"=> $this->confirm,
            "method"=> $this->method,
            "exchange_co"=> $this->exchange_co,
            "transfer_no"=> $this->transfer_no
        );
    }
}
