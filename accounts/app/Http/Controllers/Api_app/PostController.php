<?php

namespace App\Http\Controllers\Api_app;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Traits\UserActionLog;
use App\Helpers\NotificationHelper;
use App\Helpers\SocketHelpers;
use App\Models\ReportViolationMode;
use App\Models\PostReportMode;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends AuthController
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
    public function feedNoAu(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 5;

        $currentUserId = $this->request->client->id ?? 0;
        $type = (int) $request->input('type', 0);

        $query = Post::with([
            'author:id,fullname,referral_code,avatar',
            'media'
        ])
            ->withCount([
                'comments as comments_count' => function ($q) {
                    $q->whereNull('parent_id');
                },
                'likes',
                'postStars as stars_count' => function ($q) {
                    $q->whereHas('receipt', function ($r) {
                        $r->where('status', 1);
                    });
                },
            ])
            ->withExists([
                'likes as liked_by_me' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId);
                },
                'watchers as watching' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId);
                },
                'watchers as is_noti' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId)
                        ->where('active', 1);
                },
                'saveds as saved' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId);
                },
                'reports as reported' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId);
                },
                'postStars as starred' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId)
                        ->whereHas('receipt', function ($r) {
                            $r->where('status', 1);
                        });
                }
            ])
            ->where('is_hidden', 0); // Đã bỏ ->latest() để có thể sort theo kiểu khác

        // Bỏ bài đã "không quan tâm"
        $ignoredPostIds = DB::table('tbl_post_ignores')
            ->where('user_id', $currentUserId)
            ->pluck('post_id')
            ->toArray();

        if (!empty($ignoredPostIds)) {
            $query->whereNotIn('id', $ignoredPostIds);
        }

        // Bỏ bài user đã ẩn (tbl_hidePost)
        $hiddenPostIds = DB::table('tbl_hidePost')
            ->where('user_id', $currentUserId)
            ->pluck('post_id')
            ->toArray();

        if (!empty($hiddenPostIds)) {
            $query->whereNotIn('id', $hiddenPostIds);
        }

        $blockedUsers = DB::table('tbl_blocked_users')
            ->where('user_id', $currentUserId)
            ->pluck('blocked_id')
            ->toArray();

        $blockedByUsers = DB::table('tbl_blocked_users')
            ->where('blocked_id', $currentUserId)
            ->pluck('user_id')
            ->toArray();
        $query->whereNotIn('user_id', $blockedUsers)  // Loại bỏ những người đã chặn
            ->whereNotIn('user_id', $blockedByUsers);  // Loại bỏ những người đã chặn mình    
        // Lọc theo user cụ thể
        if ($request->filled('user')) {
            $referral_code = ltrim($request->input('user'), '@');
            $query->whereHas('author', function ($q) use ($referral_code) {
                $q->where('referral_code', $referral_code);
            });
        }

        // Xử lý type (0: tất cả, 1: phổ biến, 2: gần đây, 3: của tôi tạo)
        if ($type === 1) {
            $query->orderByDesc('likes_count')
                  ->orderByDesc('comments_count')
                  ->orderByDesc('created_at');
        } elseif ($type === 2) {
            $query->orderByDesc('created_at');
        } elseif ($type === 3) {
            $query->where('user_id', $currentUserId)
                  ->orderByDesc('created_at');
        } else {
            // type = 0 (tất cả)
            $query->orderByDesc('created_at');
        }

        $total = (clone $query)->count();
        $posts = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($post) {
                if ($post->author) {
                    $avatar = $post->author->avatar;

                    if (!empty($avatar) && !filter_var($avatar, FILTER_VALIDATE_URL)) {
                        $avatar = asset($avatar);
                    }

                    if (empty($avatar)) {
                        $avatar = NULL;
                    }

                    $post->author->avatar = $avatar;
                }
                
                if ($post->media) {
                    $post->media->map(function ($media) {
                        if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                            $media->media_url = $this->baseUrl.'/' . $media->media_url;
                        }
                        return $media;
                    });
                }
                
                $post->watching = (bool) $post->watching;
                $post->is_noti   = (bool) $post->is_noti;
                $post->saved = (bool) $post->saved;
                $post->reported = (bool) $post->reported;
                $post->stars_count = (int) $post->stars_count;
                return $post;
            });

        $hasMore = $total > $page * $perPage;

        return response()->json([
            'data' => $posts,
            'has_more' => $hasMore,
            'next_page' => $page + 1
        ]);
    }
    public function feed(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 5;

        $currentUserId = $this->request->client->id ?? 0;
        $type = (int) $request->input('type', 0);

        $query = Post::with([
            'author:id,fullname,referral_code,avatar',
            'media'
        ])
            ->withCount([
                'comments as comments_count' => function ($q) {
                    $q->whereNull('parent_id');
                },
                'likes',
                'postStars as stars_count' => function ($q) {
                    $q->whereHas('receipt', function ($r) {
                        $r->where('status', 1);
                    });
                },
            ])
            ->withExists([
                'likes as liked_by_me' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId);
                },
                'watchers as watching' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId);
                },
                'watchers as is_noti' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId)
                        ->where('active', 1);
                },
                'saveds as saved' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId);
                },
                'reports as reported' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId);
                },
                'postStars as starred' => function ($q) use ($currentUserId) {
                    $q->where('user_id', $currentUserId)
                        ->whereHas('receipt', function ($r) {
                            $r->where('status', 1);
                        });
                }
            ])
            ->where('is_hidden', 0); // Đã bỏ ->latest() để có thể sort theo kiểu khác

        // Bỏ bài đã "không quan tâm"
        $ignoredPostIds = DB::table('tbl_post_ignores')
            ->where('user_id', $currentUserId)
            ->pluck('post_id')
            ->toArray();

        if (!empty($ignoredPostIds)) {
            $query->whereNotIn('id', $ignoredPostIds);
        }

        // Bỏ bài user đã ẩn (tbl_hidePost)
        $hiddenPostIds = DB::table('tbl_hidePost')
            ->where('user_id', $currentUserId)
            ->pluck('post_id')
            ->toArray();

        if (!empty($hiddenPostIds)) {
            $query->whereNotIn('id', $hiddenPostIds);
        }
        $blockedUsers = DB::table('tbl_blocked_users')
            ->where('user_id', $currentUserId)
            ->pluck('blocked_id')
            ->toArray();

        $blockedByUsers = DB::table('tbl_blocked_users')
            ->where('blocked_id', $currentUserId)
            ->pluck('user_id')
            ->toArray();
        $query->whereNotIn('user_id', $blockedUsers)  // Loại bỏ những người đã chặn
            ->whereNotIn('user_id', $blockedByUsers);  // Loại bỏ những người đã chặn mình    
        // Lọc theo user cụ thể
        if ($request->filled('user')) {
            $referral_code = ltrim($request->input('user'), '@');
            $query->whereHas('author', function ($q) use ($referral_code) {
                $q->where('referral_code', $referral_code);
            });
        }

        // Xử lý type (0: tất cả, 1: phổ biến, 2: gần đây, 3: của tôi tạo)
        if ($type === 1) {
            $query->orderByDesc('likes_count')
                  ->orderByDesc('comments_count')
                  ->orderByDesc('created_at');
        } elseif ($type === 2) {
            $query->orderByDesc('created_at');
        } elseif ($type === 3) {
            $query->where('user_id', $currentUserId)
                  ->orderByDesc('created_at');
        } else {
            // type = 0 (tất cả)
            $query->orderByDesc('created_at');
        }

        $total = (clone $query)->count();
        $posts = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($post) {
                if ($post->author) {
                    $avatar = $post->author->avatar;

                    if (!empty($avatar) && !filter_var($avatar, FILTER_VALIDATE_URL)) {
                        $avatar = $this->baseUrl.'/'.($avatar);
                    }

                    if (empty($avatar)) {
                        $avatar = NULL;
                    }

                    $post->author->avatar =   $avatar;
                }
                
                if ($post->media) {
                    $post->media->map(function ($media) {
                        if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                            $media->media_url = $this->baseUrl.'/'. $media->media_url;
                        }
                        return $media;
                    });
                }
                
                $post->watching = (bool) $post->watching;
                $post->is_noti   = (bool) $post->is_noti;
                $post->saved = (bool) $post->saved;
                $post->reported = (bool) $post->reported;
                $post->stars_count = (int) $post->stars_count;
                return $post;
            });

        $hasMore = $total > $page * $perPage;

        return response()->json([
            'data' => $posts,
            'has_more' => $hasMore,
            'next_page' => $page + 1
        ]);
    }

    public function getReplyOptions()
    {
        return response()->json([
            'success' => true,
            'data' => Post::getReplyOptions(),
        ]);
    }
    public function hidePost(Request $request)
    {
        $userId = $this->request->client->id ?? 0;
        
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer|exists:tbl_posts,id',
        ], [
            'post_id.required' => 'Mã bài viết là bắt buộc.',
            'post_id.integer' => 'Mã bài viết phải là số nguyên.',
            'post_id.exists' => 'Bài viết không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->all()[0]
            ]);
        }
        
        $postId = (int)$request->input('post_id');

        if ($userId <= 0) {
            return response()->json([
                'result' => false,
                'message' => 'Dữ liệu không hợp lệ.'
            ]);
        }

        // Kiểm tra đã ẩn chưa
        $existing = DB::table('tbl_hidePost')
            ->where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();

        if ($existing) {
            // Đã ẩn rồi -> bỏ ẩn
            DB::table('tbl_hidePost')
                ->where('user_id', $userId)
                ->where('post_id', $postId)
                ->delete();

            $this->logAction($userId, 'unhide', 'post', $postId);

            return response()->json([
                'result' => true,
                'message' => 'Đã bỏ ẩn bài viết.'
            ]);
        } else {
            // Chưa ẩn -> ẩn
            DB::table('tbl_hidePost')->insert([
                'user_id' => $userId,
                'post_id' => $postId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->logAction($userId, 'hide', 'post', $postId);

            return response()->json([
                'result' => true,
                'message' => 'Đã ẩn bài viết.'
            ]);
        }
    }
    public function destroy(Request $request, $id)
    {
        $post = Post::with('media')->find($id);

        if (!$post) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bài viết.'
            ], 404);
        }

        // ✅ Kiểm tra quyền xoá (chỉ chủ bài viết)
        if ($post->user_id != ($this->request->client->id ?? 0)) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không có quyền xoá bài viết này.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // ✅ Xoá file media vật lý + bản ghi DB
            foreach ($post->media as $media) {
                $mediaPath = str_replace([asset('storage/') . '/', 'storage/'], '', $media->media_url);
                Storage::disk('public')->delete($mediaPath);
            }

            // ✅ Xoá quan hệ tag và topic
            DB::table('tbl_post_tags')->where('post_id', $post->id)->delete();
            DB::table('tbl_posts_toppic')->where('post_id', $post->id)->delete();
            DB::table('tbl_post_media')->where('post_id', $post->id)->delete();

            // ✅ Xoá bài viết
            $post->delete();

            DB::commit();

            // ✅ Ghi log, bắn socket nếu cần
            $this->logAction($post->user_id, 'delete', 'post', $post->id);
            SocketHelpers::sendSocketDeleteNewsfeed($post->user_id, $post->id);

            return response()->json([
                'result' => true,
                'message' => 'Xoá bài viết thành công.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $dataResult = [
            'result' => false,
        ];
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'visibility' => 'in:everyone,friends,justme',
            'tagged_users' => 'array',
            'tagged_users.*' => 'integer|exists:tbl_clients,id',
            'topic' => 'array',
            'topic.*' => 'integer|exists:tbl_topic,id',
            'media.*' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,mp4,avi,mov',
                'max:102400', // 100MB
                function ($attribute, $value, $fail) use ($request) {
                    $files = $request->file('media', []);

                    // Đếm số ảnh và video
                    $imageCount = 0;
                    $videoCount = 0;
                    foreach ($files as $file) {
                        if (str_starts_with($file->getMimeType(), 'image')) {
                            $imageCount++;
                        } elseif (str_starts_with($file->getMimeType(), 'video')) {
                            $videoCount++;
                        }
                    }

                    // Giới hạn số lượng
                    if ($imageCount > 10) {
                        $fail('Bạn chỉ được tải tối đa 10 hình ảnh.');
                        return;
                    }
                    if ($videoCount > 1) {
                        $fail('Bạn chỉ được tải tối đa 1 video.');
                        return;
                    }
                }
            ]
        ], [
            'content.required' => 'Bạn chưa nhập nội dung bài viết.',
            'content.string' => 'Nội dung phải là chuỗi.',
            'visibility.in' => 'Trường chế độ hiển thị không hợp lệ.',
            'tagged_users.array' => 'Danh sách người được gắn thẻ không hợp lệ.',
            'tagged_users.*.integer' => 'ID người được gắn thẻ phải là số nguyên.',
            'tagged_users.*.exists' => 'Người được gắn thẻ không tồn tại.',
            'topic.array' => 'Danh sách chủ đề không hợp lệ.',
            'topic.*.integer' => 'ID chủ đề phải là số nguyên.',
            'topic.*.exists' => 'Chủ đề không tồn tại.',
            'media.*.file' => 'Tệp tải lên không hợp lệ.',
            'media.*.mimes' => 'Định dạng tệp không được hỗ trợ.',
            'media.*.max' => 'Dung lượng tệp tối đa là 100MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => 0,
                'message' => $validator->errors()->all()[0]
            ]);
        }

        $topic = $request->input('topic');
        $post = Post::create([
            'user_id' => !empty($this->request->client) ? $this->request->client->id : 0,
            'content' => $request->input('content'),
            'visibility' => $request->input('visibility', 'everyone'),
        ]);
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('posts', 'public'); // lưu vào storage/app/public/posts
                $post->media()->create([
                    'media_url' => $path,
                    'media_type' => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image',
                ]);
            }
        }

        // lưu topic nếu có
        if ($request->filled('topic')) {
            foreach ($request->input('topic') as $topicId) {
                DB::table('tbl_posts_toppic')->insert([
                    'user_id' => $post->user_id,
                    'post_id' => $post->id,
                    'toppic_id' => $topicId
                ]);
            }
        }
        if ($request->filled('tagged_users')) {
            $tags = [];
            foreach ($request->input('tagged_users') as $taggedUserId) {
                $tags[] = [
                    'post_id' => $post->id,
                    'user_id'    => $taggedUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('tbl_post_tags')->insert($tags);
        }
        $this->logAction($post->user_id, 'create', 'post', $post->id);
        SocketHelpers::sendSocketloadNewsfeed($post->user_id, $post->id);
        // NotificationHelper::notifyPostCreated($post);
        $post->load('media');
        if ($post->media) {
            $post->media->map(function ($media) {
                if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                    $media->media_url = $this->baseUrl . '/' . $media->media_url;
                }
                return $media;
            });
        }

        return response()->json([
            'result'  => true,
            'message' => 'Đăng bài thành công.',
            'data'    => $post,
        ]);
    }
    public function updatePost(Request $request, $id)
    {
        $dataResult = ['result' => false];

        $request->validate([
            'content' => 'required|string',
            'visibility' => 'in:everyone,friends,justme',
            'tagged_users' => 'array',
            'tagged_users.*' => 'integer|exists:tbl_clients,id',
            'topic' => 'array',
            'topic.*' => 'integer|exists:tbl_topic,id',
            'media_existing' => 'array',
            'media_existing.*' => 'integer|exists:tbl_post_media,id',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,avi,mov|max:51200'
        ]);

        $post = Post::with('media')->find($id);

        if (!$post) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bài viết.'
            ], 404);
        }

        // ✅ Chỉ cho phép sửa bài viết của chính mình
        if ($post->user_id != ($this->request->client->id ?? 0)) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không có quyền sửa bài viết này.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // ✅ Cập nhật nội dung, quyền hiển thị, tăng số lần chỉnh sửa
            $post->update([
                'content' => $request->input('content'),
                'visibility' => $request->input('visibility', 'everyone'),
                'count_edit' => DB::raw('count_edit + 1'),
            ]);

            // ✅ Cập nhật topic
            DB::table('tbl_posts_toppic')->where('post_id', $post->id)->delete();
            foreach ($request->input('topic', []) as $topicId) {
                DB::table('tbl_posts_toppic')->insert([
                    'user_id' => $post->user_id,
                    'post_id' => $post->id,
                    'toppic_id' => $topicId
                ]);
            }

            // ✅ Cập nhật tag người dùng
            DB::table('tbl_post_tags')->where('post_id', $post->id)->delete();
            $tags = [];
            foreach ($request->input('tagged_users', []) as $taggedUserId) {
                $tags[] = [
                    'post_id' => $post->id,
                    'user_id' => $taggedUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($tags)) {
                DB::table('tbl_post_tags')->insert($tags);
            }

            // ✅ Xử lý media: xoá cái không còn, giữ cái còn, thêm cái mới
            $mediaIdsToKeep = $request->input('media_existing', []);
            $mediaToDelete = $post->media()->when(!empty($mediaIdsToKeep), function ($q) use ($mediaIdsToKeep) {
                $q->whereNotIn('id', $mediaIdsToKeep);
            })->get();

            foreach ($mediaToDelete as $media) {
                $mediaPath = str_replace([asset('storage/') . '/', 'storage/'], '', $media->media_url);
                Storage::disk('public')->delete($mediaPath);
                $media->delete();
            }

            // ✅ Thêm media mới (nếu có)
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = $file->store('posts', 'public');
                    $post->media()->create([
                        'media_url' => $path,
                        'media_type' => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image',
                    ]);
                }
            }

            DB::commit();

            // ✅ Ghi log, socket, notification
            $this->logAction($post->user_id, 'update', 'post', $post->id);
            // sendSocketloadNewsfeed($post->id);
            // NotificationHelper::notifyPostUpdated($post); // Đảm bảo bạn có hàm này

            $post->load('media');
            if ($post->media) {
                $post->media->map(function ($media) {
                    if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                        $media->media_url = $this->baseUrl . '/' . $media->media_url;
                    }
                    return $media;
                });
            }

            return response()->json([
                'result'  => true,
                'message' => 'Cập nhật bài viết thành công.',
                'data'    => $post,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function like($id)
    {
        $userId = $this->request->client->id ?? 0;
        $post = Post::findOrFail($id);
        PostLike::firstOrCreate([
            'post_id' => $id,
            'user_id' => !empty($this->request->client) ? $this->request->client->id : 0
        ]);
        $this->logAction($userId, 'like', 'post', $id);
        SocketHelpers::sendSocketPost($userId, $id, 'like');
        if($userId != $post->user_id){
            NotificationHelper::notifyLike($post, $userId,$post->user_id);
        }
        return response()->json(['status' => true]);
    }

    public function unlike($id)
    {
        $userId = $this->request->client->id ?? 0;

        PostLike::where('post_id', $id)
            ->where('user_id', $userId)
            ->delete();

        $this->logAction($userId, 'unlike', 'post', $id);
        SocketHelpers::sendSocketPost($userId, $id, 'unlike');

        return response()->json(['status' => true]);
    }
    public function ignore(Request $request)
    {
        $userId = $this->request->client->id ?? 0;
        $postId = (int) $request->input('post_id');

        DB::table('tbl_post_ignores')->updateOrInsert(
            ['user_id' => $userId, 'post_id' => $postId],
            ['created_at' => now()]
        );

        $this->logAction($userId, 'ignore', 'post', $postId);

        return response()->json(['result' => true, 'message' => 'Đã không quan tâm bài viết']);
    }
    public function unignore(Request $request)
    {
        $userId = $this->request->client->id ?? 0;
        $postId = (int) $request->input('post_id');

        DB::table('tbl_post_ignores')
            ->where('user_id', $userId)
            ->where('post_id', $postId)
            ->delete();

        $this->logAction($userId, 'unignore', 'post', $postId);

        return response()->json(['result' => true, 'message' => 'Đã bỏ không quan tâm bài viết']);
    }
    public function toggleWatch(Request $request)
    {
        $userId = $this->request->client->id ?? 0;
        $postId = (int) $request->input('post_id');
        $active = (int) $request->input('active', 1); // 1: nhận, 0: không nhận

        if ($userId === 0 || $postId === 0) {
            return response()->json(['result' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }

        DB::table('tbl_post_watchers')->updateOrInsert(
            [
                'user_id' => $userId,
                'post_id' => $postId,
            ],
            [
                'active' => $active,
                'created_at' => now(),
            ]
        );
        $this->logAction(
            $userId,
            $active ? 'watch' : 'unwatch',
            'post',
            $postId
        );
        return response()->json([
            'result' => true,
            'message' => $active ? 'Đã theo dõi bài viết.' : 'Đã tắt thông báo bài viết.'
        ]);
    }
    public function toggleFollow(Request $request)
    {
        $userId = $this->request->client->id ?? 0;
        $targetId = (int) $request->input('user_id');
        $active = (int) $request->input('active', 1); // 1: theo dõi, 0: bỏ theo dõi

        if ($userId === 0 || $targetId === 0 || $userId === $targetId) {
            return response()->json(['result' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }

        if ($active) {
            // Thêm theo dõi nếu chưa có
            DB::table('tbl_friends')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'friend_id' => $targetId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            NotificationHelper::notifyFollowed($userId, $targetId);
        } else {
            // Xoá
            DB::table('tbl_friends')
                ->where('user_id', $userId)
                ->where('friend_id', $targetId)
                ->delete();
        }

        $this->logAction(
            $userId,
            $active ? 'follow' : 'unfollow',
            'user',
            $targetId
        );

        return response()->json([
            'result' => true,
            'message' => $active ? 'Đã theo dõi người dùng.' : 'Đã bỏ theo dõi người dùng.'
        ]);
    }

    public function save(Request $request)
    {
        $userId = $this->request->client->id ?? 0;
        $postId = (int) $request->input('post_id');
        $active = (int) $request->input('active', 1); // 1: nhận, 0: không nhận

        if ($userId === 0 || $postId === 0) {
            return response()->json(['result' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }
        if ($active == 1) {
            DB::table('tbl_post_saved')->updateOrInsert(
                ['user_id' => $userId, 'post_id' => $postId],
                ['created_at' => now()]
            );

            $this->logAction($userId, 'save', 'post', $postId);
            // SocketHelpers::sendSocketPostIsSave($userId, $postId, 'is_save');

            return response()->json(['result' => true, 'message' => 'Đã đăng lại bài viết']);
        } else {
            DB::table('tbl_post_saved')
                ->where('user_id', $userId)
                ->where('post_id', $postId)
                ->delete();

            $this->logAction($userId, 'unsave', 'post', $postId);
            // SocketHelpers::sendSocketPostIsSave($userId, $postId, 'is_save');

            return response()->json(['result' => true, 'message' => 'Đã bỏ đăng lại bài viết']);
        }
    }
    public function getReportViolation()
    {
        $dtReportViolation = ReportViolationMode::select('id', 'name')->get();
        $data['data'] = $dtReportViolation;
        return response()->json($data);
    }
    public function report(Request $request)
    {
        $userId = $this->request->client->id ?? 0;

        $validated = $request->validate([
            'post_id' => 'required|integer|exists:tbl_posts,id',
            'violation_id' => 'required|integer|exists:tbl_reportviolation,id',
            'note' => 'nullable|string|max:1000'
        ]);

        $report = PostReportMode::create([
            'post_id' => $validated['post_id'],
            'user_id' => $userId,
            'violation_id' => $validated['violation_id'],
            'note' => $validated['note'] ?? null,
            'type' => 'post'
        ]);

        // optional: ghi log
        $this->logAction($userId, 'report', 'post', $validated['post_id']);

        return response()->json([
            'result' => true,
            'message' => 'Đã gửi báo cáo vi phạm.',
            'data' => $report
        ]);
    }   
    public function blockUser(Request $request) {
        $userId = $this->request->client->id ?? 0;
        $blockedId = (int) $request->input('user_id');
        $active = (int) $request->input('active', 1);

        if ($userId === 0 || $blockedId === 0 || $userId === $blockedId) {
            return response()->json(['result' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }

        if ($active == 1) {
            DB::table('tbl_blocked_users')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'blocked_id' => $blockedId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $message = 'Đã chặn người dùng.';
        } else {
            DB::table('tbl_blocked_users')
                ->where('user_id', $userId)
                ->where('blocked_id', $blockedId)
                ->delete();
            $message = 'Đã bỏ chặn người dùng.';
        }

        $this->logAction($userId, $active ? 'block' : 'unblock', 'user', $blockedId);

        return response()->json([
            'result' => true,
            'message' => $message
        ]);
    }
    public function giveStar(Request $request)
    {
        $userId = $this->request->client->id ?? 0;
        $postId = (int) $request->input('post_id');
        $amount = get_option('star_fee');
        if ($userId <= 0 || $postId <= 0) {
            return response()->json(['result' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }

        // kiểm tra đã có & đã thanh toán chưa
        $existing = DB::table('tbl_post_stars AS ps')
            ->join('tbl_receipts AS r', 'r.reference_id', '=', 'ps.id')
            ->where('ps.user_id', $userId)
            ->where('ps.post_id', $postId)
            ->where('r.type', 'post_star')
            ->where('r.status', 1)
            ->first();

        if ($existing) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn đã tặng sao bài viết này rồi.'
            ]);
        }

        DB::beginTransaction();
        try {
            // tạo tbl_post_stars
            $postStarId = DB::table('tbl_post_stars')->insertGetId([
                'user_id' => $userId,
                'post_id' => $postId,
                'stars' => 1,
                'amount' => 9000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $reference_no  = getReferenceTransaction('payment', 'PT');

            // tạo tbl_receipts
            DB::table('tbl_receipts')->insert([
                'user_id' => $userId,
                'reference_no' => $reference_no,
                'amount' => $amount,
                'type' => 'post_star',
                'reference_id' => $postStarId,
                'status' => 1, // chưa xác nhận
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            updateReference('payment');
            DB::commit();
            $post = Post::find($postId);
            if ($post) {
                // NotificationHelper::notifyStar($post, $userId);
            }
            // SocketHelpers::sendSocketPost($userId, $postId, 'giveStar');
            $data['accountName'] = get_option('account_name_bank');
            $data['accountNumber'] = get_option('account_number_bank');
            $data['bankShortName'] = get_option('bank_short_bank');
            $data['bank'] = [
                'account_bank' => get_option('account_name_bank'),
                'account_name' => get_option('account_name_short_bank'),
                'account_name_long' => get_option('account_name_long_bank'),
                'account_number' => get_option('account_number_bank'),
                'amount' => ceil($amount),
                'logo_bank' => asset(get_option('image_bank')),
                'note' => $reference_no,
            ];
            $data['qr'] = createQrBank([
                'amount' => ceil($amount),
                'memo' => $reference_no,
            ]);
            $data['result'] = true;
            $data['message'] = 'Tạo yêu cầu tặng sao thành công.';
            return response()->json($data);
        } catch (\Throwable $e) {
            DB::rollBack();
            // \Log::error('Error giveStar: ' . $e->getMessage());
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại.'
            ]);
        }
    }
    public function show($id)
    {
        $userId = $this->request->client->id ?? 0;

        $post = Post::with([
            'author:id,fullname,referral_code,avatar',
            'media',
            'likes.user:id,fullname,referral_code,avatar',
            'postStars',
            'saveds',
            'comments' => function ($q) use ($userId) {
                $q->whereNull('parent_id')
                    ->with([
                        'author:id,fullname,referral_code,avatar',
                        'media'
                    ])
                    ->withCount('likes')
                    ->withExists([
                        'likes as liked_by_me' => function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        }
                    ])
                    ->withCount('replies')
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc'); // đảm bảo ổn định khi cùng thời điểm
            }
        ])
            ->withCount([
                'likes',
                'postStars as stars_count' => function ($q) {
                    $q->whereHas('receipt', function ($r) {
                        $r->where('status', 1);
                    });
                },
                'saveds',
                'comments as comments_count' => function ($q) {
                    $q->whereNull('parent_id');
                }
            ])
            ->withExists([
                'likes as liked_by_me' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },
                'saveds as saved_by_me' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },
                'saveds as saved' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },
                'postStars as starred' => function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->whereHas('receipt', fn($r) => $r->where('status', 1));
                }
            ])
            ->findOrFail($id);
        if ($userId > 0) {
            DB::table('tbl_post_reads')->updateOrInsert(
                ['user_id' => $userId, 'post_id' => $id],
                ['read_at' => now(), 'created_at' => now()]
            );
        }
        if ($post->media) {
            $post->media->map(function ($media) {
                if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                    $media->media_url = $this->baseUrl.'/'. $media->media_url;
                }
                return $media;
            });
        }

        if ($post->author) {
            $post->author->is_me = ($post->author->id == $userId);
            $post->author->avatar = !empty($post->author->avatar) ? $this->baseUrl.'/'. ($post->author->avatar) : NULL;

            if (!$post->author->is_me) {
                $post->author->is_following = DB::table('tbl_friends')
                    ->where('user_id', $userId)
                    ->where('friend_id', $post->author->id)
                    ->exists();

                $post->author->is_follower = DB::table('tbl_friends')
                    ->where('user_id', $post->author->id)
                    ->where('friend_id', $userId)
                    ->exists();
            } else {
                $post->author->is_following = false;
                $post->author->is_follower = false;
            }
        }

        foreach ($post->comments as $comment) {
            if ($comment->author && $comment->author->avatar) {
                $comment->author->avatar = $this->baseUrl.'/'. $comment->author->avatar;
            }

            if ($comment->media) {
                foreach ($comment->media as $media) {
                    if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                        $media->media_url = $this->baseUrl.'/'. $media->media_url;
                    }
                }
            }

            // Load replies thủ công, tối đa 4 cái
            $comment->replies = \App\Models\Comment::where('parent_id', $comment->id)
                ->with([
                    'author:id,fullname,referral_code,avatar',
                    'media'
                ])
                ->withCount('likes')
                ->withExists([
                    'likes as liked_by_me' => function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    }
                ])
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->take(10)
                ->get();

            foreach ($comment->replies as $reply) {
                if ($reply->author && $reply->author->avatar) {
                    $reply->author->avatar = asset($reply->author->avatar);
                }

                if ($reply->media) {
                    foreach ($reply->media as $media) {
                        if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                            $media->media_url = $this->baseUrl.'/'. $media->media_url;
                        }
                    }
                }
            }
        }

        $post->watching      = (bool) $post->watching;
        $post->is_noti       = (bool) $post->is_noti;
        $post->liked_by_me   = (bool) $post->liked_by_me;
        $post->saved_by_me   = (bool) $post->saved_by_me;
        $post->starred_by_me = (bool) $post->starred_by_me;
        $canComment = false;
        if ($userId > 0) {
            $canComment = true;
        }

        switch ($post->visibility) {
            case 'everyone':
                $canComment = true;
                break;
            case 'following':
                // Chỉ cần check flag, không cần DB::table()->exists() nữa
                $canComment = (bool)($post->author->is_following ?? false);
                break;
            case 'followers':
                $canComment = (bool)($post->author->is_follower ?? false);
                break;
        }
        if ($post->author->is_me) {
            $canComment = true;
        }
        $post->isComment = (bool)$canComment;
        // Lấy 3 người đã like
        $post->liked_users = $post->likes
            ->pluck('user')
            ->filter()
            ->map(function ($user) {
                $user->avatar = $user->avatar ? asset($user->avatar) : NULL;
                return $user;
            })
            ->take(3)
            ->values();

        unset($post->likes);
        $isBlocked = DB::table('tbl_blocked_users')
            ->where(function ($q) use ($userId, $post) {
                $q->where('user_id', $userId)->where('blocked_id', $post->user_id)
                    ->orWhere('user_id', $post->user_id)->where('blocked_id', $userId);
            })
            ->exists();

        if ($isBlocked) {
            return response()->json([
                'result' => false,
                'message' => 'Bạn không thể xem bài viết này.'
            ], 403);
        }
        return response()->json([
            'data' => $post
        ]);
    }
    public function getReplies($id)
    {
        $userId = $this->request->client->id ?? 0;

        $comment = Comment::findOrFail($id);
        $page = request('page', 1);
        $perPage = 3;

        $query = $comment->replies()->with('author:id,fullname,referral_code,avatar')->withCount('likes')
            ->withExists([
                'likes as liked_by_me' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ])->latest();

        $total = $query->count();
        $replies = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
        foreach ($replies as $comment) {
            if ($comment->author && $comment->author->avatar) {
                $comment->author->avatar = asset($comment->author->avatar);
            }

            if ($comment->media) {
                foreach ($comment->media as $media) {
                    if (!empty($media->media_url)) {
                        $media->media_url = $this->baseUrl.'/'. $media->media_url;
                    }
                }
            }
        }
        $remaining = $total - ($page * $perPage);
        return response()->json([
            'replies' => $replies,
            'has_more' => $remaining > 0,
            'remaining' => $remaining,
            'next_page' => $page + 1,
        ]);
    }
    public function getComments($id)
    {
        $userId = $this->request->client->id ?? 0;

        $post = Post::findOrFail($id);

        $page = max(1, request('page', 1)); // an toàn
        $perPage = 3;

        $query = $post->comments()
            ->whereNull('parent_id')
            ->whereNotIn('user_id', function ($q) use ($userId) {
                $q->select('blocked_id')->from('tbl_blocked_users')->where('user_id', $userId);
            })
            ->whereNotIn('user_id', function ($q) use ($userId) {
                $q->select('user_id')->from('tbl_blocked_users')->where('blocked_id', $userId);
            });

        $total = $query->count();

        $comments = $query
            ->with([
                'author:id,fullname,referral_code,avatar',
                'media', // 👈 thêm media cho comment cha
                'replies' => function ($q) use ($userId) {
                    $q->with([
                        'author:id,fullname,referral_code,avatar',
                        'media'
                    ])
                        ->whereNotIn('user_id', function ($q) use ($userId) {
                            $q->select('blocked_id')->from('tbl_blocked_users')->where('user_id', $userId);
                        })
                        ->whereNotIn('user_id', function ($q) use ($userId) {
                            $q->select('user_id')->from('tbl_blocked_users')->where('blocked_id', $userId);
                        })
                        ->latest()
                        ->take(3);
                }
            ])->withCount('likes')
            ->withExists([
                'likes as liked_by_me' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ])
            ->latest()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        foreach ($comments as $comment) {
            if ($comment->author && $comment->author->avatar) {
                $comment->author->avatar = asset($comment->author->avatar);
            }

            if ($comment->media) {
                foreach ($comment->media as $media) {
                    if (!empty($media->media_url)) {
                        $media->media_url = $this->baseUrl.'/'. $media->media_url;
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
                                $media->media_url = $this->baseUrl.'/'. $media->media_url;
                            }
                        }
                    }
                }
            }
        }
        $remaining = max(0, $total - ($page * $perPage));

        return response()->json([
            'comments'   => $comments,
            'has_more'   => $remaining > 0,
            'remaining'  => $remaining,
            'total'      => $total,
            'next_page'  => $page + 1,
            'per_page'   => $perPage,
        ]);
    }
    public function getPostLikes(Request $request, $postId)
    {
        $viewerId = $this->request->client->id ?? 0;
        $search = trim($request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 5;

        $post = Post::findOrFail($postId);

        $query = DB::table('tbl_post_likes as l')
            ->join('tbl_clients as u', 'l.user_id', '=', 'u.id')
            ->where('l.post_id', $post->id)
            ->select(
                'u.id',
                'u.fullname',
                'u.referral_code',
                'u.avatar',
                'u.is_verified',
                'l.created_at as liked_at'
            );
        $query->whereNotIn('u.id', function ($q) use ($viewerId) {
            $q->select('blocked_id')->from('tbl_blocked_users')->where('user_id', $viewerId);
        })
            ->whereNotIn('u.id', function ($q) use ($viewerId) {
                $q->select('user_id')->from('tbl_blocked_users')->where('blocked_id', $viewerId);
            });
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('u.fullname', 'like', "%{$search}%")
                    ->orWhere('u.referral_code', 'like', "%{$search}%");
            });
        }

        // Phân trang với page & perPage
        $users = $query->orderByDesc('l.created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        // lấy danh sách friend_id để check is_following
        $friendIds = DB::table('tbl_friends')
            ->where('user_id', $viewerId)
            ->whereIn('friend_id', $users->pluck('id'))
            ->pluck('friend_id')
            ->toArray();

        $users->getCollection()->transform(function ($u) use ($viewerId, $friendIds) {
            $u->avatar = $u->avatar ? asset($u->avatar) : NULL;
            $u->is_me = $viewerId == $u->id;
            $u->is_following = in_array($u->id, $friendIds);
            return $u;
        });

        return response()->json([
            'result'       => true,
            'data'         => $users->items(),
            'current_page' => $users->currentPage(),
            'per_page'     => $users->perPage(),
            'last_page'    => $users->lastPage(),
            'has_more'     => $users->hasMorePages()
        ]);
    }

    /**
     * Admin: Danh sách bài viết cộng đồng (DataTables)
     */
    public function getListAdmin(Request $request)
    {
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        // DataTables gửi search dạng {value: "", regex: false}
        $searchInput = $request->input('search', '');
        $search = is_array($searchInput) ? ($searchInput['value'] ?? '') : $searchInput;
        $dateSearch = $request->input('date_search', '');
        $customerSearch = $request->input('customer_search', '');
        $statusSearch = $request->input('status_search', '-1');

        $query = Post::with([
            'author:id,fullname,referral_code,avatar,phone',
            'media'
        ])
            ->withCount([
                'comments as comments_count' => function ($q) {
                    $q->whereNull('parent_id');
                },
                'likes',
                'postStars as stars_count' => function ($q) {
                    $q->whereHas('receipt', function ($r) {
                        $r->where('status', 1);
                    });
                },
            ]);

        // Tìm kiếm theo nội dung hoặc tên tác giả
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('author', function ($q2) use ($search) {
                        $q2->where('fullname', 'like', "%{$search}%")
                            ->orWhere('referral_code', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Lọc theo khách hàng
        if (!empty($customerSearch)) {
            $query->where('user_id', $customerSearch);
        }

        // Lọc theo ngày
        if (!empty($dateSearch)) {
            $dates = explode(' - ', $dateSearch);
            if (count($dates) == 2) {
                $query->whereBetween('created_at', [
                    trim($dates[0]) . ' 00:00:00',
                    trim($dates[1]) . ' 23:59:59'
                ]);
            }
        }

        // Lọc theo trạng thái: 0 = hiển thị, 1 = đã ẩn
        if ($statusSearch !== '-1' && $statusSearch !== '' && $statusSearch !== null) {
            $query->where('is_hidden', (int) $statusSearch);
        }

        $total = (clone $query)->count();

        $posts = $query->orderByDesc('created_at')
            ->skip($start)
            ->take($length)
            ->get()
            ->map(function ($post) {
                if ($post->author && $post->author->avatar) {
                    if (!filter_var($post->author->avatar, FILTER_VALIDATE_URL)) {
                        $post->author->avatar = $this->baseUrl . '/' . $post->author->avatar;
                    }
                }
                $post->author_new = $post->author ? [
                    'id' => $post->author->id,
                    'fullname' => $post->author->fullname,
                    'referral_code' => $post->author->referral_code,
                    'avatar_new' => $post->author->avatar,
                    'phone' => $post->author->phone ?? '',
                ] : null;

                if ($post->media) {
                    $post->media->map(function ($media) {
                        if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                            $media->media_url = $this->baseUrl . '/' . $media->media_url;
                        }
                        return $media;
                    });
                }

                return $post;
            });

        return response()->json([
            'result' => true,
            'total' => $total,
            'filtered' => $total,
            'data' => $posts,
            'message' => 'Success'
        ]);
    }

    /**
     * Admin: Chi tiết bài viết cộng đồng
     */
    public function getDetailAdmin($id)
    {
        $post = Post::with([
            'author:id,fullname,referral_code,avatar,phone,email',
            'media',
            'comments' => function ($q) {
                $q->whereNull('parent_id')
                    ->with([
                        'author:id,fullname,referral_code,avatar',
                        'media',
                        'replies' => function ($q2) {
                            $q2->with([
                                'author:id,fullname,referral_code,avatar',
                                'media'
                            ])->latest()->take(5);
                        }
                    ])
                    ->withCount('likes')
                    ->withCount('replies')
                    ->latest();
            },
            'reports' => function ($q) {
                $q->with([
                    'user:id,fullname,avatar',
                    'violation:id,name'
                ])->latest();
            }
        ])
            ->withCount([
                'likes',
                'comments as comments_count' => function ($q) {
                    $q->whereNull('parent_id');
                },
                'postStars as stars_count' => function ($q) {
                    $q->whereHas('receipt', function ($r) {
                        $r->where('status', 1);
                    });
                },
            ])
            ->find($id);

        if (!$post) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bài viết.',
                'data' => []
            ]);
        }

        // Format URLs
        if ($post->author && $post->author->avatar) {
            if (!filter_var($post->author->avatar, FILTER_VALIDATE_URL)) {
                $post->author->avatar =  $this->baseUrl . '/' . $post->author->avatar;
            }
        }

        if ($post->media) {
            $post->media->map(function ($media) {
                if (!empty($media->media_url) && !filter_var($media->media_url, FILTER_VALIDATE_URL)) {
                    $media->media_url = $this->baseUrl . '/' . $media->media_url;
                }
                return $media;
            });
        }

        foreach ($post->comments as $comment) {
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
            if ($comment->replies) {
                foreach ($comment->replies as $reply) {
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
        }

        return response()->json([
            'result' => true,
            'data' => $post,
            'message' => 'Success'
        ]);
    }

    /**
     * Admin: Đếm bài viết cộng đồng
     */
    public function countAllAdmin(Request $request)
    {
        $all = Post::count();

        $arr = Post::selectRaw('is_hidden as status, COUNT(*) as count')
            ->groupBy('is_hidden')
            ->get()
            ->toArray();

        return response()->json([
            'result' => true,
            'all' => $all,
            'arr' => $arr,
            'message' => 'Success'
        ]);
    }

    /**
     * Admin: Xoá bài viết cộng đồng
     */
    public function deleteAdmin(Request $request)
    {
        $id = $request->input('id');
        $post = Post::with('media')->find($id);

        if (!$post) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bài viết.'
            ], 404);
        }

        DB::beginTransaction();
        try {
            foreach ($post->media as $media) {
                $mediaPath = str_replace([asset('storage/') . '/', 'storage/'], '', $media->media_url);
                Storage::disk('public')->delete($mediaPath);
            }

            DB::table('tbl_post_tags')->where('post_id', $post->id)->delete();
            DB::table('tbl_posts_toppic')->where('post_id', $post->id)->delete();
            DB::table('tbl_post_media')->where('post_id', $post->id)->delete();

            $post->delete();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Xoá bài viết thành công.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Ẩn/hiện bài viết
     */
    public function toggleHideAdmin(Request $request)
    {
        $id = $request->input('id');
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bài viết.'
            ], 404);
        }

        $post->is_hidden = $post->is_hidden ? 0 : 1;
        $post->save();

        return response()->json([
            'result' => true,
            'message' => $post->is_hidden ? 'Đã ẩn bài viết.' : 'Đã hiện bài viết.'
        ]);
    }

    /**
     * Admin: Danh sách báo cáo (bài viết + bình luận)
     */
    public function getReportsAdmin(Request $request)
    {
        $type        = $request->input('type', '');       // 'post' | 'comment' | ''
        $violationId = $request->input('violation_id', '');
        $search      = $request->input('search', '');
        if (is_array($search)) $search = $search['value'] ?? '';
        $dateSearch  = $request->input('date_search', '');
        $start       = (int) $request->input('start', 0);
        $length      = (int) $request->input('length', 20);

        // ---------- BÁO CÁO BÀI VIẾT ----------
        $postQuery = DB::table('tbl_post_reports as r')
            ->join('tbl_posts as p', 'r.post_id', '=', 'p.id')
            ->leftJoin('tbl_clients as u', 'r.user_id', '=', 'u.id')
            ->leftJoin('tbl_reportviolation as v', 'r.violation_id', '=', 'v.id')
            ->select(
                'r.id', DB::raw("'post' as type"),
                'r.post_id as target_id',
                DB::raw('NULL as comment_id'),
                'p.content as target_content',
                'r.note', 'r.created_at',
                'u.id as reporter_id', 'u.fullname as reporter_name', 'u.avatar as reporter_avatar',
                'v.id as violation_id', 'v.name as violation_name'
            );

        if (!empty($violationId) && $violationId != null){ 
            $postQuery->where('r.violation_id', $violationId);
        }
        if (!empty($search)) {
            $postQuery->where(function($q) use ($search) {
                $q->where('p.content', 'like', "%{$search}%")
                  ->orWhere('u.fullname', 'like', "%{$search}%");
            });
        }
        if (!empty($dateSearch)) {
            $dates = explode(' - ', $dateSearch);
            if (count($dates) == 2) {
                $postQuery->whereBetween('r.created_at', [
                    trim($dates[0]) . ' 00:00:00',
                    trim($dates[1]) . ' 23:59:59'
                ]);
            }
        }

        // ---------- BÁO CÁO BÌNH LUẬN ----------
        $commentQuery = DB::table('tbl_post_reports as r')
            ->join('tbl_comments as c', 'r.post_id', '=', 'c.id')
            ->leftJoin('tbl_clients as u', 'r.user_id', '=', 'u.id')
            ->leftJoin('tbl_reportviolation as v', 'r.violation_id', '=', 'v.id')
            ->whereNotNull('r.post_id') // same table, type discriminated by note field presence
            ->select(
                'r.id', DB::raw("'comment' as type"),
                'r.post_id as target_id',
                'r.post_id as comment_id',
                'c.content as target_content',
                'r.note', 'r.created_at',
                'u.id as reporter_id', 'u.fullname as reporter_name', 'u.avatar as reporter_avatar',
                'v.id as violation_id', 'v.name as violation_name'
            );

        // Chỉ lấy các post_id mà KHÔNG tồn tại trong tbl_posts (→ là comment_id)
        $commentQuery->whereNotExists(function($q) {
            $q->select(DB::raw(1))->from('tbl_posts')->whereRaw('tbl_posts.id = r.post_id');
        });

        if (!empty($violationId) && $violationId != null){ 
            $commentQuery->where('r.violation_id', $violationId);
        }
        if (!empty($search)) {
            $commentQuery->where(function($q) use ($search) {
                $q->where('c.content', 'like', "%{$search}%")
                  ->orWhere('u.fullname', 'like', "%{$search}%");
            });
        }
        if (!empty($dateSearch)) {
            $dates = explode(' - ', $dateSearch);
            if (count($dates) == 2) {
                $commentQuery->whereBetween('r.created_at', [
                    trim($dates[0]) . ' 00:00:00',
                    trim($dates[1]) . ' 23:59:59'
                ]);
            }
        }

        // ---------- MERGE ----------
        if ($type === 'post') {
            $union = $postQuery;
        } elseif ($type === 'comment') {
            $union = $commentQuery;
        } else {
            $union = $postQuery->union($commentQuery);
        }

        $total    = DB::table(DB::raw("({$union->toSql()}) as sub"))
                      ->mergeBindings($union)
                      ->count();

        $rows = DB::table(DB::raw("({$union->toSql()}) as sub"))
                    ->mergeBindings($union)
                    ->orderByDesc('created_at')
                    ->skip($start)->take($length)
                    ->get();

        return response()->json([
            'result'   => true,
            'total'    => $total,
            'filtered' => $total,
            'data'     => $rows,
            'message'  => 'OK'
        ]);
    }

    /**
     * Admin: Danh sách loại vi phạm
     */
    public function getViolationsAdmin()
    {
        $violations = DB::table('tbl_reportviolation')->orderBy('id')->get();
        return response()->json(['result' => true, 'data' => $violations]);
    }

    /**
     * Admin: Thêm loại vi phạm
     */
    public function storeViolationAdmin(Request $request)
    {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            return response()->json(['result' => false, 'message' => 'Tên lý do không được để trống.']);
        }
        $exists = DB::table('tbl_reportviolation')->where('name', $name)->exists();
        if ($exists) {
            return response()->json(['result' => false, 'message' => 'Lý do này đã tồn tại.']);
        }
        $id = DB::table('tbl_reportviolation')->insertGetId([
            'name'       => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['result' => true, 'message' => 'Thêm thành công.', 'id' => $id]);
    }

    /**
     * Admin: Sửa loại vi phạm
     */
    public function updateViolationAdmin(Request $request, $id)
    {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            return response()->json(['result' => false, 'message' => 'Tên lý do không được để trống.']);
        }
        $row = DB::table('tbl_reportviolation')->find($id);
        if (!$row) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy lý do vi phạm.'], 404);
        }
        DB::table('tbl_reportviolation')->where('id', $id)->update([
            'name'       => $name,
            'updated_at' => now(),
        ]);
        return response()->json(['result' => true, 'message' => 'Cập nhật thành công.']);
    }

    /**
     * Admin: Xoá loại vi phạm
     */
    public function deleteViolationAdmin(Request $request, $id)
    {
        $row = DB::table('tbl_reportviolation')->find($id);
        if (!$row) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy lý do vi phạm.'], 404);
        }
        // Kiểm tra có báo cáo nào đang dùng không
        $inUse = DB::table('tbl_post_reports')->where('violation_id', $id)->exists();
        if ($inUse) {
            return response()->json(['result' => false, 'message' => 'Không thể xoá vì đang có báo cáo sử dụng lý do này.']);
        }
        DB::table('tbl_reportviolation')->where('id', $id)->delete();
        return response()->json(['result' => true, 'message' => 'Xoá thành công.']);
    }
}
