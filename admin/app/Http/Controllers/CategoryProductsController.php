<?php

namespace App\Http\Controllers;

use App\Models\CategoryProducts;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CategoryProductsController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list(){
        if (!has_permission('category_products','view')){
            access_denied();
        }
        return view('admin.category_products.list',[
            'title' => lang('c_list_category_products'),
        ]);
    }

    public function detail($id = ''){
        if (!has_permission('category_products','view')){
            access_denied();
        }

        if (empty($id)){
            if (!has_permission('category_products', 'add')){
                access_denied();
            }
            $title = lang('c_add_category_products');
        } else {
            if (!has_permission('category_products', 'edit')){
                access_denied();
            }
            $title = lang('c_edit_category_products');
            $category = CategoryProducts::find($id);
        }
        return view('admin.category_products.detail',[
            'id' => $id ?? 0,
            'title' => $title,
            'category' => $category ?? [],
        ]);
    }


    public function getTable(){
        $category = CategoryProducts::query();
        return Datatables::of($category)
            ->addColumn('options', function ($category) {
                $edit = "<a class='dt-modal' href='admin/category_products/detail/$category->id'><i class='fa fa-pencil'></i> " . lang('c_edit_category_products') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/category_products/delete/'.$category->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_category_products') .'</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">'.$edit.'</li>
                                <li style="cursor: pointer">'.$delete.'</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('active', function ($category) {
                $checked = $category->active == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/category_products/changeStatus/'.$category->id.'" data-status="'.$category->active.'"></div>';
                return $str;
            })
            ->editColumn('image', function ($category) {
                $dtImage = !empty($category->image) ? asset('storage/'.$category->image) : 'admin/assets/images/not_available.jpg';
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="'.$dtImage.'" alt="image"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';

                return $str;
            })
            ->editColumn('max_product_review', function ($category) {
                $str = '<div class="text-center">'.number_format($category->max_product_review).'</div>';

                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'active', 'image', 'max_product_review'])
            ->make(true);
    }


    public function submit($id = 0)
    {
        $data = [];
        if (!empty($id)) {
            $category = CategoryProducts::find($id);
            if (!empty($category)){
                if ($category->code != $this->request->input('code')){
                    $validator = Validator::make($this->request->all(),
                        [
                            'code' => 'unique:tbl_category_products,code',
                            'name' => 'required',
                        ],
                        [
                            'code.unique' => lang('code_category_da_duoc_su_dung'),
                            'name.required' => 'Bạn chưa nhập tên',
                        ]
                    );

                } else {
                    $validator = Validator::make($this->request->all(),
                        [
                            'name' => 'required',
                        ],
                        [
                            'name.required' => 'Bạn chưa nhập tên',
                        ]
                    );
                }
            }
        }
        else {
            $validator = Validator::make($this->request->all(),
                [
                    'code' => 'unique:tbl_category_products,code',
                    'name' => 'required',
                ],
                [
                    'code.unique' => lang('code_category_da_duoc_su_dung'),
                    'name.required' => 'Bạn chưa nhập tên',
                ]
            );
            $category = new CategoryProducts();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }

        DB::beginTransaction();
        try {
            $category->code = $this->request->input('code');
            $category->name = $this->request->input('name');
            $category->max_product_review = number_unformat($this->request->input('max_product_review', 0));
            $category->content = $this->request->input('content');
            $category->save();
            DB::commit();
            if ($category) {
                if(empty($category->code)) {
                    $codeCategory = 'DM-' . str_pad($category->id, 6, '0', STR_PAD_LEFT);
                    $ktCodeCategory = CategoryProducts::where('code', $codeCategory)->first();
                    if(empty($ktCodeCategory->id)) {
                        $category->code = $codeCategory;
                    }
                    else {
                        $category->code = 'DM-' . str_pad($category->id, 6, '0', STR_PAD_LEFT). '-1';
                    }
                    $category->save();
                }
                if ($this->request->hasFile('image')) {
                    if (!empty($category->image)) {
                        $this->deleteFile($category->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'category_products/' . $category->id, 600, 600, false);
                    $category->image = $path;
                    $category->save();
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

    public function changeStatus($id) {
        if (!has_permission('category_products', 'approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $category = CategoryProducts::find($id);
        try {
            $category->active = $this->request->input('status') == 0 ? 1 : 0;
            $category->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }


    public function delete($id){
        if (!has_permission('category_products', 'delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $category = CategoryProducts::find($id);
        try {
            $category->delete();
            if (!empty($category->image)){
                $this->deleteFile($category->image);
            }
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }
}
