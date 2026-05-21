<?php

use App\Libraries\App;
use App\Libraries\Alepay;
use App\Models\CategoryCarDetail;
use app\Models\CategoryProducts;
use App\Models\Clients;
use App\Models\Payslip;
use App\Models\CountryCurrency;
use App\Models\ReferralLevel;
use App\Models\Transaction;
use App\Models\TransactionDriver;
use App\Models\TransferPackage;
use App\Models\RequestWithdrawMoney;
use App\Models\TransactionNotDriver;
use App\Models\TransactionDriverPusher;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Pusher\Pusher;
use App\Models\Notification;
use App\Models\ContractTransaction;
use App\Models\ContractTranTemplate;
use App\Models\HandoverRecord;
use App\Models\CustomerClass;
use App\Models\ScriptDetail;
use App\Models\Script;
use App\Models\ProductsFilter;
//use Carbon\Carbon;

function setLangAPI($request) {
    $locale = $request['_locale'];
    $_locale = 'vi';
    if($locale == 'vi') {
        $_locale = 'vi';
    }
    else if($locale == 'en') {
        $_locale = 'en';
    }
    else if($locale == 'cn') {
        $_locale = 'cn';
    }
    else if($locale == 'th') {
        $_locale = 'th';
    }
    else if($locale == 'kr' || $locale == 'ko') {
        $_locale = 'kr';
    }
    \Illuminate\Support\Facades\App::setLocale($_locale);
}

function getCountRequestMoney()
{
    $count = RequestWithdrawMoney::where('status', 0)->count();
    return $count;
}

function getTransactionStaff($service = 1)
{
    $transaction_staff_id = 0;
    $dtUser = User::whereHas('department', function ($query) {
        $query->where('check_transaction', 1);
    })->whereExists(function ($query) use ($service) {
            $query->select("tbl_user_service.user_id")->from('tbl_user_service')->whereRaw(
                    'tbl_user_service.user_id = tbl_users.id'
                )->where('tbl_user_service.service', $service);
        })->where('check_tran', 0)->where('active', 1)->orderBy('priority', 'asc')->get();
    if (count($dtUser) == 0) {
        User::whereHas('department', function ($query) {
            $query->where('check_transaction', 1);
        })->update([
            'check_tran' => 0
        ]);
        $dtUser = User::whereHas('department', function ($query) {
            $query->where('check_transaction', 1);
        })->whereExists(function ($query) use ($service) {
                $query->select("tbl_user_service.user_id")->from('tbl_user_service')->whereRaw(
                        'tbl_user_service.user_id = tbl_users.id'
                    )->where('tbl_user_service.service', $service);
            })->where('check_tran', 0)->where('active', 1)->orderBy('priority', 'asc')->get();
    }
    if (!empty($dtUser)) {
        foreach ($dtUser as $key => $value) {
            $user_id = $value->id;
            $dtTransactionCheckDriver = TransactionDriver::select(
                'id',
                'date',
                'created_at',
                DB::raw("1 as type")
            )->orderBy('created_at', 'desc')->limit(1);
            $dtTransactionCheckVs1 = Transaction::select(
                'id',
                'date',
                'created_at',
                DB::raw("2 as type")
            )->orderBy('created_at', 'desc')->limit(1)->unionall($dtTransactionCheckDriver);
            $dtTransactionCheckNew = DB::query()->fromSub($dtTransactionCheckVs1, 'union_query')->select(
                    'id',
                    'type'
                )->orderBy('created_at', 'desc')->first();
            if (!empty($dtTransactionCheckNew)) {
                if ($dtTransactionCheckNew->type == 1) {
                    $dtTransactionCheck = TransactionDriver::select(
                        'id',
                        'created_at'
                    )->find($dtTransactionCheckNew->id);
                } else {
                    $dtTransactionCheck = Transaction::select('id', 'created_at')->find($dtTransactionCheckNew->id);
                }
                if (!empty($dtTransactionCheck->transaction_staff_new())) {
                    if ($dtTransactionCheck->transaction_staff_new()->id == $user_id) {
                        continue;
                    } else {
                        $transaction_staff_id = $user_id;
                        DB::table('tbl_users')->where('id', $user_id)->update([
                            'check_tran' => 1
                        ]);
                        break;
                    }
                } else {
                    $transaction_staff_id = $user_id;
                    DB::table('tbl_users')->where('id', $user_id)->update([
                        'check_tran' => 1
                    ]);
                    break;
                }
            } else {
                $transaction_staff_id = $user_id;
                DB::table('tbl_users')->where('id', $user_id)->update([
                    'check_tran' => 1
                ]);
                break;
            }
        }
    }
    if (empty($transaction_staff_id)) {
        $dtUser = User::whereHas('department', function ($query) {
            $query->where('check_transaction', 1);
        })->whereExists(function ($query) use ($service) {
                $query->select("tbl_user_service.user_id")->from('tbl_user_service')->whereRaw(
                        'tbl_user_service.user_id = tbl_users.id'
                    )->where('tbl_user_service.service', $service);
            })->where('check_tran', 0)->where('active', 1)->orderBy('priority', 'asc')->first();
        if (!empty($dtUser)) {
            $user_id = $dtUser->id;
            $transaction_staff_id = $user_id;
            DB::table('tbl_users')->where('id', $user_id)->update([
                'check_tran' => 1
            ]);
        }
    }
    return $transaction_staff_id;
}

function autoAcceptTransaction($transaction_id = 0, $alepay = false)
{
    $data = [];
    $request = new Request();
    $customer_id = !empty($request->client) ? $request->client->id : 0;
    $dtTransaction = TransactionDriver::find($transaction_id);
    if (!empty($dtTransaction) && $dtTransaction->status == Config::get('constant')['status_request_driver']) {
        $dtTransactionRoute = $dtTransaction->route_new;
        $lat = $dtTransactionRoute->lat_start;
        $lon = $dtTransactionRoute->lng_start;
        $amount = $dtTransaction->amount;
        $amount = 0;
        $category_car_detail_id = $dtTransaction->category_car_detail_id;
        $categoryCarDetail = CategoryCarDetail::find($category_car_detail_id);
        $type_car = $categoryCarDetail->category_car->type;
        $dtPayment = $dtTransaction->payment;
        $type_payment_mode = $dtPayment->payment_mode->type;
        $orderBy = 'distance asc';
        $dtDriver = Driver::select(
            'tbl_driver.*',
            DB::raw(
                "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"
            )
        )->where(
                function ($query) use (
                    $lat,
                    $lon,
                    $type_car,
                    $customer_id,
                    $type_payment_mode,
                    $amount,
                    $category_car_detail_id
                ) {
                    if (!empty($lat) && !empty($lon)) {
                        $query->where(
                            DB::raw(
                                "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null)"
                            ),
                            '!=',
                            null
                        );
                        $query->where(
                            DB::raw(
                                "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"
                            ),
                            '>=',
                            0
                        );
                        $query->where(
                            DB::raw(
                                "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"
                            ),
                            '<=',
                            10
                        );
                    }
                    $query->where('active', 1);
                    $query->where('status', 1);
                    $query->where('auto_accpet', 1);
                    $query->where('verify_phone', 1);
                    $query->where('status_cccd', 1);
                    $query->where('status_judicial_record', 1);
                    $query->where('status_confirm_conduct', 1);
                    $query->where('status_health_certificate', 1);
                    $query->where('status_certificate_hiv', 1);
                    $query->whereHas('service_register', function ($q) use ($category_car_detail_id) {
                        $q->where('category_car_detail_id', $category_car_detail_id);
                    });
                    $query->doesntHave('transaction_driver', 'and', function ($q) use ($customer_id) {
                        $q->where(function ($instance) use ($customer_id) {
                            $instance->whereIn(
                                'status',
                                [
                                    Config::get('constant')['status_approve_driver'],
                                    Config::get('constant')['status_start_driver']
                                ]
                            );
                            $instance->orWhere(function ($ins) use ($customer_id) {
                                // hủy chuyến của khách này thì 15 phút sau mới dc nhân chuyến từ khách hàng này
                                $ins->whereIn('status', [Config::get('constant')['status_driver_cancel_driver']]);
                                $ins->where('customer_id', $customer_id);
                                $ins->where(
                                    DB::raw(
                                        'ADDDATE(date, INTERVAL ' . get_option('driver_cancel_minute') . ' MINUTE)'
                                    ),
                                    '>=',
                                    date('Y-m-d H:i:s')
                                );
                            });
                        });
                    });
                    if ($type_car == 1) {
                        $query->whereHas('driving_liscense_bike', function ($q) {
                            $q->where("status", 1);
                        });
                    } else {
                        $query->whereHas('driving_liscense', function ($q) {
                            $q->where("status", 1);
                        });
                    }
                    if ($type_payment_mode == 1) {
                        $query->where('account_balance', '>=', $amount);
                    }
                }
            )->orderByRaw($orderBy)->first();
        if (empty($dtDriver)) {
            $resultDriver = findDriver($transaction_id);
            if (empty($resultDriver['driver'])) {
                $refund_money = 0;
                $owner_refund_money = 0;
                if (!empty($dtTransaction->payment)) {
                    $cancel_trip_id = 2;
                    $dtCancelTrip = DB::table('tbl_cancel_trip_driver')->where('id', $cancel_trip_id)->first();
                    if (!empty($dtCancelTrip)) {
                        $percent_guest_cancel = $dtCancelTrip->percent_guest_cancel;
                        $refund_money = $dtTransaction->payment->payment * $percent_guest_cancel / 100;
                        $percent_owen_cancel = $dtCancelTrip->percent_owen_cancel;
                        $owner_refund_money = $dtTransaction->payment->payment * $percent_owen_cancel / 100;
                    }
                }
                addPaySlip($transaction_id);
                $dtPayment = !empty($dtTransaction->payment) ? $dtTransaction->payment : [];
                if (!empty($dtPayment)) {
                    if ($dtPayment->payment_mode->type == 1) {
                        addPaySlip($dtTransaction->id);
                    } else {
                        $dataRefund = [];
                        $dataRefund['tokenKey'] = get_option('token_key');
                        $dataRefund['transactionCode'] = $dtPayment->note;
                        $dataRefund['merchantRefundCode'] = $dtTransaction->reference_no;
                        $dataRefund['refundAmount'] = $dtPayment->payment;
                        $dataRefund['reason'] = 'Hoàn tiền giao dịch ' . $dtTransaction->reference_no;
                        $dataRefund['transaction_id'] = $dtTransaction->id;
                        getRefundTransaction($dataRefund);
                    }
                }
                TransactionDriver::where('id', $dtTransaction->id)->update([
                        'not_driver' => 1,
                        'refund_money' => $refund_money,
                        'owner_refund_money' => $owner_refund_money,
                        'status' => Config::get('constant')['status_system_cancel_driver'],
                        'date_status' => date('Y-m-d H:i:s'),
                        'staff_status' => Config::get('constant')['customer_kanow'],
                        'note_status' => 'Hệ thống hủy không tìm thấy tài xế'
                    ]);
                $data['result'] = false;
                $data['id'] = $transaction_id;
                $data['status'] = -1;
                $data['driver'] = 0;
                $data['message'] = 'Không tìm thấy tài xế phù hợp!';
            } else {
                $data['result'] = true;
                $data['id'] = $transaction_id;
                $data['status'] = -2;
                $data['driver'] = 0;
                $data['message'] = '';
            }
            if ($alepay) {
                return $data;
            } else {
                return response()->json($data);
            }
        } else {
            $percent_app_customer = !empty($dtDriver->discount_app) ? $dtDriver->discount_app->percent : 0;
            $percent_customer = 100 - $percent_app_customer;
            $amount_rent_cost = $dtTransaction->amount;
            $revenue_customer = ($amount_rent_cost * $percent_customer) / 100;
            $status = Config::get('constant')['status_approve_driver'];
            $date_status = date('Y-m-d H:i:s');
            $staff_status = Config::get('constant')['customer_kanow'];
            TransactionDriver::where('id', $dtTransaction->id)->update([
                    'percent_customer' => $percent_customer,
                    'amount_rent_cost' => $amount_rent_cost,
                    'revenue_customer' => $revenue_customer,
                    'driver_id' => $dtDriver->id,
                    'status' => $status,
                    'date_status' => $date_status,
                    'staff_status' => $staff_status,
                    'auto_accpet' => 1,
                ]);
            $data['result'] = true;
            $data['id'] = $dtTransaction->id;
            $data['status'] = $status;
            $data['driver'] = $dtDriver->id;
            $data['message'] = 'Tìm thấy tài xế phù hợp!';
            //tài xế
            $dtTransaction->driver_id = $dtDriver->id;
            $arr_object_id_driver = [];
            $arr_object_id = [];
            $dtDriver = Driver::select(
                'tbl_driver.fullname as name',
                'tbl_driver.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'driver' as 'object_type'")
            )->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_driver.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'driver'"));
                })->where('tbl_driver.id', $dtDriver->id)->get()->toArray();
            if (!empty($dtDriver)) {
                $arr_object_id_driver = array_merge($arr_object_id_driver, $dtDriver);
            }
            $arr_object_id_driver = array_values($arr_object_id_driver);
            ConnectPusher($dtTransaction, $arr_object_id_driver, 'auto-accpet-driver');
            // noti
            $transactionStaff = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )->join(
                    'tbl_transaction_driver_staff',
                    'tbl_transaction_driver_staff.user_id',
                    '=',
                    'tbl_users.id'
                )->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_driver_staff.user_id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })->where('transaction_id', $dtTransaction->id)->get()->toArray();
            if (!empty($transactionStaff)) {
                $arr_object_id = array_merge($arr_object_id, $transactionStaff);
            }
            $dtStaffAdmin = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_users.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })->where('admin', 1)->where('active', 1)->get()->toArray();
            if (!empty($dtStaffAdmin)) {
                $arr_object_id = array_merge($arr_object_id, $dtStaffAdmin);
            }
            $dtCustomer = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })->where('tbl_clients.id', $dtTransaction->customer_id)->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            $arr_object_id = array_values($arr_object_id);
            Notification::notiBookDriverTransaction(
                $dtTransaction->id,
                Config::get('constant')['noti_approve_driver'],
                $customer_id,
                2,
                $arr_object_id,
                $arr_object_id_driver
            );
        }
    }
    if ($alepay) {
        return $data;
    } else {
        return response()->json($data);
    }
}

function findDriver($transaction_id = 0)
{
    $request = new Request();
    $dtTransaction = TransactionDriver::find($transaction_id);
    $customer_id = !empty($request->client) ? $request->client->id : 0;
    if (!empty($dtTransaction) && $dtTransaction->status == Config::get('constant')['status_request_driver']) {
        $dtTransactionRoute = $dtTransaction->route_new;
        $lat = $dtTransactionRoute->lat_start;
        $lon = $dtTransactionRoute->lng_start;
        $amount = $dtTransaction->amount;
        $amount = 0;
        $category_car_detail_id = $dtTransaction->category_car_detail_id;
        $categoryCarDetail = CategoryCarDetail::find($category_car_detail_id);
        $categoryCarDetail->image = asset('storage/' . $categoryCarDetail->image);
        $dtTransaction->categoryCarDetail = $categoryCarDetail;
        $dtTransaction->fullname = $dtTransaction->customer->fullname;
        $type_car = $categoryCarDetail->category_car->type;
        $dtPayment = $dtTransaction->payment;
        $type_payment_mode = $dtPayment->payment_mode->type;
        $orderBy = 'distance asc';
        $countDriver = Driver::select(
            'tbl_driver.*',
            DB::raw(
                "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"
            )
        )->where(function ($query) use (
                $lat,
                $lon,
                $type_car,
                $customer_id,
                $type_payment_mode,
                $amount,
                $transaction_id,
                $category_car_detail_id
            ) {
                if (!empty($lat) && !empty($lon)) {
                    $query->where(
                        DB::raw(
                            "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null)"
                        ),
                        '!=',
                        null
                    );
                    $query->where(
                        DB::raw(
                            "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"
                        ),
                        '>=',
                        0
                    );
                    $query->where(
                        DB::raw(
                            "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"
                        ),
                        '<=',
                        10
                    );
                }
                $query->where('active', 1);
                $query->where('status', 1);
                $query->where('verify_phone', 1);
                $query->where('auto_accpet', 0);
                $query->where('status_cccd', 1);
                $query->where('status_judicial_record', 1);
                $query->where('status_confirm_conduct', 1);
                $query->where('status_health_certificate', 1);
                $query->where('status_certificate_hiv', 1);
                $query->whereHas('service_register', function ($q) use ($category_car_detail_id) {
                    $q->where('category_car_detail_id', $category_car_detail_id);
                });
                $query->doesntHave('transaction_driver', 'and', function ($q) use ($customer_id) {
                    $q->where(function ($instance) use ($customer_id) {
                        $instance->whereIn(
                            'status',
                            [
                                Config::get('constant')['status_approve_driver'],
                                Config::get('constant')['status_start_driver']
                            ]
                        );
                        $instance->orWhere(function ($ins) use ($customer_id) {
                            // hủy chuyến của khách này thì 15 phút sau mới dc nhân chuyến từ khách hàng này
                            $ins->whereIn('status', [Config::get('constant')['status_driver_cancel_driver']]);
                            $ins->where('customer_id', $customer_id);
                            $ins->where(
                                DB::raw('ADDDATE(date, INTERVAL ' . get_option('driver_cancel_minute') . ' MINUTE)'),
                                '>=',
                                date('Y-m-d H:i:s')
                            );
                        });
                    });
                });
                $query->doesntHave('transaction_not_driver', 'and', function ($q) use ($transaction_id) {
                    $q->where('transaction_id', $transaction_id);
                });
                $query->doesntHave('transaction_driver_pusher');
                if ($type_car == 1) {
                    $query->whereHas('driving_liscense_bike', function ($q) {
                        $q->where("status", 1);
                    });
                } else {
                    $query->whereHas('driving_liscense', function ($q) {
                        $q->where("status", 1);
                    });
                }
                if ($type_payment_mode == 1) {
                    $query->where('account_balance', '>=', $amount);
                }
            })->count();
        if ($countDriver > 0) {
            $dtDriver = Driver::select(
                'tbl_driver.*',
                DB::raw(
                    "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"
                )
            )->where(function ($query) use (
                    $lat,
                    $lon,
                    $type_car,
                    $customer_id,
                    $type_payment_mode,
                    $amount,
                    $transaction_id,
                    $category_car_detail_id
                ) {
                    if (!empty($lat) && !empty($lon)) {
                        $query->where(
                            DB::raw(
                                "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null)"
                            ),
                            '!=',
                            null
                        );
                        $query->where(
                            DB::raw(
                                "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"
                            ),
                            '>=',
                            0
                        );
                        $query->where(
                            DB::raw(
                                "IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"
                            ),
                            '<=',
                            10
                        );
                    }
                    $query->where('active', 1);
                    $query->where('status', 1);
                    $query->where('verify_phone', 1);
                    $query->where('auto_accpet', 0);
                    $query->whereHas('service_register', function ($q) use ($category_car_detail_id) {
                        $q->where('category_car_detail_id', $category_car_detail_id);
                    });
                    $query->doesntHave('transaction_driver', 'and', function ($q) use ($customer_id) {
                        $q->where(function ($instance) use ($customer_id) {
                            $instance->whereIn(
                                'status',
                                [
                                    Config::get('constant')['status_approve_driver'],
                                    Config::get('constant')['status_start_driver']
                                ]
                            );
                            $instance->orWhere(function ($ins) use ($customer_id) {
                                // hủy chuyến của khách này thì 15 phút sau mới dc nhân chuyến từ khách hàng này
                                $ins->whereIn('status', [Config::get('constant')['status_driver_cancel_driver']]);
                                $ins->where('customer_id', $customer_id);
                                $ins->where(
                                    DB::raw(
                                        'ADDDATE(date, INTERVAL ' . get_option('driver_cancel_minute') . ' MINUTE)'
                                    ),
                                    '>=',
                                    date('Y-m-d H:i:s')
                                );
                            });
                        });
                    });
                    $query->doesntHave('transaction_not_driver', 'and', function ($q) use ($transaction_id) {
                        $q->where('transaction_id', $transaction_id);
                    });
                    $query->doesntHave('transaction_driver_pusher');
                    if ($type_car == 1) {
                        $query->whereHas('driving_liscense_bike', function ($q) {
                            $q->where("status", 1);
                        });
                    } else {
                        $query->whereHas('driving_liscense', function ($q) {
                            $q->where("status", 1);
                        });
                    }
                    if ($type_payment_mode == 1) {
                        $query->where('account_balance', '>=', $amount);
                    }
                })->orderByRaw($orderBy)->first();
            if (!empty($dtDriver)) {
                //add vào pusher
                $transactionDriverPusher = new TransactionDriverPusher();
                $transactionDriverPusher->transaction_id = $transaction_id;
                $transactionDriverPusher->driver_id = $dtDriver->id;
                $transactionDriverPusher->save();
                //tài xế
                $dtTransaction->driver_id = $dtDriver->id;
                $arr_object_id = [];
                $dtDriverNew = Driver::select(
                    'tbl_driver.fullname as name',
                    'tbl_driver.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'driver' as 'object_type'")
                )->leftJoin('tbl_player_id', function ($join) {
                        $join->on('tbl_player_id.object_id', '=', 'tbl_driver.id');
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'driver'"));
                    })->where('tbl_driver.id', $dtDriver->id)->get()->toArray();
                if (!empty($dtDriverNew)) {
                    $arr_object_id = array_merge($arr_object_id, $dtDriverNew);
                }
                $arr_object_id = array_values($arr_object_id);
                ConnectPusher($dtTransaction, $arr_object_id, 'accpet-driver');
                Notification::notiFindDriverTransaction(
                    $dtTransaction->id,
                    Config::get('constant')['noti_not_driver'],
                    0,
                    2,
                    $arr_object_id
                );
                $data['result'] = true;
                $data['driver'] = $dtDriver->id;
                $data['message'] = '';
                return $data;
            } else {
                $refund_money = 0;
                $owner_refund_money = 0;
                if (!empty($dtTransaction->payment)) {
                    $cancel_trip_id = 2;
                    $dtCancelTrip = DB::table('tbl_cancel_trip_driver')->where('id', $cancel_trip_id)->first();
                    if (!empty($dtCancelTrip)) {
                        $percent_guest_cancel = $dtCancelTrip->percent_guest_cancel;
                        $refund_money = $dtTransaction->payment->payment * $percent_guest_cancel / 100;
                        $percent_owen_cancel = $dtCancelTrip->percent_owen_cancel;
                        $owner_refund_money = $dtTransaction->payment->payment * $percent_owen_cancel / 100;
                    }
                }
                addPaySlip($transaction_id);
                TransactionDriver::where('id', $dtTransaction->id)->update([
                        'not_driver' => 1,
                        'refund_money' => $refund_money,
                        'owner_refund_money' => $owner_refund_money,
                        'status' => Config::get('constant')['status_system_cancel_driver'],
                        'date_status' => date('Y-m-d H:i:s'),
                        'staff_status' => Config::get('constant')['customer_kanow'],
                        'note_status' => 'Hệ thống hủy không tìm thấy tài xế'
                    ]);
                $arr_object_id = [];
                $dtCustomer = Clients::select(
                    'tbl_clients.fullname as name',
                    'tbl_clients.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'customer' as 'object_type'")
                )->leftJoin('tbl_player_id', function ($join) {
                        $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                    })->where('tbl_clients.id', $dtTransaction->customer_id)->get()->toArray();
                if (!empty($dtCustomer)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                }
                //delete driver pusher transaction
                $dtTransaction->transaction_driver_pusher()->delete();
                ConnectPusher($dtTransaction, $arr_object_id, 'not-driver');
                $data['result'] = true;
                $data['driver'] = 0;
                $data['message'] = '';
                return $data;
            }
        } else {
            $refund_money = 0;
            $owner_refund_money = 0;
            if (!empty($dtTransaction->payment)) {
                $cancel_trip_id = 2;
                $dtCancelTrip = DB::table('tbl_cancel_trip_driver')->where('id', $cancel_trip_id)->first();
                if (!empty($dtCancelTrip)) {
                    $percent_guest_cancel = $dtCancelTrip->percent_guest_cancel;
                    $refund_money = $dtTransaction->payment->payment * $percent_guest_cancel / 100;
                    $percent_owen_cancel = $dtCancelTrip->percent_owen_cancel;
                    $owner_refund_money = $dtTransaction->payment->payment * $percent_owen_cancel / 100;
                }
            }
            addPaySlip($transaction_id);
            TransactionDriver::where('id', $dtTransaction->id)->update([
                    'not_driver' => 1,
                    'refund_money' => $refund_money,
                    'owner_refund_money' => $owner_refund_money,
                    'status' => Config::get('constant')['status_system_cancel_driver'],
                    'date_status' => date('Y-m-d H:i:s'),
                    'staff_status' => Config::get('constant')['customer_kanow'],
                    'note_status' => 'Hệ thống hủy không tìm thấy tài xế'
                ]);
            //delete driver pusher transaction
            $dtTransaction->transaction_driver_pusher()->delete();
            $arr_object_id = [];
            $dtCustomer = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })->where('tbl_clients.id', $dtTransaction->customer_id)->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            ConnectPusher($dtTransaction, $arr_object_id, 'not-driver');
            $transactionStaff = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )->join(
                    'tbl_transaction_driver_staff',
                    'tbl_transaction_driver_staff.user_id',
                    '=',
                    'tbl_users.id'
                )->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_driver_staff.user_id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })->where('transaction_id', $dtTransaction->id)->get()->toArray();
            if (!empty($transactionStaff)) {
                $arr_object_id = array_merge($arr_object_id, $transactionStaff);
            }
            $dtStaffAdmin = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_users.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })->where('admin', 1)->where('active', 1)->get()->toArray();
            if (!empty($dtStaffAdmin)) {
                $arr_object_id = array_merge($arr_object_id, $dtStaffAdmin);
            }
            $arr_object_id = array_values($arr_object_id);
            Notification::notiNotDriverTransaction(
                $dtTransaction->id,
                Config::get('constant')['noti_not_driver'],
                0,
                2,
                $arr_object_id
            );
            $data['result'] = true;
            $data['driver'] = 0;
            $data['message'] = '';
            return $data;
        }
    } else {
        //delete driver pusher transaction
        $dtTransaction->transaction_driver_pusher()->delete();
        $data['result'] = false;
        $data['driver'] = 0;
        $data['message'] = 'Chuyến đã bị hủy bởi người đặt xe !';
        return $data;
    }
}

