<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ChallengeMeResources;
use App\Models\Clients;
use App\Models\Promotion;
use App\Models\Challenge;
use App\Models\ChallengeMe;
use App\Models\ChallengeContribute;
 use App\Models\ChallengeTranslations;
use App\Services\AdminService;
use App\Services\NotiService;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ChallengeController extends AuthController
{
    use UploadFile;
    protected $AdminService;
    protected $dbService;
    protected $adminNoti;
    protected $_locale;
    public function __construct(Request $request,AdminService $adminService,NotiService $notiService, ServiceService $ServiceService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->AdminService = $adminService;
        $this->adminNoti = $notiService;
        $this->dbService = $ServiceService;
        $this->_locale = $request->_locale;
    }

    public function getList(){
        $_locale = $this->_locale;
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');
        $status_search = $this->request->input('status_search');

        $query = Challenge::with(['transalations'])
            ->select('tbl_challenge.*')
            ->addSelect('tbl_rank_community.icon as icon_rank')
            ->addSelect('ptRank.name as name_rank_community')
            ->join('tbl_rank_community','tbl_rank_community.id','=','tbl_challenge.min_rank_join')
            ->leftJoin('tbl_rank_community_translations as ptRank',function($join) use ($_locale){
                $join->on('ptRank.id_rank','=','tbl_rank_community.id');
                $join->where('ptRank.language', $_locale);
            })
            ->where('tbl_challenge.id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)) {
            $list_id_event_articles = [];
            foreach ($data as $key => $value) {
                $dtIcon = !empty($value->icon) ? env('STORAGE_URL').'/'.$value->icon : null;
                $data[$key]['icon'] = $dtIcon;

                $dtIconRank = !empty($value->icon_rank) ? env('STORAGE_URL').'/'.$value->icon_rank : null;
                $data[$key]['icon_rank'] = $dtIconRank;
                if(!empty($value->id_event_articles)) {
                    $list_id_event_articles[] = $value->id_event_articles;
                }
            }

            if(!empty($list_id_event_articles)) {
                $list_id_event_articles = array_unique($list_id_event_articles);
                $listEventArticles = $this->dbService->get_list_by_ids('api/event_articles/get_list_by_ids', array_unique($list_id_event_articles), $_locale);
                $listEventArticles = $listEventArticles->getData(true);
                $event_articles = $listEventArticles['data'] ?? [];
                foreach ($data as $key => $value) {
                    if(!empty($value->id_event_articles)) {
                        $value->event_articles = $event_articles[$value->id_event_articles] ?? null;
                    }
                }
            }
        }
        $total = Challenge::count();
        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getDetail(){
        $id = $this->request->input('id') ?? 0;
        $dtData = Challenge::with(['transalations'])->find($id);
        if (!empty($dtData)){
//            $dtImage = !empty($dtData->image) ? env('STORAGE_URL').'/'.$dtData->image : null;
//            $dtData->image = $dtImage;

            $dtIcon = !empty($dtData->icon) ? env('STORAGE_URL').'/'.$dtData->icon : null;
            $dtData->icon = $dtIcon;

            $dtBG = !empty($dtData->background) ? env('STORAGE_URL').'/'.$dtData->background : null;
            $dtData->background = $dtBG;

            $translations = $dtData->transalations ?? [];
            $data_translations = [];
            foreach ($translations as $translation) {
                $data_translations[$translation->language] = [
                    'name' => $translation->name,
                    'content_conditions' => $translation->content_conditions,
                ];
            }
            $dtData->translations = $data_translations;
        }
        $data['result'] = true;
        $data['data'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }

    public function detail(){
        $id = $this->request->input('id') ?? 0;
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required',
                'id_event_articles' => 'required'
            ],
            [
                'name.required' => lang('dt_input_name'),
                'id_event_articles.required' => lang('dt_input_event_articles'),
            ]);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            return response()->json($data);
        }
        $errors = '';
        $LanguagDefault = json_decode($this->request->input('LanguagDefault'));
        $code_lang = 'vi';
        if (!empty($LanguagDefault)){
            $code_lang = $LanguagDefault->code;
        }
        if (empty($id)) {
            $dtData = new Challenge();
        } else {
            $dtData = Challenge::find($id);
        }
        DB::beginTransaction();
        try {
            $name = !empty($this->request->input('name')) ? is_array($this->request->input('name')) ? $this->request->input('name') : (array)json_decode($this->request->input('name')) : [];
            $content_conditions = !empty($this->request->input('content_conditions')) ? is_array($this->request->input('content_conditions')) ? $this->request->input('content_conditions') : (array)json_decode($this->request->input('content_conditions')) : [];
            $dtData->name = $name[$code_lang] ?? '';
            $dtData->days = $this->request->input('days') ?? 0;
            $dtData->limit_join = $this->request->input('limit_join') ?? 0;
            $dtData->min_rank_join = $this->request->input('min_rank_join') ?? 0;
            $dtData->coin_success = $this->request->input('coin_success') ?? 0;
            $dtData->quantity_verification = $this->request->input('quantity_verification') ?? 0;
            $dtData->type = $this->request->input('type') ?? 2;
            $dtData->id_event_articles = $this->request->input('id_event_articles') ?? 2;
            if($dtData->type == 1) {
                $dtData->id_product = $this->request->input('id_product') ?? 0;
            }
            else {
                $dtData->id_product = 0;
            }
            $dtData->save();
            if ($dtData) {

                if ($this->request->hasFile('icon')) {
                    if (!empty($dtData->icon)) {
                        $this->deleteFile($dtData->icon);
                    }
                    $path = $this->UploadFile($this->request->file('icon'), 'challenge/' . $dtData->id, 70, 70, false);
                    $dtData->icon = $path;
                    $dtData->save();
                }
                if ($this->request->hasFile('background')) {
                    if (!empty($dtData->background)) {
                        $this->deleteFile($dtData->background);
                    }
                    $pathBG = $this->UploadFile($this->request->file('background'), 'challenge/' . $dtData->id, 70, 70, false);
                    $dtData->background = $pathBG;
                    $dtData->save();
                }

                if (!empty($name)){
                    foreach($name as $language => $value) {
                        DB::table('tbl_challenge_translations')->updateOrInsert(
                            [
                                'id_challenge' => $dtData->id,
                                'language' => $language
                            ],
                            [
                                'name' => $value,
                                'content_conditions' => $content_conditions[$language],
                            ]
                        );
                    }
                }
                DB::commit();
                $data['result'] = true;
                if (empty($id)) {
                    $data['message'] = lang('add_success');
                } else {
                    $data['message'] = lang('update_success');
                }
            } else {
                $data['result'] = false;
                if (empty($id)) {
                    $data['message'] = lang('add_fail');
                } else {
                    $data['message'] = lang('update_fail');
                }
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeStatus(){
        $id = $this->request->input('id') ?? 0;
        $status = $this->request->input('status') ?? 0;
        $challenge = Challenge::find($id);
        DB::beginTransaction();
        try {
            $challenge->status = $status ?? 0;
            $challenge->save();
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


    public function get_list_status() {
        $data = [
            [
                'key' => -1,
                'value' => lang('all')
            ],
            [
                'key' => 0,
                'value' => lang('challenge_status_not_joined')
            ],
            [
                'key' => 1,
                'value' => lang('challenge_status_in_progress')
            ],
            [
                'key' => 2,
                'value' => lang('challenge_status_completed')
            ],
        ];
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => lang('get_data_success')
        ]);
    }

    public function get_list_challenge_short()
    {
        $_locale = $this->_locale;
        $isFull = $this->request->input('isfull') ?? false;// lấy đầy đủ thông tin khi type = 2
        $query = Challenge::where('tbl_challenge.id','!=', 0)
        ->select('tbl_challenge.id',
            'pt.name as name',
            'pt.language',
            'tbl_challenge.type',
            'tbl_challenge.id_product',
            DB::raw("CONCAT('".env('STORAGE_URL')."/',tbl_challenge.icon) as icon")
        )
        ->leftJoin('tbl_challenge_translations as pt',function($join) use ($_locale){
            $join->on('pt.id_challenge','=','tbl_challenge.id');
            $join->where('pt.language', $_locale);
        })->whereRaw('tbl_challenge.status > 0');

        $query->orderBy('tbl_challenge.id', 'desc');
        $data = $query->get();
        if(!empty($isFull)) {
            $listProduct = [];
            foreach($data as $key => $value){
                if($value->type == 1) {
                    $listProduct[] = $value->id_product;
                }
            }
            if(!empty($listProduct)) {
                $RequestDataProduct = $this->AdminService->getListDataProductID($listProduct, $this->request);
                $dataProduct = $RequestDataProduct->getData(true);
            }
            if(!empty($dataProduct['data'])) {
                foreach ($data as $key => $value) {
                    if ($value->type == 1) {
                        if(!empty($dataProduct['data'][$value->id_product]['image'])) {
                            $value->icon = $dataProduct['data'][$value->id_product]['image'] ?? null;
                        }
                    }
                }
            }
        }

        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => lang('get_data_success')
        ]);
    }


    public function get_list_challenge() {
        //        app(\App\Http\Middleware\CheckLoginApi::class);
        $id_client = !empty($this->request->client) ? $this->request->client->id : 0;
        $status = $this->request->input('status');
        if($status === null){
            $status = -1;
        }

        if(!empty($id_client)){
            $client = Clients::find($id_client);
            $rank_client = $client->rank_community ?? 0;
        }

        $_locale = $this->_locale;
        $type = $this->request->input('type') ?? 2;
        $query = Challenge::where('tbl_challenge.id','!=', 0)
            ->select('tbl_challenge.*',
                'pt.name as name',
                'pt.content_conditions as content_conditions',
                'pt.language',
                'ptRank.name as name_rank_community',
                DB::raw("CONCAT('".env('STORAGE_URL')."/',tbl_challenge.icon) as icon")
            )
            ->leftJoin('tbl_challenge_translations as pt',function($join) use ($_locale){
                $join->on('pt.id_challenge','=','tbl_challenge.id');
                $join->where('pt.language', $_locale);
            })
            ->leftJoin('tbl_rank_community_translations as ptRank',function($join) use ($_locale){
                $join->on('ptRank.id_rank','=','tbl_challenge.min_rank_join');
                $join->where('ptRank.language', $_locale);
            })
            ->where('tbl_challenge.status', 1)
            ->where('tbl_challenge.type', $type);
        $search = $this->request->input('search');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('pt.name', 'like', "%$search%");
            });
        }
        if (!empty($id_client)) {
            $query->addSelect(DB::raw("
                CASE
                    WHEN tbl_challenge.min_rank_join <= {$rank_client} THEN 0
                    ELSE 1
                END as is_block
            "));

            $query->leftJoin('tbl_challenge_me as tcm', function ($join) use ($id_client) {
                $join->on('tcm.id_challenge', '=', 'tbl_challenge.id')
                    ->where('tcm.client_id', $id_client)
                    ->where('tcm.status', '=', 0);
                })
                ->addSelect(DB::raw('COALESCE(tcm.status, -1) as status_join'))
                ->addSelect(DB::raw('  DATEDIFF(
                                                DATE_ADD(tcm.created_at, INTERVAL days DAY),
                                                CURDATE()
                                            ) AS days_left'))
                ->addSelect('tcm.haru_xu')
                ->addSelect('tcm.id as id_challenge_me');



            $submissionsTodaySub = DB::table('challenge_me_submissions')
                ->select(
                    'challenge_me_id',
                    DB::raw('COUNT(*) as submissions_today')
                )
                ->whereBetween('created_at', [
                    now()->startOfDay(),
                    now()->endOfDay()
                ])
                ->groupBy('challenge_me_id');


            $query->leftJoinSub($submissionsTodaySub, 'submissionsDay', function ($join) {
                $join->on('submissionsDay.challenge_me_id', '=', 'tcm.id');
            })->addSelect(DB::raw('COALESCE(submissionsDay.submissions_today, 0) as submissions_today'));

            if($status == 0) {
                $query->where(function($q) {
                    $q->whereRaw('COALESCE(tcm.status, -1) = -1');
                });
            }
            else if($status == 1) {
                $query->where(function($q) {
                    $q->whereRaw('COALESCE(tcm.status, -1) = 0');
                });
            }
            else if($status == 2) {
                $query->where(function($q) {
                    $q->whereRaw('COALESCE(tcm.status, -1) = 1');
                });
            }

        }
        $query->orderBy('tbl_challenge.id', 'desc');
        $data = $query->get();

        $listProduct = [];
        foreach($data as $key => $value){

            if(empty($value->limit_join)) {
                $value->lost_join = -1;
            }
            else {
                $value->lost_join = !empty($value->limit_join) ? ($value->limit_join - ($value->quantity_joined ?? 0)) : -1;
            }

            if($value->status_join == 1) {
                $value->content_success = lang('wallet_add_xu_success', 'message', ['haru_xu' => $value->haru_xu]);
            }

            $value->content_conditions = str_replace(
                '{challenges_next_rank}',
                $value->challenges_next_rank,
                $value->content_conditions
            );
            if($value->type == 1) {
                $listProduct[] = $value->id_product;
            }
        }
        if(!empty($listProduct)) {
            $RequestDataProduct = $this->AdminService->getListDataProductID($listProduct, $this->request);
            $dataProduct = $RequestDataProduct->getData(true);
        }
        if(!empty($dataProduct['data'])) {
            foreach ($data as $key => $value) {
                if ($value->type == 1) {
                    $value->product = $dataProduct['data'][$value->id_product] ?? null;
                }
            }
        }

        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => lang('get_data_success')
        ]);
    }

    public function get_list_challenge_join()
    {
        $id_client = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($id_client)) {
            return response()->json([
                'data' => [],
                'result' => false,
                'message' => lang('need_login')
            ]);
        }
        $_locale = $this->_locale;
        $query = ChallengeMe::from('tbl_challenge_me as tcm')
            ->select(
                'tcm.id as id_challenge_me',
                'tcm.id',
                'tcm.deposit',
                'tcm.id_challenge',
                'tcm.completion_rate',
                'tcm.date_challenge',
                'tcm.status',
                'tcm.haru_xu',
                'c.type',
                'c.id_event_articles',
                'pt.name as name',
                'pt.content_conditions',
                'pt.language',
                'c.coin_success',
                //                'ptRank.name as name_rank_community',
                DB::raw("CONCAT('" . env('STORAGE_URL') . "/', c.icon) as icon"),
                DB::raw('tcm.status as status_join'),
                DB::raw('DATEDIFF(
                    DATE_ADD(tcm.created_at, INTERVAL c.days DAY),
                    CURDATE()
                ) as days_left'),
                DB::raw('CASE
                                WHEN DATEDIFF(tcm.date_challenge, CURDATE()) <= 2 THEN 1
                                ELSE 0
                            END AS is_expire_soon')
            )
            ->join('tbl_challenge as c', function ($join) {
                $join->on('c.id', '=', 'tcm.id_challenge')
                    ->where('c.status', 1);
            })
            ->leftJoin('tbl_challenge_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_challenge', '=', 'c.id')
                    ->where('pt.language', $_locale);
            })
            //            ->leftJoin('tbl_rank_community_translations as ptRank', function ($join) use ($_locale) {
            //                $join->on('ptRank.id_rank', '=', 'c.min_rank_join')
            //                    ->where('ptRank.language', $_locale);
            //            })
            ->where('tcm.client_id', $id_client)
            ->whereIn('tcm.status', [0]) // 🔥 đang tham gia
            ->orderBy('days_left', 'asc')
            ->orderByRaw('(DATEDIFF(CURDATE(), tcm.created_at) / c.days) DESC')
            ->orderBy('tcm.created_at', 'desc');

        $submissionsTodaySub = DB::table('challenge_me_submissions')
            ->select(
                'challenge_me_id',
                DB::raw('COUNT(*) as submissions_today')
            )
            ->whereBetween('created_at', [
                now()->startOfDay(),
                now()->endOfDay()
            ])
            ->groupBy('challenge_me_id');


        $query->leftJoinSub($submissionsTodaySub, 'submissionsDay', function ($join) {
            $join->on('submissionsDay.challenge_me_id', '=', 'tcm.id');
        })
            ->addSelect(DB::raw('COALESCE(submissionsDay.submissions_today, 0) as submissions_today'));

        $data = $query->get();
        foreach($data as $key => $value){
            if($value->status_join == 1) {
                $value->content_success = lang('wallet_add_xu_success', 'message', ['haru_xu' => $value->haru_xu]);
            }
            $value->content_conditions = str_replace(
                '{challenges_next_rank}',
                $value->challenges_next_rank,
                $value->content_conditions
            );
        }


        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => lang('get_data_success')
        ]);
    }

    public function detail_challenge($id_challenge = 0)
    {
        $id_client = !empty($this->request->client) ? $this->request->client->id : 0;
        if(!empty($id_client)){
            $client = Clients::find($id_client);
            $rank_client = $client->rank_community ?? 0;
        }

        $_locale = $this->_locale;
        $query = Challenge::where('tbl_challenge.id', '=', $id_challenge)
            ->select('tbl_challenge.*',
                'pt.name as name',
                'pt.content_conditions as content_conditions',
                'pt.language',
                'ptRank.name as name_rank_community',
                DB::raw("CONCAT('".env('STORAGE_URL')."/',tbl_challenge.icon) as icon"),
                DB::raw("CONCAT('".env('STORAGE_URL')."/',tbl_challenge.background) as background")
            )
            ->leftJoin('tbl_challenge_translations as pt',function($join) use ($_locale){
                $join->on('pt.id_challenge','=','tbl_challenge.id');
                $join->where('pt.language', $_locale);
            })
            ->leftJoin('tbl_rank_community_translations as ptRank',function($join) use ($_locale){
                $join->on('ptRank.id_rank','=','tbl_challenge.min_rank_join');
                $join->where('ptRank.language', $_locale);
            })
            ->where('tbl_challenge.status', 1);
        $search = $this->request->input('search');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('pt.name', 'like', "%$search%");
            });
        }
        if (!empty($id_client)) {
            $query->addSelect(DB::raw("
                CASE
                    WHEN tbl_challenge.min_rank_join <= {$rank_client} THEN 0
                    ELSE 1
                END as is_block
            "));
            $query->leftJoin('tbl_challenge_me as tcm', function ($join) use ($id_client) {
                $join->on('tcm.id_challenge', '=', 'tbl_challenge.id')
                    ->where('tcm.client_id', $id_client)
                    ->where('tcm.status', '=', 0);
            })
                ->addSelect(DB::raw('COALESCE(tcm.status, -1) as status_join'))
                ->addSelect(DB::raw('  DATEDIFF(
                                                tcm.date_challenge,
                                                CURDATE()
                                            ) AS days_left'))
                ->addSelect(DB::raw('COALESCE(haru_xu, 0) as haru_xu'))
                ->addSelect(DB::raw('COALESCE(coin_success, 0) as coin_success'));

        }
        $data = $query->first();
        if(!empty($data->id)) {
            $listProduct = [];

            if($data->type == 1) {
                unset($data->background);
            }

            if(empty($data->limit_join)) {
                $data->lost_join = -1;
            }
            else {
                $data->lost_join = !empty($data->limit_join) ? ($data->limit_join - ($data->quantity_joined ?? 0)) : -1;
            }

            $data->benefit = lang('wallet_add_xu_success_before', 'message', ['haru_xu' => $data->coin_success]);

            $data->content_conditions = str_replace(
                '{challenges_next_rank}',
                $data->challenges_next_rank,
                $data->content_conditions
            );
            if($data->type == 1) {
                $listProduct[] = $data->id_product;
            }
            if(!empty($listProduct)) {
                $RequestDataProduct = $this->AdminService->getListDataProductID($listProduct, $this->request);
                $dataProduct = $RequestDataProduct->getData(true);
                $data->product = $dataProduct['data'][$data->id_product] ?? null;
            }

            $RequestDataToJoin = $this->AdminService->GetUrlSetings('api/how_to_join', [
                '_locale' => $this->_locale,
                'type' => $data->type == 2 ? 'daily' :'trademark',
                'haru_xu' => $data->coin_success,
            ]);
            $dataGetJoin = $RequestDataToJoin['data'] ?? null;
            $data->how_to_join = $dataGetJoin['how_to_join'];


            $RequestDataPolicy = $this->AdminService->GetUrlSetings('api/policy', [
                '_locale' => $this->_locale,
                'id_event_articles' => $data->id_event_articles,
            ]);
            $dataGetPolicy = $RequestDataPolicy['data'] ?? null;
            $data->policy = $dataGetPolicy;

            return response()->json([
                'title' => lang('notification'),
                'data' => $data,
                'result' => true,
                'message' => lang('get_data_success')
            ]);
        }
        return response()->json([
            'result' => false,
            'title' => lang('notification'),
            'message' => lang('challenge_not_exist')
        ]);
    }

    //api lấy lịch sử đặt cọc và tham gia thử thách
    public function history_challenge_payment() {
        $id_client = !empty($this->request->client) ? $this->request->client->id : 0;
        $_locale = $this->_locale;

        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }


        $query = DB::table('tbl_history_payment_challenge  as tcm')
            ->select(
                'tcm.id',
                //                'tcm.deposit',
                'tcm.id_challenge',
                //                'tcm.completion_rate',
                //                'tcm.date_challenge',
                'tcm.created_at',
                'tcm.money',
                //                'tcm.haru_xu',
                'pt.name as name',
                //                'pt.content_conditions',
                'pt.language',
            )
            ->where('id_client', $id_client)
            ->leftJoin('tbl_challenge_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_challenge', '=', 'tcm.id_challenge')
                    ->where('pt.language', $_locale);
            })->orderBy('id', 'desc');


        $history_challeng_me = $query->paginate($per_page, ['*'], 'page', $current_page);

        $dataResult = [
            'result' => true,
            'data' => $history_challeng_me,
            'title' => lang('notification'),
            'message' => lang('get_data_success')
        ];
        if($current_page == 1) {
            $dataResult['summary']['total_deposit_payment'] = (clone $query)->sum('tcm.money');
            $dataResult['summary']['count_all'] = (clone $query)->count();
        }

        return response()->json($dataResult);
    }


    //thống kê dữ liệu
    public function statistical() {
        $id = !empty($this->request->id) ? $this->request->id : 0;
        $_locale = $this->_locale;
        try {
            $dataResult = [];
            $dataResult['all'] = Challenge::where('status', 1)
                ->count();

            $baseQuery = ChallengeMe::where(function($q) use ($id) {
                if(!empty($id)) {
                    $q->where('id_challenge', $id);
                }
            });

            $dataResult['deposit'] = (clone $baseQuery)->sum('deposit');

            $dataResult['donations'] = (clone $baseQuery)
                ->where('status', 2)
                ->sum('deposit');

            $dataResult['join'] = (clone $baseQuery)
                ->distinct('client_id')
                ->count('client_id');

            $dataResult['success'] = (clone $baseQuery)
                ->where('status', 1)
                ->count();

            if(empty($id)) {
                //TOP 4 XÁC THỰC NHIỀU NHẤT TRONG TUẦN
                $start = Carbon::now()->startOfWeek()->format('Y-m-d');
                $end = Carbon::now()->endOfWeek()->format('Y-m-d');
                $topVerifications = DB::table('challenge_me_submissions as cms')->join(
                    'tbl_challenge_me as cm',
                    'cm.id',
                    '=',
                    'cms.challenge_me_id'
                )->join('tbl_challenge as c', 'c.id', '=', 'cm.id_challenge')->leftJoin(
                    'tbl_challenge_translations as pt',
                    function ($join) use ($_locale) {
                        $join->on('pt.id_challenge', '=', 'c.id');
                        $join->where('pt.language', $_locale);
                    }
                )->whereBetween('cms.created_at', [$start, $end])->selectRaw(
                    '
                        c.id as challenge_id,
                        COALESCE(pt.name, c.name) as name,
                        COUNT(cms.id) as total_verify
                    '
                )->groupBy('c.id', DB::raw('COALESCE(pt.name, c.name)'))->orderByDesc('total_verify')->limit(4)->get();
                $topTotal = $topVerifications->sum('total_verify');
                $accPercent = 0;
                $listColor = [
                    '#3086EF', '#4CAF50', '#F5A623',  '#9B59B6'
                ];
                $dataResult['top_verify'] = $topVerifications->values()->map(
                    function ($item, $index) use ($topTotal, &$accPercent, $topVerifications, $listColor) {
                        if ($index === $topVerifications->count() - 1) {
                            $percent = 100 - $accPercent;
                        } else {
                            $percent = $topTotal > 0 ? round(($item->total_verify / $topTotal) * 100) : 0;
                            $accPercent += $percent;
                        }
                        return [
                            'id' => $item->challenge_id,
                            'title' => $item->name,
                            'total_verify' => $item->total_verify,
                            'percent' => $percent,
                            'color' => $listColor[$index]
                        ];
                    }
                );
                //END TOP 4 XÁC THỰC NHIỀU NHẤT TRONG TUẦN
                //BIỂU ĐỒ XÁC THỰC TRONG TUẦN
                $startWeek = Carbon::now()->startOfWeek(); // Thứ 2
                $endWeek = Carbon::now()->endOfWeek();   // Chủ nhật
                $days = [];
                for ($i = 0; $i < 7; $i++) {
                    $days[] = $startWeek->copy()->addDays($i)->format('Y-m-d');
                }
                $verifyByDay = DB::table('challenge_me_submissions')->selectRaw(
                    'DATE(created_at) as day, COUNT(*) as total'
                )->whereBetween('created_at', [
                    $startWeek->format('Y-m-d 00:00:00'),
                    $endWeek->format('Y-m-d 23:59:59')
                ])->groupBy(DB::raw('DATE(created_at)'))->pluck('total', 'day');
                $weeklyChart = [];
                $totalPercent = 0;
                foreach ($days as $date) {
                    // số xác thực trong ngày (nếu không có = 0)
                    $verify = $verifyByDay[$date] ?? 0;
                    // số challenge còn hạn tại ngày đó
                    $activeChallenge = DB::table('tbl_challenge_me')->whereDate('created_at', '<=', $date)->whereDate(
                        'date_challenge',
                        '>=',
                        $date
                    )->count();
                    // tính %
                    $percent = $activeChallenge > 0 ? round(($verify / $activeChallenge) * 100) : 0;
                    $nameDay = lang(Carbon::parse($date)->locale('vi')->isoFormat('ddd'));
                    $weeklyChart[] = [
                        'date' => $date,
                        'day_label' => $nameDay,
                        'total_verify' => $verify,
                        'active_challenge' => $activeChallenge,
                        'percent' => $percent
                    ];
                    $totalPercent += $percent;
                }
                $dataResult['weekly_verify_chart'] = $weeklyChart;
                $dataResult['weekly_verify_medium'] = round($totalPercent / 7);
                //END BIỂU ĐỒ XÁC THỰC TRONG TUẦN
            }

            $data['data'] = $dataResult;
            $data['result'] = true;
            $data['message'] = lang('get_data_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function history_join_challenge() {
        $client_id = $this->request->client->id ?? 0;
        $_locale = $this->_locale ?? 'vi';
        $status = $this->request->input('status', 1);
        $exchange_point_now = $this->AdminService->get_option('exchange_rate_haru_wallet') ?? 1;
        $dataHistory = ChallengeMe::select(
            'tbl_challenge_me.id',
            'pt.name as name',
            'pt.content_conditions as content_conditions',
            'pt.language',
            'tbl_challenge_me.date_status',
            'tbl_challenge_me.total_haru_xu as all_haru_xu',
            'tbl_challenge_me.deposit as deposit',
            'tbl_challenge_me.created_at as created_at',
            'tbl_challenge.id_event_articles as id_event_articles',
        )->where('client_id', $client_id)->leftJoin('tbl_challenge', function ($join) use ($_locale) {
            $join->on('tbl_challenge_me.id_challenge', '=', 'tbl_challenge.id');;
        })->leftJoin('tbl_challenge_translations as pt', function ($join) use ($_locale) {
            $join->on('pt.id_challenge', '=', 'tbl_challenge.id');
            $join->where('pt.language', $_locale);
        })->where('tbl_challenge_me.status', $status)->when(empty($status), function ($q) {
            $q->addSelect(DB::raw('DATEDIFF(tbl_challenge_me.date_challenge, CURDATE()) AS days_left'));
        })->when(!empty($status), function ($q) use ($status, $exchange_point_now) {
            if ($status == 2) {
                $q->addSelect(DB::raw('(deposit * (-1)) AS money_to_vnd'));
            } else {
                if ($status == 1) {
                    $q->addSelect(
                        DB::raw(
                            DB::raw("(tbl_challenge_me.total_haru_xu * $exchange_point_now) as money_to_vnd")
                        )
                    );
                }
            }
        });

        if($status == 2) {
            $listIds = ChallengeMe::query()
                ->join('tbl_challenge', 'tbl_challenge.id', '=', 'tbl_challenge_me.id_challenge')
                ->where('tbl_challenge_me.client_id', $client_id)
                ->where('tbl_challenge_me.status', 2)
                ->pluck('tbl_challenge.id_event_articles')
                ->filter()
                ->unique()
                ->values()
                ->all();
            if(!empty($listIds)) {
                $listIds = array_unique($listIds);
                $listEventArticles = $this->dbService->get_list_by_ids('api/event_articles/get_list_by_ids', $listIds, $_locale);
                $listEventArticles = $listEventArticles->getData(true);
                $event_articles = $listEventArticles['data'] ?? [];
            }


            $_dataHistory = [];
            $total_haru_xu = 0;
            $total_money_to_vnd = 0;
            $contentFail = $this->AdminService->get_option('when_fail_challenge_' . $_locale) ?? '';


            foreach($event_articles as $key => $value) {
                $dataHistoryClone = clone $dataHistory;
                $dataFiltered = $dataHistoryClone->where('id_event_articles', $value['id'])->get();
                $total_money_to_vnd_clone = $dataFiltered->sum('money_to_vnd') * (-1);

                $href_articles = 'https://maskforyou.vn/vi/event/'.($value['slug'] ?? '');
                $contentFail = str_replace('{event_articles}', ($value['name'] ?? ''), $contentFail);
                $contentFail = str_replace('/admin/{href_articles}', $href_articles, $contentFail);
                $contentFail = str_replace('{href_articles}', $href_articles, $contentFail);

                $_dataHistory[] = [
                    'event_articles' => $value,
                    'history' => $dataFiltered,
                    'total_money_to_vnd' => $total_money_to_vnd_clone ?? 0,
                    'content_fail' => lang('with_challenge_false') . ', ' . $contentFail ?? '',
                ];
                $total_money_to_vnd += $total_money_to_vnd_clone;
            }
            $dataResult = [
                'result' => true,
                'data' => $_dataHistory,
                'total_money_to_vnd' => $total_money_to_vnd ?? 0,
                'title' => lang('notification'),
                'message' => lang('get_data_success')
            ];
            return response()->json($dataResult);
        }
        else {
            $dataHistory = $dataHistory->get();
            $total_haru_xu = $dataHistory->sum('all_haru_xu');
            $dataResult = [
                'result' => true,
                'data' => $dataHistory,
                'total_haru_xu' => $total_haru_xu ?? 0,
                'title' => lang('notification'),
                'message' => lang('get_data_success')
            ];
            return response()->json($dataResult);
        }

    }

    //lấy danh sách người dùng đã ủng hộ chiến dịch quyên góp
    public function get_client_join($id_event_articles = 0) {
        $_locale = $this->_locale;
        $date_start = $this->request->query('date_start', null);
        $date_end = $this->request->query('date_end', null);
        $currentPage = max(1, (int)$this->request->query('current_page', 1));
        $perPage = max(1, (int)$this->request->query('per_page', 20));
        $langName = lang('c_bgop_tt');
        $paginator = ChallengeContribute::from('tbl_challenge_contribute as tcm')
            ->select(
                'client.fullname',
                'tcm.money as deposit',
                'c.id as id_challenge_me',
                'tcm.created_at as date_status',
                'pt.language',
                DB::raw("CONCAT('" . env('STORAGE_URL') . "/', client.avatar) as avatar"),
            )->selectRaw(
                "CASE
                    WHEN tcm.type = 2 THEN ?
                    ELSE pt.name
                 END AS name",
                [$langName]
            )
            ->leftJoin('tbl_challenge as c', function ($join) {
                $join->on('c.id', '=', 'tcm.id_challenge');
            })
            ->join('tbl_clients as client', function ($join) {
                $join->on('client.id', '=', 'tcm.client_id');
            })
            ->leftJoin('tbl_challenge_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_challenge', '=', 'c.id')
                    ->where('pt.language', $_locale);
            })
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start)) {
                    $q->whereDate('tcm.created_at', '>=', $date_start);
                }
                if(!empty($date_end)) {
                    $q->whereDate('tcm.created_at', '<=', $date_end);
                }
            })
