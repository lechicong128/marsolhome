<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TransactionResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'date' => $this->date,
            'total' => $this->total,
            'total_promotion' => $this->total_promotion,
            'percent_discount' => $this->percent_discount,
            'vat' => $this->vat,
            'total_vat' => $this->total_vat,
            'total_discount' => $this->total_discount,
            'total_discount_leader' => $this->total_discount_leader,
            'total_discount_customer_f1' => $this->total_discount_customer_f1,
            'cost_delivery' => $this->cost_delivery,
            'discount_cost_delivery' => $this->discount_cost_delivery,
            'total_accumulate' => $this->total_accumulate,
            'level' => $this->level,
            'check_leader' => $this->check_leader,
            'point' => $this->point,
            'point_total' => $this->point_total,
            'grand_total' => $this->grand_total,
            'warehouse_status' => $this->warehouse_status,
            'info_delivery' => [
                'name_delivery' => $this->name_delivery,
                'phone_delivery' => $this->phone_delivery,
                'address_delivery' => $this->address_delivery,
            ],
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'fullname' => $this->customer->fullname,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                    'avatar_new' => !empty($this->customer->avatar) ? env('STORAGE_URL').'/'.$this->customer->avatar : null,
                ];
            }),
            'customer_leader' => $this->whenLoaded('customer_leader', function () {
                return [
                    'id' => $this->customer_leader->id,
                    'fullname' => $this->customer_leader->fullname,
                    'email' => $this->customer_leader->email,
                    'phone' => $this->customer_leader->phone,
                    'avatar_new' => !empty($this->customer_leader->avatar) ? env('STORAGE_URL').'/'.$this->customer_leader->avatar : null,
                ];
            }),
            'customer_f1' => $this->whenLoaded('customer_f1', function () {
                return [
                    'id' => $this->customer_f1->id,
                    'fullname' => $this->customer_f1->fullname,
                    'email' => $this->customer_f1->email,
                    'phone' => $this->customer_f1->phone,
                    'avatar_new' => !empty($this->customer_f1->avatar) ? env('STORAGE_URL').'/'.$this->customer_f1->avatar : null,
                ];
            }),
            'note' => $this->note,
            'status' => [
                'id' => $this->status,
                'name' => getValueStatusTransaction($this->status,'name'),
                'color' => getValueStatusTransaction($this->status,'color'),
                'background' => getValueStatusTransaction($this->status,'background'),
                'date_status' => $this->date_status,
                'note' => $this->note_status,
            ],
            'payment_mode' => [
                'id' => $this->payment_mode['id'],
                'name' => $this->payment_mode['name'],
                'image' => $this->payment_mode['image'],
            ],
            'items' => TransactionItemResources::collection($this->transaction_item),
            'is_review' => $this->is_review ?? 0,
            'info_payment' => $this->info_payment ?? false,
            'warning_payment' => $this->warning_payment ?? 0,
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
