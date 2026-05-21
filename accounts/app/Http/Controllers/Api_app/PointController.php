<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\HistoryPointResource;
use App\Models\HistoryPoint;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Clients;

use function Aws\map;

class PointController extends AuthController
{
    protected $fnbService;
    protected $fnbAdmin;
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getListHistoryPoint(){
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $year_month = $this->request->input('year_month') ?? date('Y-m');
        $dtHistoryPoint = HistoryPoint::where(function ($query) use ($customer_id,$year_month){
            $query->where('tbl_client_point_history.customer_id',$customer_id);
            // if (!empty($year_month)){
            //     $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?",[$year_month]);
            // }
        })
            ->orderByRaw('tbl_client_point_history.created_at desc')
            ->paginate($per_page, ['*'], '', $current_page);
        return HistoryPointResource::collection($dtHistoryPoint);
    }

    public function getListMonthPoint(){
        $customer_id = $this->request->client->id ?? 0;
        $query = HistoryPoint::where('id','!=',0);
        $query->where('customer_id', $customer_id);
        $query->select(DB::raw("DATE_FORMAT(created_at, '%m-%Y') as month_year"));
        $query->groupBy(DB::raw("DATE_FORMAT(created_at, '%m-%Y')"));
        $query->orderByRaw("DATE_FORMAT(created_at, '%m-%Y') desc");
        $dtData = $query->get();
        $data['result'] = true;
        $data['data'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }
    function updatePointReview() {
        $id_review = (int)$this->request->input('id_review',0);
        $dtObject = changePoint($id_review, 'review_items');
        
        return response()->json($dtObject);
    }
    function getDetailPointReview() {
        $id_review = (int)$this->request->input('id_review',0);
        $query = HistoryPoint::where('object_id','=',$id_review);
        $query->where('object_type', 'review_items');
        $dtData = $query->first();
        $DataClients = [];
        $data['HistoryPoint'] = $dtData;
        if(!empty($dtData)) {
            $customer_id = $dtData->customer_id;
            $queryClient = DB::table('tbl_clients')->where('id', $customer_id);
            $DataClients = $queryClient->first();
        }
        $data['dataClients'] = $DataClients;
        return response()->json($data);
    }
    function getDeleteDetailPointReivew() {
        $id_review = (int)$this->request->input('id_review',0);
        $id_history = (int)$this->request->input('id_history',0);
       

        $query = HistoryPoint::where('id','=',$id_history);
        $dtData = $query->first();
        if($dtData){
            $point = $dtData['point'];
            HistoryPoint::where('id', $id_history)->delete();
            $customer_id = $dtData->customer_id;
            $dtClient = Clients::find($customer_id);
            $point_client = $dtClient->point - $point;
            Clients::where('id', $dtClient->id)->update([
                'point' => $point_client,
            ]);
            if (!empty($point)) {
                $arr_object_id = [];
                $dtCustomer = Clients::select(
                    'tbl_clients.fullname as name',
                    'tbl_clients.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'customer' as 'object_type'")
                )
                    ->leftJoin('tbl_player_id', function ($join) {
                        $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                    })
                    ->where('tbl_clients.id', $customer_id)
                    ->get()->toArray();
                if (!empty($dtCustomer)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                }
                $fnbNoti = new \App\Services\NotiService();

                $request_noti = new Request([
                    'type_noti' => 'change_point',
                    'arr_object_id' => $arr_object_id,
                    'dtData' => [],
                    'customer_id' => $customer_id,
                    'point' => -$point,
                    'point_client' => $point_client,
                    'title_point' => '',
                    'locale' => $dtClient->lang_default ?? Config::get('constant')['lang_default'],
                    'type' => 'staff',
                    'staff_id' => 0
                ]);
                $fnbNoti->addNoti($request_noti);
            }
        }
        return response()->json(true);
    }
}
