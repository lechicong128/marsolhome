<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\UserActionLog;
use App\Helpers\NotificationHelper;
use App\Helpers\SocketHelpers;


class CommentController extends AuthController
{
    use UserActionLog;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        if (config('app.debug')) {
            DB::enableQueryLog();
        }
        $this->baseUrl = config('services.storage.url');
    }
    public function index($postId)
    {
        $userId = $this->request->client->id ?? 0;

        $comments = Comment::where('post_id', $postId)
            ->whereNull('parent_id')
            ->with([
                'author:id,fullname,referral_code,avatar',
                'media',
                'replies' => function ($q) use ($userId) {
                    $q->with([
                        'author:id,fullname,referral_code,avatar',
                        'media'
                    ])
                    ->withCount('likes')
                    ->withExists([
                        'likes as liked_by_me' => function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        }
                    ]);
                }
            ])
            ->withCount('likes')
            ->withExists([
                'likes as liked_by_me' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($comments as $comment) {
            $comment->is_like = (bool) $comment->liked_by_me;
            
            if ($comment->author && $comment->author->avatar) {
                if (!filter_var($comment->author->avatar, FILTER_VALIDATE_URL)) {
                    $comment->author->avatar = $this->baseUrl . '/' .($comment->author->avatar);
                }
            }
            if ($comment->media) {
                foreach ($comment->media as $media) {
                    if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                        $media->media_url = $this->baseUrl . '/' . str_replace('storage/', '', $media->media_url);
                    }
                }
            }
            if (!empty($comment->replies)) {
                foreach ($comment->replies as $reply) {
                    $reply->is_like = (bool) $reply->liked_by_me;
                    
                    if ($reply->author && $reply->author->avatar) {
                        if (!filter_var($reply->author->avatar, FILTER_VALIDATE_URL)) {
                            $reply->author->avatar = $this->baseUrl . '/' .($reply->author->avatar);
                        }
                    }
                    if ($reply->media) {
                        foreach ($reply->media as $media) {
                            if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                                $media->media_url = $this->baseUrl . '/' . str_replace('storage/', '', $media->media_url);
                            }
                        }
                    }
                }
            }
        }

        return $comments;
    }
    public function update(Request $request, $id_comment)
    {
        $userId = $this->request->client->id ?? 0;
        
        $request->validate([
            'content' => 'required|string',
            'tagged_users' => 'array',
            'tagged_users.*' => 'integer|exists:tbl_clients,id',
            'media_existing' => 'array',
            'media_existing.*' => 'integer|exists:tbl_comment_media,id',
            'media.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:102400',
        ]);

        $comment = Comment::with('media')->find($id_comment);

        if (!$comment) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bình luận.'
            ], 404);
        }

        if ($comment->user_id != $userId) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không có quyền sửa bình luận này.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Cập nhật nội dung
            $comment->update([
                'content' => trim($request->input('content', '')) ?: '',
            ]);

            // Cập nhật tag
            DB::table('tbl_comment_tags')->where('comment_id', $comment->id)->delete();
            if ($request->filled('tagged_users')) {
                $tags = [];
                foreach ($request->input('tagged_users') as $taggedUserId) {
                    $tags[] = [
                        'comment_id' => $comment->id,
                        'user_id'    => $taggedUserId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('tbl_comment_tags')->insert($tags);
            }

            // Xoá media cũ không nằm trong media_existing
            $mediaIdsToKeep = $request->input('media_existing', []);
            $mediaToDelete = $comment->media()->when(!empty($mediaIdsToKeep), function ($q) use ($mediaIdsToKeep) {
                $q->whereNotIn('id', $mediaIdsToKeep);
            })->get();

            foreach ($mediaToDelete as $media) {
                $mediaPath = str_replace([asset('storage/') . '/', 'storage/'], '', $media->media_url);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($mediaPath);
                $media->delete();
            }

            // Thêm media mới
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    if (!$file->isValid()) continue;

                    $ext = strtolower($file->extension());
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        continue;
                    }

                    $path = $file->store('comments', 'public');

                    DB::table('tbl_comment_media')->insert([
                        'comment_id' => $comment->id,
                        'media_url'  => 'storage/' . $path,
                        'media_type' => 'image',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Log hành vi (nếu có update log thì ghi)
            $this->logAction($userId, 'update', 'comment', $comment->id);

            // Query trả về sau update, giữ nguyên cấu trúc mong đợi
            $comment = Comment::query()
                ->with([
                    'author:id,fullname,referral_code,avatar',
                    'media',
                    'replies' => function ($q) use ($userId) {
                        $q->with([
                            'author:id,fullname,referral_code,avatar',
                            'media'
                        ])
                        ->withCount('likes')
                        ->withExists([
                            'likes as liked_by_me' => function ($query) use ($userId) {
                                $query->where('user_id', $userId);
                            }
                        ]);
                    }
                ])
                ->withCount('likes')
                ->withExists([
                    'likes as liked_by_me' => function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    }
                ])
                ->findOrFail($comment->id);

            // Map URL
            $comment->is_like = (bool) $comment->liked_by_me;
            if ($comment->author && $comment->author->avatar) {
                if (!filter_var($comment->author->avatar, FILTER_VALIDATE_URL)) {
                    $comment->author->avatar = asset($comment->author->avatar);
                }
            }
            if ($comment->media) {
                foreach ($comment->media as $media) {
                    if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                        $media->media_url = $this->baseUrl . '/' . str_replace('storage/', '', $media->media_url);
                    }
                }
            }
            if (!empty($comment->replies)) {
                foreach ($comment->replies as $reply) {
                    $reply->is_like = (bool) $reply->liked_by_me;
                    if ($reply->author && $reply->author->avatar) {
                        if (!filter_var($reply->author->avatar, FILTER_VALIDATE_URL)) {
                            $reply->author->avatar = asset($reply->author->avatar);
                        }
                    }
                    if ($reply->media) {
                        foreach ($reply->media as $media) {
                            if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                                $media->media_url = $this->baseUrl . '/' . str_replace('storage/', '', $media->media_url);
                            }
                        }
                    }
                }
            }

            return response()->json([
                'result'  => true,
                'message' => 'Cập nhật bình luận thành công.',
                'data'    => $comment,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function store(Request $request, $postId)
    {
        $userId = $this->request->client->id ?? 0;
        $user = Clients::findOrFail($userId);
        $post = Post::findOrFail($postId);
        $request->validate([
            'media.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:102400',
            'tagged_users' => 'array',
            'tagged_users.*' => 'integer|exists:tbl_clients,id',
        ]);
        $comment = Comment::create([
            'post_id'   => $postId,
            'user_id'   => $userId,
            'parent_id' => $request->input('parent_id'),
            'content'   => trim($request->input('content', '')) ?: '',
        ]);

        // Ghi log bình luận
        $this->logAction($userId, 'comment', 'post', $postId, [
            'comment_id' => $comment->id,
            'parent_id' => $comment->parent_id,
        ]);

        // Nếu chưa theo dõi
        DB::table('tbl_post_watchers')
            ->updateOrInsert(
                ['post_id' => $postId, 'user_id' => $userId],
                ['active' => 1, 'created_at' => now()]
            );

        $this->logAction($userId, 'watch', 'post', $postId);
        if ($request->filled('tagged_users')) {
            $tags = [];
            foreach ($request->input('tagged_users') as $taggedUserId) {
                $tags[] = [
                    'comment_id' => $comment->id,
                    'user_id'    => $taggedUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('tbl_comment_tags')->insert($tags);
        }
        // 🔷 Xử lý media
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                if (!$file->isValid()) continue;

                $ext = strtolower($file->extension());
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    continue;
                }

                $path = $file->store('comments', 'public');

                DB::table('tbl_comment_media')->insert([
                    'comment_id' => $comment->id,
                    'media_url'  => 'storage/' . $path,
                    'media_type' => 'image',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $commentparent = Comment::find($comment->parent_id);
        NotificationHelper::notifyComment($comment, $request->input('tagged_users', []),$post->user_id,$userId,$commentparent);

        $comment->load('media'); // eager load media trả về kèm
        $comment = Comment::query()
            ->with([
                'author:id,fullname,referral_code,avatar',
                'media',
                'replies' => function ($q) {
                    $q->with([
                        'author:id,fullname,referral_code,avatar',
                        'media'
                    ])
                        ->latest()
                        ->take(3);
                }
            ])
            ->withCount('likes')
            ->withExists([
                'likes as liked_by_me' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ])
            ->findOrFail($comment->id);

        // xử lý URL
        if ($comment->author && $comment->author->avatar) {
            $comment->author->avatar = asset($comment->author->avatar);
        }
        if ($comment->media) {
            foreach ($comment->media as $media) {
                if (!empty($media->media_url)) {
                    $media->media_url = $this->baseUrl . '/' . str_replace('storage/', '', $media->media_url);
                }
            }
        }
        if (!empty($comment->replies)) {
            foreach ($comment->replies as $reply) {
                if ($reply->author && $reply->author->avatar) {
                    $reply->author->avatar = asset($reply->author->avatar);
                }
                if ($reply->media) {
                    foreach ($reply->media as $media) {
                        if (!empty($media->media_url)) {
                            $media->media_url = $this->baseUrl . '/' . str_replace('storage/', '', $media->media_url);
                        }
                    }
                }
            }
        }
        // SocketHelpers::sendSocketComment($userId, $postId);
        if(!empty($request->input('parent_id'))){
            SocketHelpers::sendSocketCommentReply($userId, $comment->post_id, $request->input('parent_id'), 'reply');
        }else{
            SocketHelpers::sendSocketPost($userId, $postId, 'comment');
        }
        // return response()->json($comment);
        return response()->json($comment);
    }

    public function like($id)
    {
        $userId = $this->request->client->id ?? 0;

        $like = CommentLike::firstOrCreate([
            'comment_id' => $id,
            'user_id'    => $userId
        ]);

        $this->logAction($userId, 'like', 'comment', $id);

        // Gửi thông báo
        $comment = Comment::find($id);
        if ($comment) {
            if($userId != $comment->user_id){
                NotificationHelper::notifyLikeComment($comment,[], $comment->user_id, $userId);
            }
            SocketHelpers::sendSocketCommentReply($userId, $comment->post_id, $id, 'like');
        }

        return response()->json(['status' => 'liked']);
    }

    public function unlike($id)
    {
        $userId = $this->request->client->id ?? 0;

        CommentLike::where('comment_id', $id)
            ->where('user_id', $userId)
            ->delete();

        $this->logAction($userId, 'unlike', 'comment', $id);
        $comment = Comment::find($id);
        if ($comment) {
            SocketHelpers::sendSocketCommentReply($userId, $comment->post_id, $id, 'like');
        }
        return response()->json(['status' => 'unliked']);
    }
    public function report(Request $request){
        $userId = $this->request->client->id ?? 0;

        $validated = $request->validate([
            'comment_id' => 'required|integer|exists:tbl_comments,id',
            'violation_id' => 'required|integer|exists:tbl_reportviolation,id',
            'note' => 'nullable|string|max:1000'
        ]);

        $comment = Comment::find($validated['comment_id']);
        if (!$comment) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bình luận.'
            ], 404);
        }

        $report = \App\Models\PostReportMode::create([
            'post_id' => $validated['comment_id'],
            'user_id' => $userId,
            'violation_id' => $validated['violation_id'],
            'note' => $validated['note'] ?? null,
            'type' => 'comment'
        ]);

        $this->logAction($userId, 'report', 'comment', $validated['comment_id']);

        return response()->json([
            'result' => true,
            'message' => 'Đã gửi báo cáo vi phạm bình luận.',
            'data' => $report
        ]);
    }
    public function deleteComment(Request $request, $id) {
        $userId = $this->request->client->id ?? 0;
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bình luận.'
            ], 404);
        }
        if ($comment->user_id != $userId) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không có quyền xóa bình luận này.'
            ], 403);
        }
        DB::beginTransaction();
        try {
            // Lấy tất cả các replies của comment này (nếu có)
            $allComments = Comment::where('parent_id', $comment->id)->get();
            // Đưa comment hiện tại vào mảng để xóa luôn
            $allComments->push($comment);

            foreach ($allComments as $c) {
                // Xóa file media vật lý trong storage
                $mediaItems = $c->media()->get();
                foreach ($mediaItems as $media) {
                    if (!empty($media->media_url)) {
                        $mediaPath = str_replace([asset('storage/') . '/', 'storage/'], '', $media->media_url);
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($mediaPath);
                    }
                }
                
                // Xóa dữ liệu ở các bảng liên quan (cascade tay)
                $c->media()->delete();
                $c->tags()->delete();
                $c->likes()->delete();
                
                // Xóa comment/reply
                $c->delete();
            }

            DB::commit();
            
            // Xóa socket nếu cẩn thiết (tuỳ logic hệ thống có hay không)
            if(!empty($comment->parent_id)){
                SocketHelpers::sendSocketCommentReply($userId, $comment->post_id, $comment->parent_id, 'reply');
            }else{
                SocketHelpers::sendSocketPost($userId, $comment->post_id, 'comment');
            }
            SocketHelpers::sendSocketDeleteComment($userId, $comment->post_id, $comment->id, $comment->parent_id);
            return response()->json([
                'result' => true,
                'message' => 'Xóa bình luận thành công.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Xóa bình luận thất bại: ' . $e->getMessage()
            ], 500);
        }
    }
}