//            ->where('tcm.status', 2)
            ->where('tcm.id_event_articles', $id_event_articles)
            ->orderBy('tcm.created_at', 'desc')
            ->simplePaginate(
                $perPage,
                ['*'],
                'page',
                $currentPage
            );

        return response()->json([
            'result' => true,
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'next' => $paginator->hasMorePages(),
        ]);
    }

    //lấy số người tham gia và số tiền đã góp theo danh sách sự kiện
    public function countJoin() {
        $ids = $this->request->input('ids');
        if(!empty($ids)) {
            $listCount = ChallengeContribute::from('tbl_challenge_contribute as tcm')
                ->leftJoin(
                    'tbl_challenge as c',
                    'c.id',
                    '=',
                    'tcm.id_challenge'
                )->whereIn('tcm.id_event_articles', $ids)->select(
                    'c.id_event_articles',
                    DB::raw('COUNT(DISTINCT tcm.client_id) as total'),
                    DB::raw('SUM(tcm.money) as money_to_vnd')
                )->groupBy('c.id_event_articles')->get()->keyBy('id_event_articles')->map(function ($row) {
                    return [
                        'total' => $row->total,
                        'money_to_vnd' => $row->money_to_vnd,
                    ];
                })->toArray();
            return response()->json([
                'result' => true,
                'data' => $listCount ?? [],
                'message' => lang('get_data_success')
            ]);
        }
        else {
            return response()->json([
                'result' => false,
                'data' => $listCount ?? [],
                'message' => lang('get_data_fail')
            ]);
        }

    }

    public function contribute() {

        $_locale = $this->_locale;
        $id_event_articles = $this->request->input('id_event_articles', 0);
        $id_client = $this->request->client->id ?? 0;
        $haru_xu = (float)$this->request->input('haru_xu', 0);
        if(empty($id_client)) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => lang('vui_long_dang_nhap_truoc')
            ]);
        }
        if(empty($id_event_articles)) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => lang('pls_choose_event_articles')
            ]);
        }
        if(empty($haru_xu)) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => lang('pls_min_xu')
            ]);
        }

        $dtClient = Clients::find($id_client);
        $point_client = $dtClient->point;
        if($point_client < $haru_xu) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => lang('not_enough_xu_wallet', 'message', ['haru_xu' => $haru_xu])
            ]);
        }

        $exchange_point_now = $this->AdminService->get_option('exchange_rate_haru_wallet') ?? 1;
        $contribute = new ChallengeContribute();
        $contribute->id_event_articles = $id_event_articles;
        $contribute->id_challenge = 0;
        $contribute->id_challenge_me = 0;
        $contribute->type = 2;
        $contribute->client_id = $id_client;
        $contribute->exchange_point_now = $exchange_point_now;
        $contribute->total_haru_xu = $haru_xu;
        $contribute->money = $haru_xu * $exchange_point_now;
        $contribute->save();

        if(!empty($contribute)) {
            changePoint($contribute->id, 'contribute');
            if(!empty([$id_event_articles])) {
                $list_id_event_articles = [$id_event_articles];
                $listEventArticles = $this->dbService->get_list_by_ids('api/event_articles/get_list_by_ids', $list_id_event_articles, $_locale);
                $listEventArticles = $listEventArticles->getData(true);
                $event_articles = $listEventArticles['data'][$id_event_articles] ?? [];
            }
            return response()->json([
                'result' => true,
                'title' => lang('notification'),
                'message' => lang('c_thank_you_contribute', 'message', [
                    'name_event_articles' => $event_articles['name'] ?? '',
                    'total_haru_xu' => $haru_xu
                ])
            ]);
        }
        return response()->json([
            'result' => false,
            'title' => lang('notification'),
            'message' => lang('c_contribute_false')
        ]);
    }
}
