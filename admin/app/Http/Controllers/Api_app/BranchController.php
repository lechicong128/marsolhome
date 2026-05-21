<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Branch;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends AuthController
{
    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
        $this->dbAccount = $accountService;
        $this->baseUrl   = config('services.storage.url');
    }

    /**
     * API lấy danh sách chi nhánh đang hoạt động
     *
     * @return \Illuminate\Http\JsonResponse
     * [
     *   { id, name, phone, address, map_link }
     * ]
     */
    public function getList()
    {
        $branches = Branch::where('active', 1)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($branch) {
                return [
                    'id'       => $branch->id,
                    'name'     => $branch->name,
                    'phone'    => $branch->phone,
                    'address'  => $branch->address,
                    'map_link' => $branch->map_link,
                ];
            });

        return response()->json([
            'result'  => true,
            'message' => lang('get_data_success'),
            'data'    => $branches,
        ]);
    }
}
