<?php

namespace App\Http\Controllers;

use App\Models\ApplicationCommentSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ApplicationCommentsController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('application_comments', 'view')) {
            access_denied();
        }
        return view('admin.application_comments.list', [
            'title' => 'Mẫu góp ý ứng dụng',
        ]);
    }
    public function getTable()
    {
        if (!has_permission('application_comments', 'view')) {
            access_denied();
        }

        $comments = DB::table('tbl_application_comments')
            ->orderBy('id', 'desc')
            ->get();

        $memberIds = $comments->pluck('member_id')->filter()->unique()->toArray();
        $clientsMap = [];
        if (!empty($memberIds)) {
            $accountService = app(\App\Services\AccountService::class);
            $newRequest = new \Illuminate\Http\Request();
            $newRequest->merge(['list_id' => $memberIds]);
            $responseClient = $accountService->getListDetailCustomer($newRequest);
            $dataClient = $responseClient->getData(true);
            if (!empty($dataClient['clients'])) {
                $clientsMap = $dataClient['clients'];
            }
        }

        $suggestions = DB::table('tbl_application_comment')->pluck('content', 'id')->toArray();

        return DataTables::of($comments)
            ->addColumn('options', function ($item) {
                $delete = '<a type="button" class="po-delete btn btn-icon btn-danger" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/application_comments/delete/' . $item->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove"></i></a>';
                return '<div class="text-center">' . $delete . '</div>';
            })
            ->editColumn('comment_date', function ($item) {
                return _dt($item->comment_date);
            })
            ->editColumn('member_name', function ($item) use ($clientsMap) {
                $client = $clientsMap[$item->member_id] ?? [];
                $name = $item->member_name ?? $client['fullname'] ?? 'N/A';
                $phone = !empty($client['phone']) ? '<br><small class="text-muted">' . e($client['phone']) . '</small>' : '';
                return '<div>' . e($name) . $phone . '</div>';
            })
            ->editColumn('rating', function ($item) {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $item->rating) {
                        $stars .= '<i class="fa fa-star text-warning"></i>';
                    } else {
                        $stars .= '<i class="fa fa-star-o text-muted"></i>';
                    }
                }
                return '<div class="text-center">' . $stars . ' <br><small>(' . $item->rating . '/5)</small></div>';
            })
            ->editColumn('content', function ($item) use ($suggestions) {
                $content = '<div>' . e($item->content) . '</div>';
                
                // $ids = [];
                // if (!empty($item->suggestion_ids)) {
                //     $ids = json_decode($item->suggestion_ids, true);
                //     if (!is_array($ids)) {
                //         $ids = explode(',', $item->suggestion_ids);
                //     }
                //     $ids = array_filter(array_map('intval', $ids));
                // }
                
                // if (!empty($ids)) {
                //     $badges = '';
                //     foreach ($ids as $id) {
                //         if (isset($suggestions[$id])) {
                //             $badges .= '<span class="label label-info" style="display: inline-block; margin-right: 5px; margin-top: 5px; white-space: normal; text-align: left;">' . e($suggestions[$id]) . '</span>';
                //         }
                //     }
                //     if (!empty($badges)) {
                //         $content .= '<div style="margin-top: 8px;">' . $badges . '</div>';
                //     }
                // }
                return $content;
            })
            ->editColumn('images', function ($item) {
                $str = '';
                if (!empty($item->images)) {
                    $images = json_decode($item->images, true);
                    if (!is_array($images)) {
                        $images = explode('||', $item->images);
                    }
                    
                    foreach ($images as $img) {
                        $img = trim($img);
                        if (!empty($img)) {
                            $str .= '<div class="product-img inline-flex m-r-5">
                                        <a href="' . $img . '" data-lightbox="comment-img-' . $item->id . '" class="display-block mbot5">
                                            <img onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" src="' . $img . '" alt="image" class="img-circle b-r-1 brs-20" style="width: 35px;height: 35px">
                                        </a>
                                    </div>';
                        }
                    }
                }
                return '<div class="text-center">' . $str . '</div>';
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'comment_date', 'rating', 'images', 'member_name', 'content'])
            ->make(true);
    }
    public function getTabletem()
    {
        if (!has_permission('application_comments', 'view')) {
            access_denied();
        }

        $comments = ApplicationCommentSuggestion::orderBy('id', 'desc')->get();

        return DataTables::of($comments)
            ->addColumn('options', function ($item) {
                $edit = "<a class='dt-modal' href='admin/application_comments/detail/$item->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_content_comment') . "</a>";
                $delete = '<a type="button" class="po-delete btn btn-icon btn-danger" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/application_comments/delete/' . $item->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove"></i></a>';
                
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('content', function ($item) {
                return '<div>' . e($item->content) . '</div>';
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'content'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = 'Thêm mẫu góp ý';
            if (!has_permission('application_comments', 'add') && !has_permission('settings', 'add')) {
                access_denied(true);
            }
        } else {
            if (!has_permission('application_comments', 'edit') && !has_permission('application_comments', 'add')) {
                access_denied(true);
            }
            $title = 'Sửa mẫu góp ý';
        }
        $comment = ApplicationCommentSuggestion::find($id);
        return view('admin.application_comments.detail', [
            'title' => $title,
            'id' => $id,
            'comment' => $comment,
        ]);
    }

    public function submit($id = 0)
    {
        if (!has_permission('application_comments', 'add')) {
            return response()->json([
                'result' => false,
                'message' => lang('dt_access')
            ]);
        }

        $validator = Validator::make($this->request->all(),
            [
                'content' => 'required|string',
            ],
            [
                'content.required' => lang('dt_required'),
            ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => implode('<br>', $validator->errors()->all())
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($id > 0) {
                $comment = ApplicationCommentSuggestion::find($id);
                if (!$comment) {
                    return response()->json(['result' => false, 'message' => 'Không tìm thấy mẫu góp ý.'], 404);
                }
            } else {
                $comment = new ApplicationCommentSuggestion();
            }

            $comment->content = $this->request->input('content');
            $comment->save();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => lang('dt_success')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        if (!has_permission('application_comments', 'delete')) {
            return response()->json(['result' => false, 'message' => 'Bạn không có quyền xóa mẫu góp ý.'], 403);
        }

        $comment = ApplicationCommentSuggestion::find($id);
        if (!$comment) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy mẫu góp ý.'], 404);
        }

        DB::beginTransaction();
        try {
            $comment->delete();
            DB::commit();

            return response()->json([
                'result' => true,
                'message' => lang('dt_success')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }
}
