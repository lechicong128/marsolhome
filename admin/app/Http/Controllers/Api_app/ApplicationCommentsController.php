<?php

namespace App\Http\Controllers\Api_app;

use App\Models\ApplicationComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\UploadFile;
use App\Models\ApplicationCommentSuggestion;

class ApplicationCommentsController extends AuthController
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
    }

    public function submit()
    {
        // TODO(security): Require authentication using CheckLoginApi middleware
        $client = $this->request->client;
        if (empty($client) || empty($client->id)) {
            return response()->json([
                'result' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện gửi góp ý!'
            ], 401);
        }

        $rules = [
            'content' => 'required|string',
            'rating' => 'required|integer|between:1,5',
            'comment_date' => 'nullable|date',
            'suggestion_ids' => 'nullable',
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => implode('<br>', $validator->errors()->all())
            ], 400);
        }

        DB::beginTransaction();
        try {
            $comment = new ApplicationComment();

            $comment_date = $this->request->input('comment_date');
            if (empty($comment_date)) {
                $comment->comment_date = date('Y-m-d H:i:s');
            } else {
                $comment->comment_date = date('Y-m-d H:i:s', strtotime($comment_date));
            }

            $comment->ticket_number = 'GY' . date('ymdHis') . rand(100, 999);
            $comment->member_id = $client->id;
            $comment->member_name = $client->fullname;
            $comment->content = $this->request->input('content');
            $comment->rating = $this->request->input('rating');

            $suggestion_ids = $this->request->input('suggestion_ids');
            if (!empty($suggestion_ids)) {
                if (is_string($suggestion_ids)) {
                    $decoded = json_decode($suggestion_ids, true);
                    if (is_array($decoded)) {
                        $comment->suggestion_ids = $decoded;
                    } else {
                        $comment->suggestion_ids = array_filter(array_map('intval', explode(',', $suggestion_ids)));
                    }
                } else if (is_array($suggestion_ids)) {
                    $comment->suggestion_ids = array_map('intval', $suggestion_ids);
                }
            }

            $images = [];

            // 1. Handle uploaded image files
            if ($this->request->hasFile('images')) {
                $files = $this->request->file('images');
                if (is_array($files)) {
                    foreach ($files as $file) {
                        $path = $this->UploadFile($file, 'application_comments', 800, 600, false);
                        if ($path) {
                            $images[] = config('services.storage.url') . '/' . $path;
                        }
                    }
                } else {
                    $path = $this->UploadFile($files, 'application_comments', 800, 600, false);
                    if ($path) {
                        $images[] = config('services.storage.url') . '/' . $path;
                    }
                }
            }

            // 2. Handle image URLs or strings of URLs passed directly
            $imagesInput = $this->request->input('images');
            if (!empty($imagesInput)) {
                if (is_array($imagesInput)) {
                    foreach ($imagesInput as $img) {
                        if (is_string($img) && !empty($img)) {
                            $images[] = $img;
                        }
                    }
                } else if (is_string($imagesInput)) {
                    $splitImages = explode('||', $imagesInput);
                    foreach ($splitImages as $img) {
                        $img = trim($img);
                        if (!empty($img)) {
                            $images[] = $img;
                        }
                    }
                }
            }

            // TODO(security): Sanitize image URLs to prevent XSS (javascript: links) in raw HTML rendering
            $images = array_filter($images, function ($img) {
                return preg_match('/^https?:\/\//i', $img) || preg_match('/^\/storage/i', $img);
            });

            $comment->images = implode('||', $images);

            $comment->save();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Gửi góp ý thành công',
                'data' => $comment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listappcomment()
    {
        $data = ApplicationCommentSuggestion::select('id', 'content')->get(); // Hoặc paginate(20)
        return response()->json([
            'result' => true,
            'data' => $data
        ]);
    }
}
