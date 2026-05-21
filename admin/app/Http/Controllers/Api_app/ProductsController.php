<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ListBank as ListBankResource;
use App\Http\Resources\CompanyCarResource;
use App\Http\Resources\ProductResources;
use App\Http\Resources\TypeCarResource;
use app\Models\CategoryProducts;
use App\Models\Products;
use App\Models\ProductsFilter;
use App\Models\ProductsVariant;
use App\Models\ProductTranslations;
use App\Models\ClientsReview;
use App\Models\ReviewFile;
use App\Models\Variant;
use App\Models\VariantOptions;
use App\Models\HistorySearch;
use App\Services\AccountService;
use App\Traits\UploadFile;
use Google\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Traits\NotificationTrait;

class ProductsController extends AuthController
{
    use UploadFile, NotificationTrait;
    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
        $this->dbAccount = $accountService;
        $this->baseUrl = config('services.storage.url');

    }

    public function getList()
    {

        $_locale = $this->request->input('_locale');// ngôn ngữ
        $NotIsSig = $this->request->input('NotIsSig');// lấy nhưng sản phẩm mà chưa đăng ký
        $_id = $this->request->input('id');// không lấy sản phẩm có id này
        $review_top = $this->request->input('review_top');// lâấy những sản phẩm đánh giá cao
        $filter_contribute = $this->request->input('is_contribute');// lâấy những sản phẩm đánh giá cao
        $id_client = $this->request->client->id ?? 0;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $tag_product_filter = $this->request->input('tag_product_filter');
        if(!empty($tag_product_filter)) {
            if(!is_array($tag_product_filter)) {
                $tag_product_filter = explode(',', $tag_product_filter);
            }
        }
        else {
            $tag_product_filter = [];
        }



        $Products = Products::from('tbl_products as p')
            ->select('p.id',
                'p.code',
                'p.is_use',
                'p.color_header',
                'p.background_color',
                'p.limit_people',
                'p.count_join',
                'p.average_star',
                'p.quantity_reviews',
                'p.contribute',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'),
                'p.slug',
                'p.sold',
                'p.date_end_promotion')
            ->selectRaw("
                CONCAT(
                  FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0)/86400), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),86400)/3600), 2, '0'), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),3600)/60),  2, '0'), ':',
                  LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),60),             2, '0')
                ) as time_left_dd_hh_mm_ss
            ")
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->where(function ($query) use ($_id) {
                $query->where('p.id', '!=', $_id);
            })
            ->where('p.is_use', '=', 1)
            ->when(true, function($q) use ($tag_product_filter) {
                if(!empty($tag_product_filter)) {
                    $q->whereRaw('(
                            SELECT 1
                            FROM tbl_tag_products_filter
                            WHERE tbl_tag_products_filter.id_product = p.id
                            AND tbl_tag_products_filter.id_product_filter IN ('.implode(',', $tag_product_filter).')
                        LIMIT 1)');
                }
            })
            ->when(true, function ($q) use ($id_client, $NotIsSig) {
                if(!empty($id_client)) {
                    if(empty($NotIsSig)) {
                        $q->addSelect(
                            DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.video_review) as video_review'),
                            DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.video_review_render) as video_review_render'),
                            DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.small_image_video_review) as small_image_video_review'),
                            'is_review as isSig',
                            'evaluate',
                            'tbl_clients_sign_up_review.id_review',
                        )->leftJoin('tbl_clients_sign_up_review', function ($join) use ($id_client) {
                                $join->on('tbl_clients_sign_up_review.id_product', '=', 'p.id')->where(
                                        'tbl_clients_sign_up_review.id_client', '=', $id_client
                                    );
                            });
                    }
                    else {
                        $q->whereRaw('NOT EXISTS(
                            SELECT 1
                            FROM tbl_clients_sign_up_review
                            WHERE tbl_clients_sign_up_review.id_product = p.id
                            AND tbl_clients_sign_up_review.id_client = '.$id_client.'
                        )');
                    }
                }
                else {
                    $q->addSelect(
                        DB::raw("null as isSig")
                    );
                }
            })
            ->when(true, function ($q) use ($filter_contribute) {
                if(!empty($filter_contribute)) {
                    $q->where('contribute', '>', 0);
                }
            });
            if (!empty($review_top)) {
                $Products->orderBy('p.average_star', 'desc')
                    ->orderBy('p.quantity_reviews', 'desc');
            }
            if (!empty($id_client)) {
                $listCategoryId = getCategoryNoLimit($id_client);
                $Products->addSelect(DB::raw("
                    IF(
                        EXISTS (
                            SELECT 1
                            FROM tbl_product_category AS c
                            WHERE c.id_product = p.id
                              AND c.id_category IN (".implode(',', $listCategoryId).")
                        ), 0, 1
                    ) AS isLimit
                "));
            }
            else {
                $Products->addSelect(DB::raw("0 as isLimit"));
            }
        $Products = $Products->paginate($per_page, ['*'], 'page', $current_page);
        return response()->json($Products);
    }

    //lấy danh sách sản phẩm dùng thử - dùng cho APP
    public function getListIsUse()
    {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        $NotIsSig = $this->request->input('NotIsSig');// lấy nhưng sản phẩm mà chưa đăng ký
        $_id = $this->request->input('id');// không lấy sản phẩm có id này
        $review_top = $this->request->input('review_top');// lâấy những sản phẩm đánh giá cao
        $filter_contribute = $this->request->input('is_contribute');// lâấy những sản phẩm đánh giá cao
        $id_client = $this->request->client->id ?? 0;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $tag_product_filter = $this->request->input('tag_product_filter');
        if(!empty($tag_product_filter)) {
            if(!is_array($tag_product_filter)) {
                $tag_product_filter = explode(',', $tag_product_filter);
            }
        }
        else {
            $tag_product_filter = [];
        }
        $Products = Products::from('tbl_products as p')
            ->select('p.id',
                'p.code',
                'p.is_use',
                'p.color_header',
                'p.background_color',
                'p.limit_people',
                'p.count_join',
                'p.average_star',
                'p.quantity_reviews',
                'p.contribute',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'),
                'p.slug',
                'p.sold',
                'p.date_end_promotion')
            ->selectRaw("
                CONCAT(
                  FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0)/86400), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),86400)/3600), 2, '0'), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),3600)/60),  2, '0'), ':',
                  LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),60),             2, '0')
                ) as time_left_dd_hh_mm_ss
            ")
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->where(function ($query) use ($_id) {
                $query->where('p.id', '!=', $_id);
            })
            ->where('p.is_use', '=', 1)
            ->when(true, function($q) use ($tag_product_filter) {
                if(!empty($tag_product_filter)) {
                    $q->whereRaw('(
                            SELECT 1
                            FROM tbl_tag_products_filter
                            WHERE tbl_tag_products_filter.id_product = p.id
                            AND tbl_tag_products_filter.id_product_filter IN ('.implode(',', $tag_product_filter).')
                        LIMIT 1)');
                }
            })
            ->when(true, function ($q) use ($id_client, $NotIsSig) {
                if(!empty($id_client)) {
                    if(empty($NotIsSig)) {
                        $q->addSelect(
                            DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.video_review) as video_review'),
                            DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.video_review_render) as video_review_render'),
                            DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.small_image_video_review) as small_image_video_review'),
                            'is_review as isSig',
                            'evaluate',
                            'tbl_clients_sign_up_review.id_review',
                        )->leftJoin('tbl_clients_sign_up_review', function ($join) use ($id_client) {
                            $join->on('tbl_clients_sign_up_review.id_product', '=', 'p.id')->where(
                                'tbl_clients_sign_up_review.id_client', '=', $id_client
                            );
                        });
                    }
                    else {
                        $q->whereRaw('NOT EXISTS(
                            SELECT 1
                            FROM tbl_clients_sign_up_review
                            WHERE tbl_clients_sign_up_review.id_product = p.id
                            AND tbl_clients_sign_up_review.id_client = '.$id_client.'
                        )');
                    }
                }
                else {
                    $q->addSelect(
                        DB::raw("null as isSig")
                    );
                }
            })
            ->when(true, function ($q) use ($filter_contribute) {
                if(!empty($filter_contribute)) {
                    $q->where('contribute', '>', 0);
                }
            });
        if (!empty($review_top)) {
            $Products->orderBy('(p.limit_people - p.count_join)', 'desc')
                ->orderBy('p.quantity_reviews', 'desc');
        }
        if (!empty($id_client)) {
            $listCategoryId = getCategoryNoLimit($id_client);
            $Products->addSelect(DB::raw("
                    IF(
                        EXISTS (
                            SELECT 1
                            FROM tbl_product_category AS c
                            WHERE c.id_product = p.id
                              AND c.id_category IN (".implode(',', $listCategoryId).")
                        ), 0, 1
                    ) AS isLimit
                "));
        }
        else {
            $Products->addSelect(DB::raw("0 as isLimit"));
        }
        $Products = $Products->paginate($per_page, ['*'], 'page', $current_page);
        return response()->json($Products);
    }

    //lấy danh sách sản phẩm dùng thử kết hợp với nhưng video review
    public function getListProductAndVideo()
    {
        $search = $this->request->input('search');
        $_locale = $this->request->input('_locale');// ngôn ngữ
        $_id = $this->request->input('id');// không lấy sản phẩm có id này
        $review_top = $this->request->input('review_top');// lâấy những sản phẩm đánh giá cao
        $check_hot = $this->request->input('check_hot') ?? 0;// sản phẩm hot
        $id_client = $this->request->client->id ?? 0;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }elseif ($this->request->query('page')) {
            $current_page = $this->request->query('page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $tag_product_filter = $this->request->input('tag_product_filter');
        if(!empty($tag_product_filter)) {
            if(!is_array($tag_product_filter)) {
                $tag_product_filter = explode(',', $tag_product_filter);
            }
        }
        else {
            $tag_product_filter = [];
        }

        $video_subquery_url = "
        (
            SELECT JSON_OBJECT(
                'video', CONCAT('".$this->baseUrlAdmin."/', video_review_render),
                'thumb', CONCAT('".$this->baseUrlAdmin."/', small_image_video_review),
                'id_client', id_client
            )
            FROM tbl_clients_sign_up_review
            WHERE id_product = p.id AND video_review_render IS NOT NULL
            ORDER BY RAND()
            LIMIT 1
        )";
        $image_url = "JSON_OBJECT('image', CONCAT('".$this->baseUrlAdmin."/', p.image))";
        $rand_condition = 'RAND() <= 0.3';
        $Products = Products::from('tbl_products as p')->with(['tag.transalations'])->with(['variant_option'])
            ->select('p.id',
                'p.code',
                'p.is_use',
                'p.color_header',
                'p.background_color',
                'p.limit_people',
                'p.count_join',
                'p.average_star',
                'p.quantity_reviews',
                'p.contribute',
                'pt.name',
                'pt.content',
                'p.id_variant',
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'),
                'p.slug',
                'p.sold',
                'p.date_end_promotion',
                'p.price',
                'p.price_min',
                'p.price_max',
                'p.check_free_ship',

                DB::raw("@video_candidate := IF($rand_condition, $video_subquery_url, NULL) AS video_candidate"),
                DB::raw("COALESCE(@video_candidate, $image_url) AS media_url"),
                DB::raw("IF(@video_candidate IS NOT NULL, 1, 0) AS is_video"),
            )
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->where(function ($query) use ($_id) {
                $query->where('p.id', '!=', $_id);
            })
            ->when(true, function($q) use ($tag_product_filter) {
                if(!empty($tag_product_filter)) {
                    $q->whereRaw('(
                            SELECT 1
                            FROM tbl_tag_products_filter
                            WHERE tbl_tag_products_filter.id_product = p.id
                            AND tbl_tag_products_filter.id_product_filter IN ('.implode(',', $tag_product_filter).')
                        LIMIT 1)');
                }
            });
        if (!empty($check_hot)){
            $Products->where('is_hot', 1);
        }
        if(!empty($search)) {
            $Products->where(function ($query) use ($search) {
                $query->where('pt.name', 'like', "%$search%");
//                $query->orWhere('pt.content', 'like', "%$search%");
                $query->orWhere('p.code', 'like', "%$search%");
            });
        }
        if (!empty($review_top)) {
            $Products->orderBy('is_hot', 'desc')
                ->orderBy('p.quantity_reviews', 'desc');
        }

        $Products = $Products->paginate($per_page, ['*'], 'page', $current_page);

        $idsOnPage = [];
        $Products->getCollection()->transform(function ($product) use ($_locale, &$idsOnPage) {

            if(!empty($product->id_variant)){
                $product->price = 0;
            }

            if(!empty($product->media_url)) {
                $objectClient = json_decode($product->media_url);
                if(!empty($objectClient->id_client)) {
                    $idsOnPage[] = $objectClient->id_client ?? 0;
                    $product->client = $objectClient->id_client;
                }
            }

            $product->tag_product = ($product->tag ?? collect([]))->map(function ($item) use ($_locale) {
                $itemNew = $item->transalations->where('language',$_locale)->first();
                return [
                    'id' => $item->id,
                    'name' => $itemNew->name,
                    'color' => $item->color,
                    'background' => $item->background
                ];

            });
            unset($product->tag);
            return $product;
        });



        $dataClient = [];
        if (!empty($idsOnPage)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['list_id' => $idsOnPage]);
            unset($newRequest['search']); // tránh ảnh hưởng bởi search chung
            $responseClient = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClient = $responseClient->getData(true);
        }
        if(!empty($dataClient)) {
            $Products->getCollection()->transform(function ($product) use ($_locale, $dataClient) {
                $client = $dataClient['clients'][$product->client] ?? [];
                if (!empty($client)) {
                    $product->data_client = [
                        'fullname' => $client['fullname'] ?? '',
                        'avatar' => $client['avatar'] ?? '',
                        'phone' => $client['phone'] ?? '',
                    ];
                }
                return $product;
            });

        }

        return response()->json($Products);
    }

    public function getDetail($slug = '')
    {
        //$slug có thể là id hoặc đường dẩn
        $_locale = $this->request->input('_locale');
        $id_client = $this->request->client->id ?? 0;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $products = Products::from('tbl_products as p')
//            ->where('p.slug', $slug)
            ->where(function ($query) use ($slug) {
                $query->where('p.id', '=', $slug);
                $query->OrWhere('p.slug', $slug);
            })
            ->select('p.id', 'p.code', 'p.is_use', 'p.color_header', 'p.background_color', 'p.limit_people', 'p.count_join', 'p.average_star', 'p.quantity_reviews', 'p.contribute',
                'pt.name', 'pt.content','p.price','p.id_variant','p.price_min',
                'p.price_max', DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'), 'p.slug', 'p.sold', 'p.date_end_promotion')
            ->selectRaw("
                CONCAT(
                  FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0)/86400), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),86400)/3600), 2, '0'), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),3600)/60),  2, '0'), ':',
                  LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),60),             2, '0')
                ) as time_left_dd_hh_mm_ss
              ")
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->when(true, function ($q) use ($id_client) {
                if(!empty($id_client)) {
                    $q->addSelect(
                        DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review) as video_review'),
                        DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review_render) as video_review_render'),
                        DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.small_image_video_review) as small_image_video_review'),
                        'is_review as isSig', 'evaluate', 'tbl_clients_sign_up_review.id_review')
                        ->leftJoin('tbl_clients_sign_up_review', function ($join) use ($id_client) {
                            $join->on('tbl_clients_sign_up_review.id_product', '=', 'p.id')
                                ->where('tbl_clients_sign_up_review.id_client', '=', $id_client);
                        });
                }
                else {
                    $q->addSelect(
                        DB::raw("null as isSig")
                    );
                }
            });

        if (!empty($id_client)) {
            $listCategoryId = getCategoryNoLimit($id_client);
            $products->addSelect(DB::raw("
            IF(
                EXISTS (
                    SELECT 1
                    FROM tbl_product_category AS c
                    WHERE c.id_product = p.id
                      AND c.id_category IN (".implode(',', $listCategoryId).")
                ), 0, 1
            ) AS isLimit
        "));
        }
        else {
            $products->addSelect(DB::raw("0 as isLimit"));
        }

        $products = $products->first();

        if(!empty($products->id)) {
            $list_images = DB::table('tbl_products_images')->where('id_product', $products->id)
                ->orderBy('order_by', 'asc')
                ->orderBy('id', 'desc')->get();
            $dataImg = [];
            if(!empty($list_images)) {
                foreach($list_images as $key => $image) {
                    if(!empty($image->image)) {
                        $dataImg[] = $this->baseUrlAdmin.'/'.$image->image;
                    }
                }
            }
            $products->list_images = $dataImg ?? [];

            $ingredients = DB::table('tbl_product_ingredients')
                ->where('id_product', $products->id)
                ->where('language', $_locale)
//                ->where(function ($query) {
//                    $query->where('tbl_product_ingredients.name', '!=', "");
//                })
                ->orderBy('key_index', 'asc')
                ->get();
            $products->ingredients = $ingredients;

            $variant = Variant::select('tbl_variant.id', 'tvt.name as name')
                ->where('tbl_variant.id', $products->id_variant)
                ->where('tbl_variant.active', 1)
                ->LeftJoin('tbl_variant_translations as tvt', function($join) use ($_locale) {
                    $join->on('tvt.id_variant', '=', 'tbl_variant.id')
                        ->where('tvt.language', $_locale);
                })
                ->first();
            if(!empty($products->id_variant) && !empty($variant->id)) {
                $variantProduct = ProductsVariant::select('tbl_variant_options.id', 'vot.name as name', 'tbl_products_variant.price as price')
                    ->LeftJoin('tbl_variant_options', 'tbl_variant_options.id', '=', 'tbl_products_variant.id_variant_options')
                    ->LeftJoin('tbl_variant_options_translations as vot', function($join) use ($_locale) {
                        $join->on('vot.id_variant_options', '=', 'tbl_variant_options.id')
                            ->where('vot.language', $_locale);
                    })
                    ->where('id_product', $products->id)
                    ->where('tbl_variant_options.active', 1)
                    ->get();
                $variant->data_options = $variantProduct;
            }

            $products->info_variant = $variant;


            $list_review_product = DB::table('tbl_clients_sign_up_review')
                ->select(
                    'tbl_clients_sign_up_review.id',
                    DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review) as video_review'),
                    DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review_render) as video_review_render'),
                    DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.small_image_video_review) as small_image_video_review'),
                    'is_review as isSig',
                    'evaluate',
                    'content_evaluate',
                    'view_see',
                    'date_review',
                    'id_client'
                )
                ->where('id_product', $products->id)
                ->where('is_review', 1)
