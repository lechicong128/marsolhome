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

class AccumulationController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
    }

    public function get_list(){
        if (!has_permission('accumulation','view')){
            access_denied();
        }

        $discount_total_orders = DB::table('tbl_discount_total_orders')->orderBy('total_order_start', 'ASC')->get();
        $leaders = DB::table('tbl_accumulation_leaders')->get();
        $reached = DB::table('tbl_accumulation_leaders_reached')->get();
        $data_reward = DB::table('tbl_accumulation_leaders_reward')->get();
        $reward = [];
        foreach($data_reward as $key => $item) {
            $reward[$item->id_leaders][$item->id_reached] = $item->money_reward;
        }
        $interest = DB::table('tbl_accumulation_interest')->get();
        $passive = DB::table('tbl_accumulation_passive')->get();

        return view('admin.accumulation.list',[
            'title' => lang('accumulation'),
            'discount_total_orders' => $discount_total_orders,
            'leaders' => $leaders,
            'reached' => $reached,
            'reward' => $reward,
            'interest' => $interest,
            'passive' => $passive,
            'accumulation_difference' => get_option('accumulation_difference') ? json_decode(get_option('accumulation_difference'), true) : []
        ]);
    }
    public function submit() {
        if (!has_permission('accumulation', 'add')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $data = [];
        $discount_total_orders = $this->request->input('discount_total_orders');
        $leaders = $this->request->input('leaders');
        $reached = $this->request->input('reached');
        $interest = $this->request->input('interest');
        $passive = $this->request->input('passive');
        $difference = $this->request->input('difference');


        DB::beginTransaction();
        try {

            foreach($discount_total_orders as $id => $item) {
                DB::table('tbl_discount_total_orders')->where('id', $id)->update([
                    'total_order_start' => !empty($item['total_order_start']) ? number_unformat($item['total_order_start']) : 0,
                    'total_order_end' => !empty($item['total_order_end']) ? number_unformat($item['total_order_end']) : NULL,
                    'discount' => number_unformat($item['discount']),
                    'content' => $item['content'] ?? NULL
                ]);
            }

            foreach($leaders as $id => $item) {
                DB::table('tbl_accumulation_leaders')->where('id', $id)->update([
                    'level_discount' => number_unformat($item['level_discount']),
                    'note' => $item['note'] ?? NULL,
                ]);
                if(!empty($item['reached'])) {
                    foreach($item['reached'] as $id_reached => $money_reward) {
                        DB::table('tbl_accumulation_leaders_reward')
                            ->where('id_leaders', $id)
                            ->where('id_reached', $id_reached)
                            ->update(
                                [ 'money_reward' => number_unformat($money_reward)]
                            );
                    }
                }
            }
            foreach($interest as $id => $item) {
                DB::table('tbl_accumulation_interest')
                    ->where('id', $id)
                    ->update([
                        'discount_npp' => number_unformat($item['discount_npp']),
                        'difference' => number_unformat($item['difference']),
                        'radio_bonus' => number_unformat($item['radio_bonus']),
                    ]);
            }
            foreach($passive as $id => $item) {
                DB::table('tbl_accumulation_passive')
                    ->where('id', $id)
                    ->update([
                        'total_order_start' => number_unformat($item['total_order_start']),
                        'total_order_end' => number_unformat($item['total_order_end']),
                        'radio_bonus' => number_unformat($item['radio_bonus']),
                        'total_radio_bonus' => number_unformat($item['total_radio_bonus']),
                    ]);
            }
            foreach($difference as $key => $item) {
                foreach($item as $key2 => $item2) {
                    foreach($item2 as $key3 => $item3) {
                        $difference[$key][$key2][$key3] = number_unformat($item3);
                    }
                }
            }
            DB::table('tbl_options')
                ->where('name', 'accumulation_difference')->update([
                'value' => json_encode($difference)
            ]);
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
