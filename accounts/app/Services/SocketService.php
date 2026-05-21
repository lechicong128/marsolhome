<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class SocketService
{
    use RequestServiceTrait;
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.admin.base_url'), '/');
    }

    public function SendSocket($request = [])
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/socket/sendSocket",
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

}
