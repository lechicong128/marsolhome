<?php

namespace App\Http\Controllers;

use App\Models\CategoryProducts;
use App\Models\ProductCategory;
use App\Models\GroupPermission;
use App\Models\Language;
use App\Models\Permission;
use App\Models\Products;
use App\Models\ProductsFilter;
use App\Models\ProductTranslations;
use App\Models\Variant;
use App\Models\VariantOptions;
use App\Models\VariantTranslations;
use App\Models\VariantOptionsTranslations;
use App\Models\ProductsVariant;
use App\Models\TagProduct;
use App\Models\Unit;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ProductsController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
        $this->currentLanguage = app()->getLocale();
    }

    public function get_list()
    {
        if (!has_permission('products', 'view')) {
            access_denied();
        }
        return view('admin.products.list', [
            'title' => lang('list_products'),
        ]);
    }

    public function detail($id = '')
    {
        if (!has_permission('products', 'view')) {
            access_denied();
        }

        if (empty($id)) {
            if (!has_permission('products', 'add')) {
                access_denied();
            }
            $title = lang('c_add_products');
        } else {
            if (!has_permission('products', 'edit')) {
                access_denied();
            }
            $title = lang('c_edit_products');
            $products = Products::find($id);
            if (!empty($products->id)) {
                $list_images = DB::table('tbl_products_images')->where('id_product', $id)
                    ->orderBy('order_by', 'asc')
                    ->orderBy('id', 'desc')->get();
                $products->list_images = $list_images ?? [];
                $ingredients = DB::table('tbl_product_ingredients')->where('id_product', $id)->get();
                $data_ingredients = [];
                foreach ($ingredients as $ingredient) {
                    $data_ingredients[$ingredient->language][] = [
                        'title' => $ingredient->title,
                        'name' => $ingredient->name,
                        'content' => $ingredient->content,
                        'product_uses' => $ingredient->product_uses ?? 0,
                    ];
                }
                $products->ingredients = $data_ingredients;
                $translations = DB::table('tbl_product_translations')->where('id_product', $id)->get();
                $data_translations = [];
                foreach ($translations as $translation) {
                    $data_translations[$translation->language] = [
                        'name' => $translation->name,
                        'content' => $translation->content,
                    ];
                }
                $products->translations = $data_translations;


                $product_category = ProductCategory::where('id_product', $id)->get();
                $data_category = [];
                foreach ($product_category as $category) {
                    $data_category[] = $category->id_category;
                }
                $products->product_category = $data_category;


                $tag_products_filter = DB::table('tbl_tag_products_filter')->where('id_product', $id)->get();
                $data_products_filter = [];
                foreach ($tag_products_filter as $value) {
                    $data_products_filter[] = $value->id_product_filter;
                }
                $products->products_filter = $data_products_filter;
            }
        }

        $language = Language::get();
        $category_products = CategoryProducts::get();
        $languageActive = Language::where('code_system', $this->currentLanguage)->first();

        $products_filter = ProductsFilter::select('tbl_products_filter.*', 'pt.name as name')
            ->leftJoin('tbl_products_filter_translations as pt', 'pt.id_product_filter', '=', 'tbl_products_filter.id')
            ->where('pt.language', $languageActive->code)
            ->where('id_parent', 0)
            ->get()->toArray();
        foreach ($products_filter as $key => $value) {
            $products_filter[$key]['child'] = ProductsFilter::select('tbl_products_filter.*', 'pt.name as name')
                ->where('id_parent', $value['id'])
                ->leftJoin('tbl_products_filter_translations as pt', 'pt.id_product_filter', '=', 'tbl_products_filter.id')
                ->where('pt.language', $languageActive->code)
                ->get();
        }
        $tag_product = TagProduct::all();

        $variant = Variant::with(['transalations_active.language_detail'])
            ->with(['variant_options.transalations_active.language_detail'])
            ->get()->toArray();
        if (!empty($products->id_variant)) {
            $variantOptions = VariantOptions::with(['transalations_active.language_detail'])
                ->where('id_variant', $products->id_variant)
                ->get()->toArray();

            $variantProduct = ProductsVariant::select('tbl_products_variant.*', 'vot.name as name_variant_options')
                ->LeftJoin('tbl_variant_options', 'tbl_variant_options.id', '=', 'tbl_products_variant.id_variant_options')
                ->LeftJoin('tbl_variant_options_translations as vot', function ($join) use ($languageActive) {
                    $join->on('vot.id_variant_options', '=', 'tbl_variant_options.id')
                        ->where('vot.language', $languageActive->code);
                })
                ->where('id_product', $products->id)
                ->get()->toArray();

            $variantProductActive = [];
            foreach ($variantProduct as $key => $value) {
                $variantProductActive[] = $value['id_variant_options'];
            }
            $products->id_variant_options = $variantProductActive;
        }

        $unit = Unit::get();


        return view('admin.products.detail', [
            'id' => $id ?? 0,
            'title' => $title,
            'products' => $products ?? [],
            'language' => $language ?? [],
            'category_products' => $category_products ?? [],
            'products_filter' => $products_filter ?? [],
            'tag_product' => $tag_product ?? [],
            'variant' => $variant ?? [],
            'variantOptions' => $variantOptions ?? [],
            'variantProduct' => $variantProduct ?? [],
            'unit' => $unit ?? [],
        ]);
    }

    public function getTable()
    {
        $currentLanguage = app()->getLocale();
        // 1. Lấy dữ liệu sản phẩm cùng với các quan hệ cần thiết
        $products = Products::with(['transalations.language_detail'])->with('category.category_detail');

        return Datatables::of($products)

            // Cột Hành động (Options)
            ->addColumn('options', function ($products) {
                $edit = "<a href='admin/products/detail/$products->id'><i class='fa fa-pencil'></i> " . lang('c_edit_products') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
            <button href=\'admin/products/delete/' . $products->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
            <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_products') . '</a>';

                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                              Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })

            // Cột Active (Tình trạng kích hoạt)
            ->editColumn('active', function ($products) {
                $checked = $products->active == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" ' . $checked . ' name="active" class="active dt-active" data-plugin="switchery" data-color="#285b23" data-href="admin/products/changeStatus/' . $products->id . '" data-status="' . $products->active . '"></div>';
                return $str;
            })

            // Cột Is Use (Đang sử dụng)
//            ->editColumn('is_use', function ($products) {
//                $checked = $products->is_use == 1 ? 'checked' : '';
//                $str = '<div><input type="checkbox" ' . $checked . ' name="active" class="active dt-active" data-plugin="switchery" data-color="#285b23" data-href="admin/products/changeUse/' . $products->id . '" data-status="' . $products->is_use . '"></div>';
//                return $str;
//            })

            // Cột Is Hot (Sản phẩm hot)
            ->editColumn('is_hot', function ($products) {
                $checked = $products->is_hot == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" ' . $checked . ' name="active" class="active dt-active" data-plugin="switchery" data-color="#285b23" data-href="admin/products/changeIsHot/' . $products->id . '" data-status="' . $products->is_hot . '"></div>';
                return $str;
            })

            // Cột Tên (Name) - Dịch thuật
            ->editColumn('name', function ($products) use ($currentLanguage) {
                $str = '';
                foreach ($products->transalations as $key => $value) {
                    if (!empty($value['name']) && $value['language'] == $currentLanguage) {
                        $imgLogo = $this->baseUrlAdmin . '/' . $value['language_detail']['image'];
                        $str .= '<div class="m-b-5"><img style="width:20px;height:20px;" src="' . $imgLogo . '"/> <span>' . $value['name'] . '</span></div>';
                    }
                }
                return $str;
            })

            // Cột Danh mục (Category) - Quan hệ
            ->editColumn('category', function ($products) {
                $str = '';
                foreach ($products->category as $key => $value) {
                    // Hiển thị tên danh mục từ quan hệ lồng nhau category_detail
                    $str .= '<div class="m-b-5"><span>' . $value['category_detail']['name'] . '</span></div>';
                }
                return $str;
            })

            // 🚨 KHẮC PHỤC LỖI TÌM KIẾM: Ngăn chặn Datatables tìm kiếm trên cột 'category' trong DB
            ->filterColumn('category', function ($query, $keyword) {
                // Sử dụng whereHas để tìm kiếm trong quan hệ lồng nhau (category -> category_detail)
                // Giả sử tên cột chứa tên danh mục là 'name' trong bảng category_details (từ category_detail)

                // Chuyển từ khóa tìm kiếm sang chữ thường để khớp với truy vấn lỗi ban đầu (LOWER)
                $searchKeyword = strtolower($keyword);

                $query->whereHas('category', function ($q1) use ($searchKeyword) {
                    // $q1 là truy vấn trên bảng trung gian (hoặc bảng category nếu quan hệ 1-n)

                    // Tiếp tục tìm kiếm trong quan hệ lồng nhau 'category_detail'
                    $q1->whereHas('category_detail', function ($q2) use ($searchKeyword) {
                        // $q2 là truy vấn trên bảng category_details
                        $q2->whereRaw('LOWER(name) LIKE ?', ["%{$searchKeyword}%"]);
                    });
                });
            })
            // --------------------------------------------------------------------------------------

            // Cột Giá (Price)
            ->editColumn('price', function ($products) {
                if(!empty($products->id_variant)){
                    if ($products->price_min == $products->price_max) {
                        return '<div class="text-right"><a class="dt-update text-center btn btn-xs">' . formatMoney($products->price_min) . '</a></div>';
                    }
                    return '<div class="text-center"><a class="dt-update text-center btn btn-xs">' . formatMoney($products->price_min) . ' - ' . formatMoney($products->price_max) . '</a></div>';
                } else {
                    return '<div class="text-right"><a class="dt-update text-center btn btn-xs">' . formatMoney($products->price) . '</a></div>';
                }
    //                return '<div class="text-right">'.formatMoney($products->price_min).' - '.formatMoney($products->price_max).'</div>';
            })

            // Cột Đánh giá trung bình (Average Star)
            ->editColumn('average_star', function ($products) {
                $str = 'Chưa đánh giá';
                if (!empty($products->average_star)) {
                    $str = '<div class="rating">';
                    // Logic hiển thị sao
                    for ($i = 0; $i < floor($products->average_star); $i++) {
                        $str .= '<span class="star"><i class="fa fa-star" style="font-size: 12px"></i></span>';
                    }
                    if ($products->average_star < 5 && (ceil($products->average_star) / $products->average_star) != 1) {
                        $str .= '<span class="star"><i class="fa fa-star-half-o" style="font-size: 12px"></i></span>';
                    }
                    $str .= '</div>';
                    $str .= '<div>(' . $products->average_star . ' sao)</div>';
                }
                return '<div class="text-center">' . $str . '</div>';
            })

            // Cột Ngày kết thúc khuyến mãi (Date End Promotion)
//            ->editColumn('date_end_promotion', function ($products) {
//                $str = '';
//                if (!empty($products->date_end_promotion)) {
//                    $str = '<div class="text-center">' . _dt($products->date_end_promotion) . '</div>';
//                }
//                return $str;
//            })

            // Cột Hình ảnh (Image)
            ->editColumn('image', function ($products) {
                $dtImage = !empty($products->image) ? asset('storage/' . $products->image) : 'admin/assets/images/not_available.jpg';
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                             class="show_image">
                             <img src="' . $dtImage . '" alt="image"
                                  class="img-responsive img-circle"
                                  style="width: 50px;height: 50px">
                         </div>';
                return $str;
            })

            // Cột Số người tham gia (Count Join)
//            ->editColumn('count_join', function ($products) {
//                if (!empty($products->limit_people)) {
//                    $str = '<a class="dt-update text-center btn btn-xs btn-info">' . number_format($products->count_join) . '/' . number_format($products->limit_people) . '</a>';
//                } else {
//                    $str = '<a class="text-center btn btn-xs btn-danger">' . number_format($products->count_join) . '</a>';
//                }
//                return '<div class="text-center">' . $str . '</div>';
//            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')

            // Các cột được phép hiển thị dưới dạng HTML thô
            ->rawColumns(['options', 'name', 'active', 'is_hot', 'is_use', 'image', 'slug', 'date_end_promotion', 'category', 'count_join', 'average_star', 'price'])

            // Kết thúc và trả về Datatables response
            ->make(true);
    }

    public function submit($id = 0)
    {
        $data = [];
        $name = $this->request->input('name');
        $product_category = $this->request->input('product_category');
        $products_filter = $this->request->input('products_filter');
        $isName = false;
        foreach ($name as $language => $value) {
            if (!empty($value)) {
                $isName = true;
                break;
            }
        }
        if (empty($isName)) {
            $data['result'] = false;
            $data['message'] = lang('pls_input_one_name_product');
            echo json_encode($data);
            die();
        }


        if (!empty($id)) {
            $products = Products::find($id);
            if (!empty($products)) {
                if ($products->code != $this->request->input('code')) {
                    $validator = Validator::make($this->request->all(),
                        [
                            'code' => 'unique:tbl_products,code',
                            'name' => 'required',
                        ],
                        [
                            'code.unique' => lang('code_products_da_duoc_su_dung'),
                            'name.required' => lang('pls_input_one_name_product'),
                        ]
                    );

                } else {
                    $validator = Validator::make($this->request->all(),
                        [
                            'name' => 'required',
                        ],
                        [
                            'name.required' => lang('pls_input_one_name_product'),
                        ]
                    );
                }
            }
        } else {
            $validator = Validator::make($this->request->all(),
                [
                    'code' => 'unique:tbl_products,code',
                    'name' => 'required',
                ],
                [
                    'code.unique' => lang('code_products_da_duoc_su_dung'),
                    'name.required' => lang('pls_input_one_name_product')
                ]
            );
            $products = new Products();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }

        $LanguagDefault = Language::where('is_default', 1)->first();
        DB::beginTransaction();
        $price = number_unformat($this->request->input('price')) ?? 0;
        try {
            $variant_options = $this->request->input('variant_options');
            $id_variant_options = $this->request->input('id_variant_options') ?? [];
            $id_variant = !empty($this->request->input('id_variant')) ? $this->request->input('id_variant') : 0;
            $slug = $this->request->input('slug');
            if (empty($slug)) {
                $slug = convertToSlug($this->request->input('name')[$LanguagDefault->code]);
                $ktProducts = Products::where('slug', $slug)->where('id', '!=', $id)->first();
                if (!empty($ktProducts->id)) {
                    $slug = $slug . '-' . time();
                }
            }

            if(!empty($id_variant)){
                if(empty($id_variant_options)){
                    $data['result'] = 0;
                    $data['message'] = lang('Vui lòng chọn chi tiết biến thể!');
                    echo json_encode($data);
                    die();
                }
            } else {
                if(empty($price)){
                    $data['result'] = 0;
                    $data['message'] = lang('Vui lòng đơn giá khi không có biến thể!');
                    echo json_encode($data);
                    die();
                }
            }

            $imagesDelete = $this->request->input('imagesDelete');
            $ingredients = $this->request->input('ingredients');
            $order_images = $this->request->input('order_images');
            $name = $this->request->input('name');
            $products->background_color = $this->request->input('background_color');
            $products->color_header = $this->request->input('color_header');
            $products->code = $this->request->input('code');
            $products->contribute = $this->request->input('contribute') ?? 0;
            $products->name = $this->request->input('name')[$LanguagDefault->code] ?? '';
            $products->content = $this->request->input('content')[$LanguagDefault->code] ?? '';
            $products->date_end_promotion = to_sql_date($this->request->input('date_end_promotion'), true);
            $products->slug = $slug;
            $products->limit_people = number_unformat($this->request->input('limit_people'));
            $products->price = $price;
            $products->check_free_ship = !empty($this->request->input('check_free_ship')) ? $this->request->input('check_free_ship') : 0;
            $products->id_variant = $id_variant;
            $products->unit_id = $this->request->input('unit_id');
            $products->save();
            DB::commit();
            if (!empty($products->id)) {
                if (!empty($imagesDelete)) {
                    $imgDelete = DB::table('tbl_products_images')->whereIn('id', $imagesDelete)->get();
                    foreach ($imgDelete as $img) {
                        if (!empty($img->image)) {
                            $this->deleteFile($img->image);
                        }
                    }
                    DB::table('tbl_products_images')->whereIn('id', $imagesDelete)->delete();
                }
                if (!empty($name)) {
                    foreach ($name as $language => $value) {
                        DB::table('tbl_product_translations')->updateOrInsert(
                            [
                                'id_product' => $products->id,
                                'language' => $language
                            ], [
                                'name' => $value,
                                'content' => $this->request->input('content')[$language] ?? '',
                            ]
                        );
                    }
                }
                DB::table('tbl_product_category')->where('id_product', $products->id)->delete();
                if (!empty($product_category)) {
                    foreach ($product_category as $key => $id_category) {
                        DB::table('tbl_product_category')->Insert(
                            [
                                'id_product' => $products->id,
                                'id_category' => $id_category
                            ],
                        );
                    }
                }


                DB::table('tbl_tag_products_filter')->where('id_product', $products->id)->delete();
                if (!empty($products_filter)) {
                    foreach ($products_filter as $key => $id_product_filter) {
                        DB::table('tbl_tag_products_filter')->Insert(
                            [
                                'id_product' => $products->id,
                                'id_product_filter' => $id_product_filter
                            ],
                        );
                    }
                }

                foreach ($ingredients as $language => $ingredient) {
                    foreach ($ingredient as $key => $value) {
                        DB::table('tbl_product_ingredients')->updateOrInsert(
                            [
                                'id_product' => $products->id,
                                'language' => $language,
                                'key_index' => $key ?? 0
                            ], [
                                'title' => $value['title'] ?? '',
                                'name' => $value['name'] ?? '',
                                'content' => $value['content'] ?? '',
                                'product_uses' => !empty($value['product_uses']) ? 1 : 0,
                            ]
                        );
                    }
                }
                if (empty($products->code)) {
                    $codeProduct = 'SP-' . str_pad($products->id, 6, '0', STR_PAD_LEFT);
                    $ktCodeProduct = Products::where('code', $codeProduct)->first();
                    if (empty($ktCodeProduct->id)) {
                        $products->code = $codeProduct;
                    } else {
                        $products->code = 'SP-' . str_pad($products->id, 6, '0', STR_PAD_LEFT) . '-1';
                    }
                    $products->save();
                }
                if ($this->request->hasFile('image')) {
                    if (!empty($products->image)) {
                        $this->deleteFile($products->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'products/' . $products->id, 600, 600, false);
                    $products->image = $path;
                    $products->save();
                }

                if (!empty($order_images)) {
                    foreach ($order_images as $key => $value) {
                        DB::table('tbl_products_images')->where('id', $key)->update(['order_by' => $value]);
                    }
                }

                $tag_product = $this->request->tag_product;
                $products->tag()->detach();
                if (!empty($tag_product)) {
                    foreach ($tag_product as $id) {
                        $products->tag()->attach($id);
                    }
                }

                DB::table('tbl_products_variant')
                    ->whereNotIn('id_variant_options', $id_variant_options)
                    ->where('id_product', $products->id)
                    ->delete();
                if (!empty($variant_options)) {
                    foreach ($variant_options as $idoptions => $value) {
                        DB::table('tbl_products_variant')->updateOrInsert(
                            [
                                'id_variant' => $id_variant,
                                'id_variant_options' => $idoptions,
                                'id_product' => $products->id,
                            ],
                            [
                                'price' => number_unformat($value['price']),
                            ]
                        );
                    }
                }

                $price_min = ProductsVariant::where('id_product', $products->id)->min('price');
                $price_max = ProductsVariant::where('id_product', $products->id)->max('price');
                $products->price_min = $price_min;
                $products->price_max = $price_max;
                $products->save();


                if ($this->request->hasFile('images')) {
                    foreach ($this->request->file('images') as $key => $value) {
                        $path = $this->UploadFile($value, 'products/' . $products->id, 600, 600, false);
                        DB::table('tbl_products_images')->insert([
                            'id_product' => $products->id,
                            'image' => $path,
                        ]);
                    }
                }

                DB::commit();
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

    public function changeStatus($id)
    {
        if (!has_permission('products', 'approve')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $products = Products::find($id);
        try {
            $products->active = $this->request->input('status') == 0 ? 1 : 0;
            $products->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function changeIsHot($id)
    {
        if (!has_permission('products', 'approve')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $products = Products::find($id);
        try {
            $products->is_hot = $this->request->input('status') == 0 ? 1 : 0;
            $products->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function changeUse($id)
    {
        if (!has_permission('products', 'approve')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $products = Products::find($id);
        try {
            $products->is_use = $this->request->input('status') == 0 ? 1 : 0;
            $products->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function delete($id)
    {
        if (!has_permission('products', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $products = Products::find($id);
        try {
            $products->delete();
            if (!empty($products->image)) {
                $this->deleteFile($products->image);
            }
            DB::table('tbl_product_translations')->where('id_product', $id)->delete();
            DB::table('tbl_product_ingredients')->where('id_product', $id)->delete();
            DB::table('tbl_products_variant')->where('id_product', $id)->delete();
            $products_images = DB::table('tbl_products_images')->where('id_product', $id)->get();
            if (!empty($products_images)) {
                foreach ($products_images as $img) {
                    if (!empty($img->image)) {
                        $this->deleteFile($img->image);
                    }
                }
            }

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
