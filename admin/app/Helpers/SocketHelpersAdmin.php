<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SocketHelpersAdmin
{

    public function getClientIdsByPost($currentUserId, $postId)
    {
        // Lấy visibility của bài viết từ bảng tbl_posts
        $post = DB::table('tbl_posts')->select('visibility')->where('id', $postId)->first();

        if (!$post) {
            return []; // Nếu không tìm thấy bài viết, trả về mảng rỗng
        }
        // Lọc những người đang theo dõi currentUserId
        $clientIds = DB::table('tbl_friends')
            ->join('tbl_clients', 'tbl_friends.user_id', '=', 'tbl_clients.id')
            ->where('tbl_friends.friend_id', $currentUserId) // Chỉ lấy những người đang theo dõi currentUserId
            ->whereNotIn('tbl_clients.id', function ($q) use ($currentUserId) {
                $q->select('blocked_id')
                    ->from('tbl_blocked_users')
                    ->where('user_id', $currentUserId);
            })
            ->whereNotIn('tbl_clients.id', function ($q) use ($currentUserId) {
                $q->select('user_id')
                    ->from('tbl_blocked_users')
                    ->where('blocked_id', $currentUserId);
            })
            ->distinct() // Đảm bảo không có client_id trùng
            ->pluck('tbl_clients.id')
            ->toArray();


        return $clientIds;
    }

    public function getClientIdsByPostToppic($currentUserId, $postId)
    {
        // Lấy visibility của bài viết từ bảng tbl_posts
        $post = DB::table('tbl_posts')
            ->select('visibility')
            ->where('id', $postId)
            ->first();

        if (!$post) {
            return []; // Nếu không tìm thấy bài viết, trả về mảng rỗng
        }

        $visibility = $post->visibility;

        // Lấy topic_id từ bảng tbl_posts_toppic
        $topicId = DB::table('tbl_posts_toppic')
            ->where('post_id', $postId)  // Tìm theo post_id
            ->value('toppic_id');  // Lấy giá trị của `toppic_id`
        if (!$topicId) {
            return []; // Nếu không có topic_id cho bài viết, trả về mảng rỗng
        }
        $clientIds = [];
        $clientIds = DB::table('tbl_clients_toppic')
            ->join('tbl_clients', 'tbl_clients_toppic.user_id', '=', 'tbl_clients.id')
            ->whereNotIn('tbl_clients.id', function ($q) use ($currentUserId) {
                $q->select('blocked_id')
                    ->from('tbl_blocked_users')
                    ->where('user_id', $currentUserId);
            })
            ->whereNotIn('tbl_clients.id', function ($q) use ($currentUserId) {
                $q->select('user_id')
                    ->from('tbl_blocked_users')
                    ->where('blocked_id', $currentUserId);
            })
            ->where('tbl_clients_toppic.toppic_id', $topicId) // Lọc theo topic_id của bài viết
            ->distinct() // Đảm bảo không có client_id trùng
            ->pluck('tbl_clients.id')
            ->toArray();
        return $clientIds;
    }




    public static function sendSocketloadNewsfeed_new($currentUserId = '', $id_post = '')
    {
        $socketHelper = new self();
        $ClientID = $socketHelper->getClientIdsByPost($currentUserId, $id_post);
        $ClientIDTopPic = $socketHelper->getClientIdsByPostToppic($currentUserId, $id_post);
        sendSocket(['id_post' => $id_post], [], 'loadNewsfeedAdmin');
        if (!empty($ClientIDTopPic)) {
            sendSocket(['id_post' => $id_post], $ClientIDTopPic, 'loadNewsfeedAll');
        }
        if (!empty($ClientID)) {
            sendSocket(['id_post' => $id_post], $ClientID, 'loadNewsfeedFollowers');
        }
    }


    public static function sendSocketloadNewsfeed($currentUserId = '', $id_post = '')
    {
        $socketHelper = new self();
        $ClientID = $socketHelper->getClientIdsByPost($currentUserId, $id_post);
        $ClientIDTopPic = $socketHelper->getClientIdsByPostToppic($currentUserId, $id_post);
        sendSocket(['id_post' => $id_post], [], 'loadNewsfeedAdmin');
        if (!empty($ClientIDTopPic)) {
            sendSocket(['id_post' => $id_post], $ClientIDTopPic, 'loadNewsfeedAll');
        }
        if (!empty($ClientID)) {
            sendSocket(['id_post' => $id_post], $ClientID, 'loadNewsfeedFollowers');
        }
    }
    public static function sendSocketPost($currentUserId = '', $id_post = '', $type)
    {
        $_data = [
            'currentUserId' => $currentUserId,
            'id_post' => $id_post,
            'type' => $type,
            'is_save' => false,
            'count' => [
                'comment' => DB::table('tbl_comments')->where('post_id', $id_post)->whereNull('parent_id')->count(),
                'like' => DB::table('tbl_post_likes')->where('post_id', $id_post)->count()
            ]
        ];
        // sendSocket(['id_post' => $id_post], [], 'loadNewsfeedAdmin');
        // sendSocket($_data, [], 'loadCountPostAdmin');
        SendSocketH($_data, [], 'loadCountPostALL');
    }
    public static function sendSocketPostIsSave($currentUserId = '', $id_post = '', $type)
    {
        $socketHelper = new self();
        $ClientID = $socketHelper->getClientIdsByPost($currentUserId, $id_post);
        $ClientIDTopPic = $socketHelper->getClientIdsByPostToppic($currentUserId, $id_post);
        $isSaved = !empty($currentUserId) && !empty($id_post)
        ? DB::table('tbl_post_saved')
            ->where('post_id', $id_post)
            ->where('user_id', $currentUserId)
            ->exists()
        : false;
        $_data = [
            'currentUserId' => $currentUserId,
            'id_post' => $id_post,
            'type' => $type,
            'is_save'  => $isSaved,
            'count' => [
                'comment' => DB::table('tbl_comments')->where('post_id', $id_post)->whereNull('parent_id')->count(),
                'like' => DB::table('tbl_post_likes')->where('post_id', $id_post)->count(),
                'post_stars' => DB::table('tbl_post_stars')
                    ->join('tbl_receipts', 'tbl_post_stars.id', '=', 'tbl_receipts.reference_id')
                    ->where('tbl_post_stars.post_id', $id_post)
                    ->where('tbl_receipts.status', 1) // Điều kiện status = 1 từ bảng tbl_receipts
                    ->count(),
            ]
        ];
        SendSocketH($_data, [$currentUserId], 'loadCountPostALL');

    }
    public static function sendSocketComment($currentUserId = '', $id_post = '')
    {
        $socketHelper = new self();
        // sendSocket(['id_post' => $id_post], [], 'loadNewsfeedAdmin');
        SendSocketH(['id_post' => $id_post], [], 'loadCommentPostDetail');
    }
    public static function sendSocketCommentReply($currentUserId = '', $id_post = '', $id_comment = '', $type = '')
    {
        $socketHelper = new self();
        $_data = [
            'currentUserId' => $currentUserId,
            'id_post' => $id_post,
            'id_comment' => $id_comment,
            'type' => $type,
            'count' => [
                'reply' => DB::table('tbl_comments')->where('parent_id', $id_comment)->count(),
                'like' => DB::table('tbl_comment_likes')->where('comment_id', $id_comment)->count()
            ]
        ];
        SendSocketH($_data, [], 'loadCommentPostDetailReply');
    }
    public static function sendSocketNewPost($currentUserId = '', $id_post = '')
    {
        $socketHelper = new self();
        // sendSocket(['id_post' => $id_post], [], 'loadNewsfeedAdmin');
        SendSocketH(['id_post' => $id_post], [], 'loadNewPost');
    }


    public static function sendSocketPaymentChallenge($id_client = '', $data = [])
    {
        $socketHelper = new self();
        SendSocketH($data, $id_client, 'payment_challenge');
    }

    public static function sendSocketToClient($id_client = '', $data = [], $event)
    {
        sendSocket($data, $id_client, $event);
    }

}
