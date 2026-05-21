<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class InvoiceService
{
    protected $baseUrl;
    use RequestServiceTrait;
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.account.base_url'), '/');
    }

    public function getListWaitingInvoice($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/invoice/getListWaitingInvoice",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ], $response->status());
            }

            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $data['total'], // tổng số user
                'recordsFiltered' => $data['filtered'], // tổng user sau khi lọc
                'data' => $data['data'], // danh sách user hiện tại
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function changeStatusInvoice($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/invoice/changeStatusInvoice",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                ], $response->status());
            }
            $data = $response->json();
            dd($data);
            return response()->json([
                'result' => $data['result'],
                'message' => $data['message']
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getTransactionItem($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/invoice/getTransactionItem",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                    'data' => [],
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'data' => $data['data'],
                'message' => $data['message']
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'data' => [],
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function submitDetailInvoice($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/invoice/submitDetailInvoice",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'message' => $data['message']
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getListInvoice($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/invoice/getListInvoice",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
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
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function viewInvoice($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/invoice/viewInvoice",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'base64pdf' => $data['base64pdf'],
                'message' => $data['message']
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                    'result' => false,
                    'base64pdf' => '',
                    'message' => $e->getMessage(),
                ], 500);
        }
    }

    public function deleteInvoice($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/invoice/deleteInvoice",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'message' => $data['message']
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDetailInvoice($request)
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/invoice/detailInvoice",
                $request,
            );
        if (!$response->successful()) {
            return response()->json([
                'result' => false,
                'status' => $response->status(),
                'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
            ], $response->status());
        }
        $data = $response->json();
        return response()->json([
            'result' => $data['result'],
            'data' => $data['data'],
            'message' => $data['message']
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'data' => [],
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
