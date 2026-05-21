<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ListBank as ListBankResource;
use App\Http\Resources\CompanyCarResource;
use App\Http\Resources\TypeCarResource;
//use App\Models\CategoryCard;
//use App\Models\CategoryErrorCar;
//use App\Models\CategoryLocation;
//use App\Models\CategoryReport;
//use App\Models\CategoryReportDriver;
//use App\Models\CompanyCar;
//use App\Models\ContentReview;
//use App\Models\ContractTemplate;
//use App\Models\GuidePayment;
//use App\Models\ListBank;
//use App\Models\ModelCar;
//use App\Models\NoteCancel;
//use App\Models\OtherAmenitiesCar;
//use App\Models\PaymentMode;
//use App\Models\Province;
//use App\Models\SampleMessage;
//use App\Models\SurchargeCar;
//use App\Models\TypeCar;
//use App\Models\Car;
use App\Models\NoteAffiliate;
use App\Models\NoteHaruWallet;
use App\Models\NoteCancel;
use App\Models\PaymentMode;
use App\Models\Unit;
use App\Traits\UploadFile;
use Google\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Products;

class CategoryController extends AuthController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
    }

    public function getListCategoryReport()
    {
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $categoryReport = CategoryReport::paginate($per_page, ['*'], '', $current_page);
        return response()->json($categoryReport);
    }

