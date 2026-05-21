<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ChallengeMeResources;
use App\Models\ChallengeMeFile;
use App\Models\ChallengeMeSubmissions;
use App\Models\Clients;
use App\Models\Challenge;
use App\Models\ChallengeMe;
use App\Models\ChallengeContribute;

// use App\Models\ChallengeMeItem;
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
use App\Helpers\SocketHelpers;
use Carbon\Carbon;

class ChallengeMeController extends AuthController
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
        $customer_search = $this->request->input('customer_search') ?? 0;
        $date_search = $this->request->input('date_search') ?? null;
        $status_search = $this->request->input('status_search');
        $event_articles_search = $this->request->input('event_articles_search');
        if (!is_numeric($status_search)) {
            $status_search = -1;
        }
        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0], true);
            $end_date = to_sql_date($date_search[1], false) . ' 23:59:59';
        } else {
            $start_date = null;
            $end_date = null;
        }
        $query = ChallengeMe::with([
            'customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            },
            'challenge' => function ($q) {
                $q->select('id', 'name', 'type', 'days');
            }
        ])->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%$search%");
                $q->orWhere('date', 'like', "%$search%");
                $q->orWhereHas('customer', function ($instance) use ($search) {
                    $instance->where('fullname', 'like', "%$search%");
                    $instance->orWhere('phone', 'like', "%$search%");
                });
            });
        }
        if ($status_search != -1) {
            if ($status_search == 1) {
                $query->whereIn('status', [
                    Config::get('constant')['status_complete_challenge']
                ]);
            } else {
                if ($status_search == 2) {
                    $query->where(function ($q) use ($status_search) {
                        $q->whereIn('status', [0, 2])->WhereRaw('date_challenge < NOW()');
                    });
                } else {
                    $query->where(function ($q) use ($status_search) {
                        $q->where('status', 0)->WhereRaw('date_challenge >= NOW()');
                    });
                }
            }
        }
        if (!empty($customer_search)) {
            $query->where('tbl_challenge_me.client_id', $customer_search);
        }
        if (!empty($event_articles_search)) {
            $query->where(function ($q) use ($event_articles_search) {
                $q->WhereHas('challenge', function ($instance) use ($event_articles_search) {
                    $instance->where('tbl_challenge.id_event_articles', $event_articles_search);
                });
            });
        }
        if (!empty($date_search)) {
            $query->whereBetween('tbl_challenge_me.date', [$start_date, $end_date]);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtCustomer = $value->customer ?? null;
                if (!empty($dtCustomer)) {
                    $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL') . '/' . $dtCustomer->avatar : null;
                    $data[$key]['customer']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]->customer = null;
                }
            }
        }
        $total = ChallengeMe::count();
        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListDataDetail()
    {
        $id = $this->request->input('id') ?? 0;
        $client_id = $this->request->client->id ?? 0;
        if (empty($id)) {
            return response()->json([
                'result' => false,
                'message' => 'Thiếu id'
            ], 422);
        }
        $challenge = ChallengeMe::with([
            'customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            },
            'challenge' => function ($q) {
                $q->select('id', 'name', 'type', 'days');
            }
        ])->find($id);
        if (empty($challenge)) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy thử thách'
            ], 404);
        }
        // helper để chuyển đường dẫn lưu thành URL đầy đủ
        $makeUrl = function ($path) {
            if (empty($path)) {
                return null;
            }
            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }
            $storage = env('STORAGE_URL') ? : config('app.url');
            return rtrim($storage, '/') . '/' . ltrim($path, '/');
        };
        // tính days_left từ date_challenge nếu có
        $diff = null;
        if (!empty($challenge->date_challenge)) {
            try {
                $end = new \DateTime($challenge->date_challenge);
                $now = new \DateTime(date('Y-m-d'));
                $diff = (int)$now->diff($end)->format('%r%a');
            } catch (\Exception $e) {
                $diff = null;
            }
        }
        $files = [];
        $submissions = [];
        // nếu có bảng submissions/files (mô hình mới) -> lấy theo submission
        try {
            if (DB::getSchemaBuilder()->hasTable('challenge_me_submissions')) {
                $rows = DB::table('challenge_me_submissions')->where('challenge_me_id', $challenge->id)->orderBy(
                        'created_at',
                        'desc'
                    )->get();
                foreach ($rows as $row) {
                    $s = [
                        'id' => $row->id,
                        'created_by' => $row->created_by,
                        'content' => $row->content,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'files' => []
                    ];
                    // lấy file liên quan nếu bảng tồn tại
                    if (DB::getSchemaBuilder()->hasTable('challenge_me_files')) {
                        $frows = DB::table('challenge_me_files')->where('submission_id', $row->id)->orderBy(
                                'created_at',
                                'asc'
                            )->get();
                        foreach ($frows as $f) {
                            $s['files'][] = [
                                'id' => $f->id,
                                'type' => $f->type,
                                'file_url' => $makeUrl($f->file_url),
                                'filename' => $f->filename,
                                'mime' => $f->mime,
                                'size' => $f->size,
                                'created_at' => $f->created_at
                            ];
                        }
                    }
                    $submissions[] = $s;
                }
                // nếu không có submissions nhưng có files đơn lẻ, load files chung
                if (empty($submissions) && DB::getSchemaBuilder()->hasTable('challenge_me_files')) {
                    $frows = DB::table('challenge_me_files')->where('challenge_me_id', $challenge->id)->orderBy(
                            'created_at',
                            'desc'
                        )->get();
                    foreach ($frows as $f) {
                        $files[] = [
                            'id' => $f->id,
                            'type' => $f->type,
                            'file_url' => $makeUrl($f->file_url),
                            'filename' => $f->filename,
                            'mime' => $f->mime,
                            'size' => $f->size,
                            'created_at' => $f->created_at
                        ];
                    }
                }
            } else {
                // fallback: sử dụng relation ChallengeMe_item nếu có (cũ)
                if (method_exists($challenge, 'ChallengeMe_item')) {
                    $items = $challenge->ChallengeMe_item()->orderBy('created_at', 'desc')->get();
                    foreach ($items as $it) {
                        // một item có thể coi là một file / một submission tùy cấu trúc
                        $files[] = [
                            'id' => $it->id ?? null,
                            'type' => $it->type ?? ($it->file_type ?? 'image'),
                            'file_url' => $makeUrl($it->file_url ?? $it->image ?? null),
                            'content' => $it->content ?? null,
                            'created_by' => $it->created_by ?? null,
                            'created_at' => $it->created_at ?? null
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // nếu có lỗi khi truy vấn bảng mới thì fallback thầm lặng sang relation cũ
            if (method_exists($challenge, 'ChallengeMe_item')) {
                $items = $challenge->ChallengeMe_item()->orderBy('created_at', 'desc')->get();
                foreach ($items as $it) {
                    $files[] = [
                        'id' => $it->id ?? null,
                        'type' => $it->type ?? ($it->file_type ?? 'image'),
                        'file_url' => $makeUrl($it->file_url ?? $it->image ?? null),
                        'content' => $it->content ?? null,
                        'created_by' => $it->created_by ?? null,
                        'created_at' => $it->created_at ?? null
                    ];
                }
            }
        }
        // thêm avatar khách hàng đầy đủ url
        if (!empty($challenge->customer) && !empty($challenge->customer->avatar)) {
            $challenge->customer->avatar = $makeUrl($challenge->customer->avatar);
        } elseif (!empty($challenge->customer)) {
            $challenge->customer->avatar = null;
        }
        return response()->json([
            'result' => true,
            'message' => 'Lấy chi tiết thành công',
            'data' => [
                'challenge' => new ChallengeMeResources($challenge),
                'submissions' => $submissions,
                'files' => $files,
                'days_left' => $diff
            ]
        ]);
    }

    public function countAll()
    {
        $customer_search = $this->request->input('customer_search');
        $date_search = $this->request->input('date_search');
        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0], true);
            $end_date = to_sql_date($date_search[1], false) . ' 23:59:59';
        }
        $now = Carbon::now();
        /**
         * Base filter (chung cho tất cả)
         */
        $baseQuery = ChallengeMe::where('id', '!=', 0);
        if (!empty($customer_search)) {
            $baseQuery->where('client_id', $customer_search);
        }
        if (!empty($date_search)) {
            $baseQuery->whereBetween('date', [$start_date, $end_date]);
        }
        /**
         * Tổng số (tất cả trạng thái)
         */
        $all = (clone $baseQuery)->count();
        /**
         * Đếm 3 trạng thái theo đúng nghiệp vụ
         */
        $counts = [
            // 0 - Đang diễn ra
            0 => (clone $baseQuery)->where(function ($q) use ($now) {
                $q->where('status', 0)->where('date_challenge', '>=', $now);
            })->count(),
            // 1 - Hoàn thành
            1 => (clone $baseQuery)->whereIn(
                'status',
                [Config::get('constant')['status_complete_challenge']]
            )->count(),
            // 2 - Hết hạn
            2 => (clone $baseQuery)->where(function ($q) use ($now) {
                $q->whereIn('status', [0, 2])->where('date_challenge', '<', $now);
            })->count(),
        ];
        /**
         * Format trả về cho frontend
         */
        $arr = [
            ['status' => 0, 'count' => $counts[0]],
            ['status' => 1, 'count' => $counts[1]],
            ['status' => 2, 'count' => $counts[2]],
        ];
        return response()->json([
            'all' => $all,
            'arr' => $arr,
            'result' => true,
            'message' => 'Thành công',
        ]);
    }

    public function getDetail()
    {
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (!empty($client)) {
            $dtImage = !empty($client->avatar) ? env('STORAGE_URL') . '/' . $client->avatar : null;
            $client->avatar = $dtImage;
        }
        $data['result'] = true;
        $data['client'] = $client;
        $data['message'] = 'Lấy thông tin khách hàng thành công';
        return response()->json($data);
    }

    public function delete()
    {
        $id = $this->request->input('id') ?? 0;
        $dtData = ChallengeMe::find($id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = lang('c_not_find_challenge_me');
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if ($dtData->point > 0 && $dtData->refund_point == 0) {
                changePoint($dtData->id, 'ChallengeMe_point_refund', $this->request->input('staff_status') ?? 0);
            }
            $dtData->delete();
            $challengeFile = ChallengeMeFile::where('challenge_me_id', $id)->get();
            foreach ($challengeFile as $key => $value) {
                // xóa file trên storage
                if (!empty($value->file_url) && Storage::disk('public')->exists($value->file_url)) {
                    Storage::disk('public')->delete($value->file_url);
                }
                $value->delete();
            }
            ChallengeMeSubmissions::where('challenge_me_id', $id)->delete();
            //            $dtData->ChallengeMe_item()->delete();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData()
    {
        $current_page = 1;
        $per_page = 10;
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $customer_id = $this->request->client->id ?? 0;
        $status_search = $this->request->input('status_search');
        $query = ChallengeMe::with([
            'customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            },
            'challenge' => function ($q) {
                $q->select('id', 'name', 'type', 'days');
            }
        ])->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%$search%");
            });
        }
        $query->where(function ($q) use ($status_search, $customer_id) {
            // if ($status_search != -1) {
            //     $status_search = is_array($status_search) ? $status_search : [$status_search];
            //     $q->whereIn('status', $status_search);
            // }
            if (!empty($customer_search)) {
                $q->where('client_id', $customer_search);
            }
            $q->where('client_id', $customer_id);
        });
        $query->orderByRaw("id desc");
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);
        $collection = ChallengeMeResources::collection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function authentic_challenge()
    {
        $input = $this->request->all();
        $client_id = $this->request->client->id ?? 0;
        if (empty($client_id)) {
            return response()->json([
                'title' => lang('notification'),
                'result' => false,
                'message' => lang('dt_login_use_app')
            ], 401);
        }
        $validator = Validator::make($input, [
            'id' => 'required|integer',
            'content' => 'nullable|string',
            // changed: completion_rate comes from server (count of evidences), not client input
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,bmp,webp,svg,heic|max:51200',
            'video' => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm,video/avi,video/mpeg,video/3gpp,video/ogg|max:51200',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'title' => lang('notification'),
                'result' => false,
                'message' => lang('dt_invalid_data'),
                'errors' => $validator->errors()
            ], 422);
        }
        $ChallengeMe = ChallengeMe::find($input['id']);
        if (empty($ChallengeMe)) {
            return response()->json([
                'title' => lang('notification'),
                'result' => false,
                'message' => lang('dt_not_found_challenge')
            ], 404);
        }
        $challenge = Challenge::find($ChallengeMe->id_challenge);
        if (empty($challenge)) {
            return response()->json([
                'title' => lang('notification'),
                'result' => false,
                'message' => lang('dt_not_found_challenge')
            ], 404);
        }
        // --- ADDED: kiểm tra nếu thử thách đã hoàn thành thì không cho xác thực thêm
        $radio_challenge_success = $this->AdminService->get_option('radio_challenge_success'); // số % tính là hoàn thành
        $status_complete = Config::get('constant')['status_complete_challenge'] ?? null;
        $currentCompletion = (float)($ChallengeMe->completion_rate ?? 0);
        if (($status_complete !== null && $ChallengeMe->status == $status_complete) || $currentCompletion >= 100) {
            return response()->json([
                'title' => lang('notification'),
                'result' => false,
                'message' => lang('dt_challenge_already_completed')
            ], 200);
        }
        // --- END ADDED
        // Kiểm tra quyền: chỉ chủ sở hữu mới được xác thực
        $ownerId = $ChallengeMe->client_id ?? $ChallengeMe->customer_id ?? 0;
        if ($ownerId != $client_id) {
            return response()->json([
                'title' => lang('notification'),
                'result' => false,
                'message' => lang('dt_no_permission_action_challenge')
            ], 403);
        }
        // kiểm tra đã xác thực thử thách trong ngày hay chưa
        $today = date('Y-m-d');
        $alreadyToday = false;
        try {
            if (DB::getSchemaBuilder()->hasTable('challenge_me_submissions')) {
                $alreadyToday = DB::table('challenge_me_submissions')->where(
                        'challenge_me_id',
                        $ChallengeMe->id
                    )->where('created_by', $client_id)->whereDate('created_at', $today)->exists();
            } elseif (method_exists($ChallengeMe, 'ChallengeMe_item')) {
                $alreadyToday = (bool)$ChallengeMe->ChallengeMe_item()->where('created_by', $client_id)->whereDate(
                        'created_at',
                        $today
                    )->exists();
            }
        } catch (\Exception $e) {
            // nếu có lỗi khi kiểm tra thì bỏ qua và cho phép tiếp tục
            $alreadyToday = false;
        }
        if ($alreadyToday) {
            return response()->json([
                'title' => lang('notification'),
                'result' => false,
                'message' => lang('dt_challenge_already_authenticated_today')
            ], 200);
        }
        // Kiểm tra ngày còn lại (date_challenge lưu dạng Y-m-d theo addChallenge)
        if (!empty($ChallengeMe->date_challenge)) {
            try {
                $end = new \DateTime($ChallengeMe->date_challenge);
                $now = new \DateTime(date('Y-m-d'));
                $diff = (int)$now->diff($end)->format('%r%a');
                if ($diff < 0) {
                    return response()->json([
                        'title' => lang('notification'),
                        'result' => false,
                        'message' => lang('dt_challenge_expired_cannot_authenticate')
                    ], 400);
                }
            } catch (\Exception $e) {
                // nếu date_challenge không hợp lệ thì bỏ qua kiểm tra
                $diff = null;
            }
        } else {
            $diff = null;
        }
        // NEW: use fixed required attempts
        $requiredAttempts = $challenge->quantity_verification ?? 0;
        // Count existing evidence items via relation (existing in project)
        $currentAttempts = 0;
        if (method_exists($challenge, 'ChallengeMe_item')) {
            try {
                $currentAttempts = (int)$ChallengeMe->ChallengeMe_item()->count();
            } catch (\Exception $e) {
                $currentAttempts = 0;
            }
        }
        // If already reached required attempts, block further submissions
        if ($currentAttempts >= $requiredAttempts || $currentCompletion >= 100) {
            return response()->json([
                'title' => lang('notification'),
                'result' => false,
                'message' => lang('dt_challenge_already_completed')
            ], 400);
        }
        // Lưu file lên storage và thu thập url trả về
        $stored = [
            'images' => [],
            'video' => null
        ];
        try {
            $basePath = "challenges/{$ChallengeMe->id}";
            $seenOriginalNames = [];
            if ($this->request->hasFile('images')) {
                foreach ($this->request->file('images') as $file) {
                    if (!$file->isValid()) {
                        continue;
                    }
                    // original client filename dùng để so sánh trùng
                    $originalName = $file->getClientOriginalName();
                    // kiểm tra trùng trong cùng request
                    if (in_array($originalName, $seenOriginalNames, true)) {
                        return response()->json([
                            'title' => lang('notification'),
                            'result' => false,
                            'message' => "Tệp \"$originalName\" đã được chọn trong yêu cầu. Vui lòng đổi tên file."
                        ], 409);
                    }
                    // kiểm tra trùng trong DB (schema mới) -> so sánh filename (tên gốc) hoặc file_url chứa tên gốc (fallback)
                    if (DB::getSchemaBuilder()->hasTable('challenge_me_files')) {
                        $exists = DB::table('challenge_me_files')->where('challenge_me_id', $ChallengeMe->id)->where(
                                function ($q) use ($originalName) {
                                    $q->where('filename', $originalName)->orWhere(
                                            'file_url',
                                            'like',
                                            '%' . $originalName . '%'
                                        );
                                }
                            )->exists();
                        if ($exists) {
                            return response()->json([
                                'title' => lang('notification'),
                                'result' => false,
                                'message' => "Tệp \"$originalName\" đã được tải lên trước đó. Không thể xác thực thử thách với file trùng tên."
                            ], 409);
                        }
                    } elseif (method_exists($ChallengeMe, 'ChallengeMe_item')) {
                        // fallback cho schema cũ: so sánh filename hoặc file_url chứa tên gốc
                        $exists = $ChallengeMe->ChallengeMe_item()->where(function ($q) use ($originalName) {
                                $q->where('filename', $originalName)->orWhere(
                                        'file_url',
                                        'like',
                                        '%' . $originalName . '%'
                                    );
                            })->exists();
                        if ($exists) {
                            return response()->json([
                                'title' => lang('notification'),
                                'result' => false,
                                'message' => "Tệp \"$originalName\" đã được tải lên trước đó. Không thể xác thực thử thách với file trùng tên."
                            ], 409);
                        }
                    }
                    // đánh dấu đã thấy tên này trong request hiện tại
                    $seenOriginalNames[] = $originalName;
                    $generatedFilename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs($basePath . '/images', $generatedFilename, 'public');
                    // lưu đường dẫn kèm tên gốc để insert sau
                    $stored['images'][] = [
                        'path' => $path,
                        'original' => $originalName,
                        'stored' => $generatedFilename
                    ];
                }
            }
            if ($this->request->hasFile('video')) {
                $file = $this->request->file('video');
                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    // kiểm tra trùng tương tự images
                    if (in_array($originalName, $seenOriginalNames, true)) {
                        return response()->json([
                            'title' => lang('notification'),
                            'result' => false,
                            'message' => "Tệp \"$originalName\" đã được chọn trong yêu cầu. Vui lòng đổi tên file."
                        ], 409);
                    }
                    if (DB::getSchemaBuilder()->hasTable('challenge_me_files')) {
                        $exists = DB::table('challenge_me_files')->where('challenge_me_id', $ChallengeMe->id)->where(
                                function ($q) use ($originalName) {
                                    $q->where('filename', $originalName)->orWhere(
                                            'file_url',
                                            'like',
                                            '%' . $originalName . '%'
                                        );
                                }
                            )->exists();
                        if ($exists) {
                            return response()->json([
                                'title' => lang('notification'),
                                'result' => false,
                                'message' => "Tệp \"$originalName\" đã được tải lên trước đó. Không thể xác thực thử thách với file trùng tên."
                            ], 409);
                        }
                    } elseif (method_exists($ChallengeMe, 'ChallengeMe_item')) {
                        $exists = $ChallengeMe->ChallengeMe_item()->where(function ($q) use ($originalName) {
                                $q->where('filename', $originalName)->orWhere(
                                        'file_url',
                                        'like',
                                        '%' . $originalName . '%'
                                    );
                            })->exists();
                        if ($exists) {
                            return response()->json([
                                'title' => lang('notification'),
                                'result' => false,
                                'message' => "Tệp \"$originalName\" đã được tải lên trước đó. Không thể xác thực thử thách với file trùng tên."
                            ], 409);
                        }
                    }
                    $seenOriginalNames[] = $originalName;
                    $generatedFilename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs($basePath . '/videos', $generatedFilename, 'public');
                    $stored['video'] = [
                        'path' => $path,
                        'original' => $originalName,
                        'stored' => $generatedFilename
                    ];
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'title' => lang('notification'),
                'result' => false,
                'message' => 'Lưu file thất bại: ' . $e->getMessage()
            ], 500);
        }
        // --- NEW: tạo record evidence submission và tạo từng file trong challenge_me_files
        $submissionId = null;
        try {

            $exchange_point_now = $this->AdminService->get_option('exchange_rate_haru_wallet') ?? 1;
            // tạo một record tổng (mỗi lần người dùng submit)
            $now = date('Y-m-d H:i:s');
            $submissionData = [
                'challenge_me_id' => $ChallengeMe->id,
                'created_by' => $client_id,
                'haru_xu' => $challenge->coin_success ?? 0,
                'content' => $input['content'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
                'exchange_point_now' => $exchange_point_now,
            ];

            // insert submission and get id
            $submissionId = DB::table('challenge_me_submissions')->insertGetId($submissionData);
            // insert images each as a file row
            if (!empty($stored['images'])) {
                foreach ($stored['images'] as $img) {
                    try {
                        DB::table('challenge_me_files')->insert([
                            'submission_id' => $submissionId,
                            'challenge_me_id' => $ChallengeMe->id,
                            'type' => 'image',
                            'file_url' => $img['path'],
                            'filename' => $img['original'], // store original filename for duplicate checks
                            'mime' => null,
                            'size' => null,
                            'created_by' => $client_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    } catch (\Exception $e) {
                        // ignore individual file insert errors
                    }
                }
            }
            if (!empty($stored['video'])) {
                try {
                    DB::table('challenge_me_files')->insert([
                        'submission_id' => $submissionId,
                        'challenge_me_id' => $ChallengeMe->id,
                        'type' => 'video',
                        'file_url' => $stored['video']['path'],
                        'filename' => $stored['video']['original'], // store original filename
                        'mime' => null,
                        'size' => null,
                        'created_by' => $client_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } catch (\Exception $e) {
                    // ignore video insert error
                }
            }
        } catch (\Exception $e) {
            // If insertion to the new tables fails (tables not exist), fall back to existing relation if present
            $submissionId = null;
            if (method_exists($challenge, 'ChallengeMe_item')) {
                // images
                if (!empty($stored['images'])) {
                    foreach ($stored['images'] as $imgUrl) {
                        try {
                            $ChallengeMe->ChallengeMe_item()->create([
                                'type' => 'image',
                                'file_url' => $imgUrl,
                                'content' => $input['content'] ?? null,
                                'created_by' => $client_id
                            ]);
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                }
                // video
                if (!empty($stored['video'])) {
                    try {
                        $ChallengeMe->ChallengeMe_item()->create([
                            'type' => 'video',
                            'file_url' => $stored['video'],
                            'content' => $input['content'] ?? null,
                            'created_by' => $client_id
                        ]);
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
            }
        }
        // Đếm lại số lần sau khi thêm
        try {
            // Đếm số lần xác thực dựa trên bảng challenge_me_submissions
            if (DB::getSchemaBuilder()->hasTable('challenge_me_submissions')) {
                $currentAttempts = (int)DB::table('challenge_me_submissions')->where(
                        'challenge_me_id',
                        $ChallengeMe->id
                    )->count();
            } else {
                // fallback: nếu không có bảng submissions thì giữ nguyên giá trị cũ
            }
        } catch (\Exception $e) {
            // giữ nguyên currentAttempts nếu có lỗi
        }
        // Tính lại completion_rate dựa trên số lần đã xác thực
        $newCompletion = ($currentAttempts / $requiredAttempts) * 100;
        if ($newCompletion > 100) {
            $newCompletion = 100;
        }
        $ChallengeMe->completion_rate = round($newCompletion, 2);
        // nếu đạt 100% thì gán trạng thái hoàn thành (nếu có cấu hình)
        if ($ChallengeMe->completion_rate >= $radio_challenge_success) {
            if ($status_complete !== null) {
                $ChallengeMe->status = $status_complete;
            }
            if($ChallengeMe->completion_rate > 100) {
                $ChallengeMe->completion_rate = 100;
            }
        }
        $ChallengeMe->total_haru_xu = ($ChallengeMe->total_haru_xu ?? 0) + ($challenge->coin_success ?? 0);
        if ($ChallengeMe->status == $status_complete) {
            $ChallengeMe->date_status = date('Y-m-d H:i:s');
            $ChallengeMe->total_haru_xu = ($ChallengeMe->total_haru_xu + $ChallengeMe->deposit);
        }
        $ChallengeMe->save();
        if ($ChallengeMe->status == $status_complete) {
            challenge_success($ChallengeMe->id);
            reviewClassChallenge($client_id);
        }
        if (!empty($submissionId)) {
            $dtObject = changePoint($submissionId, 'authenticated_challengeMe');
            try {
                Clients::query()->update(['new_post' => 1]);
            } catch (\Exception $e) {
                // ignore update errors
            }
            SocketHelpers::sendSocketNewPost(0, $submissionId);
        }
        // --- END NEW
        // Lưu evidence vào bảng items nếu hệ thống có quan hệ ChallengeMe_item
        // (Khuyến nghị: tùy chỉnh theo cấu trúc thực tế của project; phần này hiện chỉ trả về dữ liệu)
        $responseData = [
            'title' => lang('notification'),
            'result' => true,
            'message' => lang('dt_challenge_authenticated_success'),
            'data' => [
                'challenge' => new ChallengeMeResources($challenge),
                'files' => $stored,
                'days_left' => $diff
            ]
        ];
        return response()->json($responseData, 200);
    }

    public function addChallenge()
    {
        $input = $this->request->all();
        $customer_id = $this->request->client->id ?? 0;
        $dataPost = $this->request->input();
        if (empty($customer_id)) {
            return response()->json([
                'title' => lang('notification'),
                'data' => [],
                'result' => false,
                'message' => lang('dt_login_use_app')
            ]);
        }
        $validator = Validator::make($input, [
            'id_challenge' => 'required|integer',
            'reference_no' => 'nullable|string',
            'date' => 'nullable|string',
            'deposit' => 'nullable|numeric',
            'completion_rate' => 'nullable|numeric',
            'haru_xu' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_invalid_data'),
                'errors' => $validator->errors()
            ], 422);
        }
        // kiểm tra đã tham gia challenge này chưa
        $ktChallenge = ChallengeMe::where('client_id', $customer_id)->where(
                'id_challenge',
                $input['id_challenge']
            )->where('status', 0)->exists();
        if ($ktChallenge) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => lang('dt_already_joined_challenge')
            ], 409);
        }
        DB::beginTransaction();
        try {
            $challenge = Challenge::find($input['id_challenge']);
            $Client = Clients::find($customer_id);
            $rankCustomer = RankCommunity::find($Client->rank_community_id ?? 0);
            if (!empty($rankCustomer)) {
                if ($rankCustomer->id < $challenge->min_rank_join) {
                    return response()->json([
                        'result' => false,
                        'message' => lang('rank_you_not_join_challenge')
                    ], 400);
                }
            }
            if ($challenge->limit_join > 0) {
                if (($challenge->quantity_joined ?? 0) >= $challenge->limit_join) {
                    return response()->json([
                        'result' => false,
                        'title' => lang('notification'),
                        'message' => lang('dt_challenge_join_limit_reached')
                    ], 400);
                }
            }
            $reference_no = $this->AdminService->getOrderRef('challengme')['reference_no'];
            // tạo record chính
            $challengeMe = new ChallengeMe();
            // dùng customer_id cho nhất quán với phần còn lại của controller
            $challengeMe->client_id = $customer_id;
            $challengeMe->id_challenge = $input['id_challenge'];
            $challengeMe->reference_no = $input['reference_no'] ?? $reference_no;
            // chuẩn hoá ngày bắt đầu
            $baseDate = !empty($input['date']) ? to_sql_date($input['date'], true) : date('Y-m-d H:i:s');
            $challengeMe->date = $baseDate;
            // các trường bổ sung
            $challengeMe->status = $input['status'] ?? (Config::get('constant')['status_request'] ?? 0);
            if (isset($input['deposit'])) {
                $challengeMe->deposit = $input['deposit'];
//                $challengeMe->deposit_payment = $challengeMe->deposit;
                $challengeMe->payment_mode_id = $input['payment_mode_id'] ?? 0;
            }
            $challengeMe->completion_rate = 0;
            $challengeMe->haru_xu = $challenge->coin_success ?? 0;
            $challengeMe->total_haru_xu = 0;
            // lấy số ngày (days) từ bảng tbl_challenge để tính date_challenge
            $days = (int)DB::table('tbl_challenge')->where('id', $input['id_challenge'])->value('days') ? : 0;
            if ($days > 0) {
                $dt = new \DateTime($baseDate);
                $dt->modify("+{$days} days");
                // lưu dạng date (Y-m-d) như cấu trúc DB
                $challengeMe->date_challenge = $dt->format('Y-m-d');
            } else {
                $challengeMe->date_challenge = null;
            }
            $challengeMe->save();
            $this->AdminService->updateOrderRef('challengme');
            if (!empty($input['deposit'])) {
                DB::table('tbl_history_payment_challenge')->insert([
                    'id_challenge' => $challengeMe->id_challenge,
                    'id_client' => $customer_id,
                    'money' => $input['deposit'],
                    'payment_mode_id' => $input['payment_mode_id'] ?? 0,
                ]);
            }
            $challenge->quantity_joined = ($challenge->quantity_joined ?? 0) + 1; // cập nhật số lượng tham gias
            $challenge->save();

            $successPayment = $this->AdminService->createQRPayment([
                'code' => $challengeMe->reference_no,
                'amount' => $challengeMe->deposit,
            ],$this->_locale);
            if(empty($successPayment['qr'])) {
                DB::rollBack();
                return response()->json([
                    'result' => false,
                    'title' => lang('notification'),
                    'message' => $successPayment['message']
                ], 500);
            }

            DB::commit();
            // load lại quan hệ để trả về đầy đủ dữ liệu
            $challengeMe = ChallengeMe::with(['customer', 'challenge'])->find($challengeMe->id);

            return response()->json([
                'result' => true,
                'title' => lang('notification'),
                'message' => lang('dt_add_challenge_success'),
                'sub_message' => lang('dt_add_challenge_success_sub'),
                'data' => new ChallengeMeResources($challengeMe),
                'info_payment' => $successPayment ?? []
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // thanh toán deposit, nhưng hiệ tại chưa dùng
    public function paymentDeposit()
    {
        $input = $this->request->all();
        $customer_id = $this->request->client->id ?? 0;
        if (empty($customer_id)) {
            return response()->json([
                'title' => lang('notification'),
                'data' => [],
                'result' => false,
                'message' => lang('dt_login_use_app')
            ]);
        }
        $validator = Validator::make($input, [
            'id' => 'required|integer',
            'payment_method' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_invalid_data'),
                'errors' => $validator->errors()
            ], 422);
        }
        $challengeMe = ChallengeMe::where('id', $input['id'])->where('client_id', $customer_id)->first();
        if (empty($challengeMe)) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_not_found_challenge')
            ], 404);
        }
        if ($challengeMe->deposit_payment >= $challengeMe->deposit) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_deposit_already_paid')
            ], 400);
        }
        // Xử lý thanh toán deposit ở đây (tùy thuộc vào phương thức thanh toán)
        // ...
        // Giả sử thanh toán thành công:
        $challengeMe->deposit_payment = $challengeMe->deposit;
        $challengeMe->save();
        return response()->json([
            'result' => true,
            'message' => lang('dt_deposit_payment_success'),
            'data' => new ChallengeMeResources($challengeMe)
        ], 200);
    }

    public function edit_authentic_challenge()
    {
        $input = $this->request->all();
        $client_id = $this->request->client->id ?? 0;
        if (empty($client_id)) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_login_use_app')
            ], 401);
        }
        $validator = Validator::make($input, [
            'id' => 'required|integer', // id of submission (new schema) or item (old schema)
            'content' => 'nullable|string',
            'delete_file_ids' => 'nullable|array',
            'delete_file_ids.*' => 'integer',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,bmp,webp,svg,heic|max:51200',
            'video' => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm,video/avi,video/mpeg,video/3gpp,video/ogg|max:51200',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_invalid_data'),
                'errors' => $validator->errors()
            ], 422);
        }
        $id = (int)$input['id'];
        $now = date('Y-m-d H:i:s');
        // Try new schema (submissions + files)
        $useNewSchema = DB::getSchemaBuilder()->hasTable('challenge_me_submissions') && DB::getSchemaBuilder(
            )->hasTable('challenge_me_files');
        $challengeMe = null;
        $submission = null;
        $item = null;
        if ($useNewSchema) {
            $submission = DB::table('challenge_me_submissions')->where('id', $id)->first();
            if (empty($submission)) {
                return response()->json([
                    'result' => false,
                    'message' => lang('dt_not_found_challenge')
                ], 404);
            }
            $challengeMe = ChallengeMe::find($submission->challenge_me_id);
            if (empty($challengeMe)) {
                return response()->json([
                    'result' => false,
                    'message' => lang('dt_not_found_challenge')
                ], 404);
            }
        } else {
            // fallback to old relation: find ChallengeMe that has an item with this id
            $challengeMe = ChallengeMe::whereHas('ChallengeMe_item', function ($q) use ($id) {
                $q->where('id', $id);
            })->first();
            if (empty($challengeMe)) {
                return response()->json([
                    'result' => false,
                    'message' => lang('dt_not_found_challenge')
                ], 404);
            }
            $item = $challengeMe->ChallengeMe_item()->where('id', $id)->first();
            if (empty($item)) {
                return response()->json([
                    'result' => false,
                    'message' => lang('dt_not_found_challenge')
                ], 404);
            }
        }
        // ownership check: only owner can edit
        $ownerId = $challengeMe->client_id ?? $challengeMe->customer_id ?? 0;
        // also allow submission creator when new schema
        if ($useNewSchema) {
            if ($ownerId != $client_id && ($submission->created_by ?? 0) != $client_id) {
                return response()->json([
                    'result' => false,
                    'message' => lang('dt_no_permission_action_challenge')
                ], 403);
            }
        } else {
            if ($ownerId != $client_id && ($item->created_by ?? 0) != $client_id) {
                return response()->json([
                    'result' => false,
                    'message' => lang('dt_no_permission_action_challenge')
                ], 403);
            }
        }
        // Update content
        if (isset($input['content'])) {
            if ($useNewSchema) {
                DB::table('challenge_me_submissions')->where('id', $id)->update([
                    'content' => $input['content'],
                    'updated_at' => $now
                ]);
            } else {
                try {
                    $item->content = $input['content'];
                    $item->save();
                } catch (\Exception $e) {
                    // ignore save error here, but return failure
                    return response()->json([
                        'result' => false,
                        'message' => $e->getMessage()
                    ], 500);
                }
            }
        }
        // Handle deleting existing files (if provided)
        $deletedFiles = [];
        if (!empty($input['delete_file_ids']) && is_array($input['delete_file_ids'])) {
            foreach ($input['delete_file_ids'] as $fid) {
                if ($useNewSchema) {
                    $f = DB::table('challenge_me_files')->where('id', $fid)->first();
                    if ($f && ((int)$f->challenge_me_id === (int)$challengeMe->id)) {
                        // delete storage file if present
                        if (!empty($f->file_url)) {
                            $path = parse_url($f->file_url, PHP_URL_PATH) ? : $f->file_url;
                            $path = ltrim($path, '/');
                            try {
                                Storage::disk('public')->delete($path);
                            } catch (\Exception $e) {
                                // ignore storage deletion errors
                            }
                        }
                        DB::table('challenge_me_files')->where('id', $fid)->delete();
                        $deletedFiles[] = $fid;
                    }
                } else {
                    // old schema: delete item if belongs to challengeMe and is a file-type row
                    $itm = $challengeMe->ChallengeMe_item()->where('id', $fid)->first();
                    if ($itm) {
                        if (!empty($itm->file_url)) {
                            $path = parse_url($itm->file_url, PHP_URL_PATH) ? : $itm->file_url;
                            $path = ltrim($path, '/');
                            try {
                                Storage::disk('public')->delete($path);
                            } catch (\Exception $e) {
                            }
                        }
                        try {
                            $challengeMe->ChallengeMe_item()->where('id', $fid)->delete();
                            $deletedFiles[] = $fid;
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                }
            }
        }
        // Handle new uploads (add images and/or a video)
        $addedFiles = [
            'images' => [],
            'video' => null
        ];
        try {
            $basePath = "challenges/{$challengeMe->id}";
            if ($this->request->hasFile('images')) {
                foreach ($this->request->file('images') as $file) {
                    if (!$file->isValid()) {
                        continue;
                    }
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs($basePath . '/images', $filename, 'public');
                    if ($useNewSchema) {
                        DB::table('challenge_me_files')->insert([
                            'submission_id' => $useNewSchema ? $submission->id : null,
                            'challenge_me_id' => $challengeMe->id,
                            'type' => 'image',
                            'file_url' => $path,
                            'filename' => basename($filename),
                            'mime' => $file->getClientMimeType(),
                            'size' => $file->getSize(),
                            'created_by' => $client_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    } else {
                        try {
                            $challengeMe->ChallengeMe_item()->create([
                                'type' => 'image',
                                'file_url' => $path,
                                'content' => $input['content'] ?? null,
                                'created_by' => $client_id
                            ]);
                        } catch (\Exception $e) {
                            // ignore individual create errors
                        }
                    }
                    $addedFiles['images'][] = $path;
                }
            }
            if ($this->request->hasFile('video')) {
                $file = $this->request->file('video');
                if ($file->isValid()) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs($basePath . '/videos', $filename, 'public');
                    if ($useNewSchema) {
                        DB::table('challenge_me_files')->insert([
                            'submission_id' => $useNewSchema ? $submission->id : null,
                            'challenge_me_id' => $challengeMe->id,
                            'type' => 'video',
                            'file_url' => $path,
                            'filename' => basename($filename),
                            'mime' => $file->getClientMimeType(),
                            'size' => $file->getSize(),
                            'created_by' => $client_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    } else {
                        try {
                            $challengeMe->ChallengeMe_item()->create([
                                'type' => 'video',
                                'file_url' => $path,
                                'content' => $input['content'] ?? null,
                                'created_by' => $client_id
                            ]);
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                    $addedFiles['video'] = $path;
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Lưu file thất bại: ' . $e->getMessage()
            ], 500);
        }
        // Prepare response: return updated submission/item with current files
        $files = [];
        if ($useNewSchema) {
            $files = DB::table('challenge_me_files')->where('challenge_me_id', $challengeMe->id)->where(
                    function ($q) use ($submission) {
                        // include files for this submission and also files that are attached to the challenge_me
                        $q->where('submission_id', $submission->id)->orWhereNull('submission_id');
                    }
                )->orderBy('created_at', 'asc')->get()->map(function ($f) {
                    $path = $f->file_url;
                    $url = preg_match('#^https?://#i', $path)
                        ? $path
                        : rtrim(
                            env('STORAGE_URL') ? : config('app.url'),
                            '/'
                        ) . '/' . ltrim($path, '/');
                    return [
                        'id' => $f->id,
                        'type' => $f->type,
                        'file_url' => $url,
                        'filename' => $f->filename,
                        'mime' => $f->mime,
                        'size' => $f->size,
                        'created_at' => $f->created_at
                    ];
                })->toArray();
        } else {
            $items = $challengeMe->ChallengeMe_item()->orderBy('created_at', 'asc')->get();
            foreach ($items as $it) {
                $path = $it->file_url ?? $it->image ?? null;
                $url = preg_match('#^https?://#i', $path)
                    ? $path
                    : (empty($path)
                        ? null
                        : (rtrim(
                                env('STORAGE_URL') ? : config('app.url'),
                                '/'
                            ) . '/' . ltrim($path, '/')));
                $files[] = [
                    'id' => $it->id ?? null,
                    'type' => $it->type ?? ($it->file_type ?? 'image'),
                    'file_url' => $url,
                    'content' => $it->content ?? null,
                    'created_by' => $it->created_by ?? null,
                    'created_at' => $it->created_at ?? null
                ];
            }
        }
        return response()->json([
            'result' => true,
            'message' => lang('dt_challenge_authenticated_success'),
            'data' => [
                'submission_id' => $id,
                'deleted_file_ids' => $deletedFiles,
                'added_files' => $addedFiles,
                'files' => $files,
                'content' => $input['content'] ?? null
            ]
        ], 200);
    }


    //nhắc nhở thử thách
    public function remind_challenge()
    {
        $hour = now()->hour; // 0..23
        if ($hour < 10 || $hour > 20) {
            return response()->json([
                'result' => true,
                'message' => lang('Ngoài khung giờ nhắc nhở thử thách')
            ], 200);
        }
        $today = now()->toDateString();
        $todayStart = now()->startOfDay();
        $listClientIds = ChallengeMe::query()
            ->from('tbl_challenge_me as cm')
            ->join('tbl_challenge as c', 'c.id', '=', 'cm.id_challenge')
            ->leftJoin('tbl_clients as cl', 'cl.id', '=', 'cm.client_id')
            ->where(function ($q) use ($today) {
                $q->whereNull('cm.date_reminded')
                    ->orWhere('cm.date_reminded', '<', $today);
            })
            ->where('cm.status', 0)
            ->whereNotNull('cm.date_challenge')
            ->whereDate('cm.date_challenge', '<', $today)
            ->where('cm.created_at', '>', $todayStart)
            ->select('cm.client_id')
            ->distinct()
            ->limit(10)
            ->get();

        $AdminService = new \App\Services\AdminService();
        $listLang = $AdminService->GetUrlSetings('api/getLanguageCurrent');
        $dataNotify = [];
        foreach ($listLang['data'] as $key => $value) {
            $dataNotify[$value['code']] = [
                'title' => lang('dt_challenge_reminder_title_10h', 'message', [], $value['code']),
                'content' => lang('dt_challenge_reminder_content', 'message', [], $value['code']),
            ];
        }
        $list_client = [];
        foreach ($listClientIds as $valClient) {
            $list_client[] = $valClient->client_id;
            // cập nhật lại ngày nhắc nhở
            ChallengeMe::where('client_id', $valClient->client_id)->update([
                'date_reminded' => date('Y-m-d')
            ]);
        }
        if(!empty($list_client)) {
            $dtData = [
                ['arrLang' => $dataNotify]
            ];
            notificationCusMutil('remind', $list_client, $dtData);
        }
        return response()->json([
            'result' => true,
            'message' => lang('nhắc nhở thành công ') . count($list_client). ' khách'
        ], 200);
    }

    //đổi trạng thái những chiến dịch đã hết hạn
    public function cron_challenge_expired() {
        $listChallengeMe = ChallengeMe::select('tbl_challenge_me.*', 'tbl_clients.lang_default', 'tbl_challenge.id_event_articles')
            ->where('tbl_challenge_me.date_challenge', '<', date('Y-m-d'))
            ->where('tbl_challenge_me.status', '=', 0)
            ->where('tbl_challenge_me.completion_rate', '<=', 80)
            ->join('tbl_clients', 'tbl_clients.id', '=', 'tbl_challenge_me.client_id')
            ->join('tbl_challenge', 'tbl_challenge.id', '=', 'tbl_challenge_me.id_challenge')
            ->limit(5)
            ->get();
        foreach ($listChallengeMe as $challengeMe) {
            $dataNotify = [];
            $AdminService = new \App\Services\AdminService();
            $listLang = $AdminService->GetUrlSetings('api/getLanguageCurrent');
            foreach ($listLang['data'] as $key => $value) {
                $challengeStran = DB::table('tbl_challenge_translations')
                    ->where('id_challenge', $challengeMe->id_challenge)
                    ->where('language', $value['code'])->first();
                $dataNotify[$value['code']]['content'] = lang('notify_challenge_false', 'message', [
                    'name' => $challengeStran->name
                ], $value['code']);

                $dataNotify[$value['code']]['title'] = lang('title_notify_challenge_false', 'message', [
                    'name' => $challengeStran->name
                ], $value['code']);
            }

            $dataNotify['id_challenge'] = $challengeMe->id_challenge;
            $dataNotify['id_challenge_me'] = $challengeMe->id;
            $dataNotify['id_event_articles'] = $challengeMe->id_event_articles;
            notificationCus('challenge_me_fail', 0, $challengeMe->client_id, $dataNotify);

            $challengeMe->status = 2;
            $challengeMe->date_status = date('Y-m-d H:i:s');
            $challengeMe->save();



            $exchange_point_now = $this->AdminService->get_option('exchange_rate_haru_wallet') ?? 1;

            $contribute = new ChallengeContribute();
            $contribute->client_id = $challengeMe->client_id;
            $contribute->id_challenge = $challengeMe->id_challenge;
            $contribute->id_challenge_me = $challengeMe->id;
            $contribute->id_event_articles = $challengeMe->id_event_articles;
            $contribute->exchange_point_now = $exchange_point_now;
            $contribute->money = $challengeMe->deposit;
            $contribute->total_haru_xu = ($challengeMe->deposit * $exchange_point_now);
            $contribute->type = 1;
            $contribute->save();
        }
        return response()->json([
            'result' => true,
            'message' => lang('dt_challenge_reminded_success')
        ], 200);
    }

    public function addChallengeWaiting()
    {
        $input = $this->request->all();
        $customer_id = $this->request->client->id ?? 0;
        $dataPost = $this->request->input();
        if (empty($customer_id)) {
            return response()->json([
                'title' => lang('notification'),
                'data' => [],
                'result' => false,
                'message' => lang('dt_login_use_app')
            ]);
        }
        $validator = Validator::make($input, [
            'id_challenge' => 'required|integer',
            'reference_no' => 'nullable|string',
            'date' => 'nullable|string',
            'deposit' => 'nullable|numeric',
            'completion_rate' => 'nullable|numeric',
            'haru_xu' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_invalid_data'),
                'errors' => $validator->errors()
            ], 422);
        }
        // kiểm tra đã tham gia challenge này chưa
        $ktChallenge = ChallengeMe::where('client_id', $customer_id)->where(
            'id_challenge',
            $input['id_challenge']
        )->where('status', 0)->exists();
        if ($ktChallenge) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => lang('dt_already_joined_challenge')
            ], 409);
        }
        DB::beginTransaction();
        try {
            $challenge = Challenge::find($input['id_challenge']);
            $Client = Clients::find($customer_id);
            $rankCustomer = RankCommunity::find($Client->rank_community_id ?? 0);
            if (!empty($rankCustomer)) {
                if ($rankCustomer->id < $challenge->min_rank_join) {
                    return response()->json([
                        'result' => false,
                        'message' => lang('rank_you_not_join_challenge')
                    ], 400);
                }
            }
            if ($challenge->limit_join > 0) {
                if (($challenge->quantity_joined ?? 0) >= $challenge->limit_join) {
                    return response()->json([
                        'result' => false,
                        'title' => lang('notification'),
                        'message' => lang('dt_challenge_join_limit_reached')
                    ], 400);
                }
            }
            $codeWaiting =  'CMW-'.time();
            DB::table('tbl_challenge_waiting')->insert([
                'reference_no' => $codeWaiting,
                'id_challenge' => $input['id_challenge'],
                'id_client' => $customer_id,
                'deposit' => $input['deposit'],
                'payment_mode_id' => $input['payment_mode_id'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $successPayment = $this->AdminService->createQRPayment([
                'code' => $codeWaiting,
                'amount' => $input['deposit'],
            ],$this->_locale);
            if(empty($successPayment['qr'])) {
                DB::rollBack();
                return response()->json([
                    'result' => false,
                    'title' => lang('notification'),
                    'message' => $successPayment['message']
                ], 500);
            }
            DB::commit();
            return response()->json([
                'result' => true,
                'title' => lang('notification'),
                'message' => lang('c_payment_to_join_success_challenge'),
                'info_payment' => $successPayment ?? []
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
