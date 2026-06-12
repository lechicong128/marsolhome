<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $baseUrl = config('services.storage.url') ?? env('STORAGE_URL');

        // Calculate experience based on registration date (created_at)
        $experience = 'Mới tham gia';
        if ($this->created_at) {
            $diff = Carbon::parse($this->created_at)->diff(now());
            if ($diff->y > 0) {
                $experience = $diff->y . ' năm' . ($diff->m > 0 ? ' ' . $diff->m . ' tháng' : '');
            } else {
                $experience = '< 1 năm';
            }
        }

        $totalHomes = $this->total_homes;

        $totalTransactions = $this->total_transactions;

        return [
            'id' => $this->id,
            'name' => $this->fullname,
            'fullname' => $this->fullname,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => !empty($this->avatar)
                ? (str_starts_with($this->avatar, 'http') ? $this->avatar : $baseUrl . '/' . $this->avatar)
                : null,
            'type_client' => $this->type_client,
            'active' => $this->active,
            'total_homes' => $totalHomes,
            'experience' => $experience,
            'total_transactions' => $totalTransactions,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
