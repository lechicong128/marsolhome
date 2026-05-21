<?php

namespace App\Http\Controllers;

use App\Services\CommunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class CommunityController extends Controller
{
    protected $communityService;

    public function __construct(Request $request, CommunityService $communityService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->communityService = $communityService;
    }

    public function get_list()
    {
        if (!has_permission('community', 'view') && !has_permission('community', 'viewown')) {
            access_denied();
        }
        $title = 'Cộng đồng';
        return view('admin.community.list', [
            'title' => $title
        ]);
    }

    public function getList()
    {
        if (!has_permission('community', 'view') && !has_permission('community', 'viewown')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền xem!');
            $data['data'] = [];
            return response()->json($data);
        }
        $response = $this->communityService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $view = "<a href='admin/community/view/$id' class='dt-modal'><i class='fa fa-eye'></i> Xem chi tiết</a>";
                $hide = '<a href="admin/community/toggleHide/' . $id . '" class="dt-update"><i class="fa fa-eye-slash"></i> Ẩn/Hiện</a>';
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/community/delete/' . $id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $view . '</li>
                                <li style="cursor: pointer">' . $hide . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('stt', function ($row) use (&$start) {
                return '<div>' . (++$start) . '</div>';
            })
            ->addColumn('author_info', function ($dtData) {
                $author = $dtData['author_new'] ?? $dtData['author'] ?? [];
                $url = !empty($author['avatar_new']) ? $author['avatar_new'] : (!empty($author['avatar']) ? $author['avatar'] : asset('admin/assets/images/users/avatar-1.jpg'));
                return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" style="width:35px;height:35px;" src="' . $url . '" alt="avatar" />
                        </div>
                        <div>
                            <strong>' . (!empty($author['fullname']) ? $author['fullname'] : '') . '</strong>
                            <br><small>' . (!empty($author['phone']) ? $author['phone'] : '') . '</small>
                        </div>
                    </div>';
            })
            ->editColumn('content', function ($dtData) {
                $content = $dtData['content'] ?? '';
                $short = mb_strlen($content) > 100 ? mb_substr($content, 0, 100) . '...' : $content;
                return '<div style="max-width: 300px; word-wrap: break-word;">' . e($short) . '</div>';
            })
            ->addColumn('media_info', function ($dtData) {
                $media = $dtData['media'] ?? [];
                $imageCount = 0;
                $videoCount = 0;
                foreach ($media as $m) {
                    if (($m['media_type'] ?? '') === 'video') {
                        $videoCount++;
                    } else {
                        $imageCount++;
                    }
                }
                $html = '';
                if ($imageCount > 0) {
                    $html .= '<span class="media-badge media-photos"><i class="fa fa-image"></i> ' . $imageCount . ' ảnh</span> ';
                }
                if ($videoCount > 0) {
                    $html .= '<span class="media-badge media-video"><i class="fa fa-video-camera"></i> ' . $videoCount . ' video</span>';
                }
                return $html ?: '<span class="text-muted">—</span>';
            })
            ->editColumn('created_at', function ($dtData) {
                return '<div class="text-center">' . (!empty($dtData['created_at']) ? date('d/m/Y H:i', strtotime($dtData['created_at'])) : '') . '</div>';
            })
            ->addColumn('stats', function ($dtData) {
                $likes = $dtData['likes_count'] ?? 0;
                $comments = $dtData['comments_count'] ?? 0;
                return '<div class="text-center">
                    <span title="Lượt thích"><i class="fa fa-heart text-danger"></i> ' . $likes . '</span><br>
                    <span title="Bình luận"><i class="fa fa-comment text-primary"></i> ' . $comments . '</span>
                </div>';
            })
            ->editColumn('is_hidden', function ($dtData) {
                $isHidden = $dtData['is_hidden'] ?? 0;
                if ($isHidden) {
                    return '<div class="text-center"><span class="label label-danger">Đã ẩn</span></div>';
                }
                return '<div class="text-center"><span class="label label-success">Hiển thị</span></div>';
            })
            ->rawColumns(['options', 'stt', 'author_info', 'content', 'media_info', 'created_at', 'stats', 'is_hidden'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->addIndexColumn()
            ->skipPaging()
            ->make(true);
    }

    public function view($id = 0)
    {
        if (!has_permission('community', 'view') && !has_permission('community', 'viewown')) {
            access_denied(true, lang('dt_access'));
        }
        $title = 'Chi tiết bài viết';
        $this->request->merge(['id' => $id]);
        $response = $this->communityService->getDetail($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return view('admin.community.view', [
            'title' => $title,
            'dtData' => $dtData,
        ]);
    }

    public function countAll()
    {
        $response = $this->communityService->countAll($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function delete($id = '')
    {
        if (!has_permission('community', 'delete')) {
            access_denied(true, lang('dt_access'));
        }
        $this->request->merge(['id' => $id]);
        $response = $this->communityService->delete($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function toggleHide($id = '')
    {
        if (!has_permission('community', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->communityService->toggleHide($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function get_report_list()
    {
        if (!has_permission('report_violation', 'view') && !has_permission('report_violation', 'viewown')) {
            access_denied();
        }
        // Lấy danh sách vi phạm để đổ vào filter
        $resp       = $this->communityService->getViolations();
        $violations = $resp->getData(true)['data'] ?? [];

        return view('admin.community.reports', [
            'title'      => 'Báo cáo vi phạm',
            'violations' => $violations,
        ]);
    }

    public function getReportList()
    {
        if (!has_permission('report_violation', 'view') && !has_permission('report_violation', 'viewown')) {
            return response()->json(['result' => false, 'message' => 'Không có quyền xem!', 'data' => []]);
        }
        $response = $this->communityService->getReports($this->request);
        $data     = $response->getData(true);
        if (!($data['result'] ?? false)) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start  = intval($this->request->input('start', 0));

        return (new CollectionDataTable($dtData))
            ->addColumn('stt', function ($row) use (&$start) {
                return '<div class="text-center">' . (++$start) . '</div>';
            })
            ->addColumn('type_label', function ($row) {
                if (($row['type'] ?? '') === 'post') {
                    return '<span class="label label-primary"><i class="fa fa-file-text-o"></i> Bài viết</span>';
                }
                return '<span class="label label-warning"><i class="fa fa-comment-o"></i> Bình luận</span>';
            })
            ->addColumn('reporter_info', function ($row) {
                $avatar = $row['reporter_avatar'] ?? '';
                $name   = $row['reporter_name'] ?? '—';
                $img    = $avatar
                    ? '<img class="img-circle" style="width:28px;height:28px;margin-right:6px;object-fit:cover;" src="' . $avatar . '" onerror="this.src=\'admin/assets/images/not_available.jpg\'">'
                    : '<i class="fa fa-user-circle" style="font-size:22px;margin-right:6px;"></i>';
                return '<div class="d-flex align-items-center">' . $img . e($name) . '</div>';
            })
            ->addColumn('target_short', function ($row) {
                $content = $row['target_content'] ?? '';
                $short   = mb_strlen($content) > 120 ? mb_substr($content, 0, 120) . '...' : $content;
                $link = '';
                if (($row['type'] ?? '') === 'post') {
                    $link = '<a href="admin/community/view/' . ($row['target_id'] ?? '') . '" class="dt-modal" style="font-size:11px;"><i class="fa fa-external-link"></i> Xem bài</a>';
                }
                return '<div style="max-width:320px;word-wrap:break-word;">' . e($short) . '</div>' . $link;
            })
            ->addColumn('violation_label', function ($row) {
                $name = $row['violation_name'] ?? 'Khác';
                return '<span class="label label-danger">' . e($name) . '</span>';
            })
            ->addColumn('note_short', function ($row) {
                $note = $row['note'] ?? '';
                return $note ? '<span title="' . e($note) . '">' . e(mb_substr($note, 0, 80)) . (mb_strlen($note) > 80 ? '…' : '') . '</span>' : '<span class="text-muted">—</span>';
            })
            ->editColumn('created_at', function ($row) {
                return '<div class="text-center">' . (!empty($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : '—') . '</div>';
            })
            ->rawColumns(['stt', 'type_label', 'reporter_info', 'target_short', 'violation_label', 'note_short', 'created_at'])
            ->setTotalRecords($data['total'] ?? 0)
            ->setFilteredRecords($data['filtered'] ?? 0)
            ->with(['draw' => intval($this->request->input('draw'))])
            ->addIndexColumn()
            ->skipPaging()
            ->make(true);
    }

    // ====== THIẾT LẬP LÝ DO VI PHẠM ======
    public function get_violation_list()
    {
        if (!has_permission('setup_violation', 'view') && !has_permission('setup_violation', 'viewown')) {
            access_denied();
        }
        return view('admin.community.violations', [
            'title' => 'Thiết lập lý do vi phạm',
        ]);
    }

    public function getViolationTable()
    {
        $resp       = $this->communityService->getViolations();
        $violations = collect($resp->getData(true)['data'] ?? []);
        $start      = 0;

        return (new CollectionDataTable($violations))
            ->addColumn('options', function ($row) {
                $id   = $row['id'];
                $name = addslashes($row['name']);
                $edit   = '<a class="btn-edit-vio" data-id="' . $id . '" data-name="' . $name . '" style="cursor:pointer">'
                        . '<i class="fa fa-pencil width-icon-actions"></i> ' . lang('dt_edit') . '</a>';
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="'
                        . '<button href=\'admin/community/violations/delete/' . $id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>'
                        . '<button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>'
                        . '">'
                        . '<i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete') . '</a>';
                return '<div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" data-toggle="dropdown" aria-expanded="true">
                             ' . lang('dt_actions') . ' <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li style="cursor:pointer">' . $edit . '</li>
                                <li style="cursor:pointer">' . $delete . '</li>
                            </ul>
                        </div>';
            })
            ->addColumn('stt', function ($row) use (&$start) {
                return '<div class="text-center">' . (++$start) . '</div>';
            })
            ->rawColumns(['options', 'stt'])
            ->addIndexColumn()
            ->skipPaging()
            ->make(true);
    }

    public function storeViolation()
    {
        if (!has_permission('setup_violation', 'add')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $response = $this->communityService->storeViolation($this->request);
        return response()->json($response->getData(true));
    }

    public function updateViolation($id)
    {
        if (!has_permission('setup_violation', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $response = $this->communityService->updateViolation($this->request, $id);
        return response()->json($response->getData(true));
    }

    public function deleteViolation($id)
    {
        if (!has_permission('setup_violation', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $response = $this->communityService->deleteViolation($id);
        return response()->json($response->getData(true));
    }
}
