<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

//use Illuminate\Support\Facades\Request;

class ServiceService
{
    use RequestServiceTrait;
    protected $baseUrl;
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.service.base_url'), '/');
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
                ['token' => $request->client->token ?? 'm4u']
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

    public function get_list_by_ids($url = '', $list_ids = [], $locale = 'vi'){
        $request = new Request();
        $request->merge([
            'ids' => $list_ids,
            '_locale' => $locale,
        ]);
        try {
            $response = $this->sendRequestToService(
                'post',
                "{$this->baseUrl}/{$url}",
                $request,
                ['token' => $request->client->token ?? 'm4u']
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

}
