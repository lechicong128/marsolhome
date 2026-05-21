<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\PromotionResources;
use App\Models\Promotion;
use App\Models\Clients;
use App\Models\PromotionCustomer;
use App\Models\PromotionTranslations;
use App\Models\ReferralLevel;
use App\Models\RankCommunity;
use App\Services\AdminService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Dropbox\Client;

class RankCommunityController extends AuthController
{
    protected $AdminService;
    protected $_locale;
    use UploadFile;
    public function __construct(Request $request,AdminService $adminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->AdminService = $adminService;
        $this->_locale = $request->_locale;
    }

    public function getList(){
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');
        $status_search = $this->request->input('status_search');

        $query = RankCommunity::with(['transalations'])->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtImage = !empty($value->image) ? env('STORAGE_URL').'/'.$value->image : null;
                $data[$key]['image'] = $dtImage;

                $dtIcon = !empty($value->icon) ? env('STORAGE_URL').'/'.$value->icon : null;
                $data[$key]['icon'] = $dtIcon;

            }
        }
        $total = RankCommunity::count();
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
        $dtData = RankCommunity::with(['transalations'])->find($id);
        if (!empty($dtData)){
            $dtImage = !empty($dtData->image) ? env('STORAGE_URL').'/'.$dtData->image : null;
            $dtData->image = $dtImage;

            $dtIcon = !empty($dtData->icon) ? env('STORAGE_URL').'/'.$dtData->icon : null;
            $dtData->icon = $dtIcon;

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
                'name' => 'required'
            ]
            , [
                'name.required' => lang('dt_input_name'),
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
            $dtData = new RankCommunity();
        } else {
            $dtData = RankCommunity::find($id);
        }
        DB::beginTransaction();
        try {
            $name = !empty($this->request->input('name')) ? is_array($this->request->input('name')) ? $this->request->input('name') : (array)json_decode($this->request->input('name')) : [];
            $content_conditions = !empty($this->request->input('content_conditions')) ? is_array($this->request->input('content_conditions')) ? $this->request->input('content_conditions') : (array)json_decode($this->request->input('content_conditions')) : [];
            $dtData->name = $name[$code_lang] ?? '';
            $dtData->challenges_next_rank = $this->request->challenges_next_rank;
            $dtData->betting_limit = $this->request->betting_limit;
            $dtData->save();
            if ($dtData) {
                if ($this->request->hasFile('image')) {
                    if (!empty($dtData->image)) {
                        $this->deleteFile($dtData->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'rank_community/' . $dtData->id, 70, 70, false);
                    $dtData->image = $path;
                    $dtData->save();
                }

                if ($this->request->hasFile('icon')) {
                    if (!empty($dtData->icon)) {
                        $this->deleteFile($dtData->icon);
                    }
                    $path = $this->UploadFile($this->request->file('icon'), 'rank_community/' . $dtData->id, 70, 70, false);
                    $dtData->icon = $path;
                    $dtData->save();
                }

                if (!empty($name)){
                    foreach($name as $language => $value) {
                        DB::table('tbl_rank_community_translations')->updateOrInsert(
                            [
                                'id_rank' => $dtData->id,
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


    public function get_list_rank_community() {
        $id_client = !empty($this->request->client) ? $this->request->client->id : 0;
        $query = RankCommunity::where('tbl_rank_community.id','!=',0)
            ->leftJoin('tbl_rank_community_translations as pt','pt.id_rank','=','tbl_rank_community.id')
            ->select('tbl_rank_community.*',
                'pt.name as name',
                'pt.content_conditions as content_conditions',
                'pt.language',
                DB::raw("CONCAT('".env('STORAGE_URL')."/',tbl_rank_community.image) as image"),
                DB::raw("CONCAT('".env('STORAGE_URL')."/',tbl_rank_community.icon) as icon"),
            )
            ->where('pt.language',$this->_locale);
        $search = $this->request->input('search');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        $query->orderBy('id', 'asc');
        $data = $query->get();

        $info = [];
        if(!empty($id_client)) {
            $clients = Clients::find($id_client);
            $sum_deposit = DB::table('tbl_challenge_me')
                ->where('client_id', $clients->id)
                ->sum('deposit');
            $info['sum_deposit'] = $sum_deposit;


            $count_deposit = DB::table('tbl_challenge_me')
                ->where('client_id', $clients->id)
                ->where('status', 1)
                ->count();

        }

        foreach($data as $key => $value){
            $value->content_conditions = str_replace('{challenges_next_rank}', ($value->challenges_next_rank), $value->content_conditions);
            if(!empty($clients->rank_community)) {
                if($value->id == $clients->rank_community) {
                    $value->is_current_rank = true;
                    $info['rank_community'] = $value;
                    $count_deposit = $count_deposit ?? 0;
                    if(!empty($data[$key + 1])) {
                        $challenges_next_rank = $data[$key + 1]->challenges_next_rank ?? 0;
                        $dk_next_rank = $challenges_next_rank - $count_deposit;
                        $info['content_next_rank'] = lang('success_append').' ' . $dk_next_rank . ' '. lang('challenge_next_rank');
                    }
                    else {
                        $info['content_next_rank'] = '';
                    }
                } else {
                    $value->is_current_rank = false;
                }
            }
            else {
                $value->is_current_rank = false;
            }
        }


        return response()->json([
            'data' => $data,
            'info' => $info,
            'result' => true,
            'message' => lang('get_data_success')
        ]);
    }

}
