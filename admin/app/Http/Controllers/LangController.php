<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Not;
use Yajra\DataTables\DataTables;

class LangController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
    }

    public function app()
    {
        $data['title'] = 'Quản lý File ngôn ngữ APP';
        $data['language'] = Language::orderByRaw('is_default desc')->get();
        $data['data_language'] = [];
        foreach($data['language'] as $key => $lang) {
            $data['data_language'][$lang['code']] = getLangAppDataFlatTest($lang['code'], true) ?? NULL;
        }
        return view('admin.lang.list', $data);
    }

    public function submit() {
        $sql_query = $this->request->sql_query;
        foreach($sql_query as $locale => $val) {
            $baseDir = storage_path('app/public/lang_app/');
            if (! is_dir($baseDir)) {
                // nếu thư mục chưa có thì tạo
                if (! mkdir($baseDir, 0755, true) && ! is_dir($baseDir)) {
                    return false;
                }
            }
            $filePath = $baseDir . '/'.$locale.'.json';
            file_put_contents($filePath, $val, LOCK_EX);
        }
        $data['result'] = true;
        $data['message'] = lang('dt_success');
        return response()->json($data);
    }

}
