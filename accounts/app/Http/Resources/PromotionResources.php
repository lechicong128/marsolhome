<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $_locale = $request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $trans = $this->transalations->where('language',$_locale)->first();
        return [
            'id' => $this->id,
            'code' => !empty($trans) ? $trans['code'] : $this->code,
            'name' => !empty($trans) ? $trans['name'] : $this->name,
            'type' => $this->type,
            'percent' => $this->percent,
            'cash' => $this->cash,
            'money_max' => $this->money_max,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'indefinite' => $this->indefinite,
            'detail' => !empty($trans) ? $trans['detail'] : $this->detail,
            'note' => !empty($trans) ? $trans['note'] : $this->note,
            'machines_id' => $this->machines_id,
            'image' => !empty($this->image) ? asset('storage/'.$this->image) : null,
            'title_referral' => $this->title_referral,
            'content_referral' => $this->content_referral,
        ];
    }
}
