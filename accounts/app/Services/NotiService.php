<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class NotiService
{
    use RequestServiceTrait;
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.admin.base_url'), '/');
    }

    public function addNoti($request = [])
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/notification/addNoti",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'] ?? false,
                'message' => $data['message'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addNotiMutil($request = [])
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/notification/addNotiMutil",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'] ?? false,
                'message' => $data['message'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function addNotiPost($request = [])
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/notification/addNotiPost",
                $request,
            );
            $data = $response->json();
            // dd($data);
            return response()->json([
                'data' => $data,
                'result' => $data['result'] ?? false,
                'message' => $data['message'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
