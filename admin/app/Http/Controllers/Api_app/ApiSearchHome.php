<?php

namespace App\Http\Controllers\Api_app;

use app\Services\ServiceService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\FilesHelpers;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Terms;
use App\Models\TermsTranslations;
use App\Models\IconApp;
use App\Models\TransferAddress;
use App\Models\TransferAddressRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Google_Client;
use function Laravel\Prompts\table;
use DateTime;
use App\Libraries\App;
use App\Models\Home;
use App\Models\HistorySearchHome;
use App\Http\Resources\HomeResources;
use App\Services\AccountService;

class ApiSearchHome extends AuthController
{
    protected $dbService;
    public function __construct(Request $request, ServiceService $serviceService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->SaveSession = true;
        app(\App\Http\Middleware\CheckLoginApi::class)->getDataToken($this->request);
        $this->baseUrlAdmin = config('services.storage.url');
        $this->baseUrl = config('services.storage.url');
        $this->dbService = $serviceService;
    }
    function GetListSearch() {
        $dbQuery = DB::table('tbl_search_suggestions');
        $data = $dbQuery->select('suggestion', 'id')->orderBy('score', 'desc')->limit(5)->get();
        
        return response()->json([
            'result' => true,
            'data' => $data
        ], 200);
    }
    function GetListHistorySearch() {
        $id_client = $this->request->client->id ?? 0;
        if (empty($id_client)) {
            return response()->json([
                'result' => true,
                'data' => []
            ], 200);
        }

        $data = HistorySearchHome::where('id_client', $id_client)
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        return response()->json([
            'result' => true,
            'data' => $data
        ], 200);
    }
    function GetListSearchHome() {
        $query = $this->request->input('q', '');
        if (empty($query)) {
            return response()->json(['data' => [], 'highlight' => []]);
        }

        $words = is_array($query) ? $query : explode(' ', $query);
        $highlight = [];
        foreach ($words as $word) {
            $word = trim($word);
            if ($word !== '') {
                $highlight[] = $word;
            }
        }

        $dbQuery = DB::table('tbl_search_suggestions')
            ->where('suggestion', 'LIKE', '%' . $query . '%');

        $results = $dbQuery->orderBy('score', 'desc')
            ->limit(10)
            ->get();

        $formattedData = $results->map(function ($item) use ($highlight) {
            $highlightText = $item->suggestion;
            
            foreach ($highlight as $word) {
                // Escape special regex chars and match case-insensitively
                $highlightRegex = '/' . preg_quote($word, '/') . '/i';
                $highlightText = preg_replace($highlightRegex, '<b>$0</b>', $highlightText);
            }

            $data = (array) $item;
            $data['highlightText'] = $highlightText;
            $data['highlight'] = $highlight;
            return $data;
        });

        return response()->json([
            'data' => $formattedData,
            'highlight' => $highlight
        ]);
    }

    public function getListHome() {
        
        $current_page = 1;
        $per_page = 10;
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }

        $lat = !empty($this->request->input('lat')) ? $this->request->input('lat') : 0;
        $lon = !empty($this->request->input('lon')) ? $this->request->input('lon') : 0;

