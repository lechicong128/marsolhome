<?php

namespace App\Helpers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\PostLike;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\clients;
use App\Models\PlayerId;
use Illuminate\Http\Request;

class NotificationHelper
{
    use NotificationTrait;
    public static function getUserName($userId)
    {
        return optional(clients::find($userId))->fullname ?? 'Ai đó';
    }
    public static function notifyLike($post, $likerId, $customer_id)
    {
        $actorName = self::getUserName($likerId); // ví dụ: "Trần Thị B"

        $totalLikes = PostLike::where('post_id', $post->id)->count();
        $others = max(0, $totalLikes - 1);

        $arr_object_id = [];
        $dtCustomer = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.id as object_id',
            'tbl_player_id.player_id as player_id',
            DB::raw("'customer' as 'object_type'")
        )
            ->leftJoin('tbl_player_id', function ($join) {
                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
            })
            ->where('tbl_clients.id', $customer_id)
            ->get()->toArray();
        if (!empty($dtCustomer)) {
            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
        }
        $dtClient = Clients::find($customer_id);
        $fnbNoti = new \App\Services\NotiService();
        $request_noti_review = new Request([
            'type_noti' => 'notifyLikePost',
            'arr_object_id' => $arr_object_id,
            'dtData' => $post,
            'actorName' => $actorName,
            'likerId' => $likerId,
            'customer_id' => $customer_id,
            'others' => $others,
            'locale' => $dtClient->lang_default ?? Config::get('constant')['lang_default']
        ]);
        $fnbNoti->addNotiPost($request_noti_review);
    }
    public static function notifyLikeComment($comment,$tagged_users, $customer_id, $commenterId )
    {
        $actorName = self::getUserName($commenterId); // ví dụ: "Trần Thị B"

        $arr_object_id = [];
        $dtCustomer = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.id as object_id',
            'tbl_player_id.player_id as player_id',
            DB::raw("'customer' as 'object_type'")
        )
            ->leftJoin('tbl_player_id', function ($join) {
                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
            })
            ->where('tbl_clients.id', $comment->user_id)
            ->get()->toArray();
        if (!empty($dtCustomer)) {
            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
        }
        $dtClient = Clients::find($customer_id);
        $fnbNoti = new \App\Services\NotiService();
        $request_noti_review = new Request([
            'type_noti' => 'notifyLikeComment',
            'arr_object_id' => $arr_object_id,
            'tagged_users' => $tagged_users,
            'dtData' => $comment,
            'actorName' => $actorName,
            'commenterId' => $commenterId,
            'customer_id' => $commenterId,
            'locale' => $dtClient->lang_default ?? Config::get('constant')['lang_default']
        ]);
        $fnbNoti->addNotiPost($request_noti_review);
    }
    public static function notifyComment($comment,$tagged_users, $customer_id, $commenterId,$comment_parent )
    {
        $actorName = self::getUserName($commenterId); // ví dụ: "Trần Thị B"

        $arr_object_id = [];
        $dtCustomer = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.id as object_id',
            'tbl_player_id.player_id as player_id',
            DB::raw("'customer' as 'object_type'")
        )
            ->leftJoin('tbl_player_id', function ($join) {
                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
            })
            ->where('tbl_clients.id', $customer_id)
            ->get()->toArray();
        if (!empty($dtCustomer)) {
            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
        }

        $arr_object_id_tag = [];
        if(!empty($tagged_users[0]))
        $dtCustomertag = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.id as object_id',
            'tbl_player_id.player_id as player_id',
            DB::raw("'customer' as 'object_type'")
        )
            ->leftJoin('tbl_player_id', function ($join) {
                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
            })
            ->where('tbl_clients.id', $tagged_users[0])
            ->get()->toArray();
        if (!empty($dtCustomertag)) {
            $arr_object_id_tag = array_merge($arr_object_id_tag, $dtCustomertag);
        }

        $arr_object_id_comment = [];
        if(!empty($comment_parent->user_id))
        $dtCustomercomment = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.id as object_id',
            'tbl_player_id.player_id as player_id',
            DB::raw("'customer' as 'object_type'")
        )
            ->leftJoin('tbl_player_id', function ($join) {
                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
            })
            ->where('tbl_clients.id', $comment_parent->user_id)
            ->get()->toArray();
        if (!empty($dtCustomercomment)) {
            $arr_object_id_comment = array_merge($arr_object_id_comment, $dtCustomercomment);
        }
        $dtClient = Clients::find($customer_id);
        $fnbNoti = new \App\Services\NotiService();
        $request_noti_review = new Request([
            'type_noti' => 'notifyComment',
            'arr_object_id' => $arr_object_id,
            'arr_object_id_tag' => $arr_object_id_tag,
            'arr_object_id_comment' => $arr_object_id_comment,
            'tagged_users' => $tagged_users,
            'dtData' => $comment,
            'actorName' => $actorName,
            'commenterId' => $commenterId,
            'customer_id' => $customer_id,
            'locale' => $dtClient->lang_default ?? Config::get('constant')['lang_default']
        ]);
        $fnbNoti->addNotiPost($request_noti_review);
    }
}
