<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class CommunityService
{
    protected $baseUrl;
    use RequestServiceTrait;
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.account.base_url'), '/');
    }

    public function getList($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/community/getList",
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
                ]);
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

    public function getDetail($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/community/getDetail/{$request->id}",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ($response->json()['message'] ?? 'Unknown error'),
                    'data' => []
                ]);
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

    public function countAll($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/community/countAll",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'all' => 0,
                    'arr' => [],
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ]);
            }

            $data = $response->json();
            return response()->json([
                'all' => $data['all'],
                'arr' => $data['arr'],
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

    public function delete($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/community/delete",
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

    public function toggleHide($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/community/toggleHide",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ($response->json()['message'] ?? 'Unknown error'),
                ]);
            }
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'] ?? false,
                'message' => $data['message'] ?? 'Thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getReports($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/community/getReports",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false, 'status' => $response->status(),
                    'message' => $response->json()['message'] ?? 'Unknown error',
                    'total' => 0, 'filtered' => 0, 'data' => [],
                ]);
            }
            $data = $response->json();
            return response()->json([
                'result'   => $data['result'] ?? true,
                'total'    => $data['total'] ?? 0,
                'filtered' => $data['filtered'] ?? 0,
                'data'     => $data['data'],
                'message'  => $data['message'] ?? 'OK',
            ]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getViolations()
    {
        try {
            $response = $this->sendRequestToService(
                'GET',
                "{$this->baseUrl}/api/community/getViolations",
                request(),
            );
            $data = $response->json();
            return response()->json(['result' => true, 'data' => $data['data'] ?? []]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'data' => []]);
        }
    }

    public function storeViolation($request)
    {
        try {
            $response = $this->sendRequestToService('POST', "{$this->baseUrl}/api/community/violations/store", $request);
            $data = $response->json();
            return response()->json(['result' => $data['result'] ?? false, 'message' => $data['message'] ?? 'Lỗi']);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateViolation($request, $id)
    {
        try {
            $response = $this->sendRequestToService('POST', "{$this->baseUrl}/api/community/violations/update/{$id}", $request);
            $data = $response->json();
            return response()->json(['result' => $data['result'] ?? false, 'message' => $data['message'] ?? 'Lỗi']);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteViolation($id)
    {
        try {
            $response = $this->sendRequestToService('DELETE', "{$this->baseUrl}/api/community/violations/delete/{$id}", request());
            $data = $response->json();
            return response()->json(['result' => $data['result'] ?? false, 'message' => $data['message'] ?? 'Lỗi']);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