//    public function getDataCar()
//    {
//        $data = [];
//        $dtCategoryTrip = DB::table('tbl_cancel_trip')->select('id', 'name', 'guest_cancel',
//            'owen_cancel')->get()->toArray();
//        $cancel_trip = [
//            'title_cancel_trip' => get_option('title_cancel_trip'),
//            'policy_cancel_trip' => $dtCategoryTrip,
//            'note_cancel_trip' => get_option('note_cancel_trip'),
//            'compensation_refund' => get_option('compensation_refund'),
//        ];
//
//        $data['getListPriceMonth'] = getListPriceMonth();
//        $data['document_license'] = get_option('document_license');
//
//        $data['cancel_trip'] = $cancel_trip;
//        $data['documentation_policy_car'] = get_option('documentation_policy_car');
//        $data['mortgage_policy_car'] = get_option('mortgage_policy_car');
//        $data['setting_price_car'] = get_option('setting_price_car');
//        $data['setting_insurance_car'] = get_option('setting_insurance_car');
//        $data['percent_insurance'] = get_option('percent_insurance');
//        $data['percent_deposit'] = get_option('percent_deposit');
//        $data['number_deposit_car'] = get_option('number_deposit_car');
//        $data['document_deposit'] = get_option('document_deposit');
//        $data['document_payment'] = get_option('document_payment');
//
//
//        $data['car_talent']['setting_price_car_talent'] = get_option('setting_price_car_talent');
//        $data['car_talent']['setting_insurance_car_talent'] = get_option('setting_insurance_car_talent');
//        $data['car_talent']['total_km_car_talent'] = get_option('total_km_car_talent');
//        $data['car_talent']['setting_service_car_talent'] = get_option('setting_service_car_talent');
//        $data['car_talent']['setting_shuttle_car_talent'] = get_option('setting_shuttle_car_talent');
//        $data['car_talent']['setting_number_hour_day_car_talent'] = get_option('setting_number_hour_day_car_talent');
//        $data['car_talent']['setting_hour_night_car_talent'] = get_option('setting_hour_night_car_talent');
//        $data['car_talent']['setting_interprovincial_travel'] = get_option('setting_interprovincial_travel');
//        return response()->json($data);
//    }

    public function getListProvince()
    {
        $limit = 50;
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $dtProvince = Province::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->where('name', 'like', '%' . $search . '%');
            }
        })->orderByRaw('order_by desc')->take($limit)->get();
        $data['data'] = $dtProvince;
        return response()->json($data);
    }

    public function getListDistrict()
    {
        $limit = 50;
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $province_id = !empty($this->request->input('province_id')) ? $this->request->input('province_id') : 0;
        $dtDistrict = DB::table('tbl_district')->where(function ($query) use ($search, $province_id) {
            if (!empty($search)) {
                $query->where('name', 'like', '%' . $search . '%');
            }
            $query->where('province_id', $province_id);
        })->take($limit)->get();
        $data['data'] = $dtDistrict;
        return response()->json($data);
    }

    public function getListWard()
    {
        $limit = 50;
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $district_id = !empty($this->request->input('district_id')) ? $this->request->input('district_id') : 0;
        $dtWard = DB::table('tbl_wards')->where(function ($query) use ($search, $district_id) {
            if (!empty($search)) {
                $query->where('name', 'like', '%' . $search . '%');
            }
            $query->where('district_id', $district_id);
        })->take($limit)->get();
        $data['data'] = $dtWard;
        return response()->json($data);
    }

    public function getListPaymentMode()
    {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $payment_mode_id = $this->request->input('payment_mode_id') ?? 0;
        if (!empty($payment_mode_id)) {
            $payment_mode_id = is_array($payment_mode_id) ? $payment_mode_id : [$payment_mode_id];
        }
        $dtPaymentMode = PaymentMode::select('id','name','code','type','note')
            ->selectRaw('CONCAT("' . asset('storage') . '/", image) as image')
            ->where(function ($query) use ($search,$payment_mode_id) {
                if (!empty($search)) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
                if (!empty($payment_mode_id)){
                    $query->whereIn('id',$payment_mode_id);
                }
                $query->where('active', 1);
            })
            ->orderByRaw('id desc')->get();
        $dtPaymentMode = $dtPaymentMode->map(function ($item) use ($_locale){
            $trans = $item->transalations->where('language',$_locale)->first();
            $item->name = $trans['name'] ?? '';
            $item->makeHidden(['transalations']);
            return $item;
        });
        $data['data'] = $dtPaymentMode;
        $data['result'] = true;
        $data['message'] = 'Lấy danh sách thành công';
        return response()->json($data);
    }

    public function getListNoteCancel()
    {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $dtNoteCancel = NoteCancel::select('id','note')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('note', 'like', '%' . $search . '%');
                }
            })->orderByRaw('id desc')->get();
        $dtNoteCancel = $dtNoteCancel->map(function ($item) use ($_locale){
            $trans = $item->transalations->where('language',$_locale)->first();
            $item->note = $trans['note'] ?? '';
            $item->makeHidden(['transalations']);
            return $item;
        });
        $data['data'] = $dtNoteCancel;
        $data['result'] = true;
        $data['message'] = 'Lấy danh sách thành công';
        return response()->json($data);
    }
    public function getListNoteHaruWallet()
    {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $type = $this->request->input('type') ?? 1;
        $dtData = NoteHaruWallet::select('id','title','content','screen_link','color','background',DB::raw('CONCAT("'.$this->baseUrl.'/", image) as image'))
            ->where(function ($query) use ($search,$type) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
                $query->where('type',$type);
            })->orderByRaw('id asc')->get();
        $dtData = $dtData->map(function ($item) use ($_locale){
            $trans = $item->transalations->where('language',$_locale)->first();
            $item->title = $trans['title'] ?? '';
            $item->content = $trans['content'] ?? '';
            $item->makeHidden(['transalations']);
            return $item;
        });
        $data['data'] = $dtData;
        $data['result'] = true;
        $data['message'] = 'Lấy danh sách thành công';
        return response()->json($data);
    }
    public function getListNoteAffiliate()
    {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $type = $this->request->input('type') ?? 1;
        $dtData = NoteAffiliate::select('id','title','content')
           ->selectRaw('CONCAT("' . asset('storage') . '/", image) as image')
            ->where(function ($query) use ($search,$type) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
                $query->where('type',$type);
            })->orderByRaw('id asc')->get();
        $dtData = $dtData->map(function ($item) use ($_locale){
            $trans = $item->transalations->where('language',$_locale)->first();
            $item->title = $trans['title'] ?? '';
            $item->content = $trans['content'] ?? '';
            $item->makeHidden(['transalations']);
            return $item;
        });
        $data['data'] = $dtData;
        $data['result'] = true;
        $data['message'] = 'Lấy danh sách thành công';
        return response()->json($data);
    }