//                ->where(DB::raw('video_review IS NOT NULL'))
                ->orderBy('date_review', 'desc')
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get();
            $dataVideo = [];
            $dataThumbalVideo = [];
            $dataClientReview = [];
            $idsOnPage = [];
            $products->has_more_view_review = false;
            if(!empty($list_review_product)) {
                foreach($list_review_product as $key => $value) {
                    if(!empty($value->video_review) || !empty($value->video_review_render)) {
                        $dataVideo[] = $value->video_review_render ?? $value->video_review;
                        $dataThumbalVideo[] = [
                            'video' => $value->video_review_render ?? $value->video_review,
                            'thumbnail' => $value->small_image_video_review ?? ''
                        ];
                    }
                    $idsOnPage[] = $value->id_client;
                    $ReviewFile = ReviewFile::where('id_review', $value->id)->where('type', 'image')->limit(15)->get();
                    $dataReviewFile = [];
                    foreach ($ReviewFile as $vFileReview) {
                        $dataReviewFile[] = [
                            'id' => $vFileReview->id,
                            'filetype' => $vFileReview->filetype,
                            'media' => $this->baseUrlAdmin . '/' . $vFileReview->media,
                            'mime_type' => $vFileReview->mime_type,
                            'name_file' => $vFileReview->name_file,
                        ];
                    }
                    $dataClientReview[] = [
                        'evaluate' => $value->evaluate,
                        'content_evaluate' => $value->content_evaluate,
                        'view_see' => $value->view_see,
                        'date_review' => $value->date_review,
                        'id_client' => $value->id_client,
                        'media_other' => $dataReviewFile ?? []
                    ];
                }
                $has_more = DB::table('tbl_clients_sign_up_review')
                    ->where('id_product', $products->id)
                    ->where('is_review', 1)
                    ->orderBy('date_review', 'asc')
                    ->orderBy('id', 'desc')
                    ->offset(20)
                    ->limit(1)
                    ->exists();
                $products->has_more_view_review = $has_more;
            }
            $idsOnPage = array_unique($idsOnPage);
            $dataClient = [];
            if (!empty($idsOnPage)) {
                $newRequest = clone $this->request;
                $newRequest->merge(['list_id' => $idsOnPage]);
                unset($newRequest['search']); // tránh ảnh hưởng bởi search chung
                $responseClient = $this->dbAccount->getListDetailCustomer($newRequest);
                $dataClient = $responseClient->getData(true);
            }
            if(!empty($dataClient)) {
                foreach ($dataClientReview as $key => $clientReview) {
                    $client = $dataClient['clients'][$clientReview['id_client']] ?? [];
                    if (!empty($client)) {
                        $dataClientReview[$key]['client'] = [
                            'fullname' => $client['fullname'] ?? '',
                            'avatar' => $client['avatar'] ?? '',
                            'phone' => $client['phone'] ?? '',
                        ];
                    }
                }
            }



            $products->info_client_review = $dataClientReview ?? [];
            $products->video_review = $dataVideo ?? [];
            $products->video_thumbal_review = $dataThumbalVideo ?? [];


            $products->countStar = DB::table('tbl_client_evaluate')
                ->leftJoin('tbl_clients_sign_up_review', 'tbl_clients_sign_up_review.id', '=', 'tbl_client_evaluate.id_sign_review')
                ->where('tbl_clients_sign_up_review.id_product', $products->id)
                ->select('tbl_client_evaluate.star', 'tbl_client_evaluate.id_evaluate', DB::raw('COUNT(tbl_client_evaluate.id_evaluate) as total'))
                ->groupBy('tbl_client_evaluate.star')
                ->orderBy('tbl_client_evaluate.star', 'desc')
                ->get();

            $type_evaluate = DB::table("tbl_type_evaluate")->select('tbl_type_evaluate.*', 'tbl_type_evaluate_translations.name', 'tbl_type_evaluate_translations.language')
                ->join('tbl_type_evaluate_translations', 'tbl_type_evaluate_translations.id_evaluate', '=', 'tbl_type_evaluate.id')
                ->where('tbl_type_evaluate_translations.language', $_locale)
                ->get();
            foreach($type_evaluate as $key => $value) {
                $average_star = DB::table('tbl_client_evaluate')
                    ->select(DB::raw('CAST(ROUND(AVG(star), 1) AS DECIMAL(3,1)) as average_star'))
                    ->leftJoin('tbl_clients_sign_up_review', 'tbl_clients_sign_up_review.id', '=', 'tbl_client_evaluate.id_sign_review')
                    ->where('tbl_clients_sign_up_review.id_product', $products->id)
                    ->where('tbl_client_evaluate.id_evaluate', $value->id)
                    ->first();
                $type_evaluate[$key]->star = $average_star->average_star ?? 0;
            }

            if(!empty($products->id_variant)){
                $products->price = 0;
            }


            $products->type_evaluate = $type_evaluate;
            $products->tag_product = $products->tag->map(function ($item) use ($_locale){
                $itemNew = $item->transalations->where('language',$_locale)->first();
                return [
                    'id' => $item->id,
                    'name' => $itemNew->name,
                    'color' => $item->color,
                    'background' => $item->background
                ];
            });
            unset($products->tag);
        }

        if(!empty($products->id)) {
            return response()->json([
                'result' => true,
                'message' => lang('get_data_success'),
                'data' => $products
            ]);
        }
        else {
            return response()->json([
                'result' => false,
                'message' => lang('get_data_fail'),
                'data' => []
            ]);
        }
    }

    //lấy thêm video chi tiết đánh giá sản phẩm
    function getReviewProductPage($id_product) {
        $current_page = 1;
        $per_page = 20;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $offset = ($current_page - 1) * $per_page;
        $list_review_product = DB::table('tbl_clients_sign_up_review')
            ->select(
                'tbl_clients_sign_up_review.id',
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review) as video_review'),
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review_render) as video_review_render'),
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.small_image_video_review) as small_image_video_review'),
                'is_review as isSig',
                'evaluate',
                'content_evaluate',
                'view_see',
                'date_review',
                'id_client'
            )
            ->where('id_product', $id_product)
            ->where('is_review', 1)
            //                ->where(DB::raw('video_review IS NOT NULL'))
            ->orderBy('date_review', 'asc')
            ->orderBy('id', 'desc')
            ->limit($per_page)
            ->offset($offset)
            ->get();
        $dataVideo = [];
        $dataThumbalVideo = [];
        $dataClientReview = [];
        $idsOnPage = [];
        if(!empty($list_review_product)) {
            foreach($list_review_product as $key => $value) {
                if(!empty($value->video_review) || !empty($value->video_review_render)) {
                    $dataVideo[] = $value->video_review_render ?? $value->video_review;
                    $dataThumbalVideo[] = [
                        'video' => $value->video_review_render ?? $value->video_review,
                        'thumbnail' => $value->small_image_video_review ?? ''
                    ];
                }
                $idsOnPage[] = $value->id_client;


                $ReviewFile = ReviewFile::where('id_review', $value->id)->where('type', 'image')->limit(15)->get();
                $dataReviewFile = [];
                foreach ($ReviewFile as $vFileReview) {
                    $dataReviewFile[] = [
                        'id' => $vFileReview->id,
                        'filetype' => $vFileReview->filetype,
                        'media' => $this->baseUrlAdmin . '/' . $vFileReview->media,
                        'mime_type' => $vFileReview->mime_type,
                        'name_file' => $vFileReview->name_file,
                    ];
                }
                $dataClientReview[] = [
                    'evaluate' => $value->evaluate,
                    'content_evaluate' => $value->content_evaluate,
                    'view_see' => $value->view_see,
                    'date_review' => $value->date_review,
                    'id_client' => $value->id_client,
                    'media_other' => $dataReviewFile ?? []
                ];
            }

            $has_more = DB::table('tbl_clients_sign_up_review')
                ->where('id_product', $id_product)
                ->where('is_review', 1)
                ->orderBy('date_review', 'asc')
                ->orderBy('id', 'desc')
                ->offset($offset)
                ->limit(1)
                ->exists();
        }
        $idsOnPage = array_unique($idsOnPage);
        $dataClient = [];
        if (!empty($idsOnPage)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['list_id' => $idsOnPage]);
            unset($newRequest['search']); // tránh ảnh hưởng bởi search chung
            $responseClient = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClient = $responseClient->getData(true);
        }
        if(!empty($dataClient)) {
            foreach ($dataClientReview as $key => $clientReview) {
                $client = $dataClient['clients'][$clientReview['id_client']] ?? [];
                if (!empty($client)) {
                    $dataClientReview[$key]['client'] = [
                        'fullname' => $client['fullname'] ?? '',
                        'avatar' => $client['avatar'] ?? '',
                        'phone' => $client['phone'] ?? '',
                    ];
                }
            }
        }

        $data['info_client_review'] = $dataClientReview ?? [];
        $data['video_review'] = $dataVideo ?? [];
        $data['video_thumbal_review'] = $dataThumbalVideo ?? [];



        return response()->json([
            'result' => true,
            'data' => $data,
            'current_page' => $current_page,
            'per_page' => $per_page,
            'has_more_view_review' => $has_more ?? false,
        ]);
    }

    public function getListDetail()
    {
        $_locale = $this->request->input('_locale');
        $id_client = $this->request->client->id;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $id_product = $this->request->input('id_product');
        if(!empty($id_product)) {
            $products = Products::from('tbl_products as p')
                ->select(
                    'p.id',
                    'p.code',
                    'p.is_use',
                    'p.color_header',
                    'p.background_color',
                    'p.limit_people',
                    'p.count_join',
                    'p.average_star',
                    'p.quantity_reviews',
                    'p.contribute',
                    'pt.name',
                    'pt.content',
                    DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", p.image) as image'),
                    'p.slug',
                    'p.sold',
                    'p.date_end_promotion'
                )->selectRaw(
                    "CONCAT(
                      FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0)/86400), ':',
                      LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),86400)/3600), 2, '0'), ':',
                      LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),3600)/60),  2, '0'), ':',
                      LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),60),             2, '0')
                    ) as time_left_dd_hh_mm_ss
                  ")->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                    $join->on('pt.id_product', '=', 'p.id')->where('pt.language', '=', $_locale);
                })->whereIn('p.id', $id_product)
                ->when(true, function ($q) use ($id_client) {
                    if(!empty($id_client)) {
                        $q->addSelect(
                            DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review) as video_review'),
                            DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review_render) as video_review_render'),
                            DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.small_image_video_review) as small_image_video_review'),
                            'is_review as isSig', 'evaluate')
                            ->leftJoin('tbl_clients_sign_up_review', function ($join) use ($id_client) {
                                $join->on('tbl_clients_sign_up_review.id_product', '=', 'p.id')
                                    ->where('tbl_clients_sign_up_review.id_client', '=', $id_client);
                            });
                    }
                    else {
                        $q->addSelect(
                            DB::raw("null as isSig")
                        );
                    }
                });

            if (!empty($id_client)) {
                $listCategoryId = getCategoryNoLimit($id_client);
                $products->addSelect(DB::raw("
                    IF(
                        EXISTS (
                            SELECT 1
                            FROM tbl_product_category AS c
                            WHERE c.id_product = p.id
                              AND c.id_category IN (".implode(',', $listCategoryId).")
                        ), 0, 1
                    ) AS isLimit
                "));
            }
            else {
                $products->addSelect(DB::raw("0 as isLimit"));
            }
            $products = $products->get();
            return response()->json([
                'result' => true,
                'data' => $products
            ]);
        }
        else {
            return response()->json([
                'result' => false,
                'data' => []
            ]);
        }
    }


    public function getDetailReview($slug = '') {
        $_locale = $this->request->input('_locale');
        $id_client = $this->request->client->id ?? 0;
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $products = Products::from('tbl_products as p')
            ->where('p.slug', $slug)
            ->select('p.id', 'p.code', 'p.is_use', 'p.color_header', 'p.background_color', 'p.limit_people', 'p.count_join', 'p.average_star', 'p.quantity_reviews', 'p.contribute',
                'pt.name', 'pt.content', DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'), 'p.slug', 'p.sold', 'p.date_end_promotion')
            ->selectRaw("
                CONCAT(
                  FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0)/86400), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),86400)/3600), 2, '0'), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),3600)/60),  2, '0'), ':',
                  LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),60), 2, '0')
                ) as time_left_dd_hh_mm_ss
              ")
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })->when(true, function ($q) use ($id_client) {
                if(!empty($id_client)) {
                    $q->addSelect(
                        DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review) as video_review'),
                        DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review_render) as video_review_render'),
                        DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.small_image_video_review) as small_image_video_review'),
                        'is_review as isSig', 'evaluate', 'tbl_clients_sign_up_review.id_review')
                        ->leftJoin('tbl_clients_sign_up_review', function ($join) use ($id_client) {
                            $join->on('tbl_clients_sign_up_review.id_product', '=', 'p.id')
                                ->where('tbl_clients_sign_up_review.id_client', '=', $id_client);
                        });
                }
                else {
                    $q->addSelect(
                        DB::raw("null as isSig")
                    );
                }
            });
        if (!empty($id_client)) {
            $listCategoryId = getCategoryNoLimit($id_client);
            $products->addSelect(DB::raw("
                IF(
                    EXISTS (
                        SELECT 1
                        FROM tbl_product_category AS c
                        WHERE c.id_product = p.id
                          AND c.id_category IN (".implode(',', $listCategoryId).")
                    ), 0, 1
                ) AS isLimit
            "));
        }
        else {
            $products->addSelect(DB::raw("0 as isLimit"));
        }
        $products = $products->first();

        if(!empty($products->id)) {
            $list_images = DB::table('tbl_products_images')->where('id_product', $products->id)
                ->orderBy('order_by', 'asc')
                ->orderBy('id', 'desc')->get();
            $dataImg = [];
            if(!empty($list_images)) {
                foreach($list_images as $key => $image) {
                    if(!empty($image->image)) {
                        $dataImg[] = $this->baseUrlAdmin.'/'.$image->image;
                    }
                }
            }
            $products->list_images = $dataImg ?? [];

            $ingredients = DB::table('tbl_product_ingredients')
                ->where('id_product', $products->id)
                ->where('language', $_locale)
                ->orderBy('key_index', 'asc')
                ->get();
            $products->ingredients = $ingredients;
        }

        if(!empty($products->id)) {
            $dataReview = ClientsReview::select(
                '*',
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.video_review) as video_review'),
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.video_review_render) as video_review_render'),
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.small_image_video_review) as small_image_video_review'),

            )
                ->where('id_product', $products->id)
                ->where('active', 1)
                ->whereRaw('video_review is not null')
                ->orderBy('date_review', 'desc')->paginate($per_page, ['*'], 'page', $current_page);
            $dataIdClient = [];
            $dataItemReview = $dataReview->items();
            foreach($dataItemReview as $key => $value) {
                $dataIdClient[] = $value->id_client;
            }
            if(!empty($dataIdClient)) {
                $this->request->merge(['list_id_client' => $dataIdClient]);
                $list_info_client = $this->dbAccount->getListInfoShortClient($this->request);
                $list_info_client = $list_info_client->getData(true);
                $dataClient = [];
                foreach ($list_info_client['data'] as $key => $value) {
                    $dataClient[$value['id']] = $value;
                }
                foreach ($dataReview->items() as $key => $value) {
                    $dataReview->items()[$key]->client = $dataClient[$value->id_client] ?? null;
                }
            }
            $products->review = $dataReview;


            $products->countStar = DB::table('tbl_client_evaluate')
                ->leftJoin('tbl_clients_sign_up_review', 'tbl_clients_sign_up_review.id', '=', 'tbl_client_evaluate.id_sign_review')
                ->where('tbl_clients_sign_up_review.id_product', $products->id)
                ->select('tbl_client_evaluate.star', 'tbl_client_evaluate.id_evaluate', DB::raw('COUNT(tbl_client_evaluate.id_evaluate) as total'))
                ->groupBy('tbl_client_evaluate.star')
                ->orderBy('tbl_client_evaluate.star', 'desc')
                ->get();

            $type_evaluate = DB::table("tbl_type_evaluate")->select('tbl_type_evaluate.*', 'tbl_type_evaluate_translations.name', 'tbl_type_evaluate_translations.language')
                ->join('tbl_type_evaluate_translations', 'tbl_type_evaluate_translations.id_evaluate', '=', 'tbl_type_evaluate.id')
                ->where('tbl_type_evaluate_translations.language', $_locale)
                ->get();
            foreach($type_evaluate as $key => $value) {
                $average_star = DB::table('tbl_client_evaluate')
                    ->select(DB::raw('CAST(ROUND(AVG(star), 1) AS DECIMAL(3,1)) as average_star'))
                    ->leftJoin('tbl_clients_sign_up_review', 'tbl_clients_sign_up_review.id', '=', 'tbl_client_evaluate.id_sign_review')
                    ->where('tbl_clients_sign_up_review.id_product', $products->id)
                    ->where('tbl_client_evaluate.id_evaluate', $value->id)
                    ->first();
                $type_evaluate[$key]->star = $average_star->average_star ?? 0;
            }
            $products->type_evaluate = $type_evaluate;
        }

        if(!empty($products->id)) {
            return response()->json([
                'result' => true,
                'message' => lang('get_data_success'),
                'data' => $products
            ]);
        }
        else {
            return response()->json([
                'result' => false,
                'message' => lang('Không tìm thấy sản phẩm'),
                'data' => []
            ]);
        }
    }

    public function getPageDetailReview($idProduct = '')
    {
        $_locale = $this->request->input('_locale');
        $id_client = $this->request->client->id ?? 0;
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        if(!empty($idProduct)) {
            $dataReview = ClientsReview::select(
                '*',
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.video_review) as video_review'),
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.video_review_render) as video_review_render'),
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_clients_sign_up_review.small_image_video_review) as small_image_video_review'),

            )->where('id_product', $idProduct)
                ->where('active', 1)
                ->whereRaw('video_review is not null')
                ->orderBy('date_review', 'desc')
                ->paginate($per_page, ['*'], 'page', $current_page);
            $dataIdClient = [];
            $dataItemReview = $dataReview->items();
            foreach($dataItemReview as $key => $value) {
                $dataIdClient[] = $value->id_client;
            }
            if(!empty($dataIdClient)) {
                $this->request->merge(['list_id_client' => $dataIdClient]);
                $list_info_client = $this->dbAccount->getListInfoShortClient($this->request);
                $list_info_client = $list_info_client->getData(true);
                $dataClient = [];
                foreach ($list_info_client['data'] as $key => $value) {
                    $dataClient[$value['id']] = $value;
                }
                foreach ($dataReview->items() as $key => $value) {
                    $dataReview->items()[$key]->client = $dataClient[$value->id_client] ?? null;
                }
            }
            return response()->json([
                'result' => true,
                'message' => lang('get_data_success'),
                'data' => $dataReview
            ]);
        }
    }

    public function getListDetailShort()
    {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $id_product = $this->request->input('id_product');
        if(!empty($id_product)) {
            $products = Products::from('tbl_products as p')
                ->select(
                    'p.id',
                    'p.code',
                    'p.is_use',
                    'pt.name',
                    DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", p.image) as image'),
                    'p.slug',
                    'p.sold',
                )->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                    $join->on('pt.id_product', '=', 'p.id')->where('pt.language', '=', $_locale);
                })->whereIn('p.id', $id_product)
                ->get();
            return response()->json([
                'result' => true,
                'data' => $products
            ]);
        }
        else {
            return response()->json([
                'result' => false,
                'data' => []
            ]);
        }
    }

    public function top_three_product() {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $products = Products::where('count_join', '>', 0)
            ->select(
                'tbl_products.id',
                'tbl_products.code',
                'tbl_products.is_use',
                'tbl_products.count_join',
                'tbl_products.background_color',
                'tbl_products.color_header',
                'pt.name',
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", tbl_products.image) as image'),
                'tbl_products.slug',
            )->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'tbl_products.id')->where('pt.language', '=', $_locale);
            })
            ->where('tbl_products.active', 1)
            ->orderBy('count_join', 'desc')
            ->orderBy('average_star', 'desc')
            ->orderBy('quantity_reviews', 'desc')
            ->limit(3)->get();
        return response()->json([
            'result' => true,
            'data' => $products
        ]);
    }

    public function get_list_products_filter() {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $products_filter = ProductsFilter::select('tbl_products_filter.*', 'pt.name as name')
            ->leftJoin('tbl_products_filter_translations as pt', 'pt.id_product_filter', '=', 'tbl_products_filter.id')
            ->where('pt.language', $_locale)
            ->where('id_parent', 0)
            ->where('active', 1)
            ->orderBy('tbl_products_filter.order_by', 'asc')
            ->orderBy('tbl_products_filter.id', 'desc')
            ->get()
            ->toArray();
        if(!empty($products_filter)) {
            foreach ($products_filter as $key => $value) {
                $products_filter[$key]['child'] = ProductsFilter::select('tbl_products_filter.*', 'pt.name as name')
                    ->selectRaw("(SELECT
                                    COUNT(tbl_products.id) FROM tbl_products
                                JOIN tbl_tag_products_filter ON tbl_tag_products_filter.id_product = tbl_products.id
                                WHERE id_product_filter = tbl_products_filter.id
                                  AND tbl_products.active = 1
                              ) as total_product")
                    ->leftJoin('tbl_products_filter_translations as pt', 'pt.id_product_filter', '=', 'tbl_products_filter.id')
                    ->where('pt.language', $_locale)
                    ->where('id_parent', $value['id'])
                    ->get()->toArray();
            }
        }
        return response()->json($products_filter);
    }

    public function get_list_products_filter_to_app() {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $products_filter = ProductsFilter::select('tbl_products_filter.*', 'pt.name as name')
            ->selectRaw("(SELECT
                                    COUNT(tbl_products.id) FROM tbl_products
                                JOIN tbl_tag_products_filter ON tbl_tag_products_filter.id_product = tbl_products.id
                                WHERE id_product_filter = tbl_products_filter.id
                                  AND tbl_products.active = 1
                              ) as total_product")
            ->leftJoin('tbl_products_filter_translations as pt', 'pt.id_product_filter', '=', 'tbl_products_filter.id')
            ->when(true, function($q) {
                $q->whereRaw('(
                        SELECT 1
                        FROM tbl_tag_products_filter
                        WHERE tbl_tag_products_filter.id_product_filter = tbl_products_filter.id
                    LIMIT 1)');
            })
            ->where('pt.language', $_locale)
            ->where('filter_main_app', 1)
            ->get()->toArray();


        return response()->json($products_filter);
    }

    public function getListData(){
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        $customer_id = $this->request->client->id ?? 0;
        $variant_id = $this->request->input('variant_id') ?? 0;
        $check_transaction = $this->request->input('check_transaction') ?? false;
        $arrProduct = $this->request->input('arrProduct') ?? [];
        $arrCodeProduct = $this->request->input('arrCodeProduct') ?? [];
        $query = Products::where('id','!=',0);
        if (!empty($arrProduct)) {
           $query->whereIn('id',$arrProduct);
        }
        if (!empty($arrCodeProduct)) {
            $query->whereIn('code',$arrCodeProduct);
         }
        $data = $query->get();
        $data->transform(function ($item) use ($check_transaction) {
            $item->check_transaction = $check_transaction;
            return $item;
        });
        $dtData = ProductResources::collection($data);
        return response()->json([
            'data' => $dtData->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function changeSold() {
        $product_transaction = $this->request->input('product_transaction');
        if(!empty($product_transaction)) {
            foreach ($product_transaction as $id => $sold) {
                $products = Products::find($id);
                $products->sold += $sold;
                $products->save();
            }
        }
        return response()->json([
            'result' => true,
            'message' => lang('c_update_success')
        ]);
    }

    public function getListProductAffiliate(){
        $current_page = 1;
        $per_page = 10;
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $search = $this->request->input('search') ?? null;
        $customer_id = $this->request->client->id ?? $this->request->input('customer_id');
        $query = Products::with(['variant_option'])
            ->select('tbl_products.id','price', 'code', 'name','image')
            ->where('tbl_products.id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%$search%");
                $q->orWhere('name', 'like', "%$search%");
            });
        }
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);

        $arrCustomerId = [$customer_id];
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $arrCustomerId]);
        $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $dtCustomer = collect($dataCustomer['data'] ?? []);

        $dtData->getCollection()->transform(function ($item) use ($dtCustomer){
            $client = collect($dtCustomer)->first();
            $item->check_affiliate = 1;
            $item->client = $client;
            return $item;
        });
        $collection = ProductResources::collection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    // lấy danh sách sản phẩm ngắn gọn
    public function SearchInputtingProduct()
    {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        $_id = $this->request->input('id');// không lấy sản phẩm có id này
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $search = trim($this->request->input('search'));
        if(!empty($search)) {
            $Products = Products::from('tbl_products as p')->select(
                    'p.code',
                    'pt.name',
                    DB::raw(
                        "CASE
                            WHEN p.code LIKE '{$search}%' THEN p.code
                            WHEN pt.name LIKE '{$search}%' THEN pt.name
                            WHEN p.code LIKE '%{$search}%' THEN p.code
                            WHEN pt.name LIKE '%{$search}%' THEN pt.name
                            ELSE pt.name
                        END AS display_text"
                    ),
                    DB::raw("CASE
                            WHEN p.code LIKE '{$search}%' THEN 1
                            WHEN pt.name LIKE '{$search}%' THEN 2
                            WHEN p.code LIKE '%{$search}%' THEN 3
                            WHEN pt.name LIKE '%{$search}%' THEN 4
                            ELSE 5
                        END AS match_priority")
                )->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                    $join->on('pt.id_product', '=', 'p.id')->where('pt.language', $_locale);
                })->where(function ($q) use ($search) {
                    $q->where('p.code', 'LIKE', "%{$search}%")->orWhere('pt.name', 'LIKE', "%{$search}%");
                })->orderBy('match_priority')->limit(10)->get();
        }
        return response()->json([
            'result' => true,
            'data' => $Products
        ]);
    }


    public function getListApp()
    {

        $_locale = $this->request->input('_locale');// ngôn ngữ
        $_id = $this->request->input('id');// không lấy sản phẩm có id này
        $id_client = $this->request->client->id ?? 0;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $search = trim($this->request->input('search'));
        if(!empty($search) && !empty($id_client)) {
            HistorySearch::where('id_client', $id_client)->where('search', $search)->delete();
            HistorySearch::insert([
                'id_client' => $id_client,
                'search' => $search,
            ]);
            $Products = Products::from('tbl_products as p')
                ->with(['tag.transalations'])
                ->select(
                    'p.id',
                    'p.code',
                    'p.is_use',
                    'p.color_header',
                    'p.background_color',
                    'p.limit_people',
                    'p.count_join',
                    'p.average_star',
                    'p.quantity_reviews',
                    'p.contribute',
                    'pt.name',
                    'pt.content',
                    DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", p.image) as image'),
                    'p.slug',
                    'p.sold',
                    'p.date_end_promotion',
                    'p.price_min',
                    'p.price_max',
                )
                //                ->selectRaw(
                //                    "
                //                CONCAT(
                //                  FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0)/86400), ':',
                //                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),86400)/3600), 2, '0'), ':',
                //                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),3600)/60),  2, '0'), ':',
                //                  LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),60),             2, '0')
                //                ) as time_left_dd_hh_mm_ss
                //            "
                //                )
                ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                    $join->on('pt.id_product', '=', 'p.id')->where('pt.language', '=', $_locale);
                })->where(function ($query) use ($_id) {
                    $query->where('p.id', '!=', $_id);
                })->where(function ($q) use ($search, $_locale) {
                    // OR theo TAG
                    if (!empty($search)) {
                        $q->orWhereExists(function ($sub) use ($search, $_locale) {
                            $sub->select(DB::raw(1))->from('tbl_tag_products_filter as tpf')->join(
                                'tbl_products_filter_translations as tpft',
                                function ($join) use ($_locale) {
                                    $join->on('tpft.id_product_filter', '=', 'tpf.id_product_filter')->where(
                                        'tpft.language',
                                        $_locale
                                    );
                                }
                            )->whereColumn('tpf.id_product', 'p.id')->where('tpft.name', "like", "%{$search}%");
                        });
                    }
                    // OR theo SEARCH
                    if (!empty($search)) {
                        $q->orWhere(function ($s) use ($search) {
                            $s->where('pt.name', 'LIKE', "%{$search}%")->orWhere('p.code', 'LIKE', "%{$search}%");
                        });
                    }
                });
            if (!empty($id_client)) {
                $listCategoryId = getCategoryNoLimit($id_client);
                $Products->addSelect(
                    DB::raw(
                        "
                    IF(
                        EXISTS (
                            SELECT 1
                            FROM tbl_product_category AS c
                            WHERE c.id_product = p.id
                              AND c.id_category IN (" . implode(',', $listCategoryId) . ")
                        ), 0, 1
                    ) AS isLimit
                "
                    )
                );
            } else {
                $Products->addSelect(DB::raw("0 as isLimit"));
            }
            $Products = $Products->paginate($per_page, ['*'], 'page', $current_page);
            $Products->getCollection()->transform(function ($product) use ($_locale) {
                $product->tag_product = ($product->tag ?? collect([]))->map(function ($item) use ($_locale) {
                    $itemNew = $item->transalations->where('language', $_locale)->first();
                    return [
                        'id' => $item->id,
                        'name' => $itemNew->name,
                        'color' => $item->color,
                        'background' => $item->background
                    ];
                });
                unset($product->tag);
                return $product;
            });
            return response()->json($Products);
        }
        else {
            return response()->json([
                'data' => [],
                'current_page' => $current_page,
                'per_page' => $per_page,
                'total' => 0,
            ]);
        }
    }

    // lưu sản phẩm đã xem khi tìm kiếm
    public function SaveHistoryProduct() {
        $id_client = $this->request->client->id ?? 0;
        $id_product = $this->request->input('id_product');
        if(!empty($id_client) && !empty($id_product)) {
            DB::table('tbl_history_search_view')
                ->where('id_client', $id_client)
                ->where('id_product', $id_product)
                ->delete();
            DB::table('tbl_history_search_view')->insert([
                'id_client' => $id_client,
                'id_product' => $id_product,
            ]);
            return response()->json([
                'result' => true,
            ]);
        }
        return response()->json([
            'result' => false,
        ]);
    }

    public function infoHistorySearch() {
        $_locale = $this->request->input('_locale', 'vi');// ngôn ngữ
        $id_client = $this->request->client->id ?? 0;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $history_search = HistorySearch::select('search')
            ->where('id_client', $id_client)
            ->orderBy('id', 'desc')
            ->limit(8)->get();

        if($history_search->isEmpty()) {
            $products_filter = ProductsFilter::select('pt.name as name')
                ->leftJoin('tbl_products_filter_translations as pt', 'pt.id_product_filter', '=', 'tbl_products_filter.id')
                ->where('pt.language', $_locale)
                ->where('active', 1)
                ->where('id_parent','!=', 0)
                ->where('filter_main_app', 1)
                ->orderBy('tbl_products_filter.order_by', 'asc')
                ->orderBy('tbl_products_filter.id', 'desc')
                ->limit(8)
                ->get()
                ->toArray();
        }

        $history_product_search = Products::from('tbl_products as p')
            ->select(
                'p.id',
                'p.code',
                'p.is_use',
                'p.color_header',
                'p.background_color',
                'p.limit_people',
                'p.count_join',
                'p.average_star',
                'p.quantity_reviews',
                'p.contribute',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", p.image) as image'),
                'p.slug',
                'p.sold',
                'p.date_end_promotion',
                'p.price_min',
                'p.price_max'
            )
            ->join('tbl_history_search_view', 'tbl_history_search_view.id_product', '=', 'p.id')
            ->with(['tag.transalations'])
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'p.id')->where('pt.language', '=', $_locale);
            })
            ->where('tbl_history_search_view.id_client', $this->request->client->id ?? 0)
            ->orderBy('tbl_history_search_view.created_at', 'desc')
            ->limit(8)->get();
        $history_product_search = $history_product_search->map(function ($product) use ($_locale) {
            $product->tag_product = ($product->tag ?? collect())->map(function ($item) use ($_locale) {
                $itemNew = $item->transalations
                    ->where('language', $_locale)
                    ->first();

                return [
                    'id'         => $item->id,
                    'name'       => $itemNew->name ?? null,
                    'color'      => $item->color,
                    'background' => $item->background
                ];
            });
            unset($product->tag);
            return $product;
        });

        return response()->json([
            'history_product' => $history_product_search,
            'history_search' => $history_search,
            'products_filter' => $products_filter ?? [],
        ]);
    }

    public function get_list_product_id() {
        $_locale = $this->request->input('_locale', 'vi');// ngôn ngữ
        $list_id_product = $this->request->input('list_id_product');
        if(!empty($list_id_product)) {
            if(is_numeric($list_id_product)) {
                $list_id_product = [$list_id_product];
            }
            else if(!is_array($list_id_product)){
                $list_id_product = explode(',', $list_id_product);
            }
        }
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $dataProduct = [];
        if(!empty($list_id_product)) {
            $list_product = Products::from('tbl_products as p')
                ->select(
                    'p.id',
                    'p.code',
                    'p.is_use',
                    'p.color_header',
                    'p.background_color',
                    'p.limit_people',
                    'p.count_join',
                    'p.average_star',
                    'p.quantity_reviews',
                    'p.contribute',
                    'pt.name',
                    'pt.content',
                    DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", p.image) as image'),
                    'p.slug',
                    'p.sold',
                    'p.date_end_promotion',
                    'p.price_min',
                    'p.price_max'
                )
                ->with(['tag.transalations'])
                ->whereIn('p.id', $list_id_product)
                ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                    $join->on('pt.id_product', '=', 'p.id')->where('pt.language', '=', $_locale);
                })->get();
            $list_product = $list_product->map(function ($product) use ($_locale) {
                $product->tag_product = ($product->tag ?? collect())->map(function ($item) use ($_locale) {
                    $itemNew = $item->transalations
                        ->where('language', $_locale)
                        ->first();

                    return [
                        'id'         => $item->id,
                        'name'       => $itemNew->name ?? null,
                        'color'      => $item->color,
                        'background' => $item->background
                    ];
                });
                unset($product->tag);
                return $product;
            });


            foreach($list_product as $key => $value) {
                $dataProduct[$value->id] = $value;
            }
        }
        return response()->json([
            'result' => true,
            'data' => $dataProduct,
        ]);
    }


    public function get_list_product_variant(){
//        $list_data = [
//            ['id' => 1, 'id_variant_options' => 3],
//            ['id' => 2, 'id_variant_options' => 16],
//        ];
        $list_data = $this->request->input('list_data');
        $dtData = [];
        if (!empty($list_data)) {
            $query = Products::select(
                'tbl_products.id',
                DB::raw('IF(tbl_products_variant.id_variant_options IS NOT NULL, tbl_products_variant.price, tbl_products.price) as price'),
                'tbl_products.code',
                'tbl_products.name',
                'tbl_variant_options.name as name_variant',
                'tbl_variant_options.id as id_variant_options',
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_products.image) as image')
            )->where('tbl_products.id', '!=', 0);
            $query->leftJoin('tbl_products_variant', 'tbl_products_variant.id_product', '=', 'tbl_products.id');
            $query->leftJoin('tbl_variant_options', 'tbl_variant_options.id', '=', 'tbl_products_variant.id_variant_options');
            if (!empty($list_data)) {
                $query->where(function ($q) use ($list_data) {
                    foreach ($list_data as $item) {
                        $q->orWhere(function ($sub) use ($item) {
                            $sub->where('tbl_products.id', $item['id']);

                            if (!empty($item['id_variant_options'])) {
                                $sub->where('tbl_products_variant.id_variant_options', $item['id_variant_options']);
                            } else {
                                // chỉ lấy variant mặc định (nếu có)
                                $sub->whereNull('tbl_products_variant.id_variant_options');
                            }
                        });
                    }
                });
            }
            $dtData = $query->get();
        }

        return response()->json([
            'data' => $dtData,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}
