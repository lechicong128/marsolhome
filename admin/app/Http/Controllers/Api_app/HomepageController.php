<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ProvinceResource;
use App\Models\CategoryCard;
use App\Models\CategoryPreferential;
use App\Models\ClientsReview;
use App\Models\CountryCurrencyHomepage;
use App\Models\Province;
use App\Services\AccountService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Website;
use App\Models\Products;

class HomepageController extends AuthController
{
    use UploadFile;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
        $this->baseUrl = config('services.storage.url');
        $this->dbAccount = $accountService;
    }

    public function HomePage() {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);

        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $data = Website::where('type', 'homepage')
            ->where('language', $_locale)->first();
        $content = !empty($data->content) ? json_decode($data->content, true) : [];
        foreach($content as $key => $value) {
            if (!empty($value['tab'])) {
                foreach($value['tab'] as $k => $v) {
                    if(!empty($v['img'])) {
                        $content[$key]['tab'][$k]['img'] =  asset('storage/' . ($v['img'] ?? ''));
                    }
                    if(!empty($v['icon'])) {
                        $content[$key]['tab'][$k]['icon'] =  asset('storage/' . ($v['icon'] ?? ''));
                    }
                }
            }
        }
        $banner = DB::table('tbl_banner as b')
            ->where('is_app', 0)->where('active', 1)
            ->select('bt.title', 'bt.content', 'b.is_background', 'b.hidden_button', DB::raw("CONCAT('".$this->baseUrl."/', bt.image) AS image"),
                DB::raw("CONCAT('".$this->baseUrl."/', bt.image_website) AS image_mobile"))
            ->leftJoin('tbl_banner_translations as bt', function ($join) use ($_locale) {
                $join->on('bt.id_banner', '=', 'b.id')
                    ->where('bt.language', '=', $_locale);
            })->orderByDesc('order_by')->get();

        $content['section1']['banner'] = $banner;

        $id_client = $this->request->client->id ?? 0;

        $list_review_new = ClientsReview::select(
                'tbl_clients_sign_up_review.id as id_review_detail',
                'tbl_clients_sign_up_review.id_review',
                'p.id as id_product',
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review) AS video_review"),
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review_render) AS video_review_render"),
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.small_image_video_review) AS small_image_video_review"),
                DB::raw("CONCAT('".$this->baseUrl."/', p.image) AS image_product"),
                'pt.name as name',
                'p.code',
                'p.color_header',
                'p.background_color',
                'p.limit_people',
                'p.count_join',
                'p.average_star',
                'p.quantity_reviews',
                'p.slug',
                'p.date_end_promotion',
            )
            ->selectRaw(
            "
                CONCAT(
                  FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0)/86400), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),86400)/3600), 2, '0'), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),3600)/60),  2, '0'), ':',
                  LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_end_promotion),0),60),             2, '0')
                ) as time_left_dd_hh_mm_ss
              "
            )->LeftJoin('tbl_products as p', 'tbl_clients_sign_up_review.id_product', '=', 'p.id')
            ->join('tbl_product_translations as pt', 'pt.id_product', '=', 'p.id')
            ->when(true, function ($q) use ($id_client) {
                if(!empty($id_client)) {
                    $q->addSelect(
                        DB::raw(
                            '(
                                SELECT tbl_clients_sign_up_review.is_review
                                FROM tbl_clients_sign_up_review
                                WHERE tbl_clients_sign_up_review.id_product = p.id
                                AND tbl_clients_sign_up_review.id_client = "'.$id_client.'"
                                LIMIT 1
                            ) as isSig'
                        )
                    );
                }
                else {
                    $q->addSelect(
                        DB::raw("null as isSig")
                    );
                }
            })

            ->where('pt.language', $_locale)
            ->where('is_review', 1)
            ->where('tbl_clients_sign_up_review.active', 1)
            ->orderBy('date_review', 'desc');
            if (!empty($id_client)) {
                $listCategoryId = getCategoryNoLimit($id_client);
                $list_review_new->addSelect(DB::raw("
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
                $list_review_new->addSelect(DB::raw("0 as isLimit"));
            }
        $list_review_new = $list_review_new->limit(15)->get();
        $content['list_review_new'] = $list_review_new;

        $Products = Products::from('tbl_products as p')
            ->select('p.id', 'p.code', 'p.color_header', 'p.background_color', 'p.limit_people', 'p.count_join', 'p.average_star', 'p.quantity_reviews',
                'pt.name', 'pt.content',
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'), 'p.slug', 'p.date_end_promotion')
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
            })
            ->when(true, function ($q) use ($id_client) {
                if(!empty($id_client)) {
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
                    $q->addSelect(
                        DB::raw("null as isSig")
                    );
                }
            })->where('is_hot', 1);
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
        $Products = $Products->limit(15)->get();
        $content['product_outstanding'] = $Products;

        return response()->json([
            'result' => true,
            'data' => $content
        ]);

    }

    public function HelpCentre() {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);

        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $data = Website::where('type', 'helpcentre')
            ->where('language', $_locale)->first();
        $content = !empty($data->content) ? json_decode($data->content, true) : [];
        foreach($content as $key => $value) {
            if($key == 'image') {
                foreach($value as $k => $v) {
                    $content[$key][$k] =  $this->baseUrl .'/'.($v ?? '');
                }
            }
        }

        return response()->json([
            'result' => true,
            'data' => $content
        ]);

    }

    public function Feedback() {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);

        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $data = Website::where('type', 'feedback')
            ->where('language', $_locale)->first();
        $content = !empty($data->content) ? json_decode($data->content, true) : [];
        foreach($content as $key => $value) {
            if($key == 'image') {
                $content[$key] =  $this->baseUrl . '/' . ($value ?? '');
            }
        }

        return response()->json([
            'result' => true,
            'data' => $content
        ]);
    }

    //lấy các sản phẩm top review
    public function dataReviewHub() {
        $_search = $this->request->input('search');
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
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
        $current_page = 1;// trang hiện tại
        $per_page = 10;// số lượng trang
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $topProducts = Products::orderBy('quantity_reviews', 'desc')
            ->from('tbl_products as p')
            ->select('p.id',
                'p.code',
                'p.color_header',
                'p.background_color',
                'p.limit_people',
                'p.count_join',
                'p.average_star',
                'p.quantity_reviews',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'), 'p.slug', 'p.date_end_promotion')
            ->join('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->whereRaw('EXISTS (SELECT id FROM tbl_clients_sign_up_review WHERE tbl_clients_sign_up_review.id_product = p.id AND is_review = 1 AND active = 1 AND video_review IS NOT NULL)')
            ->where('average_star', '>', 0)
            ->where('quantity_reviews', '>', 0)
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
            ->when(true, function($q) use ($_search) {
                if(!empty($_search)) {
                    $q->where('pt.name', 'like', '%'.$_search.'%')
                        ->orWhere('p.code', 'like', '%'.$_search.'%');
                }
            })
            ->orderBy('average_star', 'desc')
            ->orderBy('quantity_reviews', 'desc')
            ->orderBy('count_join', 'desc')
            ->paginate($per_page, ['*'], 'page', $current_page);

        foreach($topProducts as $key => $topProduct) {
            $clientReview = ClientsReview::select(
                    'tbl_clients_sign_up_review.*',
                    DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review) AS video_review"),
                    DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review_render) AS video_review_render"),
                    DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.small_image_video_review) AS small_image_video_review")
                )->where('id_product', $topProduct->id)
                    ->where('is_review', 1)
                    ->where('active', 1)
                    ->orderBy('date_review', 'desc')
                    ->paginate($per_page, ['*'], 'page', $current_page);
            $topProduct->review = $clientReview;
        }

        return response()->json([
            'result' => true,
            'data' => $topProducts
        ]);
    }

    public function dataReviewHubProduct($id_product) {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;// trang hiện tại
        $per_page = 10;// số lượng trang
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $clientReview = ClientsReview::select(
            'tbl_clients_sign_up_review.*',
            DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review) AS video_review"),
            DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review_render) AS video_review_render"),
            DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.small_image_video_review) AS small_image_video_review")
        )->where('id_product', $id_product)
            ->where('is_review', 1)
            ->where('active', 1)
            ->orderBy('date_review', 'desc')
            ->paginate($per_page, ['*'], 'page', $current_page);
        return response()->json([
            'result' => true,
            'data' => $clientReview
        ]);
    }

    public function dataReviewHubOther() {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $_id = $this->request->input('id');
        $current_page = 1;// trang hiện tại
        $per_page = 10;// số lượng trang
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $topProducts = Products::orderBy('quantity_reviews', 'desc')
            ->from('tbl_products as p')
            ->select('p.id',
                'p.code',
                'p.color_header',
                'p.background_color',
                'p.limit_people',
                'p.count_join',
                'p.average_star',
                'p.quantity_reviews',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'), 'p.slug', 'p.date_end_promotion')
            ->join('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->whereRaw('EXISTS (SELECT id FROM tbl_clients_sign_up_review WHERE tbl_clients_sign_up_review.id_product = p.id AND is_review = 1 AND active = 1 AND video_review IS NOT NULL)')
            ->where('p.id', '!=', $_id)
            ->where('average_star', '>', 0)
            ->where('quantity_reviews', '>', 0)
            ->orderBy('average_star', 'desc')
            ->orderBy('quantity_reviews', 'desc')
            ->orderBy('count_join', 'desc')
            ->paginate($per_page, ['*'], 'page', $current_page);

        foreach($topProducts as $key => $topProduct) {
            $clientReview = ClientsReview::select(
                'evaluate',
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review) AS video_review"),
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review_render) AS video_review_render"),
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.small_image_video_review) AS small_image_video_review")
            )->where('id_product', $topProduct->id)
                ->where('is_review', 1)
                ->where('active', 1)
                ->orderBy('date_review', 'desc')
                ->paginate(3, ['*'], 'page', 1);
            $topProduct->review = $clientReview;
        }
        return response()->json([
            'result' => true,
            'data' => $topProducts
        ]);
    }

    public function donations_and_charity() {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);

        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $data = Website::where('type', 'donations_and_charity')
            ->where('language', $_locale)->first();
        $content = !empty($data->content) ? json_decode($data->content, true) : [];
        if(!empty($content['section4']['image'])) {
            $content['section4']['image'] = $this->baseUrl . '/' . ($content['section4']['image'] ?? '');
        }
        return response()->json([
            'result' => true,
            'data' => $content
        ]);
    }

    //header review hub
    public function BannerReviewHub() {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);

        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $data = Website::where('type', 'reviewhub')
            ->where('language', $_locale)->first();
        $content = !empty($data->content) ? json_decode($data->content, true) : [];
        foreach($content as $key => $value) {
            if($key == 'image') {
                $content[$key] =  $this->baseUrl .'/'.($value ?? '');
            }
        }
        $content['countClientReview'] = ClientsReview::select(
            DB::raw('COUNT(distinct id_client) as total_client'),
        )->first()->total_client ?? 0;


        $reviewShow = ClientsReview::select('id', 'id_client', 'content_evaluate', 'evaluate', 'view_see')->where('is_review', 1)
            ->where('active', 1)
            ->where('content_evaluate', '!=', '')
            ->whereNotNull('video_review')
            ->inRandomOrder()
            ->where('video_review', '!=', '')->limit(10)->get();
        $content['Review'] = $reviewShow;

        $ClientJoin = ClientsReview::select(DB::raw('distinct id_client'))
            ->limit(10)->get()->toArray();
        $listIdClient = array_column($ClientJoin, 'id_client');
        foreach($reviewShow as $key => $value) {
            if(!empty($value->id_client)) {
                $listIdClient[] = $value->id_client ?? 0;
            }
        }
        $listIdClient = array_unique($listIdClient);
        $this->request->merge(['list_id_client' => $listIdClient]);
        $dataClientJoin = $this->dbAccount->getListInfoShortClient($this->request);
        $ClientJoin = $dataClientJoin->getData(true)['data'] ?? [];
        $content['ClientJoin'] = [];
        $KeyClient = [];

        foreach($ClientJoin as $key => $value) {
            $content['ClientJoin'][] = [
                'fullname' => $value['fullname'] ?? '',
                'avatar' => $value['avatar'] ?? '',
                'address' => $value['address'] ?? '',
            ];
            $KeyClient[$value['id'] ?? 0] = $value;
        }

        foreach($content['Review'] as $key => $value) {
            $content['Review'][$key]->client = $KeyClient[$value->id_client ?? 0] ?? null;
        }
        return response()->json([
            'result' => true,
            'data' => $content
        ]);
    }

    public function HomePageToApp() {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);

        $id_client = $this->request->client->id ?? 0;

        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $content = [];
        $banner = DB::table('tbl_banner as b')
            ->where('is_app', 1)
            ->select('bt.title', 'bt.content', 'b.is_background', DB::raw("CONCAT('".$this->baseUrl."/', bt.image) AS image"))
            ->leftJoin('tbl_banner_translations as bt', function ($join) use ($_locale) {
                $join->on('bt.id_banner', '=', 'b.id')
                    ->where('bt.language', '=', $_locale);
            })->orderByDesc('order_by')->get();

        $content['banner'] = $banner;


        $list_review_product = DB::table('tbl_clients_sign_up_review')
            ->select(
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
            ->where('is_review', 1)
            ->orderBy('date_review', 'asc')
            ->orderBy('view_see', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        $dataClientReview = [];
        $idsOnPage = [];
        if(!empty($list_review_product)) {
            foreach($list_review_product as $key => $value) {
                $idsOnPage[] = $value->id_client;
                $dataClientReview[] = [
                    'evaluate' => $value->evaluate,
//                    'content_evaluate' => $value->content_evaluate,
//                    'view_see' => $value->view_see,
//                    'date_review' => $value->date_review,
                    'id_client' => $value->id_client,
                    'video_review' => $value->video_review_render ?? $value->video_review
                ];
            }
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
        $content['list_client_review'] = $dataClientReview ?? [];



        $data = Website::where('type', 'homepage')
            ->where('language', $_locale)->first();
        $contentHome = !empty($data->content) ? json_decode($data->content, true) : [];
        $content['content_join']  = $contentHome['section2']['content_join'];


        $introduction_app = DB::table('tbl_introduction_app as b')
            ->where('active', 1)
            ->select('bt.title', 'bt.content', 'bt.description', 'b.screen_link',
                DB::raw("CONCAT('".$this->baseUrl."/', b.image) AS image"),
                DB::raw("CONCAT('".$this->baseUrl."/', b.image_main) AS image_main")
            )
            ->leftJoin('tbl_introduction_app_translations as bt', function ($join) use ($_locale) {
                $join->on('bt.id_introduction_app', '=', 'b.id')
                    ->where('bt.language', '=', $_locale);
            })->orderByDesc('order_by')->get();

        $content['introduction_app'] = $introduction_app;

        return response()->json([
            'result' => true,
            'data' => $content
        ]);
    }

    public function get_banner_web_to_app(){
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $banner = DB::table('tbl_banner as b')
            ->where('is_app', 0)
            ->where('active', 1)
            ->where('show_web_app', 1)
            ->select('bt.title', 'bt.content', 'b.is_background', 'b.hidden_button', DB::raw("CONCAT('".$this->baseUrl."/', bt.image) AS image"),
                DB::raw("CONCAT('".$this->baseUrl."/', bt.image_website) AS image_mobile"))
            ->leftJoin('tbl_banner_translations as bt', function ($join) use ($_locale) {
                $join->on('bt.id_banner', '=', 'b.id')
                    ->where('bt.language', '=', $_locale);
            })->orderByDesc('order_by')->get();
        $content['banner'] = $banner;
        return response()->json([
            'result' => true,
            'data' => $content
        ]);
    }


    public function get_banner_app(){
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $banner = DB::table('tbl_banner as b')
            ->where('is_app', 1)
            ->where('active', 1)
            ->select('bt.title', 'bt.content', 'b.is_background', 'b.hidden_button', DB::raw("CONCAT('".$this->baseUrl."/', bt.image) AS image"),
                DB::raw("CONCAT('".$this->baseUrl."/', bt.image_website) AS image_mobile"))
            ->leftJoin('tbl_banner_translations as bt', function ($join) use ($_locale) {
                $join->on('bt.id_banner', '=', 'b.id')
                    ->where('bt.language', '=', $_locale);
            })->orderByDesc('order_by')->get();
        $content['banner'] = $banner;
        return response()->json([
            'result' => true,
            'data' => $content
        ]);
    }

    public function accumulation() {
//        $id_client = $this->request->client->id ?? 0;

//        $discount_total_orders = DB::table('tbl_discount_total_orders')
//            ->orderBy('total_order_start', 'ASC')->get()->toArray();
//        $leaders = DB::table('tbl_accumulation_leaders')->get()->toArray();
//        $reached = DB::table('tbl_accumulation_leaders_reached')->get();
//        $data_reward = DB::table('tbl_accumulation_leaders_reward')->get();
//        $reward = [];
//        foreach($data_reward as $key => $item) {
//            $reward[$item->id_leaders][$item->id_reached] = $item->money_reward;
//        }
//        $interest = DB::table('tbl_accumulation_interest')->get();
//        $passive = DB::table('tbl_accumulation_passive')->get();
//
//        $dataResult = [];
////        $dataResult[] = [
////            'title' => ' 1. Chiết khấu đơn hàng & Đào tạo',
////            'content' => 'Kèm hỗ trợ đào tạo & chiến lược kinh doanh',
////            'data' => $discount_total_orders
////        ];
//
//
//        foreach($leaders as $key => $value) {
//            foreach($reached as $valReached) {
//                if($valReached->id_leaders == $value->id) {
//                    $leaders[$key]->reached[] = [
//                        'id_reached' => $valReached->id,
//                        'name_reached' => $valReached->name_reached,
//                        'money_reward' => $reward[$value->id][$valReached->id] ?? 0
//                    ];
//                }
//            }
//        }
//
//        $dataResult[] = [
//            'title' => '2. Tích lũy Leaders F1',
//            'content' => '',
//            'data' => $leaders
//        ];
//
//        echo "<pre>";
//        var_dump($dataResult);
//
//        dd($discount_total_orders);

        $discount_total_orders = DB::table('tbl_discount_total_orders')
            ->orderBy('total_order_start', 'ASC')
            ->get();

        $leaders = DB::table('tbl_accumulation_leaders')->get();

        $reached = DB::table('tbl_accumulation_leaders_reached')->get();

        $data_reward = DB::table('tbl_accumulation_leaders_reward')->get();

        $interest = DB::table('tbl_accumulation_interest')->get();

        $passive = DB::table('tbl_accumulation_passive')->get();

        $difference = get_option('accumulation_difference') ? json_decode(get_option('accumulation_difference'), true) : [];

        $discountTotal = [];
        foreach($discount_total_orders as $key => $item) {
            $discountTotal[] = [
                'id' => $item->id,
                'total' => $item->total_order_end ? $item->total_order_end : $item->total_order_start,
                'discount' => $item->discount,
                'content' => $item->content
            ];
        }

        $MoneyPass = (578000000 / 4800000000);
        $passiveTotal = [];
        foreach($passive as $key => $item) {
            $passiveTotal[] = [
                'id' => $item->id,
                'total' => $item->total_order_start ? $item->total_order_start : $item->total_order_end,
                'radio_bonus' => $item->radio_bonus,
                'total_radio_bonus' => $item->total_radio_bonus,
                'tich_luy' => $MoneyPass * $item->total_order_start
            ];
        }

        /*
         |--------------------------------------------------------------------------
         | Build reward map
         |--------------------------------------------------------------------------
         */
        $reward = [];
        foreach ($data_reward as $item) {
            $reward[$item->id_leaders][$item->id_reached] = $item->money_reward;
        }

        /*
         |--------------------------------------------------------------------------
         | Leaders + reward
         |--------------------------------------------------------------------------
         */
        $leadersResult = [];

        foreach ($leaders as $leader) {

            $reachedData = [];

            foreach ($reached as $itemReached) {

                $reachedData[] = [
                    'id_reached'   => $itemReached->id,
                    'total_start'  => $itemReached->total_start,
                    'reward'       => $reward[$leader->id][$itemReached->id] ?? 0
                ];
            }

            $leadersResult[] = [
                'id'             => $leader->id,
                'level'          => $leader->level,
                'level_discount' => $leader->level_discount,
                'note'           => $leader->note,
                'reached'        => $reachedData
            ];
        }

        /*
         |--------------------------------------------------------------------------
         | Response
         |--------------------------------------------------------------------------
         */


        $accumulation_difference = [];

        foreach ($difference as $keyLevel => $items) {
            foreach($items as $key => $item) {
                $accumulation_difference[$keyLevel][] = [
                    'total_orders' => $item['total_orders'],
                    'F' => $item['F'],
                    'F1' => $item['F1'],
                    'F2' => $item['F2'],
                    'F3' => $item['F3']
                ];
            }
        }

        $customer_id = $this->request->client->id ?? 0;
        if(!empty($customer_id)) {
            $this->request->merge(['id' => $customer_id]);
            $response = $this->dbAccount->getDetailCustomer($this->request);
            $data = $response->getData(true);
            $client = $data['client'] ?? [];
        }

        if (!empty($client) && $client['is_leader'] == 1) {
            $dataResult['leaders'] = [
                'title' => '1. Tích lũy Leaders F1',
                'content' => 'Tính theo doanh số sau chiết khấu',
                'content_footer' => 'Càng lên cấp cao – % CK cao – mốc thưởng càng lớn.',
                'data' => $leadersResult
            ];
            $dataResult['interest'] = [
                'title' => '2. Quyền lợi & chính sách công ty',
                'content' => 'Hoa hồng chênh lệch theo cấp nhập',
                'content_footer' => '% thưởng nóng khi khách hàng thanh toán 100% đơn trong lần đầu tiên ',
                'data' => $interest,
                'interest_year' => 'Giá trị đơn hàng * (578.000.000 / 4.800.000.000)'
            ];
            $dataResult['passive'] = [
                'title' => '3. Thu nhập thụ động (NPP tái nhập hàng)',
                'content' => '',
                'content_footer' => 'Giá trị đơn càng lớn → thu nhập tuyệt đối càng cao, dù % giảm.',
                'data' => $passiveTotal,
            ];
            $dataResult['accumulation_difference'] = [
                'title' => '4. Chênh lệch Leaders',
                'content' => '',
                'content_footer' => 'Tuyến càng sâu (F3+, F4+) → vẫn có thu nhập nhưng giảm dần',
                'data' => $accumulation_difference,
            ];
        } else {
            $dataResult['discount_total_orders'] = [
                'title' => '1. Chiết khấu đơn hàng & Đào tạo',
                'content' => 'Kèm hỗ trợ đào tạo & chiến lược kinh doanh',
                'data' => $discountTotal
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $dataResult
        ]);
    }
}
