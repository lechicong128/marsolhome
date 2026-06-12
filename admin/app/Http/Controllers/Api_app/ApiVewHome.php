<?php

namespace App\Http\Controllers\Api_app;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AccountService;
use App\Models\Home;
use App\Http\Resources\HomeResources;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiVewHome extends AuthController
{
    protected $dbAccount;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        $this->dbAccount = $accountService;
    }

    /**
     * Lấy danh sách user client đã từng xem 1 bất động sản
     * GET /api/viewhome/getListViewers/{home_id}
     */
    public function getListViewers($home_id)
    {
        $current_page = $this->request->query('current_page', 1);
        $per_page = $this->request->query('per_page', 20);

        // Kiểm tra BĐS có tồn tại không
        $home = DB::table('tbl_home')->where('id', $home_id)->first();
        if (empty($home)) {
            return response()->json([
                'result' => false,
                'data' => null,
                'message' => 'Bất động sản không tồn tại'
            ]);
        }

        // Query danh sách lượt xem, nhóm theo client, lấy lần xem gần nhất + tổng lượt xem
        $query = DB::table('tbl_home_views')
            ->select(
                'tbl_home_views.id_client',
                DB::raw('COUNT(*) as total_views'),
                DB::raw('MAX(tbl_home_views.viewed_at) as last_viewed_at'),
                DB::raw('MIN(tbl_home_views.viewed_at) as first_viewed_at')
            )
            ->where('tbl_home_views.home_id', $home_id)
            ->groupBy('tbl_home_views.id_client')
            ->orderByDesc('last_viewed_at');

        // Phân trang
        $total = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query)
            ->count();

        $viewers = $query
            ->offset(($current_page - 1) * $per_page)
            ->limit($per_page)
            ->get();

        // Lấy thông tin client từ AccountService
        $clientIds = $viewers->pluck('id_client')->unique()->values()->toArray();
        $dtCustomer = collect([]);

        if (!empty($clientIds)) {
            $requestCustomer = clone $this->request;
            $requestCustomer->merge(['customer_id' => $clientIds]);
            $responseCustomer = $this->dbAccount->getListData($requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $dtCustomer = collect($dataCustomer['data'] ?? []);
        }

        // Ghép thông tin client vào danh sách viewers
        $list = $viewers->map(function ($item) use ($dtCustomer) {
            $customer = $dtCustomer->where('id', $item->id_client)->first();
            return [
                'id_client' => $item->id_client,
                'total_views' => (int) $item->total_views,
                'last_viewed_at' => $item->last_viewed_at,
                'first_viewed_at' => $item->first_viewed_at,
                'customer' => $customer,
            ];
        });

        // Tổng lượt xem (tất cả)
        $totalViews = DB::table('tbl_home_views')
            ->where('home_id', $home_id)
            ->count();

        $totalUniqueViewers = $total;

        return response()->json([
            'result' => true,
            'data' => [
                'list' => $list,
                'total_views' => $totalViews,
                'total_unique_viewers' => $totalUniqueViewers,
                'current_page' => (int) $current_page,
                'per_page' => (int) $per_page,
                'last_page' => $per_page > 0 ? (int) ceil($total / $per_page) : 1,
                'total' => $total,
                'home' => [
                    'id' => $home->id,
                    'title' => $home->title ?? '',
                    'code' => $home->code ?? '',
                ],
            ],
            'message' => 'Lấy danh sách người xem thành công'
        ]);
    }

    /**
     * Ghi nhận lượt xem BĐS của 1 client
     * POST /api/viewhome/trackView
     */
    public function trackView()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $home_id = $this->request->input('home_id');

        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng này!'
            ]);
        }

        if (empty($home_id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng chọn bất động sản!'
            ]);
        }

        $source = $this->request->input('source', 'app');
        $res = track_home_view($home_id, $customer_id, $source);

        return response()->json($res);
    }

    /**
     * Lấy danh sách BĐS mà 1 client đã từng xem
     * GET /api/viewhome/getListViewedByClient
     */
    public function getListViewedByClient()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $current_page = $this->request->query('current_page', 1);
        $per_page = $this->request->query('per_page', 20);

        if (empty($customer_id)) {
            return response()->json([
                'result' => false,
                'data' => null,
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng này!'
            ]);
        }

        // Query danh sách BĐS đã xem, nhóm theo home_id
        $query = DB::table('tbl_home_views')
            ->select(
                'tbl_home_views.home_id',
                DB::raw('MAX(tbl_home_views.viewed_at) as last_viewed_at')
            )
            ->where('tbl_home_views.id_client', $customer_id)
            ->groupBy('tbl_home_views.home_id')
            ->orderByDesc('last_viewed_at');

        $total = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query)
            ->count();

        $viewedHomes = $query
            ->offset(($current_page - 1) * $per_page)
            ->limit($per_page)
            ->get();

        // Lấy thông tin BĐS
        $homeIds = $viewedHomes->pluck('home_id')->toArray();
        $homes = collect([]);
        if (!empty($homeIds)) {
            $homes = Home::with([
                'propertyType',
                'province',
                'ward',
                'direction',
                'legal',
                'interior',
                'media_items',
                'interior_amenities',
                'utilities',
                'utilities.options',
                'favourite'
            ])
            ->whereIn('id', $homeIds)
            ->get()
            ->keyBy('id');
        }

        // Sắp xếp lại danh sách Home theo thứ tự xem gần đây nhất
        $sortedHomes = collect();
        foreach ($homeIds as $id) {
            if ($home = $homes->get($id)) {
                $sortedHomes->push($home);
            }
        }

        // Lấy thông tin customer cho từng Home giống như ApiSearchHome
        $allCustomerIds = $sortedHomes->pluck('customer_id')->unique()->values()->toArray();
        $dtCustomer = collect();
        if (!empty($allCustomerIds)) {
            $this->requestCustomer = clone $this->request;
            $this->requestCustomer->merge(['customer_id' => $allCustomerIds]);
            $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $dtCustomer = collect($dataCustomer['data'] ?? []);
        }

        $sortedHomes->transform(function ($item) use ($dtCustomer) {
            $customer = $dtCustomer->where('id', $item->customer_id)->first();
            $item->customer = $customer;
            return $item;
        });

        // Tạo custom LengthAwarePaginator để giữ định dạng phân trang
        $paginator = new LengthAwarePaginator(
            $sortedHomes,
            $total,
            $per_page,
            $current_page,
            ['path' => $this->request->url(), 'query' => $this->request->query()]
        );

        $collection = HomeResources::collection($paginator);

        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách BĐS đã xem thành công'
        ]);
    }
}
