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
            ->orderBy('order_by', 'DESC')
            ->where(function($q) use ($filter_status_search) {
            if (is_numeric($filter_status_search)) {
                $q->where('tbl_banner.is_app', '=', $filter_status_search);
            }
        });
        return Datatables::of($dtBanner)
            ->addColumn('options', function ($banner) {
                $edit = "<a class='btn btn-xs btn-outline-primary btn-icon dt-modal' href='admin/banner/detail/$banner->id' title='" . lang('edit_banner') . "' style='display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; border: 1px solid #3a94ef; color: #3a94ef; background: transparent; transition: all 0.2s; margin-right: 6px;' onmouseover=\"this.style.background='#3a94ef'; this.style.color='white'\" onmouseout=\"this.style.background='transparent'; this.style.color='#3a94ef'\"><i class='fa fa-pencil'></i></a>";
                
                $delete = '<a type="button" class="btn btn-xs btn-outline-danger btn-icon po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <div style=\'padding: 8px; text-align: center; min-width: 150px;\'>
                        <p style=\'margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 13px;\'>Xác nhận xóa?</p>
                        <div style=\'display: flex; gap: 8px; justify-content: center;\'>
                            <button href=\'admin/banner/delete/' . $banner->id . '\' class=\'btn btn-danger btn-sm dt-delete\' style=\'border-radius: 4px; padding: 4px 10px; font-size: 11px; font-weight: 600;\'>' . lang('dt_delete') . '</button>
                            <button class=\'btn btn-default btn-sm po-close\' style=\'border-radius: 4px; padding: 4px 10px; font-size: 11px; font-weight: 600;\'>' . lang('dt_close') . '</button>
                        </div>
                    </div>" style=\'display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; border: 1px solid #ef4444; color: #ef4444; background: transparent; transition: all 0.2s; cursor: pointer;\' onmouseover="this.style.background=\'#ef4444\'; this.style.color=\'white\'" onmouseout="this.style.background=\'transparent\'; this.style.color=\'#ef4444\'"><i class="fa fa-trash"></i></a>';
                
                return '<div style="display: flex; justify-content: center; align-items: center;">' . $edit . $delete . '</div>';
            })
            ->editColumn('active', function ($data) {
                $checked = $data->active == 1 ? 'checked' : '';
                $str = '<div class="text-center" style="display: flex; justify-content: center; align-items: center;"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/banner/changeStatus/'.$data->id.'" data-status="'.$data->active.'"></div>';
                return $str;
            })
            ->editColumn('image', function ($data) {
                $viTranslation = $data->transalations->where('language', 'vi')->first();
                if ($viTranslation && !empty($viTranslation->image)) {
                    $imgBanner = $this->baseUrl  .'/'. $viTranslation->image;
                    return '<div class="text-center">
                                <a href="'.$imgBanner.'" data-lightbox="customer-profile_'.$data->id.'" class="display-block" style="display: inline-block;">
                                    <img src="'.$imgBanner.'" alt="image" class="img-responsive img-thumbnail" style="width: 80px; height: 50px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                                </a>
                            </div>';
                }
                return '<div class="text-center text-muted">-</div>';
            })
            ->editColumn('image_website', function ($data) {
                $dtImage = !empty($data->image_website) ? asset('storage/' . $data->image_website) : null;
                return loadImage($dtImage);
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'active', 'image'])
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
                        'title' => $translation->title ?? '',
                        'content' => $translation->content ?? '',
                        'image' => $translation->image ?? '',
                        'image_website' => $translation->image_website ?? '',
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
            // $validator = Validator::make($this->request->all(),
            //     [
            //         'name' => 'required',
            //     ],
            //     [
            //         'content.required' => 'Bạn chưa nhập tên',
            //     ]
            // );
        } else {
            // $validator = Validator::make($this->request->all(),
            //     [
            //         'name' => 'required',
            //     ],
            //     [
            //         'content.required' => 'Bạn chưa nhập tên',
            //     ]
            // );

            $banner = new Banner();
        }

        // if ($validator->fails()) {
        //     $data['result'] = 0;
        //     $data['message'] = $validator->errors()->all();
        //     echo json_encode($data);
        //     die();
        // }

        DB::beginTransaction();
        try {
            $title = $this->request->input('title') ?? [];

            $banner->name = $this->request->input('name') ?? [];
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
                        if ($language === 'vi') {
                            $banner->image = $path;
                            $banner->save();
                        }
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

    public function order_by()
    {
        $list_order_by = $this->request->input('list_order_by');
        if (!empty($list_order_by)) {
            foreach ($list_order_by as $id => $order_by) {
                $banner = Banner::find($id);
                if (!empty($banner)) {
                    $banner->order_by = $order_by;
                    $banner->save();
                }
            }
            $data['result'] = true;
            $data['message'] = lang('c_order_by_true');
            return response()->json($data);
        }
        $data['result'] = false;
        $data['message'] = lang('c_order_by_false');
        return response()->json($data);
    }
}
