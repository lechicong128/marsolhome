<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewHomeResource extends JsonResource
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
            'home_id' => $this->home_id,
            'customer_id' => $this->customer_id,
            'customer' => $this->customer ? [
                'id' => is_array($this->customer) ? ($this->customer['id'] ?? null) : ($this->customer->id ?? null),
                'name' => is_array($this->customer) ? ($this->customer['fullname'] ?? $this->customer['name'] ?? null) : ($this->customer->fullname ?? $this->customer->name ?? null),
                'email' => is_array($this->customer) ? ($this->customer['email'] ?? null) : ($this->customer->email ?? null),
                'phone' => is_array($this->customer) ? ($this->customer['phone'] ?? null) : ($this->customer->phone ?? null),
                'avatar' => $this->customer['avatar'],
                'type_client' => $this->customer['type_client'],
            ] : null,
            'poster_id' => $this->poster_id,
            'star' => $this->star,
            'content' => $this->content,
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