function getListKmDriver($id = 0)
{
    $data = [
        [
            'id' => 1,
            'name' => '< 1km',
            'value' => '0-1'
        ],
        [
            'id' => 2,
            'name' => '1km - 3km',
            'value' => '1-3'
        ],
        [
            'id' => 3,
            'name' => '3km - 5km',
            'value' => '3-5'
        ],
        [
            'id' => 4,
            'name' => '5km - 7km',
            'value' => '5-7'
        ],
        [
            'id' => 5,
            'name' => '7km - 10km',
            'value' => '7-10'
        ],
        [
            'id' => 6,
            'name' => '5 sao',
            'value' => '5'
        ],
    ];
    if (!empty($id)) {
        if (is_array($id)) {
            $data = array_filter($data, function ($item) use ($id) {
                return in_array($item['id'], $id);
            });
            if (!empty($data)) {
                $data = array_values($data);
                return $data;
            } else {
                return null;
            }
        } else {
            $data = array_filter($data, function ($item) use ($id) {
                return $item['id'] == $id;
            });
            if (!empty($data)) {
                $data = array_values($data);
                return $data[0]['value'];
            } else {
                return null;
            }
        }
    } else {
        return $data;
    }
}

function getOpeningBalanceClient($customer_id = 0, $month = null, $year = null)
{
    $dtBalance = DB::table('tbl_client_balance_month')->where('customer_id', $customer_id)->where(
            DB::raw('DATE_FORMAT(CONCAT(year,"-",month,"-",01),"%Y-%m")'),
            '<',
            $year . '-' . $month
        )->sum('balance');
    return $dtBalance;
}

function getMonthYear($month = '', $year = '')
{
    if ($month == 01) {
        $month = 12;
        $year = $year - 1;
    } else {
        $month = $month - 1;
        $year = $year;
    }
    if ($month < 10) {
        $month = '0' . $month;
    }
    return [
        'month' => $month,
        'year' => $year,
    ];
}

