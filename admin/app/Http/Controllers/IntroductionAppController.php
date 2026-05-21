<?php

namespace App\Http\Controllers;

use App\Models\IntroductionApp;
use App\Models\IntroductionAppTranslations;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Not;
use Yajra\DataTables\DataTables;

class IntroductionAppController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
        $this->screen_link = [
            [
                'id' => 'StoreScreen',
                'name' => 'StoreScreen'
            ],
            [
                'id' => 'RefferalScreen',
                'name' => 'RefferalScreen'
            ],
            [
                'id' => 'HistoryScreen',
                'name' => 'HistoryScreen'
            ]
        ];
    }

    public function getTable()
    {

        $currentLanguage = app()->getLocale();
        $dtIntroduction = IntroductionApp::with(['transalations.language_detail'])->orderBy('order_by', 'ASC');
        return Datatables::of($dtIntroduction)
            ->addColumn('options', function ($introduction) {
                $edit = "<a class='dt-modal' href='admin/introduction_app/detail/$introduction->id'><i class='fa fa-pencil'></i> " . lang('edit_introduction_app') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/introduction_app/delete/' . $introduction->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('delete_introduction_app') . '</a>';
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
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/introduction_app/changeStatus/'.$data->id.'" data-status="'.$data->active.'"></div>';
                return $str;
            })

            ->addColumn('title', function ($data) use ($currentLanguage) {
                $str = '';
                foreach($data->transalations as $key => $value) {
                    if(!empty($value['title']) && $currentLanguage == $value['language']) {
                        $imgLogo = $this->baseUrl  .'/'. $value['language_detail']['image'];
                        $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span class="inline-flex">'.$value['title'].'</span></div>';
                    }
                }
                return $str;
            })
            ->addColumn('content', function ($data)  use ($currentLanguage){
                $str = '';
                foreach($data->transalations as $key => $value) {
                    if(!empty($value['content']) && $currentLanguage == $value['language']) {
                        $imgLogo = $this->baseUrl  .'/'. $value['language_detail']['image'];
                        $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span class="inline-flex">'.$value['content'].'</span></div>';
                    }
                }
                return $str;
            })
            ->editColumn('image', function ($data) {
                $imgIntroduction = $this->baseUrl  .'/'. $data->image;
                $str = '<img style="width:150px;" src="'.$imgIntroduction.'"/>';
                return $str;
            })
            ->editColumn('image_main', function ($data) {
                $imgIntroduction = $this->baseUrl  .'/'. $data->image_main;
                $str = '<img style="width:150px;" src="'.$imgIntroduction.'"/>';
                return $str;
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'active', 'image', 'image_main', 'title','content','order_by'])
            ->make(true);
    }

    public function changeStatus($id) {
        $introduction = IntroductionApp::find($id);
        try {
            $introduction->active = $this->request->input('status') == 0 ? 1 : 0;
            $introduction->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('add_introduction_app');
        } else {
            $title = lang('edit_introduction_app');
            $introduction = IntroductionApp::find($id);
            if(!empty($introduction->id)) {
                $translations = DB::table('tbl_introduction_app_translations')->where('id_introduction_app', $id)->get();
                $data_translations = [];
                foreach ($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'title' => $translation->title,
                        'content' => $translation->content,
                        'description' => $translation->description,
                    ];
                }
                $introduction->translations = $data_translations;
            }
        }
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();


        return view('admin.introduction_app.detail', [
            'title' => $title,
            'id' => $id,
            'introduction' => $introduction ?? NULL,
            'language' => $language ?? NULL,
            'screen_link' => $this->screen_link ?? NULL,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];

        if (!empty($id)) {
            $introduction = IntroductionApp::find($id);
//            $validator = Validator::make($this->request->all(),
//                [
//                    'name' => 'required',
//                ],
//                [
//                    'content.required' => lang('pls_input_name'),
//                ]
//            );
        } else {
//            $validator = Validator::make($this->request->all(),
//                [
//                    'name' => 'required',
//                ],
//                [
//                    'content.required' => lang('pls_input_name'),
//                ]
//            );

            $introduction = new IntroductionApp();
        }

//        if ($validator->fails()) {
//            $data['result'] = 0;
//            $data['message'] = $validator->errors()->all();
//            echo json_encode($data);
//            die();
//        }

        DB::beginTransaction();
        try {
            $title = $this->request->input('title');
            $introduction->screen_link = $this->request->input('screen_link');
            $introduction->save();
            DB::commit();
            if ($introduction) {
                foreach($title as $language => $value) {
                    DB::table('tbl_introduction_app_translations')->updateOrInsert(
                        [
                            'id_introduction_app' => $introduction->id,
                            'language' => $language
                        ],
                        [
                            'title' => $value,
                            'content' => $this->request->input('content')[$language] ?? '',
                            'description' => $this->request->input('description')[$language] ?? '',
                        ]
                    );
                }


                if ($this->request->hasFile('image')) {
                    if (!empty($introduction->image)) {
                        $this->deleteFile($introduction->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'introduction_app/' . $introduction->id, 600, 600, false);
                    $introduction->image = $path;
                    $introduction->save();
                }
                if ($this->request->hasFile('image_main')) {
                    if (!empty($introduction->image_main)) {
                        $this->deleteFile($introduction->image_main);
                    }
                    $path = $this->UploadFile($this->request->file('image_main'), 'introduction_app/' . $introduction->id, 600, 600, false);
                    $introduction->image_main = $path;
                    $introduction->save();
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

    public function delete($id){
        $introduction = IntroductionApp::find($id);
        try {
            $success = $introduction->delete();
            if ($success) {
                if (!empty($introduction->image)) {
                    $this->deleteFile($introduction->image);
                }
                if (!empty($introduction->image_main)) {
                    $this->deleteFile($introduction->image_main);
                }
                DB::table('tbl_introduction_app_translations')->where('id_introduction_app', $id)->delete();

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


    public function order_by()
    {
        $list_order_by = $this->request->input('list_order_by');
        if (!empty($list_order_by)) {
            $list_array = [];
            foreach ($list_order_by as $id => $order_by) {
                $job_category = IntroductionApp::find($id);
                $job_category->order_by = $order_by;
                $job_category->save();
            }
            $data['result'] = 1;
            $data['message'] = lang('c_order_by_true');
            return response()->json($data);
        }
        $data['result'] = 0;
        $data['message'] = lang('c_order_by_false');
        return response()->json($data);
    }
}
