<?php

namespace App\Http\Controllers;


use App\Traits\UploadFile;
use App\Services\ChallengeMeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class ChallengeMeController extends Controller
{
    protected $customerService;
    protected $challengeMeService;
    use UploadFile;
    public function __construct(Request $request, ChallengeMeService $challengeMeService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->challengeMeService = $challengeMeService;
    }
    public function get_list()
    {
        if (!has_permission('challenge_me', 'view') && !has_permission('challenge_me', 'viewown')) {
            access_denied();
        }
        $title = lang('dt_challenge_me');
        return view('admin.challenge_me.list', [
            'title' => $title
        ]);
    }

    public function getList()
    {
        if (!has_permission('challenge_me', 'view') && !has_permission('challenge_me', 'viewown')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền xem!');
            $data['data'] = [];
            return response()->json($data);
        }
        $response = $this->challengeMeService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $view = "<a href='admin/challenge_me/view/$id' class='dt-modal'><i class='fa fa-eye'></i> " . lang('dt_view_challenge_me') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/challenge_me/delete/' . $id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_challenge_me') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $view . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>' . (++$start) . '</div>';
            })
            ->addColumn('reference_no', function ($dtData) {
                $id = $dtData['id'];
                return "<a class='dt-modal' href='admin/challenge_me/view/$id'>" . $dtData['reference_no'] . "</a>";
            })
            ->editColumn('date', function ($dtData) {
                return '<div class="text-center">' . (!empty($dtData['date']) ? _dt($dtData['date']) : '') . '</div>';
            })
            ->editColumn('status', function ($dtData) {
                $status = $dtData['status'] ?? null;

                // Map explicit numeric statuses per request:
                // 0 => Đang thực hiện, 2 => Hoàn thành, 3 => Trễ hạn
                $label = '';
                $const = Config::get('constant');
                $intStatus = is_numeric($status) ? (int) $status : null;

                if ($intStatus === 0) {
                    $label = '<span class="label label-warning">'.lang('step_pending').'</span>';
                    if (!empty($dtData['date_challenge'])) {
                        try {
                            $now = \Carbon\Carbon::now();
                            $deadline = \Carbon\Carbon::parse($dtData['date_challenge']);
                            if ($deadline->lt($now)) {
                                $label = '<span class="label label-danger">' . lang('step_overdue_false') . '</span>';
                            }
                        } catch (\Exception $e) {
                            // ignore parse errors
                        }
                    }
                } elseif ($intStatus === 1) {
                    $label = '<span class="label label-success">'.lang('step_success').'</span>';

                } elseif ($intStatus === 2) {
                    $label = '<span class="label label-danger">'.lang('step_overdue_false').'</span>';

                }

                $dateHtml = !empty($transaction['date_status']) ? '<div>' . _dt($transaction['date_status']) . '</div>' : '';

                return '<div class="text-center">' . $label . $dateHtml.'</div>';
            })
            ->addColumn('customer', function ($dtData) {
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" style="width:35px;height:35px;" src="' . $url . '" alt="product-img" />
                        </div>
                        <div>
                            <strong>' . (!empty($customer['fullname']) ? $customer['fullname'] : ''). '</strong>
                            <br><small>'. (!empty($customer['phone']) ? $customer['phone'] : '') .'</small>
                        </div>
                    </div>';
            })
            ->addColumn('date_time_left', function ($dtData) {
                $date_time_left = '';
                if (!empty($dtData['date_challenge'])) {
                    try {
                        $now = \Carbon\Carbon::now();
                        $deadline = \Carbon\Carbon::parse($dtData['date_challenge']);
                        if ($deadline->gt($now)) {
                            $days = $now->diffInDays($deadline);
                            if ($days > 0) {
                                $date_time_left = $days . ' '. lang('day');
                            } else {
                                $hours = $now->diffInHours($deadline);
                                if ($hours > 0) {
                                    $date_time_left = $hours . ' '. lang('hours');
                                }
                            }
                        } else {
                            $date_time_left = '<div class="label label-danger">'.lang('expired').'</div>';
                        }
                    } catch (\Exception $e) {
                        $date_time_left = '';
                    }
                }
                return '<div>' . $date_time_left . '</div>';
            })
            ->editColumn('information_challenge', function ($dtData) {
                $challenge = $dtData['challenge'] ?? [];
                $str = '<div>
                    <div>'.lang("c_name_challenge").' : ' . ($challenge['name'] ?? '') . '</div>
                    <div>'.lang("c_type_challenge").' : ' . ($challenge['type'] ? ($challenge['type'] == 1 ? '<div class="dt-update text-center btn btn-xs btn-warning">'.lang('trademark').'</div>' : '<div class="label label-default">'.lang('daily').'</div>') : '') . '</div>
                </div>';
                return '<div>' . $str . '</div>';
            })
            ->editColumn('deposit', function ($dtData) {
                return '<div class="text-right">' . (!empty($dtData['deposit']) ? formatMoney($dtData['deposit']) : 0) . '</div>';
            })
            ->editColumn('completion_rate', function ($dtData) {
                return '<div class="text-center">' . (!empty($dtData['completion_rate']) ? number_format($dtData['completion_rate']) : 0) . '%</div>';
            })
            ->editColumn('total_haru_xu', function ($dtData) {
                return '<div class="text-center">' . (!empty($dtData['total_haru_xu']) ? number_format($dtData['total_haru_xu']) : 0) . '</div>';
            })
            ->rawColumns(['options', 'reference_no', 'date', 'date_start', 'id', 'status', 'customer', 'grand_total', 'total_promotion', 'deposit', 'information_challenge', 'haru_xu', 'total_haru_xu', 'discount_cost_delivery', 'completion_rate', 'date_time_left'])
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
        if (!has_permission('challenge_me', 'view') && !has_permission('challenge_me', 'viewown')) {
            access_denied(true, lang('dt_access'));
        }
        $title = lang('dt_view_challenge_me');
        $this->request->merge(['id' => $id]);
        $response = $this->challengeMeService->getListDetailChallengeMe($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return view('admin.challenge_me.view', [
            'title' => $title,
            'dtData' => $dtData,
        ]);
    }

    public function countAll()
    {
        $response = $this->challengeMeService->countAll($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function delete($id = '')
    {
        if(!has_permission('challenge_me', 'delete')) {
            access_denied(true, lang('dt_access'));
        }
        $this->request->merge(['id' => $id]);
        $response = $this->challengeMeService->delete($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

}
