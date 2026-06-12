<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class HomeResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $baseUrl = config('services.storage.url');
        
        $provinceName = null;
        $wardName = null;
        if ($this->ward_id) {
            $ward = DB::table('tbl_wards_new')->where('id', $this->ward_id)->first();
            $wardName = $ward ? $ward->name : null;
            $prov = DB::table('tbl_provinces')->where('id', $this->province_id)->first();
            $provinceName = $prov ? $prov->name : null;
        }


        $videoUrl = null;
        if (!empty($this->video_url)) {
            if (!str_starts_with($this->video_url, 'http') && !str_starts_with($this->video_url, '/')) {
                $videoUrl = $baseUrl . '/' . $this->video_url;
            } else {
                $videoUrl = $this->video_url;
            }
        }
        $customer_id = $request->client->id ?? 0;
        $type_client = $this->customer_login['type_client'] ?? 0;
        if(empty($this->price)){
            $profit = 0;
        } else {
            $profit = (formatMoney((($this->currently_rent * 12)/$this->price) * 100));
        }
        $status = $this->status;
        if(!empty($this->end_date) && $this->end_date < date('Y-m-d')){
            $status = 6;
        }
        return [
            'id' => $this->id,
            'code' => $this->code ?? ('BĐS-' . $this->id),
            'type' => $this->type,
            'property_type_id' => $this->property_type,
            'property_type' => $this->propertyType->name ?? null,
            'property_type_icon' => !empty($this->propertyType->image) ? $baseUrl . '/' . $this->propertyType->image : null,
            'title' => $this->title,
            'detail' => $this->detail,
            'description' => $this->description,
            'address' => [
                'province_id' => $this->province_id,
                'province' => $provinceName,
                'ward_id' => $this->ward_id,
                'ward' => $wardName,
                'street' => $this->address,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'name_location' => $this->name_location,
                'distance' => $this->distance
            ],
            'price' => $this->price,
            'price_m2' => $this->price_m2,
            'loanability' => $this->loanability,
            'currently_rent' => $this->currently_rent,
            'profit' => $this->type == 1 ? $profit : 0,
            'area' => $this->area,
            'status' => getListStatusHome($status),
            'video_url' => $videoUrl,
            'is_featured' => $this->is_featured,
            'is_hot' => $this->is_featured,
            'is_new' => $this->is_new,
            'is_vip' => $this->is_vip,
            'total_review' => $this->reviews_count ?? $this->reviews()->count(),
            'avg_star' => empty($this->reviews()->count()) ? 5 : $this->reviews()->avg('star'),
            'commission_rate' => $this->commission_rate,
            'step' => $this->step,
            'legal_status' => $this->legal_status,
            'plot_land' => $this->plot_land,
            'number_sheets' => $this->number_sheets,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'customer' => $this->customer ? [
                'id' => is_array($this->customer) ? ($this->customer['id'] ?? null) : ($this->customer->id ?? null),
                'name' => is_array($this->customer) ? ($this->customer['fullname'] ?? $this->customer['name'] ?? null) : ($this->customer->fullname ?? $this->customer->name ?? null),
                'email' => is_array($this->customer) ? ($this->customer['email'] ?? null) : ($this->customer->email ?? null),
                'phone' => $type_client == 2 ? (is_array($this->customer) ? ($this->customer['phone'] ?? null) : ($this->customer->phone ?? null)) : get_option('contact_phone'),
                'avatar' => $this->customer['avatar'],
                'type_client' => $this->customer['type_client'],
                'experience' => $this->customer['experience'] ?? null,
                'total_transactions' => $this->customer['total_transactions'] ?? null,
                'total_homes' => $this->customer['total_homes'] ?? null,
            ] : null,
            'media_items' => $this->media_items ? $this->media_items->map(function ($item) use ($baseUrl) {
                return [
                    'id' => $item->id,
                    'url' => !empty($item->url) && !str_starts_with($item->url, 'http') && !str_starts_with($item->url, '/')
                        ? $baseUrl . '/' . $item->url
                        : $item->url,
                    'url_db' => $item->url,
                    'caption' => $item->caption,
                    'sort_order' => $item->sort_order,
                ];
            }) : [],
            'documents_red' => $this->documents_red ? $this->documents_red->map(function ($item) use ($baseUrl) {
                return [
                    'id' => $item->id,
                    'url' => !empty($item->url) && !str_starts_with($item->url, 'http') && !str_starts_with($item->url, '/')
                        ? $baseUrl . '/' . $item->url
                        : $item->url,
                    'type' => $item->type,
                    'url_db' => $item->url,
                    'sort_order' => $item->sort_order,
                ];
            }) : [],
            'documents_other' => $this->documents_other ? $this->documents_other->map(function ($item) use ($baseUrl) {
                return [
                    'id' => $item->id,
                    'url' => !empty($item->url) && !str_starts_with($item->url, 'http') && !str_starts_with($item->url, '/')
                        ? $baseUrl . '/' . $item->url
                        : $item->url,
                    'type' => $item->type,
                    'url_db' => $item->url,
                    'sort_order' => $item->sort_order,
                ];
            }) : [],
            'interior_amenities' => $this->interior_amenities ? $this->interior_amenities->map(function ($item) use ($baseUrl) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'icon' => $item->icon ? $baseUrl . '/' . $item->icon : null,    
                ];
            }) : [],
            'utilities' => $this->utilities ? $this->utilities->map(function ($item) use ($baseUrl) {
                $value = $item->pivot->value ?? null;
                $value_text = $value;
                if ($item->input_type === 'select' && !empty($value)) {
                    $option = $item->options->where('id', $value)->first();
                    if ($option) {
                        $value_text = $option->name;
                    }
                }
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'icon' => $item->icon ? $baseUrl . '/' . $item->icon : null,
                    'input_type' => $item->input_type,
                    'unit' => $item->unit,
                    'value' => $value,
                    'value_text' => $value_text,
                    'show_list' => $item->show_list,
                ];
            }) : [],
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'favourite' => $this->whenLoaded('favourite', function () use ($customer_id) {
                return $this->favourite->contains('customer_id', $customer_id);
            }),
            'count_favourite' => $this->whenLoaded('favourite', function () {
                return $this->favourite->count();
            }),
            'is_saved' => $this->whenLoaded('save_home', function () use ($customer_id) {
                return $this->save_home->contains('customer_id', $customer_id);
            }),
            'count_save' => $this->whenLoaded('save_home', function () {
                return $this->save_home->count();
            }),
        ];
    }
}
