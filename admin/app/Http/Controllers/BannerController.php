<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\BannerTranslations;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Not;
use Yajra\DataTables\DataTables;

class BannerController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrl = config('services.storage.url');
    }

    public function getBanner()
    {
        $filter_status_search = $this->request->input('status_search', 0);
        $dtBanner = Banner::with(['transalations.language_detail'])
            ->where(function($q) use ($filter_status_search) {
            if (is_numeric($filter_status_search)) {
                $q->where('tbl_banner.is_app', '=', $filter_status_search);
            }
        });
        return Datatables::of($dtBanner)
            ->addColumn('options', function ($banner) {
                $edit = "<a class='dt-modal' href='admin/banner/detail/$banner->id'><i class='fa fa-pencil'></i> " . lang('edit_banner') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/banner/delete/' . $banner->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('delete_banner') . '</a>';
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
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/banner/changeStatus/'.$data->id.'" data-status="'.$data->active.'"></div>';
                return $str;
            })

            ->addColumn('title', function ($data) {
                $str = '';
                foreach($data->transalations as $key => $value) {
                    if(!empty($value['title'])) {
                        $imgLogo = $this->baseUrl  .'/'. $value['language_detail']['image'];
                        $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span class="inline-flex">'.$value['title'].'</span></div>';
                    }
                }
                return $str;
            })
            ->addColumn('content', function ($data) {
                $str = '';
                foreach($data->transalations as $key => $value) {
                    if(!empty($value['content'])) {
                        $imgLogo = $this->baseUrl  .'/'. $value['language_detail']['image'];
                        $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span class="inline-flex">'.$value['content'].'</span></div>';
                    }
                }
                return $str;
            })
            ->editColumn('image', function ($data) {
                $str = '';

                $str.= '<div class="m-b-5">';
                $str.= '<table class="table">';
                $str.= '<tbody>';
                    foreach($data->transalations as $key => $value) {
                        $str.= '<tr>';
                        $imgLogo = $this->baseUrl  .'/'. $value['language_detail']['image'];
                        $str.= '<td><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> </td>';
                        $str.= '<td>';
                        $imgBanner = $this->baseUrl  .'/'. $value['image'];
                        $str.= '<a href="'.$imgBanner.'" data-lightbox="customer-profile_'.$data->id.'" class="display-block mbot5">
                                    <img src="'.$imgBanner.'" alt="image" class="img-responsive " style="width: 50px;height: 50px">
                                </a>';
                        $str.= '</td>';
                        $str.= '</tr>';
                    }
                $str.= '</tbody>';
                $str.= '</table>';
                return $str;

//                $dtImage = !empty($job_category->image) ? asset('storage/' . $job_category->image) : null;
//                return loadImage($dtImage);
            })
            ->editColumn('image_website', function ($data) {
                $dtImage = !empty($data->image_website) ? asset('storage/' . $data->image_website) : null;
                return loadImage($dtImage);
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'active', 'image','image_website', 'title','content'])
            ->make(true);
    }

    public function changeStatus($id) {
        $banner = Banner::find($id);
        try {
            $banner->active = $this->request->input('status') == 0 ? 1 : 0;
            $banner->save();
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
            $title = lang('add_banner');
        } else {
            $title = lang('edit_banner');
            $banner = Banner::find($id);
            if(!empty($banner->id)) {
                $translations = DB::table('tbl_banner_translations')->where('id_banner', $id)->get();
                $data_translations = [];
                foreach ($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'title' => $translation->title,
                        'content' => $translation->content,
                        'image' => $translation->image,
                        'image_website' => $translation->image_website,
                    ];
                }
                $banner->translations = $data_translations;
            }
        }
        $language = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();


        return view('admin.banner.detail', [
            'title' => $title,
            'id' => $id,
            'banner' => $banner ?? NULL,
            'language' => $language ?? NULL,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];

        if (!empty($id)) {
            $banner = Banner::find($id);
            $validator = Validator::make($this->request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'content.required' => 'Bạn chưa nhập tên',
                ]
            );
        } else {
            $validator = Validator::make($this->request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'content.required' => 'Bạn chưa nhập tên',
                ]
            );

            $banner = new Banner();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }

        DB::beginTransaction();
        try {
            $title = $this->request->input('title');

            $banner->name = $this->request->input('name');
            $banner->is_background = $this->request->input('is_background') ?? 0;
            $banner->region_id = config('constant.DEFAULT_REGION');
            $banner->is_app = $this->request->input('is_app') ?? 0; // = 1 là banner app, =0 là banner website
            $banner->hidden_button = $this->request->input('hidden_button') ?? 0;
            $banner->show_web_app = $this->request->input('show_web_app') ?? 0;
            $banner->save();
            DB::commit();
            if ($banner) {
                foreach($title as $language => $value) {
                    DB::table('tbl_banner_translations')->updateOrInsert(
                        [
                            'id_banner' => $banner->id,
                            'language' => $language
                        ],
                        [
                            'title' => $value,
                            'content' => $this->request->input('content')[$language] ?? '',
                        ]
                    );
                }


                if ($this->request->hasFile('image')) {
                    if (!empty($banner->image)) {
                        $this->deleteFile($banner->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'banner/' . $banner->id, 600, 600, false);
                    $banner->image = $path;
                    $banner->save();
                }
                if ($this->request->hasFile('image_website')) {
                    if (!empty($banner->image_website)) {
                        $this->deleteFile($banner->image_website);
                    }
                    $path = $this->UploadFile($this->request->file('image_website'), 'banner/' . $banner->id, 600, 600, false);
                    $banner->image_website = $path;
                    $banner->save();
                }

                if ($this->request->hasFile('images')) {
                    foreach($this->request->file('images') as $language => $value) {
                        $path = $this->UploadFile($value, 'banner/' .$banner->id . '/' .  $language, 600, 600, false);
                        DB::table('tbl_banner_translations')
                            ->where('language', $language)
                            ->where('id_banner', $banner->id)
                            ->update([
                                'image' => $path,
                            ]);
                    }
                }
                if ($this->request->hasFile('images_website')) {
                    foreach($this->request->file('images_website') as $language => $value) {
                        $path = $this->UploadFile($value, 'banner/' .  $banner->id . '/' .$language, 600, 600, false);
                        DB::table('tbl_banner_translations')
                            ->where('language', $language)
                            ->where('id_banner', $banner->id)
                            ->update([
                                'image_website' => $path,
                            ]);
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

    public function delete($id){
        $banner = Banner::find($id);

        try {
            $success = $banner->delete();
            if ($success) {
                if (!empty($banner->image)) {
                    $this->deleteFile($banner->image);
                }
                if (!empty($banner->image_website)) {
                    $this->deleteFile($banner->image_website);
                }
                DB::table('tbl_banner_translations')->where('id_banner', $id)->delete();

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
}
