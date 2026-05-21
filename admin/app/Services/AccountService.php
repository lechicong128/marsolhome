<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class AccountService
{
    protected $baseUrl;
    use RequestServiceTrait;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.account.base_url'), '/');
    }

    public function getListCustomer($request)
    {
        $search = $request->input('search.value'); // từ khóa lọc
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $orderColumnIndex = $request->input('order.0.column'); // số thứ tự cột
        $orderColumn = $request->input("columns.$orderColumnIndex.data",'id'); // tên cột
        $orderDir = $request->input('order.0.dir', 'asc');

        $_locale = $request->input('_locale', 'vi');

        $response = Http::get("{$this->baseUrl}/api/customer/getListCustomer",[
            'search' => $search,
            'start' => $start,
            'length' => $length,
            'order_by' => $orderColumn,
            'order_dir' => $orderDir,
            'filter' => $request->input(),
            '_locale' => $_locale
        ]);

        if (!$response->successful()) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
            ]);
        }

        $data = $response->json();
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $data['total'], // tổng số user
            'recordsFiltered' => $data['filtered'], // tổng user sau khi lọc
            'data' => $data['data'], // danh sách user hiện tại
            '_locale' => $_locale
        ]);
    }
    public function getDetailCustomerPlayerid($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getDetailCustomerPlayerid",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'client' => $data['client'] ?? [],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getListDataOrderReferral($request)
    {
        $search = $request->input('search.value'); // từ khóa lọc
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $orderColumnIndex = $request->input('order.0.column'); // số thứ tự cột
        $orderColumn = $request->input("columns.$orderColumnIndex.data",'id'); // tên cột
        $orderDir = $request->input('order.0.dir', 'asc');

        $_locale = $request->input('_locale', 'vi');

        $response = Http::get("{$this->baseUrl}/api/customer/getListDataOrderReferral",[
            'search' => $search,
            'start' => $start,
            'length' => $length,
            'order_by' => $orderColumn,
            'order_dir' => $orderDir,
            'customer_id' => $request->input('customer_id') ?? 0,
            'filter' => $request->input(),
            '_locale' => $_locale
        ]);
        if (!$response->successful()) {
            return response()->json([
                'data' => [],
                'result' => true,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
            ]);
        }

        $data = $response->json();
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $data['total'], // tổng số user
            'recordsFiltered' => $data['filtered'], // tổng user sau khi lọc
            'data' => $data['data'], // danh sách user hiện tại
            'result' => true,
            '_locale' => $_locale
        ]);
    }

    public function getClientsIntroduce($request)
    {
        $search = $request->input('search.value'); // từ khóa lọc
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $orderColumnIndex = $request->input('order.0.column'); // số thứ tự cột
        $orderColumn = $request->input("columns.$orderColumnIndex.data",'id'); // tên cột
        $orderDir = $request->input('order.0.dir', 'asc');

        $_locale = $request->input('_locale', 'vi');

        $response = Http::get("{$this->baseUrl}/api/customer/getClientsIntroduce",[
            'search' => $search,
            'start' => $start,
            'length' => $length,
            'order_by' => $orderColumn,
            'order_dir' => $orderDir,
            'filter' => $request->input(),
            '_locale' => $_locale
        ]);

        if (!$response->successful()) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
            ]);
        }

        $data = $response->json();
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $data['total'], // tổng số user
            'recordsFiltered' => $data['filtered'], // tổng user sau khi lọc
            'data' => $data['data'], // danh sách user hiện tại
            '_locale' => $_locale
        ]);
    }

    public function countAll($request){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/countAll",
                $request
            );
            if (!$response->successful()) {
                return response()->json([
                    'total' => 0,
                    'arrType' => [],
                ]);
            }
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'total' => 0,
                'arrType' => [],
            ]);
        }
    }

    public function getDetailCustomer($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getDetailCustomer",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'client' => $data['client'] ?? [],
                'referral' => $data['referral'] ?? [],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getListDetailCustomer($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getListDetailCustomer",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'clients' => $data['clients'] ?? [],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function detailCustomer($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/detail",
                $request,
                [
                    'has_file' => true
                ]
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCustomer($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/deleteCustomer",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function active($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/active",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function changeStatusLeader($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/changeStatusLeader",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function changeStatusTypeLeader($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/changeStatusTypeLeader",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    public function getListData($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getListData",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                    'data' => []
                ], $response->status());
            }

            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'data' => $data['data'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateTypeClient($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/updateTypeClient",
                $request
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getListInfoShortClient($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/getListInfoShortClient",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data['data'] ?? [],
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getListFeedback($request)
    {
        $search = $request->input('search.value'); // từ khóa lọc
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $orderColumnIndex = $request->input('order.0.column'); // số thứ tự cột
        $orderColumn = $request->input("columns.$orderColumnIndex.data",'id'); // tên cột
        $orderDir = $request->input('order.0.dir', 'asc');

        $_locale = $request->input('_locale', 'vi');

        $response = Http::get("{$this->baseUrl}/api/feedback/getList",[
            'search' => $search,
            'start' => $start,
            'length' => $length,
            'order_by' => $orderColumn,
            'order_dir' => $orderDir,
            'filter' => $request->input(),
            '_locale' => $_locale
        ]);

        if (!$response->successful()) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
            ]);
        }

        $data = $response->json();
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $data['total'], // tổng số user
            'recordsFiltered' => $data['filtered'], // tổng user sau khi lọc
            'data' => $data['data'], // danh sách user hiện tại
            '_locale' => $_locale
        ]);
    }

    public function deleteFeedback($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/feedback/delete",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function countAllFeedback($request){
        $response = Http::get("{$this->baseUrl}/api/feedback/countAll",[
            'filter' => $request->input()
        ]);
        if (!$response->successful()) {
            return response()->json([
                'total' => 0,
                'arrType' => [],
            ]);
        }

        $data = $response->json();

        return response()->json([
            'total' => $data['total'],
            'arrType' => $data['arrType']
        ]);
    }


    public function updateFeedbackImprove($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/feedback/update_feedback_improve",
                $request,
                [
                    'has_file' => true
                ]
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function GetFeedbackImprove($request){
        try {
            if(!empty($_locale)) {
                $request->merge(['_locale' => $_locale]);
            }
            $response = Http::get("{$this->baseUrl}/api/feedback/feedback_improve",$request);
            $data = $response->json();
            return response()->json($data);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getListCustonerIdReferral($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getListCustonerIdReferral",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'data' => $data['data'] ?? [],
                'message' => $data['message'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getTransactionDetailById($request, $id_transaction){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/transaction/getTransactionDetailById/{$id_transaction}",
                $request,
                [
                    'token' => $request->bearerToken('token')
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'data' => $data['data'] ?? [],
                'message' => $data['message'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function CheckTransactionReview($request, $id_transaction){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/transaction/CheckTransactionReview/{$id_transaction}",
                $request,
                [
                    'token' => $request->bearerToken('token')
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'data' => $data['data'] ?? [],
                'message' => $data['message'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function UnCheckTransactionReview($request, $id_transaction){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/transaction/UnCheckTransactionReview/{$id_transaction}",
                $request,
                [
                'token' => $request->bearerToken('token') ?? 'nglow'
                ]
            );

            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'data' => $data['data'] ?? [],
                'message' => $data['message'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function getList($url = '', $request) {
        $search = $request->input('search.value'); // từ khóa lọc
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $orderColumnIndex = $request->input('order.0.column'); // số thứ tự cột
        $orderColumn = $request->input("columns.$orderColumnIndex.data",'id'); // tên cột
        $orderDir = $request->input('order.0.dir', 'asc');

        $_locale = $request->input('_locale', 'vi');

        $response = Http::get("{$this->baseUrl}/{$url}",[
            'search' => $search,
            'start' => $start,
            'length' => $length,
            'order_by' => $orderColumn,
            'order_dir' => $orderDir,
            'filter' => $request->input(),
            '_locale' => $_locale
        ]);
        if (!$response->successful()) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'result' => false,
            ]);
        }

        $data = $response->json();
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $data['total'], // tổng số user
            'recordsFiltered' => $data['filtered'], // tổng user sau khi lọc
            'data' => $data['data'], // danh sách user hiện tại
            '_locale' => $_locale,
            'result' => true,
        ]);
    }

    public function detail($url = '', $request){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/{$url}",
                $request,
                ['token' => $request->client->token ?? 'nglow']
            );
            $data = $response->json();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function submit($url = '', $request) {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/{$url}",
                $request,
                [
                    'has_file' => true
                ]
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'] ?? false,
                'message' => $data['message'] ?? ''
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($url = '', $request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/{$url}",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function changeStatus($url = '', $request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/{$url}",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function updatePointReview($request)
    {
        $response = $this->sendRequestToService(
            'POST',
            "{$this->baseUrl}/api/point/updatePointReview",
            $request
        );
        $response = $response->json();
        return $response;

        // try {
        //     $response = $this->sendRequestToService(
        //         'POST',
        //         "{$this->baseUrl}/api/point/updatePointReview",
        //         $request
        //     );
        //     $data = $response->json();
        //     return response()->json([
        //         'data' => $data,
        //         'result' => $data['result'],
        //         'message' => $data['message']
        //     ]);
        // }
        // catch (\Exception $e) {
        //     return response()->json([
        //         'result' => false,
        //         'message' => $e->getMessage(),
        //     ], 500);
        // }
    }
    public function getDetailPointReivew($request){
        try {
            $response = $this->sendRequestToService(
                'post',
                "{$this->baseUrl}/api/point/getDetailPointReview",
                $request,
            );
            $data = $response->json();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getDeleteDetailPointReivew($request){
        try {
            $response = $this->sendRequestToService(
                'post',
                "{$this->baseUrl}/api/point/getDeleteDetailPointReivew",
                $request,
            );
            $data = $response->json();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getClientsOrderLeader($request)
    {
        $search = $request->input('search.value'); // từ khóa lọc
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $orderColumnIndex = $request->input('order.0.column'); // số thứ tự cột
        $orderColumn = $request->input("columns.$orderColumnIndex.data",'id'); // tên cột
        $orderDir = $request->input('order.0.dir', 'asc');

        $_locale = $request->input('_locale', 'vi');

        $response = Http::get("{$this->baseUrl}/api/customer/getClientsOrderLeader",[
            'search' => $search,
            'start' => $start,
            'length' => $length,
            'order_by' => $orderColumn,
            'order_dir' => $orderDir,
            'customer_id' => $request->input('customer_id') ?? 0,
            'year_search' => $request->input('year_search') ?? date('Y'),
            'filter' => $request->input(),
            '_locale' => $_locale
        ]);
        if (!$response->successful()) {
            return response()->json([
                'data' => [],
                'result' => true,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
            ]);
        }

        $data = $response->json();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $data['total'], // tổng số user
            'recordsFiltered' => $data['filtered'], // tổng user sau khi lọc
            'data' => $data['data'], // danh sách user hiện tại
            'result' => true,
            '_locale' => $_locale
        ]);
    }

    public function detailInformationVat($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/detailInformationVat",
                $request,
                [
                    'has_file' => true
                ]
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getListCodeLeader($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/code_leader/getList",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ($response->json()['message'] ?? 'Unknown error'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ], $response->status());
            }

            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $data['total'],
                'recordsFiltered' => $data['filtered'],
                'data' => $data['data'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function countAllCodeLeader($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/code_leader/countAll",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'total' => 0,
                    'filtered' => 0,
                    'data' => [],
                    'result' => false,
                ]);
            }
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function checkDeleteCodeLeader($field = '')
    {
        $response = Http::get("{$this->baseUrl}/api/checkDeleteCodeLeader/{$field}");
        $response = $response->json();
        return !empty($response['result']) ? $response['result'] : null;
    }
}
