<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\EventArticles;
use App\Models\EventArticlesInfoEvent;
use App\Models\EventArticlesTranslations;
use App\Helpers\FilesHelpers;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;
use Illuminate\Support\Facades\Validator;
use App\Services\AdminService;
use App\Services\AccountsService;

class EventArticlesController extends AuthController
{
    use UploadFile;
    protected $svAdmin;
    protected $svAccount;
    public function __construct(Request $request, AdminService $adminService, AccountsService $svAccount)
    {
        parent::__construct($request);
        $this->svAdmin = $adminService;
        $this->accountsAdmin = $svAccount;
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
    }

    public function get_list(){
        $filter = $this->request->input('filter');
        $active_search = $filter['active_search'] ?? -1;
        $search = $this->request->input('search');
        $orderBy = $this->request->input('order_by', 'id');
        if($orderBy == 'DT_RowIndex') {
            $orderBy = 'id';
        }
        $orderDir = $this->request->input('order_dir', 'asc');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $query = EventArticles::select('*', DB::raw('CONCAT("'.$this->baseUrl.'/", image) as image'))
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%");
            });
        }
        if ($active_search != -1 && $active_search != ''){
            $query->where('active', $active_search);
        }
        $query->with(['transalations']);
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        $total = EventArticles::count();
        return response()->json([
            'total' => $total,
            '_locale' => $this->request->_locale,
            'filtered' => $filtered,
            'data' => $data
        ]);
    }

    public function countAll(){
        $filter = $this->request->input('filter');
        $type_client_search = $filter['type_client_search'] ?? 0;
        $active_search = $filter['active_search'] ?? -1;

        $arrType = [
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ];

        $query = EventArticles::where('id','!=',0);
        if ($active_search != -1 && $active_search != ''){
            $query->where('active', $active_search);
        }
        $totalAll = $query->count();

        foreach ($arrType as $key => $value){
            $type_client = $value['id'];
            $query = Clients::where('id','!=',0);
            $query->where('type_client', $type_client);
            if (($type_client_search)){
//                $query->where('type_client', $type_client_search);
            }
            if ($active_search != -1 && $active_search != ''){
                $query->where('active', $active_search);
            }
            $total = $query->count();
            $arrType[$key]['total'] = $total;
        }

        return response()->json([
            'total' => $totalAll,
            'arrType' => $arrType,
        ]);
    }

    public function get_detail(){
        $id = $this->request->input('id') ?? 0;
        if(!empty($id)) {
            $event_articles = EventArticles::find($id);
            if (!empty($event_articles)) {
                $dtImage = !empty($event_articles->image) ? $this->baseUrl . '/' . $event_articles->image : null;
                $event_articles->image = $dtImage;

                $dtImageSponsor = !empty($event_articles->image_sponsor) ? $this->baseUrl . '/' . $event_articles->image_sponsor : null;
                $event_articles->image_sponsor = $dtImageSponsor;

                $DataTranslations = EventArticlesTranslations::where('id_event_articles', $event_articles->id)->get();
                $eventStran = [];
                foreach($DataTranslations as $translation) {
                    $eventStran[$translation->language] = $translation;
                }
                $event_articles->translations = $eventStran;

                $DataInfoEvent = EventArticlesInfoEvent::where('id_event_articles', $event_articles->id)->get();
                $eventStranInfo = [];
                foreach($DataInfoEvent as $dataInfo) {
                    $eventStranInfo[$dataInfo->language][$dataInfo->key_index] = $dataInfo;
                }

                $event_articles->info_event = $eventStranInfo;

                $list_images = DB::table('tbl_event_articles_images')
                    ->where('id_event_articles', $id)
                    ->orderBy('order_by', 'asc')
                    ->orderBy('id', 'desc')->get();
                $keyImages = [];
                foreach($list_images as $key => $img) {
                    $img->image = !empty($img->image) ? $this->baseUrl . '/' . $img->image : null;
                    $keyImages[$key] = $img;
                }
                $event_articles->list_images = $list_images ?? [];

                $DataInfoProduct = DB::table('tbl_event_articles_product')
                    ->where('id_event_articles', $event_articles->id)->get();
                $ListProduct = [];
                foreach($DataInfoProduct as $dataInfo) {
                    $ListProduct[] = $dataInfo->id_product;
                }
                $event_articles->product_id = $ListProduct;

            }
            $data['result'] = true;
            $data['data'] = $event_articles;
            $data['message'] = lang('get_data_success');
        }
        else {
            $data['result'] = false;
            $data['message'] = lang('get_data_fail');
        }
        return response()->json($data);
    }

    public function submit(){
        $id = $this->request->input('id') ?? 0;
        $event_articles = EventArticles::find($id);
        if (empty($event_articles->id) && $id != 0){
            $data['result'] = false;
            $data['message'] = lang('data_not_found');
            return response()->json($data);
        }

        $EventRules = [];
        if(filled($this->request->code)) {
            $EventRules['code'] = 'unique:tbl_event_articles,code,' . $id;
        }
        $EventMessages = [
            'code.unique' => lang('code_unique'),
        ];

        $validator = Validator::make($this->request->all(), $EventRules, $EventMessages);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all()[0];
            return response()->json($data);
        }


        DB::beginTransaction();
        try {
            if(empty($event_articles->id)){
                $event_articles = new EventArticles();
            }
            $name = $this->request->name ?? [];

            if(is_string($name)) {
                $name = json_decode($name, true);
                $ktName = false;
                foreach($name as $language => $value) {
                    if(!empty($value)) {
                        $ktName = true;

                    }
                }
                if(empty($ktName)) {
                    $data['result'] = false;
                    $data['message'] = lang('c_ls_input_one_name');
                    return response()->json($data);
                }
            }
            $content = $this->request->content ?? [];
            if(is_string($content)) {
                $content = json_decode($content, true);
            }

            $product_id = $this->request->product_id ?? [];
            if(is_string($product_id)) {
                $product_id = json_decode($product_id, true);
            }

            $info_events = $this->request->info_event ?? [];
            if(is_string($info_events)) {
                $info_events = json_decode($info_events, true);
            }

            $imagesDelete = $this->request->imagesDelete;
            if(is_string($imagesDelete)) {
                $imagesDelete = json_decode($imagesDelete, true);
            }

            $order_images = $this->request->order_images;
            if(is_string($order_images)) {
                $order_images = json_decode($order_images, true);
            }

            $event_articles->code = $this->request->code ?? time();
            $event_articles->name = $this->request->name_main ?? '';
            $event_articles->background_color = $this->request->background_color ?? NULL;
            $event_articles->prizes = $this->request->prizes ?? 0;
            $event_articles->total_money_prizes = $this->request->total_money_prizes ?? 0;
            $event_articles->total_product = $this->request->total_product ?? 0;

            $event_articles->type_sponsor = $this->request->type_sponsor ?? 1;
            if($event_articles->type_sponsor == 2) {
                $event_articles->prizes = 0;
            }
            else {
                $event_articles->total_product = 0;
            }

            $event_articles->sponsor = $this->request->sponsor ?? 0;

            if($event_articles->sponsor == 0) {
                $event_articles->name_sponsor = NULL;
                $event_articles->image_sponsor = NULL;
            }
            else {
                $event_articles->name_sponsor  = $this->request->name_sponsor ?? NULL;
            }

            $event_articles->type_event_articles  = $this->request->type_event_articles ?? 1;

            if($this->request->date_start_event) {
                $event_articles->date_start_event = to_sql_date($this->request->date_start_event, true);
            }
            else {
                $event_articles->date_start_event = NULL;
            }

            if($this->request->date_end_event) {
                $event_articles->date_end_event = to_sql_date($this->request->date_end_event, true);
            }
            else {
                $event_articles->date_end_event = NULL;
            }


            $slug = $this->request->slug;
            if(empty($slug)) {
                $slug = convertToSlug($this->request->name_main ?? '');
            }

            $ktIsSlug = EventArticles::where('slug', $slug)->where('id', '!=', $id)->first();
            if(!empty($ktIsSlug->id)) {
                $slug = $slug . '-' . time();
            }
            $event_articles->slug = $slug;

            $event_articles->save();
            if ($event_articles) {
                if(!empty($imagesDelete)) {
                    $imgDelete = DB::table('tbl_event_articles_images')
                        ->whereIn('id', $imagesDelete)->get();
                    foreach($imgDelete as $img) {
                        if (!empty($img->image)){
                            $this->deleteFile($img->image);
                        }
                    }
                    $imgDelete = DB::table('tbl_event_articles_images')
                        ->whereIn('id', $imagesDelete)->delete();
                }
                if(!empty($name)) {
                    foreach ($name as $language => $value) {
                        DB::table('tbl_event_articles_translations')->updateOrInsert(
                            [
                                'id_event_articles' => $event_articles->id,
                                'language' => $language
                            ], [
                                'name' => $value,
                                'content' => $content[$language] ?? '',
                            ]
                        );
                    }
                }
                if(!empty($product_id)) {
                    foreach ($product_id as $key => $value) {
                        DB::table('tbl_event_articles_product')->updateOrInsert(
                            [
                                'id_event_articles' => $event_articles->id,
                                'id_product' => $value
                            ], [
                                'id_product' => $value,
                                'id_event_articles' => $event_articles->id,
                            ]
                        );
                    }
                }
                else {
                    DB::table('tbl_event_articles_product')
                        ->where('id_event_articles', $event_articles->id)->delete();
                }

                foreach($info_events as $language => $info_event) {
                    foreach($info_event as $key => $value) {
                        DB::table('tbl_event_articles_info_event')->updateOrInsert(
                            [
                                'id_event_articles' => $event_articles->id,
                                'language' => $language,
                                'key_index' => $key ?? 0
                            ], [
                                'title' => $value['title'] ?? '',
                                'content' => $value['content'] ?? '',
                            ]
                        );
                    }
                }

                if(empty($event_articles->code)) {
                    $codeEvent = 'EV-' . str_pad($event_articles->id, 6, '0', STR_PAD_LEFT);
                    $ktCodeEvent = EventArticles::where('code', $codeEvent)->first();
                    if(empty($ktCodeEvent->id)) {
                        $event_articles->code = $codeEvent;
                    }
                    else {
                        $event_articles->code = 'SP-' . str_pad($event_articles->id, 6, '0', STR_PAD_LEFT). '-1';
                    }
                    $event_articles->save();
                }

                if(!empty($order_images)) {
                    foreach($order_images as $key => $value) {
                        DB::table('tbl_event_articles_images')
                            ->where('id', $key)
                            ->update(['order_by' => $value]);
                    }
                }

                if ($this->request->hasFile('image')) {
                    if (!empty($event_articles->image)) {
                        $this->deleteFile($event_articles->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'event_articles/' . $event_articles->id, 70, 70, false);
                    $event_articles->image = $path;
                    $event_articles->save();
                }

                if($event_articles->sponsor > 0) {
                    if ($this->request->hasFile('image_sponsor')) {
                        if (!empty($event_articles->image_sponsor)) {
                            $this->deleteFile($event_articles->image_sponsor);
                        }
                        $path = $this->UploadFile(
                            $this->request->file('image_sponsor'),
                            'event_articles/' . $event_articles->id,
                            70,
                            70,
                            false
                        );
                        $event_articles->image_sponsor = $path;
                        $event_articles->save();
                    }
                }

                if ($this->request->hasFile('images')) {
                    foreach($this->request->file('images') as $key => $value) {
                        $path = $this->UploadFile($value, 'event_articles/' . $event_articles->id, 600, 600, false);
                        DB::table('tbl_event_articles_images')->insert([
                            'id_event_articles' => $event_articles->id,
                            'image' => $path,
                        ]);
                    }
                }

                DB::commit();
                $data['result'] = true;
                if(!empty($id)) {
                    $data['message'] = lang('update_success');
                }
                else {
                    $data['message'] = lang('add_success');
                }
            } else {
                $data['result'] = false;
                if(!empty($id)) {
                    $data['message'] = lang('update_fail');
                }
                else {
                    $data['message'] = lang('add_fail');
                }
            }
            return response()->json($data);
        }
        catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function delete(){
        $id = $this->request->input('id') ?? 0;
        $event_articles = EventArticles::find($id);
        if (empty($event_articles->id)){
            $data['result'] = false;
            $data['message'] = lang('data_not_found');
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if (!empty($event_articles->image)){
                $this->deleteFile($event_articles->image);
            }
            if (!empty($event_articles->name_sponsor)){
                $this->deleteFile($event_articles->name_sponsor);
            }

            $event_articles->delete();
            $listImg = DB::table('tbl_event_articles_images')->where('id_event_articles', $event_articles->id)->get();
            foreach($listImg as $img) {
                if (!empty($img->image)){
                    $this->deleteFile($img->image);
                }
            }
            DB::table('tbl_event_articles_product')->where('id_event_articles', $event_articles->id)->delete();
            DB::table('tbl_event_articles_images')->where('id_event_articles', $event_articles->id)->delete();
            DB::table('tbl_event_articles_info_event')->where('id_event_articles', $event_articles->id)->delete();
            DB::table('tbl_event_articles_translations')->where('id_event_articles', $event_articles->id)->delete();

            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            return response()->json($data);
        }  catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function active(){
        $id = $this->request->input('id') ?? 0;
        $event_articles = EventArticles::find($id);
        DB::beginTransaction();
        try {
            $event_articles->active = $event_articles->active == 0 ? 1 : 0;
            $event_articles->save();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        }
        catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function change_is_hot(){
        $id = $this->request->input('id') ?? 0;
        $event_articles = EventArticles::find($id);
        DB::beginTransaction();
        try {
            $event_articles->is_hot = $event_articles->is_hot == 0 ? 1 : 0;
            $event_articles->save();
            if($event_articles->is_hot == 1) {
                EventArticles::where('id', '!=', $id)->where('is_hot', 1)->update(['is_hot' => 0]);
            }

            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        }
        catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function api_list_data() {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        $_id = $this->request->input('id');// không lấy sản phẩm có id này
        $review_top = $this->request->input('review_top');// lâấy những sản phẩm đánh giá cao

        $status = $this->request->input('status');// lâấy những sản phẩm đánh giá cao

        $type_event_articles = $this->request->input('type_event_articles');// loại chiến dịch
        $search = $this->request->input('search');
        $id_product = $this->request->input('id_product');

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

        $EventArticles = EventArticles::from('tbl_event_articles as p')
            ->select(
                'p.id',
                'p.code',
                'p.background_color',
                'p.count_view',
                'p.count_join',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image) as image'),
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image_sponsor) as image_sponsor'),
                'name_sponsor',
                'sponsor',
                'prizes',
                'total_money_prizes',
                'total_product',
                'type_event_articles',
                'p.slug',
                'p.date_start_event',
                'p.date_end_event',
                'p.type_sponsor'
            )
            ->selectRaw("
                CONCAT(
                  FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0)/86400), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0),86400)/3600), 2, '0'), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0),3600)/60),  2, '0'), ':',
                  LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0),60),             2, '0')
                ) as time_left_dd_hh_mm_ss
            ")
            ->leftJoin('tbl_event_articles_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_event_articles', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->where(function ($query) use ($_id) {
                $query->where('p.id', '!=', $_id);
            })
            ->where(function ($query) use ($type_event_articles) {
                if(!empty($type_event_articles)) {
                    $query->where('p.type_event_articles', '=', $type_event_articles);
                }
            })
            ->where(function ($query) use ($search) {
                if(!empty($search)) {
                    $query->where('pt.name', 'like', "%" . $search. "%");
                }
            })
            ->where(function ($query) use ($status) {
                if(!empty($status)) {
                    if($status == 1) {
                        $query->where('date_start_event', '>', now());
                    }
                    else if($status == 2) {
                        $query->where('date_start_event', '<=', now());
                        $query->where('date_start_event', '>=', now());
                    }
                    else if($status == 3) {
                        $query->where('date_start_event', '<', now());
                    }
                }
            })
            ->where(function ($query) use ($id_product) {
                if(!empty($id_product)) {
                    $query->whereRaw('EXISTS (
                        SELECT 1
                        FROM tbl_event_articles_product
                        WHERE tbl_event_articles_product.id_event_articles = p.id
                        AND tbl_event_articles_product.id_product = '.$id_product.'
                    )');
                }
            });
        if (!empty($review_top)) {
            $EventArticles->orderBy('p.created_at', 'desc')
                ->orderBy('p.id', 'desc');
        }
        $EventArticles = $EventArticles->paginate($per_page, ['*'], 'page', $current_page);


        $items = $EventArticles->items();
        foreach ($items as $key => $item) {
            $statusNow = eventStatus($item->date_start_event, $item->date_end_event);
            $EventArticles->items()[$key]['status_now'] = $statusNow;
        }
        return response()->json($EventArticles);
    }

    public function api_list_detail($slug = '') {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $event_articles = EventArticles::from('tbl_event_articles as p')->where('p.slug', trim($slug))
            ->select(
                'p.id',
                'p.code',
                'p.background_color',
                'p.count_view',
                'p.count_join',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image) as image'),
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image_sponsor) as image_sponsor'),
                'name_sponsor',
                'sponsor',
                'prizes',
                'total_money_prizes',
                'total_product',
                'type_event_articles',
                'p.slug',
                'p.date_start_event',
                'p.date_end_event',
                'p.type_sponsor'
            )
            ->selectRaw("
                CONCAT(
                  FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0)/86400), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0),86400)/3600), 2, '0'), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0),3600)/60),  2, '0'), ':',
                  LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0),60),             2, '0')
                ) as time_left_dd_hh_mm_ss
            ")
            ->leftJoin('tbl_event_articles_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_event_articles', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })->first();

        if(empty($event_articles->id)) {
            return response()->json([
                'result' => false,
                'data' => []
            ]);
        }

        $event_articles->status_now = eventStatus($event_articles->date_start_event, $event_articles->date_end_event);

        $DataInfoEvent = EventArticlesInfoEvent::select('title', 'content', 'key_index', 'language')
            ->where('id_event_articles', $event_articles->id)
            ->orderBy('key_index', 'asc')
            ->where('language', $_locale)->get();
        $event_articles->info_event = $DataInfoEvent;

        $list_images = DB::table('tbl_event_articles_images')
            ->where('id_event_articles', $event_articles->id)
            ->orderBy('order_by', 'asc')
            ->orderBy('id', 'desc')->get();
        $keyImages = [];
        foreach($list_images as $key => $img) {
            $img->image = !empty($img->image) ? $this->baseUrl . '/' . $img->image : null;
            $keyImages[$key] = $img;
        }
        $event_articles->list_images = $list_images ?? [];


        $DataInfoProduct = DB::table('tbl_event_articles_product')
            ->where('id_event_articles', $event_articles->id)->get();
        $ListProduct = [];
        foreach($DataInfoProduct as $dataInfo) {
            $ListProduct[] = $dataInfo->id_product;
        }
        if(!empty($ListProduct)) {
            $dataProduct = $this->svAdmin->GetProducts($ListProduct, $_locale);
            $event_articles->list_product = $dataProduct['data'] ?? [];
        }

        $event_articlesGet = EventArticles::find($event_articles->id);
        $event_articlesGet->count_view = $event_articlesGet->count_view + 1;
        $event_articlesGet->save();


        return response()->json([
            'result' => true,
            'data' => $event_articles
        ]);
    }

    public function info_data_articles() {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $typeEventOne = EventArticles::where('type_event_articles', '1')->count();
        $typeEventTwo = EventArticles::where('type_event_articles', '2')->count();
        $type_event_articles = [
            '0' => [
                'id' => 0,
                'name' => lang('all'),
                'count' => ($typeEventOne + $typeEventTwo),
                'color' => '#FE6BBA'
            ],
            '1' => [
                'id' => 1,
                'name' => lang('event'),
                'count' => $typeEventOne,
                'color' => '#12AFF0'
            ],
            '2' => [
                'id' => 2,
                'name' => lang('challenge'),
                'count' => $typeEventTwo,
                'color' => '#2DD4BF'
            ],
        ];

        $countComing = EventArticles::where('date_start_event', '>', now())->count();
        $countHappening = EventArticles::where('date_start_event', '<=', now())
            ->where('date_end_event', '>=', now())->count();
        $countEnded = EventArticles::where('date_end_event', '<', now())->count();

        $ListStatus = [
            [
                'id' => 0,
                'name' => lang('all'),
                'color' => '#00b0f0',
                'count' => ($countComing + $countHappening + $countEnded)
            ],
            [
                'id' => 1,
                'name' => lang('coming_soon'),
                'color' => '#F47690',
                'count' => $countComing
            ],
            [
               'id' => 2,
               'name' => lang('happening'),
               'color' => '#2DD4BF',
               'count' => $countHappening
            ],
            [
                'id' => 3,
                'name' => lang('ended'),
                'color' => '#E5E7EB',
                'count' => $countEnded
            ]
        ];

        return response()->json([
            'result' => true,
            '_locale' => $_locale,
            'type_event_articles' => $type_event_articles,
            'list_status' => $ListStatus,
        ]);
    }

    public function info_data_articles_is_hot() {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $event_articles = EventArticles::from('tbl_event_articles as p')->where('p.is_hot', 1)
            ->select(
                'p.id',
                'p.code',
                'p.background_color',
                'p.count_view',
                'p.count_join',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image) as image'),
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image_sponsor) as image_sponsor'),
                'name_sponsor',
                'sponsor',
                'prizes',
                'total_money_prizes',
                'total_product',
                'type_event_articles',
                'p.slug',
                'p.date_start_event',
                'p.date_end_event',
                'p.type_sponsor'
            )
            ->selectRaw("
                CONCAT(
                  FLOOR(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0)/86400), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0),86400)/3600), 2, '0'), ':',
                  LPAD(FLOOR(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0),3600)/60),  2, '0'), ':',
                  LPAD(MOD(GREATEST(TIMESTAMPDIFF(SECOND, NOW(), p.date_start_event),0),60),             2, '0')
                ) as time_left_dd_hh_mm_ss
            ")
            ->leftJoin('tbl_event_articles_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_event_articles', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })->first();

        if(empty($event_articles->id)) {
            return response()->json([
                'result' => false,
                'data' => []
            ]);
        }
        $event_articles->status_now = eventStatus($event_articles->date_start_event, $event_articles->date_end_event);
        return response()->json([
            'result' => true,
            'data' => $event_articles
        ]);
    }

    public function get_list_data() {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $type_event_articles = $this->request->input('type_event_articles') ?? 0;
        $count_join = $this->request->input('count_join') ?? 0;
        $EventArticles = EventArticles::from('tbl_event_articles as p')
            ->select(
                'p.id',
                'p.code',
                'p.background_color',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image) as image'),
                'type_event_articles',
                'total_money_prizes as money_to_vnd',
            )
            ->leftJoin('tbl_event_articles_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_event_articles', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->where(function ($query) use ($type_event_articles) {
                if(!empty($type_event_articles)) {
                    $query->where('p.type_event_articles', '=', $type_event_articles);
                }
            });
        $items = $EventArticles->get();
        if(!empty($count_join)) {
            $listID = [];
            foreach ($items as $key => $item) {
                $listID[] = $item->id;
                $item->count_join = 0;
                $item->money_to_vnd = 0;
            }
            if(!empty($listID)) {
                $response = $this->accountsAdmin->getListID(
                    'api/challenge/countJoin',
                    ['ids' => $listID]
                );
                if (!empty($response['result']) && !empty($response['data'])) {
                    $dataCountJoin = $response['data'];

                    foreach ($items as $item) {
                        $item->count_join = $dataCountJoin[$item->id]['total'] ?? 0;
                        $item->money_to_vnd = $dataCountJoin[$item->id]['money_to_vnd'] ?? 0;
                    }
                }
            }
        }
        return response()->json([
            'result' => true,
            'data' => $items,
            'message' => 'Lấy dữ liệu thành công'
        ]);
    }

    public function get_list_by_ids() {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $ids = $this->request->input('ids') ?? 0;

        $EventArticles = EventArticles::from('tbl_event_articles as p')
            ->select(
                'p.id',
                'p.code',
                'p.slug',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image) as image'),
                'type_event_articles',
            )
            ->leftJoin('tbl_event_articles_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_event_articles', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->whereIn('p.id', $ids)->get();
        $items = [];
        if(!$EventArticles->empty()) {
            return response()->json([
                'result' => false,
                'data' => $items,
                'message' => 'Không có dữ liệu'
            ]);
        }
        foreach($EventArticles as $key => $value) {
            $items[$value->id] = $value;
        }
        return response()->json([
            'result' => true,
            'data' => $items,
            'message' => 'Lấy dữ liệu thành công'
        ]);
    }

    public function api_detail_to_app($id = '') {
        $_locale = $this->request->input('_locale');// ngôn ngữ
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $event_articles = EventArticles::from('tbl_event_articles as p')->where('p.id', $id)
            ->select(
                'p.id',
                'pt.name',
                'pt.content',
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image) as image'),
                'p.slug',
                'p.code',
                'p.date_start_event',
                'p.date_end_event',
                'p.background_color')
            ->leftJoin('tbl_event_articles_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_event_articles', '=', 'p.id')
                    ->where('pt.language', '=', $_locale);
            })->first();

        if(empty($event_articles->id)) {
            return response()->json([
                'result' => false,
                'data' => []
            ]);
        }

        $response = $this->accountsAdmin->getListID(
            'api/challenge/countJoin',
            ['ids' => [$event_articles->id]]
        );
        $event_articles->count_join = 0;
        $event_articles->money_to_vnd = 0;
        if (!empty($response['result']) && !empty($response['data'])) {
            $dataCountJoin = $response['data'];
            $event_articles->count_join = $dataCountJoin[$event_articles->id]['total'] ?? 0;
            $event_articles->money_to_vnd = $dataCountJoin[$event_articles->id]['money_to_vnd'] ?? 0;
        }

        $event_articles->status_now = eventStatus($event_articles->date_start_event, $event_articles->date_end_event);
        $event_articlesGet = EventArticles::find($event_articles->id);
        $event_articlesGet->count_view = $event_articlesGet->count_view + 1;
        $event_articlesGet->save();
        return response()->json([
            'result' => true,
            'data' => $event_articles
        ]);
    }

}
