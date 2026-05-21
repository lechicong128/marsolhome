<?php

namespace App\Http\Controllers;

use app\Models\Products;
use App\Models\VideoFile;
use App\Models\Elearning;
use App\Models\ElearningUnlock;
use App\Services\AccountService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class VideoFileController extends Controller
{
    protected $dbAccount;
    use UploadFile;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        if (config('app.debug')) {
            DB::enableQueryLog();
        }
        $this->baseUrlAdmin = config('services.storage.url');
        $this->dbAccount = $accountService;
    }

    //********************************************Tips********************************************//
    public function tips() {
        if (!has_permission('video_tips','view')){
            access_denied();
        }
        return view('admin.video_file.tips.list', [
            'title' => lang('video_tips'),
        ]);
    }

    public function getTips()
    {
        $filter_status_search = $this->request->input('status_search', 0);
        $dtData = VideoFile::with(['products'])->where(function($q) use ($filter_status_search) {
                $q->where('rel_type', '=', 'tips');
                if (is_numeric($filter_status_search)) {
//                    $q->where('tbl_video_file.active', '=', 1);
                }
            });
        return Datatables::of($dtData)
            ->addColumn('options', function ($data) {
                $edit = "<a class='dt-modal' href='admin/video/detail_tips/$data->id'><i class='fa fa-pencil'></i> " . lang('dt_edit') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/video/delete/' . $data->id . '/2\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete') . '</a>';
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
            ->editColumn('active', function ($data) {
                $checked = $data->active == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/video/changeStatus/'.$data->id.'/2" data-status="'.$data->active.'"></div>';
                return $str;
            })
            ->editColumn('show_home', function ($data) {
                $checked = $data->show_home == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/video/changeShowHome/'.$data->id.'/2" data-status="'.$data->show_home.'"></div>';
                return $str;
            })
            ->editColumn('name', function ($data) {
                return '<a class="dt-modal" href="admin/video/view_tips/'.$data->id.'">'.$data->name.'</a>';
            })
            ->editColumn('original_video', function ($data) {
                if(!empty($data->original_video)) {
                    $linkVideo = $this->baseUrlAdmin.'/'.$data->original_video;
                    return $data->original_video ? '<a href="'.$linkVideo.'">Link</a>' : '';
                }
            })
            ->editColumn('video', function ($data) {
                if(!empty($data->video)) {
                    $linkVideo = $this->baseUrlAdmin.'/'.$data->video;
                    return $data->video ? '<a href="'.$linkVideo.'">Link</a>' : '';
                }
            })
            ->editColumn('count_like', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-success">'.$data->count_like.'</a></div>';
            })
            ->editColumn('count_share', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-success">'.$data->count_share.'</a></div>';
            })
            ->editColumn('count_comment', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-success">'.$data->count_comment.'</a></div>';
            })
            ->editColumn('count_see', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-success">'.$data->count_see.'</a></div>';
            })
            ->editColumn('evaluate', function ($data) {
                $str = 'Chưa đánh giá';
                if (!empty($data->evaluate)) {
                    $str = '<div class="rating">';
                    for ($i = 0; $i < floor($data->evaluate); $i++) {
                        $str .= '<span class="star"><i class="fa fa-star" style="font-size:12px"></i></span>';
                    }
                    if ($data->evaluate < 5 && (ceil($data->evaluate) / $data->evaluate) != 1) {
                        $str .= '<span class="star"><i class="fa fa-star-half-o" style="font-size:12px"></i></span>';
                    }
                    $str .= '</div><div>('.$data->evaluate.' sao)</div>';
                }
                return '<div class="text-center">'.$str.'</div>';
            })
            ->addColumn('id_product', function ($data) {
                if(!empty($data->products->id)) {
                    $imgProduct = $this->baseUrlAdmin . '/' . $data->products->image;
                    return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                 style="width:35px;height:35px;" src="' . e($imgProduct) . '"/>
                        </div>
                        <div>
                            <strong>' . e($data->products->name) . '</strong>
                            <br><small>Mã: ' . e($data->products->code) . '</small>
                        </div>
                    </div>';
                }
                else {
                    return '';
                }
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'active', 'original_video','video', 'name','count_like','count_share','count_comment','count_see','evaluate','id_product', 'show_home'])
            ->make(true);
    }

    public function detail_tips($id = 0)
    {
        if (empty($id)) {
            if (!has_permission('video_tips','add')){
                access_denied(true);
            }
            $title = lang('add_video_tips');
        } else {
            if (!has_permission('video_tips','edit')){
                access_denied(true);
            }
            $title = lang('edit_video_tips');
            $video = VideoFile::find($id);
            if(!empty($video->original_video)) {
                $video->original_video = $this->baseUrlAdmin.'/'.$video->original_video;
            }
            if(!empty($video->id_product)) {
                $product = Products::find($video->id_product);
                $product->image = $this->baseUrlAdmin.'/'.$product->image;
            }
        }
        return view('admin.video_file.tips.detail', [
            'title' => $title,
            'id' => $id,
            'video' => $video ?? NULL,
            'product' => $product ?? NULL,
        ]);
    }

    public function view_tips($id = 0)
    {
        if (!empty($id)) {
            $title = lang('view_video_tips');
            $video = VideoFile::find($id);
            if(!empty($video->original_video)) {
                $video->original_video = $this->baseUrlAdmin.'/'.$video->original_video;
            }

            if(!empty($video->video)) {
                $video->video = $this->baseUrlAdmin.'/'.$video->video;
            }
            if(!empty($video->id_product)) {
                $product = Products::find($video->id_product);
                $product->image = $this->baseUrlAdmin.'/'.$product->image;
            }
        }
        return view('admin.video_file.tips.view', [
            'title' => $title,
            'id' => $id,
            'video' => $video ?? NULL,
            'product' => $product ?? NULL,
        ]);
    }

    public function submit_tips($id = 0)
    {
        $data = [];

        if (!empty($id)) {
            $video = VideoFile::find($id);
            $validator = Validator::make($this->request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'content.required' => lang('pls_input_name_video'),
                ]
            );
        } else {
            $validator = Validator::make($this->request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'content.required' => lang('pls_input_name_video'),
                ]
            );
            $video = new VideoFile();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }

        DB::beginTransaction();
        try {

            $video->name = $this->request->input('name');
            $video->rel_type = 'tips';
            $video->id_product = $this->request->input('id_product') ?? 0;
            $video->description = $this->request->input('description') ?? NULL;
            $video->save();
            DB::commit();
            if ($video) {
                if ($this->request->hasFile('original_video')) {
                    if (!empty($video->original_video)) {
                        $this->deleteFile($video->original_video);
                    }
                    if (!empty($video->video)) {
                        $this->deleteFile($video->video);
                    }
                    $path = $this->UploadFile($this->request->file('original_video'), 'video/' . $video->id, 0, 0, false);
                    $video->original_video = $path;
                    $video->video = NULL;
                    $video->duration = $this->GetDurationVideo(storage_path('app/public/' . $video->original_video));
                    $video->save();
                }

                $data['id'] = $video->id;
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeStatus($id,$type = 1) {
        if($type == 1){
            if (!has_permission('review', 'approve')) {
                $data['result'] = false;
                $data['message'] = lang('dt_access');
                return response()->json($data);
            }
        } else {
            if (!has_permission('video_tips', 'approve')) {
                $data['result'] = false;
                $data['message'] = lang('dt_access');
                return response()->json($data);
            }
        }
        $video = VideoFile::find($id);
        try {
            $video->active = $this->request->input('status') == 0 ? 1 : 0;
            $video->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }


    //********************************************Review********************************************//

    public function review() {
        if (!has_permission('review', 'view')) {
            access_denied();
        }
        return view('admin.video_file.review.list', [
            'title' => lang('client_review'),
        ]);
    }

    public function getReview()
    {
        $filter_status_search = $this->request->input('status_search');
        $searchValue          = $this->request->input('search.value');

        // --- DataTables paging & ordering ---
        $start   = (int) $this->request->input('start', 0);
        $length  = (int) $this->request->input('length', 10);
        $orders  = $this->request->input('order', []);
        $columns = $this->request->input('columns', []);

        $colIndex = isset($orders[0]['column']) ? (int)$orders[0]['column'] : 0;
        $orderBy  = $columns[$colIndex]['name'] ?? 'tbl_video_file.id';
        $blackListColumns = ['DT_RowIndex', 'options', 'clients'];
        if (in_array($orderBy, $blackListColumns)) {
            $orderBy = 'tbl_video_file.id';
        }

        $orderDir = isset($orders[0]['dir']) && in_array(strtolower($orders[0]['dir']), ['asc','desc'])
            ? $orders[0]['dir'] : 'desc';

        // --- Base query ---
        $baseQuery = VideoFile::select('tbl_video_file.*')
            ->with(['products'])
            ->where('rel_type', 'review')
            ->when(is_numeric($filter_status_search), function ($q) use ($filter_status_search) {
//                $q->where('active', $filter_status_search);
            })
            ->when(!empty($searchValue), function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%");
            });

        // --- LẤY id_client TRÊN TRANG HIỆN TẠI ---
        $idsOnPage = (clone $baseQuery)
            ->orderBy($orderBy, $orderDir)
            ->when($length > -1, function($q) use ($start, $length) {
                $q->skip($start)->take($length);
            })
            ->pluck('id_client')
            ->unique()
            ->filter()
            ->values()
            ->all();

        // --- Lấy thông tin khách ---
        $dataClient = [];
        if (!empty($idsOnPage)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['list_id' => $idsOnPage]);
            unset($newRequest['search']);

            $responseClient = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClient = $responseClient->getData(true);
        }

        return Datatables::of($baseQuery->orderBy($orderBy, $orderDir))

            ->addColumn('clients', function ($data) use ($dataClient) {

                $client = $dataClient['clients'][$data->id_client] ?? [];

                if (empty($client)) {
                    return '<div class="text-muted">'.$data->id_client.'</div>';
                }

                $imgAvatar = $client['avatar'] ?? '';

                return '<div class="product-info">
                <div class="product-img">
                    <img class="img-circle"
                         onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                         style="width:35px;height:35px;"
                         src="'.e($imgAvatar).'"/>
                </div>
                <div>
                    <strong>'.e($client['fullname'] ?? '').'</strong>
                    <br><small>'.e($client['phone'] ?? '').'</small>
                </div>
            </div>';
            })
            ->addColumn('options', function ($data) {
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/video/delete/' . $data->id . '1\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('active', function ($data) {
                $checked = $data->active == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/video/changeStatus/'.$data->id.'/1" data-status="'.$data->active.'"></div>';
                return $str;
            })
            ->editColumn('name', function ($data) {
                return '<a class="dt-modal" href="admin/video/view_tips/'.$data->id.'">'.$data->name.'</a>';
            })
            ->editColumn('original_video', function ($data) {
                if(!empty($data->original_video)) {
                    $linkVideo = $this->baseUrlAdmin.'/'.$data->original_video;
                    return $data->original_video ? '<a href="'.$linkVideo.'">Link</a>' : '';
                }
            })
            ->editColumn('video', function ($data) {
                if(!empty($data->video)) {
                    $linkVideo = $this->baseUrlAdmin.'/'.$data->video;
                    return $data->video ? '<a href="'.$linkVideo.'">Link</a>' : '';
                }
            })
            ->editColumn('count_like', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-success">'.$data->count_like.'</a></div>';
            })
            ->editColumn('count_share', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-success">'.$data->count_share.'</a></div>';
            })
            ->editColumn('count_comment', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-success">'.$data->count_comment.'</a></div>';
            })
            ->editColumn('count_see', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-success">'.$data->count_see.'</a></div>';
            })
            ->editColumn('evaluate', function ($data) {
                $str = 'Chưa đánh giá';
                if (!empty($data->evaluate)) {
                    $str = '<div class="rating">';
                    for ($i = 0; $i < floor($data->evaluate); $i++) {
                        $str .= '<span class="star"><i class="fa fa-star" style="font-size:12px"></i></span>';
                    }
                    if ($data->evaluate < 5 && (ceil($data->evaluate) / $data->evaluate) != 1) {
                        $str .= '<span class="star"><i class="fa fa-star-half-o" style="font-size:12px"></i></span>';
                    }
                    $str .= '</div><div>('.$data->evaluate.' sao)</div>';
                }
                return '<div class="text-center">'.$str.'</div>';
            })
            ->addColumn('id_product', function ($data) {
                if(!empty($data->products->id)) {
                    $imgProduct = $this->baseUrlAdmin . '/' . $data->products->image;
                    return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                 style="width:35px;height:35px;" src="' . e($imgProduct) . '"/>
                        </div>
                        <div>
                            <strong>' . e($data->products->name) . '</strong>
                            <br><small>Mã: ' . e($data->products->code) . '</small>
                        </div>
                    </div>';
                }
                else {
                    return '';
                }
            })
            ->editColumn('show_home', function ($data) {
                $checked = $data->show_home == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/video/changeShowHome/'.$data->id.'/1" data-status="'.$data->show_home.'"></div>';
                return $str;
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'active', 'original_video','video', 'name','count_like','count_share','count_comment','count_see','evaluate','id_product','clients','show_home'])
            ->make(true);
    }

    public function view_review($id = 0)
    {
        if (!empty($id)) {
            $title = lang('view_video_review');
            $video = VideoFile::find($id);
            if(!empty($video->original_video)) {
                $video->original_video = $this->baseUrlAdmin.'/'.$video->original_video;
            }

            if(!empty($video->video)) {
                $video->video = $this->baseUrlAdmin.'/'.$video->video;
            }
            if(!empty($video->id_product)) {
                $product = Products::find($video->id_product);
                $product->image = $this->baseUrlAdmin.'/'.$product->image;
            }
        }
        return view('admin.video_file.review.view', [
            'title' => $title,
            'id' => $id,
            'video' => $video ?? NULL,
            'product' => $product ?? NULL,
        ]);
    }

    //********************************************Elearning********************************************//
    public function elearning() {
        if (!has_permission('elearning','view')){
            access_denied();
        }
        return view('admin.video_file.elearning.list', [
            'title' => lang('elearning'),
        ]);
    }

    public function getElearning()
    {
        $filter_status_search = $this->request->input('status_search', 0);
        $dtData = Elearning::query()
            ->with(['video_trailer'])
            ->withCount(['list_videos', 'unlock'])
            ->withSum('list_videos', 'duration')
            ->where(function($q) use ($filter_status_search) {
            if (is_numeric($filter_status_search)) {}
        });
        return Datatables::of($dtData)
            ->addColumn('options', function ($data) {
                $setup = "<a target='_blank' href='admin/video/setup_video_elearning/$data->id'><i class='fa fa-edit'></i> " . lang('setup_elearning_video') . "</a>";
                $edit = "<a class='dt-modal' href='admin/video/detail_elearning/$data->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_elearning') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/video/delete_elearning/' . $data->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete_elearning') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $setup . '</li>
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('active', function ($data) {
                $checked = $data->active == 1 ? 'checked' : '';
                $str = '<div class="text-center"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/video/changeStatusElearning/'.$data->id.'" data-status="'.$data->active.'"></div>';
                return $str;
            })
            ->editColumn('is_check_new', function ($data) {
                $checked = $data->is_check_new == 1 ? 'checked' : '';
                $str = '<div class="text-center"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/video/changeStatusCheckNewElearning/'.$data->id.'" data-status="'.$data->is_check_new.'"></div>';
                return $str;
            })
            ->addColumn('video_trailer', function ($data) {
                if(!empty($data->video_trailer->id)) {
                    $linkVideo = $this->baseUrlAdmin.'/'.$data->video_trailer->original_video;
                    return $data->video_trailer->original_video ? '<a href="'.$linkVideo.'">Link</a>' : '';
                }
            })
            ->addColumn('duration', function ($data) {
                $duration = (int) ($data->list_videos_sum_duration ?? 0);
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-info">'.secondsToHMS($duration).'</a></div>';
            })
            ->addColumn('count_video', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-info">'.number_format((int)($data->list_videos_count ?? 0)).'</a></div>';
            })
            ->editColumn('title', function ($data) {
                return '<a class="dt-modal" href="admin/video/view_elearning/'.$data->id.'">'.$data->title.'</a>';
            })
            ->editColumn('price', function ($data) {
                return '<div class="text-right">' . number_format($data->price) .'</div>';
            })
            ->editColumn('unlock', function ($data) {
                return '<div class="text-center"><a class="dt-update text-center btn btn-xs btn-info">'.number_format((int)($data->unlock_count ?? 0)).'</a></div>';
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'active', 'video_trailer', 'title', 'price', 'duration', 'count_video', 'is_check_new', 'unlock'])
            ->make(true);
    }


    public function detail_elearning($id = 0)
    {
        if (empty($id)) {
            if (!has_permission('elearning', 'add')) {
                access_denied(true);
            }
            $title = lang('add_video_elearning');
        } else {
            if (!has_permission('elearning', 'edit')) {
                access_denied(true);
            }
            $title = lang('edit_video_elearning');
            $elearning = Elearning::with('video_trailer')->where('id', $id)->first();
            if(!empty($elearning->video_trailer->id)) {
                $elearning->video_trailer->original_video = $this->baseUrlAdmin.'/'.$elearning->video_trailer->original_video;
                $elearning->video_trailer->video = $this->baseUrlAdmin.'/'.$elearning->video_trailer->video;
            }
            $elearning->image = $this->baseUrlAdmin . '/' . $elearning->image;
        }
        return view('admin.video_file.elearning.detail', [
            'title' => $title,
            'id' => $id,
            'elearning' => $elearning ?? NULL,
        ]);
    }


    public function submit_elearning($id = 0)
    {
        $data = [];

        if (!empty($id)) {
            $elearning = Elearning::find($id);
            $validator = Validator::make($this->request->all(),
                [
                    'title' => 'required',
                ],
                [
                    'content.required' => lang('pls_input_name_video'),
                ]
            );
        } else {
            $validator = Validator::make($this->request->all(),
                [
                    'title' => 'required',
                ],
                [
                    'content.required' => lang('pls_input_name_video'),
                ]
            );
            $elearning = new Elearning();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }

        DB::beginTransaction();
        try {
            $elearning->title = $this->request->input('title');
            $price = $this->request->input('price') ?? 0;
            $elearning->price = number_unformat($price);
            $elearning->author = $this->request->input('author') ?? NULL;
            $elearning->description = $this->request->input('description') ?? NULL;
            $elearning->save();
            DB::commit();
            if ($elearning) {
                if ($this->request->hasFile('image')) {
                    if(!empty($elearning->image)) {
                        $this->deleteFile($elearning->image);
                    }

                    $path = $this->UploadFile($this->request->file('image'), 'elearning/' . $elearning->id, 0, 0, false);
                    $elearning->image = $path;
                    $elearning->save();
                }
                if ($this->request->hasFile('original_video')) {
                    $video_trailer = VideoFile::where('rel_type', 'elearning')
                        ->where('rel_id', $elearning->id)
                        ->where('is_premium', 0)->first();
                    if(empty($video_trailer->id)) {
                        $video_trailer = new VideoFile();
                        $video_trailer->name = $this->request->input('name_video_trailer') ?? NULL;
                        $video_trailer->rel_type = 'elearning';
                        $video_trailer->is_premium = 0;
                        $video_trailer->rel_id = $elearning->id;
                        $video_trailer->save();
                    }
                    else if (!empty($video_trailer->original_video)) {
                        $this->deleteFile($video_trailer->original_video);
                    }
                    $path = $this->UploadFile($this->request->file('original_video'), 'video/' . $video_trailer->id, 0, 0, false);
                    $video_trailer->original_video = $path;
                    $video_trailer->video = NULL;
                    $video_trailer->save();
                    $data['id_trailer'] = $video_trailer->id;
                }
                else {
                    $video_trailer = VideoFile::where('rel_type', 'elearning')
                        ->where('rel_id', $elearning->id)
                        ->where('is_premium', 0)->first();
                    if(!empty($video_trailer->id)) {
                        $video_trailer->name = $this->request->input('name_video_trailer') ?? NULL;
                        $video_trailer->save();
                    }
                }
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function view_elearning($id = 0)
    {
        if (!empty($id)) {
            $title = lang('view_elearning');
            $elearning = Elearning::with(['video_trailer', 'list_videos'])->where('id', $id)->first();
            if(!empty($elearning->video_trailer->id)) {
                $elearning->video_trailer->original_video = $this->baseUrlAdmin.'/'.$elearning->video_trailer->original_video;
                $elearning->video_trailer->video = $this->baseUrlAdmin.'/'.$elearning->video_trailer->video;
                $elearning->video_trailer->thumbnail = $this->baseUrlAdmin.'/'.$elearning->video_trailer->thumbnail;
            }
            if($elearning->list_videos) {
                $countLike = 0;
                $countShare = 0;
                $countSee = 0;
                $countComment = 0;
                foreach($elearning->list_videos as $key => $value) {
                    if(!empty($value->original_video)) {
                        $elearning->list_videos[$key]->original_video = $this->baseUrlAdmin.'/'.$value->original_video;
                    }
                    if(!empty($value->video)) {
                        $elearning->list_videos[$key]->video = $this->baseUrlAdmin.'/'.$value->video;
                    }
                    if(!empty($value->thumbnail)) {
                        $elearning->list_videos[$key]->thumbnail = $this->baseUrlAdmin.'/'.$value->thumbnail;
                    }
                    $countLike += $value->count_like;
                    $countShare += $value->count_share;
                    $countSee += $value->count_see;
                    $countComment += $value->count_comment;
                }
                $elearning->count_like = $countLike;
                $elearning->count_share = $countShare;
                $elearning->count_see = $countSee;
                $elearning->count_comment = $countComment;
            }
        }

        return view('admin.video_file.elearning.view', [
            'title' => $title,
            'id' => $id,
            'elearning' => $elearning ?? NULL,
        ]);
    }


    public function setup_video_elearning($id = 0)
    {
        if (!has_permission('elearning', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        if (!empty($id)) {
            $title = lang('setup_detail_video_elearning');
            $elearning = Elearning::with(['list_videos'])->where('id', $id)->first();
            if($elearning->list_videos) {
                $sumDuration = 0;
                foreach($elearning->list_videos as $key => $value) {
                    if(!empty($value->original_video)) {
                        $elearning->list_videos[$key]->original_video = $this->baseUrlAdmin.'/'.$value->original_video;
                    }
                    if(!empty($value->video)) {
                        $elearning->list_videos[$key]->video = $this->baseUrlAdmin.'/'.$value->video;
                    }
                    $sumDuration += $value->duration;
                }
                $elearning->hms = secondsToHMS($sumDuration);
            }
        }

        return view('admin.video_file.elearning.setup_video', [
            'title' => $title,
            'id' => $id,
            'elearning' => $elearning ?? NULL,
        ]);
    }

    public function submit_video_elearning($id_elearning = 0, $id = 0)
    {
        $data = [];

        if (!empty($id)) {
            $video = VideoFile::find($id);
            if($video->rel_id != $id_elearning || $video->rel_type != 'elearning' || $video->is_premium != 1) {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
                return response()->json($data);
            }
            $validator = Validator::make($this->request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'content.required' => lang('pls_input_name_video'),
                ]
            );
        } else {
            $validator = Validator::make($this->request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'content.required' => lang('pls_input_name_video'),
                ]
            );
            $video = new VideoFile();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }

        DB::beginTransaction();
        try {

            $elearning = Elearning::find($id_elearning);

            $video->name = $this->request->input('name');
            $video->rel_type = 'elearning';
            $video->rel_id = $id_elearning;
            $video->is_premium = 1;
            $video->id_product = $this->request->input('id_product') ?? 0;
            $video->description = $this->request->input('description') ?? NULL;
            $video->save();
            DB::commit();
            if ($video) {
                if ($this->request->hasFile('original_video')) {
                    if (!empty($video->original_video)) {
                        $this->deleteFile($video->original_video);
                    }
                    if (!empty($video->video)) {
                        $this->deleteFile($video->video);
                    }
                    $path = $this->UploadFile($this->request->file('original_video'), 'video/' . $video->id, 0, 0, false);
                    $video->original_video = $path;
                    $video->video = NULL;
                    $video->duration = $this->GetDurationVideo(storage_path('app/public/' . $video->original_video));
                    $video->active = $elearning->active ?? 0;
                    $video->save();

                    $all_duration = VideoFile::where('rel_type', 'elearning')
                        ->where('rel_id', $video->rel_id)
                        ->where('is_premium', 1)
                        ->sum('duration');
                    $elearning->all_duration = $all_duration;
                    $elearning->save();
                }

                $data['id'] = $video->id;
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }


    public function detail_video_elearning($id_elearning = 0, $id = '0')
    {
        if (empty($id)) {
            $title = lang('c_add_video_elearning');
        } else {
            $title = lang('c_edit_video_elearning');
            $video = VideoFile::where('id', $id)->where('rel_type', 'elearning')
                ->where('rel_id', $id_elearning)->where('is_premium', 1)->first();
            if(!empty($video->original_video)) {
                $video->original_video = $this->baseUrlAdmin.'/'.$video->original_video;
            }
        }
        return view('admin.video_file.elearning.detail_video', [
            'title' => $title,
            'id' => $id,
            'id_elearning' => $id_elearning,
            'video' => $video ?? NULL,
        ]);
    }

    public function get_video_elearning($id_elearning)
    {
        $filter_status_search = $this->request->input('status_search', 0);
        $dtData = VideoFile::where(function($q) use ($filter_status_search, $id_elearning) {
                $q->where('rel_type', 'elearning')
                    ->where('rel_id', $id_elearning)
                    ->where('is_premium', 1);
            })->orderBy('order_premium', 'asc')->orderBy('id', 'asc');
        return Datatables::of($dtData)
            ->addColumn('options', function ($data) use ($id_elearning) {

                $delete = '<a class="btn btn-danger btn-sm po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                                <button href=\'admin/video/delete_video_elearning/' . $id_elearning . '/'.$data->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                            "><i class="fa fa-trash"></i></a>';

                $options = '<div class="lesson-actions">
                                <a class="btn btn-default btn-sm dt-modal" href="admin/video/detail_video_elearning/'.$id_elearning.'/'.$data->id.'"><i class="fa fa-edit"></i></a>
                                '.$delete.'
                            </div>';

                return $options;
            })
            ->editColumn('duration', function ($data) use ($id_elearning) {
                return gmdate("H:i:s", $data->duration ?? '');
            })
            ->editColumn('original_video', function ($data) use ($id_elearning) {
                return $data->original_video ? $this->baseUrlAdmin.'/'.$data->original_video : '';
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'original_video', 'duration'])
            ->make(true);
    }

    public function delete_video_elearning($id_elearning = 0, $id = '0'){
        $video = VideoFile::find($id);
        if(empty($video->id) || $video->rel_type != 'elearning' || $video->rel_id != $id_elearning || $video->is_premium != 1) {
            $data['result'] = false;
            $data['message'] = lang('dt_error');
            return response()->json($data);
        }

        try {
            $success = $video->delete();
            if ($success) {
                if (!empty($video->original_video)) {
                    $this->deleteFile($video->original_video);
                }
                if (!empty($video->video)) {
                    $this->deleteFile($video->video);
                }


                $all_duration = VideoFile::where('rel_type', 'elearning')
                    ->where('rel_id', $video->rel_id)
                    ->where('is_premium', 1)
                    ->sum('duration');
                $elearning = Elearning::find($video->rel_id);
                if (!empty($elearning)) {
                    $elearning->all_duration = $all_duration;
                    $elearning->save();
                }

                $data['result'] = true;
                $data['message'] = lang('dt_success');
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
            }
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function changeStatusElearning($id) {
        $elearning = Elearning::find($id);
        try {
            $elearning->active = $this->request->input('status') == 0 ? 1 : 0;
            $elearning->save();

            VideoFile::where('rel_type', 'elearning')->where('rel_id', $id)->update(['active' => $elearning->active]);

            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }
    public function changeStatusCheckNewElearning($id) {
        $elearning = Elearning::find($id);
        try {
            $elearning->is_check_new = $this->request->input('status') == 0 ? 1 : 0;
            $elearning->save();

            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function order_by_video()
    {
        $list_order_by = $this->request->input('list_order_by');
        if (!empty($list_order_by)) {
            $ids = [];
            $caseSql = 'CASE id ';
            foreach ($list_order_by as $id => $order_by) {
                $id = (int) $id;
                $order_by = (int) $order_by;
                $ids[] = $id;
                $caseSql .= "WHEN {$id} THEN {$order_by} ";
            }
            $caseSql .= 'END';

            if (!empty($ids)) {
                DB::table('tbl_video_file')
                    ->whereIn('id', $ids)
                    ->update(['order_premium' => DB::raw($caseSql)]);
            }

            $data['result'] = 1;
            $data['message'] = lang('c_order_by_true');
            return response()->json($data);
        }
        $data['result'] = 0;
        $data['message'] = lang('c_order_by_false');
        return response()->json($data);
    }

    public function delete($id,$type = 1){
        if($type == 1){
            if (!has_permission('review', 'delete')) {
                $data['result'] = false;
                $data['message'] = lang('dt_access');
                return response()->json($data);
            }
        } else {
            if (!has_permission('video_tips', 'delete')) {
                $data['result'] = false;
                $data['message'] = lang('dt_access');
                return response()->json($data);
            }
        }
        $video = VideoFile::find($id);
        try {
            $success = $video->delete();
            if ($success) {
                if (!empty($video->original_video)) {
                    $this->deleteFile($video->original_video);
                }
                if (!empty($video->video)) {
                    $this->deleteFile($video->video);
                }

                $data['result'] = true;
                $data['message'] = lang('dt_success');
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
            }
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function delete_elearning($id){
        if (!has_permission('elearning', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $elearning = Elearning::find($id);
        try {
            $success = $elearning->delete();
            if ($success) {
                VideoFile::where('rel_type', 'elearning')
                    ->where('rel_id', $id)
                    ->select(['id', 'original_video', 'video'])
                    ->chunkById(200, function ($videos) {
                        foreach ($videos as $video) {
                            if (!empty($video->original_video)) {
                                $this->deleteFile($video->original_video);
                            }
                            if (!empty($video->video)) {
                                $this->deleteFile($video->video);
                            }
                        }
                    });
                VideoFile::where('rel_type', 'elearning')->where('rel_id', $id)->delete();

                $data['result'] = true;
                $data['message'] = lang('dt_success');
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
            }
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function renderVideo($id = '') {
        return true;
        $video = VideoFile::find($id);
        if(!empty($video->video)) {
            return;
        }
        $input = storage_path('app/public/' . $video->original_video);
        if(file_exists($input)) {
            $output = storage_path('app/public/video_size/' . $video->original_video);
            $video->video = 'video_size/' . $video->original_video;
            $this->compressVideo($input, $output, 28);
            if (file_exists($output)) {

                $video->save();
            }
        }
    }

    public function getCustomerUnlock() {
        $id_elearning = $this->request->input('id_elearning', 0);
        $_locale = 'vi';
        $id_customer = $this->request->input('id_customer');
        $searchValue = $this->request->input('search.value');
        $listIDsearch = [];
        if(!empty($searchValue)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['search' => $searchValue]);
            $responseClientSearch = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClientSearch = $responseClientSearch->getData(true);
            if(!empty($dataClientSearch) && !empty($dataClientSearch['clients'])) {
                $dataClientSearch = $dataClientSearch['clients'];
                foreach($dataClientSearch as $value) {
                    $listIDsearch[] = $value['id'];
                }
            }
        }

        // DataTables paging
        $start  = (int) $this->request->input('start', 0);
        $length = (int) $this->request->input('length', 10);

        // DataTables ordering (an toàn với fallback)
        $orderReq = $this->request->input('order', []);
        $columns  = $this->request->input('columns', []);
        $columnIndex = isset($orderReq[0]['column']) ? (int)$orderReq[0]['column'] : 0;
        $orderBy = $columns[$columnIndex]['name'] ?? 'tbl_elearning_unlock.id';
        $orderDir = isset($orderReq[0]['dir']) && in_array(strtolower($orderReq[0]['dir']), ['asc','desc'])
            ? $orderReq[0]['dir'] : 'desc';
        $baseQuery = ElearningUnlock::query()
            ->select('tbl_elearning_unlock.*')
            ->where(function($q) use ($id_customer, $id_elearning) {
                if (!empty($id_customer)) {
                    $q->where('tbl_elearning_unlock.id_client', '=', $id_customer);
                }
                if (!empty($id_elearning)) {
                    $q->where('tbl_elearning_unlock.id_elearning', '=', $id_elearning);
                }
            })
            ->when(!empty($searchValue), function ($q) use ($searchValue, $listIDsearch) {
                $q->where(function ($w) use ($searchValue, $listIDsearch) {
                    $w->WhereIn('tbl_elearning_unlock.id_client', $listIDsearch);
                });
            });

        $idsOnPage = (clone $baseQuery)
            ->orderBy($orderBy, $orderDir)
            ->when($length > -1, function($q) use ($start, $length) {
                // DataTables dùng start/length; length = -1 nghĩa là hiển thị tất cả
                $q->skip($start)->take($length);
            })
            ->pluck('tbl_elearning_unlock.id_client')
            ->unique()
            ->filter() // loại null/empty
            ->values()
            ->all();

        // Lấy thông tin khách chỉ cho các id trên TRANG HIỆN TẠI
        $dataClient = [];
        if (!empty($idsOnPage)) {
            $newRequest = clone $this->request;
            $newRequest->merge(['list_id' => $idsOnPage]);
            unset($newRequest['search']); // tránh ảnh hưởng bởi search chung
            $responseClient = $this->dbAccount->getListDetailCustomer($newRequest);
            $dataClient = $responseClient->getData(true);
        }

        // Trả DataTables: áp dụng cùng order để đồng bộ với idsOnPage
        return Datatables::of($baseQuery->orderBy($orderBy, $orderDir))
            ->filter(function($query) use ($listIDsearch, $searchValue) {
                if (!empty($listIDsearch)) {
                    $query->whereIn('tbl_elearning_unlock.id_client', $listIDsearch);
                }
            })
            ->addColumn('options', function ($unlock) {
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/video/delete_unlock/'.$unlock->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('Xóa Unlock') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('Xóa Unlock') .'</a>';
                return '<div class="dropdown text-center">
                        <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                            Tác vụ <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                            <li style="cursor: pointer">'.$delete.'</li>
                        </ul>
                    </div>';
            })
            ->editColumn('id_client', function ($unlock) use ($dataClient) {
                $client = $dataClient['clients'][$unlock->id_client] ?? [];
                if (empty($client)) return '';
                $imgAvatar = $client['avatar'] ?? '';
                return '<div class="product-info">
                        <div class="product-img">
                            <img class="img-circle" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';"
                                 style="width:35px;height:35px;" src="' . $imgAvatar . '"/>
                        </div>
                        <div>
                            <strong>' . ($client['fullname'] ?? '') . '</strong>
                            <br><small>' . ($client['phone'] ?? '') . '</small>
                        </div>
                    </div>';
            })
            ->editColumn('created_at', function ($SignReview) {
                return '<div class="text-center">'._dt($SignReview->created_at).'</div>';
            })
            ->addIndexColumn()
            ->removeColumn('updated_at')
            ->rawColumns([
                'options',
                'id_client',
                'created_at',
            ])
            ->make(true);
    }

    public function delete_unlock($id) {
        $success = ElearningUnlock::where('id', $id)->delete();
        if(!empty($success)) {
            $data['result'] = true;
            $data['message'] = lang('Xóa dữ liệu thành công');
        } else {
            $data['result'] = false;
            $data['message'] = lang('dt_error');
        }
        return response()->json($data);
    }

    public function changeShowHome($id) {
        if (!has_permission('review', 'approve')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $video_file = VideoFile::find($id);
        try {
            $video_file->show_home = $this->request->input('status') == 0 ? 1 : 0;
            $video_file->save();

            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }
}
