<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\CategoryCard;
use App\Models\Clients;
use App\Models\Driver;
use App\Models\RequestWithdrawMoney;
use App\Models\Transaction;
use App\Models\TransactionDriver;
use App\Models\TransferMoney;
use App\Services\AccountService;
use App\Services\TransactionService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    protected $accountService;
    protected $transactionService;
    protected $invoiceService;
    public function __construct(Request $request,AccountService $accountService,TransactionService $transactionService,InvoiceService $invoiceService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->accountService = $accountService;
        $this->transactionService = $transactionService;
        $this->invoiceService = $invoiceService;
    }

    public function searchCustomer()
    {
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $type_client = $params['type_client'] ?? null;
        $this->request->merge(['search' => $search]);
        $this->request->merge(['type_client' => $type_client]);
        $this->request->merge(['limit' => 50]);
        $response = $this->accountService->getListData($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['fullname'] . ' (' . $value['phone'] . ')',
                'phone' => $value['phone'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchDriver(){
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $dtCustomer = Driver::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->where('fullname', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtCustomer as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->fullname
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchTransferMoney(){
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $dtTransferMoney = TransferMoney::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->where('reference_no', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtTransferMoney as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->reference_no
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchRequestWithdrawMoney(){
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $dtRequestWithdrawMoney = RequestWithdrawMoney::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->where('reference_no', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtRequestWithdrawMoney as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->reference_no
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchTransactionAll(){
        $search = $this->request->input('term');

        $tb_transaction_river = DB::table('tbl_transaction_driver')
            ->select('tbl_transaction_driver.id as id',
                'reference_no',DB::raw('1 as type'))
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('reference_no', 'like', '%' . $search . '%');
                }
            })
            ->limit(30);

        $dtTransaction = Transaction::select(
                'tbl_transaction.id as id',
                'reference_no',
                DB::raw('2 as type')
            )
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('reference_no', 'like', '%' . $search . '%');
                }
            })
            ->limit(30)->unionAll($tb_transaction_river);
        $result = DB::query()
            ->fromSub($dtTransaction, 'tb_transaction')
             ->where(function ($query) use ($search) {
                 if (!empty($search)) {
                     $query->where('reference_no', 'like', '%' . $search . '%');
                 }
             })
            ->limit(60)->get();
        $results = [];
        $arrItems = [];
        $arrItemsDriver = [];
        foreach ($result as $key => $value) {
            if ($value->type == 1){
                $arrItemsDriver[] = [
                    'id' => $value->id,
                    'text' => $value->reference_no
                ];
            } else {
                $arrItems[] = [
                    'id' => $value->id,
                    'text' => $value->reference_no
                ];
            }
        }
        $results[] = [
            'text' => 'Giao dịch đặt tài xế',
            'children' => $arrItemsDriver,
        ];
        $results[] = [
            'text' => 'Giao dịch đặt xe',
            'children' => $arrItems
        ];
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchBlog(){
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $type = !empty($params['type']) ? $params['type'] : 2;
        $dtData = Blog::where(function ($query) use ($search,$type) {
            if (!empty($type) && $type != -1) {
                $query->where('type', $type);
            }
            $query->where('active', 1);
            if (!empty($search)) {
                $query->where('title', 'like', '%' . $search . '%');
                $query->orWhere('detail', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->title,
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchCategoryCard(){
        $search = $this->request->input('term');
        $dtData = CategoryCard::where(function ($query) use ($search) {
            $query->where('active', 1);
            if (!empty($search)) {
                $query->where('code', 'like', '%' . $search . '%');
                $query->orWhere('name', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->name.' ('.$value->code.')',
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchTransaction()
    {
        $search = $this->request->input('term');
        $paramsCus = $this->request->input('paramsCus') ?? [];
        $customer_id = $paramsCus['customer_id'] ?? 0;
        $this->request->merge(['name_search' => $search]);
        $this->request->merge(['customer_search' => $customer_id]);
        $this->request->merge(['limit' => 50]);
        $this->request->merge(['status_search' => -1]);
        $this->request->merge(['cron' => 1]);
        $response = $this->transactionService->getListDataTransaction($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['reference_no'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchTransactionInvoice()
    {
        $search = $this->request->input('term');
        $paramsCus = $this->request->input('paramsCus') ?? [];
        $customer_id = $paramsCus['customer_id'] ?? 0;
        $this->request->merge(['name_search' => $search]);
        $this->request->merge(['customer_search' => $customer_id]);
        $this->request->merge(['limit' => 50]);
        $response = $this->invoiceService->getListWaitingInvoice($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['reference_no'],
                'grand_total' => $value['grand_total'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchTransactionItem($id = 0)
    {
        $transaction_id = $this->request->input('transaction_id') ?? [];
        $transaction_id = is_array($transaction_id) ? $transaction_id : [$transaction_id];
        $this->request->merge(['transaction_ids' => $transaction_id]);
        $response = $this->invoiceService->getTransactionItem($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        return response()->json([
            'result' => true,
            'data' => $dtData,
            'message' => $data['message']
        ], 200);
    }
}
