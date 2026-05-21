<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    const REPLY_EVERYONE   = 'everyone';
    const REPLY_FOLLOWING  = 'following';
    const REPLY_FOLLOWERS  = 'followers';

    public static function getReplyOptions()
    {
        return [
            self::REPLY_EVERYONE => [
                'label' => 'Tất cả mọi người',
                'images' => [
                    [
                        'id' => 'light',
                        'link' => asset('admin/assets/images/icon_post/everyone_light.png'),
                    ],
                    [
                        'id' => 'dark',
                        'link' => asset('admin/assets/images/icon_post/everyone_dark.png'),
                    ],
                    [
                        'id' => 'light_active',
                        'link' => asset('admin/assets/images/icon_post/everyone_light_active.png'),
                    ],
                    [
                        'id' => 'dark_active',
                        'link' => asset('admin/assets/images/icon_post/everyone_dark_active.png'),
                    ],
                ],
            ],
            self::REPLY_FOLLOWING => [
                'label' => 'Tài khoản bạn đang theo dõi',
                'images' => [
                    [
                        'id' => 'light',
                        'link' => asset('admin/assets/images/icon_post/following_light.png'),
                    ],
                    [
                        'id' => 'dark',
                        'link' => asset('admin/assets/images/icon_post/following_dark.png'),
                    ],
                    [
                        'id' => 'light_active',
                        'link' => asset('admin/assets/images/icon_post/following_light_active.png'),
                    ],
                    [
                        'id' => 'dark_active',
                        'link' => asset('admin/assets/images/icon_post/following_dark_active.png'),
                    ],
                ],
            ],
            self::REPLY_FOLLOWERS => [
                'label' => 'Tài khoản theo dõi bạn',
                'images' => [
                    [
                        'id' => 'light',
                        'link' => asset('admin/assets/images/icon_post/followers_light.png'),
                    ],
                    [
                        'id' => 'dark',
                        'link' => asset('admin/assets/images/icon_post/followers_dark.png'),
                    ],
                    [
                        'id' => 'light_active',
                        'link' => asset('admin/assets/images/icon_post/followers_light_active.png'),
                    ],
                    [
                        'id' => 'dark_active',
                        'link' => asset('admin/assets/images/icon_post/followers_dark_active.png'),
                    ],
                ],
            ],
        ];
    }
    protected $table = 'tbl_posts';

    protected $fillable = [
        'user_id',
        'content',
        'visibility'
    ];
    public function postStars()
    {
        return $this->hasMany(PostStar::class, 'post_id', 'id');
    }

    public function saveds()
    {
        return $this->hasMany(PostSaveds::class, 'post_id');
    }
    public function reports()
    {
        return $this->hasMany(PostReportMode::class, 'post_id');
    }
    public function watchers()
    {
        return $this->hasMany(PostWatcher::class, 'post_id');
    }
    public function author()
    {
        return $this->belongsTo(Clients::class, 'user_id', 'id');
    }
    public function media()
    {
        return $this->hasMany(PostMedia::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}
