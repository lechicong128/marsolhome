<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class BlogCategoryController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        $title = lang('dt_blog_category');
        if (!has_permission('blog_category','view')){
            access_denied();
        }
        return view('admin.blog_category.list', [
            'title' => $title,
        ]);
    }

    public function getTable()
    {
        $dtBlogCategory = BlogCategory::orderByRaw('id desc')->get();
        return Datatables::of($dtBlogCategory)
            ->addColumn('options', function ($category) {
                $edit = "<a class='dt-modal' href='admin/blog_category/detail/$category->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_blog_category') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/blog_category/delete/'.$category->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_blog_category') .'</a>';
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
            ->editColumn('name', function ($category) {
                $name = $category->name ?? '';
                return '<div>'.$name.'</div>';
            })
             ->editColumn('active', function ($category) {
                $checked = $category->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#0050c8" data-href="admin/blog_category/changeStatus/'.$category->id.'" data-status="'.$category->active.'">';
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options','name','active'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_blog_category');
            if (!has_permission('blog_category','add')){
                access_denied(true);
            }
        } else {
            if (!has_permission('blog_category','edit')){
                access_denied(true);
            }
            $title = lang('dt_edit_blog_category');
        }
        $blog_category = BlogCategory::find($id);
        return view('admin.blog_category.detail', [
            'title' => $title,
            'id' => $id,
            'blog_category' => $blog_category,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_blog_categories,name,' . $id,
            ]
            , [
                'name.required' => lang('dt_name_required'),
                'name.unique' => lang('dt_name_unique'),
            ]);
        if (!empty($id)){
            $blog_category = BlogCategory::find($id);
        } else {
            $blog_category = new BlogCategory();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        DB::beginTransaction();
        try {
            $name = $this->request->input('name');
            $blog_category->name = $name ?? '';
            $blog_category->save();

            DB::commit();
            if ($blog_category) {
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            } else {
                DB::rollBack();
                $data['result'] = false;
                $data['message'] = lang('dt_error');
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
        if (!has_permission('blog_category','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $blog_category = BlogCategory::find($id);
        DB::beginTransaction();
        try {
            $blogCount = $blog_category->blogs()->count();
            if ($blogCount > 0) {
                $data['result'] = false;
                $data['message'] = 'Loại bài viết này đang được sử dụng trong bài viết, không thể xóa!';
                return response()->json($data);
            }
            $blog_category->delete();
            DB::Commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeStatus($id)
    {
        if (!has_permission('blog_category', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $blog_category = BlogCategory::find($id);
        try {
            $blog_category->active = $this->request->input('status') == 0 ? 1 : 0;
            $blog_category->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
