<?php

namespace App\Http\Controllers;

use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\DataTables;
use App\Services\AccountService;
use App\Models\Home;
use App\Models\HomeMedia;
use App\Models\Province;
use App\Models\TypeProperty;
use App\Models\Utility;
use App\Models\HouseOrientation;
use App\Models\LegalDocument;
use App\Models\InteriorHandover;
use App\Models\InteriorAmenity;
use App\Models\Ward;
use App\Models\District;
use Illuminate\Support\Facades\Validator;

class ManageHomeController extends Controller
{
    use UploadFile;
    protected $dbAccount;

    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->dbAccount = $accountService;
    }
    public function view($id = 0){
        $title = 'Xem chi tiết bất động sản';
        $home = null;
        $initial_province_name = '';
        $initial_district_name = '';
        $initial_ward_name = '';
        $full_address = '';

        if ($id > 0) {
            $home = Home::with(['interior_amenities', 'propertyType', 'direction', 'legal', 'interior', 'media_items', 'documents_red', 'documents_other'])->find($id);
            if ($home && $home->ward_id) {
                if ($home->is_new_address) {
                    $ward = DB::table('tbl_wards_new')->where('id', $home->ward_id)->first();
                    if ($ward) {
                        $initial_ward_name = $ward->name;
                        $prov = DB::table('tbl_provinces')->where('id', $home->province_id)->first();
                        $initial_province_name = $prov ? $prov->name : '';
                    }
                } else {
                    $ward = DB::table('tblward')->where('wardid', $home->ward_id)->first();
                    if ($ward) {
                        $initial_ward_name = $ward->name;
                        $dist = DB::table('tbldistrict')->where('districtid', $ward->districtid)->first();
                        $initial_district_name = $dist ? $dist->name : '';
                        $prov = DB::table('tblprovince')->where('provinceid', $home->province_id)->first();
                        $initial_province_name = $prov ? $prov->name : '';
                    }
                }
                $addressParts = array_filter([$home->address, $initial_ward_name, $initial_district_name, $initial_province_name]);
                $full_address = implode(', ', $addressParts);
            }
        }

        if (!$home) {
            return redirect('admin/manage_home/list');
        }

        // Parse media
        $mediaUrls = [];
        $mediaCaptions = [];
        if ($home && $home->media_items) {
            foreach ($home->media_items->sortBy('sort_order') as $mediaItem) {
                $url = $mediaItem->url;
                if (!empty($url)) {
                    if (!str_starts_with($url, 'http') && !str_starts_with($url, '/')) {
                        $mediaUrls[] = asset('storage/' . $url);
                    } else {
                        $mediaUrls[] = $url;
                    }
                    $mediaCaptions[] = $mediaItem->caption ?? '';
                }
            }
        }

        // Parse video
        $videoUrl = '';
        if (!empty($home->video_url)) {
            if (!str_starts_with($home->video_url, 'http') && !str_starts_with($home->video_url, '/')) {
                $videoUrl = asset('storage/' . $home->video_url);
            } else {
                $videoUrl = $home->video_url;
            }
        }

        $typeHome = getListTypeHome($home->type);
        $user = Auth::guard('admin')->user();
        $apiToken = '';
        if ($user) {
            $privateKey = file_get_contents(storage_path('keys/private.pem'));
            $payload = [
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'guard' => 'admin',
                'date' => date('Y-m-d H:i:s'),
            ];
            $apiToken = JWT::encode($payload, $privateKey, 'RS256');
        }

          if(!empty($home->price) && $home->price > 0) {
            $home->profit = $home->type == 1 ? ((($home->currently_rent * 12)/$home->price) * 100) : 0;
        } else {
            $home->profit = 0;
        }

        // Parse red book documents
        $redBookUrls = [];
        if ($home && $home->documents_red) {
            foreach ($home->documents_red->sortBy('sort_order') as $doc) {
                $url = $doc->url;
                if (!empty($url)) {
                    if (!str_starts_with($url, 'http') && !str_starts_with($url, '/')) {
                        $redBookUrls[] = asset('storage/' . $url);
                    } else {
                        $redBookUrls[] = $url;
                    }
                }
            }
        }

        // Parse other documents
        $otherDocUrls = [];
        if ($home && $home->documents_other) {
            foreach ($home->documents_other->sortBy('sort_order') as $doc) {
                $url = $doc->url;
                if (!empty($url)) {
                    if (!str_starts_with($url, 'http') && !str_starts_with($url, '/')) {
                        $otherDocUrls[] = asset('storage/' . $url);
                    } else {
                        $otherDocUrls[] = $url;
                    }
                }
            }
        }

        return view('admin.manage_home.view', [
            'title' => $title,
            'home' => $home,
            'full_address' => $full_address,
            'initial_province_name' => $initial_province_name,
            'initial_district_name' => $initial_district_name,
            'initial_ward_name' => $initial_ward_name,
            'mediaUrls' => $mediaUrls,
            'mediaCaptions' => $mediaCaptions,
            'videoUrl' => $videoUrl,
            'typeHome' => $typeHome,
            'apiToken' => $apiToken,
            'redBookUrls' => $redBookUrls,
            'otherDocUrls' => $otherDocUrls,
        ]);
    }
    public function list(){
        if (!has_permission('manage_home', 'view')) {
            access_denied();
        }
        $title = lang('manage_home');
        return view('admin.manage_home.list', [
            'title' => $title,
        ]);
    }

    public function getList() {
        $check_foryou = $this->request->input('check_foryou');
        $customer_favourite_id = $this->request->input('customer_favourite_id');
        $customer_search = $this->request->input('customer_search');
        $property_type_search = $this->request->input('property_type_search');
        $status_search = $this->request->input('status_search');
        $query = DB::table('tbl_home as h')
            ->select([
                'h.id',
                'h.code',
                'h.type',
                'h.title',
                'h.description',
                'h.address',
                'h.price',
                'h.area',
                'h.beds',
                'h.baths',
                'h.status',
                'h.contact_name',
                'h.contact_phone',
                'h.contact_role',
                'h.customer_id',
                'h.ward_id',
                'h.province_id',
                'h.is_new_address',
                'h.created_at',
                'h.is_featured',
                'h.is_vip',
                'h.is_new',
                'tp.name as property_type_name',
                'wn.name as ward_name',
                'pn.name as province_name',
                'h.end_date as end_date',
            ])
            ->leftJoin('tbl_type_property as tp', 'tp.id', '=', 'h.property_type')
            ->leftJoin('tbl_wards_new as wn', function ($join) {
                $join->on('wn.id', '=', 'h.ward_id');
            })
            ->leftJoin('tbl_provinces as pn', function ($join) {
                $join->on('pn.id', '=', 'h.province_id');
            });
            if (!empty($customer_search)) {
                $query->where('h.customer_id', $customer_search);
            }
            if (!empty($customer_favourite_id)) {
                $query->whereExists(function ($q) use ($customer_favourite_id) {
                    $q->select(DB::raw(1))
                    ->from('tbl_favourite_home')
                    ->whereColumn('tbl_favourite_home.home_id', 'h.id')
                    ->where('tbl_favourite_home.customer_id', $customer_favourite_id);
                });
            }
            if(!empty($property_type_search)){
                $query->where('h.property_type', $property_type_search);
            }
            if(!empty($status_search)){
                //tin hết hạn
                if($status_search == 6){
                    $query->where('h.end_date', '<', date('Y-m-d'));
                } else {
                    $query->where('h.status', $status_search);
                }
            }

            // Smart Search Suggestion Filters
            $suggestion_ward_id = $this->request->input('suggestion_ward_id');
            $suggestion_is_new_address = $this->request->input('suggestion_is_new_address');
            $suggestion_type = $this->request->input('suggestion_type');
            $suggestion_to_price = $this->request->input('suggestion_to_price');
            $suggestion_text = $this->request->input('suggestion_text');
            $suggestion_street = $this->request->input('suggestion_street');

            if (!empty($suggestion_ward_id)) {
                $query->where('h.ward_id', $suggestion_ward_id)
                      ->where('h.is_new_address', $suggestion_is_new_address);
            }

            if (!empty($suggestion_type)) {
                $typeVal = ($suggestion_type === 'Mua bán') ? 1 : 2;
                $query->where('h.type', $typeVal);
            }

            if (!empty($suggestion_to_price)) {
                $query->where('h.price', '<', (int)$suggestion_to_price * 1000000);
            }

            if (!empty($suggestion_street)) {
                $query->where('h.address', 'LIKE', '%' . $suggestion_street . '%');
            }

            if (!empty($suggestion_text)) {
                $propertyTypes = DB::table('tbl_type_property')->get();
                foreach ($propertyTypes as $pt) {
                    $ptName = ($pt->name === 'Đất bán') ? 'Đất' : $pt->name;
                    if (stripos($suggestion_text, $ptName) !== false) {
                        $query->where('h.property_type', $pt->id);
                        break;
                    }
                }
            }


        $start  = (int) $this->request->input('start', 0);
        $length = (int) $this->request->input('length', 10);

        $pageRows = (clone $query)
            ->skip($start)
            ->take($length)
            ->get(['h.id', 'h.customer_id']);

        $pageHomeIds     = $pageRows->pluck('id')->filter()->unique()->values()->toArray();
        $pageCustomerIds = $pageRows->pluck('customer_id')->filter()->unique()->values()->toArray();


        $dtCustomer = collect();
        if (!empty($pageCustomerIds)) {
            $requestCustomer = new Request();
            $requestCustomer->replace(['customer_id' => $pageCustomerIds]);
            $responseCustomer = $this->dbAccount->getListData($requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $dtCustomer = collect($dataCustomer['data'] ?? []);
        }

        $dtMedia = collect();
        if (!empty($pageHomeIds)) {
            $dtMedia = DB::table('tbl_home_media')
                ->whereIn('home_id', $pageHomeIds)
                ->orderBy('sort_order')
                ->get(['home_id', 'url'])
                ->groupBy('home_id');
        }

        return DataTables::of($query)
            ->setRowClass(function ($item) {
                if (!empty($item->end_date) && $item->end_date < date('Y-m-d')) {
                    return 'expired-row';
                }
                return '';
            })
            ->filter(function ($q) {
                $search = $this->request->input('search.value');
                if (!empty($search)) {
                    $q->where(function($subQ) use ($search) {
                        $subQ->where('h.title', 'like', "%{$search}%")
                             ->orWhere('h.code', 'like', "%{$search}%")
                             ->orWhere('h.address', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('options', function ($item) {
                $id = $item->id;
                $view = "<a href='admin/manage_home/view/$id' class='flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors'><i class='fa fa-eye text-slate-400'></i> " . lang('dt_view') . "</a>";
                $edit = "<a href='admin/manage_home/detail/$id' class='flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors'><i class='fa fa-pencil text-slate-400'></i> " . lang('Sửa BĐS') . "</a>";
                $delete = '<a type="button" class="po-delete flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50/50 transition-colors" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <div class=\'p-1 text-center\'>
                    <p class=\'text-xs text-slate-600 mb-2 font-medium\'>Xác nhận xóa bất động sản này?</p>
                    <div class=\'flex gap-2 justify-center\'>
                        <button href=\'admin/manage_home/delete/' . $id. '\' class=\'btn btn-danger btn-xs dt-delete\'>' . lang('dt_delete') . '</button>
                        <button class=\'btn btn-default btn-xs po-close\'>' . lang('dt_close') . '</button>
                    </div>
                </div>
            "><i class="fa fa-remove text-red-400"></i> ' . lang('Xóa BĐS') . '</a>';
            
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-white hover:bg-slate-50 border border-slate-200 rounded-xl shadow-sm transition-all" type="button" id="dropdownMenu' . $id . '" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <i class="fa fa-chevron-down text-[9px] opacity-60"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right rounded-2xl shadow-xl border border-slate-100 py-1.5 min-w-[150px]" role="menu" aria-labelledby="dropdownMenu' . $id . '">
                                <li style="cursor: pointer">' . $view . '</li>
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li class="divider" style="margin: 4px 0"></li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';
                return $options;
            })
            ->editColumn('title', function ($item) use ($dtMedia) {
                // Lookup từ $dtMedia đã batch-load — không query DB nữa
                $mediaRow = $dtMedia->get($item->id)?->first();
                $url = $mediaRow?->url ?? null;
                if ($url) {
                    $thumb = (!str_starts_with($url, 'http') && !str_starts_with($url, '/'))
                        ? asset('storage/' . $url)
                        : $url;
                } else {
                    $thumb = 'https://placehold.co/600x400/e2e8f0/1e293b?text=BĐS';
                }
                $street = $item->address ?? '';
                $ward = $item->ward_name ?? '';
                $province = $item->province_name ?? '';
                $title = $item->title ?? 'Chưa nhập tiêu đề';
                $str = '<div class="flex items-center gap-3 py-1">
                    <div class="w-20 h-14 rounded-xl overflow-hidden border border-slate-100 bg-slate-50 flex-shrink-0 relative group shadow-sm">
                        <a href="'.$thumb.'" data-lightbox="customer-profile" data-title="">
                            <img src="' . $thumb . '" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" onerror="this.src=\'https://placehold.co/100x100/e2e8f0/1e293b?text=QA\'">
                        </a>
                    </div>
                    <div class="min-w-0 max-w-[250px]">
                        <div class="font-semibold text-slate-900 leading-snug truncate" title="' . $title . '">
                            <a href="admin/manage_home/view/' . $item->id . '" class="hover:text-brand-600 transition-colors">' . $title . '</a>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1 truncate">
                            <i class="fa-solid fa-location-dot text-slate-400"></i>
                            ' . $street . ', ' . $ward . ', ' . $province . '
                        </p>
                    </div>
                </div>';
                return $str;
            })
            ->editColumn('type', function ($item) {
                $type = getListTypeHome($item->type);
                $propertyType = $item->property_type_name ?? 'Chưa xác định';
                $str = '<div class="flex flex-col items-center gap-1.5">
                    <span class="inline-block px-2.5 py-1 text-[11px] font-bold rounded-lg border shadow-sm" style="color:'.$type['color'].'; background-color:'.$type['background_color'].'; border-color: '.$type['background_color'].';">
                        '.$type['name'].'
                    </span>
                    <span class="text-[11px] text-slate-400 font-medium">'.$propertyType.'</span>
                </div>';
                return $str;
            })
            ->editColumn('area', function ($item) {
                return '<div class="text-center font-semibold text-slate-800">' . ($item->area ?? 0) . ' m²</div>';
            })
            ->editColumn('price', function ($item) {
                $priceStr = formatMoneyVN($item->price ?? 0);
                $suffix = $item->type == 2 ? ' / tháng' : '';
                return '<div class="text-right font-bold text-rose-600 text-[13px] whitespace-nowrap">' . $priceStr . '<span class="text-[11px] text-slate-400 font-normal">' . $suffix . '</span></div>';
            })
            ->addColumn('poster', function ($item) use ($dtCustomer) {
                // Lookup từ $dtCustomer đã batch-load trước — không gọi HTTP nữa
                $poster = $dtCustomer->firstWhere('id', $item->customer_id);
                $posterName   = $poster['fullname'] ?? $poster['name'] ?? 'Chưa xác định';
                $posterPhone  = $poster['phone'] ?? '';
                $avatar       = ($poster['avatar'] ?? null) ?: 'admin/assets/images/avatar.png';
                return '<div class="flex items-center gap-2.5">
                    <img class="w-8 h-8 rounded-full object-cover border border-slate-100 shadow-sm" onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" src="' . $avatar . '"/>
                    <div class="min-w-0">
                        <span class="block font-semibold text-slate-800 text-[13px] truncate">' . $posterName . '</span>
                        <span class="block text-slate-400 text-[11px] mt-0.5">' . $posterPhone . '</span>
                        <span class="inline-block mt-1 text-[6px] uppercase tracking-wider px-1.5 py-0.5 bg-slate-50 border border-slate-200/60 text-slate-500 rounded font-bold">' . ($item->contact_role == 1 ? 'Nhân viên Sale' : 'Admin') . '</span>
                    </div>

                </div>';
            })
            ->addColumn('customer', function ($item) {
                return '<div class="min-w-0">
                    <span class="block font-semibold text-slate-800 text-[13px] truncate flex items-center gap-1.5">
                        <i class="fa fa-user text-slate-400 text-[11px]"></i> ' . ($item->contact_name ?? 'Chưa nhập') . '
                    </span>
                    <span class="block text-slate-400 text-[11px] mt-1 flex items-center gap-1.5">
                        <i class="fa fa-phone text-slate-400 text-[11px]"></i> ' . ($item->contact_phone ?? 'Chưa nhập') . '
                    </span>
                </div>';
            })
            ->editColumn('status', function ($item) use($customer_favourite_id,$check_foryou) {
                $allStatuses = getListStatusHome(); // Lấy tất cả trạng thái từ helper
                $current     = getListStatusHome($item->status);
                $currentName  = $current['name'] ?? 'Không rõ';
                $currentClass = $current['badge_class'] ?? 'bg-slate-100 text-slate-600 border-slate-300';
                $dotColor     = $current['color'] ?? '#64748b';

                // Build dropdown items
                $dropdownItems = '';
                foreach ($allStatuses as $s) {
                    if($s['status'] == 0){
                        continue;
                    }
                    $activeClass = ($s['id'] == $item->status) ? 'bg-slate-50 font-bold text-slate-900' : 'text-slate-600';
                    $activeCheck = ($s['id'] == $item->status) ? '<i class="fa fa-check text-[10px] ml-auto text-slate-500"></i>' : '';
                    $dropdownItems .= '
                        <li>
                            <a href="#" class="home-status-item flex items-center gap-2 px-3 py-2 text-[12px] hover:bg-slate-50 ' . $activeClass . ' transition-colors"
                               data-id="' . $item->id . '" data-status="' . $s['id'] . '">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:' . $s['color'] . '"></span>
                                <span class="flex-1 text-left">' . $s['name'] . '</span>
                                ' . $activeCheck . '
                            </a>
                        </li>';
                }

                  if(!empty($customer_favourite_id) || !empty($check_foryou)){
                    return '
                        <div class="relative inline-block text-left status-dropdown-wrap">
                            <button type="button"
                                class="status-badge-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-semibold border rounded-full cursor-pointer shadow-sm hover:shadow transition-all ' . $currentClass . '"
                                data-id="' . $item->id . '" data-current="' . $item->status . '">
                                <span>' . $currentName . '</span>
                            </button>
                        </div>';
                } else {
                    return '
                        <div class="relative inline-block text-left status-dropdown-wrap">
                            <button type="button"
                                class="status-badge-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-semibold border rounded-full cursor-pointer shadow-sm hover:shadow transition-all ' . $currentClass . '"
                                data-id="' . $item->id . '" data-current="' . $item->status . '">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color: ' . $dotColor . '"></span>
                                <span>' . $currentName . '</span>
                                <i class="fa fa-chevron-down text-[8px] ml-0.5 opacity-60"></i>
                            </button>
                            <ul class="status-dropdown-menu  list-none  hidden absolute z-[9999] mt-1.5 min-w-[140px] bg-white border border-slate-200 rounded-xl shadow-xl py-1.5" style="left: 50%; transform: translateX(-50%);">
                                ' . $dropdownItems . '
                            </ul>
                        </div>';
                }
            })
            ->editColumn('is_featured', function ($item) {
                $checked = !empty($item->is_featured) ? 'checked' : '';
                $str = '<div class="flex items-center justify-center">
                    <label class="relative inline-flex items-center cursor-pointer m-0">
                        <input type="checkbox" name="is_featured" value="1" ' . $checked . ' data-id="' . $item->id . '" class="sr-only peer toggle-featured-checkbox">
                        <div class="relative w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-brand-500"></div>
                    </label>
                </div>';
                return $str;
            })
            ->editColumn('is_new', function ($item) {
                $checked = !empty($item->is_new) ? 'checked' : '';
                $str = '<div class="flex items-center justify-center">
                    <label class="relative inline-flex items-center cursor-pointer m-0">
                        <input type="checkbox" name="is_new" value="1" ' . $checked . ' data-id="' . $item->id . '" class="sr-only peer dt-active" data-href="admin/manage_home/changeNew/'.$item->id.'" data-status="'.$item->is_new.'">
                        <div class="relative w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-brand-500"></div>
                    </label>
                </div>';
                return $str;
            })
            ->editColumn('is_vip', function ($item) {
                $checked = !empty($item->is_vip) ? 'checked' : '';
                $str = '<div class="flex items-center justify-center">
                    <label class="relative inline-flex items-center cursor-pointer m-0">
                        <input type="checkbox" name="is_vip" value="1" ' . $checked . ' data-id="' . $item->id . '" class="sr-only peer dt-active" data-href="admin/manage_home/changeVip/'.$item->id.'" data-status="'.$item->is_vip.'">
                        <div class="relative w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-brand-500"></div>
                    </label>
                </div>';
                return $str;
            })
            ->editColumn('created_at', function ($item) {
                return $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '';
            })
            ->editColumn('code', function ($item) {
                $code = $item->code ?? ('BĐS-' . $item->id);
                return '<div class="text-center font-bold text-slate-700 hover:text-brand-600 transition-colors"><a href="admin/manage_home/view/' . $item->id . '">' . $code . '</a></div>';
            })
            ->rawColumns(['options', 'title', 'area', 'price', 'customer', 'poster', 'status', 'is_featured', 'is_new','is_vip', 'created_at', 'code', 'type'])
            ->make(true);
    }

    public function detail($id = 0) {
        if(empty($id)){
            if (!has_permission('manage_homne', 'add')) {
                access_denied();
            }
        } else {
            if (!has_permission('manage_homne', 'edit')) {
                access_denied();
            }
        }
        if ($this->request->isMethod('post')) {
            return $this->submit($id);
        }

        $title = lang('Chi tiết bất động sản');
        $home = null;
        $selected_district_id = 0;
        $districts = [];
        $wards = [];
        $initial_province_name = '';
        $initial_district_name = '';
        $initial_ward_name = '';
        $selected_client = null;

        if ($id > 0) {
            $home = Home::with(['interior_amenities', 'media_items', 'utilities', 'documents_red', 'documents_other'])->find($id);
            if ($home) {
                if ($home->customer_id) {
                    try {
                        $accountService = app(\App\Services\AccountService::class);
                        $clientRequest = new \Illuminate\Http\Request();
                        $clientRequest->merge(['id' => $home->customer_id]);
                        $response = $accountService->getDetailCustomer($clientRequest);
                        $clientData = $response->getData(true);
                        $selected_client = $clientData['client'] ?? null;
                    } catch (\Exception $e) {
                        // Log or ignore client fetch failure
                    }
                }
                
                if ($home->ward_id) {
                    if ($home->is_new_address) {
                        // New Address system
                        $ward = DB::table('tbl_wards_new')->where('id', $home->ward_id)->first();
                        if ($ward) {
                            $initial_ward_name = $ward->name;
                            $prov = DB::table('tbl_provinces')->where('id', $home->province_id)->first();
                            $initial_province_name = $prov ? $prov->name : '';
                            $districts = [];
                            $wards = DB::table('tbl_wards_new')->where('province_id', $home->province_id)->get();
                        }
                    } else {
                        // Old Address system
                        $ward = DB::table('tblward')->where('wardid', $home->ward_id)->first();
                        if ($ward) {
                            $initial_ward_name = $ward->name;
                            $selected_district_id = $ward->districtid;
                            $dist = DB::table('tbldistrict')->where('districtid', $selected_district_id)->first();
                            $initial_district_name = $dist ? $dist->name : '';
                            $prov = DB::table('tblprovince')->where('provinceid', $home->province_id)->first();
                            $initial_province_name = $prov ? $prov->name : '';
                            $districts = DB::table('tbldistrict')->where('provinceid', $home->province_id)->get();
                            $wards = DB::table('tblward')->where('districtid', $selected_district_id)->get();
                        }
                    }
                }
            }
        }

        $provinces_old = DB::table('tblprovince')->get();
        $provinces_new = DB::table('tbl_provinces')->orderByRaw('order_by desc')->get();
        $property_types = TypeProperty::with('utilities.options')->where('active',1)->get();
        $orientations = HouseOrientation::where('active',1)->get();
        $legal_documents = LegalDocument::where('active',1)->get();
        $interior_handovers = InteriorHandover::where('active',1)->get();
        $interior_amenities = InteriorAmenity::where('active',1)->get();
        $typeHome = getListTypeHome();

        $user = Auth::guard('admin')->user();
        $apiToken = '';
        if ($user) {
            $privateKey = file_get_contents(storage_path('keys/private.pem'));
            $payload = [
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'guard' => 'admin',
                'date' => date('Y-m-d H:i:s'),
            ];
            $apiToken = JWT::encode($payload, $privateKey, 'RS256');
        }

        return view('admin.manage_home.detail', [
            'title' => $title,
            'id' => $id,
            'home' => $home,
            'selected_district_id' => $selected_district_id,
            'districts' => $districts,
            'wards' => $wards,
            'provinces_old' => $provinces_old,
            'provinces_new' => $provinces_new,
            'initial_province_name' => $initial_province_name,
            'initial_district_name' => $initial_district_name,
            'initial_ward_name' => $initial_ward_name,
            'property_types' => $property_types,
            'orientations' => $orientations,
            'legal_documents' => $legal_documents,
            'interior_handovers' => $interior_handovers,
            'interior_amenities' => $interior_amenities,
            'typeHome' => $typeHome,
            'selected_client' => $selected_client,
            'apiToken' => $apiToken,
            'google_api_key' => get_option('google_api_key'),
        ]);
    }

    public function submit($id = 0) {
        $is_new = $this->request->input('is_new_address', 0);
        $rules = [
            'title' => 'required',
            'type' => 'required',
            'property_type' => 'required',
            'province_id' => 'required',
            'ward_id' => 'required',
            'address' => 'required',
            'price' => 'required|numeric',
            'area' => 'required|numeric',
            'contact_name' => 'required',
            'contact_phone' => 'required',
            'commission_rate' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'detail' => 'required',
        ];
        if (!$is_new) {
            $rules['district_id'] = 'required';
        }

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => implode('<br>', $validator->errors()->all())
            ]);
        }

        if ($id > 0) {
            $home = Home::find($id);
            if (!$home) {
                return response()->json([
                    'result' => false,
                    'message' => 'Không tìm thấy bất động sản'
                ]);
            }
        } else {
            $home = new Home();
        }
        $type = $this->request->input('type');
        $home->type = $type;
        if($type == 1){
            if(empty(number_unformat($this->request->input('price_m2'),0))){
                return response()->json([
                    'result' => false,
                    'message' => 'Vui lòng nhập giá trên m2'
                ]);
            }
            if(empty($this->request->input('plot_land'))){
                return response()->json([
                    'result' => false,
                    'message' => 'Vui lòng nhập thửa đất số'
                ]);
            }
            if(empty($this->request->input('number_sheets'))){
                return response()->json([
                    'result' => false,
                    'message' => 'Vui lòng nhập tờ bản đồ'
                ]);
            }

            $province_id = $this->request->input('province_id');
            $ward_id = $this->request->input('ward_id');
            $plot_land = $this->request->input('plot_land');
            $number_sheets = $this->request->input('number_sheets');

            $queryDuplicate = Home::where('type', 1)
                ->where('province_id', $province_id)
                ->where('ward_id', $ward_id)
                ->where('plot_land', $plot_land)
                ->where('number_sheets', $number_sheets);

            if ($id > 0) {
                $queryDuplicate->where('id', '!=', $id);
            }

            $exist = $queryDuplicate->first();
            if ($exist) {
                return response()->json([
                    'result' => false,
                    'message' => 'Bất động sản này đã tồn tại trên hệ thống (Trùng Tỉnh/Thành, Phường/Xã, Thửa đất, Tờ bản đồ)'
                ]);
            }
        }
        $price_m2 = number_unformat($this->request->input('price_m2'),0);
        $home->price_m2 = $price_m2;
        $home->property_type = $this->request->input('property_type');
        if ($home->type == 1) { // Mua bán
            $home->legal_id = $this->request->input('legal_id');
            $home->move_in_time = null;
            $home->electricity_price = null;
            $home->water_price = null;
            $home->internet_price = null;
        } else { // Cho thuê
            $home->legal_id = null;
            $home->move_in_time = $this->request->input('move_in_time');
            $home->electricity_price = $this->request->input('electricity_price');
            $home->water_price = $this->request->input('water_price');
            $home->internet_price = $this->request->input('internet_price');
        }
        $home->status = $this->request->input('status', 1);
        $home->is_featured = $this->request->input('is_featured', 0);
        $home->province_id = $this->request->input('province_id');
        $home->ward_id = $this->request->input('ward_id');
        $home->is_new_address = $is_new;
        $home->address = $this->request->input('address');
        if ($this->request->has('latitude')) {
            $home->latitude = $this->request->input('latitude');
        }
        if ($this->request->has('longitude')) {
            $home->longitude = $this->request->input('longitude');
        }
        if ($this->request->has('name_location')) {
            $home->name_location = $this->request->input('name_location');
        }
        $home->price = $this->request->input('price');
        $home->area = $this->request->input('area');
        $home->beds = $this->request->input('beds', 1);
        $home->baths = $this->request->input('baths', 1);
        $utilitiesData = $this->request->input('utilities', []);
        $floorsUtility = Utility::where('name', 'Số tầng')->first();
        $entranceUtility = Utility::where('name', 'Đường vào (m)')->first();
        $facadeUtility = Utility::where('name', 'Mặt tiền (m)')->first();

        $home->floors = ($floorsUtility && isset($utilitiesData[$floorsUtility->id])) ? $utilitiesData[$floorsUtility->id] : null;
        $home->entrance = ($entranceUtility && isset($utilitiesData[$entranceUtility->id])) ? $utilitiesData[$entranceUtility->id] : null;
        $home->facade = ($facadeUtility && isset($utilitiesData[$facadeUtility->id])) ? $utilitiesData[$facadeUtility->id] : null;
        $home->direction_id = $this->request->input('direction_id');
        $home->interior_id = $this->request->input('interior_id');
        $home->interior_note = $this->request->input('interior_note');
        $home->title = $this->request->input('title');
        $home->detail = $this->request->input('detail');
        $home->description = $this->request->input('description');
        $home->contact_name = $this->request->input('contact_name');
        $home->contact_phone = $this->request->input('contact_phone');
        $home->contact_role = $this->request->input('contact_role', 'Chính chủ');
        $home->contact_time = $this->request->input('contact_time');
        $home->customer_id = $this->request->input('customer_id');
        $home->email_phone = $this->request->input('email_phone');
        $home->commission_rate = $this->request->input('commission_rate');
        $home->start_date = $this->request->input('start_date');
        $home->end_date = $this->request->input('end_date');

        // Process media files synchronously
        $existingMedia = $this->request->input('existing_media', []);
        $existingCaptions = $this->request->input('existing_media_captions', []);
        
        $mediaRecords = [];
        $sortOrder = 0;
        
        // 1. Process existing media
        if (is_array($existingMedia)) {
            foreach ($existingMedia as $idx => $url) {
                $cleanUrl = str_replace(asset('storage') . '/', '', $url);
                $mediaRecords[] = [
                    'url' => $cleanUrl,
                    'caption' => isset($existingCaptions[$idx]) ? $existingCaptions[$idx] : '',
                    'sort_order' => $sortOrder++,
                ];
            }
        }
        
        // 2. Process new media uploads
        if ($this->request->hasFile('new_media')) {
            $newMediaFiles = $this->request->file('new_media');
            $newCaptions = $this->request->input('new_media_captions', []);
            
            if (is_array($newMediaFiles)) {
                foreach ($newMediaFiles as $idx => $file) {
                    $uploadedPath = $this->UploadFile($file, 'homes', 800, 600, false);
                    if ($uploadedPath) {
                        $mediaRecords[] = [
                            'url' => $uploadedPath,
                            'caption' => isset($newCaptions[$idx]) ? $newCaptions[$idx] : '',
                            'sort_order' => $sortOrder++,
                        ];
                    }
                }
            } else {
                $uploadedPath = $this->UploadFile($newMediaFiles, 'homes', 800, 600, false);
                if ($uploadedPath) {
                    $mediaRecords[] = [
                        'url' => $uploadedPath,
                        'caption' => isset($newCaptions[0]) ? $newCaptions[0] : '',
                        'sort_order' => $sortOrder++,
                    ];
                }
            }
        }
        
        // Backend validation: Require at least 3 photos
        if (count($mediaRecords) < 3) {
            return response()->json([
                'result' => false,
                'message' => 'Yêu cầu tối thiểu tải lên 3 hình ảnh!'
            ]);
        }
        
        // 3. Process video
        if ($this->request->hasFile('video_file')) {
            $uploadedVideoPath = $this->UploadFile($this->request->file('video_file'), 'homes', 0, 0, false);
            if ($uploadedVideoPath) {
                $home->video_url = $uploadedVideoPath;
            }
        } else {
            $videoUrlInput = $this->request->input('video_url', '');
            $cleanVideoUrl = str_replace(asset('storage') . '/', '', $videoUrlInput);
            $home->video_url = $cleanVideoUrl;
        }

        DB::beginTransaction();
        try {
            $home->save();

            if (empty($home->code)) {
                $home->code = 'BĐS-' . $home->id;
                $home->save();
            }

            // Save media to separate table and serialize to tbl_home columns
            $home->media_items()->delete();
            $mediaUrlsArray = [];
            $mediaCaptionsArray = [];
            foreach ($mediaRecords as $record) {
                $home->media_items()->create($record);
                $mediaUrlsArray[] = $record['url'];
                $mediaCaptionsArray[] = $record['caption'];
            }
            $home->media = json_encode($mediaUrlsArray);
            $home->media_captions = json_encode($mediaCaptionsArray);
            $home->save();

            $amenities = $this->request->input('interior_amenities', []);
            $home->interior_amenities()->sync($amenities);

            // Sync dynamic utilities
            $syncUtilities = [];
            foreach ($utilitiesData as $utilityId => $value) {
                if ($value !== null && $value !== '') {
                    $syncUtilities[$utilityId] = ['value' => $value];
                }
            }
            $home->utilities()->sync($syncUtilities);

            DB::commit();
            return response()->json([
                'result' => true,
                'message' => 'Lưu thông tin bất động sản thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id) {
        if (!has_permission('manage_homne', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $home = Home::find($id);
        if (!$home) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bất động sản'
            ]);
        }

        DB::beginTransaction();
        try {
            $home->interior_amenities()->detach();
            $home->utilities()->detach();
            $home->media_items()->delete();
            $home->documents_red()->delete();
            $home->documents_other()->delete();
            $home->delete();
            DB::commit();

            // Delete the physical upload directory and all files inside it
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('homes/' . $home->id);

            return response()->json([
                'result' => true,
                'message' => 'Xóa bất động sản thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'message' => 'Có lỗi xảy ra khi xóa: ' . $e->getMessage()
            ]);
        }
    }

    public function getDistricts()
    {
        $province_id = $this->request->input('province_id');
        $districts = DB::table('tbldistrict')->where('provinceid', $province_id)->get();
        return response()->json($districts);
    }

    public function getWards()
    {
        $is_new = $this->request->input('is_new', 0);
        if ($is_new) {
            $province_id = $this->request->input('province_id');
            $wards = DB::table('tbl_wards_new')->where('province_id', $province_id)->get();
        } else {
            $district_id = $this->request->input('district_id');
            $wards = DB::table('tblward')->where('districtid', $district_id)->get();
        }
        return response()->json($wards);
    }

    public function changeFeatured($id)
    {
        $home = Home::find($id);
        if (!$home) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bất động sản'
            ]);
        }
        try {
            $home->is_featured = $this->request->input('is_featured', 0);
            if($home->is_featured == 1){
                $home->is_vip = 0;
                $home->is_new = 0;
            }
            $home->save();
            return response()->json([
                'result' => true,
                'message' => 'Cập nhật trạng thái tin nổi bật thành công'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function changeStatus() {
        $id     = $this->request->input('id');
        $status = (int) $this->request->input('status');

        // Kiểm tra status hợp lệ
        $validStatuses = array_column(getListStatusHome(), 'id');
        if (!in_array($status, $validStatuses)) {
            return response()->json(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
        }
        $home = DB::table('tbl_home')->where('id', $id)->first();
        if (!$home) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bất động sản']);
        }
        if($home->status == $status){
            return response()->json(['success' => false, 'message' => 'Bất động sản đã ở trạng thái này']);
        }
        DB::beginTransaction();
        try {
            $success = DB::table('tbl_home')->where('id', $id)->update([
                'status'     => $status,
                'updated_at' => now(),
            ]);
            if(!$success){
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Cập nhật thất bại']);
            } else {
                $statusInfo = getListStatusHome($status);
                DB::commit();
                return response()->json([
                    'success'     => true,
                    'message'     => 'Cập nhật trạng thái thành công',
                    'status'      => $status,
                    'status_name' => $statusInfo['name'],
                    'badge_class' => $statusInfo['badge_class'],
                    'color'       => $statusInfo['color'],
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()] );
        }

    }

    public function countAll() {
        $customer_search = $this->request->input('customer_search');
        $property_type_search = $this->request->input('property_type_search');

        $query = Home::query();
        if ($customer_search) {
            $query->where('customer_id', $customer_search);
        }
        if ($property_type_search) {
            $query->where('property_type', $property_type_search);
        }

        $allQuery = clone $query;
        $allCount = $allQuery->count();

        // status counts
        $statusCounts = $query->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $arrType = [];
        foreach (getListStatusHome() as $status) {
            //tin hết hạn 
            if($status['id'] == 6){
                $matched = DB::table('tbl_home')->where('end_date','<',date('Y-m-d'))->count();
                $total = $matched;
            } else {
                $matched = $statusCounts->firstWhere('status', $status['id']);
                $total = $matched ? $matched->total : 0;
            }
            $arrType[] = [
                'id' => $status['id'],
                'total' => $total
            ];
        }

        return response()->json([
            'success' => true,
            'all' => $allCount,
            'arrType' => $arrType
        ]);
    }

       public function generate_ai() {
        $tone = $this->request->input('ai_tone', 'polite');
        $type = $this->request->input('type'); // 1: Mua bán, 2: Cho thuê
        $propertyTypeId = $this->request->input('property_type');
        $price = $this->request->input('price');
        $area = $this->request->input('area');
        $provinceId = $this->request->input('province_id');
        $districtId = $this->request->input('district_id');
        $wardId = $this->request->input('ward_id');
        $address = $this->request->input('address');
        $priceM2 = $this->request->input('price_m2');
        $loanability = $this->request->input('loanability');
        $amenityIds = $this->request->input('interior_amenities', []);

        // Resolve property type name
        $propertyTypeName = 'Bất động sản';
        if ($propertyTypeId) {
            $pt = DB::table('tbl_type_property')->where('id', $propertyTypeId)->first();
            if ($pt) {
                $propertyTypeName = $pt->name;
            }
        }

        // Resolve location / address
        $addressParts = [];
        if ($address) {
            $addressParts[] = $address;
        }
        if ($wardId) {
            $ward = DB::table('tbl_wards_new')->where('id', $wardId)->first();
            if ($ward) {
                $addressParts[] = $ward->name;
            }
        }
        if ($districtId) {
            $district = DB::table('tbldistrict')->where('districtid', $districtId)->first();
            if ($district) {
                $addressParts[] = $district->name;
            }
        }
        if ($provinceId) {
            $province = DB::table('tbl_provinces')->where('id', $provinceId)->first();
            if ($province) {
                $addressParts[] = $province->name;
            }
        }
        $locationStr = !empty($addressParts) ? implode(', ', $addressParts) : '';

        // Format Price
        $priceFormatted = 'Thỏa thuận';
        if ($price) {
            $priceFormatted = number_format($price) . ' VNĐ';
            if ($type == 2) {
                $priceFormatted .= ' / tháng';
            }
        }

        // Format Area
        $areaStr = $area ? $area . ' m²' : '';

        // Format Price/m2
        $priceM2Str = '';
        if ($priceM2) {
            $rawPriceM2 = str_replace(',', '', $priceM2);
            if (is_numeric($rawPriceM2)) {
                $priceM2Str = number_format((float)$rawPriceM2) . ' VNĐ/m²';
            } else {
                $priceM2Str = $priceM2 . ' VNĐ/m²';
            }
        }

        // Format Loanability
        $loanabilityStr = '';
        if ($loanability) {
            $rawLoan = str_replace(',', '', $loanability);
            if (is_numeric($rawLoan)) {
                $loanabilityStr = number_format((float)$rawLoan) . ' VNĐ';
            } else {
                $loanabilityStr = $loanability;
            }
        }

        // Resolve amenities
        $amenityNames = [];
        if (!empty($amenityIds) && is_array($amenityIds)) {
            $amenities = DB::table('tbl_interior_amenities')->whereIn('id', $amenityIds)->get();
            foreach ($amenities as $amenity) {
                $amenityNames[] = $amenity->name;
            }
        }
        $amenitiesStr = !empty($amenityNames) ? implode(', ', $amenityNames) : '';

        // Resolve utilities (dynamic fields like bedrooms, floors, orientation, etc.)
        $utilitiesInput = $this->request->input('utilities', []);
        $resolvedUtilities = [];
        if (!empty($utilitiesInput) && is_array($utilitiesInput)) {
            $utilityIds = array_keys($utilitiesInput);
            $utilities = DB::table('tbl_utilities')->whereIn('id', $utilityIds)->get();
            
            $optionIds = [];
            foreach ($utilitiesInput as $uId => $val) {
                if ($val !== null && $val !== '' && is_numeric($val)) {
                    $optionIds[] = (int)$val;
                }
            }
            
            $resolvedOptions = [];
            if (!empty($optionIds)) {
                $resolvedOptions = DB::table('tbl_utility_options')
                    ->whereIn('id', $optionIds)
                    ->pluck('name', 'id')
                    ->toArray();
            }
            
            foreach ($utilities as $utility) {
                $val = $utilitiesInput[$utility->id] ?? null;
                if ($val === null || $val === '') {
                    continue;
                }
                
                if (isset($resolvedOptions[$val])) {
                    $displayVal = $resolvedOptions[$val];
                } else {
                    $displayVal = $val;
                    if (!empty($utility->unit)) {
                        $displayVal .= ' ' . $utility->unit;
                    }
                }
                
                $resolvedUtilities[] = $utility->name . ": " . $displayVal;
            }
        }
        $utilitiesStr = implode(', ', $resolvedUtilities);
        $title = '';
        $detail = '';
        $description = '';

        // Call GPT API to generate high quality description
        $transactionType = ($type == 2) ? 'Cho thuê' : 'Mua bán';
        $prompt = "Hãy viết tiêu đề tin đăng, nội dung video ngắn (detail) và mô tả chi tiết (description) cho một bất động sản với thông tin sau:\n";
        $prompt .= "- Loại hình giao dịch: " . $transactionType . "\n";
        $prompt .= "- Loại bất động sản: " . $propertyTypeName . "\n";
        $prompt .= "- Vị trí / Địa chỉ: " . ($locationStr ?: 'Đang cập nhật') . "\n";
        $prompt .= "- Diện tích: " . ($areaStr ?: 'Đang cập nhật') . "\n";
        $prompt .= "- Giá: " . $priceFormatted . "\n";
        if ($priceM2Str) {
            $prompt .= "- Đơn giá theo m²: " . $priceM2Str . "\n";
        }
        if ($loanabilityStr) {
            $prompt .= "- Hỗ trợ vay tối đa: " . $loanabilityStr . "\n";
        }
        if (!empty($utilitiesStr)) {
            $prompt .= "- Đặc điểm chi tiết: " . $utilitiesStr . "\n";
        }
        if (!empty($amenitiesStr)) {
            $prompt .= "- Tiện ích xung quanh: " . $amenitiesStr . "\n";
        }
        $prompt .= "- Tông giọng yêu cầu: " . ($tone === 'polite' ? 'Lịch sự, trang trọng' : 'Trẻ trung, năng động') . "\n\n";
        $prompt .= "Yêu cầu đặc biệt về vị trí/địa chỉ: Không ghi tên đường cụ thể hay số nhà trong Tiêu đề (title) và Mô tả chi tiết (description), chỉ ghi thông tin cấp Phường/Xã, Quận/Huyện và Tỉnh/Thành phố.\n\n";
        $prompt .= "Yêu cầu đặc biệt cho phần Mô tả chi tiết (description): Hãy trình bày thoáng đãng, chia thành các đoạn rõ ràng và sử dụng các biểu tượng cảm xúc (emojis / icons) đẹp mắt, chuyên nghiệp (như 📍, 📐, 💰, 🏠, ✨, 📞,...) ở đầu dòng để làm nổi bật các ý chính, giúp bài viết hấp dẫn và người đọc dễ theo dõi. Tuyệt đối không sử dụng các ký tự định dạng markdown như dấu sao đôi (**) để bôi đậm, tất cả phải là chữ thường/chữ hoa bình thường và các biểu tượng cảm xúc.\n\n";
        $prompt .= "Yêu cầu đặc biệt cho phần Nội dung ngắn video (detail): Tối đa 100 ký tự, ngắn gọn, súc tích và hấp dẫn.\n\n";
        $prompt .= "Yêu cầu định dạng kết quả trả về là một chuỗi JSON hợp lệ duy nhất có cấu trúc như sau (không kèm ký tự markdown như ```json hay bất cứ giải thích nào khác):\n";
        $prompt .= "{\n  \"title\": \"Tiêu đề tin đăng\",\n  \"detail\": \"Nội dung ngắn video (tối đa 100 ký tự)\",\n  \"description\": \"Mô tả chi tiết\"\n}";
        try {
            $gptResponse = \App\Helpers\AigptHeplers::getChatbotResponse($prompt);
            
            // Clean markdown block wrappers if GPT returned them
            $cleanJsonStr = trim($gptResponse);
            if (strpos($cleanJsonStr, '```json') === 0) {
                $cleanJsonStr = substr($cleanJsonStr, 7);
            } elseif (strpos($cleanJsonStr, '```') === 0) {
                $cleanJsonStr = substr($cleanJsonStr, 3);
            }
            if (substr($cleanJsonStr, -3) === '```') {
                $cleanJsonStr = substr($cleanJsonStr, 0, -3);
            }
            $cleanJsonStr = trim($cleanJsonStr);
            
            $data = json_decode($cleanJsonStr, true);
            if ($data && !empty($data['title']) && !empty($data['detail']) && !empty($data['description'])) {
                $title = $data['title'];
                $detail = $data['detail'];
                $description = $data['description'];
            }
        } catch (\Exception $e) {
            // Fallback automatically used
        }

        return response()->json([
            'result' => true,
            'title' => $title,
            'detail' => $detail,
            'description' => $description,
            'message' => 'Tạo nội dung mô tả bằng AI thành công!'
        ]);
    }

    public function delete_review($id)
    {
        if (!has_permission('review_home', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        return app(\App\Http\Controllers\Api_app\HomeController::class)->delete_review($id);
    }

    public function edit_review($id)
    {
        if (!has_permission('review_home', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        return app(\App\Http\Controllers\Api_app\HomeController::class)->edit_review($id);
    }

    public function getSearchSuggestions(Request $request)
    {
        $query = $request->input('q', '');
        $tab = $request->input('tab', '');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $dbQuery = DB::table('tbl_search_suggestions')
            ->where('suggestion', 'LIKE', '%' . $query . '%');

        if (!empty($tab) && $tab !== 'Dự án') {
            $dbQuery->where('type', $tab);
        }

        $results = $dbQuery->orderBy('score', 'desc')
            ->limit(10)
            ->get();

        $formattedData = $results->map(function ($item) use ($query) {
            // Escape special regex chars and match case-insensitively
            $highlightRegex = '/' . preg_quote($query, '/') . '/i';
            $highlightText = preg_replace($highlightRegex, '<b>$0</b>', $item->suggestion);

            $data = (array) $item;
            $data['highlightText'] = $highlightText;
            return $data;
        });

        return response()->json([
            'data' => $formattedData
        ]);
    }

    public function changeVip($id)
    {
        if (!has_permission('manage_homne', 'edit')) {
            access_denied();
        }
        $home = Home::find($id);
        if (!$home) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bất động sản'
            ]);
        }
        try {
            $home->is_vip = $this->request->status == 0 ? 1 : 0;
            if($home->is_vip == 1){
                $home->is_new = 0;
                $home->is_featured = 0;
            }
            $home->save();
            return response()->json([
                'result' => true,
                'message' => 'Cập nhật trạng thái tin VIP thành công'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function changeNew($id)
    {
        if (!has_permission('manage_homne', 'edit')) {
            access_denied();
        }
        $home = Home::find($id);
        if (!$home) {
            return response()->json([
                'result' => false,
                'message' => 'Không tìm thấy bất động sản'
            ]);
        }
        try {
            $home->is_new = $this->request->status == 0 ? 1 : 0;
            if($home->is_new == 1){
                $home->is_vip = 0;
                $home->is_featured = 0;
            }
            $home->save();
            return response()->json([
                'result' => true,
                'message' => 'Cập nhật trạng thái tin Mới thành công'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

}

