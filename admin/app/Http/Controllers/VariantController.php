<?php

namespace App\Http\Controllers;

use App\Models\ProductsVariant;
use App\Models\Variant;
use App\Models\VariantTranslations;
use App\Models\VariantOptions;
use App\Models\VariantOptionsTranslations;
use App\Models\ProductCategory;
use App\Models\GroupPermission;
use App\Models\Language;
use App\Models\Permission;
use App\Models\Products;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\App;

class VariantController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
//        $this->locale = app()->getLocale();
    }

    public function get_list(){
        if (!has_permission('variant','view')){
            access_denied();
        }
        return view('admin.variant.list',[
            'title' => lang('list_variant'),
        ]);
    }

    public function detail($id = ''){
        if (!has_permission('variant','view')){
            access_denied();
        }

        if (empty($id)){
            if (!has_permission('variant', 'add')){
                access_denied();
            }
            $title = lang('c_add_variant');
        }
        else {
            if (!has_permission('variant', 'edit')){
                access_denied();
            }
            $title = lang('c_edit_variant');
            $variant = Variant::find($id);
            if(!empty($variant->id)) {
                $translations = VariantTranslations::where('id_variant', $id)->get();
                $data_translations = [];
                foreach($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'name' => $translation->name,
                    ];
                }
                $variant->translations = $data_translations;
            }
        }
        $language = Language::get();

        return view('admin.variant.detail',[
            'id' => $id ?? 0,
            'title' => $title,
            'variant' => $variant ?? [],
            'language' => $language ?? [],
        ]);
    }

    public function detail_child($idVariant = '', $id = '0'){
        if (!has_permission('variant','view')){
            access_denied();
        }
        if (empty($id)){
            if (!has_permission('variant', 'add')){
                access_denied();
            }
            $title = lang('c_add_variant');
        }
        else {
            if (!has_permission('variant', 'edit')){
                access_denied();
            }
            $title = lang('c_edit_variant');
            $VariantOptions = VariantOptions::find($id);
            if(!empty($VariantOptions->id)) {
                $translations = VariantOptionsTranslations::where('id_variant_options', $id)->get();
                $data_translations = [];
                foreach($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'name' => $translation->name,
                    ];
                }
                $VariantOptions->translations = $data_translations;
            }
        }
        $languageActive = Language::where('code_system', App::getLocale())->first();
        $language = Language::get();

        $variant = Variant::select('tbl_variant.*', 'pt.name', 'pt.language', 'pt.id as id_variant')
            ->join('tbl_variant_translations as pt', 'pt.id_variant', '=', 'tbl_variant.id')
            ->where('pt.language', $languageActive->code)
            ->where('tbl_variant.id', $idVariant)
            ->first();
        $title .= ' - ' . ($variant->name ?? '');
        return view('admin.variant.detail_child',[
            'id' => $id ?? 0,
            'idVariant' => $idVariant ?? 0,
            'title' => $title,
            'variant' => $variant ?? [],
            'language' => $language ?? [],
            'VariantOptions' => $VariantOptions ?? [],
        ]);
    }

    public function getTable(){
        $languageActive = Language::where('code_system', App::getLocale())->first();
        $dataTable = Variant::with(['transalations.language_detail'])->orderBy('id', 'desc');
        return Datatables::of($dataTable)
            ->addColumn('options', function ($dataTable) {
                $edit = "<a class='dt-modal' href='admin/variant/detail/$dataTable->id'><i class='fa fa-pencil'></i> " . lang('c_edit_variant') . "</a>";
                $appendChild = "<a class='dt-modal' href='admin/variant/detail_child/$dataTable->id/0'><i class='fa fa-plus'></i> ".lang('append_variant_option_detail')."</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/variant/delete/'.$dataTable->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_variant') .'</a>';
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
            ->addColumn('child', function ($dataTable) use ($languageActive) {
                $dataChild = VariantOptions::where('id_variant', $dataTable->id)
                    ->select('tbl_variant_options.*', 'pt.name', 'pt.language', 'pt.id as id_variant_options')
                    ->join('tbl_variant_options_translations as pt', 'pt.id_variant_options', '=', 'tbl_variant_options.id')
                    ->where('pt.language', ($languageActive->code ?? 'vi'))
                    ->get();
                foreach($dataChild as $key => $child) {
                    $checked = $child->active == 1 ? 'checked' : '';
                    $dataChild[$key]->active = '<div class="text-center"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/variant/changeStatusOption/'.$child->id.'" data-status="'.$child->active.'"></div>';

                }
                return $dataChild;
            })
            ->editColumn('active', function ($dataTable) {
                $checked = $dataTable->active == 1 ? 'checked' : '';
                $str = '<div class="text-center"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#285b23" data-href="admin/variant/changeStatus/'.$dataTable->id.'" data-status="'.$dataTable->active.'"></div>';
                return $str;
            })
            ->editColumn('name', function ($dataTable) use ($languageActive) {
               $str = '';
               foreach($dataTable->transalations as $key => $value) {
                   if(!empty($value['name']) && $value['language'] == $languageActive->code) {
//                       $imgLogo = $this->baseUrlAdmin  .'/'. $value['language_detail']['image'];
//                       $str.= '<div class="m-b-5"><img style="width:20px;height:20px;" src="'.$imgLogo.'"/> <span>'.$value['name'].'</span></div>';
                       $str.= '<div class="m-b-5"><span>'.$value['name'].'</span></div>';
                   }
               }
               return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'active', 'child', 'order_by', 'id'])
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
            $variant = Variant::find($id);
        }
        else {
            $variant = new Variant();
        }

        $LanguagDefault = Language::where('is_default', 1)->first();
        DB::beginTransaction();
        try {
            $name = $this->request->input('name');
            $variant->name = $this->request->input('name')[$LanguagDefault->code] ?? '';
            $variant->save();
            DB::commit();
            if ($variant) {
                foreach($name as $language => $value) {
                    DB::table('tbl_variant_translations')->updateOrInsert(
                        [
                            'id_variant' => $variant->id,
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


    public function submit_child($id_variant = 0, $id = 0) {
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


        $variant = Variant::find($id_variant);
        if(empty($variant->id)) {
            $data['result'] = false;
            $data['message'] = lang('variant_not_exist');
            echo json_encode($data);
            die();
        }

        if (!empty($id)) {
            $variant_options = VariantOptions::find($id);
        }
        else {
            $variant_options = new VariantOptions();
        }

        $LanguagDefault = Language::where('is_default', 1)->first();
        DB::beginTransaction();
        try {
            $name = $this->request->input('name');
            $variant_options->id_variant = $variant->id;
            $variant_options->name = $this->request->input('name')[$LanguagDefault->code] ?? '';
            $variant_options->save();

            DB::commit();
            if ($variant_options) {
                foreach($name as $language => $value) {
                    DB::table('tbl_variant_options_translations')->updateOrInsert(
                        [
                            'id_variant_options' => $variant_options->id,
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
        if (!has_permission('variant', 'approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $variant = Variant::find($id);
        try {
            $variant->active = $this->request->input('status') == 0 ? 1 : 0;
            $variant->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function changeStatusOption($id) {
        if (!has_permission('variant', 'approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $variantOption = VariantOptions::find($id);
        try {
            $variantOption->active = $this->request->input('status') == 0 ? 1 : 0;
            $variantOption->save();
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
        if (!has_permission('variant', 'delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $variant = Variant::find($id);
        try {

            $ktProductVariant = ProductsVariant::where('id_variant', $variant->id)->exists();
            if(!empty($ktProductVariant)) {
                $data['result'] = false;
                $data['message'] = lang('variant_used_in_product_cannot_delete');
                return response()->json($data);
            }

            $getVariantOptions = VariantOptions::where('id_variant', $variant->id)->get();
            foreach($getVariantOptions as $key => $value) {
                VariantOptionsTranslations::where('id_variant_options', $value->id)->delete();
            }
            VariantOptions::where('id_variant', $variant->id)->delete();

            VariantTranslations::where('id_variant', $variant->id)->delete();

            $variant->delete();

            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function delete_child($id){
        if (!has_permission('variant', 'delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $getVariantOptions = VariantOptions::find($id);
        try {
            $ktProductVariant = ProductsVariant::where('id_variant_options', $getVariantOptions->id)->exists();
            if(!empty($ktProductVariant)) {
                $data['result'] = false;
                $data['message'] = lang('variant_options_used_in_product_cannot_delete');
                return response()->json($data);
            }

            VariantOptionsTranslations::where('id_variant_options', $getVariantOptions->id)->delete();
            VariantOptions::where('id', $getVariantOptions->id)->delete();
            $getVariantOptions->delete();

            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function getVariantOptions() {
        $id_variant = $this->request->input('id_variant');
        $variantOptions = VariantOptions::where('tbl_variant_options.id_variant', $id_variant)
            ->select('tbl_variant_options.id', 'pt.name')
            ->LeftJoin('tbl_variant_options_translations as pt', function($q) {
                $q->on('pt.id_variant_options', '=', 'tbl_variant_options.id')
                  ->where('pt.language', app()->getLocale());
            })
            ->get();
        $data['result'] = true;
        $data['variantOptions'] = $variantOptions;
        return response()->json($data);
    }

}
