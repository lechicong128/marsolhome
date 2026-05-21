<?php

namespace App\Http\Controllers;

use App\Models\CategoryProducts;
use App\Models\ProductCategory;
use App\Models\GroupPermission;
use App\Models\Language;
use App\Models\Permission;
use App\Models\Products;
use App\Models\ProductsFilter;
use App\Models\ProductsFilterTranslations;
use App\Models\ProductTranslations;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\App;

class ProductsFilterController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
    }

    public function get_list(){
        if (!has_permission('products_filter','view')){
            access_denied();
        }
        return view('admin.products_filter.list',[
            'title' => lang('list_products_filter'),
        ]);
    }

    public function detail($id = ''){
        if (!has_permission('products_filter','view')){
            access_denied();
        }

        if (empty($id)){
            if (!has_permission('products_filter', 'add')){
                access_denied();
            }
            $title = lang('c_add_group_products_filter');
        }
        else {
            if (!has_permission('products_filter', 'edit')){
                access_denied();
            }
            $title = lang('c_edit_group_products_filter');
            $products_filter = ProductsFilter::find($id);
            if(!empty($products_filter->id)) {
                $translations = ProductsFilterTranslations::where('id_product_filter', $id)->get();
                $data_translations = [];
                foreach($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'name' => $translation->name,
                    ];
                }
                $products_filter->translations = $data_translations;
            }
        }
        $language = Language::get();

        return view('admin.products_filter.detail',[
            'id' => $id ?? 0,
            'title' => $title,
            'products_filter' => $products_filter ?? [],
            'language' => $language ?? [],
        ]);
    }

    public function detail_child($id_parent = '', $id = '0'){
        if (!has_permission('products_filter','view')){
            access_denied();
        }
        if (empty($id)){
            if (!has_permission('products_filter', 'add')){
                access_denied();
            }
            $title = lang('c_add_products_filter');
        }
        else {
            if (!has_permission('products_filter', 'edit')){
                access_denied();
            }
            $title = lang('c_edit_products_filter');
            $products_filter = ProductsFilter::find($id);
            if(!empty($products_filter->id)) {
                $translations = ProductsFilterTranslations::where('id_product_filter', $id)->get();
                $data_translations = [];
                foreach($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'name' => $translation->name,
                    ];
                }
                $products_filter->translations = $data_translations;
            }
        }
        $languageActive = Language::where('code_system', App::getLocale())->first();
        $language = Language::get();

        $productFilterParent = ProductsFilter::where('id_parent', 0)
            ->select('tbl_products_filter.*', 'pt.name', 'pt.language', 'pt.id as id_translation')
            ->join('tbl_products_filter_translations as pt', 'pt.id_product_filter', '=', 'tbl_products_filter.id')
            ->where('pt.language', $languageActive->code)
            ->where('tbl_products_filter.id', $id_parent)
            ->first();
        $title .= ' - ' . ($productFilterParent->name ?? '');
        return view('admin.products_filter.detail_child',[
            'id' => $id ?? 0,
            'id_parent' => $id_parent ?? 0,
            'title' => $title,
            'products_filter' => $products_filter ?? [],
            'language' => $language ?? [],
            'productsFilterParent' => $productFilterParent ?? [],
        ]);
    }

    public function getTable(){
        $languageActive = Language::where('code_system', App::getLocale())->first();
        $productsFilter = ProductsFilter::where('id_parent', 0)
            ->with(['transalations.language_detail'])->orderBy('order_by', 'ASC');
        return Datatables::of($productsFilter)
            ->addColumn('options', function ($productsFilter) {
                $edit = "<a class='dt-modal' href='admin/products_filter/detail/$productsFilter->id'><i class='fa fa-pencil'></i> " . lang('c_edit_products_filter') . "</a>";
                $appendChild = "<a class='dt-modal' href='admin/products_filter/detail_child/$productsFilter->id/0'><i class='fa fa-plus'></i> ".lang('append_products_filter_child')."</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/products_filter/delete/'.$productsFilter->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_products_filter') .'</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">'.$edit.'</li>
                                <li style="cursor: pointer">'.$appendChild.'</li>
                                <li style="cursor: pointer">'.$delete.'</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('child', function ($productsFilter) use ($languageActive) {
                $dataChild = ProductsFilter::where('id_parent', $productsFilter->id)
                    ->select('tbl_products_filter.*', 'pt.name', 'pt.language', 'pt.id as id_translation')
                    ->join('tbl_products_filter_translations as pt', 'pt.id_product_filter', '=', 'tbl_products_filter.id')
                    ->where('pt.language', $languageActive->code)
                    ->get();
                foreach($dataChild as $key => $child) {
                    $checked = $child->active == 1 ? 'checked' : '';
                    $dataChild[$key]->active = '<div class="text-center"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/products_filter/changeStatus/'.$child->id.'" data-status="'.$child->active.'"></div>';

                    $checkedMain = $child->filter_main_app == 1 ? 'checked' : '';
                    $dataChild[$key]->filter_main_app = '<div class="text-center"><input type="checkbox" '.$checkedMain.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/products_filter/changeFilterMain/'.$child->id.'" data-status="'.$child->filter_main_app.'"></div>';
                }
                return $dataChild;
            })
            ->editColumn('active', function ($productsFilter) {
                $checked = $productsFilter->active == 1 ? 'checked' : '';
                $str = '<div class="text-center"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/products_filter/changeStatus/'.$productsFilter->id.'" data-status="'.$productsFilter->active.'"></div>';
                return $str;
            })
            ->editColumn('filter_main_app', function ($productsFilter) {
                $checked = $productsFilter->filter_main_app == 1 ? 'checked' : '';
                $str = '<div class="text-center"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/products_filter/changeFilterMain/'.$productsFilter->id.'" data-status="'.$productsFilter->filter_main_app.'"></div>';
                return $str;
            })
            ->editColumn('name', function ($productsFilter) {
               $str = '';
               foreach($productsFilter->transalations as $key => $value) {
                   if(!empty($value['name']) && $value['language'] == App::getLocale()) {
                       $imgLogo = $this->baseUrlAdmin  .'/'. $value['language_detail']['image'];
                       $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span>'.$value['name'].'</span></div>';
                   }
               }
               return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'active', 'filter_main_app', 'child', 'order_by', 'id'])
            ->make(true);
    }

    public function submit($id = 0) {
        $data = [];
        $name = $this->request->input('name');
        $isName = false;
        foreach($name as $language => $value) {
            if(!empty($value)) {
                $isName = true;
                break;
            }
        }
        if(empty($isName)) {
            $data['result'] = false;
            $data['message'] = lang('pls_input_one_name');
            echo json_encode($data);
            die();
        }


        if (!empty($id)) {
            $products_filter = ProductsFilter::find($id);
        }
        else {
            $products_filter = new ProductsFilter();
        }

        $LanguagDefault = Language::where('is_default', 1)->first();
        DB::beginTransaction();
        try {
            $slug = $this->request->input('slug');
            if(empty($slug)) {
                $slug = convertToSlug($this->request->input('name')[$LanguagDefault->code]);
                $ktProducts = Products::where('slug', $slug)->where('id', '!=', $id)->first();
                if(!empty($ktProducts->id)) {
                    $slug = $slug . '-' . time();
                }
            }

            $name = $this->request->input('name');
            $products_filter->name = $this->request->input('name')[$LanguagDefault->code] ?? '';
            $products_filter->save();
            DB::commit();
            if ($products_filter) {
                foreach($name as $language => $value) {
                    DB::table('tbl_products_filter_translations')->updateOrInsert(
                        [
                            'id_product_filter' => $products_filter->id,
                            'language' => $language
                        ],
                        [
                            'name' => $value
                        ]
                    );
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


    public function submit_child($id_parent = 0, $id = 0) {
        $data = [];
        $name = $this->request->input('name');
        $isName = false;
        foreach($name as $language => $value) {
            if(!empty($value)) {
                $isName = true;
                break;
            }
        }
        if(empty($isName)) {
            $data['result'] = false;
            $data['message'] = lang('pls_input_one_name');
            echo json_encode($data);
            die();
        }


        $products_filter_parent = ProductsFilter::find($id_parent);
        if(empty($products_filter_parent->id)) {
            $data['result'] = false;
            $data['message'] = lang('parent_products_filter_not_exist');
            echo json_encode($data);
            die();
        }

        if (!empty($id)) {
            $products_filter = ProductsFilter::find($id);
        }
        else {
            $products_filter = new ProductsFilter();
        }

        $LanguagDefault = Language::where('is_default', 1)->first();
        DB::beginTransaction();
        try {
            $name = $this->request->input('name');
            $products_filter->id_parent = $products_filter_parent->id;
            $products_filter->level = 1;
            $products_filter->name = $this->request->input('name')[$LanguagDefault->code] ?? '';
            $products_filter->save();

            if ($this->request->hasFile('icon')) {
                if (!empty($products_filter->icon)) {
//                    $this->deleteFile($products_filter->icon);
                }

                $path = $this->UploadFile($this->request->file('icon'), 'products_filter/' . $products_filter->id, 600, 600, false);
                $products_filter->icon = $path;
                $products_filter->save();
            }
            if ($this->request->hasFile('icon_active')) {
                if (!empty($products_filter->icon_active)) {
//                    $this->deleteFile($products_filter->icon_active);
                }

                $path = $this->UploadFile($this->request->file('icon_active'), 'products_filter/' . $products_filter->id, 600, 600, false);
                $products_filter->icon_active = $path;
                $products_filter->save();
            }

            DB::commit();
            if ($products_filter) {
                foreach($name as $language => $value) {
                    DB::table('tbl_products_filter_translations')->updateOrInsert(
                        [
                            'id_product_filter' => $products_filter->id,
                            'language' => $language
                        ],
                        [
                            'name' => $value
                        ]
                    );
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
        if (!has_permission('products_filter', 'approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $products_filter = ProductsFilter::find($id);
        try {
            $products_filter->active = $this->request->input('status') == 0 ? 1 : 0;
            $products_filter->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function changeFilterMain($id) {
        $products_filter = ProductsFilter::find($id);
        try {
            $products_filter->filter_main_app = $this->request->input('status') == 0 ? 1 : 0;
            $products_filter->save();
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
        if (!has_permission('products_filter', 'delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $products_filter = ProductsFilter::find($id);
        try {
            if($products_filter->id_parent == 0) {
                $childs = ProductsFilter::where('id_parent', $products_filter->id)->get();
                foreach($childs as $child) {
                    ProductsFilterTranslations::where('id_product_filter', $child->id)->delete();
                    ProductsFilter::where('id', $child->id)->delete();
                }
            }
            $products_filter->delete();
            ProductsFilterTranslations::where('id_product_filter', $id)->delete();

            $data['result'] = true;
            $data['message'] = lang('dt_success');
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
                $job_category = ProductsFilter::find($id);
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
