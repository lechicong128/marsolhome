<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ListBank as ListBankResource;
use app\Models\CategoryProducts;
use App\Models\ReportViolationMode;
use App\Services\AccountService;
use App\Traits\UploadFile;
use Google\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Traits\NotificationTrait;

class ReportViolationApiController extends AuthController
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

    public function getList()
    {

        $_locale = $this->request->input('_locale');// ngôn ngữ
        $_type = $this->request->input('type');// ngôn ngữ
        if (empty($_locale)) {
            $_locale = 'vi';
        }
        $ReportViolation = ReportViolationMode::from('tbl_reportviolation as r')
            ->select(
                'r.id',
                'pt.name',
            )
            ->leftJoin('tbl_reportviolation_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_reportviolation', '=', 'r.id')
                    ->where('pt.language', '=', $_locale);
            });
        if (!empty($_type)) {
            $ReportViolation = $ReportViolation->where('r.type', $_type);
        }
        $ReportViolation = $ReportViolation->get();
        return response()->json([
            'result' => true,
            'data' => [
                'ReportViolation' => $ReportViolation,
            ],
        ]);
    }
}