<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\TransactionResources;
use App\Models\Clients;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\AdminService;
use App\Services\NotiService;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\TransactionPayment;

class ReportController extends AuthController
{
    use UploadFile;

    protected $AdminService;
    protected $adminNoti;
    protected $_locale;

    public function __construct(Request $request, AdminService $adminService, NotiService $notiService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->AdminService = $adminService;
        $this->adminNoti = $notiService;
        $this->_locale = $request->_locale;
    }

    public function getListReportTransaction(){
        
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $customer_search = $this->request->input('customer_search') ?? 0;
        $date_search = $this->request->input('date_search') ?? null;
        $status_search = $this->request->input('status_search') ?? -1;

        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0], true);
            $end_date = to_sql_date($date_search[1], true);
        } else {
            $start_date = null;
            $end_date = null;
        }


        $tb_transaction = DB::query()
            ->from('tbl_transaction')
            ->select('tbl_transaction.leader_id',
                DB::raw('COUNT(tbl_transaction.id) as count_transaction'),
                DB::raw('SUM(tbl_transaction.total) as total'),
                DB::raw('SUM(tbl_transaction.total_promotion) as total_promotion'),
                DB::raw('SUM(tbl_transaction.total_discount) as total_discount'),
                DB::raw('SUM(tbl_transaction.total_vat) as total_vat'),
                DB::raw('SUM(tbl_transaction.grand_total) as grand_total'),
                DB::raw('SUM(tbl_transaction.total_discount_leader) as total_leader'),
                DB::raw('SUM(tbl_transaction.total_accumulate) as total_accumulate')
            )
            ->where(function ($query) use ($start_date,$end_date,$date_search,$customer_search,$status_search){
                if (!empty($date_search)){
                    $query->whereBetween('date', [$start_date, $end_date]);
                }
                if (!empty($customer_search)){
                    $query->where('leader_id', $customer_search);
                }
                if ($status_search != -1) {
                    $query->where('tbl_transaction.status', $status_search);
                }
            })
            ->groupBy('tbl_transaction.leader_id');

        $query = Clients::select('id', 'fullname', 'phone', 'email', 'avatar',
            DB::raw('COALESCE(tb_transaction.count_transaction,0) as total_transaction'),
            DB::raw('COALESCE(tb_transaction.total,0) as total'),
            DB::raw('COALESCE(tb_transaction.total_promotion,0) as total_promotion'),
            DB::raw('COALESCE(tb_transaction.total_discount,0) as total_discount'),
            DB::raw('COALESCE(tb_transaction.total_vat,0) as total_vat'),
            DB::raw('COALESCE(tb_transaction.grand_total,0) as grand_total'),
            DB::raw('COALESCE(tb_transaction.total_leader,0) as total_leader'),
            DB::raw('COALESCE(tb_transaction.total_accumulate,0) as total_accumulate')
        )
            ->where('id', '!=', 0);
        $query->joinSub($tb_transaction, 'tb_transaction', 'tb_transaction.leader_id', '=', 'tbl_clients.id');
        $query->when(!empty($search), function ($q) use ($search) {
            $q->where('fullname', 'like', '%' . trim($search) . '%');
        });
        $filtered = (clone $query)->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $data[$key]['avatar_new'] = !empty($value->avatar)
                ? env('STORAGE_URL') . '/' . $value->avatar
                : null;
            }
        }
        $total = Clients::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListReportTransactionDetail(){
     
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 100);
        if ($length == -1){
            $length = 100000;
        }

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');
        
        $customer_search = $this->request->input('customer_search') ?? 0;
        $customer_leader_search = $this->request->input('customer_leader_search') ?? 0;
        $product_search = $this->request->input('product_search') ?? 0;
        $date_search = $this->request->input('date_search') ?? null;
        $status_search = $this->request->input('status_search') ?? -1;

        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0], true);
            $end_date = to_sql_date($date_search[1], true);
        } else {
            $start_date = null;
            $end_date = null;
        }


        $query = Transaction::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        },'customer_leader' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }]);
        $query->select('tbl_transaction.*');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%$search%");
                $q->orWhere('date', 'like', "%$search%");
                $q->orWhereHas('customer', function ($instance) use ($search) {
                    $instance->where('fullname', 'like', "%$search%");
                    $instance->orWhere('phone', 'like', "%$search%");
                });
            });
        }
        if ($status_search != -1) {
            $query->where('tbl_transaction.status', $status_search);
        }
        if (!empty($customer_search)) {
            $query->where('tbl_transaction.customer_id', $customer_search);
        }
        if (!empty($customer_leader_search)) {
            $query->where('tbl_transaction.leader_id', $customer_leader_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        if(!empty($product_search)) {
            $query->whereHas('transaction_item', function ($q) use ($product_search) {
                $q->where('product_id', $product_search);
            });
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();


        $allProductIds = $data->map(function ($item) {
            return $item->transaction_item->pluck('product_id')->toArray();
        })->flatten()->unique()->toArray();
        $arrProductId = $allProductIds;
        $arrProductId = !empty($arrProductId) ? $arrProductId : [0];
        $this->requestProduct = clone $this->request;
        $this->requestProduct->merge(['arrProduct' => $arrProductId]);
        $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
        $dataProduct = $responseProduct->getData(true);
        $dtProduct = collect($dataProduct['data']['data'] ?? []);

        $dataNew = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtCustomer = $value->customer ?? null;
                if (!empty($dtCustomer)) {
                    $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL') . '/' . $dtCustomer->avatar : null;
                    $data[$key]['customer']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]['customer_leader'] = null;
                }
                $dtCustomerLeader = $value->customer_leader ?? null;
                if (!empty($dtCustomerLeader)) {
                    $dtImage = !empty($dtCustomerLeader->avatar) ? env('STORAGE_URL') . '/' . $dtCustomerLeader->avatar : null;
                    $data[$key]['customer_leader']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]['customer_leader'] = null;
                }
                $dataNew[]=[
                    'id' => $value->id,
                    'date' => $value->date,
                    'reference_no' => $value->reference_no,
                    'status' => $value->status,
                    'customer' => $value->customer,
                    'customer_leader' => $value->customer_leader,
                    'total' => $value->total,
                    'total_promotion' => $value->total_promotion,
                    'total_discount' => $value->total_discount,
                    'total_vat' => $value->total_vat,
                    'grand_total' => $value->grand_total,
                    'type' => 'parent',
                    'product_id' => 0,
                    'product_name' => '',
                    'product_image' => null,
                    'quantity' => 0,
                    'price' => 0,
                    'total_item' => 0,
                    'variant_option' => null,
                    'total_discount_leader' => $value->total_discount_leader,
                    'total_accumulate' => $value->total_accumulate,
                    'level' => $value->level,
                    'check_leader' => $value->check_leader
                ];

                foreach ($value->transaction_item as $tranItem) {
                    $product = $dtProduct->where('id', $tranItem->product_id)->first() ?? [];
                    $variant = collect($product['variant_option'] ?? [])->where('id', $tranItem->variant_id)->first();
                    $product['variant_option'] = $variant;
                    $tranItem->product = $product;
                    $dataNew[] = [
                        'id' => 0,
                        'date' => null,
                        'reference_no' => $value->reference_no,
                        'status' => 0,
                        'customer' => null,
                        'customer_leader' => null,
                        'total' => $tranItem->total,
                        'total_promotion' => 0,
                        'total_discount' => 0,
                        'total_vat' => 0,
                        'grand_total' => 0,
                        'type' => 'child',
                        'product_id' => $tranItem->product_id,
                        'product_name' => $tranItem->product['name'],
                        'product_image' => $tranItem->product['image'],
                        'quantity' => $tranItem->quantity,
                        'price' => $tranItem->price,
                        'total_item' => $tranItem->total,
                        'variant_option' => (!empty($variant['category']['name']) ? $variant['category']['name'] .':'. $variant['name'] : ''),
                        'total_discount_leader' => 0,
                        'total_accumulate' => 0,
                        'level' => null,
                        'check_leader' => false
                    ];
                }
            }
        }
        $total = Transaction::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $dataNew,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}