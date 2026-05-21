<?php

namespace App\Http\Controllers\Api_app;

use App\Models\CategoryService;
use App\Models\Service;
use App\Services\AccountService;
use App\Traits\UploadFile;
use App\Models\HistorySearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\NotificationTrait;

class ServiceController extends AuthController
{
    use UploadFile, NotificationTrait;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
        $this->dbAccount = $accountService;
        $this->baseUrl = config('services.storage.url');
    }

    /**
     * API lấy danh sách danh mục dịch vụ
     */
    public function getListCategories()
    {
        $categories = CategoryService::where('active', 1)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($cat) {
                return [
                    'id'    => $cat->id,
                    'name'  => $cat->name,
                    'image' => !empty($cat->image) ? $this->baseUrlAdmin . '/' . $cat->image : null,
                ];
            });

        return response()->json([
            'result'  => true,
            'message' => lang('get_data_success'),
            'data'    => $categories,
        ]);
    }

    /**
     * API lấy danh sách dịch vụ (có phân trang, filter theo category, tìm kiếm)
     * Query params:
     *   - current_page (default: 1)
     *   - per_page     (default: 10)
     *   - id_category  (lọc theo danh mục)
     *   - search       (tìm theo tên)
     */
    public function saveSearch() {
        $id_client = $this->request->client->id ?? 0;
        $search       = $this->request->input('search');
        if(!empty($search) && !empty($id_client)) {
            HistorySearchService::where('id_client', $id_client)->where('search', $search)->delete();
            HistorySearchService::insert([
                'id_client' => $id_client,
                'search' => $search,
            ]);
        }
        return response()->json([
            'result' => true,
            'message' => lang('save_history_search_success'),
        ]);
    }
    public function getList()
    {
        $current_page = $this->request->query('current_page', 1);
        $per_page     = $this->request->query('per_page', 10);
        $id_category  = $this->request->input('id_category');
        $search       = $this->request->input('search');
        $is_hot       = $this->request->input('is_hot');
        $id_client = $this->request->client->id ?? 0;
        $query = Service::from('tbl_services as s')
            ->select(
                's.id',
                's.code',
                's.name',
                's.price',
                's.discount_percent',
                's.duration_minutes',
                's.id_category',
                's.active',
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", s.image) as image'),
                'cs.name as category_name'
            )
            ->leftJoin('tbl_category_services as cs', 'cs.id', '=', 's.id_category')
            ->where('s.active', 1);

        if (!empty($id_category)) {
            $query->where('s.id_category', $id_category);
        }
        
        if (!empty($is_hot)) {
            $query->where('s.is_hot', $is_hot);
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('s.name', 'like', "%{$search}%")
                  ->orWhere('s.code', 'like', "%{$search}%");
            });
        }

        $query->orderBy('s.id', 'asc');

        $services = $query->paginate($per_page, ['*'], 'page', $current_page);

        return response()->json($services);
    }
    public function infoHistorySearch() {
        $id_client = $this->request->client->id ?? 0;
        
        $history_search = HistorySearchService::select('search')
            ->where('id_client', $id_client)
            ->orderBy('id', 'desc')
            ->limit(8)->get();

        $history_service_search = Service::from('tbl_services as s')
            ->select(
                's.id',
                's.code',
                's.name',
                's.price',
                's.discount_percent',
                's.duration_minutes',
                's.id_category',
                's.active',
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", s.image) as image'),
                'cs.name as category_name'
            )
            ->join('tbl_history_search_service_view as hsv', 'hsv.id_service', '=', 's.id')
            ->leftJoin('tbl_category_services as cs', 'cs.id', '=', 's.id_category')
            ->where('hsv.id_client', $id_client)
            ->orderBy('hsv.id', 'desc')
            ->limit(8)->get();

        return response()->json([
            'history_service' => $history_service_search,
            'history_search' => $history_search,
        ]);
    }
    /**
     * API lấy chi tiết dịch vụ theo id
     * Route param: {id}
     */
    public function getDetail($id,$is_search = 0)
    {
        $id_client = $this->request->client->id ?? 0;
        if(!empty($id_client) && $is_search == 1) {
            DB::table('tbl_history_search_service_view')
                ->where('id_client', $id_client)
                ->where('id_service', $id)
                ->delete();
            DB::table('tbl_history_search_service_view')->insert([
                'id_client' => $id_client,
                'id_service' => $id,
            ]);
        }
            
        $service = Service::from('tbl_services as s')
            ->select(
                's.id',
                's.code',
                's.name',
                's.price',
                's.discount_percent',
                's.duration_minutes',
                's.id_category',
                's.content',
                's.active',
                DB::raw('CONCAT("' . $this->baseUrlAdmin . '/", s.image) as image'),
                'cs.name as category_name'
            )
            ->leftJoin('tbl_category_services as cs', 'cs.id', '=', 's.id_category')
            ->where('s.id', $id)
            ->first();

        if (empty($service)) {
            return response()->json([
                'result'  => false,
                'message' => lang('get_data_fail'),
                'data'    => [],
            ]);
        }

        // Lấy ảnh gallery
        $galleryImages = DB::table('tbl_services_images')
            ->where('id_service', $service->id)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($img) {
                return $this->baseUrlAdmin . '/' . $img->image;
            })
            ->toArray();

        // Gộp ảnh đại diện (đứng đầu) + ảnh gallery
        $allImages = [];
        if (!empty($service->image)) {
            $allImages[] = $service->image; // đã có full URL từ CONCAT trong select
        }
        $allImages = array_merge($allImages, $galleryImages);

        $service->list_images = $allImages;

        return response()->json([
            'result'  => true,
            'message' => lang('get_data_success'),
            'data'    => $service,
        ]);
    }
}