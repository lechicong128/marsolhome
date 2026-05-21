<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\TransactionResources;
use App\Models\Clients;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Invoice as InvoiceModel;
use App\Models\InvoiceItem;
use App\Services\AdminService;
use App\Services\NotiService;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use App\Traits\InvoiceTrait;
use App\Libraries\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\TransactionPayment;

class InvoiceController extends AuthController
{
    use UploadFile;
    use InvoiceTrait; 
    protected $AdminService;
    protected $adminNoti;
    protected $_locale;
    protected $invoice;
    public function __construct(Request $request, AdminService $adminService, NotiService $notiService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->AdminService = $adminService;
        $this->adminNoti = $notiService;
        $this->_locale = $request->_locale;
        $this->invoice = new Invoice();
    }

    public function getListWaitingInvoice()
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
        $transaction_search = $this->request->input('transaction_search') ?? null;
        $date_search = $this->request->input('date_search') ?? null;
        $status_search = $this->request->input('status_search') ?? -1;
        $name_search = $this->request->input('name_search') ?? null;

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
                $q->where('reference_no', 'like', '%' . $search . '%');
                $q->orWhere('date', 'like', '%' . $search . '%');
                $q->orWhereHas('customer', function ($instance) use ($search) {
                    $instance->where('fullname', 'like', '%' . $search . '%');
                    $instance->orWhere('phone', 'like', '%' . $search . '%');
                });
            });
        }
        if (!empty($name_search)) {
            $query->where(function ($q) use ($name_search) {
                $q->where('reference_no', 'like', '%' . $name_search . '%');
                $q->orWhere('date', 'like', '%' . $name_search . '%');
                $q->orWhereHas('customer', function ($instance) use ($name_search) {
                    $instance->where('fullname', 'like', '%' . $name_search . '%');
                });
            });
        }
        if (!empty($transaction_search)) {
            $query->where('id', $transaction_search);
        }
        $query->whereNotExists(function ($q) {
            $q->select(DB::raw(1))
              ->from('tbl_invoice_item')
              ->whereColumn('tbl_invoice_item.transaction_id', 'tbl_transaction.id');
        });
        $query->whereIn('status', [
            Config::get('constant')['status_finish'],
            Config::get('constant')['status_approve'],
        ]);
        if (!empty($customer_search)) {
            $query->where('customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        $filtered = (clone $query)->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();


        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtCustomer = $value->customer ?? null;
                if (!empty($dtCustomer)) {
                    $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL') . '/' . $dtCustomer->avatar : null;
                    $data[$key]['customer']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]->customer = null;
                }
            }
        }
        $total = $filtered;

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function submitDetailInvoice()
    {
        $vatRules = [
            'customer_id' => 'required',
        ];
        $vatMessages = [
            'customer_id.required' => 'Vui lòng chọn khách hàng',
        ];
        $validator = Validator::make($this->request->all(), $vatRules, $vatMessages);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all()[0];
            return response()->json($data);
        }
        $transaction_id = $this->request->input('transaction_id');
        $transaction_id = is_array($transaction_id) ? $transaction_id : [$transaction_id];
        $query = Transaction::whereIn('id', $transaction_id);
        $data = $query->get();
        if (empty($data)) {
            $data['result'] = false;
            $data['message'] = 'Không tìm thấy đơn hàng';
            return response()->json($data);
        }
        $customer_id = $this->request->input('customer_id') ?? 0;
        $dtCustomer = Clients::find($customer_id);
        if (empty($dtCustomer)) {
            $data['result'] = false;
            $data['message'] = 'Không tìm thấy khách hàng';
            return response()->json($data);
        }
        if(empty($dtCustomer->client_information_vat)) {
            $data['result'] = false;
            $data['message'] = 'Khách hàng không có thông tin VAT';
            return response()->json($data);
        }
        $transaction_item_id = $this->request->input('transaction_item_id');
        $transaction_item_id = is_array($transaction_item_id) ? $transaction_item_id : [$transaction_item_id];
        $query = TransactionItem::whereIn('id', $transaction_item_id);
        $data = $query->get();
        if (empty($data)) {
            $data['result'] = false;
            $data['message'] = 'Không tìm thấy sản phẩm';
            return response()->json($data);
        }
        $arrItem = [];
        $tax_rate = 0;
        $total_net = 0;
        $total_tax = 0;
        $total_discount = 0;
        $grand_total = 0;
        $total = 0;
        foreach ($transaction_item_id as $key => $value) {
            $transaction_id = $this->request->input('transaction_id')[$key] ?? 0;
            $product_id = $this->request->input('product_id')[$key] ?? 0;
            $variant_id = $this->request->input('variant_id')[$key] ?? 0;
            $quantity = $this->request->input('quantity')[$key] ?? 0;
            $price = $this->request->input('price')[$key] ?? 0;
            $total_item = $this->request->input('total')[$key] ?? 0;
            $discount_item = $this->request->input('discount_item')[$key] ?? 0;
            $vat = $this->request->input('vat')[$key] ?? 0;
            $total_amount_item = $this->request->input('total_amount_item')[$key] ?? 0;
            $total_amount_vat_item = $this->request->input('total_amount_vat_item')[$key] ?? 0;
            $total_amount = $this->request->input('total_amount')[$key] ?? 0;
            $code_item = $this->request->input('code_item')[$key] ?? '';
            $name_item = $this->request->input('name_item')[$key] ?? '';
            $unit_id = $this->request->input('unit_id')[$key] ?? 0;
            $unit_name = $this->request->input('unit_name')[$key] ?? '';
            if(empty($unit_id)) {
                $data['result'] = false;
                $data['message'] =  'Sản phẩm '.$name_item.' không có đơn vị';
                return response()->json($data);
            }
            $arrItem[] = [
                'transaction_item_id' => $value,
                'transaction_id' => $transaction_id,
                'product_id' => $product_id,
                'variant_id' => $variant_id,
                'quantity' => $quantity,
                'price' => $price,
                'total_item' => $total_item,
                'total_discount_item' => $discount_item,
                'vat' => $vat,
                'total_net_item' => $total_amount_item,
                'total_tax_item' => $total_amount_vat_item,
                'grand_total_item' => $total_amount,
                'code_item' => $code_item,
                'name_item' => $name_item,
                'unit_id' => $unit_id,
                'unit_name' => $unit_name,
            ];
            $tax_rate = $vat;
            $total_net += $total_amount_item;
            $total_tax += $total_amount_vat_item;
            $total_discount += $discount_item;
            $grand_total += $total_amount;
            $total += $total_item;
        }
        if(empty($arrItem)) {
            $data['result'] = false;
            $data['message'] = 'Không có sản phẩm';
            return response()->json($data);
        }
        DB::beginTransaction();
        $reference_no = $this->AdminService->getOrderRef('invoice');
        try {
            $dtInvoice = new InvoiceModel();
            $dtInvoice->reference_no = $reference_no['reference_no'];
            $dtInvoice->date = to_sql_date($this->request->input('date_invoice')) ?? date('Y-m-d');
            $dtInvoice->customer_id = $customer_id;
            $dtInvoice->tax_rate = $tax_rate;
            $dtInvoice->total = $total;
            $dtInvoice->total_net = $total_net;
            $dtInvoice->total_tax = $total_tax;
            $dtInvoice->total_discount = $total_discount;
            $dtInvoice->grand_total = $grand_total;
            $dtInvoice->created_by = $this->user->id ?? 7;
            $dtInvoice->save();
            $invoice_id = $dtInvoice->id;
            if($invoice_id){
                foreach ($arrItem as $key => $value) {
                    $dtInvoiceItem = new InvoiceItem();
                    $dtInvoiceItem->invoice_id = $invoice_id;
                    $dtInvoiceItem->transaction_id = $value['transaction_id'];
                    $dtInvoiceItem->transaction_item_id = $value['transaction_item_id'];
                    $dtInvoiceItem->variant_id = $value['variant_id'];
                    $dtInvoiceItem->product_id = $value['product_id'];
                    $dtInvoiceItem->quantity = $value['quantity'];
                    $dtInvoiceItem->price = $value['price'];
                    $dtInvoiceItem->total_item = $value['total_item'];
                    $dtInvoiceItem->total_discount_item = $value['total_discount_item'];
                    $dtInvoiceItem->vat = $value['vat'];
                    $dtInvoiceItem->total_net_item = $value['total_net_item'];
                    $dtInvoiceItem->total_tax_item = $value['total_tax_item'];
                    $dtInvoiceItem->grand_total_item = $value['grand_total_item'];
                    $dtInvoiceItem->code_item = $value['code_item'];
                    $dtInvoiceItem->name_item = $value['name_item'];
                    $dtInvoiceItem->unit_id = $value['unit_id'];
                    $dtInvoiceItem->unit_name = $value['unit_name'];
                    $dtInvoiceItem->save();
                }
            }
            $this->AdminService->updateOrderRef('invoice');
            $dataPostInvoice['invoice_id'] = $invoice_id;
            $resultImport = $this->createInvoice($dataPostInvoice);
            if(empty($resultImport['result'])) {
                DB::rollBack();
                $data['result'] = false;
                $data['message'] = $resultImport['message'] ?? 'Thêm hóa đơn thất bại';
                return response()->json($data);
            }
            DB::commit();
            $data['result'] = true;
            $data['message'] = 'Thêm hóa đơn thành công';
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getTransactionItem()
    {
        $transaction_id = $this->request->input('transaction_id');
        $transaction_id = is_array($transaction_id) ? $transaction_id : [$transaction_id];
        $query = TransactionItem::whereIn('transaction_id', $transaction_id);
        $data = $query->get();
        //Sản phẩm
        $allProductIds = $data->pluck('product_id')->toArray();
        $arrProductId = $allProductIds;
        $arrProductId = !empty($arrProductId) ? $arrProductId : [0];
        $this->requestProduct = clone $this->request;
        $this->requestProduct->merge(['arrProduct' => $arrProductId]);
        $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
        $dataProduct = $responseProduct->getData(true);
        $dtProduct = collect($dataProduct['data']['data'] ?? []);
        $data = $data->transform(function ($item) use ($dtProduct) {
            $vat = $item->transaction->vat;
            $percent_discount = $item->transaction->percent_discount;
            $discount_item = $item->total * $percent_discount / 100;
            $total_amount_item = $item->total - $discount_item;
            $total_amount_vat_item = $total_amount_item * $vat / 100;
            $item->discount_item = $discount_item;
            $item->total_amount_item = $total_amount_item;
            $item->total_amount_vat_item = $total_amount_vat_item;
            $item->vat = $vat;
            $product = $dtProduct->where('id', $item->product_id)->first();
            $item->product = $product;
            $item->data_transaction = [
                'id' => $item->transaction->id,
                'reference_no' => $item->transaction->reference_no,
                'grand_total' => $item->transaction->grand_total,
                'vat' => $item->transaction->vat,
                'total_vat' => $item->transaction->total_vat,
                'total_amount_vat' => $item->transaction->total_amount_vat,
                'total_discount' => $item->transaction->total_discount,
            ];
            $item->makeHidden(['transaction']);
            return $item;
        });
        return response()->json(['result' => true, 'data' => $data, 'message' => 'Lấy danh sách sản phẩm thành công']);
    }

    public function getListInvoice()
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
        $transaction_search = $this->request->input('transaction_search') ?? null;
        $date_search = $this->request->input('date_search') ?? null;
        $status_search = $this->request->input('status_search') ?? -1;
        $name_search = $this->request->input('name_search') ?? null;

        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0], true);
            $end_date = to_sql_date($date_search[1], true);
        } else {
            $start_date = null;
            $end_date = null;
        }


        $query = InvoiceModel::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', '%' . $search . '%');
                $q->orWhere('date', 'like', '%' . $search . '%');
                $q->orWhereHas('customer', function ($instance) use ($search) {
                    $instance->where('fullname', 'like', '%' . $search . '%');
                    $instance->orWhere('phone', 'like', '%' . $search . '%');
                });
            });
        }
        if(!empty($transaction_search)){
            $query->whereHas('invoice_item', function ($instance) use ($transaction_search) {
                $instance->where('transaction_id', $transaction_search);
            });
        }
        if (!empty($invoice_search)) {
            $query->where('id', $invoice_search);
        }
        if (!empty($customer_search)) {
            $query->where('customer_id', $customer_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        $filtered = (clone $query)->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        $allTransactionId = $data->map(function ($item) {
            return $item->invoice_item->pluck('transaction_id')->toArray();
        })->flatten()->unique()->toArray();

        $allTransaction = [];
        if (!empty($allTransactionId)) {
            $allTransaction = DB::table('tbl_invoice_item')
            ->select('tbl_transaction.id','reference_no','tbl_invoice_item.invoice_id')
            ->join('tbl_transaction', 'tbl_transaction.id', '=', 'tbl_invoice_item.transaction_id')
            ->whereIn('transaction_id', $allTransactionId)
            ->groupBy('tbl_transaction.id','tbl_invoice_item.invoice_id','reference_no')
            ->get()->toArray();
            $allTransaction = array_reduce($allTransaction, function ($carry, $item) {
                $carry[$item->invoice_id][] = $item;
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
                $data[$key]['transaction'] = $allTransaction[$value->id] ?? [];
            }
        }
        $total = $filtered;

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function viewInvoice()
    {
        $invoice_id = $this->request->input('invoice_id');
        $invoice = InvoiceModel::find($invoice_id);
        if (empty($invoice)) {
            return response()->json(['result' => false, 'base64pdf' => '', 'message' => 'Không tìm thấy hóa đơn']);
        }
        $dataInvoice = [
            'magiaodich' => $invoice->magiaodich,
            'ma_hoadon' => $invoice->reference_no,
        ];
        $base64pdf = $this->invoice->TaiHoaDonPDF($dataInvoice);
        return response()->json(['result' => true, 'base64pdf' => $base64pdf->result->base64pdf ?? null, 'message' => 'Xem hóa đơn thành công']);
    }

    public function deleteInvoice()
    {
        $invoice_id = $this->request->input('id');
        $invoice = InvoiceModel::find($invoice_id);
        if (empty($invoice)) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy hóa đơn']);
        }
        $dataInvoice = [
            'hoadon_goc' => $invoice->reference_no,
            'ma_hoadon' => $invoice->reference_no,
            'ngaylap' => date('Y-m-d H:i:s', strtotime($invoice->created_at))
        ];
        $result = $this->invoice->GuiHoadonHuyBo($dataInvoice);
        $success = false;
        if($result->result->maketqua == "01") {
            $success = true;
        } else {
            return response()->json(['result' => false, 'message' => $result->result->thongbao ?? 'Xóa hóa đơn thất bại']);
        }
        if(!$success) {
            return response()->json(['result' => false, 'message' => 'Xóa hóa đơn thất bại']);
        }
        DB::beginTransaction();
        try {
            $invoice->delete();
            $invoice->invoice_item()->delete();
            DB::commit();
            return response()->json(['result' => true, 'message' => 'Xóa hóa đơn thành công']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $exception->getMessage()]);
        }
    
    }

    public function detailInvoice()
    {
        $invoice_id = $this->request->input('id');
        $query = InvoiceModel::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])
            ->where('id', '!=', 0);
        $data = $query->where('id', $invoice_id)->first();
        if (empty($data)) {
            return response()->json(['result' => false, 'data' => [], 'message' => 'Không tìm thấy hóa đơn']);
        }
        $allTransactionId = $data->invoice_item->map(function ($item) {
            return $item->transaction_id;
        })->flatten()->unique()->toArray();

        $allProductId = $data->invoice_item->map(function ($item) {
            return $item->product_id;
        })->flatten()->unique()->toArray();
        $arrProductId = !empty($allProductId) ? $allProductId : [0];
        $this->requestProduct = clone $this->request;
        $this->requestProduct->merge(['arrProduct' => $arrProductId]);
        $responseProduct = $this->AdminService->getListDataProduct($this->requestProduct);
        $dataProduct = $responseProduct->getData(true);
        $dtProduct = collect($dataProduct['data']['data'] ?? []);
    
        $allTransaction = [];
        if (!empty($allTransactionId)) {
            $allTransaction = DB::table('tbl_invoice_item')
            ->select('tbl_transaction.id','reference_no','tbl_invoice_item.invoice_id')
            ->join('tbl_transaction', 'tbl_transaction.id', '=', 'tbl_invoice_item.transaction_id')
            ->whereIn('transaction_id', $allTransactionId)
            ->groupBy('tbl_transaction.id','tbl_invoice_item.invoice_id','reference_no')
            ->get()->toArray();
            $allTransaction = array_reduce($allTransaction, function ($carry, $item) {
                $carry[$item->invoice_id][] = $item;
                return $carry;
            }, []);
        }
        $data->transaction = $allTransaction[$data->id] ?? [];
        $data->customer = $data->customer ?? null;
        if (!empty($data->customer)) {
            $data->customer->avatar_new = !empty($data->customer->avatar) ? env('STORAGE_URL') . '/' . $data->customer->avatar : null;
            $client_information_vat = $data->customer->client_information_vat ?? null;
            $client_information_vat_new = null;
            if (!empty($client_information_vat)) {
                if($client_information_vat->type == 1) {
                   $client_information_vat_new = [
                        'type' => 1,
                        'name' => $client_information_vat->company,
                        'vat' => $client_information_vat->vat,
                        'address' => $client_information_vat->address,
                   ];
                } else {
                    $client_information_vat_new = [
                        'type' => 2,
                        'name' => $client_information_vat->name,
                        'vat' => $client_information_vat->vat,
                        'address' => $client_information_vat->address,
                    ];
                }
            }
            $data->customer->client_information_vat_new = $client_information_vat_new ?? null;
            $data->customer->makeHidden(['client_information_vat']);
        } else {
            $data->customer = null;
        }
        $data->invoice_item = $data->invoice_item->transform(function ($item) use ($dtProduct) {
            $product = $dtProduct->where('id', $item->product_id)->first();
            $variant = collect($product['variant_option'] ?? [])->where('id', $item->variant_id)->first();
            $product['variant_option'] = $variant;
            $item->product = $product;
            $transaction = $item->transaction_item->transaction ?? null;
            if (!empty($transaction)) {
                $transaction = [
                    'id' => $transaction->id,
                    'reference_no' => $transaction->reference_no,
                ];
                $item->transaction = $transaction;
            }
            $item->makeHidden(['transaction_item']);
            return $item;
        });
        return response()->json(['result' => true, 'data' => $data, 'message' => 'Lấy thông tin hóa đơn thành công']);
    }
}