//
//    public function getListCategoryReportDriver()
//    {
//        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
//        $dtCategoryReport = CategoryReportDriver::select('id','name')
//            ->where(function ($query) use ($search) {
//                if (!empty($search)) {
//                    $query->where('name', 'like', '%' . $search . '%');
//                }
//            })->orderByRaw('id desc')->get();
//        $data['data'] = $dtCategoryReport;
//        return response()->json($data);
//    }
//
//    public function getListSampleMessage()
//    {
//        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
//        $type = !empty($this->request->input('type')) ? $this->request->input('type') : 1;
//        $dtSampleMessage = SampleMessage::select('id','message','type')
//            ->where(function ($query) use ($search,$type) {
//                if (!empty($search)) {
//                    $query->where('message', 'like', '%' . $search . '%');
//                }
//                $query->where('type',$type);
//            })->orderByRaw('id desc')->get();
//        $data['data'] = $dtSampleMessage;
//        return response()->json($data);
//    }
//
//    public function getListCategoryLocation()
//    {
//        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
//        $dtCategoryLocation = CategoryLocation::where(function ($query) use ($search) {
//                if (!empty($search)) {
//                    $query->where('name', 'like', '%' . $search . '%');
//                }
//            })->orderByRaw('id desc')->get();
//        $data['data'] = $dtCategoryLocation;
//        return response()->json($data);
//    }
//
//    public function getListContractTemplate(){
//        $dtContractTemplate = ContractTemplate::select('id','name_file','active')->get();
//        if (!empty($dtContractTemplate)){
//            foreach ($dtContractTemplate as $key => $value){
//                $name_file = explode('___',$value->name_file);
//                $name_file_new = $name_file[1];
//                $dtContractTemplate[$key]['file'] = asset('storage').'/'.$value->name_file;
//                $dtContractTemplate[$key]['name_file'] = $name_file_new;
//            }
//        }
//        $data['data'] = $dtContractTemplate;
//        return response()->json($data);
//    }
//
//    public function getListStatusCar(){
//        $dtListStatusCar = getListStatusCar();
//        $data['data'] = $dtListStatusCar;
//        return response()->json($data);
//    }
//
//    public function getListCompanyCar()
//    {
//        $limit = 50;
//        $name_search = !empty($this->request->input('name_search')) ? $this->request->input('name_search') : null;
//        $dtCompanyCar = CompanyCar::where(function ($query) use ($name_search) {
//            if (!empty($name_search)) {
//                $query->where('name', 'like', '%' . $name_search . '%');
//            }
//        })->take($limit)->get();
//        return CompanyCarResource::collection($dtCompanyCar);
//    }
//
//    public function getListModelCar()
//    {
//        $search = !empty($this->request->input('name_search')) ? $this->request->input('name_search') : null;
//        $company_car_id = !empty($this->request->input('company_car_id')) ? $this->request->input('company_car_id') : 0;
//        $dtModelCar = ModelCar::where(function ($query) use ($search, $company_car_id) {
//            if (!empty($search)) {
//                $query->where('name', 'like', '%' . $search . '%');
//            }
//            $query->where('company_car_id', $company_car_id);
//        })->get();
//        $data['data'] = $dtModelCar;
//        return response()->json($data);
//    }
//
//    public function getListTypeCar()
//    {
//        $limit = 50;
//        $name_search = !empty($this->request->input('name_search')) ? $this->request->input('name_search') : null;
//        $dtTypeCar = TypeCar::where(function ($query) use ($name_search) {
//            if (!empty($name_search)) {
//                $query->where('name', 'like', '%' . $name_search . '%');
//            }
//        })->take($limit)->get();
//        return TypeCarResource::collection($dtTypeCar);
//    }
//
//    public function getListUtilitiesCar()
//    {
//        $dtTransmission = getListTransmission();
//        $dtTypeFuel = getListTypeFuel();
//
//        $data['other'] = [
//            'km_delivery_car' => get_option('km_delivery_car'),
//            'fee_km_delivery_car' => get_option('fee_km_delivery_car'),
//            'free_km_delivery_car' => get_option('free_km_delivery_car'),
//            'km_delivery_car_limit' => get_option('km_delivery_car_limit'),
//            'fee_km_delivery_car_limit' => get_option('fee_km_delivery_car_limit'),
//            'free_km_delivery_car_limit' => get_option('free_km_delivery_car_limit'),
//            'range_km_delivery_car' => get_option('range_km_delivery_car'),
//            'range_fee_km_delivery_car' => get_option('range_fee_km_delivery_car'),
//            'range_free_km_delivery_car' => get_option('range_free_km_delivery_car'),
//            'limit_km_day' => get_option('limit_km_day'),
//            'percent_discount' => get_option('percent_discount'),
//            'note_mortgage' => get_option('note_mortgage'),
//            'noti_mortgage' => get_option('noti_mortgage'),
//        ];
//       $data['other_talent'] = [
//            'km_delivery_car' => get_option('km_delivery_car_talent'),
//            'fee_km_delivery_car' => get_option('fee_km_delivery_car_talent'),
//            'free_km_delivery_car' => get_option('free_km_delivery_car_talent'),
//            'range_km_delivery_car_talent' => get_option('range_km_delivery_car_talent'),
//            'range_fee_km_delivery_car_talent' => get_option('range_fee_km_delivery_car_talent'),
//            'range_free_km_delivery_car_talent' => get_option('range_free_km_delivery_car_talent'),
//            'limit_km_day' => get_option('limit_km_day_talent'),
//        ];
//        $data['dtFee'] = SurchargeCar::where('type',1)->where('check_fee',1)->first();
//
//        $data['dtTransmission'] = $dtTransmission;
//        $data['dtTypeFuel'] = $dtTypeFuel;
//        return response()->json($data);
//    }
//
//    public function getListOtherAmenitiesCar()
//    {
//        $dtOtherAmenitiesCar = OtherAmenitiesCar::all();
//        if (!empty($dtOtherAmenitiesCar)){
//            foreach ($dtOtherAmenitiesCar as $key => $value){
//                $dtOtherAmenitiesCar[$key]['image'] = asset('storage/'.$value->image);
//            }
//        }
//
//        $data['data'] = $dtOtherAmenitiesCar;
//        return response()->json($data);
//    }
//
//    public function getListSurchargeCar()
//    {
//        $type = !empty($this->request->input('type')) ? $this->request->input('type') : 1;
//        $dtSurchargeCar = SurchargeCar::where('type',$type)->get();
//        $data['data'] = $dtSurchargeCar;
//        return response()->json($data);
//    }
//
//    public function getRentCostPropose(){
//        $type = !empty($this->request->input('type')) ? $this->request->input('type') : 1;
//        $year = !empty($this->request->input('year')) ? $this->request->input('year') : 0;
//        $company_car = !empty($this->request->input('company_car')) ? $this->request->input('company_car') : 0;
//        $model_car = !empty($this->request->input('model_car')) ? $this->request->input('model_car') : 0;
//        if($type == 1){
//            $rent_cost = Car::where('year_manu',$year)->where('company_car_id',$company_car)->where('model_car_id',$model_car)->min('rent_cost');
//        } else {
//            $rent_cost = Car::join('tbl_car_talent','tbl_car_talent.car_id','=','tbl_car.id')
//                ->where('year_manu',$year)->where('company_car_id',$company_car)->where('model_car_id',$model_car)->min('tbl_car_talent.rent_cost');
//        }
//        $rent_cost_propose = !empty($rent_cost) ? $rent_cost : 0;
//        $data['rent_cost_propose'] = $rent_cost_propose;
//        return response()->json($data);
//    }
//
//    public function getListGuidePayment(){
//        $dtGuidePayment = GuidePayment::where('active',1)->get();
//        if (!empty($dtGuidePayment)){
//            foreach ($dtGuidePayment as $key => $value){
//                $dtGuidePayment[$key]['content'] = str_replace('src="/storage', 'src="'.asset('/storage').'', $value->content);
//            }
//        }
//        $data['data'] = $dtGuidePayment;
//        return response()->json($data);
//    }
//
//    public function getListContentRewiew()
//    {
//        $type_review = !empty($this->request->input('type_review')) ? $this->request->input('type_review') : 1;
//        $search = !empty($this->request->input('name_search')) ? $this->request->input('name_search') : null;
//        $dtData = ContentReview::where(function ($query) use ($search,$type_review) {
//            if (!empty($search)) {
//                $query->where('content', 'like', '%' . $search . '%');
//            }
//            $query->where('type',$type_review);
//        })->get();
//        $data['data'] = $dtData;
//        return response()->json($data);
//    }
//
//    public function getListBank()
//    {
//        $limit = 50;
//        $name_search = !empty($this->request->input('name_search')) ? $this->request->input('name_search') : null;
//        $dtListBank = ListBank::where(function ($query) use ($name_search) {
//            if (!empty($name_search)) {
//                $query->where('code', 'like', '%' . $name_search . '%');
//                $query->orWhere('name', 'like', '%' . $name_search . '%');
//            }
//        })->take($limit)->get();
//        return ListBankResource::collection($dtListBank);
//    }
//
//    public function getListCategoryErrorCar()
//    {
//        $search = !empty($this->request->input('name_search')) ? $this->request->input('name_search') : null;
//        $dtData = CategoryErrorCar::where(function ($query) use ($search) {
//            if (!empty($search)) {
//                $query->where('name', 'like', '%' . $search . '%');
//            }
//        })->select('id','name')->get();
//        $data['data'] = $dtData;
//        return response()->json($data);
//    }
//
//    public function getListCategoryCard()
//    {
//        $_locale = $this->request->_locale;
//        $locale_default_vn = config('constant.locale_default_vn');
//
//        $search = !empty($this->request->input('name_search')) ? $this->request->input('name_search') : null;
//        $dtData = CategoryCard::where(function ($query) use ($search, $_locale, $locale_default_vn) {
//            if ($_locale != $locale_default_vn) {
//                if (!empty($search)) {
//                    $query->where("name_{$_locale}", 'like', '%' . $search . '%');
//                }
//            } else if (!empty($search)) {
//                $query->where('name', 'like', '%' . $search . '%');
//            }
//            $query->where('active',1);
//
//        })
//        ->select('*')
//        ->when($_locale != $locale_default_vn, function ($query) use ($_locale) {
//            $query->selectRaw(DB::raw("name_{$_locale} as name"));
//            $query->selectRaw(DB::raw("content_{$_locale} as content"));
//        })
//        ->orderByRaw('order_by asc')
//        ->get();
//        $data['data'] = $dtData;
//        $data['base']['base'] = asset('storage/');
//        return response()->json($data);
//    }



    public function getListProduct($id = 0)
    {
        $limit = 50;
        $params = $this->request->input('paramsCus');
        $term = $this->request->input('term');
        $select2 = !empty($params['select2']) ? $params['select2'] : true;
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $type = !empty($this->request->input('type')) ? $this->request->input('type') : 1;
        $dtProducts = Products::select('id',
                'name',
                'code',
                'price',
                'id_variant',
                DB::raw('CONCAT("'.$this->baseUrl.'/", image) as image'),
                DB::raw('CONCAT(code, " - ", name) as name')
            )
            ->where(function ($query) use ($search, $type, $term) {
                if (!empty($search)) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
                if (!empty($term)) {
                    $query->where('name', 'like', '%' . $term . '%');
                    $query->orWhere('code', 'like', '%' . $term . '%');
                }
                $query->where('active', $type);
            })->orderByRaw('id desc')
            ->take($limit)->get();

//        if ($data['result'] == false){
//            return response()->json($data);
//        }
        if ($select2){
            $results = [];
            foreach ($dtProducts as $key => $value) {
                $variant_option = $value->variant_option->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'price' => $item->pivot->price,
                    ];
                });
                $results[] = [
                    'id' => $value['id'],
                    'text' => $value['name'],
                    'image' => $value['image'],
                    'code' => $value['code'],
                    'name' => $value['name'],
                    'price' => $value['price'],
                    'id_variant' => $value['id_variant'],
                    'variant_option' => $variant_option,
                ];
            }
            $data = [
                'items' => $results
            ];
        } else {
            $data = [
                'data' => $dtProducts
            ];
        }
        return response()->json($data);
    }


    public function getListUnit()
    {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $unit_id = $this->request->input('unit_id') ?? 0;
        if (!empty($unit_id)) {
            $unit_id = is_array($unit_id) ? $unit_id : [$unit_id];
        }
        $dtUnit = Unit::select('id','name')
            ->where(function ($query) use ($search,$unit_id) {
                if (!empty($search)) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
                if (!empty($unit_id)){
                    $query->whereIn('id',$unit_id);
                }

            })
            ->orderByRaw('id desc')->get();
        $data['data'] = $dtUnit;
        $data['result'] = true;
        $data['message'] = 'Lấy danh sách thành công';
        return response()->json($data);
    }
}
