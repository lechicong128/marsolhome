<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\Feedback;
use App\Models\FeedbackFile;
use App\Helpers\FilesHelpers;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//use App\Services\AccountService;
//use App\Services\AresService;
use Yajra\DataTables\CollectionDataTable;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends AuthController
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
    }

    public function getList(){
        $_locale = $this->request->_locale;
        if(empty($_locale)){
            $_locale = 'vi';
        }

        $filter = $this->request->input('filter');
        $active_search = $filter['active_search'] ?? -1;
        $search = $this->request->input('search');
        $orderBy = $this->request->input('order_by', 'id');
        $orderDir = $this->request->input('order_dir', 'asc');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $query = Feedback::select('tbl_feedback.*', 'tbl_clients.fullname', 'tbl_clients.phone', DB::raw('CONCAT("'.$this->baseUrl.'/", tbl_clients.avatar) as avatar'))
            ->where('tbl_feedback.id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }
        if(!empty($filter['star_like_search'])){
            $query->where('star_like', $filter['star_like_search']);
        }
        $query->addSelect(DB::raw('CONCAT("'.$this->baseUrl.'/star_like/", tbl_feedback.star_like, ".png") as img_star_like'));

        $query->addSelect(DB::raw('
            (
                SELECT GROUP_CONCAT(tbl_feedback_improve_translations.name SEPARATOR "||")
                FROM tbl_feedback_improve_detail
                LEFT JOIN tbl_feedback_improve ON tbl_feedback_improve.key_main = tbl_feedback_improve_detail.key_main AND tbl_feedback_improve.id_star_like = tbl_feedback_improve_detail.id_star_like
                LEFT JOIN tbl_feedback_improve_translations ON tbl_feedback_improve_translations.key_main = tbl_feedback_improve.key_main AND tbl_feedback_improve.id_star_like = tbl_feedback_improve_translations.id_star_like
                WHERE tbl_feedback_improve_detail.id_feedback = tbl_feedback.id
                AND tbl_feedback_improve_translations.language = "'.$_locale.'"
            ) as improve'));
        $query->addSelect(DB::raw('
            (
                SELECT GROUP_CONCAT(CONCAT("'.$this->baseUrl.'/", tbl_feedback_file.media) SEPARATOR "||")
                FROM tbl_feedback_file
                WHERE tbl_feedback_file.id_feedback = tbl_feedback.id
            ) as file_feedback'));
        $query->join('tbl_clients', 'tbl_feedback.id_client', '=', 'tbl_clients.id');
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        $total = Feedback::count();
        return response()->json([
            'total' => $total,
            '_locale' => $_locale,
            'filtered' => $filtered,
            'data' => $data
        ]);
    }

    public function countAll(){
        $arrType = [
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
            [
                'id' => 3,
            ],
            [
                'id' => 4,
            ],
            [
                'id' => 5,
            ],
        ];

        $query = Feedback::where('id','!=',0);
        $totalAll = $query->count();
        foreach ($arrType as $key => $value){
            $query = Feedback::where('id','!=',0);
                $query->where('star_like', $value['id']);
            $total = $query->count();
            $arrType[$key]['total'] = $total;
        }
        return response()->json([
            'total' => $totalAll,
            'arrType' => $arrType,
        ]);
    }

    public function getDetail(){
        $_locale = $this->request->_locale;
        if(empty($_locale)){
            $_locale = 'vi';
        }
        $id = $this->request->input('id') ?? 0;
        $feedback = Feedback::find($id);
        if (!empty($feedback->id)){
            $feedback->improve = DB::table('tbl_feedback_improve_detail')
                ->where('tbl_feedback_improve_detail.id_feedback', $feedback->id)
                ->leftJoin('tbl_feedback_improve', function($join) {
                    $join->on('tbl_feedback_improve.key_main', '=', 'tbl_feedback_improve_detail.key_main')
                        ->where('tbl_feedback_improve.id_star_like', '=', 'tbl_feedback_improve_detail.id_star_like');
                })
                ->leftJoin('tbl_feedback_improve_translations', function($join) {
                    $join->on('tbl_feedback_improve_translations.key_main', '=', 'tbl_feedback_improve.key_main')
                        ->where('tbl_feedback_improve_translations.id_star_like', '=', 'tbl_feedback_improve.id_star_like');
                })
                ->where('tbl_feedback_improve_translations.language', $_locale)
                ->get();

            $feedback->list_file = DB::table('tbl_feedback_file')
                ->select('tbl_feedback_file.*', DB::raw('CONCAT("'.$this->baseUrl.'/", media) as media'))
                ->where('id_feedback', $feedback->id)->get();
        }
        $data['result'] = true;
        $data['feedback'] = $feedback;

        $data['message'] = 'Lấy dữ liệu thành công';
        return response()->json($data);
    }


    public function send_feedback(){
        $_locale = $this->request->_locale;
        $client_id = $this->request->client->id ?? 0;
        $client = Clients::find($client_id);
        if(!empty($client)){
            $feedback = new Feedback();
            $feedback->id_client = $client_id;
            $feedback->star_like  = $this->request->input('star_like') ?? 1;
            $feedback->content_feedback  = $this->request->input('content_feedback') ?? NULL;
            $feedback->save();
            if(!empty($feedback->id)) {
                $improve = $this->request->input('improve') ?? [];
                foreach($improve as $key => $value){
                    DB::table('tbl_feedback_improve_detail')->insert([
                        'id_feedback' => $feedback->id,
                        'id_star_like' => $feedback->star_like,
                        'key_main' => $value,
                    ]);
                }

                if ($this->request->hasFile('file')) {
                    if (!empty($this->request->file('file'))) {
                        if (is_array($this->request->file('file'))) {
                            foreach ($this->request->file('file') as $file) {
                                $filetype = $file->getClientOriginalExtension();
                                $name_file = $file->getClientOriginalName();
                                $file_size = $file->getSize();
                                $mime_type = $file->getMimeType();
                                $FeedbackFile = new FeedbackFile();
                                $path = $this->UploadFile($file, 'feedback/' . $feedback->id, 800, 600,false);
                                $FeedbackFile->media = $path;
                                $FeedbackFile->id_feedback = $feedback->id;
                                $FeedbackFile->filetype = $filetype;
                                $FeedbackFile->name_file = $name_file;
                                $FeedbackFile->file_size = $file_size;
                                $FeedbackFile->mime_type = $mime_type;
                                $FeedbackFile->type = explode('/', $mime_type)[0];
                                $FeedbackFile->save();
                            }
                        }
                    }
                }

                $data['result'] = true;
                $data['message'] = 'Gửi phản hồi thành công. Cảm ơn bạn đã đóng góp ý kiến cho chúng tôi!';
                return response()->json($data);
            }
            else {
                $data['result'] = false;
                $data['message'] = 'Gửi phản hồi không thành công';
                return response()->json($data);
            }
        }
    }

    public function delete(){
        $id = $this->request->input('id') ?? 0;
        $feedback = Feedback::find($id);
        if (empty($feedback->id)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại feedback';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $feedback->delete();

            DB::table('tbl_feedback_improve_detail')
                ->where('id_feedback', $id)
                ->delete();

            $feedbackFile = DB::table('tbl_feedback_file')
                ->where('id_feedback', $id)->get();
            foreach($feedbackFile as $file){
                $this->deleteFile($file->media);
            }

            $feedbackFile = DB::table('tbl_feedback_file')
                ->where('id_feedback', $id)->delete();

            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            return response()->json($data);
        }  catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function get_improve_feedback() {
        $_locale = $this->request->_locale;
        if(empty($_locale)){
            $_locale = 'vi';
        }
        $array_improve = [];
        for($i = 1; $i <= 5; $i++) {
            $data_improve = DB::table('tbl_feedback_improve')
                ->where('id_star_like', $i)
                ->get();
            if(!empty($data_improve)) {
                foreach ($data_improve as $key => $item) {
                    $dataStran = DB::table('tbl_feedback_improve_translations')
                        ->where('key_main', $item->key_main)
                        ->where('language', $_locale)
                        ->first();
                    $item->name = $dataStran->name;
                }
            }
            $array_improve[$i][] = [
                'id_star_like' => $i,
                'name_star_like' => lang('lang_star_like_' . $i),
                'improve' => $data_improve
            ];
        }

        return response()->json([
            'result' => true,
            'data' => $array_improve
        ]);
    }

    public function update_feedback_improve() {
        $key_improve = $this->request->input('key_improve');
        $improve = $this->request->input('improve');
        if(!empty($key_improve)) {
            $arrayIsNotDelete = [];
            foreach ($key_improve as $id_star_like => $items) {
                foreach ($items as $key => $value) {
                    if(empty($value)) continue;
                    $ktImprove = DB::table('tbl_feedback_improve')
                        ->where('id_star_like', $id_star_like)
                        ->where('key_main', $value)->first();
                    if(!empty($ktImprove->id)) {
                        $id_improve = $ktImprove->id;
                    }
                    else {
                        $id_improve = DB::table('tbl_feedback_improve')->insertGetId([
                            'id_star_like' => $id_star_like,
                            'key_main' => $value,
                            'name' => $value,
                        ]);
                    }
                    $arrayIsNotDelete[] = $id_improve;
                    if(!empty($improve[$id_star_like][$key])) {
                        foreach ($improve[$id_star_like][$key] as $lang => $itemsInprove) {
                            DB::table('tbl_feedback_improve_translations')->updateOrInsert([
                                'key_main' => $value,
                                'language' => $lang,
                                'id_star_like' => $id_star_like
                            ],
                            [
                                'name' => $itemsInprove ?? '',
                                'key_main' => $value,
                            ]);
                        }
                    }
                }
            }
            $imporveDelele = DB::table('tbl_feedback_improve')->whereNotIn('id', $arrayIsNotDelete)->get();
            foreach($imporveDelele as $key => $value) {
                DB::table('tbl_feedback_improve_translations')
                    ->where('key_main', $value->key_main)
                    ->where('id_star_like', $value->id_star_like)->delete();
            }
            DB::table('tbl_feedback_improve')->whereNotIn('id', $arrayIsNotDelete)->delete();

            $data['result'] = true;
            $data['message'] = 'Cập nhật thành công';
            return response()->json($data);
        }
    }

    public function feedback_improve() {
        $feedback_improve = DB::table('tbl_feedback_improve')->get();
        if(!empty($feedback_improve)) {
            foreach ($feedback_improve as $key => $item) {
                $dataStran = DB::table('tbl_feedback_improve_translations')
                    ->where('key_main', $item->key_main)->get();
                $item->translations = $dataStran;
            }
        }
        return response()->json([
            'result' => true,
            'data' => $feedback_improve
        ]);
    }
}
