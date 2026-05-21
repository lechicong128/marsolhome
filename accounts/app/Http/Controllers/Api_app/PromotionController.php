<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\PromotionResources;
use App\Models\Promotion;
use App\Models\PromotionCustomer;
use App\Models\PromotionTranslations;
use App\Models\ReferralLevel;
use App\Services\AdminService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PromotionController extends AuthController
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

        $query = Promotion::with('transalations')->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%$search%");
                $q->orWhere('date_start', 'like', "%$search%");
                $q->orWhere('date_end', 'like', "%$search%");
                $q->orWhere('name', 'like', "%$search%");
                $q->orWhere('detail', 'like', "%$search%");
            });
        }
        if (!empty($status_search)){
            if ($status_search == 1){
                $query->where('date_start','>',date('Y-m-d'));
                $query->where('indefinite',0);
            } elseif ($status_search == 2){
                $query->where('date_start','<=',date('Y-m-d'));
                $query->where('date_end','>=',date('Y-m-d'));
                $query->orWhere('indefinite',1);
            } elseif ($status_search == 3){
                $query->where('date_end','<',date('Y-m-d'));
            }
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtImage = !empty($value->image) ? env('STORAGE_URL').'/'.$value->image : null;
                $data[$key]['image'] = $dtImage;
            }
        }
        $total = Promotion::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function countAll(){
        $arr = [
            [
                'id' => 1,
                'name' => 'Sắp áp dụng'
            ],
            [
                'id' => 2,
                'name' => 'Đang áp dụng'
            ],
            [
                'id' => 3,
                'name' => 'Hết hạn'
            ],
        ];
        foreach ($arr as $key => $value){
            $status = $value['id'];
            $promotion = Promotion::where(function ($query) use ($status){
                if (!empty($status)){
                    if ($status == 1){
                        $query->where('date_start','>',date('Y-m-d'));
                        $query->where('indefinite',0);
                    } elseif ($status == 2){
                        $query->where('date_start','<=',date('Y-m-d'));
                        $query->where('date_end','>=',date('Y-m-d'));
                        $query->orWhere('indefinite',1);
                    } elseif ($status == 3){
                        $query->where('date_end','<',date('Y-m-d'));
                    }
                }
            })->count();
            $arr[$key]['count'] = $promotion;
        }
        $data['arr'] = $arr;
        $data['result'] = true;
        return response()->json($data);
    }

    public function getDetail(){
        $id = $this->request->input('id') ?? 0;
        $dtData = Promotion::with([
            'customer' => function ($q) {
                $q->select('id', 'promotion_id', 'customer_id');
                $q->with([
                    'customer' => function ($qr) {
                        $qr->select('id', 'code', 'fullname','phone');
                    }
                ]);
            }
        ])
        ->find($id);
        if (!empty($dtData)){
            $dtImage = !empty($dtData->image) ? env('STORAGE_URL').'/'.$dtData->image : null;
            $dtData->image = $dtImage;
            $translations = PromotionTranslations::where('promotion_id', $id)->get();
            $data_translations = [];
            foreach ($translations as $translation) {
                $data_translations[$translation->language] = [
                    'code' => $translation->code,
                    'name' => $translation->name,
                    'detail' => $translation->detail,
                    'note' => $translation->note,
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
                'code' => 'required|unique:tbl_promotion,code,' . $id,
                'name' => 'required'
            ]
            , [
                'required.required' => lang('dt_input_name'),
                'code.required' => lang('dt_input_code'),
                'code.unique' => lang('dt_input_unique_code'),
            ]);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            return response()->json($data);
        }
        $errors = '';
        if (empty($this->request->indefinite)) {
            if (empty($this->request->date_start)) {
                $errors .= '<div>'.lang('dt_input_date_start').'</div>';
            }
            if (empty($this->request->date_end)) {
                $errors .= '<div>'.lang('dt_input_date_end').'</div>';
            }
        }
        if (!empty($errors)) {
            $data['result'] = false;
            $data['message'] = $errors;
            return response()->json($data);
        }
        $LanguagDefault = json_decode($this->request->input('LanguagDefault'));
        $code_lang = 'vi';
        if (!empty($LanguagDefault)){
            $code_lang = $LanguagDefault->code;
        }
        if (empty($id)) {
            $dtData = new Promotion();
        } else {
            $dtData = Promotion::find($id);
        }
        DB::beginTransaction();
        try {
            $code = !empty($this->request->input('code')) ? is_array($this->request->input('code')) ? $this->request->input('code') : (array)json_decode($this->request->input('code')) : [];
            $name = !empty($this->request->input('name')) ? is_array($this->request->input('name')) ? $this->request->input('name') : (array)json_decode($this->request->input('name')) : [];
            $detail = !empty($this->request->input('detail')) ? is_array($this->request->input('detail')) ? $this->request->input('detail') : (array)json_decode($this->request->input('detail')) : [];
            $note = !empty($this->request->input('note')) ? is_array($this->request->input('note')) ? $this->request->input('note') : (array)json_decode($this->request->input('note')) : [];
            $dtData->code = $code[$code_lang];
            $dtData->name = $name[$code_lang];
            $dtData->type = $this->request->type;
            if ($this->request->type == 0) {
                $dtData->percent = ($this->request->percent);
                $dtData->money_max = ($this->request->money_max);
                $dtData->cash = 0;
            } else {
                $dtData->percent = 0;
                $dtData->money_max = 0;
                $dtData->cash = ($this->request->cash);
            }
            $dtData->number_day = ($this->request->number_day);
            $dtData->type_use_one = !empty($this->request->type_use_one) ? 1 : 0;
            $dtData->indefinite = !empty($this->request->indefinite) ? 1 : 0;
            if (!empty($this->request->indefinite)) {
                $dtData->date_start = null;
                $dtData->date_end = null;
            } else {
                $dtData->date_start = to_sql_date($this->request->date_start);
                $dtData->date_end = to_sql_date($this->request->date_end);
            }
            $dtData->type_customer = $this->request->type_customer;
            $arrCustomer = [];
            if ($this->request->type_customer == 1){
                if (!empty($this->request->customer_id)) {
                    $arrCustomer[] = $this->request->customer_id;
                }
            } else {
                $arrCustomer = [];
            }

            $dtData->detail = $detail[$code_lang];
            $dtData->note = $note[$code_lang];
            $dtData->created_by = $this->request->created_by;
            $dtData->active = 1;
            $dtData->save();
            if ($dtData) {
                if ($this->request->hasFile('image')) {
                    if (!empty($dtData->image)) {
                        $this->deleteFile($dtData->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'promotion/' . $dtData->id, 70, 70, false);
                    $dtData->image = $path;
                    $dtData->save();
                }
                $dtData->customer()->delete();
                if (!empty($arrCustomer)){
                    foreach ($arrCustomer as $key => $value){
                        $promotionCustomer = new PromotionCustomer();
                        $promotionCustomer->customer_id = $value;
                        $promotionCustomer->promotion_id = $dtData->id;
                        $promotionCustomer->save();
                    }
                }
                if (!empty($name)){
                    foreach($name as $language => $value) {
                        $valueCode = $code[$language] ?? '';
                        $valueNote = $note[$language] ?? '';
                        $valueDetail = $detail[$language] ?? '';
                        DB::table('tbl_promotion_translations')->updateOrInsert(
                            [
                                'promotion_id' => $dtData->id,
                                'language' => $language
                            ],
                            [
                                'code' => $valueCode,
                                'name' => $value,
                                'detail' => $valueDetail,
                                'note' => $valueNote,
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

    public function delete(){
        $id = $this->request->input('id') ?? 0;
        $dtData = Promotion::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = lang('dt_not_exist_promotion');
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if (count($dtData->transaction) > 1){
                $data['result'] = false;
                $data['message'] = lang('dt_promotion_use_note_delete');
                return response()->json($data);
            }
            $dtData->delete();
            if (!empty($dtData->image)){
                $this->deleteFile($dtData->image);
            }
            $dtData->customer()->delete();
            $dtData->transalations()->delete();
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

    public function getPromotionReferral(){
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $machine_id = !empty($this->request->input('machine_id')) ? $this->request->input('machine_id') : null;
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($customer_id)){
            $dtCheckReferral = DB::table('tbl_client_introduce')->where('id_client',$customer_id)->first();
            if (empty($dtCheckReferral)){
                return response()->json([
                    'data' => [],
                    'result' => true,
                    'message' => lang('dt_list_success')
                ]);
            }
        }
        $promotion = Promotion::where(function ($query){
            $query->where('date_end','>=',date('Y-m-d'));
            $query->orWhere('indefinite',1);
        })
            ->where(function ($query) use ($customer_id,$machine_id) {
                $query->where('active', 1);
                if (!empty($customer_id)) {
                    $query->where(function ($q) use ($customer_id,$machine_id) {
                        $q->where(function ($qq) use ($customer_id,$machine_id) {
                            $qq->where('require_first_order', 1)
                            ->whereRaw("
                                    NOT EXISTS (
                                        SELECT 1 FROM tbl_transaction
                                        WHERE tbl_transaction.customer_id = ?
                                        AND tbl_transaction.status IN (". Config::get('constant')['status_approve'].",". Config::get('constant')['status_finish'].",".Config::get('constant')['status_request'].",".Config::get('constant')['status_payment'].")
                                    )
                               ", [$customer_id])
                            ->whereRaw("
                                NOT EXISTS (
                                    SELECT 1 FROM tbl_transaction
                                    WHERE tbl_transaction.machines_id = ?
                                    AND tbl_transaction.status IN (". Config::get('constant')['status_approve'].",". Config::get('constant')['status_finish'].",".Config::get('constant')['status_request'].",".Config::get('constant')['status_payment'].")
                                )
                           ", [$machine_id]);
                        });
                    });
                    $query->where('machines_id', $machine_id);
                } else {
                    $query->where(function ($q) use ($machine_id){
                        $q->where('machines_id', $machine_id)
                            ->where('require_first_order',1)
                            ->whereRaw("
                                        NOT EXISTS (
                                            SELECT 1 FROM tbl_transaction
                                            WHERE tbl_transaction.machines_id = ?
                                            AND tbl_transaction.status IN (". Config::get('constant')['status_approve'].",". Config::get('constant')['status_finish'].",".Config::get('constant')['status_request'].",".Config::get('constant')['status_payment'].")
                                        )
                                   ", [$machine_id]);
                    });
                }
            })
            ->orderByRaw('indefinite desc,date_end asc')
            ->first();
        if (empty($promotion)){
            return response()->json([
                'data' => [],
                'result' => true,
                'message' => lang('dt_list_success')
            ]);
        }
        $percent_referral = $this->AdminService->get_option('percent_referral');
        $content_referral = $this->AdminService->get_option('content_referral_'.$_locale.'');
        $content_referral = json_decode($content_referral, true);
        $title_referral = $content_referral['title'];
        $content_referral_new = $content_referral['content'];
        $content_referral_new = str_replace('{percent}',$percent_referral.' %',$content_referral_new);
        $promotion->title_referral = $title_referral;
        $promotion->content_referral = $content_referral_new;
        $promotion->type_check = 'referral';
        $collection = PromotionResources::make($promotion);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'length' => 1,
            'result' => true,
            'message' => lang('dt_list_success')
        ]);
    }


    public function active(){
        $id = $this->request->input('id') ?? 0;
        $promotion = Promotion::find($id);
        try {
            $promotion->active = $this->request->status == 0 ? 1 : 0;
            $promotion->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

}
