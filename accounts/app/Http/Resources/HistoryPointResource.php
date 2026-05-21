<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class HistoryPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $title = '';
        if ($this->object_type == 'transaction'){
            $title = lang('dt_refferal');
        } elseif ($this->object_type == 'affiliate_product'){
            $title = lang('dt_affiliate');
        } elseif ($this->object_type == 'transaction_point'){
            $title = lang('dt_use_haru_xu');
        } elseif ($this->object_type == 'transaction_point_refund'){
            $title = lang('dt_refund_haru_xu');
        }
        elseif ($this->object_type == 'review_items'){
            $title = lang('dt_review_items');
        }
        elseif ($this->object_type == 'authenticated_challengeMe'){
            $title = lang('ch_authenticated_challengeMe');
        }
        $storageUrl = config('services.storage.url');
        return [
            'id' => $this->id,
            'title' => $title,
            'customer' => [
                'id' => $this->customer->id,
                'fullname' => $this->customer->fullname,
                'avatar' => !empty($this->customer->avatar) ? $storageUrl.'/'.$this->customer->avatar : null,
            ],
            'type_check' => $this->type_check,
            'created_at' => $this->created_at,
            'point' => $this->point,
            'exchange_point' => $this->exchange_point,
        ];
    }

    public function with($request)
    {
        return [
            'base' => [
                'base' => asset('storage'),
            ]
        ];
    }
}
