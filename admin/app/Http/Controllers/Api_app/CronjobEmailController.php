<?php

namespace App\Http\Controllers\Api_app;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CronjobEmailController extends AuthController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function insertCronjobEmail($id = '', $type = 1){
        if (!empty($id)) {
            DB::table('tbl_cron_email')->insert([
                'id_ref' => $id,
                'type' => $type,
                'status' => 0,
            ]);
        }
    }

}