        $query = Home::select('tbl_home.*', DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))->with([
            'propertyType',
            'province',
            'ward',
            'direction',
            'legal',
            'interior',
            'media_items',
            'interior_amenities',
            'utilities',
            'utilities.options',
            'favourite'
        ])->where('id', '!=', 0);

        // Always filter by status 2 (active)
        $query->where('status', 2);

        $id_suggestions = $this->request->input('id_suggestions');
        $search = $this->request->input('search');
        if (empty($search)) {
            $search = $this->request->input('q');
        }

        if (empty($id_suggestions) && !empty($search)) {
            $matchedSuggestion = DB::table('tbl_search_suggestions')->where('suggestion', trim($search))->first();
            if ($matchedSuggestion) {
                $id_suggestions = $matchedSuggestion->id;
            }
        }

        if (!empty($id_suggestions)) {
            $suggestion = DB::table('tbl_search_suggestions')->where('id', $id_suggestions)->first();
            if ($suggestion) {
                // ward_id & is_new_address filter
                if (!empty($suggestion->ward_id)) {
                    $query->where('tbl_home.ward_id', $suggestion->ward_id)
                          ->where('tbl_home.is_new_address', $suggestion->is_new_address);
                }

                // type filter: 'Mua bán' => 1, 'Cho thuê' => 2
                if (!empty($suggestion->type)) {
                    $typeVal = ($suggestion->type === 'Mua bán') ? 1 : 2;
                    $query->where('tbl_home.type', $typeVal);
                }

                // to_price filter: price < to_price * 1000000
                if (!empty($suggestion->to_price)) {
                    $query->where('tbl_home.price', '<', (int)$suggestion->to_price * 1000000);
                }

                // street filter: extract Street Name
                $suggestion_street = '';
                if (preg_match('/Đường\s+([^,]+)/', $suggestion->suggestion, $matches)) {
                    $suggestion_street = trim($matches[1]);
                }
                if (!empty($suggestion_street)) {
                    $query->where('tbl_home.address', 'LIKE', '%' . $suggestion_street . '%');
                }

                // Property type filter
                if (!empty($suggestion->suggestion)) {
                    $propertyTypes = DB::table('tbl_type_property')->get();
                    foreach ($propertyTypes as $pt) {
                        $ptName = ($pt->name === 'Đất bán') ? 'Đất' : $pt->name;
                        if (stripos($suggestion->suggestion, $ptName) !== false) {
                            $query->where('tbl_home.property_type', $pt->id);
                            break;
                        }
                    }
                }
            }
        } else {
            // Search query fallback
            if (!empty($search)) {
                $query->where(function($subQ) use ($search) {
                    $subQ->where('tbl_home.title', 'like', "%{$search}%")
                         ->orWhere('tbl_home.code', 'like', "%{$search}%")
                         ->orWhere('tbl_home.address', 'like', "%{$search}%");
                });
            }
        }

        // Save search history if client is logged in
        $id_client = $this->request->client->id ?? 0;
        if (!empty($id_client)) {
            $history_term = '';
            if (!empty($id_suggestions) && $suggestion && !empty($suggestion->suggestion)) {
                $history_term = trim($suggestion->suggestion);
            } else {
                $search = $this->request->input('search');
                if (empty($search)) {
                    $search = $this->request->input('q');
                }
                if (!empty($search)) {
                    $history_term = trim($search);
                }
            }

            if (!empty($history_term)) {
                $histQuery = HistorySearchHome::where('id_client', $id_client);
                if (!empty($id_suggestions)) {
                    $histQuery->where('id_suggestions', $id_suggestions);
                } else {
                    $histQuery->where('search', $history_term);
                }
                $histQuery->delete();

                HistorySearchHome::insert([
                    'id_client' => $id_client,
                    'search' => $history_term,
                    'id_suggestions' => !empty($id_suggestions) ? $id_suggestions : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (!empty($lat) && !empty($lon)) {
            $query->orderBy('distance')->orderByDesc('id');
        } else {
            $query->orderByDesc('id');
        }

        $dtData = $query->paginate($per_page, ['*'], '', $current_page);

        $allCustomerIds = $dtData->pluck('customer_id')->unique()->values()->toArray();
        $dtCustomer = collect();
        if (!empty($allCustomerIds)) {
            $accountService = app(AccountService::class);
            $this->requestCustomer = clone $this->request;
            $this->requestCustomer->merge(['customer_id' => $allCustomerIds]);
            $responseCustomer = $accountService->getListData($this->requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $dtCustomer = collect($dataCustomer['data'] ?? []);
        }

        $dtData->getCollection()->transform(function ($item) use ($dtCustomer) {
            $customer = $dtCustomer->where('id', $item->customer_id)->first();
            $item->customer = $customer;
            return $item;
        });

        $collection = HomeResources::collection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}