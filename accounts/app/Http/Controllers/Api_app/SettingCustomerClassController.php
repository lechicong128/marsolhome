<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\PromotionResources;
use App\Models\Promotion;
use App\Models\PromotionCustomer;
use App\Models\PromotionTranslations;
use App\Models\ReferralLevel;
use App\Models\SettingCustomerClass;
use App\Services\AdminService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingCustomerClassController extends AuthController
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

        $query = SettingCustomerClass::with(['transalations','rule'])->where('id','!=',0);
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

                $dtImageBackGround = !empty($value->image_background) ? (env('STORAGE_URL').'/'.$value->image_background) : null;
                $data[$key]['image_background'] = $dtImageBackGround;
            }
        }
        $total = SettingCustomerClass::count();
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
        $dtData = SettingCustomerClass::with([
            'rule','transalations'
        ])
        ->find($id);
        if (!empty($dtData)){
            $dtImage = !empty($dtData->image) ? env('STORAGE_URL').'/'.$dtData->image : null;
            $dtData->image = $dtImage;

            $dtIcon = !empty($dtData->icon) ? env('STORAGE_URL').'/'.$dtData->icon : null;
            $dtData->icon = $dtIcon;

            $dtImageBackGround = !empty($dtData->image_background) ? env('STORAGE_URL').'/'.$dtData->image_background : null;
            $dtData->image_background = $dtImageBackGround;
            $translations = $dtData->transalations ?? [];
            $data_translations = [];
            foreach ($translations as $translation) {
                $data_translations[$translation->language] = [
                    'name' => $translation->name,
                    'benefits' => $translation->benefits,
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
            $dtData = new SettingCustomerClass();
        } else {
            $dtData = SettingCustomerClass::find($id);
        }
        DB::beginTransaction();
        try {
            $rule_id = !empty($this->request->input('rule_id')) ? is_array($this->request->input('rule_id')) ? $this->request->input('rule_id') : (array)json_decode($this->request->input('rule_id')) : [];
            $benefits = !empty($this->request->input('benefits')) ? is_array($this->request->input('benefits')) ? $this->request->input('benefits') : (array)json_decode($this->request->input('benefits')) : [];
            $content_conditions = !empty($this->request->input('content_conditions')) ? is_array($this->request->input('content_conditions')) ? $this->request->input('content_conditions') : (array)json_decode($this->request->input('content_conditions')) : [];
            $name = !empty($this->request->input('name')) ? is_array($this->request->input('name')) ? $this->request->input('name') : (array)json_decode($this->request->input('name')) : [];
            $dtData->name = $name[$code_lang] ?? '';
            $dtData->benefits = $benefits[$code_lang] ?? '';
            $dtData->percent = $this->request->percent;
            $dtData->save();
            if ($dtData) {
                if ($this->request->hasFile('image')) {
                    if (!empty($dtData->image)) {
                        $this->deleteFile($dtData->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'setting_customer_class/' . $dtData->id, 70, 70, false);
                    $dtData->image = $path;
                    $dtData->save();
                }

                if ($this->request->hasFile('icon')) {
                    if (!empty($dtData->icon)) {
                        $this->deleteFile($dtData->icon);
                    }
                    $path = $this->UploadFile($this->request->file('icon'), 'setting_customer_class/' . $dtData->id, 70, 70, false);
                    $dtData->icon = $path;
                    $dtData->save();
                }

                if ($this->request->hasFile('image_background')) {
                    if (!empty($dtData->image_background)) {
                        $this->deleteFile($dtData->image_background);
                    }
                    $path = $this->UploadFile($this->request->file('image_background'), 'setting_customer_class/' . $dtData->id, 70, 70, false);
                    $dtData->image_background = $path;
                    $dtData->save();
                }
                if (!empty($name)){
                    foreach($name as $language => $value) {
                        $valueBenefits = $benefits[$language] ?? '';
                        $valueConditions = $content_conditions[$language] ?? '';
                        DB::table('tbl_setting_customer_class_translations')->updateOrInsert(
                            [
                                'setting_customer_class_id' => $dtData->id,
                                'language' => $language
                            ],
                            [
                                'name' => $value,
                                'benefits' => $valueBenefits,
                                'content_conditions' => $valueConditions,
                            ]
                        );
                    }
                }
                if (!empty($rule_id)){
                    foreach ($rule_id as $key => $value){
                        $rule_post = !empty($this->request->input('rule')) ? is_array($this->request->input('rule')) ? $this->request->input('rule') : (array)json_decode($this->request->input('rule')) : [];
                        $rule = $rule_post[$value] ?? 0;
                        DB::table('tbl_setting_customer_class_rule')->updateOrInsert(
                            [
                                'setting_customer_class_id' => $dtData->id,
                                'id' => $value
                            ],
                            [
                                'rule' => $rule,
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

    public function getListData(){
        $current_page = 1;
        $per_page = 10;
        $machine_id = !empty($this->request->input('machine_id')) ? $this->request->input('machine_id') : null;
        $code = !empty($this->request->input('code')) ? $this->request->input('code') : null;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $promotion = Promotion::where(function ($query) use ($code){
            $query->where('date_end','>=',date('Y-m-d'));
            $query->orWhere('indefinite',1);
        })
            ->where(function ($query) use ($code,$customer_id,$machine_id) {
                $query->where('active', 1);
                if (!empty($code)) {
                    $query->where('name', 'like', '%' . $code . '%');
                }
                if (!empty($customer_id)) {
                    $query->doesntHave('transaction', 'and', function ($q) use ($customer_id) {
                        $q->where(function ($instance) use ($customer_id) {
                            $instance->where('customer_id', $customer_id);
                            $instance->whereNotIn('status',
                                [
                                    Config::get('constant')['status_cancel'],
//                                    Config::get('constant')['status_request'],
                                ]);
                        });
                        $q->where('type_use_one',1);
                    });
                    $query->where(function ($q) use ($customer_id) {
                        $q->where(function ($inst) use ($customer_id){
                            $inst->where('type_customer',1);
                            $inst->whereHas('customer',function ($instance) use ($customer_id) {
                                $instance->where('customer_id', $customer_id);
                            });
                        });
                        $q->orWhere('type_customer',0);
                    });
                } else {
                    $query->where(function ($query) use ($machine_id){
                        $query->where('type_customer',0);
                        $query->orWhere(function ($q) use ($machine_id){
                            $q->where('machines_id', $machine_id)
                                ->where('require_first_order',1);
                        });
                    });
                }
            })
            ->orderByRaw('indefinite desc,date_end asc')
            ->paginate($per_page, ['*'], '', $current_page);
        $collection = PromotionResources::collection($promotion);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => lang('dt_list_success')
        ]);
    }

    public function apiGetClassList() {
//        $query = SettingCustomerClass::with(['transalations','rule'])->where('id','!=',0);
        $query = SettingCustomerClass::where('tbl_setting_customer_class.id','!=',0)
            ->leftJoin('tbl_setting_customer_class_translations as pt','pt.setting_customer_class_id','=','tbl_setting_customer_class.id')
            ->select('tbl_setting_customer_class.*',
                'pt.name as name',
                'pt.benefits as benefits',
                'pt.content_conditions as content_conditions',
                'pt.language',
                DB::raw("CONCAT('".env('STORAGE_URL')."/',tbl_setting_customer_class.image) as image"),
                DB::raw("CONCAT('".env('STORAGE_URL')."/',tbl_setting_customer_class.icon) as icon"),
                DB::raw("CONCAT('".env('STORAGE_URL')."/',tbl_setting_customer_class.image_background) as image_background")
            )
            ->where('pt.language',$this->_locale);
        $search = $this->request->input('search');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        $query->orderBy('id', 'desc');
        $data = $query->get();

        foreach($data as $key => $value){
            $value->benefits = str_replace('{percent}', ($value->percent.'%'), $value->benefits);
            $rules = DB::table('tbl_setting_customer_class_rule')
                ->where('setting_customer_class_id',$value->id)
                ->get();
            $content_conditions = $value->content_conditions;
            foreach($rules as $k => $v){
                if($v->type == 'review') {
                    $content_conditions = str_replace('{review}', $v->rule, $content_conditions);
                }
                else if($v->type == 'affiliate') {
                    $content_conditions = str_replace('{affiliate}', $v->rule, $content_conditions);
                }
                $value->content_conditions = $content_conditions;
            }
//            $data[$key]->rules = $rules;
        }

        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => lang('get_data_success')
        ]);
    }


    public function reviewClassClientApi() {
        app(\App\Http\Middleware\CheckLoginApi::class);
        $id_client = !empty($this->request->client) ? $this->request->client->id : 0;
        if(empty($id_client)){
            $id_client = $this->request->input('id_client', 0);
        }
        if(!empty($id_client)) {
            $setting_customer_class_id = reviewClassClientApi($id_client);
            if(!empty($setting_customer_class_id)) {
                return response()->json([
                    'data' => [
                        'setting_customer_class_id' => $setting_customer_class_id
                    ],
                    'result' => true,
                    'message' => lang('dt_success')
                ]);
            }
        }

        return response()->json([
            'data' => null,
            'result' => false,
            'message' => lang('dt_error')
        ]);
    }
}
