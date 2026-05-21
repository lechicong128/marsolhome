<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ChallengeMeResources extends JsonResource
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
            'date_challenge' => $this->date_challenge,
            'completion_rate' => $this->completion_rate,
            'deposit' => $this->deposit,
            'haru_xu' => $this->haru_xu,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'fullname' => $this->customer->fullname,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                    'avatar_new' => !empty($this->customer->avatar) ? $this->customer->avatar : null,
                ];
            }),
            'challenge' => $this->whenLoaded('challenge', function () {
                return [
                    'id' => $this->challenge->id,
                    'name' => $this->challenge->name,
                    'days' => $this->challenge->days,
                ];
            }),
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