function changeBalance(
    $id = 0,
    $type = '',
    $type_increase = true,
    $typeF = '',
    $customer_id = 0,
    $balance_default = 0,
    $date = null
) {
    $dtObject = [];
    if ($type == 'finish') {
        $dtObject = Transaction::select(
            'tbl_transaction.id',
            'tbl_transaction.date as date',
            'tbl_transaction.total as total',
            DB::raw("0 as 'revenue_customer'"),
            'tbl_transaction.customer_id as customer_id',
        )->where('check_balance', 0)->whereHas('payment')->where('tbl_transaction.id', $id)->first();
        $title_balance = 'Approve the transaction';
    } elseif ($type == 'cancel') {
        $dtObject = Transaction::select(
            'tbl_transaction.id',
            'tbl_transaction.date as date',
            'tbl_transaction.total as total',
            DB::raw("0 as 'revenue_customer'"),
            'tbl_transaction.customer_id as customer_id',
        )->whereHas('payment')->where('tbl_transaction.id', $id)->first();
        $title_balance = 'Cancel the transaction';
    } elseif ($type == 'reward_profit') {
        $dtObject = CustomerClass::select(
            'tbl_customer_class.id',
            'tbl_customer_class.created_at as date',
            'tbl_customer_class.total as total',
            DB::raw("0 as 'revenue_customer'"),
            'tbl_customer_class.customer_id as customer_id',
        )->where('tbl_customer_class.id', $id)->first();
        $title_balance = 'Investment profit';
    } elseif ($type == 'reward_commission') {
        $dtObject = CustomerClass::select(
            'tbl_customer_class.id',
            'tbl_customer_class.created_at as date',
            'tbl_customer_class.total as total',
            'tbl_customer_class.percent_f1 as percent_f1',
            'tbl_customer_class.percent_f2 as percent_f2',
            'tbl_customer_class.percent_f3 as percent_f3',
            DB::raw("0 as 'revenue_customer'"),
            'tbl_customer_class.customer_id as customer_id',
        )->where('tbl_customer_class.id', $id)->first();
        $title_balance = 'Referral to purchase a package';
    } elseif ($type == 'reward_ranking_bonus') {
        $dtObject = collect([
            [
                'id' => 0,
                'date' => $date,
                'customer_id' => $customer_id,
                'total' => 0,
                'revenue_customer' => 0,
            ]
        ])->first();
        $dtObject = (object)$dtObject;
        $title_balance = 'Rank-bonus reward';
    } elseif ($type == 'reward_leader_bonus') {
        $dtObject = collect([
            [
                'id' => 0,
                'date' => $date,
                'customer_id' => $customer_id,
                'total' => 0,
                'revenue_customer' => 0,
            ]
        ])->first();
        $dtObject = (object)$dtObject;
        $title_balance = 'Leadership reward';
    } elseif ($type == 'transaction_wallet') {
        $dtObject = Transaction::select(
            'tbl_transaction.id',
            'tbl_transaction.date as date',
            'tbl_transaction.total as total',
            DB::raw("0 as 'revenue_customer'"),
            'tbl_transaction.customer_id as customer_id',
        )->where('check_balance_wallet', 0)->where('id', $id)->first();
        $title_balance = 'Buy package';
    } elseif ($type == 'request_money') {
        if (!empty($type_increase)) {
            $dtObject = RequestWithdrawMoney::select(
                'tbl_request_withdraw_money.id',
                'tbl_request_withdraw_money.date as date',
                'tbl_request_withdraw_money.grand_total as total',
                DB::raw("0 as 'revenue_customer'"),
                'tbl_request_withdraw_money.customer_id as customer_id',
            )
                //                ->whereHas('transfer_money')
                ->where('check_balance', 0)->where('id', $id)->first();
            $title_balance = 'Withdrawal request';
        } else {
            $dtObject = RequestWithdrawMoney::select(
                'tbl_request_withdraw_money.id',
                'tbl_request_withdraw_money.date as date',
                'tbl_request_withdraw_money.grand_total as total',
                DB::raw("0 as 'revenue_customer'"),
                'tbl_request_withdraw_money.customer_id as customer_id',
            )->where('check_balance', 1)->where('id', $id)->first();
            $title_balance = 'Cancel the withdrawal request';
        }
    } elseif ($type == 'transfer_money') {
        if (!empty($type_increase)) {
            $dtObject = TransferPackage::select(
                'tbl_transfer_package.id',
                'tbl_transfer_package.date as date',
                'tbl_transfer_package.grand_total as total',
                'tbl_transfer_package.total as total_receive',
                DB::raw("0 as 'revenue_customer'"),
                'tbl_transfer_package.customer_id as customer_id',
                'tbl_transfer_package.customer_id_receive as customer_id_receive',
            )->where('check_balance', 0)->where('id', $id)->first();
            $title_balance = 'Transfer package';
        } else {
            $dtObject = TransferPackage::select(
                'tbl_transfer_package.id',
                'tbl_transfer_package.date as date',
                'tbl_transfer_package.grand_total as total',
                'tbl_transfer_package.total as total_receive',
                DB::raw("0 as 'revenue_customer'"),
                'tbl_transfer_package.customer_id as customer_id',
                'tbl_transfer_package.customer_id_receive as customer_id_receive',
            )->where('check_balance', 1)->where('id', $id)->first();
            $title_balance = 'Delete transfer package';
        }
    } elseif ($type == 'adjust_balance') {
        $dtObject = collect([
            [
                'id' => 0,
                'date' => $date,
                'customer_id' => $customer_id,
                'total' => 0,
                'revenue_customer' => 0,
            ]
        ])->first();
        $dtObject = (object)$dtObject;
    }
    if (!empty($dtObject)) {
        $type_check = 1;
        $revenue = 0;
        $revenue_receive = 0;
        $customer_id_receive = 0;
        $type_driver = 1;
        $date = _dthuan($dtObject->date);
        $dateNew = to_sql_date($date);
        $date = explode('/', $date);
        $month = $date[1];
        $year = $date[2];
        $revenue_customer = $dtObject->revenue_customer;
        $total = $dtObject->total;
        if ($type == 'finish') {
            $revenue = $total;
            $customer_id = $dtObject->customer_id;
            $object_type = 'transaction';
        } elseif ($type == 'cancel') {
            $revenue = -$total;
            $customer_id = $dtObject->customer_id;
            $object_type = 'transaction';
        } elseif ($type == 'transaction_wallet') {
            $revenue = -$total;
            $customer_id = $dtObject->customer_id;
            $object_type = 'transaction_wallet';
        } elseif ($type == 'request_money') {
            if (!empty($type_increase)) {
                $revenue = -$total;
            } else {
                $revenue = $total;
            }
            $customer_id = $dtObject->customer_id;
            $object_type = 'request_money';
        } elseif ($type == 'transfer_money') {
            if (!empty($type_increase)) {
                $revenue = -$total;
                $revenue_receive = $dtObject->total_receive;
            } else {
                $revenue = $total;
                $revenue_receive = -$dtObject->total_receive;
            }
            $customer_id = $dtObject->customer_id;
            $customer_id_receive = $dtObject->customer_id_receive;
            $object_type = 'transfer_money';
        } elseif ($type == 'reward_profit') {
            $revenue = $balance_default;
            $customer_id = $dtObject->customer_id;
            $object_type = 'reward_profit';
        } elseif ($type == "reward_commission") {
            if ($typeF == 'F1') {
                $revenue = ($total * $dtObject->percent_f1) / 100;
                $customer_id = $customer_id;
                $object_type = 'reward_commission';
            } elseif ($typeF == 'F2') {
                $revenue = ($total * $dtObject->percent_f2) / 100;
                $customer_id = $customer_id;
                $object_type = 'reward_commission';
            } elseif ($typeF == 'F3') {
                $revenue = ($total * $dtObject->percent_f3) / 100;
                $customer_id = $customer_id;
                $object_type = 'reward_commission';
            }
        } elseif ($type == 'reward_ranking_bonus') {
            if (!empty($type_increase)) {
                $revenue = $balance_default;
            } else {
                $revenue = -$balance_default;
            }
            $customer_id = $customer_id;
            $object_type = 'reward_ranking_bonus';
        } elseif ($type == 'reward_leader_bonus') {
            if (!empty($type_increase)) {
                $revenue = $balance_default;
            } else {
                $revenue = -$balance_default;
            }
            $customer_id = $customer_id;
            $object_type = 'reward_leader_bonus';
        } elseif ($type == 'adjust_balance') {
            if (!empty($type_increase)) {
                $revenue = $balance_default;
            } else {
                $revenue = -$balance_default;
            }
            $customer_id = $customer_id;
            $object_type = 'adjust_balance';
        }
        $dtClient = Clients::find($customer_id);
        $account_balance_client = $dtClient->account_balance + $revenue;
        Clients::where('id', $dtClient->id)->update([
            'account_balance' => $account_balance_client
        ]);
        if ($type == 'request_money') {
            if (!empty($type_increase)) {
                RequestWithdrawMoney::where('id', $dtObject->id)->update([
                    'check_balance' => 1
                ]);
            } else {
                RequestWithdrawMoney::where('id', $dtObject->id)->update([
                    'check_balance' => 0
                ]);
            }
        }
        if ($type == 'transaction_wallet') {
            Transaction::where('id', $dtObject->id)->update([
                'check_balance_wallet' => 1
            ]);
        } elseif ($type == 'transfer_money') {
            if (!empty($type_increase)) {
                TransferPackage::where('id', $dtObject->id)->update([
                    'check_balance' => 1
                ]);
            } else {
                TransferPackage::where('id', $dtObject->id)->update([
                    'check_balance' => 0
                ]);
            }
        } elseif ($type == 'reward_profit') {
        } elseif ($type == 'reward_commission') {
        } elseif ($type == 'reward_ranking_bonus') {
        } elseif ($type == 'reward_leader_bonus') {
        } elseif ($type == 'adjust_balance') {
        } else {
            Transaction::where('id', $dtObject->id)->update([
                'check_balance' => 1
            ]);
        }
        $clientBalance = DB::table('tbl_client_balance_month')->where('customer_id', $customer_id)->where(
                'month',
                $month
            )->where('year', $year)->first();
        if (!empty($clientBalance)) {
            $balance = $clientBalance->balance + $revenue;
            DB::table('tbl_client_balance_month')->where('id', $clientBalance->id)->update([
                    'balance' => $balance
                ]);
        } else {
            DB::table('tbl_client_balance_month')->insert([
                'customer_id' => $customer_id,
                'month' => $month,
                'year' => $year,
                'balance' => $revenue,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if ($type == 'transfer_money') {
            $dtClientReceive = Clients::find($customer_id_receive);
            $account_balance_client_receive = $dtClientReceive->account_balance + $revenue_receive;
            Clients::where('id', $dtClientReceive->id)->update([
                'account_balance' => $account_balance_client_receive
            ]);
            $clientBalanceReceive = DB::table('tbl_client_balance_month')->where(
                    'customer_id',
                    $customer_id_receive
                )->where('month', $month)->where('year', $year)->first();
            if (!empty($clientBalanceReceive)) {
                $balance = $clientBalanceReceive->balance + $revenue_receive;
                DB::table('tbl_client_balance_month')->where('id', $clientBalanceReceive->id)->update([
                        'balance' => $balance
                    ]);
            } else {
                DB::table('tbl_client_balance_month')->insert([
                    'customer_id' => $customer_id_receive,
                    'month' => $month,
                    'year' => $year,
                    'balance' => $revenue_receive,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        //daily
        if ($type == 'reward_commission') {
            $dtProfitDay = DB::table('tbl_customer_reward_profit_day')->where('customer_id', $customer_id)->where(
                    'type',
                    2
                )->where('date', $dateNew)->first();
            if (!empty($dtProfitDay)) {
                $balance_day_profit = $dtProfitDay->balance + $revenue;
                DB::table('tbl_customer_reward_profit_day')->where('id', $dtProfitDay->id)->update([
                        'balance' => $balance_day_profit,
                    ]);
            } else {
                DB::table('tbl_customer_reward_profit_day')->insert([
                    'customer_id' => $customer_id,
                    'date' => $dateNew,
                    'balance' => $revenue,
                    'type' => 2,
                    'date_start' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $dtProfit = DB::table('tbl_customer_reward_day')->where('customer_id', $customer_id)->where(
                    'date',
                    $dateNew
                )->first();
            if (!empty($dtProfit)) {
                $balance_day = $dtProfit->balance + $revenue;
                DB::table('tbl_customer_reward_day')->where('id', $dtProfit->id)->update([
                        'balance' => $balance_day
                    ]);
            } else {
                DB::table('tbl_customer_reward_day')->insert([
                    'customer_id' => $customer_id,
                    'date' => $dateNew,
                    'balance' => $revenue,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            // commission f1,f2,f3
            if ($typeF == 'F1') {
                $type_commission = 1;
            } elseif ($typeF == 'F2') {
                $type_commission = 2;
            } elseif ($typeF == 'F3') {
                $type_commission = 3;
            }
            $dtRewardCommission = DB::table('tbl_customer_reward_commission_day')->where(
                    'customer_id',
                    $customer_id
                )->where('date', $dateNew)->where('type', $type_commission)->first();
            if (!empty($dtRewardCommission)) {
                $balance_commission_day = $dtRewardCommission->balance + $revenue;
                DB::table('tbl_customer_reward_commission_day')->where('id', $dtRewardCommission->id)->update([
                        'balance' => $balance_commission_day
                    ]);
            } else {
                DB::table('tbl_customer_reward_commission_day')->insert([
                    'customer_id' => $customer_id,
                    'date' => $dateNew,
                    'type' => $type_commission,
                    'balance' => $revenue,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $arr_object_id = [];
            $dtCustomer = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })->where('tbl_clients.id', $customer_id)->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            if (!empty($arr_object_id)) {
                $arr_object_id = array_values($arr_object_id);
                ConnectPusher('', $arr_object_id, 'reward_day');
            }
        }
        //
        if ($revenue < 0) {
            $type_check = 2;
        } else {
            $type_check = 1;
        }
        $object_id = $dtObject->id;
        if ($type == 'adjust_balance') {
            DB::table('tbl_client_balance_history')->insert([
                'customer_id' => $customer_id,
                'object_id' => $object_id,
                'object_type' => $object_type,
                'balance' => $revenue,
                'type_check' => $type_check,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if (!in_array($type, ['reward_profit', 'reward_ranking_bonus', 'adjust_balance'])) {
            DB::table('tbl_client_balance_history')->insert([
                'customer_id' => $customer_id,
                'object_id' => $object_id,
                'object_type' => $object_type,
                'balance' => $revenue,
                'type_check' => $type_check,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            if (!empty($revenue)) {
                Notification::notiChangeBalanceOwner(
                    $customer_id,
                    $revenue,
                    $account_balance_client,
                    $title_balance,
                    $type_driver
                );
            }
            if ($type == "transfer_money") {
                if ($revenue_receive < 0) {
                    $type_check_receive = 2;
                } else {
                    $type_check_receive = 1;
                }
                DB::table('tbl_client_balance_history')->insert([
                    'customer_id' => $customer_id_receive,
                    'object_id' => $object_id,
                    'object_type' => $object_type,
                    'balance' => $revenue_receive,
                    'type_check' => $type_check_receive,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                if (!empty($revenue_receive)) {
                    Notification::notiChangeBalanceOwner(
                        $customer_id_receive,
                        $revenue_receive,
                        $account_balance_client_receive,
                        $title_balance,
                        $type_driver
                    );
                }
            }
        }
        return true;
    }
    return false;
}

function changeBalanceDriver($id = 0, $type = '')
{
    $dtObject = [];
    if ($type == 'finish') {
        $dtObject = TransactionDriver::select(
            'tbl_transaction_driver.id',
            'tbl_transaction_driver.date as date',
            'tbl_transaction_driver.grand_total as total',
            'tbl_transaction_driver.revenue_customer as revenue_customer',
            'tbl_transaction_driver.driver_id as driver_id',
            'tbl_transaction_driver.status as status',
        )->where('check_balance', 0)->where('tbl_transaction_driver.id', $id)->first();
        $title_balance = 'Hoàn thành chuyến đi';
    } elseif ($type == 'confirm_cancel_driver' || $type == 'not_confirm_cancel_driver') {
        $dtObject = TransactionDriver::select(
            'tbl_transaction_driver.id',
            'tbl_transaction_driver.date as date',
            'tbl_transaction_driver.owner_refund_money as total',
            DB::raw("0 as 'revenue_customer'"),
            'tbl_transaction_driver.driver_id as driver_id',
            'tbl_transaction_driver.status as status',
        )->where('check_balance', 0)->where('tbl_transaction_driver.id', $id)->first();
    }
    if (!empty($dtObject)) {
        $revenue = 0;
        $date = _dthuan($dtObject->date);
        $date = explode('/', $date);
        $month = $date[1];
        $year = $date[2];
        $revenue_customer = $dtObject->revenue_customer;
        $total = $dtObject->total;
        $driver_id = $dtObject->driver_id;
        $object_type = 'transaction_driver';
        if ($type == 'finish') {
            if ($dtObject->payment->payment_mode->type == 1) {
                $revenue = ($dtObject->payment->payment - $revenue_customer);
                $revenue = -$revenue;
            } else {
                $revenue = $revenue_customer;
            }
            $object_type = 'transaction_driver';
        } elseif ($type == 'confirm_cancel_driver') {
            if ($dtObject->status == Config::get('constant')['status_guest_cancel_driver']) {
                $revenue = $total;
                $title_balance = 'Người đặt xe hủy chuyến';
            } elseif ($dtObject->status == Config::get('constant')['status_driver_cancel_driver']) {
                $revenue = -$total;
                $title_balance = 'Tài xế hủy chuyến';
            }
        } elseif ($type == 'not_confirm_cancel_driver') {
            if ($dtObject->status == Config::get('constant')['status_guest_cancel_driver']) {
                $revenue = 0;
                $title_balance = 'Người đặt xe hủy chuyến';
            } elseif ($dtObject->status == Config::get('constant')['status_driver_cancel_driver']) {
                $revenue = 0;
                $title_balance = 'Tài xế hủy chuyến';
            }
        }
        $dtDriver = Driver::find($driver_id);
        $account_balance_driver = $dtDriver->account_balance + $revenue;
        Driver::where('id', $dtDriver->id)->update([
            'account_balance' => $account_balance_driver
        ]);
        if ($type == 'request_money') {
            RequestWithdrawMoney::where('id', $dtObject->id)->update([
                'check_balance' => 1
            ]);
        } else {
            TransactionDriver::where('id', $dtObject->id)->update([
                'check_balance' => 1
            ]);
        }
        $clientBalance = DB::table('tbl_driver_balance_month')->where('driver_id', $driver_id)->where(
                'month',
                $month
            )->where('year', $year)->first();
        if (!empty($clientBalance)) {
            $balance = $clientBalance->balance + $revenue;
            DB::table('tbl_driver_balance_month')->where('id', $clientBalance->id)->update([
                    'balance' => $balance
                ]);
        } else {
            DB::table('tbl_driver_balance_month')->insert([
                'driver_id' => $driver_id,
                'month' => $month,
                'year' => $year,
                'balance' => $revenue,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if ($revenue < 0) {
            $type_check = 2;
        } else {
            $type_check = 1;
        }
        $object_id = $dtObject->id;
        DB::table('tbl_driver_balance_history')->insert([
            'driver_id' => $driver_id,
            'object_id' => $object_id,
            'object_type' => $object_type,
            'balance' => $revenue,
            'type_check' => $type_check,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        if (!empty($revenue)) {
            Notification::notiChangeBalanceDriver($driver_id, $revenue, $account_balance_driver, $title_balance);
        }
        return true;
    }
    return false;
}

function changePoint($id = 0, $type = '')
{
    $dtObject = [];
    if ($type == 'finish') {
        $dtObject = Transaction::select(
            'tbl_transaction.id',
            'tbl_transaction.date_end as date',
            'tbl_transaction.customer_id as customer_id'
        )->where('status', Config::get('constant')['status_transaction_finish'])->where(
                'tbl_transaction.id',
                $id
            )->first();
        $title_point = 'Hoàn thành chuyến đi';
    } elseif ($type == 'finish_driver') {
        $dtObject = TransactionDriver::select(
            'tbl_transaction_driver.id',
            'tbl_transaction_driver.date as date',
            'tbl_transaction_driver.customer_id as customer_id',
        )->where('status', Config::get('constant')['status_finish_driver'])->where(
                'tbl_transaction_driver.id',
                $id
            )->first();
        $title_point = 'Hoàn thành chuyến đi đặt tài xế';
    } elseif ($type == 'reset') {
        $dtObject = Clients::select(
            'tbl_clients.id',
            'tbl_clients.point',
            DB::raw('"' . date('Y-m-d') . '" as date'),
            'tbl_clients.id as customer_id',
        )->where('tbl_clients.id', $id)->first();
        $title_point = 'Reset điểm hết hạn';
    }
    if (!empty($dtObject)) {
        $date = _dthuan($dtObject->date);
        $date_point = to_sql_date(($date));
        $date = explode('/', $date);
        $month = $date[1];
        $year = $date[2];
        $point = 0;
        $type_check = 1;
        $type_check_history = 1;
        if ($type == 'finish') {
            $point = get_option('point_car');
            $customer_id = $dtObject->customer_id;
            $type_check = 1;
            $type_check_history = 1;
        } elseif ($type == 'finish_driver') {
            $point = get_option('point_driver');
            $customer_id = $dtObject->customer_id;
            $type_check = 2;
            $type_check_history = 1;
        } elseif ($type == 'reset') {
            $point = -$dtObject->point;
            $customer_id = $dtObject->customer_id;
            $type_check_history = 3;
        }
        $dtClient = Clients::find($customer_id);
        $point_client = $dtClient->point + $point;
        if ($type == 'reset') {
            Clients::where('id', $dtClient->id)->update([
                'point' => $point_client
            ]);
        } else {
            Clients::where('id', $dtClient->id)->update([
                'point' => $point_client,
                'date_point' => $date_point
            ]);
        }
        $clientPoint = DB::table('tbl_client_point_month')->where('customer_id', $customer_id)->where(
                'month',
                $month
            )->where('year', $year)->first();
        if (!empty($clientPoint)) {
            $pointNew = $clientPoint->point + $point;
            DB::table('tbl_client_point_month')->where('id', $clientPoint->id)->update([
                    'point' => $pointNew
                ]);
        } else {
            DB::table('tbl_client_point_month')->insert([
                'customer_id' => $customer_id,
                'month' => $month,
                'year' => $year,
                'point' => $point,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        DB::table('tbl_client_point_history')->insert([
            'customer_id' => $customer_id,
            'point' => $point,
            'type' => $type_check,
            'type_check' => $type_check_history,
            'transaction_id' => ($type == 'reset' ? 0 : $dtObject->id),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        if (!empty($point)) {
            Notification::notiChangePointClient($customer_id, $point, $point_client, $title_point, $type_check);
        }
        return true;
    }
    return false;
}

function getRefundTransaction($dataPost = [])
{
    $opts = [
        'apiKey' => get_option('token_key'),
        'encryptKey' => get_option('encrypt_key'),
        'checksumKey' => get_option('checksum_key'),
        'callbackUrl' => asset('/api/alepay/resultAlepay'),
        'callbackUrlCar' => asset('/api/alepay/resultAlepayCard'),
        'callbackUrlDriver' => asset('/api/alepay/resultAlepayDriver')
    ];
    $alepay = new Alepay($opts);
    $transaction_id = $dataPost['transaction_id'];
    $checkRefund = TransactionDriver::whereHas('payslip', function ($query) use ($transaction_id) {
        $query->where('transaction_driver_id', $transaction_id);
    })->count();
    if (!empty($checkRefund)) {
        $data['result'] = false;
        $data['message'] = 'Giao dịch này đã được hoàn tiền !';
        return $data;
    }
    $result = $alepay->getRefundTransaction($dataPost);
    if (isset($result) && $result->code === '000') {
        $transaction = TransactionDriver::find($transaction_id);
        if (!empty($transaction)) {
            $dtPayment = $transaction->payment;
            if (!empty($dtPayment)) {
                $dtPaySlip = new Payslip();
                $reference_no_pay = getReference('pay_slip');
                $dtPaySlip->reference_no = $reference_no_pay;
                $dtPaySlip->date = date('Y-m-d H:i:s');
                $dtPaySlip->object_id = $transaction->customer_id;
                $dtPaySlip->transaction_driver_id = $transaction->id;
                $dtPaySlip->payment_mode_id = $dtPayment->payment_mode_id;
                $dtPaySlip->cost_id = Config::get('constant')['cost_id_default'];
                $dtPaySlip->payment_driver_id = $dtPayment->id;
                $dtPaySlip->total = $dtPayment->payment;
                $dtPaySlip->created_by = $transaction->customer_id;
                $dtPaySlip->type_create = 2;
                $dtPaySlip->note = $result->data->reason;
                $dtPaySlip->transactionCode = $result->data->transactionCode;
                $dtPaySlip->checksum = $result->data->checksum;
                $dtPaySlip->save();
                if ($dtPaySlip) {
                    if ($reference_no_pay == getReference('pay_slip')) {
                        updateReference('pay_slip');
                    }
                }
            }
        }
    }
    $data['result'] = true;
    $data['message'] = 'Thành công';
    return $data;
}

function addPaySlip($transaction_id)
{
    $transaction = Transaction::find($transaction_id);
    if (!empty($transaction)) {
        $dtPayment = !empty($transaction->payment) ? $transaction->payment[0] : null;
        $dtPayslipOld = $transaction->payslip;
        if (empty($dtPayslipOld)) {
            if (!empty($dtPayment)) {
                $dtPaySlip = new Payslip();
                $reference_no_pay = getReference('pay_slip');
                $dtPaySlip->reference_no = $reference_no_pay;
                $dtPaySlip->date = date('Y-m-d H:i:s');
                $dtPaySlip->object_id = $transaction->customer_id;
                $dtPaySlip->transaction_id = $transaction->id;
                $dtPaySlip->payment_mode_id = $dtPayment->payment_mode_id;
                $dtPaySlip->cost_id = Config::get('constant')['cost_id_default'];
                $dtPaySlip->payment_id = $dtPayment->id;
                $dtPaySlip->total = $dtPayment->payment;
                $dtPaySlip->created_by = get_staff_user_id();
                $dtPaySlip->type_create = 1;
                $dtPaySlip->note = null;
                $dtPaySlip->save();
                if ($dtPaySlip) {
                    if ($reference_no_pay == getReference('pay_slip')) {
                        updateReference('pay_slip');
                    }
                }
            }
        }
    }
}

function addContract($transaction_id, $admin = false)
{
    $transaction = Transaction::find($transaction_id);
    if (!empty($transaction)) {
        $contract_transaction_template = Config::get('constant')['contract_transaction_template'];
        $contractTran = $transaction->contract;
        $dtContractTemplate = ContractTranTemplate::find($contract_transaction_template);
        if (!empty($dtContractTemplate)) {
            if ($transaction->status == Config::get('constant')['status_despoit'] || $admin == true) {
                if (!empty($contractTran)) {
                    $dtContract = ContractTransaction::find($contractTran->id);
                    $reference_no = $contractTran->reference_no;
                } else {
                    $dtContract = new ContractTransaction();
                    $reference_no = getReference('contract_transaction');
                }
                $dtContract->reference_no = $reference_no;
                $dtContract->date = date('Y-m-d H:i:s');
                $dtContract->date_start = $transaction->date_start;
                $dtContract->date_end = $transaction->date_end;
                $dtContract->transaction_id = $transaction->id;
                $dtContract->customer_id = $transaction->customer_id;
                $dtContract->grand_total = $transaction->grand_total;
                $dtContract->content = $dtContractTemplate->content;
                $dtContract->save();
                if ($dtContract) {
                    if (empty($contractTran)) {
                        if ($reference_no == getReference('contract_transaction')) {
                            updateReference('contract_transaction');
                        }
                    }
                    return true;
                }
            }
        }
    }
    return true;
}

function addHandoverRecord($transaction_id, $admin = false)
{
    $transaction = Transaction::find($transaction_id);
    if (!empty($transaction)) {
        $contract_transaction_template = Config::get('constant')['handover_transaction_template'];
        $handoverRecord = $transaction->handover_record;
        $dtContractTemplate = ContractTranTemplate::find($contract_transaction_template);
        if (!empty($dtContractTemplate)) {
            if ($transaction->status == Config::get('constant')['status_despoit'] || $admin == true) {
                if (!empty($handoverRecord)) {
                    $dtHandover = HandoverRecord::find($handoverRecord->id);
                    $reference_no = $handoverRecord->reference_no;
                } else {
                    $dtHandover = new HandoverRecord();
                    $reference_no = getReference('handover_record');
                }
                $dtHandover->reference_no = $reference_no;
                $dtHandover->date = date('Y-m-d H:i:s');
                $dtHandover->transaction_id = $transaction->id;
                $dtHandover->customer_id = $transaction->customer_id;
                $dtHandover->content = $dtContractTemplate->content;
                $dtHandover->save();
                if ($dtHandover) {
                    if (empty($contractTran)) {
                        if ($reference_no == getReference('handover_record')) {
                            updateReference('handover_record');
                        }
                    }
                    return true;
                }
            }
        }
    }
    return true;
}

function getAllDateInMonth($month, $year, $format = "d/m/Y")
{
    $list = [];
    for ($d = 1; $d <= 31; $d++) {
        $time = mktime(12, 0, 0, $month, $d, $year);
        if (date('m', $time) == $month) {
            $ymd = date('Y-m-d', $time);
            $list[$ymd] = date($format, $time);
        }
    }
    return $list;
}

function convert_vi_to_en($str)
{
    $str = preg_replace("(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)", "a", $str);
    $str = preg_replace("(à|á|ạ|ả|ã|â|ầ|ấ|ạ|ẩ|ẫ|ă|ẳ|ẵ|ặ|ắ|ằ)", "a", $str);
    $str = preg_replace("(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)", "e", $str);
    $str = preg_replace("(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)", "e", $str);
    $str = preg_replace("(ì|í|ị|ỉ|ĩ)", "i", $str);
    $str = preg_replace("(ì|í|ị|ỉ|ĩ)", "i", $str);
    $str = preg_replace("(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)", "o", $str);
    $str = preg_replace("(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)", "o", $str);
    $str = preg_replace("(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)", "u", $str);
    $str = preg_replace("(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)", "u", $str);
    $str = preg_replace("(ỳ|ý|ỵ|ỷ|ỹ)", "y", $str);
    $str = preg_replace("(ỳ|ý|ỵ|ỹ)", "y", $str);
    $str = preg_replace("(đ)", "d", $str);
    $str = preg_replace("(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)", "A", $str);
    $str = preg_replace("(À|Á|Ạ|Á|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ẵ|Ẳ|Ặ|Ắ|Ằ)", "A", $str);
    $str = preg_replace("(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)", "E", $str);
    $str = preg_replace("(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)", "E", $str);
    $str = preg_replace("(Ì|Í|Ị|Ỉ|Ĩ)", "I", $str);
    $str = preg_replace("(Ì|Í|Ị|Í|Ĩ)", "I", $str);
    $str = preg_replace("(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)", "O", $str);
    $str = preg_replace("(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)", "O", $str);
    $str = preg_replace("(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)", "U", $str);
    $str = preg_replace("(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)", "U", $str);
    $str = preg_replace("(Ỳ|Ý|Ỵ|Ỷ|Ỹ)", "Y", $str);
    $str = preg_replace("(Ỳ|Ý|Ỵ|Ý|Ỹ)", "Y", $str);
    $str = preg_replace("(Đ)", "D", $str);
    $str = preg_replace("(Đ)", "D", $str);
    return $str;
}

function send_zalo($phone = 0, $event = null, $template_id = 0, $data = [], $access_token_zalo = '')
{
    if (empty($phone)) {
        return false;
    }
    if(empty($access_token_zalo)) {
        $access_token_zalo = get_option('access_token_zalo');
    }
    $phone = substr($phone, 1, 9);
    $phone = '84' . $phone;
    $datetimeid = (time() . random_int(100, 999));
    $id_send_zalo = DB::table('tbl_send_zalo')->insertGetId([
        'template_id' => $template_id,
        'event' => $event,
        'send_zalo_id' => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    if (!empty($id_send_zalo)) {
        DB::table('tbl_send_zalo_client')->insertGetId([
            'send_zalo_id' => $id_send_zalo,
            'phone' => $phone,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    $dtTemplate = DB::table('tbl_template_zalo')->where('template_id', $template_id)->first();
    $content_api = '';
    if (!empty($dtTemplate)) {
        $content_api = $dtTemplate->content_api;
    }
    foreach($data as $key => $value) {
        $content_api = str_replace('{' . $key . '}', '"' . $value . '"', $content_api);
    }
//    $content_api = str_replace('{otp}', '"' . $data['otp'] . '"', $content_api);
    if ($template_id == '538989') {
        $content = 'Mã xác thực của bạn là<br/>OTP: ' . $data['otp'];
    }
    else if ($template_id == '492827') {
        $content = 'Trải nghiệm sản phẩm<br/>
                    Xin chào <name>, đơn hàng <order_code> đăng ký vào vào ngày <date> đã và đang được <status>.
                    Mã đơn hàng:
                    <order_code>
                    Trạng thái: <status>
                    Sản phẩm: <code_product>
                    Cám ơn bạn đã quan tâm đến sản phẩm của chúng tôi.';

        foreach($data as $key => $value) {
            $content = str_replace('<' . $key . '>', '"' . $value . '"', $content);
        }
    }
    else if ($template_id == '493204') {
        $content = 'Xin chào <customer_name>,
                    Cám ơn bạn đã tin tưởng và đăng ký trải nghiệm sản phẩm của chúng tôi.
                    Bạn có thể cho chúng tôi đánh giá về sản phẩm sau khi trải nghiệm để chúng tôi có thể cải thiện chất lượng sản phẩm.
                    Mã đơn hàng <order_code>
                    Trạng thái <status_review>
                    Ngày nhận hàng';

        foreach($data as $key => $value) {
            $content = str_replace('<' . $key . '>', '"' . $value . '"', $content);
        }
    }
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://business.openapi.zalo.me/message/template',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_POSTFIELDS => '{
        "phone": "' . $phone . '",
        "template_id": "' . $template_id . '",
        "template_data": ' . $content_api . ',
            "tracking_id":"' . $datetimeid . '"
        }',
        CURLOPT_HTTPHEADER => array(
            'access_token: ' . $access_token_zalo . '',
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $curl_response = json_decode($response);
    $status = 0;
    $log = null;
    $sendZalo = false;
    if (!empty($curl_response)) {
        if ($curl_response->error == 0) {
            $status = $curl_response->data->msg_id;
            $sendZalo = true;
        } elseif ($curl_response->error == -124) {
            $status = $curl_response->error;
            refresh_token_zalo($phone, $event, $template_id, $data);
        } else {
            $status = $curl_response->error;
        }
        $log = $curl_response->message;
    }
    DB::table('tbl_send_zalo')->where('id', $id_send_zalo)->update([
        'send_zalo_id' => $status,
        'content' => $content,
        'log' => $log
    ]);
    if (!empty($sendZalo)) {
        return true;
    }
    return false;
}

function refresh_token_zalo($phone = 0, $event = '', $template_id = 0, $data = [])
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://oauth.zaloapp.com/v4/oa/access_token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_POSTFIELDS => 'refresh_token=' . get_option(
                'refresh_token_zalo'
            ) . '&app_id='.get_option('app_id_zalo').'&grant_type=refresh_token',
        CURLOPT_HTTPHEADER => array(
            'secret_key: '.get_option('secret_key_zalo').'',
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response);
    if (!empty($response->refresh_token)) {
        DB::table('tbl_options')->where('name', 'refresh_token_zalo')->update([
            'value' => $response->refresh_token
        ]);
        DB::table('tbl_options')->where('name', 'access_token_zalo')->update([
            'value' => $response->access_token
        ]);
        $app = new App();
        $app->flushCache();
        send_zalo($phone, $event, $template_id, $data, $response->access_token);
    }
}

function send_sms($phone = "", $content = "", $event = null)
{
    if (!empty($phone) && !empty($content)) {
        $phone = substr($phone, 1, 9);
        $phone = '84' . $phone;
        $content = convert_vi_to_en($content);
        $datetimeid = (time() . random_int(100, 999));
        $data_string = "";
        $status_send = 1;
        $id_send_sms = DB::table('tbl_send_sms')->insertGetId([
            'phone' => $phone,
            'brand_name' => get_option('brand_sms'),
            'message' => $content,
            'status' => $status_send,
            'event' => $event,
            'date_send' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $service_url = "https://apisms.idiqr.com/smsapi/sendSMS?username=" . get_option('user_sms');
        $service_url .= '&password=' . get_option('password_sms');
        $service_url .= '&source_addr=' . get_option('brand_sms');
        $service_url .= '&dest_addr=' . $phone;
        $service_url .= '&message=' . urlencode($content);
        $service_url .= '&type=0';
        $service_url .= '&request_id=' . $datetimeid;
        $curl = curl_init($service_url);
        curl_setopt($curl, CURLOPT_URL, $service_url);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        $curl_response = curl_exec($curl);
        curl_close($curl);
        $sendSms = false;
        $messageError = '';
        $status_send = $curl_response;
        if ($curl_response > 0) {
            $sendSms = true;
            $log = 'Thành công';
        } elseif ($curl_response == -1) {
            $log = 'Sai tên hoặc mật khẩu!';
        } elseif ($curl_response == -2) {
            $log = 'Tài khoản không đủ tiền để gửi (trả trước)';
        } elseif ($curl_response == -3) {
            $log = 'Sai định dạng số điện thoại (dest_addr) hoặc chưa hỗ trợ Mạng di động của số vừa gửi';
        } elseif ($curl_response == -4) {
            $log = 'Brandname (source_addr) chưa đăng ký';
        } elseif ($curl_response == -5) {
            $log = 'Mẫu tin gửi chưa được khai báo';
        } elseif ($curl_response == -99) {
            $log = 'Lỗi nội tại hệ thống';
        } else {
            $log = $messageError;
        }
        DB::table('tbl_send_sms')->where('id', $id_send_sms)->update([
            'status' => $status_send,
            'log' => $log
        ]);
        if (!empty($sendSms)) {
            return true;
        }
    }
    return false;
}

function send_sms_multi($phone = [], $content = "", $event = null)
{
    if (!empty($phone) && !empty($content)) {
        $content = convert_vi_to_en($content);
        $datetimeid = (time() . random_int(100, 999));
        $data_string = "";
        $status_send = 1;
        $stringPhone = '';
        $arrSendSmsId = [];
        $arrPhone = [];
        foreach ($phone as $k => $v) {
            $v = substr($v, 1, 10);
            $v = '84' . $v;
            $stringPhone .= $v . ',';
            $arrPhone[] = $v;
        }
        $stringPhone = trim($stringPhone, ',');
        $service_url = "https://apisms.idiqr.com/smsapi/sendMulti?username=" . get_option('user_sms');
        $service_url .= '&password=' . get_option('password_sms');
        $service_url .= '&source_addr=' . get_option('brand_sms');
        $service_url .= '&dest_addr=' . $stringPhone;
        $service_url .= '&message=' . urlencode($content);
        $service_url .= '&type=0';
        $service_url .= '&request_id=' . $datetimeid;
        $curl = curl_init($service_url);
        curl_setopt($curl, CURLOPT_URL, $service_url);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        $curl_response = curl_exec($curl);
        $curl_response = json_decode($curl_response);
        curl_close($curl);
        $sendSms = false;
        if (!empty($curl_response)) {
            foreach ($curl_response as $k => $v) {
                $v = (array)$v;
                if (count($curl_response) > 1) {
                    if (in_array($v['dest_addr'], $arrPhone)) {
                        $id_send_sms = DB::table('tbl_send_sms')->insertGetId([
                            'phone' => $v['dest_addr'],
                            'brand_name' => get_option('brand_sms'),
                            'message' => $content,
                            'status' => $v['msgid'],
                            'log' => $v['decription'],
                            'event' => $event,
                            'date_send' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    } else {
                        $id_send_sms = DB::table('tbl_send_sms')->insertGetId([
                            'phone' => $arrPhone[$k],
                            'brand_name' => get_option('brand_sms'),
                            'message' => $content,
                            'status' => $v['msgid'],
                            'log' => $v['decription'],
                            'event' => $event,
                            'date_send' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                } else {
                    foreach ($arrPhone as $kk => $vv) {
                        $id_send_sms = DB::table('tbl_send_sms')->insertGetId([
                            'phone' => $vv,
                            'brand_name' => get_option('brand_sms'),
                            'message' => $content,
                            'status' => $v['status'],
                            'log' => $v['decription'],
                            'event' => $event,
                            'date_send' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }
        return true;
    }
    return false;
}

function send_sms_old($phone = "", $content = "", $event = null)
{
    if (!empty($phone) && !empty($content)) {
        $content = convert_vi_to_en($content);
        $datetimeid = (time() . random_int(100, 999));
        $data_string = "";
        $status_send = 1;
        $id_send_sms = DB::table('tbl_send_sms')->insertGetId([
            'phone' => $phone,
            'brand_name' => get_option('brand_sms'),
            'message' => $content,
            'status' => $status_send,
            'event' => $event,
            'date_send' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $service_url = "https://api.s247.vn:8443/api/sms?site=" . get_option('brand_sms');
        $service_url .= '&LoginName=' . get_option('user_sms');
        $service_url .= '&Password=' . get_option('password_sms');
        $service_url .= '&SendServiceCode=11';
        $service_url .= '&BrandName=' . get_option('brand_sms');
        $service_url .= '&mobile=' . $phone;
        $service_url .= '&Message=' . urlencode($content);
        $service_url .= '&SmsId=' . $datetimeid;
        $service_url .= '&Unicode=0';
        // $service_url = get_option('curl_post_sms');
        $curl = curl_init($service_url);
        curl_setopt($curl, CURLOPT_URL, $service_url);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('application/json'));
        $curl_response = curl_exec($curl);
        curl_close($curl);
        $curl_response = json_decode($curl_response);
        $sendSms = false;
        $messageError = '';
        if (!empty($curl_response->message)) {
            $messageError = $curl_response->message;
        }
        if ($curl_response->code == '1') {
            $sendSms = true;
            $status_send = 1;
            $log = $curl_response->message;
        } else {
            $status_send = 2;
            $log = $messageError;
        }
        DB::table('tbl_send_sms')->where('id', $id_send_sms);
        DB::table('tbl_send_sms')->update([
            'status' => $status_send,
            'log' => $log
        ]);
        if (!empty($sendSms)) {
            return true;
        }
    }
    return false;
}

function total_payment($payment_mode_id = 0, $date_start = null, $date_end = null)
{
    $totalPayment = 0;
    $totalPaymentDriver = 0;
    if (is_numeric($payment_mode_id) && !empty($date_start)) {
        $totalPayment = DB::table('tbl_payment')->where('payment_mode_id', $payment_mode_id)->where(
                function ($query) use ($date_start, $date_end) {
                    if (!empty($date_start) && !empty($date_end)) {
                        $query->where('date', '>=', $date_start);
                        $query->where('date', '<=', $date_end);
                    } else {
                        $query->where('date', '<', $date_start);
                    }
                }
            )->sum('payment');
        $totalPaymentDriver = DB::table('tbl_payment_driver')->where('payment_mode_id', $payment_mode_id)->where(
                function ($query) use ($date_start, $date_end) {
                    if (!empty($date_start) && !empty($date_end)) {
                        $query->where('date', '>=', $date_start);
                        $query->where('date', '<=', $date_end);
                    } else {
                        $query->where('date', '<', $date_start);
                    }
                }
            )->sum('payment');
    }
    return $totalPayment + $totalPaymentDriver;
}

function total_payslip($payment_mode_id = 0, $date_start = null, $date_end = null)
{
    $totalPayslip = 0;
    $totalPayslipDriver = 0;
    if (is_numeric($payment_mode_id) && !empty($date_start)) {
        $totalPayslip = DB::table('tbl_transfer_money')->where('payment_mode_id', $payment_mode_id)->where(
                function ($query) use ($date_start, $date_end) {
                    if (!empty($date_start) && !empty($date_end)) {
                        $query->where('date', '>=', $date_start);
                        $query->where('date', '<=', $date_end);
                    } else {
                        $query->where('date', '<', $date_start);
                    }
                }
            )->sum('total');
        $totalPayslipDriver = DB::table('tbl_pay_slip')->where('payment_mode_id', $payment_mode_id)->where(
                function ($query) use ($date_start, $date_end) {
                    if (!empty($date_start) && !empty($date_end)) {
                        $query->where('date', '>=', $date_start);
                        $query->where('date', '<=', $date_end);
                    } else {
                        $query->where('date', '<', $date_start);
                    }
                }
            )->sum('total');
    }
    return $totalPayslip + $totalPayslipDriver;
}

function countNotiNotRead()
{
    $staff_id = get_staff_user_id();
    $dtNoti = Notification::whereHas('notification_staff', function ($query) use ($staff_id) {
        $query->where('is_read', 0);
        $query->where('object_id', $staff_id);
        $query->where('object_type', 'staff');
    })->count();
    return $dtNoti;
}

function distance($lat1, $lon1, $lat2, $lon2, $unit)
{
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    } else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(
                deg2rad($theta)
            );
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
        if ($unit == "K") {
            return ($miles * 1.609344);
        } elseif ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}

function diffInHours($start_date, $end_date)
{
    $starttimestamp = strtotime($start_date);
    $endtimestamp = strtotime($end_date);
    $diff = abs($endtimestamp - $starttimestamp) / 3600;
    return $diff;
}

function formatDayHour($hour = 0)
{
    $day_new = 0;
    $day = floor($hour / 24);
    $hour_old = $hour - ($day * 24);
    if ($hour_old > 0) {
        $day_new = 1;
    }
    $day = $day + $day_new;
    return $day;
}

function formatDayHourProvince($hour = 0)
{
    if ($hour < 24) {
        return '0.' . $hour;
    }
    $day_new = 0;
    $day = floor($hour / 24);
    $hour_old = $hour - ($day * 24);
    if ($hour_old > 0) {
        $hour_old = $hour_old;
    }
    $day = $day . '.' . $hour_old;
    return $day;
}

function ConnectPusher($data = '', $users = [], $events = 'change-status')
{
    $config = Config::get('broadcasting.connections.pusher');
    if(!empty($config['key']) && !empty($config['secret']) && !empty($config['app_id'])) {
        return true;
    }
    $options = [
        'cluster' => $config['options']['cluster'],
        'encrypted' => $config['options']['encrypted'],
        'useTLS' => false
    ];
    if (!is_array($users) || count($users) == 0) {
        return false;
    }
    $channels = [];
    foreach ($users as $user) {
        $object_type = $user['object_type'] == 'owen' ? 'customer' : $user['object_type'];
        array_push($channels, 'notifications-channel-' . $user['object_id'] . '-' . $object_type);
    }
    $channels = array_unique($channels);
    $pusher = new Pusher($config['key'], $config['secret'], $config['app_id'], $options);
    if (count($channels) <= 100) {
        $pusher->trigger($channels, $events, $data);
    } else {
        // Chia mảng thành các phần nhỏ và gửi từng phần
        $chunks = array_chunk($channels, 100);
        foreach ($chunks as $chunk) {
            $pusher->trigger($chunk, $events, $data);
        }
    }
    //    $pusher->trigger($channels, $events, $data);
    return true;
}

if (!function_exists('lang')) {
    function lang(
        $message = null,
        $key = 'message',
        array $replace = [],
        ?string $locale = null
    ) {
        $fullLang = $key . '.' . $message;
        if (!empty($locale)) {
            $fullLangLater = trans($key . '.' . $message, $replace, $locale);
        } else {
            $fullLangLater = trans($key . '.' . $message, $replace);
        }
        if ($fullLang == $fullLangLater) {
            return $message;
        }
        return $fullLangLater;
    }

    function langStran($message = null, $locale = null) {
        $key = 'message';
        $fullLang = $key . '.' . $message;
        if(!empty($locale)){
            $fullLangLater = trans(($key . '.' . $message), [], $locale);
            if ($fullLang == $fullLangLater) {
                return $message;
            }
            return $fullLangLater;
        }
        else {
            return lang($message, $key);
        }
    }
}
if (!function_exists('has_permission')) {
    function has_permission($permission_parent, $permission, $user_id = 0)
    {
        if (!empty($user_id)) {
            $user = Cache::remember('user-info', 3600, function () use ($user_id) {
                return \App\Models\User::find($user_id);
            });
        } else {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        }
        if (empty($user)) {
            return false;
        }
        $subAdmin = $user->admin;
        if ($subAdmin) {
            return true;
        }
        return $user->hasPermissionUser($permission_parent, $permission);
    }
}
if (!function_exists('has_permission_parent')) {
    function has_permission_parent($permission_parent, $user_id = 0)
    {
        if (!empty($user_id)) {
            $user = Cache::remember('user-info', 3600, function () use ($user_id) {
                return \App\Models\User::find($user_id);
            });
        } else {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        }
        if (empty($user)) {
            return false;
        }
        $subAdmin = $user->admin;
        if ($subAdmin) {
            return true;
        }
        return $user->hasPermissionParent($permission_parent);
    }
}
if (!function_exists('has_permission_parent_all')) {
    function has_permission_parent_all($permission_parent = [], $user_id = 0)
    {
        if (!empty($user_id)) {
            $user = Cache::remember('user-info', 3600, function () use ($user_id) {
                return \App\Models\User::find($user_id);
            });
        } else {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        }
        if (empty($user)) {
            return false;
        }
        $subAdmin = $user->admin;
        if ($subAdmin) {
            return true;
        }
        if (empty($permission_parent)){
            return false;
        }
        $check = false;
        foreach ($permission_parent as $key => $value) {
            $check = $user->hasPermissionParent($value['id']);
            if ($check == true){
                break;
            }
        }
        return $check;
    }
}
if (!function_exists('is_admin')) {
    function is_admin($user_id = 0)
    {
        if (!empty($user_id)) {
            $user = Cache::remember('user-info', 3600, function () use ($user_id) {
                return \App\Models\User::find($user_id);
            });
        } else {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        }
        if (empty($user)) {
            return false;
        }
        $subAdmin = $user->admin;
        if ($subAdmin) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('access_denied')) {
    function access_denied($js = false, $lang = 'dt_access')
    {
        $redirectUrl = $_SERVER["HTTP_REFERER"] ?? url('admin/dashboard');

        if ($js) {
            throw new \App\Exceptions\AccessDeniedException(lang($lang), $redirectUrl);
        } else {
            return abort(401);
        }
    }
}
if (!function_exists('alert_float')) {
    function alert_float($type = 'success', $message = '')
    {
        return \Notify::$type($message, $title = null, $options = []);
    }
}
function loadImage($src = null, $size = '50px', $type = 'img-circle', $value = '', $delete = false, $height = '')
{
    return !empty($src) ? '<div style="display: flex;justify-content:center;margin-top: 5px;margin-right: 5px"
                 class="show_image">
                <a href="' . $src . '" data-lightbox="customer-profile" class="display-block mbot5">
                <img src="' . $src . '" alt="image"
                     class="img-responsive ' . $type . '"
                     ' . (!empty($size) ? 'style="width: ' . $size . ';height: ' . (!empty($height) ? $height : $size) . '"' : '') . ' >
                </a>
                <input type="hidden" name="image_old[]" id="image_old"
                class="image_old"
               data-buttonbefore="true" value="' . $value . '">
                ' . (!empty($delete) ? '<span class="delete_image" style="cursor: pointer;"><i
                class="glyphicon glyphicon-remove"></i></span>' : '') . '
            </div>' : '';
}

function loadImageNew(
    $src = null,
    $size = '50px',
    $type = 'img-circle',
    $value = '',
    $delete = false,
    $height = '',
    $name = 'image_old'
) {
    return !empty($src) ? '<div style="display: flex;justify-content:center;margin-top: 5px;margin-right: 5px"
                 class="show_image">
                <a href="' . $src . '" data-lightbox="customer-profile" class="display-block mbot5">
                <img src="' . $src . '" alt="image"
                     class="img-responsive ' . $type . '"
                     ' . (!empty($size) ? 'style="width: ' . $size . ';height: ' . (!empty($height) ? $height : $size) . '"' : '') . ' >
                </a>
                <input type="hidden" name="' . $name . '[]" id="' . $name . '"
                class="' . $name . '"
               data-buttonbefore="true" value="' . $value . '">
                ' . (!empty($delete) ? '<span class="delete_image" style="cursor: pointer;"><i
                class="glyphicon glyphicon-remove"></i></span>' : '') . '
            </div>' : '';
}

function loadImageAvatar($src = null, $size = '50px', $type = 'img-circle')
{
    return !empty($src) ? '<div style="margin-top: 5px;margin-right: 5px"
                 class="show_image">
                <a href="' . $src . '" data-lightbox="customer-profile" class="display-block mbot5">
                <img src="' . $src . '" alt="image"
                     class="img-responsive ' . $type . '"
                     style="width: ' . $size . ';height: ' . $size . '">
                </a>
            </div>' : '';
}

function loadHtmlGPLX($type = 0)
{
    if ($type == 0) {
        $html = '<div style="display: flex;align-items: center; border: 1px solid rgba(249, 236, 201, 1.35);background: rgba(249, 236, 201, 1.35); border-radius: 15px; padding: 5px;color: black"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 7.33398V10.4407" stroke="#666666" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 6.05469C8.27614 6.05469 8.5 5.83083 8.5 5.55469C8.5 5.27855 8.27614 5.05469 8 5.05469C7.72386 5.05469 7.5 5.27855 7.5 5.55469C7.5 5.83083 7.72386 6.05469 8 6.05469Z" fill="#666666"></path><path d="M7.99967 14.1673C11.4054 14.1673 14.1663 11.4064 14.1663 8.00065C14.1663 4.5949 11.4054 1.83398 7.99967 1.83398C4.59392 1.83398 1.83301 4.5949 1.83301 8.00065C1.83301 11.4064 4.59392 14.1673 7.99967 14.1673Z" stroke="#666666" stroke-linecap="round" stroke-linejoin="round"></path></svg>  Chưa xác thực</div>';
    } elseif ($type == 1) {
        $html = '<div style="display: flex;align-items: center; border: 1px solid rgb(177 255 179);background: rgb(177 255 179); border-radius: 15px; padding: 5px;color: black"><img src="admin/assets/images/tick-circle.svg"> Đã xác thực</div>';
    } elseif ($type == 2) {
        $html = '<div style="display: flex;align-items: center; border: 1px solid rgb(237 179 174);background: rgb(237 179 174); border-radius: 15px; padding: 5px;color: black"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 7.33398V10.4407" stroke="#666666" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 6.05469C8.27614 6.05469 8.5 5.83083 8.5 5.55469C8.5 5.27855 8.27614 5.05469 8 5.05469C7.72386 5.05469 7.5 5.27855 7.5 5.55469C7.5 5.83083 7.72386 6.05469 8 6.05469Z" fill="#666666"></path><path d="M7.99967 14.1673C11.4054 14.1673 14.1663 11.4064 14.1663 8.00065C14.1663 4.5949 11.4054 1.83398 7.99967 1.83398C4.59392 1.83398 1.83301 4.5949 1.83301 8.00065C1.83301 11.4064 4.59392 14.1673 7.99967 14.1673Z" stroke="#666666" stroke-linecap="round" stroke-linejoin="round"></path></svg>  Không xác thực</div>';
    }
    return $html;
}

function loadHtmlReviewStar($star = 1)
{
    $html = '';
    $star = number_unformat($star);
    $maxStar = 5 - $star;
    for ($i = 1; $i <= $star; $i++) {
        $html .= '<li><a class="fa fa-star" href=""></a></li>';
    }
    for ($i = 1; $i <= $maxStar; $i++) {
        $html .= '<li><a class="fa fa-star-o" href=""></a></li>';
    }
    return '<div class="rating"><ul class="list-inline">' . $html . '</ul></div>';
}

function loadHtmlReviewStarNew($star = 1)
{
    $html = '';
    $star = number_unformat($star);
    $maxStar = 5 - $star;
    for ($i = 1; $i <= $star; $i++) {
        $html .= '<li><a class="fa fa-star" href=""></a></li>';
    }
    return '<div class="rating"><ul class="list-inline">' . $html . '</ul></div>';
}

function getFileType()
{
    $arFileType = ['png', 'jpeg', 'jpg', 'gif'];
    return $arFileType;
}

function loadTableCar()
{
    return ' <table id="table_car" class="table table-bordered table_car">
            <thead>
            <tr>
                <th class="text-center">' . lang('dt_stt') . '</th>
                <th class="text-center">' . lang('dt_image') . '</th>
                <th class="text-center">' . lang('dt_name_car') . '</th>
                <th class="text-center">' . lang('dt_number_car') . '</th>
                <th class="text-center">' . lang('dt_province_district') . '</th>
                <th class="text-center">' . lang('dt_customer') . '</th>
                <th class="text-center">' . lang('dt_company_car') . '</th>
                <th class="text-center">' . lang('dt_type_car') . '</th>
                <th class="text-center">' . lang('dt_model_car') . '</th>
                <th class="text-center">' . lang('dt_rent_cost') . '</th>
                <th class="text-center">' . lang('dt_service_car') . '</th>
                <th class="text-center">' . lang('dt_car_1') . '</th>
                <th class="text-center">' . lang('dt_car_2') . '</th>
                <th class="text-center">' . lang('dt_status') . '</th>
                <th class="text-center">' . lang('dt_actions') . '</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>';
}

function loadTableCarFavourite()
{
    return ' <table id="table_favourite_car" class="table table-bordered table_favourite_car">
            <thead>
            <tr>
                <th class="text-center">' . lang('dt_stt') . '</th>
                <th class="text-center">' . lang('dt_image') . '</th>
                <th class="text-center">' . lang('dt_name_car') . '</th>
                <th class="text-center">' . lang('dt_number_car') . '</th>
                <th class="text-center">' . lang('dt_province_district') . '</th>
                <th class="text-center">' . lang('dt_customer') . '</th>
                <th class="text-center">' . lang('dt_company_car') . '</th>
                <th class="text-center">' . lang('dt_type_car') . '</th>
                <th class="text-center">' . lang('dt_model_car') . '</th>
                <th class="text-center">' . lang('dt_rent_cost') . '</th>
                <th class="text-center">' . lang('dt_service_car') . '</th>
                <th class="text-center">' . lang('dt_car_1') . '</th>
                <th class="text-center">' . lang('dt_car_2') . '</th>
                <th class="text-center">' . lang('dt_status') . '</th>
                <th class="text-center">' . lang('dt_actions') . '</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>';
}

function getListTypeMotobike($id = 0)
{
    $data = [
        [
            'id' => 1,
            'name' => 'Trên 50cc',
        ],
        [
            'id' => 2,
            'name' => 'Dưới 50cc',
        ],
        [
            'id' => 3,
            'name' => 'Xe 3 bánh',
        ],
    ];
    if (!empty($id)) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            return $data[0]['name'];
        } else {
            return null;
        }
    } else {
        return $data;
    }
}

function getListBusiness($id = 0)
{
    $data = [
        [
            'id' => 1,
            'name' => 'Không kinh doanh',
        ],
        [
            'id' => 2,
            'name' => 'Kinh doanh',
        ],
    ];
    if (!empty($id)) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            return $data[0]['name'];
        } else {
            return null;
        }
    } else {
        return $data;
    }
}

function getListTypeCarInsurance($id = 0)
{
    $data = [
        [
            'id' => 1,
            'name' => 'Chở người',
        ],
        [
            'id' => 2,
            'name' => 'Pickup - Minivan',
        ],
        [
            'id' => 3,
            'name' => 'Chở hàng',
        ],
    ];
    if (!empty($id)) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            return $data[0]['name'];
        } else {
            return null;
        }
    } else {
        return $data;
    }
}

function getListGender($id = 0)
{
    $data = [
        [
            'id' => 1,
            'name' => 'Nam',
        ],
        [
            'id' => 2,
            'name' => 'Nữ',
        ],
        [
            'id' => 2,
            'name' => 'Khác',
        ],
    ];
    if (!empty($id)) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            return $data[0]['name'];
        } else {
            return null;
        }
    } else {
        return $data;
    }
}

function getListDrivingLiscense()
{
    return [
        [
            'id' => -1,
            'name' => 'Tất cả',
        ],
        [
            'id' => 1,
            'name' => 'Chưa xác thực',
        ],
        [
            'id' => 2,
            'name' => 'Đã xác thực',
        ],
        [
            'id' => 3,
            'name' => 'Không xác thực',
        ],
        [
            'id' => 4,
            'name' => 'Chưa có GPLX',
        ],
    ];
}

function getListStatusBusiness()
{
    return [
        [
            'id' => -1,
            'name' => 'Tất cả',
        ],
        [
            'id' => 1,
            'name' => 'Chưa xác thực',
        ],
        [
            'id' => 2,
            'name' => 'Đã xác thực',
        ],
        [
            'id' => 3,
            'name' => 'Không xác thực',
        ],
        [
            'id' => 4,
            'name' => 'Tài khoản doanh nghiệp',
        ],
        [
            'id' => 5,
            'name' => 'Tài khoản thường',
        ],
    ];
}

function getListPriceMonth()
{
    return [
        [
            'id' => 1,
            'name' => '1 tháng tới',
            'selected' => false,
        ],
        [
            'id' => 2,
            'name' => '2 tháng tới',
            'selected' => false,
        ],
        [
            'id' => 3,
            'name' => '3 tháng tới',
            'selected' => true,
        ],
        [
            'id' => 5,
            'name' => '5 tháng tới',
            'selected' => false,
        ],
    ];
}

function getListStatusCar()
{
    return [
        [
            'id' => 0,
            'name' => lang('dt_status_car_0'),
            'color' => '#989898',
        ],
        [
            'id' => 1,
            'name' => lang('dt_status_car_1'),
            'color' => '#81c868',
        ],
        [
            'id' => 2,
            'name' => lang('dt_status_car_2'),
            'color' => '#f05050',
        ],
        [
            'id' => 3,
            'name' => lang('dt_status_car_3'),
            'color' => '#46b0b9',
        ],
    ];
}

function getValueStatusCar($id, $type = 'name')
{
    $option[0]['name'] = lang('dt_status_car_0');
    $option[1]['name'] = lang('dt_status_car_1');
    $option[2]['name'] = lang('dt_status_car_2');
    $option[3]['name'] = lang('dt_status_car_3');
    $option[0]['color'] = '#989898';
    $option[1]['color'] = '#81c868';
    $option[2]['color'] = '#f05050';
    $option[3]['color'] = '#46b0b9';
    return $option[$id][$type];
}

function getListStatusTransactionDriver($province = false)
{
    if ($province) {
        return [
            [
                'id' => 0,
                'name' => 'Đang tìm tài xế',
                'color' => '#989898',
                'index' => 0,
            ],
            [
                'id' => 1,
                'name' => 'Đã có tài xế',
                'color' => '#5d9cec',
                'index' => 1,
            ],
            [
                'id' => 2,
                'name' => lang('dt_status_transaction_driver_2'),
                'color' => '#46b0b9',
                'index' => 2,
            ],
            [
                'id' => 3,
                'name' => lang('dt_status_transaction_driver_3'),
                'color' => '#81c868',
                'index' => 3,
            ],
            [
                'id' => 4,
                'name' => lang('dt_status_transaction_driver_4'),
                'color' => '#f05050',
                'index' => 4,
            ],
            [
                'id' => 5,
                'name' => lang('dt_status_transaction_driver_5'),
                'color' => '#f05050',
                'index' => 5,
            ],
            [
                'id' => 6,
                'name' => lang('dt_status_transaction_driver_6'),
                'color' => '#f05050',
                'index' => 6,
            ],
        ];
    } else {
        return [
            [
                'id' => 0,
                'name' => lang('dt_status_transaction_driver_0'),
                'color' => '#989898',
                'index' => 0,
            ],
            [
                'id' => 1,
                'name' => lang('dt_status_transaction_driver_1'),
                'color' => '#5d9cec',
                'index' => 1,
            ],
            [
                'id' => 2,
                'name' => lang('dt_status_transaction_driver_2'),
                'color' => '#46b0b9',
                'index' => 2,
            ],
            [
                'id' => 3,
                'name' => lang('dt_status_transaction_driver_3'),
                'color' => '#81c868',
                'index' => 3,
            ],
            [
                'id' => 4,
                'name' => lang('dt_status_transaction_driver_4'),
                'color' => '#f05050',
                'index' => 4,
            ],
            [
                'id' => 5,
                'name' => lang('dt_status_transaction_driver_5'),
                'color' => '#f05050',
                'index' => 5,
            ],
            [
                'id' => 6,
                'name' => lang('dt_status_transaction_driver_6'),
                'color' => '#f05050',
                'index' => 6,
            ],
        ];
    }
}

function getValueStatusTransactionDriver($id, $type = 'name', $province = false)
{
    if ($province) {
        $option[0]['name'] = 'Đang tìm tài xế';
        $option[1]['name'] = 'Đã có tài xế';
    } else {
        $option[0]['name'] = lang('dt_status_transaction_driver_0');
        $option[1]['name'] = lang('dt_status_transaction_driver_1');
    }
    $option[2]['name'] = lang('dt_status_transaction_driver_2');
    $option[3]['name'] = lang('dt_status_transaction_driver_3');
    $option[4]['name'] = lang('dt_status_transaction_driver_4');
    $option[5]['name'] = lang('dt_status_transaction_driver_5');
    $option[6]['name'] = lang('dt_status_transaction_driver_6');
    $option[0]['color'] = '#989898';
    $option[1]['color'] = '#5d9cec';
    $option[2]['color'] = '#46b0b9';
    $option[3]['color'] = '#81c868';
    $option[4]['color'] = '#f05050';
    $option[5]['color'] = '#f05050';
    $option[6]['color'] = '#f05050';
    $option[0]['index'] = 0;
    $option[1]['index'] = 1;
    $option[2]['index'] = 2;
    $option[3]['index'] = 3;
    $option[4]['index'] = 4;
    $option[5]['index'] = 5;
    $option[6]['index'] = 6;
    return $option[$id][$type];
}

function getListTypeCar($province = false)
{
    if ($province) {
        return [
            [
                'id' => 1,
                'name' => lang('dt_self_driving_car'),
                'color' => '#46b0b9'
            ],
            [
                'id' => 2,
                'name' => lang('dt_talented__car'),
                'color' => '#5d9cec'
            ],
            [
                'id' => 3,
                'name' => lang('dt_car_3'),
                'color' => '#ffbd4a'
            ],
            [
                'id' => 4,
                'name' => lang('dt_car_4'),
                'color' => '#f05050'
            ]
        ];
    } else {
        return [
            [
                'id' => 1,
                'name' => lang('dt_self_driving_car'),
                'color' => '#46b0b9'
            ],
            [
                'id' => 2,
                'name' => lang('dt_talented__car'),
                'color' => '#5d9cec'
            ],
            [
                'id' => 3,
                'name' => lang('dt_car_3'),
                'color' => '#ffbd4a'
            ]
        ];
    }
}

function getValueTypeCar($id, $type = 'name')
{
    $option[1]['name'] = lang('dt_self_driving_car');
    $option[2]['name'] = lang('dt_talented__car');
    $option[3]['name'] = lang('dt_car_3');
    $option[4]['name'] = lang('dt_car_4');
    $option[1]['color'] = '#46b0b9';
    $option[2]['color'] = '#5d9cec';
    $option[3]['color'] = '#ffbd4a';
    $option[4]['color'] = '#f05050';
    return $option[$id][$type];
}

function getListTransmission()
{
    return [
        [
            'id' => 2,
            'name' => lang('dt_number_automaic'),
        ],
        [
            'id' => 1,
            'name' => lang('dt_number_manual'),
        ]
    ];
}

function getValueTransmission($id)
{
    $option[1] = lang('dt_number_manual');
    $option[2] = lang('dt_number_automaic');
    return $option[$id];
}

function getListTypeFuel()
{
    return [
        [
            'id' => 1,
            'name' => lang('dt_xang'),
        ],
        [
            'id' => 2,
            'name' => lang('dt_dau'),
        ],
        [
            'id' => 3,
            'name' => lang('dt_dien'),
        ]
    ];
}

function getValueTypeFuel($id)
{
    $option[1] = lang('dt_xang');
    $option[2] = lang('dt_dau');
    $option[3] = lang('dt_dien');
    return $option[$id];
}

//Hình ảnh mặc định
function imgDefault()
{
    return 'admin/assets/images/users/avatar-1.jpg';
}

function imgCameraDefault()
{
    return 'admin/assets/images/not_available.jpg';
}

function get_option($field = '')
{
    $app = new App();
    return $app->get_option($field);
    // $data = DB::table('tbl_options')->where('name', $field)->get()->first();
    // return !empty($data) ? $data->value : null;
}

function to_sql_date($date, $datetime = false)
{
    if (strpos($date, ' ') === false) {
        $date .= ' 00:00:00';
    }
    $from_format = get_current_date_format(true);
    $date = _simplify_date_fix($date, $from_format);
    $timestamp = strtotime($date);
    if (!empty($datetime)) {
        $mydate = strftime('%Y-%m-%d %H:%M:%S', $timestamp);
    } else {
        $mydate = strftime('%Y-%m-%d', $timestamp);
    }
    return $mydate;
}

function _simplify_date_fix($date, $from_format)
{
    if ($from_format == 'd/m/Y') {
        $date = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$2-$1 $4', $date);
    } elseif ($from_format == 'm/d/Y') {
        $date = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    } elseif ($from_format == 'm.d.Y') {
        $date = preg_replace('#(\d{2}).(\d{2}).(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    } elseif ($from_format == 'm-d-Y') {
        $date = preg_replace('#(\d{2})-(\d{2})-(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    }
    return $date;
}

function number_unformat($number, $force_number = true)
{
    $decimal_separator = get_option('decimal_separator');
    $thousand_separator = get_option('thousand_separator');
    if ($force_number) {
        $number = preg_replace('/^[^\d]+/', '', $number);
    } elseif (preg_match('/^[^\d]+/', $number)) {
        return false;
    }
    $dec_point = $decimal_separator;
    $thousands_sep = $thousand_separator;
    $type = (strpos($number, $dec_point) === false) ? 'int' : 'float';
    $number = str_replace([
        $dec_point,
        $thousands_sep,
    ], [
        '.',
        '',
    ], $number);
    settype($number, $type);
    return $number;
}

function get_staff_user_id($staff_id = 0)
{
    if (!empty($staff_id)) {
        $user = Cache::remember('user-info', 3600, function ($staff_id) {
            return \App\Models\User::find($staff_id);
        });
    } else {
        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
    }
    if (empty($user)) {
        return 0;
    } else {
        return $user->id;
    }
}

function getDiffForHumans($date = '')
{
    if (empty($date)) {
        return null;
    }
    Carbon::setLocale('vi');
    $dt = new Carbon($date);
    $now = Carbon::now();
    return $dt->diffForHumans($now);
}

if (!function_exists('convertDate')) {
    function convertDate($date_work)
    {
        $date = '';
        switch (true) {
            case $date_work == 'Mon':
                $date = 'Monday';
                break;
            case $date_work == 'Tue':
                $date = 'Tuesday';
                break;
            case $date_work == 'Wed':
                $date = 'Wednesday';
                break;
            case $date_work == 'Thu':
                $date = 'Thursday';
                break;
            case $date_work == 'Fri':
                $date = 'Friday';
                break;
            case $date_work == 'Sat':
                $date = 'Saturday';
                break;
            case $date_work == 'Sun':
                $date = 'Sunday';
                break;
            default:
                $date = $date_work;
                break;
        }
        return $date;
    }
}
function get_current_date_format($php = false)
{
    $format = "d/m/Y|%d/%m/%Y";
    $format = explode('|', $format);
    if ($php == false) {
        return $format[1];
    }
    return $format[0];
}

function _dt($date, $is_timesheet = false)
{
    $original = $date;
    $time_format = 24;
    if ($date == '' || is_null($date) || $date == '0000-00-00 00:00:00') {
        return '';
    }
    $format = get_current_date_format();
    $hour12 = ($time_format == 24 ? false : true);
    if ($is_timesheet == false) {
        $date = strtotime($date);
    }
    if ($hour12 == false) {
        $tf = '%H:%M:%S';
        if ($is_timesheet == true) {
            $tf = '%H:%M';
        }
        $date = strftime($format . ' ' . $tf, $date);
    } else {
        $date = date(get_current_date_format(true) . ' g:i A', $date);
    }
    return $date;
}

function _dt_new($date)
{
    $original = $date;
    $time_format = 24;
    if ($date == '' || is_null($date) || $date == '0000-00-00 00:00:00') {
        return '';
    }
    $format = get_current_date_format();
    $hour12 = ($time_format == 24 ? false : true);
    $date = strtotime($date);
    if ($hour12 == false) {
        $tf = '%H:%M';
        $dayname = convertDate(date('D', $date));
        $date = strftime($tf . ' - ' . $dayname . ', ' . $format, $date);
    } else {
        $date = date(get_current_date_format(true) . ' g:i A', $date);
    }
    return $date;
}

function _dthuan($date, $is_timesheet = false)
{
    $original = $date;
    $time_format = 24;
    if ($date == '' || is_null($date) || $date == '0000-00-00') {
        return '';
    }
    $format = get_current_date_format();
    $hour12 = ($time_format == 24 ? false : true);
    if ($is_timesheet == false) {
        $date = strtotime($date);
    }
    if ($hour12 == false) {
        $tf = '';
        if ($is_timesheet == true) {
            $tf = '';
        }
        $date = strftime($format . ' ' . $tf, $date);
    } else {
        $date = date(get_current_date_format(true) . ' g:i A', $date);
    }
    $date = trim($date);
    return $date;
}

function formatDecimalMoney($number, $decimals = null)
{
    $decimals_money = get_option('decimals_money');
    if (!is_numeric($number)) {
        return null;
    }
    if (!$decimals) {
        $decimals = $decimals_money;
    }
    return number_format($number, $decimals, '.', '');
}

function formatSAC($num)
{
    $pos = strpos((string)$num, ".");
    if ($pos === false) {
        $decimalpart = "00";
    } else {
        $decimalpart = substr($num, $pos + 1, 2);
        $num = substr($num, 0, $pos);
    }
    if (strlen($num) > 3 & strlen($num) <= 12) {
        $last3digits = substr($num, -3);
        $numexceptlastdigits = substr($num, 0, -3);
        $formatted = $numexceptlastdigits;
        $stringtoreturn = $formatted . "," . $last3digits . "." . $decimalpart;
    } elseif (strlen($num) <= 3) {
        $stringtoreturn = $num . "." . $decimalpart;
    } elseif (strlen($num) > 12) {
        $stringtoreturn = number_format($num, 2);
    }
    if (substr($stringtoreturn, 0, 2) == "-,") {
        $stringtoreturn = "-" . substr($stringtoreturn, 2);
    }
    return $stringtoreturn;
}

function is_decimal($val)
{
    return is_numeric($val) && floor($val) != $val;
}

function formatMoney($number, $decimals = null)
{
    $decimals_money = get_option('decimals_money');
    $sac = 0;
    $thousands_sep = get_option('thousands_sep');
    $decimals_sep = get_option('decimals_sep');
    if ($sac) {
        return formatSAC(formatDecimalMoney($number));
    }
    if (!$decimals) {
        $decimals = $decimals_money;
    }
    if (!is_decimal($number)) {
        $decimals = 0;
    }
    $ts = $thousands_sep == '0' ? ' ' : $thousands_sep;
    $ds = $decimals_sep;
    return number_format($number, $decimals, $ds, $ts);
}

function formatNumber($number, $decimals = null)
{
    $decimals_number = get_option('decimals_number');
    $thousands_sep = get_option('thousands_sep');
    $decimals_sep = get_option('decimals_sep');
    if (!$decimals) {
        $decimals = $decimals_number;
    }
    $ts = $thousands_sep == '0' ? ' ' : $thousands_sep;
    $ds = $decimals_sep;
    return number_format($number, $decimals, $ds, $ts);
}

function formatNumberStar($number, $decimals = null)
{
    $decimals_number = 1;
    $thousands_sep = get_option('thousands_sep');
    $decimals_sep = get_option('decimals_sep');
    if (!$decimals) {
        $decimals = $decimals_number;
    }
    $ts = $thousands_sep == '0' ? ' ' : $thousands_sep;
    $ds = $decimals_sep;
    return number_format($number, $decimals, $ds, $ts);
}

function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y')
{
    $dates = array();
    $first = explode('/', $first);
    $last = explode('/', $last);
    if (empty($first) || empty($last)) {
        return null;
    }
    $current = mktime(0, 0, 0, $first[1], $first[0], $first[2]);
    $last = mktime(0, 0, 0, $last[1], $last[0], $last[2]);
    while ($current <= $last) {
        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }
    return $dates;
}

function createDateRangeArray($month, $year)
{
    $date_array = array();
    $start_date = mktime(0, 0, 0, $month, 1, $year);
    $end_date = mktime(0, 0, 0, $month + 1, 0, $year);
    while ($start_date <= $end_date) {
        $date_array[] = date('Y-m-d', $start_date);
        $start_date = strtotime('+1 day', $start_date);
    }
    return $date_array;
}

if (!function_exists('convertDateNew')) {
    function convertDateNew($date_work)
    {
        $date = '';
        switch (true) {
            case $date_work == 'Mon':
                $date = 'T2';
                break;
            case $date_work == 'Tue':
                $date = 'T3';
                break;
            case $date_work == 'Wed':
                $date = 'T4';
                break;
            case $date_work == 'Thu':
                $date = 'T5';
                break;
            case $date_work == 'Fri':
                $date = 'T6';
                break;
            case $date_work == 'Sat':
                $date = 'T7';
                break;
            case $date_work == 'Sun':
                $date = 'CN';
                break;
            default:
                $date = $date_work;
                break;
        }
        return $date;
    }
}
function year2array($year)
{
    $res = $year >= 1970;
    if ($res) {
        date_default_timezone_set(date_default_timezone_get());
        $dt = strtotime("-1 day", strtotime("$year-01-01 00:00:00"));
        $res = array();
        $week = array_fill(1, 7, false);
        $last_month = 1;
        $w = 1;
        do {
            $dt = strtotime('+1 day', $dt);
            $dta = getdate($dt);
            $wday = $dta['wday'] == 0 ? 7 : $dta['wday'];
            if (($dta['mon'] != $last_month) || ($wday == 1)) {
                if ($week[1] || $week[7]) {
                    $res[$last_month][] = $week;
                }
                $week = array_fill(1, 7, false);
                $last_month = $dta['mon'];
            }
            $week[$wday] = $dta['mday'];
        } while ($dta['year'] == $year);
    }
    return $res;
}

function month2table($month, $calendar_array, $year, $dtDate = array(), $type = 0, $type_car = 1)
{
    $month_new = $month;
    if ($month < 10) {
        $month = (int)($month);
    }
    $dateNow = date('Y-m-d');
    if ($type_car == 1) {
        $classTable = 'tb-price';
        $dayName = 'day';
        $priceName = 'price';
        $statusName = 'status';
    } else {
        $classTable = 'tb-price-talent';
        $dayName = 'day_talent';
        $priceName = 'price_talent';
        $statusName = 'status_talent';
    }
    $res = '<table class="tb-price-month ' . $classTable . '">
            <thead>
                 <tr>
                    <td colspan="7">Tháng ' . $month . ' ' . $year . '</td>
                 </tr>
            </thead>
            <tr class="days">
                <td>T2</td>
                <td>T3</td>
                <td>T4</td>
                <td>T5</td>
                <td>T6</td>
                <td>T7</td>
                <td>CN</td>
                </tr>';
    foreach ($calendar_array[$month] as $week) {
        $res .= '<tr>';
        foreach ($week as $day) {
            $price = -1;
            $date = '';
            $dateCheck = '';
            $status = 0;
            $id = 0;
            if (!empty($dtDate)) {
                foreach ($dtDate as $k => $v) {
                    if ($v->day == $day) {
                        $price = $v->price;
                        $date = _dthuan($v->date);
                        $dateCheck = ($v->date);
                        $status = $v->status;
                        $id = $v->id;
                    }
                }
            }
            if (empty($day)) {
                $res .= '<td></td>';
            } else {
                $res .= '<td class="normal status_' . $status . ' ' . (strtotime($dateCheck) < strtotime(
                        $dateNow
                    ) ? 'day_old' : '') . ' month_' . $month_new . '">
                    ' . ($type == 0 ? '<a class="dt-modal2 update" data-id="' . $id . '" href="admin/car/updatePrice/' . $id . '/' . $type_car . '">' : '<a class="update" data-id="' . $id . '" onclick="updateStatus(' . $id . ',' . $type_car . ')" >') . '
                    <input type="hidden" name="' . $dayName . '[' . $month_new . '][]" value="' . $date . '">
                    <input type="hidden" name="' . $priceName . '[' . $month_new . '][' . $date . ']" value="' . $price . '">
                    <input type="hidden" name="' . $statusName . '[' . $month_new . '][' . $date . ']" value="' . $status . '">
                    <span class="day">' . $day . '</span><br><span class="price_db status_' . $status . '">' . ($price != -1 ? formatMoney(
                        $price
                    ) : '') . '</span>
                    </a>
                </td>';
            }
        }
        $res .= '</tr>';
    }
    $res .= '</table>';
    return $res;
}

//function that checks if a holiday lands on saturday/sunday and so we can move them to a friday/monday respectively
function getObservedDate($holidayDate)
{
    $dayofweek = date("w", strtotime($holidayDate));
    if ($dayofweek == 6) {
        $holidayDate = date('m/d/Y', strtotime("$holidayDate - 1 days"));
    } //saturday moves to friday
    elseif ($dayofweek == 0) {
        $holidayDate = date('m/d/Y', strtotime("$holidayDate + 1 days"));
    }  //sunday moves monday
    return $holidayDate;
}

//function that calculates the holidays for any given year
function getFederalHolidaysForYear($year)
{
    $NY = getObservedDate(date('m/d/Y', strtotime("1/1/$year"))); //new years day
    $MLK = getObservedDate(date('m/d/Y', strtotime("third monday of january $year")));  //martin luther king day
    $PD = getObservedDate(date('m/d/Y', strtotime("third monday of february $year")));; //presidents day
    $MDay = getObservedDate(date('m/d/Y', strtotime("last monday of May $year"))); //memorial day
    $IDay = getObservedDate(date('m/d/Y', strtotime("7/4/$year")));  // independence day
    $LD = getObservedDate(date('m/d/Y', strtotime("first monday of september $year"))); //labor day
    $VD = getObservedDate(date('m/d/Y', strtotime("11/11/$year"))); //veterans day
    $ColD = getObservedDate(date('m/d/Y', strtotime("second monday of october $year"))); //columbus day
    $TG = getObservedDate(date('m/d/Y', strtotime("last thursday of november $year"))); // thanksgiving
    $CD = getObservedDate(date('m/d/Y', strtotime("12/25/$year")));  //christmas day
    $nonWorkingDays = array();
    array_push($nonWorkingDays, $NY, $MLK, $PD, $MDay, $IDay, $LD, $ColD, $VD, $TG, $CD);
    return $nonWorkingDays;
}

function dateWord()
{
    return [
        [
            'id' => 'CN',
            'name' => 'CN'
        ],
        [
            'id' => 'T2',
            'name' => 'T2'
        ],
        [
            'id' => 'T3',
            'name' => 'T3'
        ],
        [
            'id' => 'T4',
            'name' => 'T4'
        ],
        [
            'id' => 'T5',
            'name' => 'T5'
        ],
        [
            'id' => 'T6',
            'name' => 'T6'
        ],
        [
            'id' => 'T7',
            'name' => 'T7'
        ],
    ];
}

if (!function_exists('getMonth')) {
    function getMonth()
    {
        $option[''] = '';
        $option['01'] = 'Tháng 1';
        $option['02'] = 'Tháng 2';
        $option['03'] = 'Tháng 3';
        $option['04'] = 'Tháng 4';
        $option['05'] = 'Tháng 5';
        $option['06'] = 'Tháng 6';
        $option['07'] = 'Tháng 7';
        $option['08'] = 'Tháng 8';
        $option['09'] = 'Tháng 9';
        $option['10'] = 'Tháng 10';
        $option['11'] = 'Tháng 11';
        $option['12'] = 'Tháng 12';
        return $option;
    }
}

if (!function_exists('getYear')) {
    function getYear()
    {
        $year = [];
        $year[''] = '';
        for ($i = -1; $i < 5; $i++) {
            $date = date('Y', strtotime(date('Y') . ' -' . $i . ' year'));
            $year[$date] = $date;
        }
        return $year;
    }
}
function loadWeekToMonthYear($month, $year)
{
    $startDate = new DateTime("$year-$month-01");
    $endDate = clone $startDate;
    $endDate->modify('last day of this month');
    $dates = [];
    while ($startDate <= $endDate) {
        $dates[] = $startDate->format('Y-m-d');
        $startDate->modify('+1 day');
    }
    return $dates;
}

if (!function_exists('getReference')) {
    function getReference($field)
    {
        $q = DB::table('tbl_order_ref')->where('ref_id', 1)->first();
        if (!empty($q)) {
            $ref = $q;
            switch ($field) {
                case 'transaction':
                    $prefix = 'ĐH';
                    break;
                case 'payment':
                    $prefix = 'PT';
                    break;
                case 'driver_ticket':
                    $prefix = 'FD';
                    break;
                case 'request_withdraw_money':
                    $prefix = 'YCRT';
                    break;
                case 'transfer_money':
                    $prefix = 'CT';
                    break;
                case 'transfer_package':
                    $prefix = 'CG';
                    break;
                case 'transaction_driver':
                    $prefix = 'GDTX';
                    break;
                case 'payment_driver':
                    $prefix = 'PTTX';
                    break;
                case 'pay_slip':
                    $prefix = 'PC';
                    break;
                case 'client':
                    $prefix = 'KH';
                    break;
                case 'driver':
                    $prefix = 'TX';
                    break;
                case 'contract_transaction':
                    $prefix = 'HĐTX';
                    break;
                case 'handover_record':
                    $prefix = 'BBBG';
                    break;
                case 'review':
                    $prefix = 'RV';
                    break;
                case 'review_transaction':
                    $prefix = 'RVT';
                    break;
                case 'challengme':
                    $prefix = 'CM';
                    break;
                case 'transaction_payment':
                    $prefix = 'PT';
                    break;
                case 'invoice':
                    $prefix = 'HD';
                    break;
                default:
                    $prefix = '';
            }
            $separator = get_option('separator');
            $format_date_prefix = get_option('format_date_prefix');
            $ref_no = (!empty($prefix)) ? $prefix . "$separator" : '';
            $ref_no .= date("$format_date_prefix") . sprintf("%02s", $ref->{$field});
            return $ref_no;
        }
        return false;
    }
}

if (!function_exists('updateReference')) {
    function updateReference($field)
    {
        $q = DB::table('tbl_order_ref')->where('ref_id', 1)->first();
        if (!empty($q)) {
            $ref = $q;
            DB::table('tbl_order_ref')->where('ref_id', 1)->update([$field => $ref->{$field} + 1]);
            return true;
        }
        return false;
    }
}
function optionHour($hour_start = '00:00', $hour_end = '23:30', $defaultValue = -1)
{
    $hour_start = strtotime(date('Y-m-d') . ' ' . $hour_start);
    $hour_end = strtotime(date('Y-m-d') . ' ' . $hour_end);
    $arrHour = [];
    $value = 0;
    for ($i = ($hour_start); $i <= $hour_end; $i = $i_new) {
        $date_new = strftime("%Y-%m-%d %H:%M:%S", $i);
        if ($defaultValue == $value) {
            return strftime("%H:%M", strtotime($date_new));
        }
        $arrHour[] = [
            'value' => $value,
            'hour' => date("h:i a", strtotime($date_new)),
            'hour_new' => strftime("%H:%M", strtotime($date_new)),
        ];
        $i = strftime("%Y-%m-%d %H:%M:%S", $i);
        $i_new = strtotime($i . " +30 minutes");
        $value += 1800;
    }
    return $arrHour;
}

function GetCurlData($service_url = "", $data_string = [])
{
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_URL, $service_url);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPGET, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('application/json'));
    if (!empty($data_string)) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    }
    $curl_response = curl_exec($curl);
    curl_close($curl);
    return $curl_response;
}

if (!function_exists('menuHelper')) {
    function menuHelper()
    {
        $menu = [
            [
                'id' => 'Dashboard',
                'name' => 'Dashboard',
                'link' => 'admin/dashboard',
                'class' => 'dashboad',
                'image' => 'ti-dashboard',
                'child' => []
            ],
            [
                'id' => 'category',
                'name' => lang('dt_category'),
                'link' => '',
                'class' => 'danh_muc',
                'image' => 'admin/assets/images/icon_menu/danh_muc.png',
                'child' => [
                    [
                        'id' => 'department',
                        'name' => lang('dt_department'),
                        'link' => 'admin/department/list',
                        'image' => '',
                    ],
                    [
                        'id' => 'role',
                        'name' => lang('dt_role'),
                        'link' => 'admin/role/list',
                        'image' => '',
                    ],
                    [
                        'id' => 'permission',
                        'name' => lang('dt_permission'),
                        'link' => 'admin/permission/list',
                        'image' => '',
                    ],
                    [
                        'id' => 'payment_mode',
                        'name' => lang('dt_payment_mode'),
                        'link' => 'admin/payment_mode/list',
                        'image' => '',
                    ],
                ]
            ],
            [
                'id' => 'user',
                'name' => lang('dt_user'),
                'link' => 'admin/user/list',
                'class' => 'nhan_vien',
                'image' => 'admin/assets/images/icon_menu/nhan_vien.png',
                'child' => []
            ],
            [
                'id' => 'clients',
                'name' => lang('manager clients'),
                'link' => 'admin/clients/list',
                'class' => 'nguoi_dung_app',
                'image' => 'admin/assets/images/icon_menu/nguoi_dung_app.png',
                'child' => [],
            ],
            [
                'id' => 'settings',
                'name' => lang('settings'),
                'link' => 'admin/settings',
                'class' => 'cai_dat',
                'image' => 'admin/assets/images/icon_menu/cai_dat.png',
                'child' => []
            ],
        ];
        return $menu;
    }
}

if (!function_exists('typePolicy')) {
    function typePolicy($key = false)
    {
        $option[1] = [
            'id' => 1,
            'name' => 'Xe tự lái',
        ];
        $option[2] = [
            'id' => 2,
            'name' => 'Tài xế xe hộ',
        ];
        if ($key !== false) {
            return $option[$key];
        } else {
            return $option;
        }
    }
}
function getListDay()
{
    return [
        [
            'id' => 'Mon',
            'name' => 'Thứ 2'
        ],
        [
            'id' => 'Tue',
            'name' => 'Thứ 3'
        ],
        [
            'id' => 'Wed',
            'name' => 'Thứ 4'
        ],
        [
            'id' => 'Thu',
            'name' => 'Thứ 5'
        ],
        [
            'id' => 'Fri',
            'name' => 'Thứ 6'
        ],
        [
            'id' => 'Sat',
            'name' => 'Thứ 7'
        ],
        [
            'id' => 'Sun',
            'name' => 'Chủ nhật'
        ],
    ];
}

if (!function_exists('recursiveCategorySetupQa')) {
    function recursiveCategorySetupQa($id = 0, $check = false, &$output = null, $parent_id = 0, $indent = null)
    {
        $query = DB::table('tbl_category_setup_qa')->where(function ($query) use ($parent_id) {
            $query->where('parent_id', $parent_id);
        })->orderByRaw('parent_id asc')->get();
        foreach ($query as $key => $item) {
            if ($item->parent_id == $parent_id) {
                $disabled = '';
                if ($check) {
                    if ($parent_id == 0) {
                        $disabled = 'disabled';
                    }
                }
                if ($item->id == $id && $id != 0) {
                    continue;
                }
                $output .= '<option ' . $disabled . '  value="' . $item->id . '">' . $indent . '➪ ' . $item->name . "</option>";
                recursiveCategorySetupQa($id, $check, $output, $item->id, $indent . "&nbsp;&nbsp;&nbsp;&nbsp;");
            }
        }
        return $output;
    }
}

if (!function_exists('recursiveSetupQa')) {
    function recursiveSetupQa($id = 0, &$output = null, $parent_id = 0, $indent = null)
    {
        $query = DB::table('tbl_setup_qa')->where(function ($query) use ($parent_id) {
            $query->where('parent_id', $parent_id);
        })->orderByRaw('parent_id asc')->get();
        foreach ($query as $key => $item) {
            if ($item->parent_id == $parent_id) {
                $disabled = '';
                if ($item->id == $id && $id != 0) {
                    continue;
                }
                $output .= '<option ' . $disabled . '  value="' . $item->id . '">' . $indent . '➪ ' . $item->name . "</option>";
                recursiveSetupQa($id, $output, $item->id, $indent . "&nbsp;&nbsp;&nbsp;&nbsp;");
            }
        }
        return $output;
    }
}

if (!function_exists('recursiveSetupQaFilter')) {
    function recursiveSetupQaFilter($id = 0, &$output = null, $parent_id = 0, $indent = null)
    {
        $query = DB::table('tbl_setup_qa')->where(function ($query) use ($parent_id, $id) {
            $query->where('category_setup_qa_id', $id);
        })->orderByRaw('category_setup_qa_id asc')->get();
        foreach ($query as $key => $item) {
            if ($item->parent_id == $parent_id) {
                $disabled = '';
                if ($item->id == $id && $id != 0) {
                    continue;
                }
                $output .= '<option ' . $disabled . '  value="' . $item->id . '">' . $indent . '➪ ' . $item->name . "</option>";
                recursiveSetupQaFilter($id, $output, $item->id, $indent . "&nbsp;&nbsp;&nbsp;&nbsp;");
            }
        }
        return $output;
    }
}

function get_parent($id)
{
    $arrIdParent = array();
    get_parent_id_helper($id, $arrIdParent);
    if ($arrIdParent) {
        $arrIdParent = array_unique($arrIdParent);
    }
    krsort($arrIdParent);
    $htmlParent = implode(' -> ', $arrIdParent);
    return $htmlParent;
}

function get_parent_id_helper($child_id = '', &$result = array(), $dem = 0)
{
    $dem++;
    $dtCategory = \App\Models\CategorySetupQa::find($child_id);
    $parent_id = $dtCategory->parent_id;
    $query = DB::table('tbl_category_setup_qa')->where(function ($query) use ($parent_id) {
        $query->where('id', $parent_id);
    })->get();
    if ($dem == 100) {
        return $query;
    }
    foreach ($query as $value) {
        array_push($result, $value->name);
        get_parent_id_helper($value->id, $result, $dem);
    }
}

function getSslPage($url)
{
    $ch = curl_init();
    $headr = array();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
    curl_setopt($ch, CURLOPT_URL, $url); // get the url contents
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'spider');
    $data = curl_exec($ch); // execute curl request
    curl_close($ch);
    return $data;
}

function show_rate_usd_to_vnd($filter = [])
{
    $host = "https://www.vietcombank.com.vn/api/exchangerates?date";
    $json_string = getSslPage($host);
    $result_array = json_decode($json_string, true);
    $listCurrent = [];
    $i = 0;
    $url_default = 'https://www.vietcombank.com.vn';
    if (!empty($result_array['Data'])) {
        foreach ($result_array['Data'] as $country_curency) {
            $code = $country_curency['currencyCode'];
            $name = trim($country_curency['currencyName']);
            $sell = $country_curency['sell'];
            $cash = $country_curency['cash'];
            $transfer = $country_curency['transfer'];
            $icon = $country_curency['icon'];
            $listCurrent[$i] = [
                'code' => $code,
                'name' => $name,
                'cash' => $cash,
                'transfer' => $transfer,
                'sell' => $sell,
                'icon' => $url_default . $icon,
            ];
            //            $countryCurency = CountryCurrency::where('code',$code)->first();
            //            if (!empty($countryCurency)){
            //                $countryCurency->name = $name;
            //                $countryCurency->save();
            //            } else {
            //                $countryCurency = new CountryCurrency();
            //                $countryCurency->code = $code;
            //                $countryCurency->name = $name;
            //                $countryCurency->save();
            //            }
            $i++;
        }
    }
    $listCurrentNew = [];
    if (!empty($listCurrent)) {
        foreach ($listCurrent as $key => $value) {
            if (in_array($value['code'], $filter)) {
                $listCurrentNew[] = $value;
            }
        }
    }
    return $listCurrentNew;
}

function generateRandomStringOld($id = '', $length = 8)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $keyRand = $characters[rand(0, $charactersLength - 1)];
        $randomString .= $keyRand; // Lấy ký tự ngẫu nhiên
    }
    return $randomString;
}

function generateRandomStringOldVs1($id, $length)
{
    // Chuyển số thành chuỗi để xử lý từng ký tự
    $numberString = strval($id);
    // Tạo chuỗi chứa các ký tự chữ cái ngẫu nhiên
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    // Duyệt qua các vị trí trong chuỗi
    for ($i = 0; $i < ($length - 1); $i++) {
        if (isset($numberString[$i]) && is_numeric($numberString[$i])) {
            // Giữ nguyên chữ số tại vị trí nếu có trong số ban đầu
            $randomString .= $numberString[$i];
        } else {
            // Thêm ký tự ngẫu nhiên vào vị trí còn lại
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
    }
    return $characters[rand(0, strlen($characters) - 1)] . $randomString;
}

function extractCoordinates($url)
{
    // Biểu thức chính quy để tìm tọa độ trong URL
    $pattern = '/@(-?\d+\.\d+),(-?\d+\.\d+)/';
    preg_match("/\/maps\/place\/([^\/@]+)/", $url, $place);
    $data_place = null;
    if (isset($place[1])) {
        // Thay thế dấu '+' bằng khoảng trắng
        $data_place = $place[1];
    }
    // Kiểm tra xem URL có chứa tọa độ không
    if (preg_match($pattern, $url, $matches)) {
        // $matches[1] chứa vĩ độ và $matches[2] chứa kinh độ
        return [
            'data_place' => $data_place,
            'latitude' => $matches[1],
            'longitude' => $matches[2]
        ];
    } else {
        // Trả về giá trị mặc định nếu không tìm thấy tọa độ
        return [
            'data_place' => $data_place,
            'latitude' => null,
            'longitude' => null
        ];
    }
}

function generateRandomString($id)
{
    $dtClient = Clients::find($id);
    $numberString = strval($id);
    $randomString = str_replace(' ', '', convert_vi_to_en($dtClient->fullname)) . '_' . $numberString;
    return $randomString;
}

function getDataTreeReferralLevel($id)
{
    $arrId = array();
    $arrID_child = array();
    get_childs_id_helper($id, $arrID_child);
    array_push($arrID_child, $id);
    if ($arrID_child) {
        $arrId = array_unique($arrID_child);
    }
    return $arrId;
}

function get_childs_id_helper($parent_id = '', &$result = array(), $dem = 0)
{
    $dem++;
    $items = \App\Models\ReferralLevel::where('parent_id', $parent_id)->get();
    if ($dem == 25) {
        return $items;
    }
    foreach ($items as $value) {
        array_push($result, $value->customer_id);
        get_childs_id_helper($value->customer_id, $result, $dem);
    }
}

function get_parent_id_referral_level($data, $parentId = 0, $level = 0)
{
    $array = array();
    foreach ($data as $key => $value) {
        if ($value->parent_id == $parentId) {
            $value->level = $level;
            $total_day = isset($value->customer->customer_class_day_seo) ? $value->customer->customer_class_day_seo->total : 0;
            $total_day_new = isset($value->customer->customer_class_day_seo) ? $value->customer->customer_class_day_seo->total : 0;
            $total = isset($value->customer->customer_class) ? $value->customer->customer_class->total : 0;
            $total_new = isset($value->customer->customer_class) ? $value->customer->transaction->where(
                'status',
                1
            )->sum('total') : 0;
            $total_affiliate = isset($value->customer->customer_class) ? $value->customer->transaction->where(
                'status',
                1
            )->sum('total') : 0;
            $total_reward = isset($value->customer->customer_class) ? $value->customer->customer_class->grand_total : 0;
            $total_reward_day = isset($value->customer->customer_class) ? $value->customer->customer_class->grand_total_day : 0;
            $children = get_parent_id_referral_level($data, $value->customer_id, $level + 1);
            if ($children) {
                $value->children = $children;
                foreach ($children as $child) {
                    //trên sơ đồ
                    $total_affiliate += isset($child->total_affiliate) ? $child->total_affiliate : 0;
                    //end
                    $total += isset($child->total) ? $child->total : 0;
                    $total_day += isset($child->total_day) ? $child->total_day : 0;
                }
            }
            $value->total_day = $total_day;
            $value->total_transaction_day = $total_day_new;
            $value->total_seo_day = ($value->total_day - $value->total_transaction_day) > 0 ? ($value->total_day - $value->total_transaction_day) : 0;
            $value->total = $total;
            $value->total_affiliate = $total_affiliate;
            $value->total_transaction = $total_new;
            // tổng total seo và gói đầu tư
            $value->total_seo = ($value->total_affiliate + $value->total_transaction - $value->total_transaction) > 0 ? ($value->total_affiliate + $value->total_transaction - $value->total_transaction) : 0;
            $value->total_reward = $total_reward;
            $value->total_reward_day = $total_reward_day;
            $value->total_old_reward = isset($value->customer->reward_day_profit_sum) ? $value->customer->reward_day_profit_sum->where(
                'type',
                1
            )->where('customer_id', $value->customer_id)->sum('balance') : 0;
            $value->class_customer = isset($value->customer->class_customer) ? $value->customer->class_customer->class_id : 0;
            $class_customer_new = isset($value->customer->class_customer) ? $value->customer->class_customer->class_id : 0;
            if ($class_customer_new == 4) {
                $value->class_customer_4 = 4;
                $value->class_customer_3 = 3;
                $value->class_customer_2 = 2;
                $value->class_customer_1 = 1;
            } elseif ($class_customer_new == 3) {
                $value->class_customer_4 = 0;
                $value->class_customer_3 = 3;
                $value->class_customer_2 = 2;
                $value->class_customer_1 = 1;
            } elseif ($class_customer_new == 2) {
                $value->class_customer_4 = 0;
                $value->class_customer_3 = 0;
                $value->class_customer_2 = 2;
                $value->class_customer_1 = 1;
            } elseif ($class_customer_new == 1) {
                $value->class_customer_4 = 0;
                $value->class_customer_3 = 0;
                $value->class_customer_2 = 0;
                $value->class_customer_1 = 1;
            } else {
                $value->class_customer_4 = 0;
                $value->class_customer_3 = 0;
                $value->class_customer_2 = 0;
                $value->class_customer_1 = 0;
            }
            $value->leader_customer = isset($value->customer->leadership_customer) ? $value->customer->leadership_customer->leader_ship_id : 0;
            $array[] = ($value);
        }
    }
    return $array;
}

function get_parent_id_referral_level_new($data, $parentId = 0, $level = 0, $branch = 0)
{
    $array = array();
    foreach ($data as $key => $value) {
        if ($value->parent_id == $parentId) {
            $value->level = $level;
            if ($level == 1) {
                $branch++;
                $value->branch = $branch;
            } elseif ($level > 1) {
                $value->branch = $branch;
            } elseif ($level == 0) {
                $value->branch = 0;
            }
            $array[] = ($value);
            $children = get_parent_id_referral_level_new($data, $value->customer_id, $level + 1, $branch);
            $array = array_merge($array, $children);
        }
    }
    return $array;
}

function get_level_role($id = 0)
{
    $items = \App\Models\ReferralLevel::where('customer_id', $id)->first();
    $lever = 1;
    if (!empty($items)) {
        if ($items->parent_id == 0) {
            $lever = 1;
        } else {
            $lever = 1;
            $parent = $items->parent_id;
            while ($parent > 0) {
                $ktr = \App\Models\ReferralLevel::where('customer_id', $parent)->first();
                $parent = $ktr->parent_id;
                $lever++;
            }
        }
    }
    return $lever;
}

function get_referral_count_level($data, $level)
{
    $parents = array_column($data, 'parent_id');
    array_multisort($parents, SORT_ASC, $data);
    $arrayCount = [];
    if ($level > 0) {
        for ($i = 1; $i <= $level; $i++) {
            $count = 0;
            $checkParent = -1;
            foreach ($data as $key => $value) {
                if ($i == 1) {
                    if ($value['level'] == $i) {
                        $count++;
                    }
                } else {
                    if ($value['level'] == $i && $checkParent != $value['parent_id']) {
                        $count++;
                        $checkParent = $value['parent_id'];
                    }
                }
            }
            $arrayCount[$i] = $count;
        }
    }
    return $arrayCount;
}

function get_parent_customer($customer_id)
{
    $arrStaffId = [];
    $arrID_child = [];
    get_parent_customer_id_helper($customer_id, $arrID_child);
    if ($arrID_child) {
        $arrStaffId = array_unique($arrID_child);
    }
    return $arrStaffId;
}

function get_parent_customer_id_helper($child_id = 0, &$result = array(), $dem = 0)
{
    $items = \App\Models\ReferralLevel::where('customer_id', $child_id)->get();
    if ($dem == 25) {
        return $items;
    }
    foreach ($items as $value) {
        array_push($result, $value->parent_id);
        get_parent_customer_id_helper($value->parent_id, $result, $dem);
    }
}

function replaceChar($string)
{
    return trim(strstr(preg_replace("/[a-zA-Z]/", "", $string), '_'), '_');
}

function send_telegram($message = "", $type = 1)
{
    if ($type == 1) {
        $chat_id = get_option('chat_id_in');
    } elseif ($type == 2) {
        $chat_id = get_option('chat_id');
    }
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.telegram.org/bot' . get_option(
                'token_key_tele'
            ) . '/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message) . '',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function send_lark($message = "", $type = 1)
{
    if ($type == 1) {
        $chat_id = get_option('lark_in');
        $link = asset('admin/transaction/list');
    } elseif ($type == 2) {
        $chat_id = get_option('lark_out');
        $link = asset('admin/request_withdraw_money/list');
    } elseif ($type == 3) {
        $chat_id = get_option('lark_in');
        $link = asset('admin/transfer_package/list');
    }
    $postData = [
        "msg_type" => "post",
        "content" => [
            "post" => [
                "en_us" => [
                    "content" => [
                        [
                            [
                                "tag" => "text",
                                "text" => $message
                            ],
                            [
                                "tag" => "a",
                                "text" => "view detail",
                                "href" => $link
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $chat_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function checkRewardCustomerCommission($customer_class_id, $customer_id)
{
    $parent_id = get_parent_customer($customer_id);
    if (!empty($parent_id)) {
        $customer_id_f1 = !empty($parent_id[0]) ? $parent_id[0] : 0;
        $customer_id_f2 = !empty($parent_id[1]) ? $parent_id[1] : 0;
        $customer_id_f3 = !empty($parent_id[2]) ? $parent_id[2] : 0;
        if (!empty($customer_id_f1)) {
            $dtCustomerF1 = Clients::where('id', $customer_id_f1)->where('active_reward', 1)->first();
            if (!empty($dtCustomerF1->customer_class)) {
                changeBalance($customer_class_id, 'reward_commission', true, 'F1', $customer_id_f1);
            }
        }
        if (!empty($customer_id_f2)) {
            $dtCustomerF2 = ReferralLevel::whereHas('customer', function ($query) {
                $query->whereHas('customer_class');
            })->where(function ($query) use ($customer_id_f2) {
                $query->where('parent_id', $customer_id_f2);
            })->count();
            $dtDataCustomer = Clients::where('id', $customer_id_f2)->where('active_reward', 1)->first();
            if ($dtCustomerF2 >= number_unformat(get_option('condition_f2'))) {
                if (!empty($dtDataCustomer) && $dtDataCustomer->customer_class->total >= number_unformat(
                        get_option('condition_total_package')
                    )) {
                    changeBalance($customer_class_id, 'reward_commission', true, 'F2', $customer_id_f2);
                }
            }
        }
        if (!empty($customer_id_f3)) {
            $dtCustomerF3 = ReferralLevel::whereHas('customer', function ($query) {
                $query->whereHas('customer_class');
            })->where(function ($query) use ($customer_id_f3) {
                $query->where('parent_id', $customer_id_f3);
            })->count();
            $dtDataCustomer = Clients::where('id', $customer_id_f3)->where('active_reward', 1)->first();
            if ($dtCustomerF3 >= number_unformat(get_option('condition_f3'))) {
                if (!empty($dtDataCustomer) && $dtDataCustomer->customer_class->total >= number_unformat(
                        get_option('condition_total_package')
                    )) {
                    changeBalance($customer_class_id, 'reward_commission', true, 'F3', $customer_id_f3);
                }
            }
        }
    }
}

function getListRewardF()
{
    $arr = [
        [
            'id' => 1,
            'name' => 'F1'
        ],
        [
            'id' => 2,
            'name' => 'F2'
        ],
        [
            'id' => 3,
            'name' => 'F3'
        ],
    ];
    return $arr;
}

function get_parent_id_referral_level_reward($data, $parentId = 0, $level = 1)
{
    // Khởi tạo mảng kết quả
    $array = array();
    // Dừng đệ quy nếu level > 5
    if ($level > 5) {
        return $array;  // Trả về mảng rỗng nếu level vượt quá 5
    }
    // Lặp qua dữ liệu và xử lý mỗi phần tử
    foreach ($data as $key => $value) {
        // Kiểm tra nếu phần tử hiện tại có parent_id bằng $parentId
        if ($value->parent_id == $parentId) {
            // Gán level cho phần tử hiện tại
            $value->level = $level;
            // Nếu level >= 2 và <= 5, thêm vào mảng kết quả
            if ($level >= 2 && $level <= 3) {
                // Lấy tổng giao dịch của khách hàng (nếu có)
                $total = isset($value->customer->customer_class) ? $value->customer->customer_class->total : 0;
                $total_new = isset($value->customer->customer_class) ? $value->customer->customer_class->total : 0;
                // Đệ quy lấy các con của phần tử hiện tại
                $children = get_parent_id_referral_level_reward($data, $value->customer_id, $level + 1);
                // Nếu có con, gán chúng vào giá trị và tính tổng
                if ($children) {
                    $value->children = $children;
                    foreach ($children as $child) {
                        $total += isset($child->total) ? $child->total : 0;
                    }
                }
                // Tính tổng và tổng giao dịch của phần tử
                $value->total = $total;
                $value->total_transaction = $total_new;
                // Tính tổng SEO (chênh lệch giữa total và total_transaction)
                $value->total_seo = ($value->total - $value->total_transaction) > 0 ? ($value->total - $value->total_transaction) : 0;
                // Thêm giá trị vào mảng kết quả
                $array[] = $value;
            } else {
                // Nếu level < 2, tiếp tục đệ quy nhưng không trả về kết quả
                $children = get_parent_id_referral_level_reward($data, $value->customer_id, $level + 1);
                if ($children) {
                    $value->children = $children;
                    foreach ($children as $child) {
                        // Cộng tổng cho các cấp con
                        $value->total += isset($child->total) ? $child->total : 0;
                    }
                }
            }
        }
    }
    // Trả về mảng kết quả
    return $array;
}

function getDescendants($collection, $parentIds = [0])
{
    // Lọc các phần tử có parent_id là một trong những id trong mảng $parentIds
    $children = $collection->whereIn('parent_id', $parentIds);
    // Nếu không có phần tử con nào, trả về mảng rỗng
    if ($children->isEmpty()) {
        return $children;
    }
    // Lấy tất cả các id của phần tử con đã tìm thấy
    $childIds = $children->where('class_customer', 0)->pluck('customer_id');
    // Tìm phần tử con của các phần tử con (đệ quy)
    $grandChildren = getDescendants($collection, $childIds);
    // Gộp các phần tử con vào với nhau
    return $children->merge($grandChildren);
}

function ViewImage($src = null, $width = '320px', $height = '240px')
{
    return !empty($src) ? '<div style="margin-top: 5px;margin-right: 5px"
                 class="show_image">
                <a href="' . $src . '" data-lightbox="customer-profile" class="display-block mbot5">
                <img onerror="this.onerror=null; this.src=\'admin/assets/images/not_available.jpg\';" src="' . $src . '" alt="image"
                     class="img-responsive b-r-1 brs-20"
                     style="width: ' . $width . ';height: ' . $height . '">
                </a>
            </div>' : '';
}

function convertToSlug($string = '')
{
    $slug = strtolower(trim($string));
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug); // bỏ dấu tiếng Việt
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // bỏ ký tự đặc biệt
    $slug = preg_replace('/\s+/', '-', $slug); // thay khoảng trắng bằng -
    $slug = preg_replace('/-+/', '-', $slug); // bỏ dấu - trùng lặp
    return trim($slug, '-');        // thay khoảng trắng bằng dấu -
}

function getListStatusService($id = -1, $type = 'name')
{
    $data = [
        [
            'id' => 0,
            'name' => lang('Đang chờ duyệt'),
            'color' => '#989898',
        ],
        [
            'id' => 1,
            'name' => lang('Đang hoạt động'),
            'color' => '#81c868',
        ],
        [
            'id' => 2,
            'name' => lang('Đã bị từ chối'),
            'color' => '#f05050',
        ],
        [
            'id' => 3,
            'name' => lang('Đang tạm ngưng'),
            'color' => '#46b0b9',
        ],
    ];
    if ($id != -1) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            return $data[0][$type];
        } else {
            return null;
        }
    } else {
        return $data;
    }
}

function status_product_review($key = NULL) {
    $lang = \Illuminate\Support\Facades\App::getLocale();
    if($lang == 'vi') {
        $lang  = 'vi';
    }
    else if($lang == 'kr') {
        $lang  = 'kr';
    }
    else if($lang == 'cn') {
        $lang  = 'cn';
    }
    else if($lang == 'th') {
        $lang  = 'th';
    }
    if(empty($lang)) {
        $lang = 'vi';
    }
    if(is_numeric($key)) {
        $dataStatus = DB::table('tbl_status_review')
            ->select('tbl_status_review.*', 'tbl_status_review_translations.name as name')
            ->join('tbl_status_review_translations', 'tbl_status_review_translations.id_status', '=', 'tbl_status_review.id')
            ->where('tbl_status_review_translations.language', $lang)
            ->where('tbl_status_review.id', $key)
            ->first();
        return !empty($dataStatus) ? (array)$dataStatus : NULL;
    }
    else {
        $dataStatus = DB::table('tbl_status_review')
            ->select('tbl_status_review.*', 'tbl_status_review_translations.name as name')
            ->join('tbl_status_review_translations', 'tbl_status_review_translations.id_status', '=', 'tbl_status_review.id')
            ->where('tbl_status_review_translations.language', $lang)
            ->get();
        $data = [];
        foreach($dataStatus as $keyS => $vStautus) {
            $data[$keyS]['name'] = $vStautus->name;
            $data[$keyS]['color'] = $vStautus->color;
            $data[$keyS]['id'] = $vStautus->id;
        }

        return $data;
    }
}

function status_product_review_active($id = -1, $type = 'name')
{
    $data = [
        [
            'id' => 0,
            'name' => lang('Inreview'),
            'color' => '#989898',
            'background' => '#FFC559',
        ],
        [
            'id' => 1,
            'name' => lang('Approved'),
            'color' => '#81c868',
            'background' => '#1DAC8E',
        ],
        [
            'id' => 2,
            'name' => lang('Rejected'),
            'color' => '#f05050',
            'background' => '#FF7B81',
        ]
    ];
    if ($id != -1) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            if($type == 'all') {
                return $data[0];
            }
            else {
                return $data[0][$type];
            }
        } else {
            return null;
        }
    } else {
        return $data;
    }
}


function status_challenge($id = -1, $type = 'name') {
    $data = [
        [
            'id' => 0,
            'name' => lang('Inreview'),
            'color' => '#989898',
            'background' => '#FFC559',
        ],
        [
            'id' => 1,
            'name' => lang('Approved'),
            'color' => '#81c868',
            'background' => '#1DAC8E',
        ],
        [
            'id' => 2,
            'name' => lang('Ended'),
            'color' => '#f05050',
            'background' => '#FF7B81',
        ]
    ];
    if ($id != -1) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            if($type == 'all') {
                return $data[0];
            }
            else {
                return $data[0][$type];
            }
        } else {
            return null;
        }
    } else {
        return $data;
    }
}

function ConnectSocket($user_id, $user_name)
{
    $curl = curl_init();
    $db_name = env('DB_DATABASE', 'nglow-admin');
    $dataField = json_encode([
        'user_id' => $user_id,
        'user_name' => $user_name,
        'db_name' => $db_name
    ]);

    $link_connect_socket = get_option('link_connect_socket');
    //    $link_connect_socket = 'http://192.168.1.178:3005';

    curl_setopt_array($curl, array(
        CURLOPT_URL => $link_connect_socket . '/add-user',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $dataField,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response, true);
    return !empty($response['token']) ? $response['token'] : NULL;
}

function sendSocket($data, $channels, $events)
{
    $curl = curl_init();
    $link_connect_socket = get_option('link_connect_socket');
    $db_name = env('DB_DATABASE', 'nglow-admin');
    if(!is_array($channels)) {
        $channels = [$channels];
    }
    $dataField = json_encode([
        'channels' => $channels,
        'event' => $events,
        'data' => $data,
        'db_name' => $db_name,
    ]);
    $data = $dataField;

    curl_setopt_array($curl, array(
        CURLOPT_URL => $link_connect_socket . '/send-notification',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $dataField,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return true;
}

function LangNoti($content = '') {
    $langNow =  \Illuminate\Support\Facades\App::getLocale();
    preg_match_all('/\{(.*?)\}/', $content, $matches);
    $arrayContent = $matches[1];
    foreach($arrayContent as $k => $v) {
        if(count(explode('.lang', $v)) == 2) {
            $contentLang = explode('.lang', $v);
            $arrayContent[$k] = get_option($contentLang[0] . '_' . $langNow);
        }
        else {
            $arrayContent[$k] = lang($v);
        }
    }
    $LangContent = implode(' ', $arrayContent);
    return $LangContent;
}

function eventStatus(string $dateStart, string $dateEnd, string $tz = 'Asia/Ho_Chi_Minh', ?Carbon $now = null)
{
    // Chuẩn hoá "bây giờ"
    $now = $now ?: Carbon::now($tz);

    // Parse thời gian đầu/cuối (chấp nhận nhiều định dạng, ví dụ: '2025/09/01 10:10')
    $start = Carbon::parse($dateStart, $tz);
    $end   = Carbon::parse($dateEnd, $tz);
    // Phòng lỗi cấu hình sai
    if ($end->lt($start)) {
        // Tuỳ chọn: ném exception hoặc trả về đặc biệt
        return [
            'id' => 0,
            'name' => lang('invalid_range')
        ];
    }

    // Quy ước: đang diễn ra khi start <= now < end
    if ($now->lt($start)) {
        return [
            'id' => 1,
            'name' => lang('coming_soon')
        ]; // chưa đến
    }
    if ($now->lt($end)) {
        return [
            'id' => 2,
            'name' => lang('happening')
        ];   // đang hoạt động
    }
    return [
        'id' => 3,
        'name' => lang('ended')
    ];// đã kết thúc
}

function getCategoryNoLimit($id_client = '') {
    $type = 0;
    //$type == 0 là tính toán dựa trên số đăng ký review theo số sản phẩm
    //$type == 2 là tính toán dựa trên số đăng ký review theo số lượng tất cả sản phẩm
    if(!empty($id_client)) {
        $listCategoryId = [];
        $listCategory = CategoryProducts::select('tbl_category_products.id')
            ->where('tbl_category_products.active', 1)
            ->whereRaw('tbl_category_products.max_product_review > 0');
        if($type == 0) {
            $listCategory->whereRaw(
                "(
                        SELECT COUNT(tbl_clients_sign_up_review.id)
                        FROM tbl_clients_sign_up_review
                        LEFT JOIN tbl_product_category ON tbl_product_category.id_product = tbl_clients_sign_up_review.id_product
                        WHERE tbl_product_category.id_category = tbl_category_products.id
                        AND tbl_clients_sign_up_review.id_client = $id_client
                        ) < tbl_category_products.max_product_review"
            );
        }
        else if($type == 1){
            //phần này dự trù cho tương lai khi có số lượng sản phẩm
//            $listCategory->whereRaw(
//                "(
//                        SELECT SUM(tbl_clients_sign_up_review.quantity)
//                        FROM tbl_clients_sign_up_review
//                        LEFT JOIN tbl_product_category ON tbl_product_category.id_product = tbl_clients_sign_up_review.id_product
//                        WHERE tbl_product_category.id_category = tbl_category_products.id
//                        AND tbl_clients_sign_up_review.id_client = $id_client
//                        ) < tbl_category_products.max_product_review"
//            );
        }
        $listCategory = $listCategory->get();
        if(!empty($listCategory)) {
            foreach($listCategory as $key => $value) {
                $listCategoryId[] = $value->id;
            }
        }
        return $listCategoryId ?? [0];
    }
    return false;
}




//START HELPER ROBOT CHAT SUPPORT
function getViewChildScript($id = '') {
    $_locale = getLangSystemToClient();
    $script_detail = ScriptDetail::where('tbl_script_detail.id_parent', $id)
        ->select('tbl_script_detail.*', 'st.content as content', 'st.language')
        ->LeftJoin('tbl_script_detail_translations as st', function ($join) use ($_locale) {
            $join->on('tbl_script_detail.id', '=', 'st.id_script_detail')
                ->where('st.language', '=', $_locale);
        })
        ->get()
        ->toArray();
    if(!empty($script_detail)) {
        $HtmlChild = '<ul class="ul_'.$id.'">';
        foreach ($script_detail as $key => $value) {
            $HtmlChild .= ViewHtmlChildScript($value);
        }
        $HtmlChild .= '</ul>';
        return  $HtmlChild;
    }
    return '';
}

function ViewHtmlChildScript($data = []) {
    if(!empty($data)) {
        $EventPlusChild = '';
        $_locale = getLangSystemToClient();
        $show_move_event = !empty($data['show_move_event']) ? ('(' . list_view_append($data['show_move_event'], 0, $_locale).')') : '';
        $event_show = !empty($data['event_show']) ? list_event_show($data['event_show']) : '';
        return '<li>
					<div class="div div_child_'.$data['id'].' '.(!empty($data['use_limit']) ? 'bg-danger' : '').' active_view_app_'.$data['active_view_app'].'">
						<div>
							<span class="content_auto">
								'.$data['name'].' (' . ($event_show)  . ') - ' . $show_move_event .
                (empty($data['type_send']) ? '<b class="text-warning">('.lang('staff').')</b>' : '<b class="text-success">'.($data['type_send'] == 2 ? lang('title') : lang('client')).'</b>'). '
							</span>
							<span class="mleft10">
								<a class="dt-modal" href="'. ('admin/script/edit_detail_child/' . $data['id']) . '"><i class="fa fa-edit"></i></a>
								<a class=" mleft15 text-danger" onclick="removeBotChat('.$data['id'].')"><i class="fa fa-remove"></i></a>
							</span>
							<div class="onoffswitch pull-auto">
								<input type="checkbox" data-id="' . $data['id'] . '"
									   class="onoffswitch-checkbox checkStatus"
									   id="c_active_' . $data['id'] . '" '. (!empty($data['active_view_app']) ? 'checked' : '').'>
								<label class="onoffswitch-label" for="c_active_' . $data['id'] . '"></label>
							</div>
							<div><i class="div_content">'.lang('content_send').': '. $data['content'] . '</i></div>
							<div><i class="div_file"></i></div>
						</div>
						'.$EventPlusChild.'
					</div>
					'.getViewChildScript($data['id']).'
				</li>';

    }
    return '';
}

function ViewHtmlScript($id = '') {
    if(!empty($id)) {
        $_locale = getLangSystemToClient();
        $script_detail = ScriptDetail::where('tbl_script_detail.id', $id)
            ->select('tbl_script_detail.*', 'st.content as content', 'st.language')
            ->LeftJoin('tbl_script_detail_translations as st', function ($join) use ($_locale) {
                $join->on('tbl_script_detail.id', '=', 'st.id_script_detail')
                    ->where('st.language', '=', $_locale);
            })
            ->get()
            ->first();
        return '<div>
					<span class="content_auto">
					' . $script_detail->name . ' (' . (list_event_show($script_detail->event_show)) . ') - ' .
            (empty($script_detail->type_send) ? '<b class="text-warning">(Nhân viên)</b>' : '<b class="text-success">'.($script_detail->type_send == 2 ? 'Tiêu đề' : 'Khách hàng').'</b>').
            '</span>
					<span class="mleft10">
						<a class="dt-modal" href="'. ('admin/script/edit_detail_child/' . $script_detail->id) . '"><i class="fa fa-edit"></i></a>
						<a class=" mleft15 text-danger" onclick="removeBotChat('.$script_detail->id.')"><i class="fa fa-remove"></i></a>
					</span>
					<div class="onoffswitch pull-auto">
						<input type="checkbox" data-id="'.$script_detail->id.'"
							   class="onoffswitch-checkbox checkStatus"
							   id="c_active_' . $script_detail->id . '" '. (!empty($script_detail->active_view_app) ? 'checked' : '').'>
						<label class="onoffswitch-label" for="c_active_' . $script_detail->id . '"></label>
					</div>
					<div><i class="div_content">Nội dung gửi: '. $script_detail->content . '</i></div>
				</div>
				<div class="mtop10"><a onclick="plusChild(this, ' . $script_detail->id . ')"><i class="fa fa-plus"></i></a></div>';

    }
    return '';
}



function list_event_show($id = '') {
    $event_show = [
        ['id' => 'text', 'name' => lang('text')],
        ['id' => 'select', 'name' => lang('select')],
        ['id' => 'options', 'name' => lang('options')],
        ['id' => 'event_app', 'name' => lang('event_app')],
    ];
    if(!empty($id)) {
        $event_show = [
            'start' => lang('start'),
            'text' => lang('text'),
            'select' => lang('select'),
            'options' => lang('options'),
            'event_app' => lang('event_app'),
        ];
        return !empty($event_show[$id]) ? $event_show[$id] : '';
    }
    return $event_show;
}

function list_event_app($id = '') {
    $event_app = [
        ['id' => 'products_filter', 'name' => lang('products_filter')],
        ['id' => 'result_products_filter', 'name' => lang('result_products_filter')],
    ];
    if(!empty($id)) {
        $event_app = [
            'products_filter' => lang('products_filter'),
            'result_products_filter' => lang('result_products_filter'),
        ];
        return !empty($event_app[$id]) ? $event_app[$id] : '';
    }
    return $event_app;
}

function list_view_append($type = '', $id = '0', $_locale = '') {
    if(empty($_locale)) {
        $_locale = getLangSystemToClient();
    }
    if(!empty($type)) {
        $id = $id ?? 0;
        $baseUrl = config('services.storage.url');
        if($type == 'products_filter') {
            return ProductsFilter::select('tbl_products_filter.id',
                DB::raw("CONCAT('".$baseUrl."/', tbl_products_filter.icon) AS icon"),
                DB::raw("CONCAT('".$baseUrl."/', tbl_products_filter.icon_active) AS icon_active"),
                'st.name'
                )
                ->join('tbl_products_filter_translations as st', 'st.id_product_filter', '=', 'tbl_products_filter.id')
                ->where('st.language', $_locale)
//                ->where('tbl_products_filter.active', 1)
                ->where('tbl_products_filter.id_parent', $id)
                ->get();
        }
    }
}
//END HELPER ROBOT CHAT SUPPORT

function export_excel_table($headings, $rows = [], $filename = 'export.xlsx', $config = [])
{
    $export = new class($headings, $rows, $config)
        implements FromArray, WithHeadings, WithEvents {

        private $headings;
        private $rows;
        private $config;

        public function __construct($headings, $rows, $config)
        {
            $this->headings = $headings;
            $this->rows     = $rows;
            $this->config   = $config;
        }

        public function headings(): array
        {
            return $this->headings;
        }

        public function array(): array
        {
            return $this->rows;
        }

        public function registerEvents(): array
        {
            return [
                AfterSheet::class => function(AfterSheet $event) {

                    $sheet = $event->sheet;
                    $highestRow = $sheet->getHighestRow();
                    $highestCol = $sheet->getHighestColumn();

                    /** =======================
                     *  1) AUTO SIZE COLUMN
                     * ======================= */
                    foreach (range('A', $highestCol) as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    /** =======================
                     *  2) BORDER FULL
                     * ======================= */
                    $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                        ->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                                ]
                            ]
                        ]);

                    /** =======================
                     *  3) HEADER CSS
                     * ======================= */
                    $sheet->getStyle("A1:{$highestCol}1")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['rgb' => '4F81BD']
                        ],
                        'alignment' => ['horizontal' => 'center']
                    ]);

                    /** =======================
                     *  4) GROUP MERGE ROW (OPTION)
                     * ======================= */
                    if (!empty($this->config['group_by'])) {

                        $groupIndex = $this->config['group_by'];

                        $currentValue = null;
                        $startRow = 2; // bắt đầu từ dòng data
                        $rowCount = 0;

                        for ($i = 0; $i < count($this->rows); $i++) {
                            $value = $this->rows[$i][$groupIndex] ?? null;

                            if ($value !== $currentValue) {
                                // group mới
                                if ($rowCount > 1) {

                                    // merge tất cả cột GROUP
                                    foreach ($this->config['group_columns'] as $colLetter) {
                                        $sheet->mergeCells("{$colLetter}{$startRow}:{$colLetter}" . ($startRow + $rowCount - 1));
                                        $sheet->getStyle("{$colLetter}{$startRow}")
                                            ->getAlignment()->setVertical('center');
                                    }
                                }

                                // reset
                                $currentValue = $value;
                                $startRow = $i + 2;
                                $rowCount = 1;
                            } else {
                                $rowCount++;
                            }
                        }

                        // merge group cuối
                        if ($rowCount > 1) {
                            foreach ($this->config['group_columns'] as $colLetter) {
                                $endRow = $startRow + $rowCount - 1;
                                $sheet->mergeCells("{$colLetter}{$startRow}:{$colLetter}{$endRow}");
                                $sheet->getStyle("{$colLetter}{$startRow}")
                                    ->getAlignment()->setVertical('center');
                            }
                        }
                    }
                }
            ];
        }
    };

    return Excel::download($export, $filename);
}

function getLocale($request) {
    $_locale = $request->header('locale');
    if(empty($_locale)) {
        $_locale = $request->_locale;
    }
    return $_locale;
}

function getLangSystemToClient() {
    $locale = app()->getLocale();
    if($locale == 'vi') {
        return 'vi';
    }
    else if($locale == 'en') {
        return 'en';
    }
    else if($locale == 'ko') {
        return 'kr';
    }
    else if($locale == 'cn') {
        return 'cn';
    }
    else if($locale == 'th') {
        return 'th';
    }
    else {
        return 'vi';
    }
    return $locale;
}

function getLangAppDataFlat($locale = null, $string = false) {
    $locale = $locale ?: app()->getLocale();
    $baseDir = resource_path('lang_app/' . $locale);
    if (! is_dir($baseDir)) {
        return [];
    }
    $data = [];
    foreach (glob($baseDir . '/message.php') as $path) {
        $fileName = pathinfo($path, PATHINFO_FILENAME);
        $arr      = include $path;

        if (!is_array($arr)) {
            continue;
        }

        foreach ($arr as $key => $value) {
            $data[$key] = $value;
        }
    }
    if(!empty($string)) {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    return $data;
}

function getLangAppDataFlatTest($locale = null, $string = false) {
    $locale = $locale ?: app()->getLocale();
    $baseDir = storage_path('app/public/lang_app/');
    if (! is_dir($baseDir)) {
        return [];
    }
    $data = [];
    $filePath = $baseDir . '/' . $locale . '.json';
    if (!is_file($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);

    if ($content === false || $content === '') {
        return [];
    }

//    $arr = json_decode($content, true);
    $arr = $content;

    // Nếu decode lỗi, trả về mảng rỗng
//    if (!is_array($arr)) {
//        return [];
//    }

    return $arr;
}

function getListStatusTransaction()
{
    return [
        [
            'id' => 0,
            'name' => lang('dt_status_order_request'),
            'color' => '#ffffff',
            'background' => '#9f86dd',
            'index' => 0,
        ],
        [
            'id' => 4,
            'name' => lang('dt_status_order_payment'),
            'color' => '#ffffff',
            'background' => '#ffbd4a',
            'index' => 1,
        ],
        [
            'id' => 1,
            'name' => lang('dt_status_order_approve'),
            'color' => '#ffffff',
            'background' => '#3086EF',
            'index' => 2,
        ],
        [
            'id' => 2,
            'name' => lang('dt_status_order_finish'),
            'color' => '#ffffff',
            'background' => '#009689',
            'index' => 3,
        ],
        [
            'id' => 3,
            'name' => lang('dt_status_order_cancel'),
            'color' => '#ffffff',
            'background' => '#FF7B81',
            'index' => 4,
        ],
    ];
}

function getValueStatusTransaction($id, $type = 'name')
{
    $option[0]['name'] = lang('dt_status_order_request');
    $option[4]['name'] = lang('dt_status_order_payment');
    $option[1]['name'] = lang('dt_status_order_approve');
    $option[2]['name'] = lang('dt_status_order_finish');
    $option[3]['name'] = lang('dt_status_order_cancel');

    $option[0]['color'] = '#ffffff';
    $option[1]['color'] = '#ffffff';
    $option[2]['color'] = '#ffffff';
    $option[3]['color'] = '#ffffff';
    $option[4]['color'] = '#ffffff';

    $option[0]['background'] = '#9f86dd';
    $option[1]['background'] = '#3086EF';
    $option[2]['background'] = '#009689';
    $option[3]['background'] = '#FF7B81';
    $option[4]['background'] = '#ffbd4a';

    $option[0]['index'] = 0;
    $option[1]['index'] = 2;
    $option[2]['index'] = 3;
    $option[3]['index'] = 4;
    $option[4]['index'] = 1;

    return $option[$id][$type];
}

function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
//    $jsonResult = json_decode($result, true);
    return $result;
}


function createPay2SPayment($dataPost = [])
{
    $endpoint = 'https://payment.pay2s.vn/v1/gateway/api/create';
//    $endpoint = 'https://sandbox-payment.pay2s.vn/v1/gateway/api/create';
    $NameCompany = "CONG TY TNHH CONG NGHE FOSO";
    $accessKey = get_option('pay2s_access_key');
    $secretKey = get_option('pay2s_secret_key');
    $order_desc = $dataPost['code'];//Code đơn hàng

    $partnerCode = get_option('pay2s_partner_code');// mã định danh giao dịch
    $accessKey = !empty($accessKey) ? $accessKey : '305edb539f2c06c673d2ad2bf0a44c7b8faef630bc7516e62d16ffedc904d0c9';
    $secretKey = !empty($secretKey) ? $secretKey : '8957c9ec4e285843910d515e7890196b14b3df4239eb90563f0d0a00f766ee5e';
    $partnerCode = !empty($partnerCode) ? $partnerCode : 'PAY2SSGOJR2P5FMWHIXT';

    $redirectUrl = 'https://webhook.site/b7d50ebe-53ca-42e4-b11b-8249b47d378a';// link chuyển hướng sau khi thanh toán
    $ipnUrl = 'https://webhook.site/b7d50ebe-53ca-42e4-b11b-8249b47d378a';// link thông báo kết quả thanh toán

    $amount = $dataPost['amount']; //số tiền cần thanh toán
    $orderId = $dataPost['code'];
    $requestId = $dataPost['code'] . '_' . time();
    $requestType = 'pay2s';
    $lang = 'vi';
    $orderInfo = str_replace('{{orderid}}', $orderId, $order_desc);
    $bankList = [
        [
            'account_number' => '95652868',
            'bank_id' => 'MBB'
        ]
    ];
    // Tạo chữ ký HMAC SHA256
    $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&bankAccounts=Array&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
    $signature = hash_hmac("sha256", $rawHash, $secretKey);
    $data = array(
        'accessKey' => $accessKey,
        'partnerCode' => $partnerCode,
        'partnerName' => $NameCompany,
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'orderType' => $requestType,
        'bankAccounts' => $bankList,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'requestType' => $requestType,
        'signature' => $signature,
    );
    $result = execPostRequest($endpoint, json_encode($data));
    $jsonResult = json_decode($result, true);  // decode json
    return $jsonResult;
}

function createQrBank($data = array()){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://payment.pay2s.vn/v1/gateway/create_qr',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            'Cookie: PHPSESSID=t5g2srp7922an81o637aadbc7t'
        ),
    ));
    $response = curl_exec($curl);
    $response = json_decode($response);
    curl_close($curl);
    return $response;
}

function secondsToHMS($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}

function get_parent_id_referral_level_html($data, $parentId = 0,&$html = '',$dem = 0)
{
    $array = [];
    foreach ($data as $key => $value) {
        if ($value['id_client_introduce'] == $parentId) {
            $array[] = ($value);
        }
    }

    $dem ++;
    if ($array) {
        $html.= '<ul class="data-tree" style="display: flex;">';
        foreach ($array as $item)
        {
            $customer = $item['customer'] ?? [];
            if (empty($customer)){
                continue;
            }
            if ($customer['type_client'] == 1){
                $object = 'clients';
            } else {
                $object = 'clients';
            }
            $image = !empty($item['customer']['avatar']) ? $item['customer']['avatar'] : 'admin/assets/images/avatar.jpg';
            $html.= '<li>';
            $html.= '<a target="_blank" href="admin/'.$object.'/view/'.$item['customer']['id'].'"><img style="width: 50px; border-radius: 50%;height:50px" src="'.$image.'" /></a>';
            $html.= '<div>'.$item['customer']['fullname'].'</div>';
            get_parent_id_referral_level_html($data, $item['id_client'],$html,$dem);
            $html.= '</li>';
        }
        $html.= '</ul>';
    }
    return $html;
}


function extractAndNormalizeVideo($content)
{
    // Chuẩn hóa content
    $content = trim($content);

    $content = trim($content);

    // CMW-1768988447 (10 digits)
    if (preg_match('/^ELN[\s\-]?(\d{10})\b/i', $content, $matches)) {
        return 'ELN-' . $matches[1];
    }

    return null;
}

function getListTypeLeader($id = 0, $type = 'name')
{
    $data = [
        [
            'id' => 1,
            'name' => lang('F1')
        ],
        [
            'id' => 2,
            'name' => lang('F1+'),
        ],
    ];
    if (!empty($id)) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            return $data[0][$type];
        } else {
            return null;
        }
    } else {
        return $data;
    }
}
function createCodeIntroduceUser()
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 7; $i++) {
        $keyRand = $characters[rand(0, $charactersLength - 1)];
        $randomString .= $keyRand; // Lấy ký tự ngẫu nhiên
    }
    $ktCode = DB::table('tbl_users')->where('code_introduce', $randomString)->first();
    if (!empty($ktCode->id)) {
        $randomString = createCodeIntroduceUser();
    }
    return $randomString;
}

