<?php

namespace App\Http\Resources;

use Aws\Emr\EmrClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ProductResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $_locale = $request->input('_locale') ?? 'vi';
        $variant_id = $request->variant_id;
        $arrCodeReview = $request->arrCodeReview ?? [0];
        $price = $this->price;
        $price_percent = 0;
        $price_display = null;
        $price_percent = null;
        if (!empty($this->check_affiliate)){
            $percent = get_option('percent_affiliate') ?? 0;
            $price_percent = ($price * $percent) / 100;
            $link_referral = get_option('short_link_referral');
            $link_referral = $link_referral.'?deep_link_value=product&deep_link_sub1='.$this->id.'&af_dp=nglow29://product/'.$this->id.'&af_force_deeplink=true&af_sub1='.$this->client['code_introduce'].'';

            $prices = $this->variant_option->pluck('pivot.price')->filter()->unique()->values();
            if (!empty($this->check_affiliate) && $prices->isNotEmpty()){
                $percent = get_option('percent_affiliate') ?? 0;
                if ($prices->count() === 1) {
                    $price_display = [$prices->first()];
                    $price_percent = [($prices->first() * $percent) / 100];
                } else {
                    $price_display = [$prices->min(), $prices->max()];
                    $price_percent = [($prices->min() * $percent) / 100, ($prices->max() * $percent) / 100];
                }
            }
        }
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'price' => $price,
            'price_display' => $price_display,
            'price_percent' => $price_percent,
            'link_share' => !empty($this->check_affiliate) ? $link_referral : null,
            'image' => !empty($this->image) ? config('services.storage.url').'/'. $this->image : null,
            'product_category' => $this->category->map(function ($item){
                return [
                    'id' => $item->id_category,
                    'name' => $item->category_detail->name
                ];
            }),
            'unit' =>  $this->unit ? [
                'id' => $this->unit->id,
                'name' => $this->unit->name
            ] : null,
            'review' => $this->client_reviews
                ->when(!empty($arrCodeReview), function ($q) use ($arrCodeReview) {
                    return $q->whereIn('code_review', $arrCodeReview);
                })
                ->map(function ($item) use ($_locale){
                    return [
                        'id' => $item->id,
                        'code_review' => $item->code_review,
                        'customer_id' => $item->id_client,
                    ];
                })
                ->values(),
            'variant_option' => $this->variant_option
                ->when(!empty($variant_id), function ($q) use ($variant_id) {
                    return $q->where('id', $variant_id);
                })
                ->map(function ($item) use ($_locale){
                    $tran = $item->transalations->where('language',$_locale)->first();
                    $tranCate = $item->variant->transalations->where('language',$_locale)->first();
                    return [
                        'id' => $item->id,
                        'name' => !empty($tran) ? $tran->name : $item->name,
                        'price' => $item->pivot->price,
                        'category' => [
                            'id' => $item->variant->id,
                            'name' => !empty($tranCate) ? $tranCate->name : $item->variant->name,
                        ]
                    ];
                })
                ->values(),
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
