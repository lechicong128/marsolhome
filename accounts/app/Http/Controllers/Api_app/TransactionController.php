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

class TransactionController extends AuthController
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

    public function getList()
    {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        if ($length == -1){
            $length = 100000;
        }

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


        $query = Transaction::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])
            ->where('id', '!=', 0);
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
            if ($status_search == -2) {
                $query->whereIn('status', [
                    Config::get('constant')['status_request'],
                    Config::get('constant')['status_approve'],
                ]);
            } elseif ($status_search == -3) {
                $query->where('warehouse_status', 0);
            } elseif ($status_search == -4) {
                $query->where('warehouse_status', 1);
            } else {
                $query->where('status', $status_search);
            }
        }
        if (!empty($customer_search)) {
            $query->where('customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        $allTransactionId = $data->pluck('id')->unique()->values()->toArray();

        $allInvoiceItem = [];
        if (!empty($allTransactionId)) {
            $allInvoiceItem = DB::table('tbl_invoice_item')
            ->select('tbl_invoice_item.id','tbl_invoice_item.transaction_id','invoice_id','reference_no','reference_no_bill','status_invoice','date','status')
            ->join('tbl_invoice', 'tbl_invoice_item.invoice_id', '=', 'tbl_invoice.id')
            ->whereIn('transaction_id', $allTransactionId)->get()->toArray();
            $allInvoiceItem = array_reduce($allInvoiceItem, function ($carry, $item) {
                $carry[$item->transaction_id][] = $item;
                return $carry;
            }, []);
        }
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtCustomer = $value->customer ?? null;
                if (!empty($dtCustomer)) {
                    $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL') . '/' . $dtCustomer->avatar : null;
                    $data[$key]['customer']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]->customer = null;
                }
                $data[$key]['invoice']= $allInvoiceItem[$value->id] ?? [];
            }
        }
        $total = Transaction::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function countAll()
    {
        $customer_search = $this->request->input('customer_search') ?? 0;
        $date_search = $this->request->input('date_search') ?? null;
        $date_search_end = $this->request->input('date_search_end') ?? null;
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
        }])->where('id', '!=', 0);
        if (($customer_search)) {
            $query->where('customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        $query->whereIn('status', [
            Config::get('constant')['status_request'],
            Config::get('constant')['status_payment'],
            Config::get('constant')['status_approve'],
        ]);
        $follow = $query->count();

        $arr = getListStatusTransaction();
        foreach ($arr as $key => $value) {
            $status = $value['id'];
            $query = Transaction::where('id', '!=', 0);
            if (($customer_search)) {
                $query->where('customer_id', $customer_search);
            }
            if (!empty($date_search)) {
                $query->whereBetween('date', [$start_date, $end_date]);
            }
            $query->where('status', $status);
            $arr[$key]['count'] = $query->count();
        }

        $query = Transaction::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])->where('id', '!=', 0);
        if (($customer_search)) {
            $query->where('customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        $query->where('warehouse_status','=',0);
        $warehouse_status_0 = $query->count();

        $query = Transaction::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])->where('id', '!=', 0);
        if (($customer_search)) {
            $query->where('customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        $query->where('warehouse_status','=',1);
        $warehouse_status_1 = $query->count();

        return response()->json([
            'follow' => $follow,
            'arr' => $arr,
            'warehouse_status_0' => $warehouse_status_0,
            'warehouse_status_1' => $warehouse_status_1,
            'result' => true,
            'message' => 'Thành công'
        ]);
    }

    public function getDetail()
    {
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (!empty($client)) {
            $dtImage = !empty($client->avatar) ? env('STORAGE_URL') . '/' . $client->avatar : null;
            $client->avatar = $dtImage;
        }
        $data['result'] = true;
        $data['client'] = $client;
        $data['message'] = 'Lấy thông tin khách hàng thành công';
        return response()->json($data);
    }

    public function delete()
    {
        $id = $this->request->input('id') ?? 0;
        $is_admin = $this->request->input('is_admin') ?? 0;
        $dtData = Transaction::find($id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại đơn hàng';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if($is_admin == 0) {
                if (!empty($dtData->payment)) {
                    $data['result'] = false;
                    $data['message'] = 'Đơn hàng đã có thanh toán không thể xóa! Vui lòng xóa phiếu thanh toán trước!';
                    return response()->json($data);
                }
            }

            $allTransactionItem = $dtData->transaction_item;
            foreach ($allTransactionItem as $key => $value) {
                if($value->invoice()->count() > 0) {
                    $data['result'] = false;
                    $data['message'] = 'Đơn hàng đã có xuất hóa đơn không thể xóa!';
                    return response()->json($data);
                }
            }
            $dtData->delete();
            $dtData->transaction_item()->delete();
            $dtData->payment()->delete();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData()
    {
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
        $customer_id = $this->request->client->id ?? 0;
        $cron = $this->request->input('cron');
        $transaction_id = $this->request->input('transaction_id') ?? 0;
        $customer_search = $this->request->input('customer_search') ?? 0;
        $status_search = $this->request->input('status_search');
        $search = $this->request->input('name_search') ?? null;
        $query = Transaction::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%$search%");
            });
        }
        if (!empty($transaction_id)) {
            $query->where('id', $transaction_id);
        }
        $query->where(function ($q) use ($status_search, $customer_id,$cron) {
            if ($status_search != -1) {
                    $status_search = is_array($status_search) ? $status_search : [$status_search];
                    $q->whereIn('status', $status_search);
            }
            if (!empty($customer_search)) {
                $q->where('customer_id', $customer_search);
            }
            if(empty($cron)){
                $q->where('customer_id', $customer_id);
            }
        });
        $query->orderByRaw("id desc");
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);

        $allPaymentModeIds = $dtData->pluck('payment_mode_id')->unique()->values()->toArray();
        $this->requestPaymentMode = clone $this->request;
        $this->requestPaymentMode->merge(['payment_mode_id' => $allPaymentModeIds]);
        $responsePaymentMode = $this->AdminService->getListPaymentMode($this->requestPaymentMode);
        $dataPaymentMode = $responsePaymentMode->getData(true);
        $dtPaymentMode = collect($dataPaymentMode['data'] ?? []);
    

        $allProductIds = $dtData->map(function ($item) {
            return $item->transaction_item->pluck('product_id')->toArray();
        })->flatten()->unique()->toArray();
        $arrProductId = $allProductIds;
        $arrProductId = !empty($arrProductId) ? $arrProductId : [0];
        $this->requestProduct = clone $this->request;
        $this->requestProduct->merge(['arrProduct' => $arrProductId]);
        $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
        $dataProduct = $responseProduct->getData(true);
        $dtProduct = collect($dataProduct['data']['data'] ?? []);

        $dtData->getCollection()->transform(function ($item) use ($dtPaymentMode, $dtProduct) {
            $payment_mode = $dtPaymentMode->where('id', $item->payment_mode_id)->first();
            $item->payment_mode = $payment_mode;
            $item->transaction_item->transform(function ($tranItem) use ($dtProduct) {
                $product = $dtProduct->where('id', $tranItem->product_id)->first();
                $variant = collect($product['variant_option'] ?? [])->where('id', $tranItem->variant_id)->first();
                $product['variant_option'] = $variant;
                $tranItem->product = $product;
                return $tranItem;
            });
            return $item;
        });
        $collection = TransactionResources::collection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListDataDetail($id = 0)
    {
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        $dtData = Transaction::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }, 'customer_leader' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }, 'customer_f1' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])->find($id);
        $check_detail_transaction = $this->request->input('check_detail_transaction') ?? 0;
        $allPaymentModeIds = $dtData->payment_mode_id;
        $this->requestPaymentMode = clone $this->request;
        $this->requestPaymentMode->merge(['payment_mode_id' => $allPaymentModeIds]);
        $responsePaymentMode = $this->AdminService->getListPaymentMode($this->requestPaymentMode);
        $dataPaymentMode = $responsePaymentMode->getData(true);
        $dtPaymentMode = collect($dataPaymentMode['data'][0] ?? []);
        //Sản phẩm
        $allProductIds = $dtData->transaction_item->pluck('product_id')->toArray();
        $arrProductId = $allProductIds;
        $arrProductId = !empty($arrProductId) ? $arrProductId : [0];
        $this->requestProduct = clone $this->request;
        $this->requestProduct->merge(['arrProduct' => $arrProductId]);
        $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
        $dataProduct = $responseProduct->getData(true);
        $dtProduct = collect($dataProduct['data']['data'] ?? []);
        //end
        if (!empty($dtData)) {
            //kt để khi xóa k tự sinh ra phiếu thanh toán
            if($check_detail_transaction == 0) {
                if ($dataPaymentMode['data'][0]['type'] == 2 && $dtData->warning_payment == 1) {
                    $dtData->info_payment = $this->createPaymentTransaction($dtData->id);
                }
            }

            $dtData->payment_mode = $dtPaymentMode;
            $dtData->transaction_item->map(function ($item) use ($dtProduct) {
                $product = $dtProduct->where('id', $item->product_id)->first();
                $variant = collect($product['variant_option'] ?? [])->where('id', $item->variant_id)->first();
                $product['variant_option'] = $variant;
                $item->product = $product;
                return $item;
            });
        }
        $collection = TransactionResources::make($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy thông tin thành công'
        ]);
    }

    public function addTransaction()
    {
        $admin = $this->request->input('admin') ?? 0;
        if($admin == 1){
            $customer_id = $this->request->input('customer_id') ?? 0;
        } else {
            $customer_id = $this->request->client->id ?? 0;
        }
        $dataPost = $this->request->input();

        if (empty($customer_id)) {
            return response()->json([
                'title' => lang('notification'),
                'data' => [],
                'result' => false,
                'message' => lang('dt_login_use_app')
            ]);
        }
        if (empty($dataPost)) {
            return response()->json([
                'title' => lang('notification'),
                'data' => [],
                'result' => false,
                'message' => lang('dt_not_exist_data_order')
            ]);
        }
        $reference_no = $this->AdminService->getOrderRef('transaction')['reference_no'];
        $app = $dataPost['app'] ?? 0;
        $promotion_id = $dataPost['promotion_id'] ?? 0;
        $cost_delivery = $dataPost['cost_delivery'] ?? 0;
        $discount_cost_delivery = $dataPost['discount_cost_delivery'] ?? 0;
        $payment_mode_id = $dataPost['payment_mode_id'] ?? 0;
        $name_delivery = $dataPost['name_delivery'] ?? null;
        $phone_delivery = $dataPost['phone_delivery'] ?? null;
        $address_delivery = $dataPost['address_delivery'] ?? null;
        $email_delivery = $dataPost['email_delivery'] ?? null;
        $note = $dataPost['note'] ?? null;
        $payment_mode_id = Config::get('constant')['payment_mode_default'] ?? 2;
        $date = date('Y-m-d H:i:s');
        $items = $dataPost['items'] ?? [];
        if (empty($items)) {
            $data['title'] = lang('notification');
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = lang('dt_not_exist_data_item_order');
            return response()->json($data);
        }
        if (empty($reference_no)) {
            $data['title'] = lang('notification');
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = lang('dt_error_create_code_order');
            return response()->json($data);
        }
        if (empty($name_delivery) || empty($phone_delivery) || empty($address_delivery)) {
            $data['title'] = lang('notification');
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = lang('dt_input_address_delivery');
            return response()->json($data);
        }
        if (empty($payment_mode_id)) {
            $data['title'] = lang('notification');
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = lang('dt_empty_payment_mode');
            return response()->json($data);
        }

        $this->requestPaymentMode = clone $this->request;
        $this->requestPaymentMode->merge(['payment_mode_id' => $payment_mode_id]);
        $responsePaymentMode = $this->AdminService->getListPaymentMode($this->requestPaymentMode);
        $dataPaymentMode = $responsePaymentMode->getData(true);
        $dtPaymentMode = collect($dataPaymentMode['data'][0] ?? []);
        if (empty($dtPaymentMode)) {
            $data['title'] = lang('notification');
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = lang('dt_empty_payment_mode');
            return response()->json($data);
        }

        $dtClient = Clients::find($customer_id);
        $id_client_introduce = $dtClient->client_intro_level->id_client_introduce ?? 0;

        $arrProductId = array_column($items, 'product_id');
        $arrProductId = !empty($arrProductId) ? $arrProductId : [0];
        $this->requestProduct = clone $this->request;
        $this->requestProduct->merge(['arrProduct' => $arrProductId]);
        $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
        $dataProduct = $responseProduct->getData(true);
        $dtProduct = collect($dataProduct['data']['data'] ?? []);
        $total = 0;
        $arrItems = [];
        $checkNotiAffiliate = false;
        foreach ($items as $key => $value) {
            $product_id = $value['product_id'] ?? 0;
            $code_introduce = $value['code_introduce'] ?? -1;
            if (empty($product_id)) {
                continue;
            }
            $variant_id = $value['variant_id'] ?? 0;
            $product = $dtProduct->where('id', $product_id)->first();
            
            if(!empty($variant_id)){
                $variant_option = collect($product['variant_option']) ?? [];
                if (empty($variant_option)) {
                    $data['title'] = lang('notification');
                    $data['result'] = false;
                    $data['data'] = [];
                    $data['message'] = lang('dt_not_exist_variant');
                    return response()->json($data);
                }
                $variant = $variant_option->where('id', $variant_id)->first();
                if (empty($variant)) {
                    $data['title'] = lang('notification');
                    $data['result'] = false;
                    $data['data'] = [];
                    $data['message'] = lang('dt_not_exist_variant');
                    return response()->json($data);
                }
            }

            $dtCustomerAffiliate = Clients::where('code_introduce',$code_introduce)->first();
            $percent_affiliate = $this->AdminService->get_option('percent_affiliate') ?? 0;
            $customer_id_affiliate = $dtCustomerAffiliate->id ?? 0;
            $review_id = 0;

            $quantity = $value['quantity'] ?? 0;
            $price = $value['price'] ?? 0;
            if(!empty($variant_id)){
                if ($variant['price'] != $price) {
                    $data['title'] = lang('notification');
                    $data['result'] = false;
                    $data['data'] = [];
                    $data['message'] = lang('dt_not_exist_price');
                    return response()->json($data);
                }
            }
            $amount = $price * $quantity;

            $total_affiliate = 0;
            if (!empty($dtCustomerAffiliate)) {
                $total_affiliate = ($amount * $percent_affiliate) / 100;
                $checkNotiAffiliate = true;
            }

            $arrItems[] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $amount,
                'variant_id' => $variant_id,
                'customer_id_affiliate' => $customer_id_affiliate,
                'percent_affiliate' => $percent_affiliate,
                'total_affiliate' => $total_affiliate,
                'review_id' => $review_id,
                'note' => null
            ];
            $total += $amount;
        }
        if (empty($arrItems)) {
            $data['title'] = lang('notification');
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = lang('dt_insufficient_items');
            return response()->json($data);
        }
        $dtPromotion = Promotion::where('id', $promotion_id)->first();
        $checkFirst = false;
        $machines_id = null;
        if (!empty($dtPromotion) && $dtPromotion['require_first_order']) {
            $checkFirst = true;
            $machines_id = $dtPromotion['machines_id'];
        }
        $checkPromotion = Promotion::where('id', $promotion_id)->where(function ($query) {
            $query->where('date_end', '>=', date('Y-m-d'));
            $query->orWhere('indefinite', 1);
        })
            ->where(function ($query) use ($customer_id, $checkFirst, $machines_id) {
                $query->where('active', 1);
                $query->where(function ($q) use ($customer_id, $checkFirst, $machines_id) {
                    $q->where(function ($qq) use ($customer_id) {
                        $qq->where(function ($qqq) use ($customer_id) {
                            $qqq->where('require_first_order', 1);
                            $qqq->whereRaw("
                                NOT EXISTS (
                                    SELECT 1 FROM tbl_transaction
                                    WHERE tbl_transaction.customer_id = ?
                                    AND tbl_transaction.status IN (" . Config::get('constant')['status_approve'] . "," . Config::get('constant')['status_finish'] . "," . Config::get('constant')['status_request'] . "," . Config::get('constant')['status_payment'] . ")
                                )
                           ", [$customer_id]);
                        });
                        $qq->orWhere('require_first_order', 0);
                    });
                    $q->where(function ($qq) use ($customer_id, $checkFirst, $machines_id) {
                        $qq->where(function ($inst) use ($customer_id, $checkFirst, $machines_id) {
                            if ($checkFirst) {
                                $inst->where('machines_id', $machines_id);
                            } else {
                                $inst->where('type_customer', 1);
                                $inst->whereHas('customer', function ($instance) use ($customer_id) {
                                    $instance->where('customer_id', $customer_id);
                                });
                            }
                        });
                        $qq->orWhere('type_customer', 0);
                    });
                    $q->doesntHave('transaction', 'and', function ($q) use ($customer_id) {
                        $q->where(function ($instance) use ($customer_id) {
                            $instance->where('customer_id', $customer_id);
                            $instance->whereNotIn('status',
                                [
                                    Config::get('constant')['status_cancel'],
                                ]);
                        });
                        $q->where('type_use_one', 1);
                    });
                });
            })
            ->first();
        $dtCheckReferral = DB::table('tbl_client_introduce')->where('id_client', $customer_id)->first();
        if (!empty($checkPromotion) && $checkPromotion->require_first_order == 1) {
            if (empty($dtCheckReferral)) {
                $checkPromotion = false;
            }
        }
        $promotion = 0;
        $percent_promotion = 0;
        if (!empty($checkPromotion)) {
            if ($checkPromotion['require_first_order'] == 1) {
                $checkTransaction = Transaction::where(function ($query) use ($checkPromotion, $machines_id) {
                    $query->where(function ($q) use ($checkPromotion, $machines_id) {
                        $q->where('promotion_id', $checkPromotion->id ?? 0);
                        $q->orWhere('machines_id', $machines_id);
                    });
                    $query->whereIn('status',
                        [
                            Config::get('constant')['status_approve'],
                            Config::get('constant')['status_finish'],
                            Config::get('constant')['status_request'],
                            Config::get('constant')['status_payment'],
                        ]);
                })->first();
                if (!empty($checkTransaction)) {
                    $data['result'] = false;
                    $data['data'] = [];
                    $data['message'] = lang('dt_not_exists_promotion');
                    return response()->json($data);
                }
            }
            if ($checkPromotion->type == 0) {
                $percent_promotion = $checkPromotion->percent;
                $money_max = $checkPromotion->money_max;
                $promotion = $total * $percent_promotion / 100;
                if (!empty($money_max)) {
                    if ($promotion > $money_max) {
                        $promotion = $money_max;
                    } else {
                        $promotion = $promotion;
                    }
                }
            } else {
                $percent_promotion = 0;
                $promotion = $checkPromotion->cash;
            }
            $machines_id = $checkPromotion->machines_id;
        } else {
            if (!empty($promotion_id)) {
                $data['title'] = lang('notification');
                $data['result'] = false;
                $data['data'] = [];
                $data['message'] = lang('dt_not_issue_promotion');
                return response()->json($data);
            }
        }

        $level = get_level_role($customer_id);
        $check_leader = 0;

        //discount
        $check_default_percent_post = $dataPost['check_default_percent'] ?? 0;
        $percent_discount_post = $dataPost['percent_discount'] ?? 0;
        $check_default_percent = 0;
        $percent_discount = 0;
        $percent_leader_ad = $this->AdminService->get_option('percent_leader');
        $percent_f1_ad = $this->AdminService->get_option('percent_f1');
        if($dtClient->is_leader == 1){
            $percent_discount = $percent_leader_ad;
            $check_default_percent = 1;
            $leader_id = $customer_id;
            $percent_leader = $percent_leader_ad;
            $customer_id_f1 = 0;
            $percent_customer_f1 = 0;
            $check_leader = 1;
        } else {
            $check_leader_id = get_parent_customer_leader($dtClient->id);
            $dtCheckF1 = DB::table('tbl_client_introduce')->where('id_client_introduce',$check_leader_id)->where('id_client',$customer_id)->first();
            if (!empty($dtCheckF1) && $dtCheckF1->id_client == $customer_id){
                $percent_discount = $percent_f1_ad;
                $check_default_percent = 1;
                $leader_id = $dtCheckF1->id_client_introduce;
                $customer_id_f1 = $customer_id;
                $percent_leader = $percent_leader_ad;
                $percent_customer_f1 = $percent_f1_ad;
            } else {
                $leader_id = $check_leader_id ?? 0;
                $customer_id_f1 = getBranchChild($leader_id,$customer_id);
                $percent_leader = $percent_leader_ad;
                $percent_customer_f1 = $percent_f1_ad;
                $this->requestDiscount = clone $this->request;
                $responseDiscount = $this->AdminService->getListDiscountOrder($this->requestDiscount);
                $dataDiscount = $responseDiscount->getData(true);
                $dtDiscount = collect($dataDiscount['data'] ?? []);
                $dtDiscount = $dtDiscount->first(function ($item) use ($total) {
                    return $item['total_order_start'] <= $total &&
                        (is_null($item['total_order_end']) || $item['total_order_end'] >= $total);
                });
                $percent_discount = $dtDiscount['discount'] ?? 0;
            }

        }

        $total_discount_leader = 0;
        $total_discount_f1 = 0;
        $total_discount = 0;
        if (!empty($percent_discount)) {
            $total_discount = $total * $percent_discount / 100;
        }

        if (!empty($percent_leader)) {
            $total_discount_leader = $total * $percent_leader / 100;
        }

        if (!empty($percent_customer_f1)) {
            $total_discount_f1 = $total * $percent_customer_f1 / 100;
        }
        if(empty($leader_id)){
            $percent_leader = 0;
            $total_discount_leader = 0;
        }
        if(empty($customer_id_f1)){
            $percent_customer_f1 = 0;
            $total_discount_f1 = 0;
        }
        

        $vat = $this->AdminService->get_option('vat') ?? 0;
        $total_amount_vat = $total - $promotion - $total_discount;
        $total_vat = $total_amount_vat * $vat / 100;

        $grand_total = $total - $promotion + $cost_delivery - $discount_cost_delivery - $total_discount + $total_vat;

        $grand_total = $grand_total < 0 ? 0 : $grand_total;

        $dtLeader = Clients::find($leader_id);
        if(!empty($dtLeader)){
            $accumulate_year = $this->AdminService->get_option('accumulate_year');
            if($dtLeader->type_leader == 1){
                $accumulate_f1 = $this->AdminService->get_option('accumulate_f1');
            } else {
                $accumulate_f1 = $this->AdminService->get_option('accumulate_f1_new');
            }
            $total_accumulate = $grand_total * $accumulate_f1 / $accumulate_year;
        }

        //point
        $pointCustomer = $dtClient->point;
        $point = $dataPost['point'] ?? 0;
        if ($point > $pointCustomer) {
            $data['title'] = lang('notification');
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = lang('dt_point_limit');
            return response()->json($data);
        }
        $exchange_rate_haru_wallet = $this->AdminService->get_option('exchange_rate_haru_wallet');
        $point_total = $point * $exchange_rate_haru_wallet;


        $point_total_new = 0;
        if ($point_total >= $grand_total) {
            $point_total_new = $point_total - $grand_total;
        }
        $point_total = $point_total - $point_total_new;

        $grand_total = ($grand_total - $point_total) < 0 ? 0 : ($grand_total - $point_total);
        $point = $point_total / $exchange_rate_haru_wallet;


        $percent_customer = 0;
        $total_customer = 0;
        $arr_object_id = [];
        $checkNotiReferral = false;
        $_locale = Config::get('constant')['lang_default'];
        if ($id_client_introduce) {
            $checkTransaction = Transaction::where(function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
                $query->whereIn('status',
                    [
                        Config::get('constant')['status_approve'],
                        Config::get('constant')['status_finish'],
                        Config::get('constant')['status_request'],
                        Config::get('constant')['status_payment'],
                    ]);
            })->first();
            if (empty($checkTransaction)) {
                $percent_customer = $this->AdminService->get_option('percent_person_referral');
                $total_customer = ($grand_total * $percent_customer) / 100;
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
                    ->where('tbl_clients.id', $id_client_introduce)
                    ->get()->toArray();
                if (!empty($dtCustomer)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                }
                $arr_object_id = array_values($arr_object_id);
                $checkNotiReferral = true;
                $dtParent = Clients::find($id_client_introduce);
                $_locale = $dtParent->lang_default ?? Config::get('constant')['lang_default'];
            }
            $checkNotiReferral = false;
        }

        DB::beginTransaction();

        try {
            $transaction = new Transaction();
            $transaction->reference_no = $reference_no;
            $transaction->date = date('Y-m-d H:i:s');
            $transaction->customer_id = $customer_id;
            $transaction->status = 0;
            $transaction->created_by = $customer_id;
            $transaction->type_created = 2;
            $transaction->note = $note;
            $transaction->total = $total;
            $transaction->promotion_id = $promotion_id;
            $transaction->percent_promotion = $percent_promotion;
            $transaction->total_promotion = $promotion;
            $transaction->grand_total = $grand_total;
            $transaction->cost_delivery = $cost_delivery;
            $transaction->discount_cost_delivery = $discount_cost_delivery;
            $transaction->name_delivery = $name_delivery;
            $transaction->phone_delivery = $phone_delivery;
            $transaction->address_delivery = $address_delivery;
            $transaction->email_delivery = $email_delivery;
            $transaction->machines_id = $machines_id;
            $transaction->app = $app;
            $transaction->payment_mode_id = $payment_mode_id;
            $transaction->parent_customer_id = !empty($checkNotiReferral) ? $id_client_introduce : 0;
            $transaction->percent_customer = $percent_customer;
            $transaction->total_customer = $total_customer;
            $transaction->point = $point;
            $transaction->exchange_point = $exchange_rate_haru_wallet;
            $transaction->point_total = $point_total;
            $transaction->percent_discount = $percent_discount;
            $transaction->total_discount = $total_discount;
            $transaction->leader_id = $leader_id;
            $transaction->percent_discount_leader = $percent_leader;
            $transaction->total_discount_leader = $total_discount_leader;
            $transaction->customer_id_f1 = $customer_id_f1;
            $transaction->percent_discount_customer_f1 = $percent_customer_f1;
            $transaction->total_discount_customer_f1 = $total_discount_f1;
            $transaction->total_amount_vat = $total_amount_vat;
            $transaction->vat = $vat;
            $transaction->total_vat = $total_vat;
            $transaction->accumulate_year = !empty($leader_id) ? $accumulate_year : 0;
            $transaction->accumulate_f1 = !empty($leader_id) ? $accumulate_f1 : 0;
            $transaction->total_accumulate = !empty($leader_id) ? $total_accumulate : 0;
            $transaction->level = $level;
            $transaction->check_leader = $check_leader;
            $transaction->admin = $admin;
            $transaction->save();
            $this->AdminService->updateOrderRef('transaction');
            if ($transaction) {
                foreach ($arrItems as $key => $item) {
                    $transactionItem = new TransactionItem();
                    $transactionItem->transaction_id = $transaction->id;
                    $transactionItem->product_id = $item['product_id'];
                    $transactionItem->quantity = $item['quantity'];
                    $transactionItem->price = $item['price'];
                    $transactionItem->total = $item['total'];
                    $transactionItem->variant_id = $item['variant_id'];
                    $transactionItem->customer_id_affiliate = $item['customer_id_affiliate'];
                    $transactionItem->percent_affiliate = $item['percent_affiliate'];
                    $transactionItem->total_affiliate = $item['total_affiliate'];
                    $transactionItem->review_id = $item['review_id'];
                    $transactionItem->save();
                }
            }
            $transaction->transaction_item->map(function ($item) use ($dtProduct, $checkNotiAffiliate) {
                $product = $dtProduct->where('id', $item->product_id)->first();
                $variant = collect($product['variant_option'] ?? [])->where('id', $item->variant_id)->first();
                $product['variant_option'] = $variant;
                $item->product = $product;

                //gửi thông báo đến người giới thiệu sản phầm
                $arr_object_id_affiliate = [];
                $lang_default = Config::get('constant')['lang_default'];
                if (!empty($checkNotiAffiliate)) {
                    if (!empty($item->customer_id_affiliate)) {
                        $dtCustomer = Clients::select(
                            'tbl_clients.fullname as name',
                            'tbl_clients.id as object_id',
                            'tbl_clients.lang_default as lang_default',
                            'tbl_player_id.player_id as player_id',
                            DB::raw("'customer' as 'object_type'")
                        )
                            ->leftJoin('tbl_player_id', function ($join) {
                                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                                $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                            })
                            ->where('tbl_clients.id', $item->customer_id_affiliate)
                            ->get()->toArray();
                        if (!empty($dtCustomer)) {
                            $arr_object_id_affiliate = array_merge($arr_object_id_affiliate, $dtCustomer);
                        }
                        $arr_object_id_affiliate = array_values($arr_object_id_affiliate);
                        $lang_default = array_column($arr_object_id_affiliate, 'lang_default')[0] ?? Config::get('constant')['lang_default'];
                    }
                }
                $item->locale = $lang_default;
                $item->arr_object_id = $arr_object_id_affiliate;

                return $item;
            });
            $transaction->payment_mode = $dtPaymentMode;

            //update email vào client nếu chưa chưa
            if (empty($dtClient->email) || empty($dtClient->fullname) || empty($dtClient->address)) {
                if (empty($dtClient->email)) {
                    $dtClient->email = $email_delivery;
                }
                if (empty($dtClient->fullname)) {
                    $dtClient->fullname = $name_delivery;
                }
                if (empty($dtClient->address)) {
                    $dtClient->address = $address_delivery;
                }
                $dtClient->save();
            }
            $dtShipping = DB::table('tbl_client_address')->where('customer_id', $customer_id)->first();
            if (!empty($dtShipping)) {
                DB::table('tbl_client_address')->where('id', $dtShipping->id)
                    ->update([
                        'address' => $address_delivery,
                        'name' => $name_delivery,
                        'phone' => $phone_delivery,
                    ]);
            } else {
                DB::table('tbl_client_address')->insert([
                    'customer_id' => $customer_id,
                    'address' => $address_delivery,
                    'name' => $name_delivery,
                    'phone' => $phone_delivery,
                    'default_address' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // trừ điểm khi sử dụng điểm cho đơn hàng
            if ($point > 0) {
                changePoint($transaction->id, 'transaction_point', $this->request->input('staff_status') ?? 0);
            }


            if (!empty($checkNotiReferral)) {
                $this->requestNoti = $this->request->duplicate(
                    [],
                    $this->request->only(['client'])
                );
                $transaction->data_customer = [
                    'id' => $transaction->customer->id,
                    'fullname' => $transaction->customer->fullname,
                    'phone' => $transaction->customer->phone,
                    'email' => $transaction->customer->email,
                ];
                $transaction->parent_customer_id = $id_client_introduce;
                $transaction->locale = $_locale;
                $transaction->makeHidden(['customer']);
                $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
                $this->requestNoti->merge(['dtData' => $transaction]);
                $this->requestNoti->merge(['customer_id' => $customer_id]);
                $this->requestNoti->merge(['type' => 'customer']);
                $this->requestNoti->merge(['staff_id' => 0]);
                $this->requestNoti->merge(['type_noti' => 'noti_referral_parent_customer']);
                $this->adminNoti->addNoti($this->requestNoti);
            }

            if (!empty($checkNotiAffiliate)) {
                $this->requestNotiAffiliate = $this->request->duplicate(
                    [],
                    $this->request->only(['client'])
                );
                $transaction->data_customer = [
                    'id' => $transaction->customer->id,
                    'fullname' => $transaction->customer->fullname,
                    'phone' => $transaction->customer->phone,
                    'email' => $transaction->customer->email,
                ];
                $transaction->makeHidden(['customer']);
                $this->requestNotiAffiliate->merge(['arr_object_id' => []]);
                $this->requestNotiAffiliate->merge(['dtData' => $transaction]);
                $this->requestNotiAffiliate->merge(['customer_id' => $customer_id]);
                $this->requestNotiAffiliate->merge(['type' => 'customer']);
                $this->requestNotiAffiliate->merge(['staff_id' => 0]);
                $this->requestNotiAffiliate->merge(['type_noti' => 'noti_affiliate_customer']);
                $this->adminNoti->addNoti($this->requestNotiAffiliate);
            }


            if ($dataPaymentMode['data'][0]['type'] == 2) {
                $createPayment = $this->createPaymentTransaction($transaction->id);
                if (empty($createPayment)) {
                    $data['result'] = false;
                    $data['data'] = [];
                    $data['message'] = lang('create_transaction_fail');
                    $data['title'] = lang('notification');
                } else {
                    $data['info_payment'] = $createPayment->info_payment ?? [];
                }
            }
            $this->AdminService->insertCronjobEmail($transaction->id);

            DB::commit();
            $data['result'] = true;
            $data['data'] = TransactionResources::make($transaction);
            $data['message'] = lang('dt_success_add_order');
            $data['title'] = lang('notification');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = $exception->getMessage();
            $data['title'] = lang('notification');
            return response()->json($data);
        }
    }

    private function createPaymentTransaction($id = '')
    {
        $transaction = Transaction::find($id);
        if (!empty($transaction)) {
            $paymentTransaction = TransactionPayment::where('id_transaction', $transaction->id)->first();
            if (empty($paymentTransaction->id)) {
                $reference_no = $this->AdminService->getOrderRef('transaction_payment')['reference_no'];
                $paymentTransaction = new TransactionPayment();
                $paymentTransaction->reference_no = $reference_no;
                $paymentTransaction->id_transaction = $transaction->id;
                $paymentTransaction->date = date('Y-m-d H:i:s');
                $paymentTransaction->amount = $transaction->grand_total;
                $paymentTransaction->payment_mode_id = $transaction->payment_mode_id;
                $paymentTransaction->customer_id = $transaction->customer_id;
                $paymentTransaction->type_created = 2;
                $paymentTransaction->save();
                $this->AdminService->updateOrderRef('transaction_payment');

                $transaction->warning_payment = 1;
                $transaction->save();
            } else {
                $reference_no = $paymentTransaction->reference_no;
            }
            $successPayment = $this->AdminService->createQRPayment([
                'code' => $reference_no,
                'amount' => $paymentTransaction->amount,
            ], $this->_locale);
            if (empty($successPayment['qr'])) {
                return false;
            }
            $paymentTransaction->info_payment = $successPayment ?? [];

            return $paymentTransaction;
        }
        return false;
    }

    public function getListStatusTransaction()
    {
        $customer_id = $this->request->client->id ?? 0;
        $start_date = null;
        $end_date = null;
        $result = array_merge([[
            'id' => -1,
            'name' => lang('all'),
            'color' => '#111827',
            'background' => '',
            'index' => -1,
        ]], getListStatusTransaction());
        $result = array_values($result);
        foreach ($result as $key => $value) {
            $status = $value['id'];
            $query = Transaction::where('id', '!=', 0);
            if (!empty($date_search)) {
                $query->whereBetween('date', [$start_date, $end_date]);
            }
            if ($status != '-1') {
                $query->where('status', $status);
            }
            $query->where('customer_id', $customer_id);
            $result[$key]['count'] = $query->count();
        }
        $data['result'] = true;
        $data['message'] = 'Lấy thông tin thành công';
        $data['data'] = $result;
        return response()->json($data);
    }

    public function countTransaction()
    {
        $service_id = $this->request->input('service_id') ?? 0;
        $type_search = $this->request->input('type_search') ?? 'all';
        $query = Transaction::where('id', '!=', 0);
        if (!empty($service_id)) {
            $query->whereHas('transaction_day_item', function ($q) use ($service_id) {
                $q->where('service_id', $service_id);
            });
        }
        if ($type_search == 'finish') {
            $query->where('status', Config::get('constant')['status_transaction_finish']);
        }
        $total = $query->count();
        $data['result'] = true;
        $data['message'] = 'Thành công';
        $data['data'] = $total;
        return response()->json($data);
    }

    public function changeStatus()
    {
        $transaction_id = $this->request->input('transaction_id');
        $status = $this->request->input('status');
        $noteStatus = $this->request->input('note');
        $type_update = $this->request->input('type_update') ?? 1;

        $transaction = Transaction::with('customer')->find($transaction_id);
        $index = getValueStatusTransaction($transaction->status, 'index');
        $index_current = getValueStatusTransaction($status, 'index');
        $status_current = $transaction->status;
        $arr = [Config::get('constant')['status_cancel']];
        if ($index_current < $index) {
            if (!in_array($status, $arr)) {
                $data['result'] = false;
                $data['message'] = lang('status_loss_status_now');
                return response()->json($data);
            }
        }
        if ($transaction->status == $this->request->status) {
            $data['result'] = false;
            $data['message'] = lang('status_isset_status_now');
            return response()->json($data);
        }

        if ($transaction->status == Config::get('constant')['status_finish']) {
            $data['result'] = false;
            $data['message'] = lang('dt_transaction_finish');
            return response()->json($data);
        }

        if ($status == Config::get('constant')['status_cancel']) {
            if ($transaction->status == Config::get('constant')['status_finish']) {
                $data['result'] = false;
                $data['message'] = lang('dt_transaction_finish_not_cancel');
                return response()->json($data);
            }
        }
        $customer_id = $transaction->customer_id;
        $parent_customer_id = $transaction->parent_customer_id;
        $arr_object_id = [];
        $sendNotiReferral = false;
        $locale_parent = Config::get('constant')['lang_default'];
        if ($status == Config::get('constant')['status_finish']) {
            if (!empty($parent_customer_id)) {
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
                    ->where('tbl_clients.id', $parent_customer_id)
                    ->get()->toArray();
                if (!empty($dtCustomer)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                }
                $sendNotiReferral = true;
                $arr_object_id = array_values($arr_object_id);
                $dtParent = Clients::find($parent_customer_id);
                $locale_parent = $dtParent->lang_default ?? Config::get('constant')['lang_default'];
            }
        }

        //kiểm tra xem có đủ điều kiện gửi hóa đơn
        $checkNotiAffiliate = 0;
        if ($status == Config::get('constant')['status_finish']) {
            $checkNotiAffiliate = $transaction->transaction_item->where('review_id', '!=', 0)->count();
        }

        //Công bổ sung để cập nhất số lượng đã bán cho sản phẩm
        if ($status == Config::get('constant')['status_finish'] && $status != $transaction->status) {
            $this->update_sold($transaction->id);
        } else if ($status != Config::get('constant')['status_finish'] && $transaction->status == Config::get('constant')['status_finish']) {
            $this->update_sold($transaction->id, false);
        }
        //end


        $arr_object_id_new = [];
        $dtCustomerChild = Clients::select(
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
        if (!empty($dtCustomerChild)) {
            $arr_object_id_new = array_merge($arr_object_id_new, $dtCustomerChild);
        }
        $arr_object_id_new = array_values($arr_object_id_new);

        DB::beginTransaction();
        try {
            $transaction->status = $status;
            $transaction->date_status = date('Y-m-d H:i:s');
            $transaction->staff_status = $this->request->input('staff_status');
            $transaction->note_status = $noteStatus ?? null;
            $transaction->type_update = $type_update;
            if ($status == Config::get('constant')['status_payment']){
                //update đơn hàng
                $transaction->warning_payment = 0;
                $transaction->type_staff_status = 1;
                $transaction->save();
            }
            $transaction->save();


            $transaction->data_customer = [
                'id' => $transaction->customer->id,
                'fullname' => $transaction->customer->fullname,
                'phone' => $transaction->customer->phone,
                'email' => $transaction->customer->email,
            ];
            $transaction->locale_parent = $locale_parent;
            $transaction->locale = $transaction->customer->lang_default ?? Config::get('constant')['lang_default'];
            $transaction->makeHidden(['customer']);
            $this->requestNotiStatus = clone $this->request;
            $this->requestNotiStatus->merge(['arr_object_id' => $arr_object_id_new]);
            $this->requestNotiStatus->merge(['dtData' => $transaction]);
            $this->requestNotiStatus->merge(['customer_id' => $customer_id]);
            $this->requestNotiStatus->merge(['type' => 'staff']);
            $this->requestNotiStatus->merge(['staff_id' => $this->request->input('staff_status')]);
            if ($status == Config::get('constant')['status_approve']) {
                $this->requestNotiStatus->merge(['type_noti' => 'noti_approve_transaction']);
            } elseif ($status == Config::get('constant')['status_finish']) {
                $this->requestNotiStatus->merge(['type_noti' => 'noti_finish_transaction']);
                //noti affiliate
                if (!empty($checkNotiAffiliate)) {
                    $transaction->transaction_item->map(function ($item) use ($checkNotiAffiliate) {
                        $arr_object_id_affiliate = [];
                        $lang_default = Config::get('constant')['lang_default'];
                        if (!empty($checkNotiAffiliate)) {
                            if (!empty($item->customer_id_affiliate)) {
                                $dtCustomer = Clients::select(
                                    'tbl_clients.fullname as name',
                                    'tbl_clients.id as object_id',
                                    'tbl_clients.lang_default as lang_default',
                                    'tbl_player_id.player_id as player_id',
                                    DB::raw("'customer' as 'object_type'")
                                )
                                    ->leftJoin('tbl_player_id', function ($join) {
                                        $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                                        $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                                    })
                                    ->where('tbl_clients.id', $item->customer_id_affiliate)
                                    ->get()->toArray();
                                if (!empty($dtCustomer)) {
                                    $arr_object_id_affiliate = array_merge($arr_object_id_affiliate, $dtCustomer);
                                }
                                $arr_object_id_affiliate = array_values($arr_object_id_affiliate);
                                $lang_default = array_column($arr_object_id_affiliate, 'lang_default')[0] ?? Config::get('constant')['lang_default'];
                            }
                        }
                        $item->locale = $lang_default;
                        $item->arr_object_id = $arr_object_id_affiliate;

                        return $item;
                    });
                }

            } elseif ($status == Config::get('constant')['status_cancel']) {
                $this->requestNotiStatus->merge(['type_noti' => 'noti_cancel_transaction']);

                if ($transaction->point > 0 && $transaction->refund_point == 0) {
                    changePoint($transaction->id, 'transaction_point_refund', $this->request->input('staff_status') ?? 0);
                }
            } elseif ($status == Config::get('constant')['status_payment']){
                if (!empty($transaction->payment)){
                    $dtPayment = $transaction->payment;
                    $dtPayment->status = 1;
                    $dtPayment->date_status = date('Y-m-d H:i:s');
                    $dtPayment->staff_status = 7;
                    $dtPayment->amount_payment = $transaction->grand_total;
                    $dtPayment->save();
                } else {
                    $reference_no = $this->AdminService->getOrderRef('transaction_payment')['reference_no'];
                    $dtPayment = new TransactionPayment();
                    $dtPayment->reference_no = $reference_no;
                    $dtPayment->id_transaction = $transaction->id;
                    $dtPayment->date = date('Y-m-d H:i:s');
                    $dtPayment->amount = $transaction->grand_total;
                    $dtPayment->amount_payment = $transaction->grand_total;
                    $dtPayment->payment_mode_id = $transaction->payment_mode_id;
                    $dtPayment->customer_id = $transaction->customer_id;
                    $dtPayment->type_created = 2;
                    $dtPayment->status = 1;
                    $dtPayment->date_status = date('Y-m-d H:i:s');
                    $dtPayment->staff_status = 7;
                    $dtPayment->save();
                    $this->AdminService->updateOrderRef('transaction_payment');
                }

                //gửi thông báo
                $dtPayment->data_customer = [
                    'id' => $dtPayment->customer->id,
                    'fullname' => $dtPayment->customer->fullname,
                    'phone' => $dtPayment->customer->phone,
                    'email' => $dtPayment->customer->email,
                ];
                $dtPayment->makeHidden(['customer']);

                $dtPayment->data_transaction = [
                    'id' => $dtPayment->transaction->id,
                    'reference_no' => $dtPayment->transaction->reference_no,
                ];
                $dtPayment->makeHidden(['transaction']);

                $this->requestNotiPayment = clone $this->request;
                $this->requestNotiPayment->merge(['type_noti' => 'change_status_payment']);
                $this->requestNotiPayment->merge(['arr_object_id' => $arr_object_id_new]);
                $this->requestNotiPayment->merge(['dtData' => $dtPayment]);
                $this->requestNotiPayment->merge(['customer_id' => $customer_id]);
                $this->requestNotiPayment->merge(['type' => 'staff']);
                $this->requestNotiPayment->merge(['staff_id' => $this->request->input('staff_status')]);
                $this->adminNoti->addNoti($this->requestNotiPayment);
            }

            if ($status != Config::get('constant')['status_payment']) {
                $this->adminNoti->addNoti($this->requestNotiStatus);
            }

            if (!empty($sendNotiReferral)) {
                //gửi thông báo
                $this->requestNoti = clone $this->request;
                $this->requestNoti->merge(['type_noti' => 'noti_add_point_parent_customer_referral']);
                $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
                $this->requestNoti->merge(['dtData' => $transaction]);
                $this->requestNoti->merge(['customer_id' => $parent_customer_id]);
                $this->requestNoti->merge(['type' => 'staff']);
                $this->requestNoti->merge(['staff_id' => $this->request->input('staff_status')]);
                $this->adminNoti->addNoti($this->requestNoti);

                changePoint($transaction_id, 'transaction', $this->request->input('staff_status') ?? 0);
            }

            if (!empty($checkNotiAffiliate)) {
                $this->requestNotiAffiliate = $this->request->duplicate(
                    [],
                    $this->request->only(['client'])
                );
                $transaction->data_customer = [
                    'id' => $transaction->customer->id,
                    'fullname' => $transaction->customer->fullname,
                    'phone' => $transaction->customer->phone,
                    'email' => $transaction->customer->email,
                ];
                $transaction->makeHidden(['customer']);
                $this->requestNotiAffiliate->merge(['arr_object_id' => []]);
                $this->requestNotiAffiliate->merge(['dtData' => $transaction]);
                $this->requestNotiAffiliate->merge(['customer_id' => $customer_id]);
                $this->requestNotiAffiliate->merge(['type' => 'customer']);
                $this->requestNotiAffiliate->merge(['staff_id' => 0]);
                $this->requestNotiAffiliate->merge(['type_noti' => 'noti_affiliate_customer_finish']);
                $this->adminNoti->addNoti($this->requestNotiAffiliate);
                $transaction->transaction_item->map(function ($item) use ($checkNotiAffiliate) {
                    if (!empty($checkNotiAffiliate)) {
                        if (!empty($item->customer_id_affiliate)) {
                            changePoint($item->id, 'affiliate_product', $this->request->input('staff_status') ?? 0);
                        }
                    }
                });
            }

            DB::commit();
            $data['result'] = true;
            $data['data'] = $transaction;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeWarehouseStatus()
    {
        $transaction_id = $this->request->input('transaction_id');
        $warehouse_status = intval($this->request->input('warehouse_status') ?? 0);
        $warehouse_approved_at = $this->request->input('warehouse_approved_at') ?? date('Y-m-d H:i:s');
        $warehouse_approved_by = ($this->request->input('warehouse_approved_by') ?? 0);

        $transaction = Transaction::find($transaction_id);
        if (!$transaction) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy đơn hàng']);
        }

        $transaction->warehouse_status = $warehouse_status;
        // $transaction->warehouse_approved_at = $warehouse_approved_at;
        $transaction->warehouse_approved_by = $warehouse_approved_by;
        $transaction->save();

        return response()->json(['result' => true, 'message' => 'Cập nhật trạng thái kho thành công']);
    }

    public function getListDataTransactionBill()
    {
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
        $customer_id = $this->request->client->id ?? 0;
        $search = $this->request->input('search') ?? null;
        $check = !empty($this->request->input('check')) ? $this->request->input('check') : 0; // loc trang thai
        $date_search = $this->request->input('date_search') ?? null;
        $query = TransactionDayItem::with('transaction')
            ->with('transaction_day')
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('transaction', function ($instance) use ($search) {
                    $instance->where('reference_no', 'like', "%$search%");
                });
            });
        }
        if (!empty($date_search)) {
            $query->whereHas('transaction_day', function ($instance) use ($date_search) {
                $instance->whereDate('date', to_sql_date($date_search));
            });
        }
        $query->whereHas('transaction', function ($instance) use ($search) {
            $instance->whereNotIn('status', [
                Config::get('constant')['status_transaction_cancel'],
                Config::get('constant')['status_transaction_finish'],
            ]);
        });
        if (!empty($check)) {
            if ($check == 1) {
                $query->whereNotIn('status', [
                    Config::get('constant')['status_transaction_item_cancel'],
                    Config::get('constant')['status_transaction_item_finish'],
                ]);
            }
        }
        $query->where('partner_id', $customer_id);
        $query->orderByRaw("id desc");
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);

        //gian hàng
        $allServiceIds = $dtData->pluck('service_id')->unique()->values()->toArray();
        $this->requestService = clone $this->request;
        $this->requestService->merge(['service_id' => $allServiceIds]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbServiceService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);
        $dtData->getCollection()->transform(function ($item) use ($services) {
            $service = $services->where('id', $item->service_id)->first();
            $item->service = $service;
            $item->check_list = true;
            return $item;
        });
        //end
        $collection = TransactionDayItemResource::collection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }


    //cập nhật số lượng đã bán cho products
    private function update_sold($id, $plus = true)
    {

        $token = $this->request->bearerToken();
        if (empty($token)) {
            $token = Config::get('constant')['token_default'] ?? 'nglow';
        }
        $transactionDetail = TransactionItem::where('transaction_id', $id)->get();
        $request = new Request();
        $listProductTransaction = [];
        foreach ($transactionDetail as $key => $value) {
            if (empty($listProductTranssaction[$value->product_id])) {
                $listProductTransaction[$value->product_id] = 0;
            }
            if (!empty($plus)) {
                $listProductTransaction[$value->product_id] += $value->quantity;
            } else {
                $listProductTransaction[$value->product_id] -= $value->quantity;
            }
        }
        $request->merge(['product_transaction' => $listProductTransaction]);
        $this->AdminService->PostAdmin($request, $token, 'api/products/changeSold');
        return true;
    }

    public function getTransactionDetailById($id_transaction)
    {
        $id_client = $this->request->client->id ?? 0;
        $transaction = Transaction::where('id', $id_transaction)
            ->where('customer_id', $id_client)->first();
        if (!empty($transaction->id)) {
            $transactionItems = TransactionItem::where('transaction_id', $id_transaction)->get();
            $dataItems = [];
            foreach ($transactionItems as $key => $item) {
                $dataItems[] = [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                ];
            }
            if (!empty($dataItems)) {
                return response()->json([
                    'data' => [
                        'id_transaction' => $transaction->id,
                        'reference_no' => $transaction->reference_no,
                        'date' => $transaction->date,
                        'items' => $dataItems
                    ],
                    'result' => true,
                    'message' => lang('Lấy danh sách thành công')
                ]);
            }
        }

        return response()->json([
            'data' => NULL,
            'result' => false,
            'message' => lang('Không tìm thấy dữ liệu')
        ]);
    }

    public function CheckTransactionReview($id_transaction)
    {
        $id_client = $this->request->client->id ?? 0;
        $un_is_review = $this->request->input('un_is_review') ?? 0; // bỏ check đánh giá
        $is_review = 1;
        if (!empty($un_is_review)) {
            $is_review = 0;
        }
        $transaction = Transaction::where('id', $id_transaction)
            ->where('customer_id', $id_client)->update([
                'is_review' => 1
            ]);
        if ($transaction) {
            return response()->json([
                'result' => true,
                'message' => lang('Check Đánh giá thành công')
            ]);
        } else {
            return response()->json([
                'result' => false,
                'message' => lang('Check Đánh giá không thành công')
            ]);
        }
    }

    public function UnCheckTransactionReview($id_transaction)
    {
        $transaction = Transaction::where('id', $id_transaction)
            ->update([
                'is_review' => 0
            ]);
        if ($transaction) {
            return response()->json([
                'result' => true,
                'message' => lang('Un Check Đánh giá thành công')
            ]);
        } else {
            return response()->json([
                'result' => false,
                'message' => lang('Un Check Đánh giá không thành công')
            ]);
        }
    }

    public function test()
    {
        reviewClassChallenge(105);

        echo "thành công";
    }

    public function getDiscountCustomer()
    {
        $customer_id = $this->request->input('customer_id');
        $level = get_level_role($customer_id);
        $data = Clients::find($customer_id);
        $data->level = $level;
        $check_default_percent = 0;
        $percent_discount = 0;
        if($data->is_leader == 1){
            $percent_discount = $this->AdminService->get_option('percent_leader');
            $check_default_percent = 1;
            $data->type_leader = getListTypeLeader($data->type_leader);
        } else {
            $check_leader = get_parent_customer_leader($customer_id);
            $dtCheckF1 = DB::table('tbl_client_introduce')->where('id_client_introduce',$check_leader)->where('id_client',$customer_id)->first();
            if (!empty($dtCheckF1) && $dtCheckF1->id_client == $customer_id){
                $percent_discount = $this->AdminService->get_option('percent_f1');
                $check_default_percent = 1;
            }
            $data->type_leader = null;
        }
        $data->check_default_percent = $check_default_percent;
        $data->percent_discount = $percent_discount;
        return response()->json([
            'result' => true,
            'message' => 'Lấy thông tin thành công',
            'data' => $data
        ]);
    }

}
