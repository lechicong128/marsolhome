<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

//use Illuminate\Support\Facades\Request;

class AdminService
{
    use RequestServiceTrait;
    protected $baseUrl;
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.admin.base_url'), '/');
    }

    public function getOrderRef($ref = '')
    {
        $response = Http::get("{$this->baseUrl}/api/getOrderRef/{$ref}");
        $response = $response->json();
        return $response;
    }

    public function updateOrderRef($ref = '')
    {
        $response = Http::get("{$this->baseUrl}/api/updateOrderRef/{$ref}");
        $response = $response->json();
        return $response;
    }

    public function SignUpReviewProduct($listReview = [], $id_client = 0, $token = '', $_locale = 'vi', $data_append = [])
    {
        $request = new Request();
        $request->merge(['list_review' => $listReview]);
        $request->merge(['id_client' => $id_client]);
        $request->merge(['_locale' => $_locale]);
        if(!empty($data_append)) {
            foreach($data_append as $key => $value) {
                $request->merge([$key => $value]);
            }
        }
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/SignUpReviewProduct",
                $request,
                ['token' => $token],
            );
            $response = $response->json();
            return $response;
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

//        $response = Http::post("{$this->baseUrl}/api/SignUpReviewProduct", [
//            'list_review' => $listReview,
//            'id_client' => $id_client,
//            'token' => $token,
//        ]);
//        $response = $response->json();
//        return $response;
    }

    public function ktReviewProduct($listReview = [], $id_client = 0, $_locale = 'vi')
    {
        $response = Http::post("{$this->baseUrl}/api/ktReviewProduct", [
            'list_review' => $listReview,
            'id_client' => $id_client,
            '_locale' => $_locale,
        ]);
        $response = $response->json();
        return $response;
    }

    public function GetSetings()
    {
        $response = Http::get("{$this->baseUrl}/api/get_setings_account");
        $response = $response->json();
        return $response;
    }
    public function GetDetailReview($id, $_locale = 'vi')
    {
        $response = Http::get("{$this->baseUrl}/api/GetDetailReview/{$id}",[
            'id' => $id,
            '_locale' => $_locale,
        ]);
        $response = $response->json();
        return $response;
    }
    public function GetProductsReview($id_client, $id_product, $_locale = 'vi')
    {
        $response = Http::get("{$this->baseUrl}/api/get_product_review",[
            'id_product' => $id_product,
            'id_client' => $id_client,
            '_locale' => $_locale,
        ]);
        $response = $response->json();
        return $response;
    }

    public function get_option($field = '')
    {
        $response = Http::get("{$this->baseUrl}/api/getOption/{$field}");
        $response = $response->json();
        return !empty($response['result']) ? $response['result'] : null;
    }
    public function get_code_leader($field = '')
    {
        $response = Http::get("{$this->baseUrl}/api/get_code_leader/{$field}");
        $response = $response->json();
        return !empty($response['result']) ? $response['result'] : null;
    }
    public function getDataClientReview($request = []){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/getDataClientReview",
                $request,
                ['token' => $request->client->token ?? 'nglow']
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
                'type' => $data['type'] ?? null,
                'message' => $data['message'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getListDataProduct($request = []){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/products/getListData",
                $request,
                ['token' => $request->client->token ?? 'nglow']
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

    public function getListDataProductID($list_id_product = [], $request = []){
        try {
            $request->merge(['list_id_product' => $list_id_product]);
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/products/get_list_product_id",
                $request,
                ['token' => $request->client->token ?? 'nglow']
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

    public function getListPaymentMode($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/category/getListPaymentMode",
                $request,
                ['token' => $request->client->token ?? 'nglow']
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



    public function PostAdmin($request, $token, $url = '')
    {
        try {
            if(!empty($url)) {
                $response = $this->sendRequestToService(
                    'POST',
                    "{$this->baseUrl}/$url",
                    $request,
                    ['token' => $token],
                );
                $response = $response->json();
                return $response;
            }
            else {
                return response()->json([
                    'result' => false,
                ], 500);
            }
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        //        $response = Http::post("{$this->baseUrl}/api/SignUpReviewProduct", [
        //            'list_review' => $listReview,
        //            'id_client' => $id_client,
        //            'token' => $token,
        //        ]);
        //        $response = $response->json();
        //        return $response;
    }

    public function getListDataReview($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/getListDataReview",
                $request,
                ['token' => $request->client->token ?? 'nglow']
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


    public function getCountReviewClient($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/CountReviewClient",
                $request,
                ['token' => $request->client->token ?? 'nglow']
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

    public function getListDiscountOrder($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/getListDiscountOrder",
                $request,
                ['token' => $request->client->token ?? 'nglow']
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

    public function GetUrlSetings($url = '', $params = [])
    {
        $response = Http::get("{$this->baseUrl}/{$url}", $params);
        $response = $response->json();
        return $response;
    }
    public function getListReportViolation($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/report_violation/getList",
                $request,
                ['token' => $request->client->token ?? 'nglow']
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

    public function createQRPayment($data = [], $_locale = 'vi')
    {
        $response = Http::post("{$this->baseUrl}/api/createQRPay2s", [
            'info' => $data,
            '_locale' => $_locale,
        ]);
        $response = $response->json();
        return $response;
    }
  
    public function insertCronjobEmail($ref = '')
    {
        $response = Http::get("{$this->baseUrl}/api/insertCronjobEmail/{$ref}");
        $response = $response->json();
        return $response;
    }
    public function updateOption($field = '', $value = ''){
        $response = Http::post("{$this->baseUrl}/api/updateOption/{$field}/{$value}");
        $response = $response->json();
        return $response;
    }
}
