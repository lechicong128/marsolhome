<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Controllers\Controller;
use App\Models\Elearning;
use App\Models\ElearningWaiting;
use App\Models\ElearningUnlock;
use App\Models\Notification;
use App\Models\Products;
use App\Models\VideoFile;
use App\Models\Variant;
use App\Models\ClientActionVideo;
use App\Models\CommentVideo;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\UploadFile;
use function PHPUnit\Framework\isNull;

class VideoController extends AuthController
{
    protected $dbAccount;
    use UploadFile;
    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        if (config('app.debug')) {
            DB::enableQueryLog();
        }
        $this->SaveSession = true;
        $this->baseUrl = config('services.storage.url');
        $this->url = config('services.url');
//        $this->url = 'https://enlarge-clay-sand-riding.trycloudflare.com';
//        $this->baseUrl = 'https://enlarge-clay-sand-riding.trycloudflare.com/storage';
        $this->dbAccount = $accountService;
    }


    // danh sách video tổng hợp có cả video tips, review, elearning
    public function get_list() {
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
        $id_client = $this->request->client->id ?? 138;
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = (int) $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = (int) $this->request->query('per_page');
        }
        // rel_type: chỉ cho phép các giá trị hợp lệ (tránh query sai / SQL injection orderByRaw)
        $search = $this->request->query('search') ?? '';
        $rel_type = $this->request->query('rel_type') ?? '0';
        $show_home = $this->request->query('show_home') ?? '0';
        $allowedRelTypes = ['0', 'tips', 'review', 'elearning', 'popular'];
        if (!in_array($rel_type, $allowedRelTypes, true)) {
            $rel_type = '0';
        }
        $seed = session()->get('video_seed', rand());
        session()->put('video_seed', $seed);
        $orderBy = "RAND($seed)";
        // Ràng buộc paging để tránh load quá nặng
        if ($current_page < 1) $current_page = 1;
        if ($per_page < 1) $per_page = 10;
        if ($per_page > 50) $per_page = 50;

        if(!empty($rel_type) && $rel_type == 'popular') {
            $orderBy = "count_see desc";
        }
        else {
            $orderBy = "created_at desc";
        }
        // popular chỉ là cách sắp xếp, không phải rel_type trong DB
        $dbRelType = ($rel_type === 'popular') ? '0' : $rel_type;
        $list_video = VideoFile::with(['products'])->with(['products.variant_option', 'products.tag.transalations'])
            ->select('tbl_video_file.id', 'name', 'video', 'rel_type', 'rel_id', 'count_like', 'count_share', 'count_comment', 'count_see', 'evaluate', 'id_client', 'description', 'id_product')
            ->selectRaw('CONCAT("'.$this->baseUrl.'/", thumbnail) as thumbnail')
            ->selectRaw('EXISTS(
                    SELECT 1
                    FROM tbl_client_action_video
                    WHERE id_video = tbl_video_file.id
                    AND event = "like"
                    AND id_client = ?
                ) as is_like', [$id_client])
            ->where('tbl_video_file.is_premium', 0)
            ->where('tbl_video_file.active', 1)
            ->where(function ($query) use ($dbRelType, $show_home, $search) {
                if(!empty($dbRelType) && $dbRelType !== '0') {
                    $query->where('tbl_video_file.rel_type', $dbRelType);
                }
                if(!empty($show_home)) {
                    $query->where('tbl_video_file.show_home', 1);
                }
                if(!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('tbl_video_file.name', 'like', '%' . $search . '%')
                            ->orWhere('tbl_video_file.description', 'like', '%' . $search . '%');
                    });
                }
            })
            ->orderByRaw($orderBy)
            ->paginate($per_page, ['*'], 'page', $current_page);

        // Tránh N+1 khi lấy unlock cho elearning
        $elearningIds = $list_video->getCollection()
            ->where('rel_type', 'elearning')
            ->pluck('rel_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $unlockByElearningId = [];
        if (!empty($elearningIds)) {
            $unlockByElearningId = ElearningUnlock::query()
                ->whereIn('id_elearning', $elearningIds)
                ->where('id_client', $id_client)
                ->get()
                ->keyBy('id_elearning')
                ->all();
        }

        $list_video->getCollection()->transform(function ($video) use ($unlockByElearningId) {
            $video->video = !empty($video->video) ? $this->baseUrl . '/' . $video->video : null;
            if($video->rel_type == 'review') {
                $video->url_detail = $this->url . '/api/video/detail_review/' . $video->id;
            }
            else if($video->rel_type == 'tips') {
                $video->url_detail = $this->url . '/api/video/detail_tips/' . $video->id;
            }
            else if($video->rel_type == 'elearning') {
                $video->url_detail = $this->url . '/api/video/detail_elearning/' . $video->rel_id;
                $video->unlock = $unlockByElearningId[$video->rel_id] ?? null;
            }
            return $video;
        });
        return response()->json($list_video);
    }

    public function list_elearning() {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = (int) $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = (int) $this->request->query('per_page');
        }
        if ($current_page < 1) $current_page = 1;
        if ($per_page < 1) $per_page = 10;
        if ($per_page > 50) $per_page = 50;
        $is_check_new = $this->request->query('is_check_new');
        $search = $this->request->query('search');
        $is_unlock = $this->request->query('is_unlock');
        $id_client = $this->request->client->id ?? 138;
        $seed = session()->get('video_seed', rand());
        session()->put('video_seed', $seed);
        $orderBy = "RAND($seed)";
        $dtData = Elearning::query()
            ->with(['video_trailer'])
            ->with(['unlock' => function ($q) use ($id_client) {
                $q->where('id_client', $id_client);
            }])
            ->where(function($query) use ($is_check_new, $is_unlock, $search) {
                  if(!empty($is_check_new)) {
                      $query->where('is_check_new', 1);
                  }
                if(!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('tbl_elearning.title', 'like', '%' . $search . '%')
                            ->orWhere('tbl_elearning.author', 'like', '%' . $search . '%')
                            ->orWhere('tbl_elearning.description', 'like', '%' . $search . '%');
                    });
                }
            })->when($is_unlock, function ($q) use ($id_client) {
                $q->whereHas('unlock', function ($q) use ($id_client) {
                    $q->where('id_client', $id_client);
                });
            })
            ->orderByRaw($orderBy)
            ->paginate($per_page, ['*'], 'page', $current_page);

        // Tránh N+1: tính duration_see theo nhóm (rel_id) cho các elearning trên trang hiện tại
        $elearningIds = $dtData->getCollection()->pluck('id')->filter()->unique()->values()->all();
        $durationSeeByElearningId = [];
        if (!empty($elearningIds)) {
            $durationSeeByElearningId = DB::table('tbl_client_action_video as cav')
                ->join('tbl_video_file as vf', 'vf.id', '=', 'cav.id_video')
                ->where('cav.id_client', $id_client)
                ->where('vf.rel_type', 'elearning')
                ->whereIn('vf.rel_id', $elearningIds)
                ->groupBy('vf.rel_id')
                ->selectRaw('vf.rel_id as elearning_id, COALESCE(SUM(cav.note_event), 0) as duration_see')
                ->pluck('duration_see', 'elearning_id')
                ->all();
        }

        $dtData->getCollection()->transform(function ($elearning) use ($durationSeeByElearningId) {
            $duration_see = (int) ($durationSeeByElearningId[$elearning->id] ?? 0);
            $elearning->duration_see = $duration_see;
            $elearning->duration_radio_see = !empty($elearning->all_duration) ? round($duration_see / $elearning->all_duration * 100) : 0;

            if (!empty($elearning->video_trailer)) {
                $elearning->video_trailer->original_video = !empty($elearning->video_trailer->original_video) ? $this->baseUrl . '/' . $elearning->video_trailer->original_video : null;
                $elearning->video_trailer->video = !empty($elearning->video_trailer->video) ? $this->baseUrl . '/' . $elearning->video_trailer->video : null;
                $elearning->video_trailer->thumbnail = !empty($elearning->video_trailer->thumbnail) ? $this->baseUrl . '/' . $elearning->video_trailer->thumbnail : null;
            }
            $elearning->image = !empty($elearning->image) ? $this->baseUrl . '/' . $elearning->image : null;
            $elearning->url_detail = $this->url . '/api/video/detail_elearning/' . $elearning->id;
            return $elearning;
        });
        return response()->json($dtData);
    }

    public function detail_review($id = '') {
        $id_client = $this->request->client->id ?? 138;
        $videoFile = VideoFile::with(['products'])
            ->with(['products.variant_option', 'products.tag.transalations'])
            ->selectRaw('EXISTS(
                        SELECT 1
                        FROM tbl_client_action_video
                        WHERE id_video = tbl_video_file.id
                        AND event = "like"
                        AND id_client = ?
                    ) as is_like', [$id_client])
            ->select('tbl_video_file.id', 'name', 'video', 'rel_type', 'count_like', 'count_share', 'count_comment', 'count_see', 'evaluate', 'id_client', 'description', 'id_product')
            ->selectRaw('CONCAT("'.$this->baseUrl.'/", thumbnail) as thumbnail')
            ->where('tbl_video_file.id', $id)
            ->where('tbl_video_file.rel_type', 'review')
            ->where('tbl_video_file.active', 1)->first();
        if(!empty($videoFile->id)) {
            $videoFile->video = !empty($videoFile->video) ? $this->baseUrl . '/' . $videoFile->video : NULL;
            if (!empty($videoFile->products)) {
                $videoFile->products->image = $this->baseUrl . '/' . $videoFile->products->image;
            }
            if(!empty($videoFile->id_client)) {
                $requestClient = new Request();
                $requestClient->merge(['list_id' => [$videoFile->id_client]]);
                $responseClient = $this->dbAccount->getListDetailCustomer($requestClient);
                $dataClient = $responseClient->getData(true);
                $videoFile->client = $dataClient['clients'][$videoFile->id_client] ?? [];
            }

            if(!empty($videoFile->products)) {
                $_locale = 'vi';
                $videoFile->products->tag_product = ($videoFile->products->tag ?? collect([]))->map(function ($item) use ($_locale) {
                    $itemNew = $item->transalations->where('language', $_locale)->first();
                    return [
                        'id' => $item->id,
                        'name' => $itemNew->name ?? ($item->name ?? ''),
                        'color' => $item->color,
                        'background' => $item->background
                    ];

                });
                unset($videoFile->products->tag);
            }

            return response()->json([
                'result' => true,
                'data' => $videoFile
            ]);
        }
        else {
            return response()->json([
                'result' => false,
                'data' => [],
                'message' => 'Không tìm thấy video'
            ]);
        }
    }

    public function detail_tips($id = '') {
        $id_client = $this->request->client->id ?? 138;
        $videoFile = VideoFile::with(['products'])->with(['products.variant_option', 'products.tag.transalations'])
            ->select('tbl_video_file.id', 'name', 'video', 'rel_type', 'count_like', 'count_share', 'count_comment', 'count_see', 'evaluate', 'id_client', 'description', 'id_product')
            ->selectRaw('CONCAT("'.$this->baseUrl.'/", thumbnail) as thumbnail')
            ->selectRaw('EXISTS(
                                    SELECT 1
                                    FROM tbl_client_action_video
                                    WHERE id_video = tbl_video_file.id
                                    AND event = "like"
                                    AND id_client = ?
                                ) as is_like', [$id_client])
            ->where('tbl_video_file.id', $id)
            ->where('tbl_video_file.rel_type', 'tips')
            ->where('tbl_video_file.active', 1)->first();

        if(!empty($videoFile->id)) {
            $videoFile->video = $this->baseUrl . '/' . $videoFile->video;
            if (!empty($videoFile->products)) {
                $videoFile->products->image = $this->baseUrl . '/' . $videoFile->products->image;
            }

            if(!empty($videoFile->products)) {
                $_locale = 'vi';
                $videoFile->products->tag_product = ($videoFile->products->tag ?? collect([]))->map(function ($item) use ($_locale) {
                    $itemNew = $item->transalations->where('language', $_locale)->first();
                    return [
                        'id' => $item->id,
                        'name' => $itemNew->name ?? ($item->name ?? ''),
                        'color' => $item->color,
                        'background' => $item->background
                    ];

                });
                unset($videoFile->products->tag);
            }

            return response()->json([
                'result' => true,
                'data' => $videoFile
            ]);
        }
        else {
            return response()->json([
                'result' => false,
                'data' => [],
                'message' => 'Không tìm thấy video'
            ]);
        }
    }

    public function detail_elearning($id = '') {
        $id_client = $this->request->client->id ?? 138;
        $elearning = null;
        $videoFileTraller = VideoFile::where('rel_id', $id)->select('*')
            ->selectRaw('CONCAT("'.$this->baseUrl.'/", video) as video')
            ->selectRaw('CONCAT("'.$this->baseUrl.'/", original_video) as original_video')
            ->selectRaw('CONCAT("'.$this->baseUrl.'/", thumbnail) as thumbnail')
            ->where('tbl_video_file.is_premium', 0)
            ->where('tbl_video_file.active', 1)
            ->where('rel_type', 'elearning')
            ->first();
        if(!empty($videoFileTraller->id)) {
            $elearning = Elearning::with(['unlock' => function ($q) use ($id_client) {
                $q->where('id_client', $id_client);
            }])
            ->select('*')
            ->selectRaw('CONCAT("'.$this->baseUrl.'/", image) as image')
            ->where('id', $videoFileTraller->rel_id)->first();

            if (empty($elearning)) {
                return response()->json([
                    'result' => false,
                    'data' => [],
                    'message' => 'Không tìm thấy Elearning'
                ], 404);
            }

            $query = VideoFile::where('tbl_video_file.rel_id', $elearning->id)
                ->with(['time_play' => function ($q) use ($id_client) {
                    $q->where('id_client', $id_client);
                }])
                ->select(
                    'tbl_video_file.id',
                    'name',
                    'rel_type',
                    'count_like',
                    'count_share',
                    'count_comment',
                    'count_see',
                    'evaluate',
                    'id_client',
                    'tbl_video_file.description',
                    'tbl_video_file.duration',
                )
                ->selectRaw('CONCAT("'.$this->baseUrl.'/", thumbnail) as thumbnail')
                ->selectRaw('EXISTS(
                    SELECT 1
                    FROM tbl_client_action_video
                    WHERE id_video = tbl_video_file.id
                    AND event = "like"
                    AND id_client = ?
                ) as is_like', [$id_client])
                ->where('tbl_video_file.is_premium', 1)
                ->where('tbl_video_file.active', 1)
                ->where('rel_type', 'elearning')
                ->orderBy('tbl_video_file.order_premium', 'asc')
                ->orderBy('tbl_video_file.id', 'asc');

            if ($elearning->unlock->isNotEmpty()) {
                $query->selectRaw('1 as `unlock`');
                $query->selectRaw('CONCAT("'.$this->baseUrl.'/", video) as video');
                $query->selectRaw('CONCAT("'.$this->baseUrl.'/", original_video) as original_video');
            }
            else {
                $query->selectRaw('0 as `unlock`');
            }
            $ListVideoFile = $query->get();
            $elearning->video_trailer = $videoFileTraller;
            foreach($ListVideoFile as $key => $value) {
                $value->duration_see = $value->time_play[0]->note_event ?? 0;
                $value->duration_radio_see = !empty($value->duration) ? round($value->duration_see / $value->duration * 100) : 0;
                $value->author = !empty($elearning->author) ? $elearning->author : NULL;
            }
            $elearning->list_video = $ListVideoFile;
        }


        if(!empty($elearning->id)) {
            return response()->json([
                'result' => true,
                'data' => $elearning
            ]);
        }
        else {
            return response()->json([
                'result' => false,
                'data' => [],
                'message' => 'Không tìm thấy Elearning'
            ]);
        }
    }

    public function see_video($id = '')
    {
        $id_client = $this->request->client->id ?? 138;

        $video = VideoFile::find($id);

        if (!$video) {
            return response()->json([
                'result' => false,
                'message' => 'Video không tồn tại'
            ]);
        }

        DB::beginTransaction();

        try {

            // 👉 tăng lượt xem
            VideoFile::where('id', $id)->increment('count_see');

            $now = now();

            // 👉 insert log see (có thể bị spam nếu không kiểm soát)
            DB::table('tbl_client_action_video')->insert([
                'id_client' => $id_client,
                'id_video' => $id,
                'event' => 'see',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 👉 xử lý play (update hoặc insert)
            $ktEvent = DB::table('tbl_client_action_video')
                ->where('id_client', $id_client)
                ->where('id_video', $id)
                ->where('event', 'play')
                ->first();

            if ($ktEvent) {

                DB::table('tbl_client_action_video')
                    ->where('id', $ktEvent->id)
                    ->update([
                        'updated_at' => $now
                    ]);

            } else {

                DB::table('tbl_client_action_video')->insert([
                    'id_client' => $id_client,
                    'id_video' => $id,
                    'event' => 'play',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Check lượt xem thành công'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('SEE VIDEO ERROR', [
                'message' => $e->getMessage(),
                'video_id' => $id,
                'client_id' => $id_client
            ]);

            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại'
            ]);
        }
    }

    public function like_video($id = '', $like = 1)
    {
        $id_client = $this->request->client->id ?? 138;

        $video = VideoFile::find($id);

        if (!$video) {
            return response()->json([
                'result' => false,
                'message' => 'Video không tồn tại'
            ]);
        }

        DB::beginTransaction();

        try {

            $ktEvent = DB::table('tbl_client_action_video')
                ->where('id_client', $id_client)
                ->where('id_video', $id)
                ->where('event', 'like')
                ->first();

            $now = now();

            if ($like) {

                if (empty($ktEvent)) {

                    // 👉 tăng like
                    VideoFile::where('id', $id)->increment('count_like');

                    // 👉 insert log
                    DB::table('tbl_client_action_video')->insert([
                        'id_client' => $id_client,
                        'id_video' => $id,
                        'event' => 'like',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

            } else {

                if (!empty($ktEvent)) {

                    // 👉 giảm like
                    VideoFile::where('id', $id)
                        ->where('count_like', '>', 0)
                        ->decrement('count_like');

                    // 👉 xóa log
                    DB::table('tbl_client_action_video')
                        ->where('id', $ktEvent->id)
                        ->delete();
                }
            }

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => $like
                    ? 'Thích video thành công'
                    : 'Bỏ thích video thành công'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('LIKE VIDEO ERROR', [
                'message' => $e->getMessage(),
                'video_id' => $id,
                'client_id' => $id_client
            ]);

            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại'
            ]);
        }
    }

    public function share_video($id = '') {
        $id_client = $this->request->client->id ?? 138;
        $video = VideoFile::find($id);
        if(!empty($video->id)) {
            VideoFile::where('id', $id)->increment('count_share', 1);
            $now = now();
            DB::table('tbl_client_action_video')->insert([
                'id_client' => $id_client,
                'id_video' => $id,
                'event' => 'share',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            return response()->json([
                'result' => true,
                'message' => 'Chia sẽ thành công'
            ]);
        }
        else {
            return response()->json([
                'result' => false,
                'message' => 'Chia sẽ không thành công'
            ]);
        }
    }

    public function play_video($id = '') {
        $id_client = $this->request->client->id ?? 138;
        $time_video = $this->request->input('time_video') ?? 0;
        $now = now();
        $updated = DB::table('tbl_client_action_video')
            ->where('id_client', $id_client)
            ->where('id_video', $id)
            ->where('event', 'play_video')
            ->update([
                'note_event' => $time_video,
                'updated_at' => $now,
            ]);
        if (empty($updated)) {
            DB::table('tbl_client_action_video')->insert([
                'id_client' => $id_client,
                'id_video' => $id,
                'event' => 'play_video',
                'note_event' => $time_video,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return response()->json([
            'result' => true,
            'message' => 'Đánh dấu lượt xem thành công'
        ]);

    }


    public function comment($id_video = '') {
        $id_client = $this->request->client->id ?? 138;
        $id_parent = $this->request->input('id_comment') ?? 0;
        $reply_to_user_id = $this->request->input('reply_to_user_id') ?? 0;

        $comment = trim($this->request->input('comment'));

        if ($comment === '') {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng nhập nội dung để bình luận'
            ]);
        }

        DB::beginTransaction();

        try {

            // 👉 check comment cha
            if (!empty($id_parent)) {

                $commentReply = CommentVideo::find($id_parent);

                if (!$commentReply) {
                    return response()->json([
                        'result' => false,
                        'message' => 'Bình luận cha không tồn tại'
                    ]);
                }

                // 👉 luôn gán về comment gốc (giống TikTok)
                if ($commentReply->id_parent != 0) {
                    $id_parent = $commentReply->id_parent;
                }
            }

            // 👉 insert comment
            $commentVideo = new CommentVideo();
            $commentVideo->id_video = $id_video;
            $commentVideo->id_client = $id_client;
            $commentVideo->comment = $comment;
            $commentVideo->id_parent = $id_parent;
            $commentVideo->reply_to_user_id = $id_parent ? $reply_to_user_id : 0;
            $commentVideo->save();

            // 👉 update count
            if ($id_parent) {
                CommentVideo::where('id', $id_parent)->increment('count_reply');
            }

            VideoFile::where('id', $id_video)->increment('count_comment');

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => $id_parent
                    ? 'Trả lời bình luận thành công'
                    : 'Bình luận video thành công'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            // 👉 log lỗi để debug
            \Log::error('COMMENT ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id_video' => $id_video,
                'id_client' => $id_client
            ]);

            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại'
            ]);
        }
    }

    public function edit_comment($id = '')
    {
        $id_client = $this->request->client->id ?? 138;

        $commentVideo = CommentVideo::find($id);

        if (!$commentVideo) {
            return response()->json([
                'result' => false,
                'message' => 'Bình luận không tồn tại'
            ]);
        }

        if ($commentVideo->id_client != $id_client) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không thể chỉnh sửa bình luận của người khác'
            ]);
        }

        $comment = trim($this->request->input('comment'));

        if ($comment === '') {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng nhập nội dung để bình luận'
            ]);
        }

        // log lịch sử
        $data_logs = json_decode($commentVideo->data_logs, true) ?? [];

        $data_logs[] = [
            'comment' => $commentVideo->comment,
            'updated_at' => $commentVideo->updated_at,
        ];

        // update
        $commentVideo->data_logs = json_encode($data_logs);
        $commentVideo->comment = $comment;

        $success = $commentVideo->save();

        return response()->json([
            'result' => $success,
            'message' => $success
                ? 'Cập nhật bình luận thành công'
                : 'Cập nhật bình luận không thành công'
        ]);
    }

    public function delete_comment($id = '')
    {
        $id_client = $this->request->client->id ?? 138;
        $commentVideo = CommentVideo::find($id);
        if (!$commentVideo) {
            return response()->json([
                'result' => false,
                'message' => 'Bình luận không tồn tại'
            ]);
        }
        if ($commentVideo->id_client != $id_client) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không thể xóa bình luận của người khác'
            ]);
        }
        DB::beginTransaction();
        try {
            $id_video = $commentVideo->id_video;
            // đếm số comment bị xóa
            $totalDelete = 1;
            if ($commentVideo->id_parent == 0) {
                $childIds = CommentVideo::where('id_parent', $commentVideo->id)->pluck('id');
                $totalDelete += count($childIds);
                CommentVideo::whereIn('id', $childIds)->delete();
            }
            else {
                // nếu là comment reply thì giảm count_reply của comment cha
                CommentVideo::where('id', $commentVideo->id_parent)
                    ->where('count_reply', '>', 0)
                    ->decrement('count_reply');
            }

            // xóa chính
            $commentVideo->delete();

            // update count
            VideoFile::where('id', $id_video)
                ->where('count_comment', '>=', $totalDelete)
                ->decrement('count_comment', $totalDelete);

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Xóa bình luận thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Xóa bình luận không thành công'
            ]);
        }
    }

    public function list_comment($id_video = 0, $id_parent = 0)
    {
        $id_client = $this->request->client->id ?? 138;
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = (int) $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = (int) $this->request->query('per_page');
        }
        if ($current_page < 1) $current_page = 1;
        if ($per_page < 1) $per_page = 10;
        if ($per_page > 50) $per_page = 50;

        $orderBy = 'tbl_client_comment_video.created_at desc, tbl_client_comment_video.id desc';
        if(!empty($id_parent)) {
            $orderBy = 'tbl_client_comment_video.created_at asc, tbl_client_comment_video.id asc';
        }

        $list_comment = CommentVideo::with(['comment_reply' => function ($q) {
                $q->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(1);
            }])
            ->select('tbl_client_comment_video.id', 'tbl_client_comment_video.comment', 'tbl_client_comment_video.id_client', 'tbl_client_comment_video.created_at', 'tbl_client_comment_video.count_like', 'tbl_client_comment_video.count_reply', 'tbl_client_comment_video.reply_to_user_id')
            ->selectRaw('EXISTS(
                    SELECT 1
                    FROM tbl_action_comment
                    WHERE id_comment = tbl_client_comment_video.id
                    AND event = "like"
                    AND id_client = ?
                ) as is_like', [$id_client])
            ->where('tbl_client_comment_video.id_video', $id_video)
            ->where('tbl_client_comment_video.id_parent', $id_parent)
            ->orderByRaw($orderBy)
            ->paginate($per_page, ['*'], 'page', $current_page);

        $idClients = $list_comment->getCollection()
            ->flatMap(function ($item) {
                $ids = [$item->id_client];
                if(!empty($item->reply_to_user_id)) {
                    $ids[] = $item->reply_to_user_id;
                }
                if (!empty($item->comment_reply)) {
                    foreach ($item->comment_reply as $reply) {
                        $ids[] = $reply->id_client;
                        if(!empty($reply->reply_to_user_id)) {
                            $ids[] = $reply->reply_to_user_id;
                        }
                    }
                }

                return $ids;
            })
            ->unique()
            ->values()
            ->toArray();

        $requestClient = new Request();
        $requestClient->merge(['list_id' => $idClients]);
        $responseClient = $this->dbAccount->getListDetailCustomer($requestClient);
        $listDataClient = $responseClient->getData(true);
        $dataClient = [];
        if($listDataClient['result']) {
            $dataClient = $listDataClient['clients'];
        }

        $videoFile = VideoFile::find($id_video);

        $list_comment->getCollection()->transform(function ($detail) use ($dataClient, $videoFile, $id_client) {
            $detail->client = $dataClient[$detail->id_client] ?? null;
            if($detail->id_client == $videoFile->id_client) {
                $detail->author = $detail->author;
            }
            if(!empty($detail->reply_to_user_id)) {
                $detail->client_reply = $dataClient[$detail->reply_to_user_id] ?? null;
            }
            $detail->more_count_reply = ($detail->count_reply > 1) ? ($detail->count_reply - 1) : 0;
            if($detail->id_client == $videoFile->id_client) {
                $detail->author = 1;
            }
            if($detail->comment_reply) {
                foreach($detail->comment_reply as $reply) {
                    $reply->client = $dataClient[$reply->id_client] ?? null;

                    $ktLike = DB::table('tbl_action_comment')
                        ->where('id_comment', $reply->id)
                        ->where('id_client', $id_client)
                        ->where('event', 'like')->first();
                    $reply->is_like = 0;
                    if(!empty($ktLike->id)) {
                        $reply->is_like = 1;
                    }

                    $reply->client_reply = $dataClient[$reply->reply_to_user_id] ?? null;
                    if($reply->id_client == $videoFile->id_client) {
                        $reply->author = 1;
                    }
                }
            }
            return $detail;
        });
        $list_comment = $list_comment->toArray();
        $list_comment['count_comment'] = $videoFile->count_comment;
        return response()->json($list_comment);
    }

    public function like_comment($id = '', $like = 1)
    {
        $id_client = $this->request->client->id ?? 138;

        $comment = CommentVideo::find($id);

        if (!$comment) {
            return response()->json([
                'result' => false,
                'message' => 'Bình luận không tồn tại'
            ]);
        }

        DB::beginTransaction();

        try {

            $ktEvent = DB::table('tbl_action_comment')
                ->where('id_client', $id_client)
                ->where('id_comment', $id)
                ->where('event', 'like')
                ->first();

            if ($like) {

                if (empty($ktEvent)) {

                    // tăng like
                    CommentVideo::where('id', $id)->increment('count_like');

                    // insert log
                    $now = now();
                    DB::table('tbl_action_comment')->insert([
                        'id_client' => $id_client,
                        'id_comment' => $id,
                        'event' => 'like',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

            } else {

                if (!empty($ktEvent)) {

                    // giảm like
                    CommentVideo::where('id', $id)
                        ->where('count_like', '>', 0)
                        ->decrement('count_like');

                    // xóa log
                    DB::table('tbl_action_comment')
                        ->where('id', $ktEvent->id)
                        ->delete();
                }
            }

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => $like ? 'Thích bình luận thành công' : 'Bỏ thích thành công'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            // log lỗi để debug
            \Log::error('LIKE COMMENT ERROR: ' . $e->getMessage());

            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại'
            ]);
        }
    }

    public function unlockElearning($id = '') {
        $elearning = Elearning::find($id);
        if(empty($elearning->id)) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => 'Không tìm thấy khóa học elearning'
            ], 404);
        }
        $id_client = $this->request->client->id ?? 138;
        $elearningUnlock = ElearningUnlock::where('id_elearning', $id)->where('id_client', $id_client)->first();
        if(!empty($elearningUnlock->id)) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => 'Bạn đã mở khóa khóa học elearning này'
            ], 400);
        }
        try {
            $payload = DB::transaction(function () use ($id, $id_client, $elearning) {
                $elearningWaiting = ElearningWaiting::where('id_elearning', $id)
                    ->where('id_client', $id_client)
                    ->where('status', 0)
                    ->lockForUpdate()
                    ->first();

                if(empty($elearningWaiting->id)) {
                    $codeWaiting =  'ELN-' . time();
                    $elearningWaiting = new ElearningWaiting();
                    $elearningWaiting->id_elearning = $id;
                    $elearningWaiting->id_client = $id_client;
                    $elearningWaiting->money = $elearning->price;
                    $elearningWaiting->payment_mode_id = 0;
                    $elearningWaiting->reference_no = $codeWaiting;
                    $elearningWaiting->save();
                } else {
                    $elearningWaiting->money = $elearning->price;
                    $elearningWaiting->save();
                }

                $moneyNeedPayment = $elearning->price - ($elearningWaiting->money_payment ?? 0);
                $successPayment = $this->createQRPayment($elearningWaiting->reference_no, $moneyNeedPayment);
                if(empty($successPayment['qr'])) {
                    throw new \RuntimeException($successPayment['message'] ?? 'Tạo mã QR thất bại');
                }

                return $successPayment;
            }, 3);

            return response()->json([
                'result' => true,
                'title' => lang('notification'),
                'message' => lang('Tạo phiếu thanh toán thành công, vui lòng thanh toán để được mở khóa khóa học'),
                'info_payment' => $payload ?? [],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function createQRPayment($code, $amount) {
        $account_bank        = get_option('pay2s_account_bank');
        $account_number      = get_option('pay2s_account_number');
        $account_name        = get_option('pay2s_account_name');
        $account_bank_short  = get_option('pay2s_account_bank_short');
        $account_bank_long   = get_option('pay2s_account_bank_long');
        $logo_bank           = get_option('pay2s_logo_bank');
        $account_number_show = get_option('pay2s_account_number_show');
        $account_name_show   = get_option('pay2s_account_name_show');

        $resultQr = createQrBank([
            'bankShortName' => $account_bank,
            'accountNumber' => $account_number,
            'accountName'   => $account_name,
            'amount'        => ceil($amount),
            'memo'          => $code,
            'is_mask'       => 0,
        ]);

        if (empty($resultQr)) {
            return ['qr' => null, 'message' => 'Tạo mã QR thất bại'];
        }

        return [
            'qr'   => $resultQr,
            'bank' => [
                'account_bank'     => $account_name_show,
                'account_number'   => $account_number_show,
                'account_name'     => $account_bank_short,
                'amount'           => ceil($amount),
                'note'             => $code,
                'account_name_long'=> $account_bank_long,
                'logo_bank'        => rtrim($this->url, '/') . '/' . $logo_bank,
            ],
        ];
    }

    public function detail_video($id = '') {
        $id_client = $this->request->client->id ?? 138;

        // Guard: id phải tồn tại
        $videoFile = VideoFile::find($id);
        if (empty($videoFile)) {
            return response()->json([
                'result' => false,
                'data'   => [],
                'message'=> 'Không tìm thấy Video',
            ], 404);
        }

        // Nếu là video thuộc elearning (bài học)
        if ($videoFile->rel_type === 'elearning') {
            // Kiểm tra đã unlock chưa
            $ktUnlock = ElearningUnlock::where('id_elearning', $videoFile->rel_id)
                ->where('id_client', $id_client)
                ->first();

            if (empty($ktUnlock->id)) {
                return response()->json([
                    'result' => false,
                    'data'   => [],
                    'message'=> 'Bạn chưa mở khóa khóa học elearning này',
                ], 400);
            }

            // Lấy thông tin khóa học để lấy author (nếu cần)
            $elearning = Elearning::find($videoFile->rel_id);

            // Lấy chi tiết đúng 1 video (id hiện tại) kèm time_play & is_like + full URL
            $detail = VideoFile::where('tbl_video_file.id', $videoFile->id)
                ->with(['time_play' => function ($q) use ($id_client) {
                    $q->where('id_client', $id_client);
                }])
                ->select(
                    'tbl_video_file.id',
                    'name',
                    'rel_type',
                    'count_like',
                    'count_share',
                    'count_comment',
                    'count_see',
                    'evaluate',
                    'id_client',
                    'tbl_video_file.description',
                    'tbl_video_file.duration'
                )
                ->selectRaw('CONCAT("'.$this->baseUrl.'/", thumbnail) as thumbnail')
                ->selectRaw('EXISTS(
                        SELECT 1
                        FROM tbl_client_action_video
                        WHERE id_video = tbl_video_file.id
                        AND event = "like"
                        AND id_client = ?
                    ) as is_like', [$id_client])
                ->selectRaw('CONCAT("'.$this->baseUrl.'/", video) as video')
                ->selectRaw('CONCAT("'.$this->baseUrl.'/", original_video) as original_video')
                ->where('tbl_video_file.is_premium', 1)
                ->where('tbl_video_file.active', 1)
                ->where('rel_type', 'elearning')
                ->first();

            if (empty($detail)) {
                return response()->json([
                    'result' => false,
                    'data'   => [],
                    'message'=> 'Không tìm thấy Video',
                ], 404);
            }

            $detail->author = !empty($elearning->author ?? null) ? $elearning->author : null;
            $detail->duration_see = $detail->time_play[0]->note_event ?? 0;
            $detail->duration_radio_see = !empty($detail->duration)
                ? round($detail->duration_see / $detail->duration * 100)
                : 0;

            return response()->json([
                'result' => true,
                'data'   => $detail,
            ]);
        }

        // Nếu là review/tips thì dùng luôn các hàm chi tiết hiện có
        if ($videoFile->rel_type === 'review') {
            return $this->detail_review($id);
        }

        if ($videoFile->rel_type === 'tips') {
            return $this->detail_tips($id);
        }

        // Các loại khác (nếu có) trả về lỗi chuẩn
        return response()->json([
            'result' => false,
            'data'   => [],
            'message'=> 'Loại video không được hỗ trợ',
        ], 400);
    }

}
