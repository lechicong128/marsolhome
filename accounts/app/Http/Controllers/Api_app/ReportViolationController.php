<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ChallengeMeResources;
use App\Models\Clients;
use App\Models\Challenge;
use App\Models\ReportViolation;
use App\Models\RankCommunity;
use App\Services\AdminService;
use App\Services\NotiService;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ReportViolationController extends AuthController
{
    use UploadFile;
    protected $AdminService;
    protected $adminNoti;
    protected $_locale;
    public function __construct(Request $request, AdminService $adminService, NotiService $notiService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->AdminService = $adminService;
        $this->adminNoti = $notiService;
        $this->_locale = $request->_locale;
    }
    public function getList()
    {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');
        $status_search = $this->request->input('status_search') ?? -1;
        $query = ReportViolation::with([

        ])
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        if ($status_search != -1) {
            $query->where('type', $status_search);
        }
        
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        $total = ReportViolation::count();
        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}