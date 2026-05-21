<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Notification;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\Validator;
use App\Helpers\FilesHelpers;
use Illuminate\Http\Request;
use App\Models\Script;
use App\Models\ScriptDetail;
use App\Models\ScriptDetailTranslations;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
//use App\Traits\UserActionLog;
//use App\Helpers\NotificationHelper;
//use App\Models\ReportViolationMode;
//use App\Models\PostReportMode;
//use App\Models\Comment;
use Carbon\Carbon;
//use App\Models\TopicMode;
use App\Models\ScriptChat;
use App\Models\Products;
use App\Models\Language;
use App\Models\ScriptChatTranslations;
//use App\Models\ItemVariantOption;

use App\Traits\NotificationTrait;

class ScriptController extends AuthController
{
    use UploadFile, NotificationTrait;
    protected $dbService;
    protected $_locale;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->vsession = $request->vsession ?? null;
        $this->id_client = $request->client->id ?? null;
        $this->isweb = $request->isweb ?? 0;
        $this->_locale = $request->_locale;

        $this->baseUrlAdmin = config('services.storage.url');
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
//        $this->dbAccount = $accountService;

    }

    function generateSessionCode($length = 32) {
        // Đảm bảo length là số chẵn để dùng bin2hex
        if ($length % 2 !== 0) {
            $length++;
        }

        // random_bytes tạo chuỗi nhị phân ngẫu nhiên, rất an toàn
        $bytes = random_bytes($length / 2);

        // bin2hex chuyển thành chuỗi hex (0-9, a-f)
        return bin2hex($bytes);
    }

    public function createSession() {
        $generateSessionCode = $this->generateSessionCode(40);
        if($generateSessionCode) {
            return response()->json([
                'result' => true,
                'vsession' => $generateSessionCode
            ]);
        }
    }

    //lấy danh sách cuộc trò chuyện hỗ trợ
    public function list_chat($type = 0)
    {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        $vsession = $this->vsession;
        $currentPage = max(1, (int)$this->request->query('current_page', 1));
        $perPage = max(1, (int)$this->request->query('per_page', 20));
        $paginator = ScriptChat::select('tbl_script_chat.*',
                DB::raw('COALESCE(st.message, tbl_script_chat.message) AS message'),
                DB::raw('COALESCE(st.json_item, tbl_script_chat.json_item) AS json_item'),
                DB::raw('COALESCE(st.option_filter, tbl_script_chat.option_filter) AS option_filter')
            )->LeftJoin('tbl_script_chat_translations as st', function ($join) use ($_locale) {
                $join->on('tbl_script_chat.id', '=', 'st.id_script_chat')
                    ->where('st.language', '=', $_locale);
            })
            ->where(function($q) use ($id_client, $vsession) {
            if (!empty($id_client)) {
                $q->where('id_client', $id_client);
            }
            else if (!empty($vsession)) {
                $q->where('vsession', $vsession);
            }
        })->where('hidden', 0)
            ->orderByDesc('id')
            ->simplePaginate(
                $perPage,
                ['*'],
                'page',
                $currentPage
            );
        if (!empty($paginator->items())) {
            // Chuẩn hoá item + file path
            $items = $paginator->getCollection()->map(function ($chat) use ($id_client) {

                $chatArr = $chat->toArray();
//                $options = is_array($chatArr['options'] ?? null) ? $chatArr['options'] : [];
//                foreach ($options as $k => $opt) {
//                    if (!is_array($opt)) {
//                        continue;
//                    }
//                    $options[$k]['file'] = !empty($opt['file']) ? asset('storage/' . $opt['file']) : null;
//                }
//                $chatArr['options'] = $options;



                $chatArr['option_filter'] = $chatArr['option_filter'] ? json_decode($chatArr['option_filter'], true) : null;
                if($chatArr['event_app'] == 'result_products_filter') {
                    $chatArr['products'] = $chatArr['option_filter'];

                    if(!empty($id_client)) {
                        $listCategoryId = getCategoryNoLimit($id_client);
                        $products = Products::from('tbl_products as p')->select('p.id')
                            ->where('p.id', $chatArr['products']['id'])
                            ->addSelect(
                                DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review) as video_review'), 'is_review as isSig')
                                ->leftJoin('tbl_clients_sign_up_review', function ($join) use ($id_client) {
                                    $join->on('tbl_clients_sign_up_review.id_product', '=', 'p.id')
                                        ->where('tbl_clients_sign_up_review.id_client', '=', $id_client);
                                });
                            $products->addSelect(DB::raw("
                                IF(
                                    EXISTS (
                                        SELECT 1
                                        FROM tbl_product_category AS c
                                        WHERE c.id_product = p.id
                                          AND c.id_category IN (".implode(',', $listCategoryId).")
                                    ), 0, 1
                                ) AS isLimit
                            "));
                        $infoProducts = $products->first();
                        if(!empty($infoProducts->id)) {
                            $chatArr['products']['isSig'] = $infoProducts->isSig ?? NULL;
                            $chatArr['products']['isLimit'] = $infoProducts->isLimit ?? 0;
                        }
                    }
                }
                $chatArr['options'] = $chatArr['json_item'] ? json_decode($chatArr['json_item'], true) : null;
                $chatArr['json_item'] = $chatArr['json_item'] ? json_decode($chatArr['json_item'], true) : null;
                return $chatArr;
            })->values();
        }
        if ($currentPage == 1) {
            if(!empty($items)) {
                if(!empty($items[0]['id_script_detail'])) {
                    $script_detail = ScriptDetail::find($items[0]['id_script_detail']);
                }
                else {
                    $script_detail = ScriptDetail::find($items[0]['id_script']);
                }
                if (!empty($script_detail->id)) {
                    $id_items = $items[0]['suport_items'] ?? 0;
                    if (!empty($script_detail->event_show) && $script_detail->event_show == 'select') {
                        $options = $this->detail_child($script_detail->id_script, $script_detail->id, $id_items);
                        $arrayItem = $items->toArray();
                        $arrayItem[0]['options'] = $options;
                        $items = collect($arrayItem);
                    }
                    else if($script_detail->event_app == 'products_filter') {
                        $arrayItem = $items->toArray();
                        $data_options = $this->get_products_filter($script_detail->id_event_app, $script_detail->id_script, $script_detail->id, $_locale, ($script_detail->is_multiple ?? 0));
                        $arrayItem[0]['options'] = $data_options['data'];
                        if(!empty($data_options['next'])) {
                            $arrayItem[0]['next'] = $data_options['next'];
                        }
                        $items = collect($arrayItem);
                    }
                    else {
                        $arrayItem = $items->toArray();
                        $is_next = ScriptDetail::where('id_script', $script_detail->id_script)->where(
                                'id_parent', $script_detail->id
                            )->where('active_view_app', 1)
                            ->get()
                            ->first();
                        $urlNext = !empty($is_next) ? url(
                            'api/script/active_script/' . $script_detail->id_script . '/' . $is_next->id . '/' . $id_items.'?_locale='.$_locale
                        ) : false;



                        if(empty($urlNext)) {
                            if(!empty($script_detail->active_start) || !empty($script_detail->end_to_reset)) {
                                $urlNext = url(
                                    'api/script/active_script/' . $script_detail->id_script . '?_locale=' . $_locale . '&active_start=1'
                                );
                                if(!empty($script_detail->active_start)) {
                                    $arrayItem[0]['start'] = 1;
                                }
                                else {
                                    $arrayItem[0]['end_to_reset'] = 1;
                                    $arrayItem[0]['event_app'] = 'event_restart';
                                }
                            }
                        }

                        if(!empty($script_detail->end_to_web) && !empty($this->isweb)) {
                            $urlNext = false;
                        }
                        $arrayItem[0]['next'] = $urlNext;
                        $items = collect($arrayItem);
                    }
                }
            }
            else {
                if(empty($type)) {
                    $this->get_script_default();
                    return $this->list_chat(1);
                }
            }
        }





        return response()->json([
            'result' => true,
            'show_end_script' => $this->ktEndScriptChat(),
            'data' => $items ?? [],
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'next' => $paginator->hasMorePages(), // giống cờ bạn đang cần
        ]);
    }


    private function ktEndScriptChat() {
        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        $vsession = $this->vsession;

        $ktEndScript = ScriptChat::where(function($q) use ($id_client, $vsession) {
            if (!empty($id_client)) {
                $q->where('id_client', $id_client);
            }
            else if (!empty($vsession)) {
                $q->where('vsession', $vsession);
            }
        })->where('event_app', 'result_products_filter')
            ->where('hidden', 0)->first();
        if(!empty($ktEndScript->id)) {
            return true;
        }
        return false;
    }

    // lấy tất cả các kịch bản hỗ trợ
    public function get_list(Request $request)
    {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $client_id = $this->request->client->id ?? 0;
        $list_script = Script::select('id', 'name', 'icon', 'order_by')
            ->where('active', 1)
            ->where(
                'order_default', '!=', 1
            )->orderBy('order_by', 'desc')
            ->get()->toArray();
        foreach ($list_script as $key => $script) {
            $list_script[$key]['icon'] = !empty($script['icon']) ? asset('storage/' . $script['icon']) : null;
        }
        return response()->json([
            'result' => true,
            'data' => $list_script
        ]);
    }

    //chạy lấy bước kế tiếp của kịch bản
    public function detail_child($id_script = '', $id_child = '0', $id_items = 0) {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $client_id = $this->request->client->id ?? 0;
        //lấy tất cả kịch bản hỗ trợ bước tiếp theo
        $list_script_detail = ScriptDetail::select(
            'tbl_script_detail.id',
            'tbl_script_detail.id_script',
            'tbl_script_detail.name',
            'tbl_script_detail.level',
            'tbl_script_detail.link',
            'st.content',
            'st.language',
            'tbl_script_detail.file',
            'tbl_script_detail.type_send',
            'tbl_script_detail.event_show',
            'tbl_script_detail.event_app',
            'tbl_script_detail.show_move_event',
            'tbl_script_detail.id_event_app',
            'tbl_script_detail.active_start',
            'tbl_script_detail.end_to_reset',
            'tbl_script_detail.end_to_web',
            'tbl_script_detail.seconds_to_wait'
//            'tbl_script_detail.language_default',
        )->where('tbl_script_detail.id_script', $id_script)
            ->where('tbl_script_detail.id_parent', $id_child)
            ->where('tbl_script_detail.active_view_app', 1)
            ->LeftJoin('tbl_script_detail_translations as st', function ($join) use ($_locale) {
                $join->on('tbl_script_detail.id', '=', 'st.id_script_detail')
                    ->where('st.language', '=', $_locale);
            })->get()
            ->toArray();
        $data = [];
        foreach ($list_script_detail as $key => $scriptDetail) {
            $scriptDetail['file'] = !empty($scriptDetail['file']) ? asset('storage/' . $scriptDetail['file']) : null;
            //kiểm tra có phải select không nếu là select thì lấy thêm options
            if ($scriptDetail['event_show'] == 'select') {
                $detail_child = ScriptDetail::select(
                    'tbl_script_detail.id',
                    'tbl_script_detail.id_script',
                    'tbl_script_detail.name',
                    'tbl_script_detail.level',
                    'tbl_script_detail.link',
                    'st.content',
                    'st.language',
                    'tbl_script_detail.file',
                    'tbl_script_detail.type_send',
                    'tbl_script_detail.event_show',
                    'tbl_script_detail.event_app',
                    'tbl_script_detail.show_move_event',
                    'tbl_script_detail.language_default',
                    'tbl_script_detail.active_start',
                    'tbl_script_detail.seconds_to_wait',
                    'tbl_script_detail.end_to_reset',
                    'tbl_script_detail.end_to_web'
                )
                ->where('tbl_script_detail.id_script', $id_script)
                ->where('tbl_script_detail.id_parent', $scriptDetail['id'])
                ->where('tbl_script_detail.active_view_app', 1)
                ->LeftJoin('tbl_script_detail_translations as st', function ($join) use ($_locale) {
                    $join->on('tbl_script_detail.id', '=', 'st.id_script_detail')
                        ->where('st.language', '=', $_locale);
                })->get()->toArray();
                if (!empty($detail_child)) {
                    foreach ($detail_child as $k => $tDetailChild) {
                        $tDetailChild['file'] = !empty($tDetailChild['file']) ? asset(
                            'storage/' . $tDetailChild['file']
                        ) : null;
                        $detail_child_next = ScriptDetail::select('tbl_script_detail.*, st.content, st.language')
                            ->where('tbl_script_detail.id_script', $id_script)
                            ->where('tbl_script_detail.id_parent', $tDetailChild['id'])
                            ->where('tbl_script_detail.active_view_app', 1)->get()->count();
                        $tDetailChild['next'] = !empty($detail_child_next) ? url(
                            'api/script/active_script/' . $id_script . '/' . $tDetailChild['id'] . '/' . $id_items.'?_locale='.$_locale
                        ) : false;
                        if(!empty($tDetailChild['active_start']) || !empty($tDetailChild['end_to_reset'])) {
                            $tDetailChild['next'] = url(
                                'api/script/active_script/' . $id_script . '?_locale='.$_locale.'&active_start=1'
                            );
                            if(!empty($tDetailChild['active_start'])) {
                                $tDetailChild['start'] = 1;
                            }
                            else {
                                $tDetailChild['end_to_reset'] = 1;
                                $tDetailChild['event_app'] = 'event_restart';
                            }
                        }

                        if(!empty($tDetailChild['end_to_web']) && !empty($this->isweb)) {
                            $tDetailChild['next'] = false;
                        }

                        $scriptDetail['options'][] = $tDetailChild;
                    }
                    $data[] = $scriptDetail;
                }
            }
            else if ($scriptDetail['event_show'] == 'event_app' && $scriptDetail['event_app'] == 'products_filter') {
//                $detail_child['options'] = $this->get_products_filter($scriptDetail['id_event_app'], $scriptDetail['id_script'], $scriptDetail['id'], $_locale,
//                    ($scriptDetail['is_multiple'] ?? 0))['data'];

                $data_options = $this->get_products_filter($scriptDetail['id_event_app'], $scriptDetail['id_script'], $scriptDetail['id'], $_locale,
                    ($scriptDetail['is_multiple'] ?? 0));
                $scriptDetail['options'] = $data_options['data'];
                if(!empty($data_options['next'])) {
                    $scriptDetail['next'] = $data_options['next'];
                }
            }
            else {
                //kiểm tra xem có bước tiếp theo không
                $detail_child_next = ScriptDetail::where('id_script', $id_script)
                    ->where('id_parent', $scriptDetail['id'])
                    ->where('active_view_app', 1)
                    ->get()
                    ->count();
                //kiểm tra xem có bước quay về giỏ hàng không
                if (!empty($detail_child_next)) {
                    $scriptDetail['next'] = !empty($detail_child_next) ? url(
                        'api/script/active_script/' . $id_script . '/' . $scriptDetail['id'] . '/' . $id_items.'?_locale='.$_locale
                    ) : false;
                }

                if(empty($scriptDetail['next'])) {
                    if(!empty($scriptDetail['active_start']) || !empty($scriptDetail['end_to_reset'])) {
//                        if(!empty($scriptDetail['active_start'])) {
//                        $scriptDetail['next'] = url(
//                            'api/script/active_script/' . $id_script . '/' . $scriptDetail['id'] . '/' . $id_items.'?_locale='.$_locale.'&active_start=1'
//                        );
                        $scriptDetail['next'] = url(
                            'api/script/active_script/' . $id_script . '?_locale='.$_locale.'&active_start=1'
                        );
                        if(!empty($scriptDetail['active_start'])) {
                            $scriptDetail['start'] = 1;
                        }
                        else {
                            $scriptDetail['end_to_reset'] = 1;
                            $tDetailChild['event_app'] = 'event_restart';
                        }

//                        $scriptChild = ScriptDetail::where('id_parent', $id_script)->first();
//                        $scriptDetail['next'] = url(
//                            'api/script/active_script/' . $id_script . '/' . $scriptChild->id.'?_locale='.$_locale
//                        );
                    }
                    else if(!empty($scriptDetail['end_to_reset'])) {
                        $scriptDetail['end_to_reset'] = 1;
                    }
                }


                if(!empty($tDetailChild['end_to_web']) && !empty($this->isweb)) {
                    $scriptDetail['next'] = false;
                }
                $scriptDetail['show_end_script'] = $this->ktEndScriptChat();
                $data[] = $scriptDetail;
            }
        }
        return $data;
    }

    public function get_list_detail($id_script = '', $id_child = '0')
    {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        //lấy chi tiết(cái này chỉ để xem)
        $data = $this->detail_child($id_script, $id_child);
        return response()->json([
            'result' => true,
            'show_end_script' => $this->ktEndScriptChat(),
            'data' => $data
        ]);
    }

    public function get_script_default() {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $vsession = $this->vsession;
        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        if(!empty($vsession) || !empty($id_client)) {
            DB::table('tblsession_client')
                ->updateOrInsert([
                'vsession' => $vsession ?? NULL,
                'id_client' => $id_client ?? 0,
            ]);
            $listChat = Script::where('chat_default', 1)->first();
            $data = $this->active_script($listChat->id, 0);
            return $data;
        }
        else {
            return response()->json([
                'result' => false,
            ]);
        }
    }


    public function get_info_script() {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $dtScript = Script::where('chat_default', '=', 1)
            ->select('tbl_script.id', 'st.content as content', 'st.language')
            ->LeftJoin('tbl_script_translations as st', function ($join) use ($_locale) {
                $join->on('tbl_script.id', '=', 'st.id_script')
                    ->where('st.language', '=', $_locale);
            })->first();
        return response()->json([
            'result' => true,
            'data' => $dtScript
        ]);
    }


    //thực thi kịch bản hỗ trợ của các bước chi tiết
    public function active_script($id_script = '', $id_detail = '0', $id_items = 0)
    {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $active_start = $this->request->query('active_start', 0);

        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        $vsession = $this->vsession;

        $script_detail = ScriptDetail::where('tbl_script_detail.id_script', $id_script)->select(
                'tbl_script_detail.*',
                'st.content as content',
                'st.language'
            )->LeftJoin('tbl_script_detail_translations as st', function ($join) use ($_locale) {
                $join->on('tbl_script_detail.id', '=', 'st.id_script_detail')
                    ->where('st.language', '=', $_locale);
            })->where(function ($query) use ($id_script, $id_detail) {
                if (!empty($id_detail)) {
                    $query->where('tbl_script_detail.id', $id_detail);
                } else {
                    $query->where('tbl_script_detail.id_parent', 0);
                }
            })->first();
        if ($script_detail->event_show == 'start' && empty($script_detail->content) && empty($script_detail->file)) {
            $script_detail = ScriptDetail::select('tbl_script_detail.*, st.content, st.language')
                ->where('tbl_script_detail.id_script', $id_script)
                ->LeftJoin('tbl_script_detail_translations as st', function ($join) use ($_locale) {
                    $join->on('tbl_script_detail.id', '=', 'st.id_script_detail')->where('st.language', '=', $_locale);
                })
                ->where('tbl_script_detail.id_parent', $script_detail->id)
                ->first();
        }

        if(empty($id_detail) || !empty($active_start)) {
            ScriptChat::where('id_script', $id_script)
                ->where(function ($q) use ($id_client, $vsession) {
                    if(!empty($id_client)) {
                        $q->where('id_client', $id_client);
                    }
                    else if(!empty($vsession)) {
                        $q->where('vsession', $vsession);
                    }
                })
                ->update(['hidden' => 1]);
        }

        if (!empty($script_detail)) {
            if (empty($id_items) && !empty($script_detail->suport_items)) {
                $id_items = $script_detail->suport_items ?? 0;
            }
            DB::beginTransaction();
//            try {
                $ChatLaster = ScriptChat::where(function ($q) use ($id_client, $vsession) {
                    if(!empty($id_client)) {
                        $q->where('id_client', $id_client);
                    }
                    else if(!empty($vsession)) {
                        $q->where('vsession', $vsession);
                    }
                })->where('hidden', 0)->orderBy('created_at', 'desc')->first();
                if(!empty($ChatLaster->id)) {
                    $existingChat = ScriptChat::where('id_script', $id_script)
                        ->where('id', '=', $ChatLaster->id)
                        ->where('id_script_detail', $id_detail)
                        ->where(function ($q) use ($id_client, $vsession) {
                            if(!empty($id_client)) {
                                $q->where('id_client', $id_client);
                            }
                            else if(!empty($vsession)) {
                                $q->where('vsession', $vsession);
                            }
                        })
                        ->where('suport_items', $id_items)
                        ->where('message', $script_detail->content)
                        ->first();
                }
                if(empty($existingChat))
                {
                    $scriptChat = new ScriptChat();
                    $scriptChat->id_script = $id_script;
                    $scriptChat->id_script_detail = $id_detail;
                    $scriptChat->id_client = $id_client ?? 0;
                    $scriptChat->is_read = 0;
                    $scriptChat->type_send = $script_detail->type_send;
                    $scriptChat->message = $script_detail->content;
                    $scriptChat->event = $script_detail->event_show;
                    $scriptChat->file = $script_detail->file;
                    $scriptChat->suport_items = $id_items ?? 0;
                    $scriptChat->json_item = !empty($script_detail->data_items) ? json_encode(
                        $script_detail->data_items
                    ) : null;
                    $scriptChat->show_move_event = $script_detail->show_move_event;
                    $scriptChat->event_app = $script_detail->event_app;
                    $scriptChat->is_function = 'active_script';
                    $scriptChat->language_default = $_locale;
                    $scriptChat->vsession = $this->vsession;
                    $scriptChat->id_event_app = $script_detail->id_event_app;
                    $scriptChat->is_multiple = $script_detail->is_multiple;
                    $scriptChat->save();
                    if (!empty($scriptChat->id)) {
                        if(empty($id_detail) || !empty($active_start)) {
                            $scriptChat->id_chat_parent = $scriptChat->id;
                            $scriptChat->save();
                        }
                        else {
                            $scriptChat->id_chat_parent = $ChatLaster->id_chat_parent ?? $scriptChat->id;
                            $scriptChat->save();
                        }

                        $this->get_script_detail_translation($id_script, $script_detail->id, $scriptChat->id, [
                            'json_item' => $script_detail->json_item ?? null,
                            'option_filter' => $script_detail->option_filter ?? null,
                        ]);
                    }
                }
                else {
//                    $scriptChat = ScriptChat::find($existingChat->id);
                    $scriptChat = ScriptChat::select('tbl_script_chat.*',
                        DB::raw('COALESCE(st.message, tbl_script_chat.message) AS message'),
                        DB::raw('COALESCE(st.json_item, tbl_script_chat.json_item) AS json_item'),
                        DB::raw('COALESCE(st.option_filter, tbl_script_chat.option_filter) AS option_filter'),
                    )
                        ->LeftJoin('tbl_script_chat_translations as st', function ($join) use ($_locale) {
                        $join->on('tbl_script_chat.id', '=', 'st.id_script_chat')
                            ->where('st.language', '=', $_locale);
                    })
                        ->where(function($q) use ($id_client, $vsession) {
                        if (!empty($id_client)) {
                            $q->where('id_client', $id_client);
                        }
                        else if (!empty($vsession)) {
                            $q->where('vsession', $vsession);
                        }
                    })
                        ->where('tbl_script_chat.id', $existingChat->id)
                        ->where('hidden', 0)
                        ->first();
                }
                $scriptChat->event_app = $script_detail->event_app;
                $scriptChat->event_show = $script_detail->event_show;
                DB::commit();
                if (!empty($script_detail->event_show) && $script_detail->event_show == 'select') {
                    $scriptChat->options = $this->detail_child($id_script, $script_detail->id, $id_items);
                }
                else if ($script_detail->event_show == 'event_app' && $script_detail->event_app == 'products_filter') {


                    $data_options = $this->get_products_filter($script_detail->id_event_app, $script_detail->id_script, $script_detail->id, $_locale, ($script_detail->is_multiple ?? 0));
                    $options = $data_options['data'];
                    $scriptChat->options = $options;
                    if(!empty($data_options['next'])) {
                        $scriptChat->next = $data_options['next'];
                    }
                }
                else if ($script_detail->event_show == 'event_app' && $script_detail->event_app == 'result_products_filter') {
                    return $this->active_result_script_products_filter($script_detail->id_script, $script_detail->id , 0, $scriptChat->id);
                }
                else {
                    $is_next = ScriptDetail::where('id_script', $id_script)
                        ->where('id_parent', $script_detail->id)->get()->first();
                }

                if (!empty($script_detail->data_items)) {
                    $scriptChat->data_items = !empty($script_detail->data_items) ? $script_detail->data_items : [];
                }

                if(!empty($script_detail->active_start) || !empty($script_detail->end_to_reset)) {

                    $UrlNext = url(
                        'api/script/active_script/' . $id_script . '?_locale='.$_locale.'&active_start=1'
                    );
                    if(!empty($script_detail->end_to_web) && !empty($this->isweb)) {
                        $UrlNext = false;
                    }
                    $scriptChat->event_app = 'event_restart';

                    return response()->json([
                        'result' => true,
                        'show_end_script' => $this->ktEndScriptChat(),
                        'data' => $scriptChat,
//                        'next' => url('api/script/active_script/' . $id_script . '?_locale='.$_locale.'&active_start=1'),
                        'next' => $UrlNext,
                        'start' => $script_detail->active_start ?? 0,
                        'end_to_reset' => $script_detail->end_to_reset ?? 1,
                    ]);
                }


                if(!empty($script_detail->end_to_web) && !empty($this->isweb)) {

                    $is_next = false;
                }

                $scriptChat->seconds_to_wait = $script_detail->seconds_to_wait ?? 2;

                return response()->json([
                    'result' => true,
                    'show_end_script' => $this->ktEndScriptChat(),
                    'data' => $scriptChat,
                    'next' => !empty($is_next) ? url(
                        'api/script/active_script/' . $id_script . '/' . $is_next->id . '/' . $id_items.'?_locale='.$_locale
                    ) : false
                ]);
//            }
//            catch (\Exception $e) {
//
//                Log::error($e->getMessage());
//                Log::error(DB::getQueryLog());
//                DB::rollBack();
//                return response()->json([
//                    'result' => false,
//                    'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
//                ]);
//            }
        }
        return response()->json([
            'result' => false
        ]);
    }

    private function get_products_filter($id_event_app = '', $id_script = '', $id_script_detail = '', $_locale = 'vi', $is_multiple = 0) {
        $data = list_view_append('products_filter', $id_event_app, $_locale);
        if(!empty($is_multiple)) {
            $next = url(
                'api/script/active_script_products_filter?id_event_app=' . $id_event_app . '&id_script=' . $id_script . '&id_script_detail=' . $id_script_detail . '&_locale=' . $_locale
            );
            return [
                'next' => $next,
                'data' => $data
            ];
        }
        else {
            foreach ($data as $key => $value) {
                $data[$key]['next'] = url(
                    'api/script/active_script_products_filter?id=' . $value['id'] . '&id_event_app=' . $id_event_app . '&id_script=' . $id_script . '&id_script_detail=' . $id_script_detail . '&_locale=' . $_locale
                );
            }
            return [
                'data' => $data
            ];
        }
    }

    public function active_script_products_filter()
    {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        $vsession = $this->vsession;
        $id = $this->request->input('id');
        if(is_string($id)) {
            $id = explode(',', $id);
        }
        if(is_numeric($id)){
            $id = [$id];
        }
        $id_event_app = $this->request->input('id_event_app');
        $id_script = $this->request->input('id_script');
        $id_detail = $this->request->input('id_script_detail');
        $id_items = 0;

        $script_detail = ScriptDetail::where('tbl_script_detail.id_script', $id_script)
            ->select(
            'tbl_script_detail.*',
            'st.content',
            'st.language'
        )
            ->LeftJoin('tbl_script_detail_translations as st', function ($join) use ($_locale) {
            $join->on('tbl_script_detail.id', '=', 'st.id_script_detail')
                ->where('st.language', '=', $_locale);
        })->where(function ($query) use ($id_script, $id_detail) {
            if (!empty($id_detail)) {
                $query->where('tbl_script_detail.id', $id_detail);
            } else {
                $query->where('tbl_script_detail.id_parent', 0);
            }
        })->first();

        if (!empty($script_detail) && $script_detail->event_show == 'event_app' && $script_detail->event_app == 'products_filter') {
            DB::beginTransaction();
            try {
                $allLanguages = Language::get();
                $_options_lang = [];
                $_options_json = [];
                $option_filter_lang = [];
                $content_lang = [];
                $scriptContent = [];
                if(!empty($allLanguages)) {
                    foreach ($allLanguages as $lang) {
                        $_options_lang[$lang->code] = $this->get_products_filter(
                            $id_event_app,
                            $script_detail->id_script,
                            $script_detail->id,
                            $lang->code,
                            ($script_detail->is_multiple ?? 0)
                        )['data'];
                        foreach ($_options_lang[$lang->code] as $key => $value) {
                            if (is_numeric(array_search($value['id'], $id))) {
                                if($lang->code == $_locale) {
                                    $scriptContent[] = $value['name'];
                                }
                                $content_lang[$lang->code][] = $value['name'];
                                $option_filter_lang[$lang->code][] = [
                                    'id' => $value['id'],
                                    'name' => $value['name'],
                                ];
                            }

                            $_options_json[$lang->code][$key] = [
                                'id' => $value['id'],
                                'name' => $value['name'],
                                'icon' => $value['icon'],
                                'icon_active' => $value['icon_active'] ?? NULL,
                            ];
                            if (is_numeric(array_search($value['id'], $id))) {
                                $_options_json[$lang->code][$key]['active'] = 1;
                            }
                        }
                    }
                }
                $script_detail->content = implode(', ', $scriptContent);
                $option_filter = $option_filter_lang[$_locale] ?? [];
                $json_item = NULL;

                if(empty($existingChat)) {
                    $scriptChat = new ScriptChat();
                    $scriptChat->id_script = $id_script;
                    $scriptChat->id_script_detail = $id_detail;
                    $scriptChat->id_client = $id_client ?? 0;
                    $scriptChat->is_read = 0;
                    $scriptChat->type_send = 1;
                    $scriptChat->message = $script_detail->content;
                    $scriptChat->event = $script_detail->event_show;
                    $scriptChat->json_item = !empty($json_item) ? json_encode($json_item) : null;
                    $scriptChat->show_move_event = $script_detail->show_move_event;
                    $scriptChat->event_app = $script_detail->event_app;
                    $scriptChat->is_function = 'active_script_products_filter';
                    $scriptChat->language_default = $_locale;
                    $scriptChat->vsession = $this->vsession;
                    $scriptChat->id_event_app = $script_detail->id_event_app;
                    $scriptChat->option_filter = json_encode($option_filter);
                    $scriptChat->is_multiple = $script_detail->is_multiple;
                    $scriptChat->save();
                    if(!empty($scriptChat->id)) {
                        $this->get_script_detail_translation($id_script, $script_detail->id, $scriptChat->id, [
//                            'json_item' => $_options_json ?? null,
//                            'option_filter' => $option_filter_lang ?? null,
                            'message' => $content_lang ?? null,
                        ]);

                        $ChatLaster = ScriptChat::where(function ($q) use ($id_client, $vsession) {
                            if(!empty($id_client)) {
                                $q->where('id_client', $id_client);
                            }
                            else if(!empty($vsession)) {
                                $q->where('vsession', $vsession);
                            }
                        })->where('id', '!=', $scriptChat->id)
                            ->where('type_send', '=', 0)
                            ->where('hidden', 0)
                            ->orderBy('id', 'desc')
                            ->first();
                        if(!empty($ChatLaster->id)) {
                            $scriptChat->id_chat_parent = $ChatLaster->id_chat_parent;
                            $scriptChat->save();
                            $this->get_script_detail_translation($id_script, $script_detail->id, $ChatLaster->id, [
                                'json_item' => $_options_json ?? null,
                                'option_filter' => $option_filter_lang ?? null,
                            ], 'edit');
                        }
                    }
                }
                else {
                    $scriptChat = ScriptChat::find($existingChat->id);
                }
                $scriptChat->event_app = $script_detail->event_app;
                $scriptChat->event_show = $script_detail->event_show;

                if (!empty($script_detail->data_items)) {
                    $scriptChat->data_items = !empty($script_detail->data_items) ? $script_detail->data_items : [];
                }

                foreach($id as $kID => $vID) {
                    DB::table('tbl_chat_products_filter')->insert([
                        'vsession' => $this->vsession,
                        'id_client' => $id_client ?? 0,
                        'id_product_filter' => $vID,
                        'id_chat' => $ChatLaster->id_chat_parent ?? 0,
                    ]);
                }

                DB::commit();

                $is_next = ScriptDetail::where('tbl_script_detail.id_script', $id_script)
                    ->where('tbl_script_detail.id_parent', $id_detail)->first();
                $scriptChat->seconds_to_wait = $script_detail->seconds_to_wait ?? 2;
                return response()->json([
                    'result' => true,
                    'show_end_script' => $this->ktEndScriptChat(),
                    'data' => $scriptChat,
                    'next' => !empty($is_next) ? url(
                        'api/script/active_script/' . $id_script . '/' . $is_next->id . '/' . $id_items.'?_locale='.$_locale
                    ) : false
                ]);
            }
            catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'result' => false,
                    'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
                ]);
            }
        }
        return response()->json([
            'result' => false
        ]);
    }

    public function active_result_script_products_filter($id_script = '', $id_detail = '', $id_items = 0, $idChat = 0) {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        $vsession = $this->vsession;
        $script_detail = ScriptDetail::where('tbl_script_detail.id_script', $id_script)->select(
            'tbl_script_detail.*',
            'st.content',
            'st.language'
        )->LeftJoin('tbl_script_detail_translations as st', function ($join) use ($_locale) {
            $join->on('tbl_script_detail.id', '=', 'st.id_script_detail')
                ->where('st.language', '=', $_locale);
        })->where(function ($query) use ($id_script, $id_detail) {
            if (!empty($id_detail)) {
                $query->where('tbl_script_detail.id', $id_detail);
            } else {
                $query->where('tbl_script_detail.id_parent', 0);
            }
        })->first();

        if (!empty($script_detail) && $script_detail->event_show == 'event_app' && $script_detail->event_app == 'result_products_filter') {
            DB::beginTransaction();
            try {
                $ChatLaster = ScriptChat::where(function ($q) use ($id_client, $vsession) {
                    if(!empty($id_client)) {
                        $q->where('id_client', $id_client);
                    }
                    else if(!empty($vsession)) {
                        $q->where('vsession', $vsession);
                    }
                })->where('hidden', 0)
                    ->orderBy('created_at', 'desc')->first();
                if(!empty($idChat)) {
                    $existingChat = ScriptChat::find($idChat);
                }

                $data_product = $this->get_info_product(false, ($ChatLaster->id_chat_parent ?? 0));


                if(empty($existingChat->id)) {
                    $scriptChat = new ScriptChat();
                    $scriptChat->id_script = $id_script;
                    $scriptChat->id_script_detail = $id_detail;
                    $scriptChat->id_client = $id_client ?? 0;
                    $scriptChat->is_read = 0;
                    $scriptChat->type_send = 1;
                    $scriptChat->message = $script_detail->content;
                    $scriptChat->event = $script_detail->event_show;
                    $scriptChat->json_item = !empty($script_detail->data_items) ? json_encode(
                        $script_detail->data_items
                    ) : null;
                    $scriptChat->show_move_event = $script_detail->show_move_event;
                    $scriptChat->event_app = $script_detail->event_app;
                    $scriptChat->is_function = 'active_result_script_products_filter';
                    $scriptChat->language_default = $_locale;
                    $scriptChat->vsession = $this->vsession;
                    $scriptChat->id_event_app = $script_detail->id_event_app;
                    $scriptChat->option_filter = json_encode($data_product);
                    $scriptChat->id_product = $data_product->id ?? 0;
                    $scriptChat->save();

                    $scriptChat->products = $data_product;
                    if(!empty($scriptChat->id)) {
                        $data_product_translation = $this->get_info_product(true, ($ChatLaster->id_chat_parent ?? 0), $data_product->id);
                        $this->get_script_detail_translation($id_script, $script_detail->id, $scriptChat->id, [
                            'json_item' => $script_detail->json_item ?? null,
                            'option_filter' => $data_product_translation ?? null,
                        ]);
                    }
                }
                else {
                    $scriptChat = ScriptChat::find($existingChat->id);
                    if(empty($scriptChat->option_filter)) {
                        $scriptChat->id_product = $data_product->id ?? 0;
                        $scriptChat->option_filter = json_encode($data_product);
                        $scriptChat->save();
                        $scriptChat->products = $data_product;
                        if(!empty($scriptChat->id)) {
                            $data_product_translation = $this->get_info_product(true, ($ChatLaster->id_chat_parent ?? 0), $data_product->id);
                            $this->get_script_detail_translation($id_script, $script_detail->id, $scriptChat->id, [
                                'json_item' => $script_detail->json_item ?? null,
                                'option_filter' => $data_product_translation ?? null,
                            ], 'edit');
                        }
                    }
                }
                $scriptChat->event_app = $script_detail->event_app;
                $scriptChat->event_show = $script_detail->event_show;
                DB::commit();
                if (!empty($script_detail->data_items)) {
                    $scriptChat->data_items = !empty($script_detail->data_items) ? $script_detail->data_items : [];
                }


                $is_next = ScriptDetail::where('tbl_script_detail.id_script', $id_script)
                    ->where('tbl_script_detail.id_parent', $id_detail)->first();

                $scriptChat->seconds_to_wait = $script_detail->seconds_to_wait ?? 2;
                if($script_detail->end_to_web && !empty($this->isweb)) {
                    $is_next = false;
                }
                return response()->json([
                    'result' => true,
                    'show_end_script' => $this->ktEndScriptChat(),
                    'data' => $scriptChat,
                    'next' => !empty($is_next) ? url(
                        'api/script/active_script/' . $id_script . '/' . $is_next->id . '/' . $id_items.'?_locale='.$_locale
                    ) : false
                ]);
            }
            catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'result' => false,
                    'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
                ]);
            }
        }
        return response()->json([
            'result' => false
        ]);
    }

    private function get_info_product($fullStran = false, $id_chat = 0, $id_product = 0) {
        $_locale = $this->_locale;
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $id_client = !empty($this->request->client->id) ? $this->request->client->id : 0;
        $vsession = $this->vsession;

        $dataFilter = DB::table('tbl_chat_products_filter')
            ->join('tbl_tag_products_filter', 'tbl_tag_products_filter.id_product_filter', '=', 'tbl_chat_products_filter.id_product_filter')
            ->where(function ($q) use ($id_client, $vsession, $id_chat) {
                if(!empty($id_client)) {
                    $q->where('tbl_chat_products_filter.id_client', $id_client);
                }
                else {
                    $q->where('tbl_chat_products_filter.vsession', $vsession);
                }
                if(!empty($id_chat)) {
                    $q->where('tbl_chat_products_filter.id_chat', $id_chat);
                }
            })
            ->select('tbl_tag_products_filter.id_product', 'tbl_chat_products_filter.id')
            ->orderBy('tbl_chat_products_filter.id', 'asc')
            ->get();

//        $ids = [];
        $scores = [];
        $scoreMax = count($dataFilter);
        $score = $scoreMax;

        foreach ($dataFilter as $item) {
//            $ids[] = $item->id_product;
            $scores[$item->id_product] = $score; // save id_product → score
            $score--;
        }

        $orderIds = array_keys($scores);
        if (empty($orderIds)) {
            $infoProductFilter = Products::orderBy('count_join', 'desc')->first();
            $orderIds[] = $infoProductFilter->id;
//            return collect([]);
        }
        if(!empty($id_product)) {
            $orderIds = [$id_product];
        }

        $products = Products::from('tbl_products as p')
            ->whereIn('p.id', $orderIds)
            ->orderByRaw('FIELD(p.id, '.implode(',', $orderIds).')')
            ->select('p.id', 'p.code', 'p.is_use', 'p.color_header', 'p.background_color', 'p.limit_people', 'p.count_join', 'p.average_star', 'p.quantity_reviews', 'p.contribute',
                'pt.name', 'pt.content', 'pt.language', DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", p.image) as image'), 'p.slug', 'p.date_end_promotion')
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale, $fullStran) {
                $join->on('pt.id_product', '=', 'p.id');
                if(empty($fullStran)) {
                    $join->where('pt.language', '=', $_locale);
                }
            })
            ->when(true, function ($q) use ($id_client) {
                if(!empty($id_client)) {
                    $q->addSelect(
                        DB::raw('CONCAT("'.$this->baseUrlAdmin.'/", tbl_clients_sign_up_review.video_review) as video_review'), 'is_review as isSig', 'evaluate', 'tbl_clients_sign_up_review.id_review')
                        ->leftJoin('tbl_clients_sign_up_review', function ($join) use ($id_client) {
                            $join->on('tbl_clients_sign_up_review.id_product', '=', 'p.id')
                                ->where('tbl_clients_sign_up_review.id_client', '=', $id_client);
                        });
                }
                else {
                    $q->addSelect(
                        DB::raw("null as isSig")
                    );
                }
            });
        if (!empty($id_client)) {
            $listCategoryId = getCategoryNoLimit($id_client);
            $products->addSelect(DB::raw("
                IF(
                    EXISTS (
                        SELECT 1
                        FROM tbl_product_category AS c
                        WHERE c.id_product = p.id
                          AND c.id_category IN (".implode(',', $listCategoryId).")
                    ), 0, 1
                ) AS isLimit
            "));
        }
        else {
            $products->addSelect(DB::raw("0 as isLimit"));
        }


        if(!empty($fullStran)) {
            $data_products = $products->get();
            $dataResults = [];
            foreach($data_products as $key => $product) {
                $ingredients = DB::table('tbl_product_ingredients')
                    ->where('id_product', $product->id)
                    ->where('product_uses', 1)
                    ->where('language', $product->language)
                    ->orderBy('key_index', 'asc')
                    ->get();
                $product->ingredients = $ingredients;


                $product->tags = DB::table('tbl_chat_products_filter')
                    ->join('tbl_products_filter_translations', 'tbl_products_filter_translations.id_product_filter', '=', 'tbl_chat_products_filter.id_product_filter')
                    ->where(function ($q) use ($id_client, $vsession, $id_chat) {
                        if(!empty($id_client)) {
                            $q->where('tbl_chat_products_filter.id_client', $id_client);
                        }
                        else {
                            $q->where('tbl_chat_products_filter.vsession', $vsession);
                        }
                        if(!empty($id_chat)) {
                            $q->where('tbl_chat_products_filter.id_chat', $id_chat);
                        }
                    })
                    ->where('tbl_products_filter_translations.language', $product->language)
                    ->select('tbl_products_filter_translations.name')
                    ->get();

                $dataResults[$product->language] = $product;
            }
            return $dataResults;
        }
        else {
            $infoProducts = $products->first();
            if(!empty($infoProducts->id)) {
                $ingredients = DB::table('tbl_product_ingredients')->where('id_product', $infoProducts->id)->where(
                        'language',
                        $_locale
                    )->where('product_uses', 1)->orderBy('key_index', 'asc')->get();
                $infoProducts->ingredients = $ingredients;
                $infoProducts->tags = DB::table('tbl_chat_products_filter')->join(
                        'tbl_products_filter_translations',
                        'tbl_products_filter_translations.id_product_filter',
                        '=',
                        'tbl_chat_products_filter.id_product_filter'
                    )->where(function ($q) use ($id_client, $vsession, $id_chat) {
                        if (!empty($id_client)) {
                            $q->where('tbl_chat_products_filter.id_client', $id_client);
                        } else {
                            $q->where('tbl_chat_products_filter.vsession', $vsession);
                        }
                        if(!empty($id_chat)) {
                            $q->where('tbl_chat_products_filter.id_chat', $id_chat);
                        }
                    })->where('tbl_products_filter_translations.language', $_locale)->select(
                        'tbl_products_filter_translations.name'
                    )->get();
            }
            return $infoProducts;
        }
    }


    //thêm tin nhắn dịch chi tiết kịch bản có tranlition
    private function get_script_detail_translation($id_script = '', $id_script_detail = '', $id_script_chat, $dataChat = [], $event = 'add') {
        $script_detail_trans = ScriptDetail::select('tbl_script_detail.*','st.content', 'st.language')
            ->LeftJoin('tbl_script_detail_translations as st', 'tbl_script_detail.id', '=', 'st.id_script_detail')
            ->where('tbl_script_detail.id_script', $id_script)
            ->where('tbl_script_detail.id', $id_script_detail)
            ->get();
        if($script_detail_trans) {
            foreach($script_detail_trans as $key => $script_detail) {
                $json_item = $dataChat['json_item'][$script_detail->language] ?? NULL;
                $option_filter = $dataChat['option_filter'][$script_detail->language] ?? NULL;
                $message = $dataChat['message'][$script_detail->language] ?? NULL;
                if(is_array($message)) {
                    $message = implode(', ', $message);
                }

                if(empty($message)) {
                    $message = $script_detail->content ?? null;
                }
                $scriptChatTranslation = NULL;
                if(!empty($event) && $event == 'edit') {
                    $scriptChatTranslation = ScriptChatTranslations::where('id_script_chat', $id_script_chat)
                        ->where('language', $script_detail->language)
                        ->first();
                }

                if(empty($scriptChatTranslation->id)) {
                    $scriptChatTranslation = new ScriptChatTranslations();
                    $scriptChatTranslation->id_script_chat = $id_script_chat;
                }

                $scriptChatTranslation->language = $script_detail->language ?? 'vi';
                if(!empty($message)) {
                    $scriptChatTranslation->message = $message;
                }
                $scriptChatTranslation->json_item = !empty($json_item) ? json_encode($json_item) : null;
                $scriptChatTranslation->option_filter = !empty($option_filter) ? json_encode($option_filter) : null;
                $scriptChatTranslation->save();
            }
        }
        return true;
    }


}
