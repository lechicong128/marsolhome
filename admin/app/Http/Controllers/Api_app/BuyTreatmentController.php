<?php

namespace App\Http\Controllers\Api_app;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AccountService;

class BuyTreatmentController extends AuthController
{
    protected $dbAccount;
    protected $baseUrl;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        $this->dbAccount = $accountService;
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
        $this->baseUrl = config('services.storage.url');
    }

    /**
     * API Get List Buy Treatment
     */
    public function getList(Request $request)
    {
        $id_client = $this->request->client->id ?? 0;

        if (empty($id_client)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng đăng nhập',
                'data'    => [],
            ], 401);
        }

        try {
            $current_page = (int) $request->input('current_page', 1);
            $per_page     = (int) $request->input('per_page', 10);

            $query = DB::table('tbl_treatment_purchases as tp')
                ->leftJoin('tbl_category_services as cs', 'tp.id_category', '=', 'cs.id')
                ->leftJoin('tbl_branches as br', 'tp.id_branch', '=', 'br.id')
                ->select(
                    'tp.id',
                    'tp.purchase_code',
                    'tp.treatment_name',
                    'tp.total_sessions',
                    'tp.used_sessions',
                    'tp.price',
                    'tp.status',
                    'tp.created_at',
                    'cs.name as category_name',
                    'br.name as branch_name'
                )
                ->where('tp.id_client', $id_client)
                ->orderBy('tp.created_at', 'desc');

            $status = $request->input('status');
            if (!empty($status)) {
                $query->where('tp.status', $status);
            }

            $id_category = $request->input('id_category');
            if ($request->filled('id_category')) {
                $query->where(function($q) use ($id_category) {
                    $q->where('tp.id_category', $id_category)
                      ->orWhere('tp.id_category', 0)
                      ->orWhereNull('tp.id_category');
                });
            }

            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $query->where(function($q) use ($keyword) {
                    $q->where('tp.treatment_name', 'like', "%{$keyword}%")
                      ->orWhere('tp.purchase_code', 'like', "%{$keyword}%");
                });
            }

            $paginated = $query->paginate($per_page, ['*'], 'page', $current_page);
            $items = $paginated->items();

            if (!empty($items)) {
                $purchaseIds = collect($items)->pluck('id')->toArray();

                $usedServices = DB::table('tbl_treatment_sessions as ts')
                    ->join('tbl_spa_booking_services as bs', 'ts.id_booking_service', '=', 'bs.id')
                    ->select('ts.id_purchase', 'bs.id_service', 'bs.name', DB::raw('COUNT(ts.id) as quantity'))
                    ->whereIn('ts.id_purchase', $purchaseIds)
                    ->where(function($q) {
                        $q->whereNull('ts.note')
                          ->orWhere('ts.note', 'not like', '%(Đã hoàn lại%');
                    })
                    ->groupBy('ts.id_purchase', 'bs.id_service', 'bs.name')
                    ->get();

                $groupedServices = collect($usedServices)->groupBy('id_purchase');

                foreach ($items as $item) {
                    $services = $groupedServices->get($item->id, collect([]))->map(function($svc) {
                        return [
                            'id_service' => $svc->id_service,
                            'name'       => $svc->name,
                            'quantity'   => $svc->quantity
                        ];
                    })->values();

                    $item->used_services       = $services;
                    $item->count_used_services = $services->sum('quantity'); // tổng lượt dịch vụ đã dùng
                }
            }

            $response = $paginated->toArray();
            $response['data']    = $items;
            $response['result']  = true;
            $response['message'] = lang('get_data_success');
            
            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => $e->getMessage(),
                'data'    => [],
            ], 500);
        }
    }

    /**
     * API Get Detail Buy Treatment & Sessions History
     */
    public function getDetail(Request $request)
    {
        $id_client = $this->request->client->id ?? 0;

        if (empty($id_client)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng đăng nhập',
                'data'    => (object)[],
            ], 401);
        }

        $id = $request->input('id');
        if (empty($id)) {
            return response()->json([
                'result'  => false,
                'message' => 'Vui lòng truyền id',
                'data'    => (object)[],
            ], 422);
        }

        try {
            $purchase = DB::table('tbl_treatment_purchases as tp')
                ->leftJoin('tbl_category_services as cs', 'tp.id_category', '=', 'cs.id')
                ->leftJoin('tbl_branches as br', 'tp.id_branch', '=', 'br.id')
                ->select(
                    'tp.id',
                    'tp.purchase_code',
                    'tp.treatment_name',
                    'tp.total_sessions',
                    'tp.used_sessions',
                    'tp.price',
                    'tp.status',
                    'tp.created_at',
                    'cs.name as category_name',
                    'br.name as branch_name'
                )
                ->where('tp.id', $id)
                ->where('tp.id_client', $id_client)
                ->first();

            if (empty($purchase)) {
                return response()->json([
                    'result'  => false,
                    'message' => 'Không tìm thấy liệu trình',
                    'data'    => (object)[],
                ], 404);
            }

            // Lấy danh sách lịch sử sử dụng
            $sessions = DB::table('tbl_treatment_sessions')
                ->where('id_purchase', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            $purchase->sessions = $sessions;

            return response()->json([
                'result'  => true,
                'message' => lang('get_data_success'),
                'data'    => $purchase,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => $e->getMessage(),
                'data'    => (object)[],
            ], 500);
        }
    }
}
