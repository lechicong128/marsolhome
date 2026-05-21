<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class CodeLeaderController extends Controller
{
    protected $dbAccount;
    use UploadFile;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->dbAccount = $accountService;
    }

    /**
     * Hiển thị trang danh sách mã leader
     */
    public function get_list()
    {
        if (!has_permission('code_leader', 'view') && !has_permission('code_leader', 'viewown')) {
            access_denied();
        }
        $title = 'Quản lý mã Leader';
        return view('admin.code_leader.list', [
            'title' => $title
        ]);
    }

    /**
     * Lấy danh sách mã leader cho DataTable
     */
    public function getList()
    {
        if (!has_permission('code_leader', 'view') && !has_permission('code_leader', 'viewown')) {
            access_denied();
        }
        $response = $this->dbAccount->getListCodeLeader($this->request);

        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));

        return (new CollectionDataTable($dtData))
            ->addColumn('stt', function ($row) use (&$start) {
                return '<div class="text-center">' . (++$start) . '</div>';
            })
            ->editColumn('code', function ($row) {
                return '<div class="text-center"><span class="label label-primary">' . ($row['code'] ?? '') . '</span></div>';
            })
            ->editColumn('status', function ($row) {
                $id = $row['id'];
                $status = $row['status'] ?? 0;
                $intStatus = is_numeric($status) ? (int) $status : 0;
                if ($intStatus === 0) {
                    $label = '<div class="dt-update label label-warning" style="cursor: pointer;" href="admin/code_leader/changeStatus/' . $id . '">Chưa sử dụng</div>';
                } elseif ($intStatus === 1) {
                    $label = '<span class="label label-success">Đã sử dụng</span>';
                } else {
                    $label = '<span class="label label-default">Không xác định</span>';
                }
                return '<div class="text-center">' . $label . '</div>';
            })
            ->addColumn('customer', function ($row) {
                $customer = $row['customer'] ?? [];
                if (empty($customer)) {
                    return '<div class="text-center"><span class="text-muted">Chưa có</span></div>';
                }
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : (!empty($customer['avatar']) ? $customer['avatar'] : asset('admin/assets/images/users/avatar-1.jpg'));
                return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" style="width:35px;height:35px;" src="' . $url . '" alt="avatar" />
                        </div>
                        <div>
                            <strong>' . (!empty($customer['fullname']) ? $customer['fullname'] : '') . '</strong>
                            <br><small>' . (!empty($customer['phone']) ? $customer['phone'] : '') . '</small>
                        </div>
                    </div>';
            })
            ->editColumn('created_at', function ($row) {
                return '<div class="text-center">' . (!empty($row['created_at']) ? _dt($row['created_at']) : '') . '</div>';
            })
            ->editColumn('used_at', function ($row) {
                return '<div class="text-center">' . (!empty($row['used_at']) ? _dt($row['used_at']) : '') . '</div>';
            })
            ->addColumn('options', function ($row) {
                $id = $row['id'];
                $status = $row['status'] ?? 0;
                
                if ($status == 1) {
                    $delete = '<li style="cursor: pointer"><a type="button" onclick="alert_float(\'danger\', \'Mã này đã có người sử dụng, bạn không thể xóa.\')"><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete') . '</a></li>';
                } else {
                    $delete = '<li style="cursor: pointer"><a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                        <button href=\'admin/code_leader/delete/' . $id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                        <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                    "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete') . '</a></li>';
                }

                $options = '<div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                ' . $delete . '
                            </ul>
                        </div>';

                return $options;
            })
            ->rawColumns(['stt', 'code', 'status', 'customer', 'created_at', 'used_at', 'options'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->addIndexColumn()
            ->skipPaging()
            ->make(true);
    }

    /**
     * Đếm tổng mã leader theo trạng thái
     */
    public function countAll()
    {
        $response = $this->dbAccount->countAllCodeLeader($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    /**
     * Tạo mã leader mới (trang detail/form)
     */
    public function get_detail($id = 0)
    {
        if (!has_permission('code_leader', 'add')) {
            access_denied(true, lang('dt_access'));
        }
        $title = $id > 0 ? 'Chỉnh sửa mã Leader' : 'Tạo mã Leader';
        return view('admin.code_leader.detail', [
            'title' => $title,
            'id' => $id,
        ]);
    }

    /**
     * Submit tạo mã leader
     */
    public function submit($id = 0)
    {
        if (!has_permission('code_leader', 'add')) {
            return response()->json(['result' => false, 'message' => lang('dt_access')]);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->submit('api/code_leader/submit', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'] ?? $dataRes;
        return response()->json($data);
    }

    /**
     * Xoá mã leader
     */
    public function delete($id = 0)
    {
        if (!has_permission('code_leader', 'delete')) {
            return response()->json(['result' => false, 'message' => lang('dt_access')]);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->delete('api/code_leader/delete', $this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    /**
     * Đổi trạng thái mã leader
     */
    public function changeStatus($id = 0)
    {
        if (!has_permission('code_leader', 'approve')) {
            return response()->json(['result' => false, 'message' => lang('dt_access')]);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->dbAccount->changeStatus('api/code_leader/changeStatus', $this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    /**
     * Gán khách hàng vào mã leader
     */
    public function addClient()
    {
        if (!has_permission('code_leader', 'edit')) {
            return response()->json(['result' => false, 'message' => lang('dt_access')]);
        }
        $response = $this->dbAccount->submit('api/code_leader/addClient', $this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'] ?? $dataRes;
        return response()->json($data);
    }
    
}
