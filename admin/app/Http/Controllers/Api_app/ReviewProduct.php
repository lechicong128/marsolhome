<?php

namespace App\Http\Controllers\Api_app;

use App\Helpers\FilesHelpers;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Products;
use App\Models\ClientsReview;
use App\Models\ProductsVariant;
use App\Models\ReviewFile;
use App\Models\SignUpReview;
use App\Models\TransferAddress;
use App\Models\TransferAddressRequest;
use App\Models\Variant;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\App;
use Google_Client;
use function Laravel\Prompts\table;
use DateTime;

class ReviewProduct extends AuthController
{
    protected $dbAccount;
    use UploadFile;
    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->SaveSession = true;
        $this->baseUrl = config('services.storage.url');
        $this->dbAccount = $accountService;
    }
    function GetDetailReview($id = '') {
         $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        // $SignUpReview = SignUpReview::where('id', $id)->first();
        $SignUpReview = ClientsReview::query()
        ->select('tbl_clients_sign_up_review.*','pt.name', 'pt.content', 'pt.language',
            DB::raw('CONCAT("'.$this->baseUrl.'/", tbl_products.image) as image'), 'tbl_products.slug', 'tbl_products.date_end_promotion'
        )
        ->where('tbl_clients_sign_up_review.id', $id)
        ->join('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product')
        ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
            $join->on('pt.id_product', '=', 'tbl_products.id')
                ->where('pt.language', '=', $_locale);
        })
        ->first();

        return response()->json($SignUpReview);
    }
    public function list_review() {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $filterSearch = $this->request->input('search');
        $filterStatus = $this->request->input('status');
        $dateStartSignup = $this->request->input('date_start_sign_up');
        if(!empty($dateStartSignup)) {
            $dateStartSignup = to_sql_date($dateStartSignup);
        }
        $dateEndSignup = $this->request->input('date_end_sign_up');
        if(!empty($dateEndSignup)) {
            $dateEndSignup = to_sql_date($dateEndSignup);
        }

        $id_client = $this->request->client->id;
        if(!empty($id_client)) {
            $SignUpReview = SignUpReview::select('tbl_sign_up_review.*')
                ->addSelect('tbl_status_review_translations.name as name_status', 'tbl_status_review.color as status_color')
                ->join('tbl_status_review_translations', 'tbl_status_review_translations.id_status', '=', 'tbl_sign_up_review.status')
                ->join('tbl_status_review', 'tbl_status_review.id', '=', 'tbl_sign_up_review.status')
                ->where('language', $_locale)
                ->whereHas('clients_review.products', function ($p) use ($_locale) {
                    $p->whereExists(function ($sub) use ($_locale) {
                        $sub->from('tbl_product_translations as pt')
                            ->whereColumn('pt.id_product', 'tbl_products.id')
                            ->where('pt.language', $_locale);
                    });
                })
                ->with(['clients_review' => function ($cr) use ($_locale) {
                    $cr->select([
                        'tbl_clients_sign_up_review.*',
                        DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review) AS video_review"),
                        DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review_render) AS video_review_render"),
                        DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.small_image_video_review) AS small_image_video_review"),
                    ])->whereHas('products', function ($p) use ($_locale) {
                        $p->whereExists(function ($sub) use ($_locale) {
                            $sub->from('tbl_product_translations as pt')
                                ->whereColumn('pt.id_product', 'tbl_products.id')
                                ->where('pt.language', $_locale);
                        });
                    })
                        ->with(['products' => function ($joinProduct) use ($_locale) {
                            // Join để lấy tên theo locale (không lọc theo filterSearch ở đây để không ẩn các sp khác của phiếu)
                            $joinProduct->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                                $join->on('pt.id_product', '=', 'tbl_products.id')
                                    ->where('pt.language', $_locale);
                            });
                            $joinProduct->addSelect([
                                'tbl_products.*',
                                DB::raw('pt.name as name'),
                                DB::raw('pt.language as language'),
                                DB::raw('CONCAT("'.$this->baseUrl.'/", tbl_products.image) as image')
                            ]);
                        }]);
                }])
                ->when(!empty($filterSearch), function ($q) use ($filterSearch, $_locale) {
                    $q->where(function ($w) use ($filterSearch, $_locale) {
                        $w->where('tbl_sign_up_review.code_review', 'like', "%{$filterSearch}%")
                            ->orWhereHas('clients_review.products', function ($p) use ($filterSearch, $_locale) {
                                // ràng buộc locale cho chắc
                                $p->whereExists(function ($sub) use ($_locale, $filterSearch) {
                                    $sub->from('tbl_product_translations as pt')
                                        ->whereColumn('pt.id_product', 'tbl_products.id')
                                        ->where('pt.language', $_locale)
                                        ->where(function($qPdocut) use ($filterSearch) {
                                            $qPdocut->where('pt.name', 'like', "%{$filterSearch}%");
                                            $qPdocut->orWhere('tbl_products.code', '=', $filterSearch);
                                        });
                                });
                            });
                    });
                })

                // Các filter khác
                ->when(!empty($id_client), fn($q) => $q->where('tbl_sign_up_review.id_client', $id_client))
                ->when(!empty($filterStatus), fn($q) => $q->where('status', $filterStatus))
                ->when(!empty($dateStartSignup), fn($q) =>
                    $q->whereDate('tbl_sign_up_review.created_at', '>=', $dateStartSignup)
                )
                ->when(!empty($dateEndSignup), function($q) use ($dateEndSignup) {
                    $q->whereRaw('DATE_FORMAT(tbl_sign_up_review.created_at, "%Y-%m-%d") <= ?', [$dateEndSignup]);
                })
                ->orderByDesc('tbl_sign_up_review.created_at')
                ->paginate($per_page, ['*'], 'page', $current_page);

            $SignUpReview->getCollection()->transform(function ($signReview) {
                $clients = $signReview->clients_review ?? collect([]);
                $signReview->success_review = !$clients->contains('is_review', 0);

                foreach($clients as $kReview => $vReview) {
                    $ReviewFile = ReviewFile::where('id_review', $vReview->id)->get();
                    $dataReviewFile = [];
                    foreach ($ReviewFile as $key => $value) {
                        $dataReviewFile[] = [
                            'id' => $value->id,
                            'filetype' => $value->filetype,
                            'media' => $this->baseUrl . '/' . $value->media,
                            'mime_type' => $value->mime_type,
                            'name_file' => $value->name_file,
                        ];
                    }
                    $vReview->media_other = $dataReviewFile;
                }
                return $signReview;
            });
            return response()->json($SignUpReview);
        }
        return response()->json([
            'result' => false,
            'message' => 'Không tìm thấy sản phẩm'
        ]);
    }

    public function get_list_product_review($id = '') {
        $_locale = $this->request->input('_locale');
        $_type = $this->request->input('type', 'sign_up');
        if(empty($_type)) {
            $_type = 'sign_up';
        }
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $id_client = $this->request->client->id;
        $productReviews = ClientsReview::query()
            ->select('tbl_clients_sign_up_review.*','pt.name', 'pt.content',
                DB::raw('CONCAT("'.$this->baseUrl.'/", tbl_products.image) as image'),
                'tbl_products.slug',
                'tbl_products.code',
                'tbl_products.is_use',
                'tbl_products.date_end_promotion',
                'tbl_products.background_color',
                'tbl_products.color_header',
                'tbl_products.background_color',
            )
            ->where(function($q) use ($_type, $id) {
                if($_type == 'sign_up') {
                    $q->where('tbl_clients_sign_up_review.id_review', $id);
                }
                else {
                    $q->where('tbl_clients_sign_up_review.id_transaction', $id);
                }
            })
            ->where('tbl_clients_sign_up_review.type_object', $_type)
            ->where('tbl_clients_sign_up_review.id_client', $id_client)
            ->join('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product')
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'tbl_products.id')
                    ->where('pt.language', '=', $_locale);
            })->get();
        foreach($productReviews as $kReview => $productReview) {
            if (!empty($productReview->id)) {
                $ReviewFile = ReviewFile::where('id_review', $productReview->id)->get();
                $dataReviewFile = [];
                foreach ($ReviewFile as $key => $value) {
                    $dataReviewFile[] = [
                        'id' => $value->id,
                        'filetype' => $value->filetype,
                        'media' => $this->baseUrl . '/' . $value->media,
                        'mime_type' => $value->mime_type,
                        'name_file' => $value->name_file,
                    ];
                }
                $productReview->media_other = $dataReviewFile;
                if (!empty($productReview->video_review)) {
                    $productReview->video_review = $this->baseUrl . '/' . $productReview->video_review;
                }
                if (!empty($productReview->video_review_render)) {
                    $productReview->video_review_render = $this->baseUrl . '/' . $productReview->video_review_render;
                }
                if (!empty($productReview->small_image_video_review)) {
                    $productReview->small_image_video_review = $this->baseUrl . '/' . $productReview->small_image_video_review;
                }
                $productReview->list_evaluate = DB::table('tbl_type_evaluate_translations')
                    ->Join('tbl_client_evaluate', 'tbl_client_evaluate.id_evaluate', '=', 'tbl_type_evaluate_translations.id_evaluate')
                    ->where('tbl_client_evaluate.id_sign_review', $productReview->id)
                    ->where('tbl_type_evaluate_translations.language', $_locale)
                    ->get();

                $productReview->data_active = status_product_review_active($productReview->active, 'all');
            }
        }
        $data['result'] = $productReviews ? true : false;
        $data['data'] = $productReviews;
        return response()->json($data);
    }

    public function get_product_review($id = '') {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);
        $id_client = !empty($this->request->client) ? $this->request->client->id : 0;
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $id_product = $this->request->input('id_product');
        $client_review = ClientsReview::where(function($rC) use ($id_client) {
            if(!empty($id_client)) {
                $rC->where('id_client', $id_client);
            }
        })->where('id', $id)->first();
        if(empty($client_review->id)) {
            $data['result'] = false;
            $data['message'] = lang('Không tìm thấy sản phẩm');
            return response()->json($data);
        }
        else {
            $productReview = ClientsReview::query()
                ->select('tbl_clients_sign_up_review.*','pt.name', 'pt.content', 'pt.language',
                    DB::raw('CONCAT("'.$this->baseUrl.'/", tbl_products.image) as image'), 'tbl_products.slug', 'tbl_products.date_end_promotion'
                )
                ->where('tbl_clients_sign_up_review.id', $id)
                ->join('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product')
                ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                    $join->on('pt.id_product', '=', 'tbl_products.id')
                        ->where('pt.language', '=', $_locale);
                })
                ->first();
            if(!empty($productReview->id)) {
                if($productReview->id_client != $id_client) {
                    $productReview->view_see = ($productReview->view_see + 1);
                    $productReview->save();
                }

                $ReviewFile = ReviewFile::where('id_review', $productReview->id)->get();
                $dataReviewFile = [];
                foreach($ReviewFile as $key => $value) {
                    $dataReviewFile[] =[
                        'id' => $value->id,
                        'filetype' => $value->filetype,
                        'media' => $this->baseUrl .'/' . $value->media,
                        'mime_type' => $value->mime_type,
                        'name_file' => $value->name_file,
                    ];
                }
                $productReview->media_other = $dataReviewFile;
                if(!empty($productReview->video_review)) {
                    $productReview->video_review = $this->baseUrl .'/' . $productReview->video_review;
                }
                if(!empty($productReview->video_review_render)) {
                    $productReview->video_review_render = $this->baseUrl .'/' . $productReview->video_review_render;
                }
                if(!empty($productReview->small_image_video_review)) {
                    $productReview->small_image_video_review = $this->baseUrl .'/' . $productReview->small_image_video_review;
                }

                $productReview->list_evaluate = DB::table('tbl_type_evaluate_translations')
                    ->Join('tbl_client_evaluate', 'tbl_client_evaluate.id_evaluate', '=', 'tbl_type_evaluate_translations.id_evaluate')
                    ->where('tbl_client_evaluate.id_sign_review', $productReview->id)
                    ->where('tbl_type_evaluate_translations.language', $_locale)
                    ->get();
            }

            $data['result'] = true;
            $data['data'] = $productReview;
            return response()->json($data);
        }
    }

    public function submitReview($id = '', Request $request = null) {
        $request = $request ?? $this->request;
        $_locale = $request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $id_client = $this->request->client->id;
        $client_review = ClientsReview::where('id_client', $id_client)
            ->where('id', $id)
            ->first();

        if(empty($client_review->id)) {
            $data['result'] = false;
            $data['message'] = lang('Không tìm thấy sản phẩm');
            return response()->json($data);
        }
        else {
            if(empty($request->input('list_evaluate'))) {
                $data['result'] = false;
                $data['message'] = lang('Vui lòng chọn số sao muốn đánh giá sản phẩm');
                return response()->json($data);
            }
            if(empty($client_review->is_review) && !$request->hasFile('video_review')) {
                if(empty($client_review->video_review)) {
                    $data['result'] = false;
                    $data['message'] = lang('Vui lòng upload video đánh giá sản phẩm');
                    return response()->json($data);
                }
            }
            if(!empty($client_review->active)) {
                $data['result'] = false;
                $data['message'] = lang('Sản phẩm đã được duyệt đánh giá không thể chỉnh sửa');
                return response()->json($data);
            }

            $evaluateArray = $request->input('list_evaluate');
            $evaluateTotal = 0;
            foreach($evaluateArray as $key => $value) {
                $evaluateTotal += $value;
            }
            $evaluate = ($evaluateTotal / count($evaluateArray));

            $DataReview = ClientsReview::find($id);

            $DataReview->evaluate = $evaluate;
            $DataReview->content_evaluate = $request->input('content_evaluate') ?? '';
            $DataReview->is_review = 1;
            $DataReview->date_review = date('Y-m-d H:i:s');
            $DataReview->save();


            DB::table('tbl_client_evaluate')->where('id_sign_review', $DataReview->id)->delete();
            foreach($evaluateArray as $id_evaluate => $value) {
                DB::table('tbl_client_evaluate')->insert([
                    'id_sign_review' => $DataReview->id,
                    'id_evaluate' => $id_evaluate,
                    'star' => $value,
                ]);
            }


            if ($request->hasFile('video_review')) {
                if (!empty($DataReview->video_review)) {
                    $this->deleteFile($DataReview->video_review);
                }
                $path = $this->UploadFile($request->file('video_review'), 'review/' . $DataReview->id, 0, 0, false);
                $DataReview->video_review = $path;
                $DataReview->save();
            }

            if ($request->hasFile('media_other')) {
                if (!empty($request->file('media_other'))) {
                    if (is_array($request->file('media_other'))) {
                        foreach ($request->file('media_other') as $file) {
                            $filetype = $file->getClientOriginalExtension();
                            $name_file = $file->getClientOriginalName();
                            $file_size = $file->getSize();
                            $mime_type = $file->getMimeType();
                            $ReviewFile = new ReviewFile();
                            $path = $this->UploadFile($file, 'review_other/' . $DataReview->id, 800, 600,false);
                            $ReviewFile->media = $path;
                            $ReviewFile->id_review = $client_review->id;
                            $ReviewFile->filetype = $filetype;
                            $ReviewFile->name_file = $name_file;
                            $ReviewFile->file_size = $file_size;
                            $ReviewFile->mime_type = $mime_type;
                            $ReviewFile->type = explode('/', $mime_type)[0];
                            $ReviewFile->staff_id = get_staff_user_id();
                            $ReviewFile->save();
                        }
                    }
                }
            }
            if($request->input('media_other_delete')) {
                $media_other_delete = $request->input('media_other_delete');
                foreach($media_other_delete as $key => $value) {
                    $ReviewFileDelete = ReviewFile::where('id', $value)->where('id_review', $DataReview->id)->first();
                    if(!empty($ReviewFileDelete->id)) {
                        $this->deleteFile($ReviewFileDelete->media);
                        $ReviewFileDelete->delete();
                    }
                }
            }

            $average_star = ClientsReview::select(DB::raw('CAST(ROUND(AVG(evaluate), 1) AS DECIMAL(3,1)) as average_star'))
                ->where('id_product', $DataReview->id_product)
                ->where('evaluate','>', 0)
                ->first();
            $average_star = $average_star->average_star;
            $DataProduct = Products::find($DataReview->id_product);
            $DataProduct->average_star = $average_star;
//            if($client_review->is_review == 0) {
//                $DataProduct->quantity_reviews = ($DataProduct->quantity_reviews + 1);
//            }
            $DataProduct->save();
            Notification::notiYouReview($client_review->id, 'send_review');
            $data['result'] = true;
            $data['message'] = lang('Đánh giá thành công');
            return response()->json($data);
        }


        $data['result'] = false;
        $data['message'] = lang('Đánh giá không thành công');
        return response()->json($data);
    }

    public function get_product_review_public($id = '') {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);
        $id_client = !empty($this->request->client) ? $this->request->client->id : 0;
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }

        $id_product = $this->request->input('id_product');
        $client_review = ClientsReview::where('id', $id)->first();
        if(empty($client_review->id)) {
            $data['result'] = false;
            $data['message'] = lang('Không tìm thấy sản phẩm');
            return response()->json($data);
        }
        else {
            $productReview = ClientsReview::query()
                ->select('tbl_clients_sign_up_review.*','pt.name', 'pt.content', 'pt.language',
                    DB::raw('CONCAT("'.$this->baseUrl.'/", tbl_products.image) as image'), 'tbl_products.slug', 'tbl_products.date_end_promotion',
                    'tbl_products.code',
                    'tbl_products.is_use',
                    'tbl_products.background_color',
                    'tbl_products.color_header',
                    'tbl_products.background_color',
                    'tbl_products.quantity_reviews',
                )
                ->where('tbl_clients_sign_up_review.id', $id)
                ->join('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product')
                ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                    $join->on('pt.id_product', '=', 'tbl_products.id')
                        ->where('pt.language', '=', $_locale);
                })
                ->first();
            if(!empty($productReview->id)) {
                if($productReview->id_client != $id_client) {
                    $productReview->view_see = ($productReview->view_see + 1);
                    $productReview->save();
                }
                $listClient = [$productReview->id_client];
                if(!empty($listClient)) {
                    $this->request->merge(['list_id' => $listClient]);
                    $responseClient = $this->dbAccount->getListDetailCustomer($this->request);
                    $dataClient = $responseClient->getData(true);
                    $productReview->client = $dataClient['clients'][$productReview->id_client] ?? [];
                }

                $ReviewFile = ReviewFile::where('id_review', $productReview->id)->get();
                $dataReviewFile = [];
                foreach($ReviewFile as $key => $value) {
                    $dataReviewFile[] =[
                        'id' => $value->id,
                        'filetype' => $value->filetype,
                        'media' => $this->baseUrl .'/' . $value->media,
                        'mime_type' => $value->mime_type,
                        'name_file' => $value->name_file,
                    ];
                }
                $productReview->media_other = $dataReviewFile;
                if(!empty($productReview->video_review)) {
                    $productReview->video_review = $this->baseUrl .'/' . $productReview->video_review;
                }

                $productReview->list_evaluate = DB::table('tbl_type_evaluate_translations')
                    ->Join('tbl_client_evaluate', 'tbl_client_evaluate.id_evaluate', '=', 'tbl_type_evaluate_translations.id_evaluate')
                    ->where('tbl_client_evaluate.id_sign_review', $productReview->id)
                    ->where('tbl_type_evaluate_translations.language', $_locale)
                    ->get();
            }

            $data['result'] = true;
            $data['data'] = $productReview;
            return response()->json($data);
        }
    }

    public function get_my_review() {
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }


        $filterSearch = $this->request->input('search');
        $filterStatus = is_numeric($this->request->input('status')) ? $this->request->input('status') : -1;
        $dateStartSignup = $this->request->input('date_start_sign_up');
        if(!empty($dateStartSignup)) {
            $dateStartSignup = to_sql_date($dateStartSignup);
        }

        $dateEndSignup = $this->request->input('date_end_sign_up');
        if(!empty($dateEndSignup)) {
            $dateEndSignup = to_sql_date($dateEndSignup);
        }

        $id_client = $this->request->client->id;
        $id_product = $this->request->input('id_product');
        $ListProductReview = ClientsReview::query()
            ->select('tbl_clients_sign_up_review.*','pt.name', 'pt.content', 'pt.language',
                'tbl_products.slug',
                'tbl_products.code',
                'tbl_products.is_use',
                DB::raw('CONCAT("'.$this->baseUrl.'/", tbl_products.image) as image'), 'tbl_products.slug', 'tbl_products.date_end_promotion',
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review) AS video_review"),
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review_render) AS video_review_render"),
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.small_image_video_review) AS small_image_video_review")
            )
            ->leftJoin('tbl_sign_up_review as sgu', function ($join) {
                $join->on('sgu.id', '=', 'tbl_clients_sign_up_review.id_review');
            })
