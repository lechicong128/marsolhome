<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ChallengeMeSubmissions extends Model
{
    use HasFactory;

    protected $table = 'challenge_me_submissions';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'challenge_me_id',
        'created_by',
        'content',
    ];

    protected $casts = [
        'challenge_me_id' => 'integer',
        'created_by' => 'integer',
    ];

    // relation: challenge chính (tbl_challenge_me)
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ChallengeMe::class, 'challenge_me_id', 'id');
    }

    // relation: file đính kèm của submission (bảng challenge_me_files, khóa submission_id)
    public function files(): HasMany
    {
        return $this->hasMany(ChallengeMeFile::class, 'submission_id', 'id');
    }

    // relation: likes (bảng PostLike, lưu post_id)
    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class, 'post_id', 'id');
    }


    // relation: media/attachments chung (nếu có model PostMedia)
    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class, 'submission_id', 'id');
    }

    // relation: người tạo submission
    public function author(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'created_by', 'id');
    }

    // tiện ích: lấy gần nhất trước
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
    protected static function booted()
    {
        static::addGlobalScope('exclude_ignored_by_user', function (Builder $q) {
            // skip if no authenticated user or running in console/queue
            if (app()->runningInConsole()) {
                return;
            }

            // Try multiple ways to resolve current user id (auth guards, request user, custom request->client, header)
            $userId = null;

            try {
                $userId = auth()->id();
            } catch (\Throwable $e) {
                $userId = null;
            }

            if (empty($userId)) {
                $userId = optional(request()->user())->id ?? null;
            }

            if (empty($userId)) {
                $userId = optional(app('request')->client)->id ?? null;
            }

            if (empty($userId)) {
                $userId = (int) request()->header('X-Client-Id', 0) ?: null;
            }

            // No user — do not modify queries
            if (empty($userId)) {
                return;
            }
            $q->whereNotIn('id', function ($sub) use ($userId) {
                $sub->select('rel_id')
                    ->from('tbl_post_ignores')
                    ->where('user_id', $userId)
                    ->where('type', 'post');
            });
        });
    }
}