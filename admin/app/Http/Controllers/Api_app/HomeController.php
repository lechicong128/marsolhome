<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Home;
use App\Models\CommentHome;
use App\Models\HomeReview;
use App\Models\Notification;
use App\Services\AccountService;
use App\Traits\UploadFile;
use App\Traits\SocketTrait;
use Google\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\HomeResources;
use App\Http\Resources\ReviewHomeResource;
use App\Models\HomeMedia;
use App\Models\Province;
use App\Models\TypeProperty;
use App\Models\Utility;
use App\Models\HouseOrientation;
use App\Models\LegalDocument;
use App\Models\InteriorHandover;
use App\Models\InteriorAmenity;
use App\Models\Ward;
use App\Models\District;
use Illuminate\Support\Facades\Validator;

class HomeController extends AuthController
{
    use UploadFile,SocketTrait;
    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->dbAccount = $accountService;
        $this->baseUrl = config('services.storage.url');
    }

    public function getListHome(){
        $current_page = 1;
        $per_page = 10;
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $status_search = $this->request->query('status_search') ?? $this->request->input('status_search');
        $property_type_search = $this->request->input('property_type_search');
        $check_save_home = $this->request->input('check_save_home');
        $type_search = $this->request->input('type_search') ?? 1;
        $lat = !empty($this->request->input('lat')) ? $this->request->input('lat') : 0;
        $lon = !empty($this->request->input('lon')) ? $this->request->input('lon') : 0;
        $province_search = !empty($this->request->input('province_id')) ? $this->request->input('province_id') : 0;
        $ward_search = !empty($this->request->input('ward_id')) ? $this->request->input('ward_id') : 0;
        $checkProvince = false;
        if(!empty($province_search)){
            $checkProvince = true;
        }
        $distance = !empty($this->request->input('distance')) ? $this->request->input('distance') : 10;
        $filter = $this->request->input('filter');
        if (!empty($filter) && is_string($filter)) {
            $filter = json_decode($filter, true);
        }
        $home_owner = $this->request->input('home_owner') ?? 0;
        $search = $this->request->input('search');
        $status_home = $this->request->input('status_home');
        $status_home = isset($status_home) ? $status_home : -1;
        $customer_search = $this->request->input('customer_search');
        $date_start = $this->request->input('date_start');
        $date_end = $this->request->input('date_end');
        $source_search = $this->request->input('source_search');
        $customer_id = $this->request->client->id ?? 0;
        $query = Home::select('tbl_home.*',DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))->with([
            'propertyType',
            'province',
            'ward',
            'direction',
            'legal',
            'interior',
            'media_items',
            'interior_amenities',
            'utilities',
            'utilities.options',
            'favourite',
            'save_home'
        ])->where('id', '!=', 0);

        if($status_home != -1){
            if($status_home == 6){
                $query->whereNotNull('tbl_home.end_date')->where('tbl_home.end_date', '<', date('Y-m-d'));
            } else {
                $query->where('status', $status_home);
                // loại bỏ lưu nháp
                if($status_home != 5){
                    if(!empty($home_owner)){
                        $query->where('tbl_home.end_date', '>=', date('Y-m-d'));
                    }
                }
            }
        } else {
            if (empty($home_owner)) {
                $query->where('status', 2);
            }
        }
        // Tin hết hạn sẽ không hiển thị (trừ khi xem tin của chính mình, hoặc lọc tin hết hạn bằng status_home = 6)
        if (empty($home_owner) && $status_home != 6) {
            $query->where(function ($q) {
                $q->whereNull('tbl_home.end_date')
                  ->orWhere('tbl_home.end_date', '>=', date('Y-m-d'));
            });
        }
        //tin đăng user đăng nhập
        if(!empty($home_owner)){
            if(!empty($customer_id)){
                $this->requestCustomer = clone $this->request;
                $this->requestCustomer->merge(['customer_id' => [$customer_id]]);
                $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
                $dataCustomer = $responseCustomer->getData(true);
                $dtCustomer = collect($dataCustomer['data'] ?? []);
                $customer_login = $dtCustomer->where('id', $customer_id)->first();
            }
            if(!empty($customer_login) && $customer_login['type_client'] != 2){
                $query->where('customer_id', $customer_id);
            }
        } else {
            $query->where('type', $type_search);
        }

        //lọc theo danh sách đăng tin 
        if (!empty($customer_search)) {
            $query->where('tbl_home.customer_id', $customer_search);
        }

        if(!empty($date_start)){
            $query->where('tbl_home.start_date', '>=', $date_start);
        }
        if(!empty($date_end)){
            $query->where('tbl_home.start_date', '<=', $date_end);
        }

        if(!empty($source_search)){
            $query->where('tbl_home.contact_role', $source_search);
        }

        //end

        if(!empty($search)){
            $query->where(function($q) use ($search){
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        if ($property_type_search) {
            if(is_array($property_type_search)){
                $query->whereIn('property_type', $property_type_search);
            }else{
                $query->where('property_type', $property_type_search);
            }
        }
        if (!empty($filter) && is_array($filter)) {
            if (isset($filter['price_min']) && $filter['price_min'] !== '') {
                $query->where('price', '>=', (float)$filter['price_min']);
            }
            if (isset($filter['price_max']) && $filter['price_max'] !== '') {
                $query->where('price', '<=', (float)$filter['price_max']);
            }
            if (isset($filter['area_min']) && $filter['area_min'] !== '') {
                $query->where('area', '>=', (float)$filter['area_min']);
            }
            if (isset($filter['area_max']) && $filter['area_max'] !== '') {
                $query->where('area', '<=', (float)$filter['area_max']);
            }
            if (isset($filter['plot_land']) && $filter['plot_land'] !== '') {
                $query->where('plot_land', $filter['plot_land']);
            }
            if (isset($filter['number_sheets']) && $filter['number_sheets'] !== '') {
                $query->where('number_sheets', $filter['number_sheets']);
            }
            if(isset($filter['property_type_search']) && !empty($filter['property_type_search'])){
                $arr = is_array($filter['property_type_search']) ? $filter['property_type_search'] : explode(',', $filter['property_type_search']);
                $query->whereIn('property_type', $arr);
            }
        }
        $utilities_filter = $this->request->input('utilities_filter');
        if (empty($utilities_filter) && !empty($filter) && is_array($filter) && isset($filter['utilities_filter'])) {
            $utilities_filter = $filter['utilities_filter'];
        }
        if (!empty($utilities_filter) && is_array($utilities_filter)) {
            foreach ($utilities_filter as $utilityId => $values) {
                if ($values !== null && $values !== '') {
                    $query->whereHas('utilities', function ($q) use ($utilityId, $values) {
                        $q->where('tbl_home_utilities.utility_id', $utilityId);
                        if (is_array($values)) {
                            $q->whereIn('tbl_home_utilities.value', $values);
                        } else {
                            $arr = explode(',', $values);
                            $q->whereIn('tbl_home_utilities.value', $arr);
                        }
                    });
                }
            }
        }
        if (!empty($status_search)) {
            if($status_search == 1){
                $query->where('is_featured', 1);
            }elseif($status_search == 2){
                $query->whereNotNull('video_url');
            }elseif($status_search == 3){
                $query->whereNull('video_url');
            }elseif($status_search == 4){
                $query->whereRaw('(is_new = 1 OR is_vip = 1)');
            }elseif($status_search == 6){
                $query->whereNotNull('tbl_home.end_date')->where('tbl_home.end_date', '<', date('Y-m-d'));
            }
        }
        if(!empty($check_save_home)){
            $query->whereExists(function ($q) use ($customer_id) {
                $q->select(DB::raw(1))
                ->from('tbl_favourite_home')
                ->whereColumn('tbl_favourite_home.home_id', 'tbl_home.id')
                ->where('tbl_favourite_home.customer_id', $customer_id);
            });
        }
        if ($this->request->query('exclude_id')) {
            $query->where('id', '!=', $this->request->query('exclude_id'));
        }
        if(empty($checkProvince)){
            if(!empty($lat) && !empty($lon)){
                $query->havingNotNull('distance');
                $query->having('distance', '<=', $distance);
            }
        } else {
            if(!empty($province_search) || !empty($ward_search)){
                $query->where(function ($q) use ($province_search,$ward_search){
                    $q->where('province_id', $province_search);
                    if(!empty($ward_search)){
                        $q->where('ward_id', $ward_search);
                    }
                });
            }
        }
        if(!empty($province_search) || !empty($ward_search) || (!empty($lat) && !empty($lon)) ){
            $query->orderBy('distance')->orderByDesc('id');
        }else{
            $query->orderByRaw("id desc");
        }
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);

        $allCustomerIds = $dtData->pluck('customer_id')->unique()->values()->toArray();
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $allCustomerIds]);
        $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $dtCustomer = collect($dataCustomer['data'] ?? []);

        $dtData->getCollection()->transform(function ($item) use ($dtCustomer) {
            $customer = $dtCustomer->where('id', $item->customer_id)->first();
            $item->customer = $customer;
            return $item;
        });
        $collection = HomeResources::collection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getDetail($id){
        $customer_id = $this->request->client->id ?? 0;
        $home = Home::with([
            'propertyType',
            'province',
            'ward',
            'direction',
            'legal',
            'interior',
            'media_items',
            'documents_red',
            'documents_other',
            'interior_amenities',
            'utilities',
            'utilities.options',
            'favourite',
            'save_home',

        ])->find($id);
        if (empty($home)) {
            return response()->json([
                'data' => null,
                'result' => false,
                'message' => 'Không tìm thấy bất động sản'
            ]);
        }

        $listReview = HomeReview::where('home_id', $id)->where('status', 1)->orderBy('id', 'desc')->paginate(3);

        $allCustomerIds = $listReview->pluck('customer_id')->unique()->values()->toArray();
        $allCustomerIds = array_unique(array_merge($allCustomerIds, [$home->customer_id, $customer_id]));
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $allCustomerIds]);
        $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $dtCustomer = collect($dataCustomer['data'] ?? []);
        $home->customer = $dtCustomer->where('id', $home->customer_id)->first();
        $home->customer_login = $dtCustomer->where('id', $customer_id)->first();

        $listReview->getCollection()->transform(function ($item) use ($dtCustomer) {
            $customer = $dtCustomer->where('id', $item->customer_id)->first();
            $item->customer = $customer;
            return $item;
        });


          $listReview->getCollection()->transform(function ($item) use ($dtCustomer) {
            $customer = $dtCustomer->where('id', $item->customer_id)->first();
            $item->customer = $customer;
            return $item;
        });

        $query = HomeReview::where('status', 1);
        $query->where('home_id', $id);

        $reviews = $query->get();

        $average_star = 0.0;
        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];

        if(!empty($reviews)) {
            foreach ($reviews as $r) {
                $starVal = (int)round($r->star);
                if ($starVal >= 1 && $starVal <= 5) {
                    $distribution[$starVal]++;
                }
            }
        }
        track_home_view($id,$customer_id);
        return response()->json([
            'data' => HomeResources::make($home),
            'review' => [
                'data' => ReviewHomeResource::collection($listReview),
                'next_page' => $listReview->hasMorePages() ? true : false,
                'distribution' => $distribution,
            ],
            'result' => true,
            'message' => 'Lấy chi tiết thành công'
        ]);
    }

    public function getRelatedData($id){
        $home = Home::find($id);
        if (empty($home)) {
            return response()->json([
                'data' => null,
                'result' => false,
                'message' => 'Không tìm thấy bất động sản'
            ]);
        }

        $relatedHomes = collect();
        if (!empty($home->property_type)) {
            $relatedHomes = Home::with([
                'propertyType',
                'province',
                'ward',
                'direction',
                'legal',
                'interior',
                'media_items',
                'interior_amenities',
                'utilities',
                'utilities.options',
                'favourite',
                'save_home'
            ])
            ->where('property_type', $home->property_type)
            ->where('id', '!=', $home->id)
            ->where('status', 2)
            ->where(function ($q) {
                $q->whereNull('tbl_home.end_date')
                  ->orWhere('tbl_home.end_date', '>=', date('Y-m-d'));
            })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

            $relatedCustomerIds = $relatedHomes->pluck('customer_id')->unique()->values()->toArray();
            if (!empty($relatedCustomerIds)) {
                $this->requestCustomer = clone $this->request;
                $this->requestCustomer->merge(['customer_id' => $relatedCustomerIds]);
                $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
                $dataCustomer = $responseCustomer->getData(true);
                $dtCustomer = collect($dataCustomer['data'] ?? []);
                
                $relatedHomes->transform(function ($item) use ($dtCustomer) {
                    $item->customer = $dtCustomer->where('id', $item->customer_id)->first();
                    return $item;
                });
            }
        }

        return response()->json([
            'result' => true,
            'data' => [
                'related_homes' => HomeResources::collection($relatedHomes),
            ],
            'message' => 'Lấy dữ liệu liên quan thành công'
        ]);
    }

    public function countHomes()
    {
        $customerIds = $this->request->input('customer_ids') ?? [];
        $admin = $this->request->input('admin') ?? 0;
        if (!is_array($customerIds)) {
            $customerIds = [$customerIds];
        }

        if(empty($admin)){
            $counts = Home::select('customer_id', DB::raw('count(*) as total'))
                ->whereIn('customer_id', $customerIds)
                ->groupBy('customer_id')
                ->get()
                ->pluck('total', 'customer_id')
                ->toArray();

            $result = [];
            foreach ($customerIds as $id) {
                $result[$id] = $counts[$id] ?? 0;
            }
        }else{
            $result['total_home'] = Home::count();
        }

        return response()->json([
            'result' => true,
            'data' => $result
        ]);
    }

    public function changeFavouriteHome()
    {
        $data = [];
        $home_id = $this->request->input('home_id');
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $status = $this->request->input('status') ?? 0;
        if (empty($customer_id)) {
            $data['result'] = false;
            $data['message'] = 'Vui lòng đăng nhập để sử dụng tính năng này!';
            return response()->json($data);
        }
        if (empty($home_id)) {
            $data['result'] = false;
            $data['message'] = 'Vui lòng chọn bất động sản!';
            return response()->json($data);
        }

        $home_ids = is_array($home_id) ? $home_id : [$home_id];
        $home_ids = array_values(array_unique(array_filter($home_ids)));

        if (empty($home_ids)) {
            $data['result'] = false;
            $data['message'] = 'Bất động sản không hợp lệ!';
            return response()->json($data);
        }

        $valid_home_ids = Home::whereIn('id', $home_ids)->pluck('id')->toArray();
        if (empty($valid_home_ids)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại BĐS!';
            return response()->json($data);
        }

        DB::beginTransaction();
        try {
            if ($status == 0) {
                DB::table('tbl_favourite_home')
                    ->whereIn('home_id', $valid_home_ids)
                    ->where('customer_id', $customer_id)
                    ->delete();
                $success = true;
                $event = 'un_favourite';
            } else {
                DB::table('tbl_favourite_home')
                    ->whereIn('home_id', $valid_home_ids)
                    ->where('customer_id', $customer_id)
                    ->delete();
                
                $insertData = [];
                $now = date('Y-m-d H:i:s');
                foreach ($valid_home_ids as $id) {
                    $insertData[] = [
                        'home_id' => $id,
                        'customer_id' => $customer_id,
                        'created_at' => $now
                    ];
                }
                $success = DB::table('tbl_favourite_home')->insert($insertData);
                $event = 'favourite';
            }
            DB::commit();

            $this->sendNotificationSocket([
                'channels' => [],
                'event' => $event,
                'data' => [
                    'home_id' => $home_ids,
                ],  
                'db_name' => config('database.connections.mysql.database')
            ],'favourite');


            if ($success) {
                $data['result'] = true;
                $data['message'] = 'Thành công';
            } else {
                $data['result'] = false;
                $data['message'] = 'Thất bại';
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function comment() {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $home_id = $this->request->input('home_id') ?? 0;
        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng này!'
            ]);
        }

        $home = Home::find($home_id);
        if (!$home) {
            return response()->json([
                'result' => false,
                'message' => 'Bất động sản không tồn tại'
            ]);
        }

        $id_parent = $this->request->input('id_comment') ?? 0;
        $reply_to_user_id = $this->request->input('reply_to_user_id') ?? 0;
        $comment = trim($this->request->input('comment'));

        if ($comment === '') {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng nhập nội dung để bình luận'
            ]);
        }

        DB::beginTransaction();
        try {
            $customer_id_reply = 0;
            $arrStaffs = [];
            if (!empty($id_parent)) {
                $commentReply = CommentHome::find($id_parent);
                if (!$commentReply) {
                    return response()->json([
                        'result' => false,
                        'message' => 'Bình luận cha không tồn tại'
                    ]);
                }
                if ($commentReply->id_parent != 0) {
                    $id_parent = $commentReply->id_parent;
                }
                $customer_id_reply = $commentReply->customer_id;
            }

            $commentHome = new CommentHome();
            $commentHome->home_id = $home_id;
            $commentHome->customer_id = $customer_id;
            $commentHome->comment = $comment;
            $commentHome->id_parent = $id_parent;
            $commentHome->reply_to_user_id = $id_parent ? $reply_to_user_id : 0;
            $commentHome->save();

            if ($id_parent) {
                CommentHome::where('id', $id_parent)->increment('count_reply');
            }

            $arrIdReply = [];
            $arrIdComment = [];
            if(!empty($reply_to_user_id)) {
                $arrStaffs[] = $reply_to_user_id;
                $arrIdReply[] = $reply_to_user_id;
            }
            if($home->customer_id == $customer_id){ // Nếu là chủ nhà
                $arrStaffs[] = $customer_id_reply; // Gửi thông báo cho người trả lời
                $arrIdReply[] = $reply_to_user_id;
                $author_id = 1;
            } else {
                $arrStaffs[] = $home->customer_id; // Gửi thông báo cho chủ nhà
                $author_id = 0;

                if(empty($id_parent)){
                    $arrIdComment[] = $home->customer_id;
                }
            }
            $arrStaffs = array_values(array_unique($arrStaffs));

            $dataCustomer = [];
            if (!empty($customer_id)) {
                $requestCustomer = new Request();
                $requestCustomer->merge(['list_id' => [$customer_id]]);
                $responseCustomer = $this->dbAccount->getListDetailCustomer($requestCustomer);
                $listDataCustomer = $responseCustomer->getData(true);
                if ($listDataCustomer['result']) {
                    $dataCustomer = $listDataCustomer['clients'];
                }
            }
            $client = $dataCustomer[$customer_id] ?? null;
            $this->sendNotificationSocket([
                'channels' => [],
                'event' => 'new_comment_home',
                'data' => [
                    'home_id' => $home_id,
                    'comment' => $comment,
                    'comment_id' => $commentHome->id,
                    'parent_id' => $id_parent,
                    'client' => $client,
                    'author' => $author_id,
                    'created_at' => $commentHome->created_at,
                    'list_user' => $arrStaffs,
                    'user_id' => $customer_id
                ],  
                'db_name' => config('database.connections.mysql.database')
            ],'comment');

             //noti
            $dtNotify = [];
            $dataCustomerPlayerid = [];
            if (!empty($arrStaffs)) {
                $requestCustomerPlayerid = new Request();
                $requestCustomerPlayerid->merge(['id' => $arrStaffs]);
                $responseCustomerPlayerid = $this->dbAccount->getDetailCustomerPlayerid($requestCustomerPlayerid);

                $listDataCustomerPlayerid = $responseCustomerPlayerid->getData(true);
                if ($listDataCustomerPlayerid['result']) {
                    $dataCustomerPlayerid = $listDataCustomerPlayerid['client'];
                }
            }
            $dtNotify['arr_object_id'] = $dataCustomerPlayerid;
            $dtNotify['home_id'] = $home_id;
            $dtNotify['comment_id'] = $commentHome->id;
            $dtNotify['parent_id'] = $id_parent;
            $dtNotify['object_type'] = 'comment';
            $dtNotify['customer'] = $client;
            $dtNotify['arr_reply_id'] = $arrIdReply;
            $dtNotify['arr_id_comment'] = $arrIdComment;
            $dtNotify['comment'] = $comment;
            Notification::notifyCommentHome($customer_id, $dtNotify, 'notifyCommentHome');
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => $id_parent
                    ? 'Trả lời bình luận thành công'
                    : 'Bình luận thành công'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function edit_comment($id = '')
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng này!'
            ]);
        }

        $commentHome = CommentHome::find($id);
        if (!$commentHome) {
            return response()->json([
                'result' => false,
                'message' => 'Bình luận không tồn tại'
            ]);
        }

        if ($commentHome->customer_id != $customer_id) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không thể chỉnh sửa bình luận của người khác'
            ]);
        }

        $comment = trim($this->request->input('comment'));
        if ($comment === '') {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng nhập nội dung để bình luận'
            ]);
        }

        DB::beginTransaction();
        try {
            $data_logs = json_decode($commentHome->data_logs, true) ?? [];
            $data_logs[] = [
                'comment' => $commentHome->comment,
                'updated_at' => $commentHome->updated_at,
            ];

            $commentHome->data_logs = json_encode($data_logs);
            $commentHome->comment = $comment;
            $success = $commentHome->save();

            $arrStaffs = [];
            if(!empty($commentHome->reply_to_user_id)) {
                $arrStaffs[] = $commentHome->reply_to_user_id;
            }
            
            if($commentHome->customer_id == $customer_id){ // Nếu là chủ nhà
                $author_id = 1;
            } else {
                $arrStaffs[] = $commentHome->customer_id; // Gửi thông báo cho chủ nhà
                $author_id = 0;
            }
            $arrStaffs = array_values(array_unique($arrStaffs));
            
            $this->sendNotificationSocket([
                'channels' => [],
                'event' => 'edit_comment_home',
                'data' => [
                    'home_id' => $commentHome->home_id,
                    'comment' => $comment,
                    'comment_id' => $commentHome->id,
                    'list_user' => $arrStaffs,
                    'user_id' => $customer_id
                ],  
                'db_name' => config('database.connections.mysql.database')
            ],'comment');

            DB::commit();
            return response()->json([
                'result' => $success,
                'message' => $success
                    ? 'Cập nhật bình luận thành công'
                    : 'Cập nhật bình luận không thành công'
            ]);
        }catch(Throwable $e){
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function delete_comment($id = '')
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng này!'
            ]);
        }

        $commentHome = CommentHome::find($id);
        if (!$commentHome) {
            return response()->json([
                'result' => false,
                'message' => 'Bình luận không tồn tại'
            ]);
        }

        if ($commentHome->customer_id != $customer_id) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không thể xóa bình luận của người khác'
            ]);
        }

        DB::beginTransaction();
        try {
            if ($commentHome->id_parent == 0) {
                $childIds = CommentHome::where('id_parent', $commentHome->id)->pluck('id');
                CommentHome::whereIn('id', $childIds)->delete();
            } else {
                CommentHome::where('id', $commentHome->id_parent)
                    ->where('count_reply', '>', 0)
                    ->decrement('count_reply');
            }

            $commentHome->delete();
            DB::commit();

               
            $this->sendNotificationSocket([
                'channels' => [],
                'event' => 'delete_comment_home',
                'data' => [
                    'home_id' => $commentHome->home_id,
                    'comment_id' => $commentHome->id,
                ],  
                'db_name' => config('database.connections.mysql.database')
            ],'comment');

            return response()->json([
                'result' => true,
                'message' => 'Xóa bình luận thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function report_comment(Request $request)
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng này!'
            ]);
        }

        $validated = Validator::make($request->all(), [
            'comment_home_id' => 'required|integer|exists:tbl_client_comment_home,id',
            'content_report_id' => 'required|integer|exists:tbl_content_report_comment,id',
            'note' => 'nullable|string|max:1000'
        ]);

        if ($validated->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validated->errors()->first()
            ]);
        }

        $dtComment = \App\Models\CommentHome::find($request->input('comment_home_id'));
        if($dtComment->customer_id == $customer_id){
            return response()->json([
                'result' => false,
                'message' => 'Bạn không thể báo cáo bình luận của chính mình'
            ]);
        }

        $contentReport = \App\Models\ContentReportComment::find($request->input('content_report_id'))->content ?? null;
        if(empty($contentReport)){
            return response()->json([
                'result' => false,
                'message' => 'Nội dung báo cáo không phù hợp'
            ]);
        }
        

        try {
            $report = \App\Models\ReportCommentHome::create([
                'comment_home_id' => $request->input('comment_home_id'),
                'customer_id' => $customer_id,
                'content_report_id' => $request->input('content_report_id') ?? 0,
                'note' => $contentReport
            ]);

            return response()->json([
                'result' => true,
                'message' => 'Đã gửi báo cáo vi phạm bình luận thành công.',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function like_comment($id = '', $like = 1)
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng này!'
            ]);
        }

        $comment = CommentHome::find($id);
        if (!$comment) {
            return response()->json([
                'result' => false,
                'message' => 'Bình luận không tồn tại'
            ]);
        }

        DB::beginTransaction();
        try {
            $ktEvent = DB::table('tbl_action_comment_home')
                ->where('customer_id', $customer_id)
                ->where('comment_home_id', $id)
                ->where('event', 'like')
                ->first();

            $ktDislikeEvent = DB::table('tbl_action_comment_home')
                ->where('customer_id', $customer_id)
                ->where('comment_home_id', $id)
                ->where('event', 'dislike')
                ->first();

            if ($like) {
                if (!empty($ktDislikeEvent)) {
                    CommentHome::where('id', $id)->where('count_dislike', '>', 0)->decrement('count_dislike');
                    DB::table('tbl_action_comment_home')->where('id', $ktDislikeEvent->id)->delete();
                    $event_new = 'un_dislike';
                }

                if (empty($ktEvent)) {
                    CommentHome::where('id', $id)->increment('count_like');

                    $now = now();
                    DB::table('tbl_action_comment_home')->insert([
                        'customer_id' => $customer_id,
                        'comment_home_id' => $id,
                        'event' => 'like',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                $event = 'like';
            } else {
                if (!empty($ktEvent)) {
                    CommentHome::where('id', $id)
                        ->where('count_like', '>', 0)
                        ->decrement('count_like');

                    DB::table('tbl_action_comment_home')
                        ->where('id', $ktEvent->id)
                        ->delete();
                }
                $event = 'un_like';
            }

            DB::commit();

            $arrStaffs = [];
            $arrStaffs[] = $comment->customer_id;
            $this->sendNotificationSocket([
                'channels' => [],
                'event' => $event,
                'data' => [
                    'home_id' => $comment->home_id,
                    'comment_id' => $comment->id,
                    'list_user' => $arrStaffs,
                    'user_id' => $customer_id
                ],  
                'db_name' => config('database.connections.mysql.database')
            ],'comment');
            if(!empty($event_new)){
                $this->sendNotificationSocket([
                    'channels' => [],
                    'event' => $event_new,
                    'data' => [
                        'home_id' => $comment->home_id,
                        'comment_id' => $comment->id,
                        'list_user' => $arrStaffs,
                        'user_id' => $customer_id
                    ],  
                    'db_name' => config('database.connections.mysql.database')
                ],'comment');
            }

            return response()->json([
                'result' => true,
                'message' => $like ? 'Thích bình luận thành công' : 'Bỏ thích thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function dislike_comment($id = '', $dislike = 1)
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng này!'
            ]);
        }

        $comment = CommentHome::find($id);
        if (!$comment) {
            return response()->json([
                'result' => false,
                'message' => 'Bình luận không tồn tại'
            ]);
        }

        DB::beginTransaction();
        try {
            $ktEvent = DB::table('tbl_action_comment_home')
                ->where('customer_id', $customer_id)
                ->where('comment_home_id', $id)
                ->where('event', 'dislike')
                ->first();

            $ktLikeEvent = DB::table('tbl_action_comment_home')
                ->where('customer_id', $customer_id)
                ->where('comment_home_id', $id)
                ->where('event', 'like')
                ->first();

            if ($dislike) {
                if (!empty($ktLikeEvent)) {
                    CommentHome::where('id', $id)->where('count_like', '>', 0)->decrement('count_like');
                    DB::table('tbl_action_comment_home')->where('id', $ktLikeEvent->id)->delete();
                    $event_new = 'un_like';
                }

                if (empty($ktEvent)) {
                    CommentHome::where('id', $id)->increment('count_dislike');

                    $now = now();
                    DB::table('tbl_action_comment_home')->insert([
                        'customer_id' => $customer_id,
                        'comment_home_id' => $id,
                        'event' => 'dislike',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $event = 'dislike';
                }
            } else {
                if (!empty($ktEvent)) {
                    CommentHome::where('id', $id)
                        ->where('count_dislike', '>', 0)
                        ->decrement('count_dislike');

                    DB::table('tbl_action_comment_home')
                        ->where('id', $ktEvent->id)
                        ->delete();
                }
                $event = 'un_dislike';
            }

            DB::commit();

            $arrStaffs = [];
            $arrStaffs[] = $comment->customer_id;
            $this->sendNotificationSocket([
                'channels' => [],
                'event' => $event,
                'data' => [
                    'home_id' => $comment->home_id,
                    'comment_id' => $comment->id,
                    'list_user' => $arrStaffs,
                    'user_id' => $customer_id
                ],  
                'db_name' => config('database.connections.mysql.database')
            ],'comment');
            if(!empty($event_new)){
                $this->sendNotificationSocket([
                    'channels' => [],
                    'event' => $event_new,
                    'data' => [
                        'home_id' => $comment->home_id,
                        'comment_id' => $comment->id,
                        'list_user' => $arrStaffs,
                        'user_id' => $customer_id
                    ],  
                    'db_name' => config('database.connections.mysql.database')
                ],'comment');
            }
            return response()->json([
                'result' => true,
                'message' => $dislike ? 'Không thích bình luận thành công' : 'Bỏ không thích thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function list_comment()
    {
        $id_home = $this->request->input('id_home', 0);
        $id_parent = $this->request->input('id_parent', 0);
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = (int) $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = (int) $this->request->query('per_page');
        }
        if ($current_page < 1) $current_page = 1;
        if ($per_page < 1) $per_page = 10;
        if ($per_page > 50) $per_page = 50;

        $focus_comment_id = (int) $this->request->input('focus_comment_id', 0);
        $focus_comment_info = null;

        if (!empty($focus_comment_id)) {
            $focusComment = CommentHome::find($focus_comment_id);
            if ($focusComment) {
                $id_home = $focusComment->home_id;
                
                if (empty($id_parent)) {
                    $targetComment = $focusComment->id_parent > 0 ? CommentHome::find($focusComment->id_parent) : $focusComment;
                    if ($targetComment) {
                        $position = CommentHome::where('home_id', $id_home)
                            ->where('id_parent', 0)
                            ->where(function ($query) use ($targetComment) {
                                $query->where('created_at', '>', $targetComment->created_at)
                                    ->orWhere(function ($q) use ($targetComment) {
                                        $q->where('created_at', '=', $targetComment->created_at)
                                          ->where('id', '>', $targetComment->id);
                                    });
                            })
                            ->count();

                        $current_page = (int) floor($position / $per_page) + 1;

                        $focus_comment_info = [
                            'comment_id' => $focus_comment_id,
                            'id_parent' => $focusComment->id_parent,
                            'home_id' => $id_home,
                            'page' => $current_page
                        ];
                    }
                } else {
                    if ($focusComment->id_parent == $id_parent) {
                        $position = CommentHome::where('home_id', $id_home)
                            ->where('id_parent', $id_parent)
                            ->where(function ($query) use ($focusComment) {
                                $query->where('created_at', '<', $focusComment->created_at)
                                    ->orWhere(function ($q) use ($focusComment) {
                                        $q->where('created_at', '=', $focusComment->created_at)
                                          ->where('id', '<', $focusComment->id);
                                    });
                            })
                            ->count();

                        $current_page = (int) floor($position / $per_page) + 1;

                        $focus_comment_info = [
                            'comment_id' => $focus_comment_id,
                            'id_parent' => $id_parent,
                            'home_id' => $id_home,
                            'page' => $current_page
                        ];
                    }
                }
            }
        }


        $orderBy = 'tbl_client_comment_home.created_at desc, tbl_client_comment_home.id desc';
        if (!empty($id_parent)) {
            $orderBy = 'tbl_client_comment_home.created_at asc, tbl_client_comment_home.id asc';
        }

        $list_comment = CommentHome::with('first_reply')
            ->select(
                'tbl_client_comment_home.id',
                'tbl_client_comment_home.comment',
                'tbl_client_comment_home.customer_id',
                'tbl_client_comment_home.created_at',
                'tbl_client_comment_home.count_like',
                'tbl_client_comment_home.count_dislike',
                'tbl_client_comment_home.count_reply',
                'tbl_client_comment_home.reply_to_user_id'
            )
            ->selectRaw('EXISTS(
                SELECT 1
                FROM tbl_action_comment_home
                WHERE comment_home_id = tbl_client_comment_home.id
                AND event = "like"
                AND customer_id = ?
            ) as is_like', [$customer_id])
            ->selectRaw('EXISTS(
                SELECT 1
                FROM tbl_action_comment_home
                WHERE comment_home_id = tbl_client_comment_home.id
                AND event = "dislike"
                AND customer_id = ?
            ) as is_dislike', [$customer_id])
            ->where('tbl_client_comment_home.home_id', $id_home)
            ->where('tbl_client_comment_home.id_parent', $id_parent)
            ->orderByRaw($orderBy)
            ->paginate($per_page, ['*'], 'page', $current_page);

        // Fetch user profiles for all customer IDs
        $idCustomers = $list_comment->getCollection()
            ->flatMap(function ($item) {
                $ids = [$item->customer_id];
                if (!empty($item->reply_to_user_id)) {
                    $ids[] = $item->reply_to_user_id;
                }
                if (!empty($item->first_reply)) {
                    $ids[] = $item->first_reply->customer_id;
                    if (!empty($item->first_reply->reply_to_user_id)) {
                        $ids[] = $item->first_reply->reply_to_user_id;
                    }
                }
                return $ids;
            })
            ->unique()
            ->values()
            ->toArray();

        $dataCustomer = [];
        if (!empty($idCustomers)) {
            $requestCustomer = new Request();
            $requestCustomer->merge(['list_id' => $idCustomers]);
            $responseCustomer = $this->dbAccount->getListDetailCustomer($requestCustomer);
            $listDataCustomer = $responseCustomer->getData(true);
            if ($listDataCustomer['result']) {
                $dataCustomer = $listDataCustomer['clients'];
            }
        }

        $home = Home::find($id_home);

        $list_comment->getCollection()->transform(function ($detail) use ($dataCustomer, $home, $customer_id) {
            $detail->client = $dataCustomer[$detail->customer_id] ?? null;
            if ($home && $detail->customer_id == $home->customer_id) {
                $detail->author = 1;
            } else {
                $detail->author = 0;
            }
            if (!empty($detail->reply_to_user_id)) {
                $detail->client_reply = $dataCustomer[$detail->reply_to_user_id] ?? null;
            }
            $detail->more_count_reply = ($detail->count_reply > 1) ? ($detail->count_reply - 1) : 0;
            $detail->more_count_reply = $detail->count_reply;
            if ($detail->first_reply) {
                $reply = $detail->first_reply;
                    $reply->client = $dataCustomer[$reply->customer_id] ?? null;

                    $ktLike = DB::table('tbl_action_comment_home')
                        ->where('comment_home_id', $reply->id)
                        ->where('customer_id', $customer_id)
                        ->where('event', 'like')->first();
                    $reply->is_like = !empty($ktLike) ? 1 : 0;

                    $ktDislike = DB::table('tbl_action_comment_home')
                        ->where('comment_home_id', $reply->id)
                        ->where('customer_id', $customer_id)
                        ->where('event', 'dislike')->first();
                    $reply->is_dislike = !empty($ktDislike) ? 1 : 0;

                    $reply->client_reply = $dataCustomer[$reply->reply_to_user_id] ?? null;
                    if ($home && $reply->customer_id == $home->customer_id) {
                        $reply->author = 1;
                    } else {
                        $reply->author = 0;
                    }
                }
            return $detail;
        });
        $data = [
            'data' => [
                'data' => $list_comment->items(),
                'links' => [
                    'first' => $list_comment->url(1),
                    'last' => $list_comment->url($list_comment->lastPage()),
                    'prev' => $list_comment->previousPageUrl(),
                    'next' => $list_comment->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $list_comment->currentPage(),
                    'from' => $list_comment->firstItem(),
                    'last_page' => $list_comment->lastPage(),
                    'links' => $list_comment->linkCollection(),
                    'path' => $list_comment->path(),
                    'per_page' => $list_comment->perPage(),
                    'to' => $list_comment->lastItem(),
                    'total' => $list_comment->total(),
                ]
            ],
            'result' => true
        ];
        if (!empty($focus_comment_info)) {
            $data['focus_comment_info'] = $focus_comment_info;
        }
        $data['count_comment'] = CommentHome::where('home_id', $id_home)->count();
        return response()->json($data);
    }

    public function detail() {
        $draft = $this->request->input('draft', 0);
        $step = $this->request->input('step') ?? 1;
        $web = $this->request->input('web', 0);
        $id = $this->request->input('id', 0);
        $is_new = $this->request->input('is_new_address', 0);
        $customer_id = $this->request->client->id ?? 0;
        $rules = [
            'title' => 'required',
            'detail' => 'required',
            'type' => 'required',
            'property_type' => 'required',
            'province_id' => 'required',
            'ward_id' => 'required',
            'address' => 'required',
            'price' => 'required|numeric',
            'area' => 'required|numeric',
        
        ];
        if($web == 1){
            $rules['contact_name'] = 'required';
            $rules['contact_phone'] = 'required';
        }
        if($draft == 0){
            if($web == 1){
                $rules['start_date'] = 'required|date';
                $rules['end_date'] = 'required|date';
            } else {
                if($step > 2){
                    $rules['start_date'] = 'required|date';
                    $rules['end_date'] = 'required|date';
                }
            }
        }

        $validator = Validator::make($this->request->all(), $rules);

       $type = $this->request->input('type');
        if($draft == 0){
            $validator = Validator::make($this->request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => implode('<br>', $validator->errors()->all())
                ]);
            }

            if($type == 1){
                if(empty(number_unformat($this->request->input('price_m2'),0))){
                    return response()->json([
                        'result' => false,
                        'message' => 'Vui lòng nhập giá trên m2'
                    ]);
                }
                if(empty($this->request->input('plot_land'))){
                    return response()->json([
                        'result' => false,
                        'message' => 'Vui lòng nhập thửa đất số'
                    ]);
                }
                if(empty($this->request->input('number_sheets'))){
                    return response()->json([
                        'result' => false,
                        'message' => 'Vui lòng nhập tờ bản đồ'
                    ]);
                }

                $province_id = $this->request->input('province_id');
                $ward_id = $this->request->input('ward_id');
                $plot_land = $this->request->input('plot_land');
                $number_sheets = $this->request->input('number_sheets');

                $queryDuplicate = Home::where('type', 1)
                    ->where('province_id', $province_id)
                    ->where('ward_id', $ward_id)
                    ->where('plot_land', $plot_land)
                    ->where('number_sheets', $number_sheets);

                if ($id > 0) {
                    $queryDuplicate->where('id', '!=', $id);
                }

                $exist = $queryDuplicate->first();
                if ($exist) {
                    return response()->json([
                        'result' => false,
                        'message' => 'Bất động sản này đã tồn tại trên hệ thống (Trùng Tỉnh/Thành, Phường/Xã, Thửa đất, Tờ bản đồ)'
                    ]);
                }
            }
        }

        if ($id > 0) {
            $home = Home::find($id);
            if (!$home) {
                return response()->json([
                    'result' => false,
                    'message' => 'Không tìm thấy bất động sản'
                ]);
            }
        } else {
            $home = new Home();
        }

        if($type == 1){
            $home->legal_status = $this->request->input('legal_status', 1);
        } else {
            $home->legal_status = null;
        }
     
        $home->type = $type;
        $home->property_type = $this->request->input('property_type');
        $home->province_id = $this->request->input('province_id');
        $home->ward_id = $this->request->input('ward_id');
        $home->is_new_address = $is_new;
        $home->address = $this->request->input('address');
        if ($this->request->has('latitude')) {
            $home->latitude = $this->request->input('latitude');
        }
        if ($this->request->has('longitude')) {
            $home->longitude = $this->request->input('longitude');
        }
        if ($this->request->has('name_location')) {
            $home->name_location = $this->request->input('name_location');
        }
        if($web == 1){
            if(empty($id)){
                $home->status = 2;
            }
            $home->customer_id = $this->request->input('customer_id');
        } else {
            if(empty($customer_id)){
                 return response()->json([
                    'result' => false,
                    'message' => 'Vui lòng đăng nhập để sử dụng tính năng!'
                ]);
            }

            $checkHome = Home::where('customer_id',$customer_id)->where('status',5)->where('id','!=',$id)->first();
            if(!empty($checkHome)){
                return response()->json([
                    'result' => false,
                    'message' => 'Bạn đã có bất động sản đang lưu nháp! Vui lòng thực hiện xong để tạo bất động sản mới!'
                ]);
            }
            $home->customer_id = $customer_id;

            if (!empty($this->request->input('save'))) {
                $home->status = 1;
                $home->draft = 0;
            } else {
                $home->draft = $draft;
                $home->status = 5;
            }
            $home->step = $this->request->input('step') ?? 1;
        }
        $home->currently_rent = number_unformat($this->request->input('currently_rent',0));
        $home->price = number_unformat($this->request->input('price'),0);
        $home->price_m2 = number_unformat($this->request->input('price_m2'),0);
        $home->loanability = ($this->request->input('loanability'));
        $home->area = number_unformat($this->request->input('area'),0);
        $home->plot_land = $this->request->input('plot_land') ?? null;
        $home->number_sheets = $this->request->input('number_sheets') ?? null;
        $utilitiesData = $this->request->input('utilities', []);
        $home->title = $this->request->input('title');
        $detail = $this->request->input('detail');
        if (!empty($detail)) {
            $detail = preg_replace_callback(
                '/(?:\+84|84|0)(?:2|3|5|7|8|9)(?:\s*[\.\-\(\)]?\s*\d){8,9}/',
                function ($matches) {
                    return preg_replace('/\d/', '*', $matches[0]);
                },
                $detail
            );
        }
        $home->detail = $detail;
        $description = $this->request->input('description');
        
        if (!empty($description)) {
            // Loại bỏ hoàn toàn các thẻ paragraph/div rỗng như <p>&nbsp;</p>, <p><br /></p> (kể cả có style/class hoặc chứa nhiều thẻ br)
            $description = preg_replace('/<p[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/i', '', $description);
            $description = preg_replace('/<div[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/div>/i', '', $description);

            // Loại bỏ các ký tự </br>, <br> hoặc \n liên tiếp từ 3 cái trở lên (chỉ giữ lại 1 cái)
            $html_el = '(?:<br\s*\/?>|<\/br>|<p[^>]*>\s*<br\s*\/?>|<div[^>]*>\s*<br\s*\/?>)';
            $description = preg_replace('/(?:' . $html_el . '[\s\t]*){2,3}/i', '<br>', $description);
            $description = preg_replace('/(\r?\n\s*){2,3}/', "\n", $description);

            // Mask phone numbers
            $description = preg_replace_callback(
                '/(?:\+84|84|0)(?:2|3|5|7|8|9)(?:\s*[\.\-\(\)]?\s*\d){8,9}/',
                function ($matches) {
                    return preg_replace('/\d/', '*', $matches[0]);
                },
                $description
            );
        }

        

        $home->description = $description;
        $home->contact_name = $this->request->input('contact_name');
        $home->contact_phone = $this->request->input('contact_phone');
        $home->contact_role = $this->request->input('contact_role', 1);
        $home->contact_time = $this->request->input('contact_time');
        $home->email_phone = $this->request->input('email_phone');
        $home->commission_rate = $this->request->input('commission_rate');
        $home->start_date = $this->request->input('start_date');
        $home->end_date = $this->request->input('end_date');


        // Process media files synchronously
        $existingMedia = $this->request->input('existing_media', []);
        $existingCaptions = $this->request->input('existing_media_captions', []);
        
        $mediaRecords = [];
        $newMediaCount = count($this->request->file('new_media') ?? []);
        $existingMediaCount = count($existingMedia);

       if($draft == 0){
            if($web == 1){
                if (($newMediaCount + $existingMediaCount) < 3) {
                    return response()->json([
                        'result' => false,
                        'message' => 'Yêu cầu tối thiểu tải lên 3 hình ảnh!'
                    ]);
                }
            } else {
                if($step > 2){
                    if (($newMediaCount + $existingMediaCount) < 3) {
                        return response()->json([
                            'result' => false,
                            'message' => 'Yêu cầu tối thiểu tải lên 3 hình ảnh!'
                        ]);
                    }
                }
            }
        }

        $existing_red_book = count($this->request->input('existing_red_book', []));
        $new_red_book = count($this->request->file('new_red_book') ?? []);
        
        if($type == 1){
            if ($draft == 0) {
                if($web == 1){
                    if ($home->legal_status == 1 && ($existing_red_book + $new_red_book) < 1) {
                        return response()->json([
                            'result' => false,
                            'message' => 'Yêu cầu tối thiểu tải lên 1 hình ảnh hoặc tài liệu Sổ đỏ, sổ hồng!'
                        ]);
                    }
                } else {
                    if($step > 2){
                        if ($home->legal_status == 1 && ($existing_red_book + $new_red_book) < 1) {
                            return response()->json([
                                'result' => false,
                                'message' => 'Yêu cầu tối thiểu tải lên 1 hình ảnh hoặc tài liệu Sổ đỏ, sổ hồng!'
                            ]);
                        }
                    }
                }
            }
        }

        $newlyUploadedFiles = [];
        $code = getReference('home');
        DB::beginTransaction();
        try {
            $home->save();

             if (empty($home->code)) {
                $home->code = $code;
                $home->save();
            }
            if(empty($id)){
                if($code == getReference('home')){
                    updateReference('home');
                }
            }
            
            $sortOrder = 0;
        
           // 1. Process existing media
            if (is_array($existingMedia)) {
                foreach ($existingMedia as $idx => $url) {
                    $cleanUrl = str_replace(asset('storage') . '/', '', $url);
                    $mediaRecords[] = [
                        'url' => $cleanUrl,
                        'caption' => isset($existingCaptions[$idx]) ? $existingCaptions[$idx] : '',
                        'sort_order' => $sortOrder++,
                    ];
                }
            }
            
            // 2. Process new media uploads
            if ($this->request->hasFile('new_media')) {
                $newMediaFiles = $this->request->file('new_media');
                $newCaptions = $this->request->input('new_media_captions', []);
                
                if (is_array($newMediaFiles)) {
                    foreach ($newMediaFiles as $idx => $file) {
                        $uploadedPath = $this->UploadFile($file, 'homes/' . $home->id, 800, 600, false);
                        if ($uploadedPath) {
                            $newlyUploadedFiles[] = $uploadedPath;
                            $mediaRecords[] = [
                                'url' => $uploadedPath,
                                'caption' => isset($newCaptions[$idx]) ? $newCaptions[$idx] : '',
                                'sort_order' => $sortOrder++,
                            ];
                        }
                    }
                } else {
                    $uploadedPath = $this->UploadFile($newMediaFiles, 'homes/' . $home->id, 800, 600, false);
                    if ($uploadedPath) {
                        $newlyUploadedFiles[] = $uploadedPath;
                        $mediaRecords[] = [
                            'url' => $uploadedPath,
                            'caption' => isset($newCaptions[0]) ? $newCaptions[0] : '',
                            'sort_order' => $sortOrder++,
                        ];
                    }
                }
            }
            
            // Backend validation: Require at least 3 photos
           if($draft == 0){
                if($web == 1){
                    if (count($mediaRecords) < 3) {
                        foreach ($newlyUploadedFiles as $filePath) {
                            $this->deleteFile($filePath);
                        }
                        return response()->json([
                            'result' => false,
                            'message' => 'Yêu cầu tối thiểu tải lên 3 hình ảnh!'
                        ]);
                    }
                } else {
                    if($step > 2){
                        if (count($mediaRecords) < 3) {
                            foreach ($newlyUploadedFiles as $filePath) {
                                $this->deleteFile($filePath);
                            }
                            return response()->json([
                                'result' => false,
                                'message' => 'Yêu cầu tối thiểu tải lên 3 hình ảnh!'
                            ]);
                        }
                    }
                }
            }
            
            // 3. Process video
            if ($this->request->hasFile('video_file')) {
                $uploadedVideoPath = $this->UploadFile($this->request->file('video_file'), 'homes/' . $home->id, 0, 0, false);
                if ($uploadedVideoPath) {
                    $newlyUploadedFiles[] = $uploadedVideoPath;
                    $home->video_url = $uploadedVideoPath;
                }
            } else {
                $videoUrlInput = $this->request->input('video_url', '');
                $cleanVideoUrl = str_replace(asset('storage') . '/', '', $videoUrlInput);
                $home->video_url = $cleanVideoUrl;
            }

            // Save media to separate table
            $home->media_items()->delete();
            foreach ($mediaRecords as $record) {
                $home->media_items()->create($record);
            }

            $amenities = $this->request->input('interior_amenities', []);
            $home->interior_amenities()->sync($amenities);

            // Sync dynamic utilities
            $syncUtilities = [];
            foreach ($utilitiesData as $utilityId => $value) {
                if ($value !== null && $value !== '') {
                    $syncUtilities[$utilityId] = ['value' => $value];
                }
            }
            $home->utilities()->sync($syncUtilities);

             // Process Red Book and Other Documents (only for Mua bán)
            $documentRecords = [];
            $docSortOrder = 0;

            if ($home->type == 1) {
                // Process Red Book files (type = 1)
                $existingRedBook = $this->request->input('existing_red_book', []);
                if (is_array($existingRedBook)) {
                    foreach ($existingRedBook as $url) {
                        $documentRecords[] = [
                            'url' => str_replace(asset('storage') . '/', '', $url),
                            'type' => 1,
                            'sort_order' => $docSortOrder++,
                        ];
                    }
                }
                if ($this->request->hasFile('new_red_book')) {
                    $newRedBookFiles = $this->request->file('new_red_book');
                    if (is_array($newRedBookFiles)) {
                        foreach ($newRedBookFiles as $file) {
                            $uploadedPath = $this->UploadFile($file, 'homes/' . $home->id, 800, 600, false);
                            if ($uploadedPath) {
                                $newlyUploadedFiles[] = $uploadedPath;
                                $documentRecords[] = [
                                    'url' => $uploadedPath,
                                    'type' => 1,
                                    'sort_order' => $docSortOrder++,
                                ];
                            }
                        }
                    } else {
                        $uploadedPath = $this->UploadFile($newRedBookFiles, 'homes/' . $home->id, 800, 600, false);
                        if ($uploadedPath) {
                            $newlyUploadedFiles[] = $uploadedPath;
                            $documentRecords[] = [
                                'url' => $uploadedPath,
                                'type' => 1,
                                'sort_order' => $docSortOrder++,
                            ];
                        }
                    }
                }

                // Process Other Document files (type = 2)
                $existingOtherDocs = $this->request->input('existing_other_documents', []);
                if (is_array($existingOtherDocs)) {
                    foreach ($existingOtherDocs as $url) {
                        $documentRecords[] = [
                            'url' => str_replace(asset('storage') . '/', '', $url),
                            'type' => 2,
                            'sort_order' => $docSortOrder++,
                        ];
                    }
                }
                if ($this->request->hasFile('new_other_documents')) {
                    $newOtherDocsFiles = $this->request->file('new_other_documents');
                    if (is_array($newOtherDocsFiles)) {
                        foreach ($newOtherDocsFiles as $file) {
                            $uploadedPath = $this->UploadFile($file, 'homes/' . $home->id, 800, 600, false);
                            if ($uploadedPath) {
                                $newlyUploadedFiles[] = $uploadedPath;
                                $documentRecords[] = [
                                    'url' => $uploadedPath,
                                    'type' => 2,
                                    'sort_order' => $docSortOrder++,
                                ];
                            }
                        }
                    } else {
                        $uploadedPath = $this->UploadFile($newOtherDocsFiles, 'homes/' . $home->id, 800, 600, false);
                        if ($uploadedPath) {
                            $newlyUploadedFiles[] = $uploadedPath;
                            $documentRecords[] = [
                                'url' => $uploadedPath,
                                'type' => 2,
                                'sort_order' => $docSortOrder++,
                            ];
                        }
                    }
                }

                // Validation: at least 1 document for each
                $redBookCount = collect($documentRecords)->where('type', 1)->count();
                $otherDocCount = collect($documentRecords)->where('type', 2)->count();

                if ($draft == 0) {
                    if($web == 1){
                        if ($home->legal_status == 1 && $redBookCount < 1) {
                            DB::rollBack();
                            foreach ($newlyUploadedFiles as $filePath) {
                                $this->deleteFile($filePath);
                            }
                            return response()->json([
                                'result' => false,
                                'message' => 'Yêu cầu tối thiểu tải lên 1 hình ảnh hoặc tài liệu Sổ đỏ, sổ hồng!'
                            ]);
                        }
                    } else {
                        if($step > 2){
                            if ($home->legal_status == 1 && $redBookCount < 1) {
                                DB::rollBack();
                                foreach ($newlyUploadedFiles as $filePath) {
                                    $this->deleteFile($filePath);
                                }
                                return response()->json([
                                    'result' => false,
                                    'message' => 'Yêu cầu tối thiểu tải lên 1 hình ảnh hoặc tài liệu Sổ đỏ, sổ hồng!'
                                ]);
                            }
                        }
                    }
                }
            }

            // Save documents to separate table
            $home->documents_red()->delete();
            $home->documents_other()->delete();
            foreach ($documentRecords as $record) {
                if ($record['type'] == 1) {
                    $home->documents_red()->create($record);
                } else {
                    $home->documents_other()->create($record);
                }
            }

            $home->save();
            DB::commit();
            return response()->json([
                'home_id' => $home->id,
                'result' => true,
                'message' => 'Lưu thông tin bất động sản thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($newlyUploadedFiles as $filePath) {
                $this->deleteFile($filePath);
            }
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }


    
    public function add_review()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện đánh giá!'
            ]);
        }

        $star = $this->request->input('star') ?? $this->request->input('start');
        $this->request->merge(['star' => $star]);

        $rules = [
            'home_id' => 'required|integer',
            'star' => 'required|numeric|between:1,5',
            'content' => 'nullable|string',
        ];

        $validator = Validator::make($this->request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => implode('<br>', $validator->errors()->all())
            ]);
        }

        $home_id = $this->request->input('home_id');
        $home = Home::find($home_id);
        if (!$home) {
            return response()->json([
                'result' => false,
                'message' => 'Bất động sản không tồn tại'
            ]);
        }

        // Prevent self-reviewing
        if ($home->customer_id == $customer_id) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không thể đánh giá bất động sản của chính mình!'
            ]);
        }

        // Check if already reviewed
        $existing = HomeReview::where('home_id', $home_id)
            ->where('customer_id', $customer_id)
            ->first();
        if ($existing) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn đã đánh giá bất động sản này rồi!'
            ]);
        }

        DB::beginTransaction();
        try {
            $review = new HomeReview();
            $review->home_id = $home_id;
            $review->customer_id = $customer_id;
            $review->poster_id = $home->customer_id ?? 0;
            $review->star = $star;
            $review->content = $this->request->input('content');
            $review->status = 0;
            $review->save();

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Đánh giá thành công'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function edit_review($id = '')
    {
        $isAdmin = false;
        if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            $isAdmin = true;
            $customer_id = \Illuminate\Support\Facades\Auth::guard('admin')->user()->id;
        } else {
            $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
            $token = $this->request->bearerToken('token');
            if ($token) {
                try {
                    $publicKey = file_get_contents(storage_path('keys/public.pem'));
                    $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($publicKey, 'RS256'));
                    $isAdmin = (!empty($decoded) && !empty($decoded->guard) && $decoded->guard == 'admin');
                    if ($isAdmin) {
                        $customer_id = $decoded->user_id ?? 0;
                    }
                } catch (\Exception $e) {}
            }
        }

        if (empty($customer_id) && !$isAdmin) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để chỉnh sửa đánh giá!'
            ]);
        }

        $review = HomeReview::find($id);
        if (!$review) {
            return response()->json([
                'result' => false,
                'message' => 'Đánh giá không tồn tại'
            ]);
        }

        if (!$isAdmin && $review->customer_id != $customer_id) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không thể chỉnh sửa đánh giá của người khác!'
            ]);
        }

        $star = $this->request->input('star') ?? $this->request->input('start');
        if ($star !== null) {
            $this->request->merge(['star' => $star]);
        }

        $rules = [
            'star' => 'required|numeric|between:1,5',
            'content' => 'nullable|string',
            'status' => 'nullable|integer',
        ];

        $validator = Validator::make($this->request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => implode('<br>', $validator->errors()->all())
            ]);
        }

        DB::beginTransaction();
        try {
            $review->star = $star;
            $review->content = $this->request->input('content');
            if ($this->request->has('status')) {
                $review->status = $this->request->input('status');
            }
            $review->save();

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Cập nhật đánh giá thành công'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function delete_review($id = '')
    {
        $isAdmin = false;
        if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            $isAdmin = true;
            $customer_id = \Illuminate\Support\Facades\Auth::guard('admin')->user()->id;
        } else {
            $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
            $token = $this->request->bearerToken('token');
            if ($token) {
                try {
                    $publicKey = file_get_contents(storage_path('keys/public.pem'));
                    $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($publicKey, 'RS256'));
                    $isAdmin = (!empty($decoded) && !empty($decoded->guard) && $decoded->guard == 'admin');
                    if ($isAdmin) {
                        $customer_id = $decoded->user_id ?? 0;
                    }
                } catch (\Exception $e) {}
            }
        }

        if (empty($customer_id) && !$isAdmin) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để xóa đánh giá!'
            ]);
        }

        $review = HomeReview::find($id);
        if (!$review) {
            return response()->json([
                'result' => false,
                'message' => 'Đánh giá không tồn tại'
            ]);
        }

        if (!$isAdmin && $review->customer_id != $customer_id) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không thể xóa đánh giá của người khác!'
            ]);
        }

        DB::beginTransaction();
        try {
            $review->delete();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Xóa đánh giá thành công'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function list_review()
    {
        $home_id = $this->request->input('home_id', 0);
        $poster_id = $this->request->input('poster_id', 0);

        $current_page = 1;
        $per_page = 5;
        if ($this->request->query('current_page')) {
            $current_page = (int) $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = (int) $this->request->query('per_page');
        }
        if ($current_page < 1) $current_page = 1;
        if ($per_page < 1) $per_page = 10;
        if ($per_page > 50) $per_page = 50;

        $isAdmin = false;
        $token = $this->request->bearerToken('token');
        if ($token) {
            try {
                $publicKey = file_get_contents(storage_path('keys/public.pem'));
                $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($publicKey, 'RS256'));
                $isAdmin = (!empty($decoded) && !empty($decoded->guard) && $decoded->guard == 'admin');
            } catch (\Exception $e) {}
        }

        $query = HomeReview::query();
        if (!$isAdmin) {
            $query->where('status', 1);
        }

        if (!empty($home_id)) {
            $query->where('home_id', $home_id);
        }
        if (!empty($poster_id)) {
            $query->where('poster_id', $poster_id);
        }

        $list = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($per_page, ['*'], 'page', $current_page);

        // Fetch customer profiles for all reviewer IDs (customer_id) and poster IDs (poster_id)
        $idCustomers = $list->getCollection()
            ->flatMap(function ($item) {
                return [$item->customer_id, $item->poster_id];
            })
            ->unique()
            ->values()
            ->toArray();

        $dataCustomer = [];
        if (!empty($idCustomers)) {
            $requestCustomer = new Request();
            $requestCustomer->merge(['list_id' => $idCustomers]);
            $responseCustomer = $this->dbAccount->getListDetailCustomer($requestCustomer);
            $listDataCustomer = $responseCustomer->getData(true);
            if ($listDataCustomer['result']) {
                $dataCustomer = $listDataCustomer['clients'];
            }
        }

        $list->getCollection()->transform(function ($review) use ($dataCustomer) {
            $review->customer = $dataCustomer[$review->customer_id] ?? null;
            $review->poster = $dataCustomer[$review->poster_id] ?? null;
            return $review;
        });

        return response()->json([
            'result' => true,
            'data' => [
                'data' => ReviewHomeResource::collection($list->getCollection()),
                'links' => [
                    'first' => $list->url(1),
                    'last' => $list->url($list->lastPage()),
                    'prev' => $list->previousPageUrl(),
                    'next' => $list->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $list->currentPage(),
                    'from' => $list->firstItem(),
                    'last_page' => $list->lastPage(),
                    'links' => $list->linkCollection(),
                    'path' => $list->path(),
                    'per_page' => $list->perPage(),
                    'to' => $list->lastItem(),
                    'total' => $list->total(),
                ]
            ]
        ]);
    }

    public function get_review_stats()
    {
        $home_id = $this->request->input('home_id', 0);
        $poster_id = $this->request->input('poster_id', 0);

        if (empty($home_id) && empty($poster_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Thiếu tham số home_id hoặc poster_id'
            ]);
        }

        $isAdmin = false;
        $token = $this->request->bearerToken('token');
        if ($token) {
            try {
                $publicKey = file_get_contents(storage_path('keys/public.pem'));
                $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($publicKey, 'RS256'));
                $isAdmin = (!empty($decoded) && !empty($decoded->guard) && $decoded->guard == 'admin');
            } catch (\Exception $e) {}
        }

        $query = HomeReview::query();
        if (!$isAdmin) {
            $query->where('status', 1);
        }
        if (!empty($home_id)) {
            $query->where('home_id', $home_id);
        }
        if (!empty($poster_id)) {
            $query->where('poster_id', $poster_id);
        }

        $reviews = $query->get();
        $total = $reviews->count();

        $average_star = 0.0;
        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];

        if ($total > 0) {
            $average_star = round($reviews->avg('star'), 1);
            foreach ($reviews as $r) {
                $starVal = (int)round($r->star);
                if ($starVal >= 1 && $starVal <= 5) {
                    $distribution[$starVal]++;
                }
            }
        }

        return response()->json([
            'result' => true,
            'data' => [
                'average_star' => $average_star,
                'total_reviews' => $total,
                'star_distribution' => $distribution
            ]
        ]);
    }

    //yêu thích video
    public function changeSaveHome()
    {
        $data = [];
        $home_id = !empty($this->request->input('home_id')) ? $this->request->input('home_id') : 0;
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $status = $this->request->input('status') ?? 0;
        if (empty($customer_id)) {
            $data['result'] = false;
            $data['message'] = 'Vui lòng đăng nhập để sử dụng tính năng này!';
            return response()->json($data);
        }
        $dtData = Home::find($home_id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn BĐS!';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if ($status == 0) {
                $success = DB::table('tbl_home_save')->where([
                    'home_id' => $home_id,
                    'customer_id' => $customer_id,
                ])->delete();
                $success = true;
                $event = 'un_save';
            } else {
                DB::table('tbl_home_save')->where([
                    'home_id' => $home_id,
                    'customer_id' => $customer_id,
                ])->delete();
                $success = DB::table('tbl_home_save')->insertGetId([
                    'home_id' => $home_id,
                    'customer_id' => $customer_id,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $event = 'save';
            }
            DB::commit();
            $this->sendNotificationSocket([
                'channels' => [],
                'event' => $event,
                'data' => [
                    'home_id' => $home_id,
                ],  
                'db_name' => config('database.connections.mysql.database')
            ],'save');


            if($customer_id != $dtData->customer_id){
                if($event == 'save'){
                    $dtNotify = [];
                    $dataCustomerPlayerid = [];
                    if (!empty($dtData->customer_id)) {
                        $requestCustomerPlayerid = new Request();
                        $requestCustomerPlayerid->merge(['id' => [$dtData->customer_id]]);
                        $responseCustomerPlayerid = $this->dbAccount->getDetailCustomerPlayerid($requestCustomerPlayerid);

                        $listDataCustomerPlayerid = $responseCustomerPlayerid->getData(true);
                        if ($listDataCustomerPlayerid['result']) {
                            $dataCustomerPlayerid = $listDataCustomerPlayerid['client'];
                        }
                    }

                    $dataCustomer = [];
                    if (!empty($customer_id)) {
                        $requestCustomer = new Request();
                        $requestCustomer->merge(['list_id' => [$customer_id]]);
                        $responseCustomer = $this->dbAccount->getListDetailCustomer($requestCustomer);
                        $listDataCustomer = $responseCustomer->getData(true);
                        if ($listDataCustomer['result']) {
                            $dataCustomer = $listDataCustomer['clients'];
                        }
                    }
                    $client = $dataCustomer[$customer_id] ?? null;
                    $dtNotify['arr_object_id'] = $dataCustomerPlayerid;
                    $dtNotify['home_id'] = $home_id;
                    $dtNotify['title_home'] = $dtData->title;
                    $dtNotify['object_type'] = 'home';
                    $dtNotify['customer'] = $client;
                    Notification::notifyLikePost($customer_id, $dtNotify, 'notifyLikePost');
                }
            }

            if ($success) {
                $data['result'] = true;
                $data['message'] = 'Thành công';
            } else {
                $data['result'] = false;
                $data['message'] = 'Thất bại';
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListUtilities()
    {
        $property_type = !empty($this->request->input('property_type')) ? $this->request->input('property_type') : 0;
        $transaction_type = $this->request->input('transaction_type');

        $query = Utility::with('options')->where('active', 1);

        if (!empty($property_type)) {
            $query->whereHas('type_properties', function ($q) use ($property_type) {
                $q->where('tbl_type_property.id', $property_type);
            });
        }

        if (!empty($transaction_type)) {
            $query->whereIn('transaction_type', [$transaction_type, 3]);
        }

        $list = $query->get();

        $list = $list->map(function ($item) {
            $item->icon = !empty($item->icon) ? $this->baseUrl . '/' . $item->icon : null;
            return $item;
        });

        $interior_amenities = InteriorAmenity::where('active',1)->get();
        $interior_amenities = $interior_amenities->map(function ($item) {
            $item->icon = !empty($item->icon) ? $this->baseUrl . '/' . $item->icon : null;
            return $item;
        });

        return response()->json([
            'result' => true,
            'data' => $list,
            'interior_amenities' => $interior_amenities
        ]);
    }

    public function checkHome(){
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        
        $home = Home::where('customer_id',$customer_id)->where('status',5)->first();
        if(!empty($home)){
            return response()->json([
                'result' => true,
                'home_id' => $home->id
            ]);
        }
        return response()->json([
            'result' => true,
            'home_id' => 0
        ]);
    }
    

    public function getListHomeAll(){
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }

        $limit = 6;
        if ($this->request->query('limit')) {
            $limit = $this->request->query('limit');
        }

        $property_type_search = $this->request->input('property_type_search');
        $check_save_home = $this->request->input('check_save_home');
        $type_search = $this->request->input('type_search') ?? 1;
        $lat = !empty($this->request->input('lat')) ? $this->request->input('lat') : 0;
        $lon = !empty($this->request->input('lon')) ? $this->request->input('lon') : 0;
        $province_search = !empty($this->request->input('province_id')) ? $this->request->input('province_id') : 0;
        $ward_search = !empty($this->request->input('ward_id')) ? $this->request->input('ward_id') : 0;
        $checkProvince = false;
        if(!empty($province_search)){
            $checkProvince = true;
        }
        $distance = !empty($this->request->input('distance')) ? $this->request->input('distance') : 10;
        $filter = $this->request->input('filter');
        if (!empty($filter) && is_string($filter)) {
            $filter = json_decode($filter, true);
        }

        $customer_id = $this->request->client->id ?? 0;
        $query = Home::select('tbl_home.*',DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))->with([
            'propertyType',
            'province',
            'ward',
            'direction',
            'legal',
            'interior',
            'media_items',
            'interior_amenities',
            'utilities',
            'utilities.options',
            'favourite',
            'save_home'
        ])->where('id', '!=', 0);
        $query->where(function ($q) {
            $q->where('status', 2);
        });
        // Tin hết hạn sẽ không hiển thị
        $query->where(function ($q) {
            $q->whereNull('tbl_home.end_date')
              ->orWhere('tbl_home.end_date', '>=', date('Y-m-d'));
        });
        if ($this->request->query('customer_id')) {
            $query->where('customer_id', $this->request->query('customer_id'));
        }
        $query->where('type', $type_search);
        if ($property_type_search) {
            if(is_array($property_type_search)){
                $query->whereIn('property_type', $property_type_search);
            }else{
                $query->where('property_type', $property_type_search);
            }
        }
        if (!empty($filter) && is_array($filter)) {
            if (isset($filter['price_min']) && $filter['price_min'] !== '') {
                $query->where('price', '>=', (float)$filter['price_min']);
            }
            if (isset($filter['price_max']) && $filter['price_max'] !== '') {
                $query->where('price', '<=', (float)$filter['price_max']);
            }
            if (isset($filter['area_min']) && $filter['area_min'] !== '') {
                $query->where('area', '>=', (float)$filter['area_min']);
            }
            if (isset($filter['area_max']) && $filter['area_max'] !== '') {
                $query->where('area', '<=', (float)$filter['area_max']);
            }
            if (isset($filter['plot_land']) && $filter['plot_land'] !== '') {
                $query->where('plot_land', $filter['plot_land']);
            }
            if (isset($filter['number_sheets']) && $filter['number_sheets'] !== '') {
                $query->where('number_sheets', $filter['number_sheets']);
            }
            if(isset($filter['property_type_search']) && !empty($filter['property_type_search'])){
                $arr = is_array($filter['property_type_search']) ? $filter['property_type_search'] : explode(',', $filter['property_type_search']);
                $query->whereIn('property_type', $arr);
            }
        }
        $utilities_filter = $this->request->input('utilities_filter');
        if (empty($utilities_filter) && !empty($filter) && is_array($filter) && isset($filter['utilities_filter'])) {
            $utilities_filter = $filter['utilities_filter'];
        }
        if (!empty($utilities_filter) && is_array($utilities_filter)) {
            foreach ($utilities_filter as $utilityId => $values) {
                if ($values !== null && $values !== '') {
                    $query->whereHas('utilities', function ($q) use ($utilityId, $values) {
                        $q->where('tbl_home_utilities.utility_id', $utilityId);
                        if (is_array($values)) {
                            $q->whereIn('tbl_home_utilities.value', $values);
                        } else {
                            $arr = explode(',', $values);
                            $q->whereIn('tbl_home_utilities.value', $arr);
                        }
                    });
                }
            }
        }
        if(!empty($check_save_home)){
            $query->whereExists(function ($q) use ($customer_id) {
                $q->select(DB::raw(1))
                ->from('tbl_favourite_home')
                ->whereColumn('tbl_favourite_home.home_id', 'tbl_home.id')
                ->where('tbl_favourite_home.customer_id', $customer_id);
            });
        }
        if ($this->request->query('exclude_id')) {
            $query->where('id', '!=', $this->request->query('exclude_id'));
        }
        if(empty($checkProvince)){
            if(!empty($lat) && !empty($lon)){
                $query->havingNotNull('distance');
                $query->having('distance', '<=', $distance);
            }
        } else {
            if(!empty($province_search) || !empty($ward_search)){
                $query->where(function ($q) use ($province_search,$ward_search){
                    $q->where('province_id', $province_search);
                    if(!empty($ward_search)){
                        $q->where('ward_id', $ward_search);
                    }
                });
            }
        }
        if(!empty($province_search) || !empty($ward_search) || (!empty($lat) && !empty($lon)) ){
            $query->orderBy('distance')->orderByDesc('id');
        }else{
            $query->orderByRaw("id desc");
        }

        // Clone queries for 3 regions
        $query1 = clone $query;
        $data1 = $query1->where('is_featured', 1)->limit($limit)->get();

        $query2 = clone $query;
        $data2 = $query2->whereNotNull('video_url')->limit($limit)->get();

        $query3 = clone $query;
        $data3 = $query3->whereRaw('(is_new = 1 OR is_vip = 1)')->limit($limit)->get();

        // Collect all unique customer IDs to load customer info in batch
        $allCustomerIds = collect()
            ->concat($data1->pluck('customer_id'))
            ->concat($data2->pluck('customer_id'))
            ->concat($data3->pluck('customer_id'))
            ->unique()
            ->values()
            ->toArray();

        if (!empty($allCustomerIds)) {
            $this->requestCustomer = clone $this->request;
            $this->requestCustomer->merge(['customer_id' => $allCustomerIds]);
            $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $dtCustomer = collect($dataCustomer['data'] ?? []);

            $mapCustomer = function ($item) use ($dtCustomer) {
                $item->customer = $dtCustomer->where('id', $item->customer_id)->first();
                return $item;
            };

            $data1->transform($mapCustomer);
            $data2->transform($mapCustomer);
            $data3->transform($mapCustomer);
        }

        return response()->json([
            'data' => [
                'hot' => HomeResources::collection($data1),
                'video' => HomeResources::collection($data2),
                'vip' => HomeResources::collection($data3),
            ],
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function extendHome()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện gia hạn!'
            ]);
        }

        if(!empty($customer_id)){
            $this->requestCustomer = clone $this->request;
            $this->requestCustomer->merge(['customer_id' => [$customer_id]]);
            $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $dtCustomer = collect($dataCustomer['data'] ?? []);
            $customer_login = $dtCustomer->where('id', $customer_id)->first();
        }

        $rules = [
            'home_id' => 'required|integer',
            'days' => 'required|integer|min:1',
        ];

        $message = [
            'home_id.required' => 'Tin đăng không được để trống',
            'home_id.integer' => 'Tin đăng không đúng định dạng',
            'days.required' => 'Số ngày gia hạn không được để trống',
            'days.integer' => 'Số ngày gia hạn không đúng định dạng',
            'days.min' => 'Số ngày gia hạn tối thiểu là 1',
        ];

        $validator = Validator::make($this->request->all(), $rules,$message);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $home_id = $this->request->input('home_id');
        $days = (int)$this->request->input('days');

        $home = Home::find($home_id);
        if (!$home) {
            return response()->json([
                'result' => false,
                'message' => 'Tin đăng không tồn tại!'
            ]);
        }

        if($customer_login['type_client'] != 2 ){
            if ($home->customer_id != $customer_id) {
                return response()->json([
                    'result' => false,
                    'message' => 'Bạn không có quyền gia hạn tin đăng này!'
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $new_end_date = date('Y-m-d', strtotime("+$days days"));
            $home->end_date = $new_end_date;
            $home->save();

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Gia hạn tin thành công',
                'end_date' => $new_end_date
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function checkEditHome()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện chỉnh sửa tin đăng!'
            ]);
        }

        if(!empty($customer_id)){
            $this->requestCustomer = clone $this->request;
            $this->requestCustomer->merge(['customer_id' => [$customer_id]]);
            $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $dtCustomer = collect($dataCustomer['data'] ?? []);
            $customer_login = $dtCustomer->where('id', $customer_id)->first();
        }
        if (empty($customer_login)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện chỉnh sửa tin đăng!'
            ]);
        }

        $rules = [
            'home_id' => 'required|integer',
        ];

        $message = [
            'home_id.required' => 'Tin đăng không được để trống',
            'home_id.integer' => 'Tin đăng không đúng định dạng',
        ];

        $validator = Validator::make($this->request->all(), $rules,$message);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $home_id = $this->request->input('home_id');

        $home = Home::find($home_id);
        if (!$home) {
            return response()->json([
                'result' => false,
                'message' => 'Tin đăng không tồn tại!'
            ]);
        }

        if($customer_login['type_client'] != 2 ){
            if ($home->customer_id != $customer_id) {
                return response()->json([
                    'result' => false,
                    'message' => 'Bạn không có quyền chỉnh sửa tin đăng này!'
                ]);
            }
        }

        $arrStatus = [1,6];
        if(!in_array($home->status, $arrStatus)){
            return response()->json([
                'result' => false,
                'message' => 'Tin đang trong trạng thái không thể chỉnh sửa!'
            ]);
        }

        return response()->json([
            'result' => true,
            'home_id' => $home_id,
            'message' => 'Tin đăng có thể chỉnh sửa!'
        ]);
    }
}