//            ->leftJoin('tbl_status_review as isStatus', function ($join) {
//                $join->on('isStatus.id', '=', 'sgu.status');
//            })
//            ->join('tbl_status_review_translations as ReviewStatus', function ($join) use ($_locale) {
//                $join->on('ReviewStatus.id_status', '=', 'sgu.status')
//                    ->where('ReviewStatus.language', '=', $_locale);
//            })
//            ->addSelect('ReviewStatus.name as name_status', 'isStatus.color as status_color')
            ->where('tbl_clients_sign_up_review.id_client', $id_client)
            ->where('tbl_clients_sign_up_review.is_review', 1)
            ->join('tbl_products', 'tbl_products.id', '=', 'tbl_clients_sign_up_review.id_product')
            ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                $join->on('pt.id_product', '=', 'tbl_products.id')
                    ->where('pt.language', '=', $_locale);
            })
            ->when(!empty($filterSearch), function ($q) use ($filterSearch, $_locale) {
                $q->where(function ($w) use ($filterSearch, $_locale) {
                    $w->where('sgu.code_review', 'like', "%{$filterSearch}%");
                    $w->orWhere('pt.name', 'like', "%{$filterSearch}%");
                    $w->orWhere('tbl_products.code', '=', $filterSearch);
                });
            })
            ->when(($filterStatus >= 0), fn($q) => $q->where('tbl_clients_sign_up_review.active', $filterStatus))
            ->when(!empty($dateStartSignup), fn($q) =>
                $q->whereDate('sgu.created_at', '>=', $dateStartSignup)
            )
            ->when(!empty($dateEndSignup), function($q) use ($dateEndSignup) {
                $q->whereRaw('DATE_FORMAT(sgu.created_at, "%Y-%m-%d") <= ?', [$dateEndSignup]);
            })
            ->paginate($per_page, ['*'], 'page', $current_page);
        if(!empty($ListProductReview->items())) {
            $ItemProductReview = $ListProductReview->items();
            foreach($ItemProductReview as $keyReview => $productReview) {
                $ReviewFile = ReviewFile::where('id_review', $productReview->id)->get();
                $dataReviewFile = [];
                foreach ($ReviewFile as $key => $value) {
                    $dataReviewFile[] = [
                        'id' => $value->id,
                        'filetype' => $value->filetype,
                        'media' => $this->baseUrl . '/' . $value->media,
                        'mime_type' => $value->mime_type,
                        'name_file' => $value->name_file,
                    ];
                }
                $productReview->media_other = $dataReviewFile;
                $ListProductReview->items()[$keyReview] = $productReview;

                $ListProductReview->items()[$keyReview]['data_active'] = status_product_review_active($productReview->active, 'all');
            }
        }

        $data['result'] = true;
        $data['data'] = $ListProductReview;
        return response()->json($data);
    }

    public function type_active_review() {
        $_locale = $this->request->input('_locale');
        $id_client = $this->request->client->id ?? 0;
        $nameAll = lang('all');
//        if(empty($_locale)) {
//            $_locale = 'vi';
//            $nameAll = 'Tất cả';
//        }
//        else if($_locale == 'en') {
//            $nameAll = 'All';
//        }
//        else if($_locale == 'kr') {
//            $nameAll = '모두';
//        }

        $data = status_product_review_active();
        $countAll = 0;
        if(!empty($id_client)) {
            foreach($data as $key => $value) {
                $data[$key]['countActive'] = DB::table('tbl_clients_sign_up_review')
                    ->where('id_client', $id_client)
                    ->where('active', $value['id'])
                    ->where('is_review', 1)
                    ->count();
                $countAll += $data[$key]['countActive'];
            }
        }
        $data = array_merge([
            [
                'id' => -1,
                'name' => $nameAll,
                'color' => '#fe6bba',
                'countActive' => $countAll,
            ]
        ], $data);
        return response()->json($data, 200);
    }

    public function getDataClientReview(){
        $type = $this->request->input('type') ?? 'count';
        $arrCustomerId = $this->request->input('customer_id') ?? [0];
        $searchValue = $this->request->input('searchValue') ?? null;
        if(empty($arrCustomerId)){
            $arrCustomerId =[0];
        }
        $arrCustomerId = is_array($arrCustomerId) ? $arrCustomerId : [$arrCustomerId];
        $listIds = "(" . implode(",", $arrCustomerId) . ")";

        $listIDsearch = [];
        if ($type == 'list') {
            if (!empty($searchValue)) {
                $newRequest = clone $this->request;
                $newRequest->merge(['search' => $searchValue]);
                $responseClientSearch = $this->dbAccount->getListDetailCustomer($newRequest);
                $dataClientSearch = $responseClientSearch->getData(true);
                if (!empty($dataClientSearch) && !empty($dataClientSearch['clients'])) {
                    $dataClientSearch = $dataClientSearch['clients'];
                    foreach ($dataClientSearch as $value) {
                        $listIDsearch[] = $value['id'];
                    }
                }
            }
        }


        $baseQuery = DB::table('tbl_sign_up_review')
            ->select('tbl_sign_up_review.*')
            ->when(!empty($searchValue), function ($q) use ($searchValue, $listIDsearch) {
                $q->where(function ($w) use ($searchValue, $listIDsearch) {
                    $w->where('tbl_sign_up_review.code_review', 'like', "%{$searchValue}%")
                        ->orWhereIn('tbl_sign_up_review.id_client', $listIDsearch);
                });
            })
            ->join(DB::raw("
                (SELECT id_client, MIN(id) AS first_date
                 FROM tbl_sign_up_review
                 WHERE tbl_sign_up_review.id_client IN $listIds
                 GROUP BY tbl_sign_up_review.id_client) t
            "), function($join) {
                $join->on('tbl_sign_up_review.id_client', '=', 't.id_client');
                $join->on('tbl_sign_up_review.id', '=', 't.first_date');
            });
        if ($type == 'count'){
            $dtData = $baseQuery->count();
        } elseif($type == 'id'){
            $dtData = $baseQuery->get()->pluck('id_client')->toArray();
        } else {
            $dtData = $baseQuery->get();
        }
        $data = [
            'result' => true,
            'type' => $type,
            'data' => $dtData
        ];
        return response()->json($data, 200);
    }


    //review sản phẩm trong đơn hàng
    public function reviewMultipleProductsInTransaction()
    {
        $id_client = $this->request->client->id;
        $id_transaction = $this->request->input('id_transaction', 0);
        $reviews_data = $this->request->all()['reviews'] ?? [];

        $response = $this->dbAccount->getTransactionDetailById($this->request, $id_transaction);
        $dataTransaction = $response->getData(true);
        if(empty($dataTransaction['result'])) {
            return response()->json([
                'result' => false,
                'message' => lang('not_find_transaction'),
            ]);
        }
        $productsVariant = [];
        $productsVariantQuantity = [];
        foreach($dataTransaction['data']['items'] as $key => $item) {
            $productsVariant[$item['product_id']][$item['variant_id']] = $item['id'];
            $productsVariantQuantity[$item['product_id']][$item['variant_id']] = $item['quantity'];
        }
        foreach ($reviews_data as $key => $item) {
            $id_product = $item['id_product'] ?? 0;
            $variant_id = $item['variant_id'] ?? 0;
            if (!isset($productsVariant[$id_product][$variant_id])) {
                return response()->json([
                    'result' => false,
                    'message' => lang('not_find_transaction_product'),
                ]);
            }
        }



        if (empty($reviews_data) || !is_array($reviews_data)) {
            return response()->json([
                'result' => false,
                'message' => lang('not_find_transaction_product'),
            ]);
        }

        $all_results = [];

        $ktTransaction = DB::table('tbl_transaction_review')->where('id_transaction', $id_transaction)->where('id_client', $id_client)->first();
        if(empty($ktTransaction->id)) {
            DB::table('tbl_transaction_review')->insert([
                'id_transaction' => $id_transaction,
                'id_client' => $id_client,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        foreach ($reviews_data as $key => $item) {
            $id_product = $item['id_product'] ?? 0;
            $variant_id = $item['variant_id'] ?? 0;

            $ClientsReview = ClientsReview::where('id_client', $id_client)
                ->where('type_object', 'transaction')
                ->where('id_product', $id_product)
                ->where('id_transaction', $id_transaction)
                ->where('variant_id', $variant_id)
                ->first();
            if(empty($ClientsReview->id)) {
                $ClientsReview = new ClientsReview();
                $ClientsReview->id_client = $id_client;
                $ClientsReview->type_object = 'transaction';
                $ClientsReview->id_product = $id_product;
                $ClientsReview->id_transaction = $id_transaction;
                $ClientsReview->variant_id = $variant_id;
                $ClientsReview->code_review = getReference('review_transaction');
                $ClientsReview->is_review = 0;
                $ClientsReview->quantity = $productsVariantQuantity[$id_product][$variant_id] ?? 1;
                $ClientsReview->save();
                if ($ClientsReview->id) {
                    updateReference('review_transaction');
                }
            }
            else if($ClientsReview->id) {
                if($ClientsReview->quantity != ($productsVariantQuantity[$id_product][$variant_id] ?? 1)) {
                    $ClientsReview->quantity = $productsVariantQuantity[$id_product][$variant_id] ?? 1;
                    $ClientsReview->save();
                }
            }


            if (empty($ClientsReview->id)) {
                $all_results[] = [
                    'result' => false,
                    'message' => lang('error_create_review_record'),
                    'id_product' => $id_product,
                    'variant_id' => $variant_id,
                ];
                continue;
            }
            $files = [];

            if ($this->request->hasFile("reviews.$key.video_review")) {
                $files['video_review'] = $this->request->file("reviews.$key.video_review");
            }

            if ($this->request->hasFile("reviews.$key.media_other")) {
                $files['media_other'] = $this->request->file("reviews.$key.media_other");
            }
            $newRequest = Request::create(
                $this->request->url(),
                'POST',
                $item,
                [],
                $files,
                $this->request->server()
            );


            // gắn client + locale để submitReview dùng
            $newRequest->client = $this->request->client;

            if ($this->request->has('_locale')) {
                $newRequest->merge(['_locale' => $this->request->input('_locale')]);
            }

//            app()->instance('request', $newRequest);
//            $this->request = $newRequest;

            $response = $this->submitReview($ClientsReview->id, $newRequest);
            $result = json_decode($response->getContent(), true);

            $result['id_product'] = $id_product;
            $result['variant_id'] = $variant_id;

            $all_results[] = $result;
//            $this->request = $reviews_data;
        }


        $this->dbAccount->CheckTransactionReview($this->request, $id_transaction);
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $content_review = get_option('content_review_'.$_locale);
        $content_review = json_decode($content_review, true);
        if($content_review) {
            $point = (int)get_option('number_coins_received_review', 0);
            $content_review['content'] = str_replace(['{point}'], [formatMoney($point)], $content_review['content']);
        }
        return response()->json([
            'result'  => true,
            'message' => lang('review_product_success'),
            'details' => $all_results,
            'content_review' => $content_review,
        ]);
    }


    public function list_review_group() {
        $filterStatus = $this->request->input('status');
        $id_client = $this->request->client->id;
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $reviewProduct = DB::table('tbl_sign_up_review')
            ->select(
                'id',
                'id_client',
                DB::raw('id as id_object'),
                'created_at',
                DB::raw("'sign_up' as type_object")
            )
            ->where('id_client', $id_client)
            ->when(is_numeric($filterStatus), function ($q) use ($filterStatus) {
                $q->whereExists(function ($query) use ($filterStatus) {
                    $query->select(DB::raw(1))
                        ->from('tbl_clients_sign_up_review as csr')
                        ->whereColumn('csr.id_review', 'tbl_sign_up_review.id')
                        ->where('csr.type_object', 'sign_up')
                        ->where('csr.active', $filterStatus);
                });
            });

        $reviewTransaction = DB::table('tbl_transaction_review')
            ->select(
                'id',
                'id_client',
                'id_transaction as id_object',
                'created_at',
                DB::raw("'transaction' as type_object")
            )->where('id_client', $id_client)
            ->when(is_numeric($filterStatus), function ($q) use ($filterStatus) {
                $q->whereExists(function ($query) use ($filterStatus) {
                    $query->select(DB::raw(1))
                        ->from('tbl_clients_sign_up_review as csr')
                        ->whereColumn('csr.id_transaction', 'tbl_transaction_review.id_transaction')
                        ->where('csr.type_object', 'transaction')
                        ->where('csr.active', $filterStatus);
                });
            });

        $unionQuery = $reviewProduct->unionAll($reviewTransaction);

        $reviews = DB::query()
            ->fromSub($unionQuery, 'reviews')
            ->orderByDesc('created_at')
            ->offset(($current_page - 1) * $per_page)
            ->limit($per_page + 1) // 👈 lấy dư 1
            ->get();

        $hasNext = $reviews->count() > $per_page;
        $reviews = $reviews->take($per_page);

        $items = $reviews->map(function ($dataReview) use ($_locale) {
            $items = ClientsReview::select([
                'tbl_clients_sign_up_review.*',
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review) AS video_review"),
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.video_review_render) AS video_review_render"),
                DB::raw("CONCAT('".$this->baseUrl."/', tbl_clients_sign_up_review.small_image_video_review) AS small_image_video_review"),
            ])->addSelect(
                DB::raw('pt.name as name'),
                DB::raw('pt.language as language'),
                DB::raw('CONCAT("'.$this->baseUrl.'/", p.image) as image'),
                DB::raw('p.price as price')
            )
                ->leftJoin('tbl_products as p', 'p.id', '=', 'tbl_clients_sign_up_review.id_product')
                ->leftJoin('tbl_product_translations as pt', function ($join) use ($_locale) {
                    $join->on('pt.id_product', '=', 'p.id')
                        ->where('pt.language', $_locale);
                })
                ->where(function($query) use ($dataReview) {
                    if($dataReview->type_object == 'sign_up') {
                        $query->where('id_review', $dataReview->id_object);
                    }
                    else if($dataReview->type_object == 'transaction') {
                        $query->where('id_transaction', $dataReview->id_object);
                    }
                    $query->where('tbl_clients_sign_up_review.type_object', $dataReview->type_object);
                })->get();



            foreach($items as $kReview => $productReview) {
                if (!empty($productReview->id)) {

                    $variant = ProductsVariant::select('tbl_variant_options.id', 'vot.name as name', 'tbl_products_variant.price as price')
                    ->LeftJoin('tbl_variant_options', 'tbl_variant_options.id', '=', 'tbl_products_variant.id_variant_options')
                    ->LeftJoin('tbl_variant_options_translations as vot', function($join) use ($_locale) {
                        $join->on('vot.id_variant_options', '=', 'tbl_variant_options.id')
                            ->where('vot.language', $_locale);
                    })
                    ->where('id_product', $productReview->id_product)
                    ->where('tbl_variant_options.id', $productReview->variant_id)
                    ->first();

                    $productReview->variant = $variant;


                    $ReviewFile = ReviewFile::where('id_review', $productReview->id)->get();
                    $dataReviewFile = [];
                    foreach ($ReviewFile as $key => $value) {
                        $dataReviewFile[] = [
                            'id' => $value->id,
                            'filetype' => $value->filetype,
                            'media' => $this->baseUrl . '/' . $value->media,
                            'mime_type' => $value->mime_type,
                            'name_file' => $value->name_file,
                        ];
                    }
                    $productReview->media_other = $dataReviewFile;
                    $productReview->data_active = status_product_review_active($productReview->active, 'all');
                    if(!empty($variant)){
                        $productReview->price = 0;
                    } else {
                        $productReview->price = $productReview->price;
                    }
                }
            }

            return [
                'id'          => $dataReview->id,
                'id_client'  => $dataReview->id_client,
                'id_object'  => $dataReview->id_object,
                'type_object'=> $dataReview->type_object,
                'created_at' => $dataReview->created_at,
                'items' => $items,

            ];
        });

        return response()->json([
            'data' => $items,
            'current_page' => (int) $current_page,
            'per_page'     => (int) $per_page,
            'has_next'     => $hasNext,
            'next_page'    => $hasNext ? $current_page + 1 : null,
        ]);

//        return response()->json([
//            'result' => false,
//            'message' => 'Không tìm thấy sản phẩm'
//        ]);
    }

    public function getListData(){
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        $code_review = is_array($this->request->input('code_review')) ? $this->request->input('code_review') : [$this->request->input('code_review')];
        $query = ClientsReview::with(['product' => function($query){
            $query->select('id','code','name',DB::raw('CONCAT("'.$this->baseUrl.'/", image) as image'));
            $query->with(['variant_option' => function($q){
                $q->select('tbl_products_variant.id_variant_options as id','name','tbl_products_variant.id_variant');
            }]);
        }])->select('id','code_review','id_product','variant_id','id_client','content_evaluate',
            DB::raw('CONCAT("'.$this->baseUrl.'/", video_review) as video_review'),
            DB::raw('CONCAT("'.$this->baseUrl.'/", video_review_render) as video_review_render'),
            DB::raw('CONCAT("'.$this->baseUrl.'/", small_image_video_review) as small_image_video_review'),
        )
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('code_review', 'like', "%$search%");
            });
        }
        if (!empty($code_review)) {
            $query->whereIn('code_review', $code_review);
        }
        $data = $query->limit($limit)->get();

        $customer_ids = $data->pluck('id_client')->unique()->values()->toArray();
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $customer_ids]);
        $this->requestCustomer->merge(['search' => null]);
        $responseCustomer = $this->dbAccount->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $customers = collect($dataCustomer['data']);

        $data = $data->map(function($item) use ($customers){
            $customer = $customers->where('id','=',$item->id_client)->first();
            $item->customer = $customer;
            if($item->product && $item->product->variant_option) {
                $item->product->variant_option = $item->product->variant_option->map(function($variant) {
                    $variant->price = $variant->pivot->price ?? null;
                    unset($variant->pivot);
                    return $variant;
                });
            }

            $prices = $item->product->variant_option->pluck('price')->filter()->unique()->values();

            if ($prices->isEmpty()) {
                $item->product->price_display = null;
            } elseif ($prices->count() === 1) {
                $item->product->price_display = [$prices->first()];
            } else {
                $item->product->price_display = [
                    $prices->min(),
                    $prices->max()
                ];
            }

            return $item;
        });
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }


    //review sản phẩm trong dùng thử nghiệm đăng ký
    public function reviewMultipleProductsInSignUp()
    {
        $id_client = $this->request->client->id;
        $id_sign_up = $this->request->input('id_sign_up', 0);
        $reviews_data = $this->request->all()['reviews'] ?? [];

        $SignUpReview = SignUpReview::find($id_sign_up);
        if(empty($SignUpReview->id)) {
            return response()->json([
                'result' => false,
                'message' => lang('not_find_sign_up'),
            ]);
        }
        $items = ClientsReview::where('id_review', $id_sign_up)->get()->toArray();
        $productsVariant = [];
        $productsVariantQuantity = [];
        foreach($items as $key => $item) {
            $item['variant_id'] = $item['variant_id'] ?? 0;
            $productsVariant[$item['id_product']][$item['variant_id']] = $item['id'];
            $productsVariantQuantity[$item['id_product']][$item['variant_id']] = $item['quantity'] ?? 1;
        }
        foreach ($reviews_data as $key => $item) {
            $id_product = $item['id_product'] ?? 0;
            $variant_id = $item['variant_id'] ?? 0;
            if (!isset($productsVariant[$id_product][$variant_id])) {
                return response()->json([
                    'result' => false,
                    'message' => lang('not_find_sign_up_product'),
                ]);
            }
        }



        if (empty($reviews_data) || !is_array($reviews_data)) {
            return response()->json([
                'result' => false,
                'message' => lang('not_find_sign_up_product'),
            ]);
        }

        $all_results = [];
//
//        DB::table('tbl_transaction_review')->insert([
//            'id_transaction' => $id_transaction,
//            'id_client' => $id_client,
//            'created_at' => date('Y-m-d H:i:s'),
//            'updated_at' => date('Y-m-d H:i:s'),
//        ]);
        foreach ($reviews_data as $key => $item) {
            $id_product = $item['id_product'] ?? 0;
            $variant_id = $item['variant_id'] ?? 0;

            $ClientsReview = ClientsReview::where('id_client', $id_client)
                ->where('type_object', 'sign_up')
                ->where('id_product', $id_product)
                ->where('id_review', $id_sign_up)
                ->where('variant_id', $variant_id)
                ->first();


            if (empty($ClientsReview->id)) {
                $all_results[] = [
                    'result' => false,
                    'message' => lang('error_create_review_record'),
                    'id_product' => $id_product,
                    'variant_id' => $variant_id,
                ];
                continue;
            }
            $files = [];
            if ($this->request->hasFile("reviews.$key.video_review")) {
                $files['video_review'] = $this->request->file("reviews.$key.video_review");
            }

            if ($this->request->hasFile("reviews.$key.media_other")) {
                $files['media_other'] = $this->request->file("reviews.$key.media_other");
            }
            $newRequest = Request::create(
                $this->request->url(),
                'POST',
                $item,
                [],
                $files,
                $this->request->server()
            );


            // gắn client + locale để submitReview dùng
            $newRequest->client = $this->request->client;

            if ($this->request->has('_locale')) {
                $newRequest->merge(['_locale' => $this->request->input('_locale')]);
            }

            //            app()->instance('request', $newRequest);
            //            $this->request = $newRequest;

            $response = $this->submitReview($ClientsReview->id, $newRequest);
            $result = json_decode($response->getContent(), true);

            $result['id_product'] = $id_product;
            $result['variant_id'] = $variant_id;

            $all_results[] = $result;
            //            $this->request = $reviews_data;
        }
        $_locale = $this->request->input('_locale');
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        $content_review = get_option('content_review_'.$_locale);
        $content_review = json_decode($content_review, true);
        if($content_review) {
            $point = (int)get_option('number_coins_received_review', 0);
            $content_review['content'] = str_replace(['{point}'], [formatMoney($point)], $content_review['content']);
        }
        return response()->json([
            'result'  => true,
            'message' => lang('review_product_success'),
            'details' => $all_results,
            'content_review' => $content_review,
        ]);
    }

    public function CountReviewClient() {
        app(\App\Http\Middleware\CheckLoginApi::class)
            ->getDataToken($this->request);
        $id_client = $this->request->client->id ?? 0;
        if(empty($id_client)) {
            $id_client = $this->request->input('id_client', 0);
        }
        $countReview = ClientsReview::where('id_client', $id_client)
            ->where('is_review', 1)
            ->where('active', 1)
            ->count();
        return response()->json([
            'result' => true,
            'data' => [
                'count_review' => $countReview,
            ],
        ]);
    }
}
