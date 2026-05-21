<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\ElearningWaiting;
use App\Models\ElearningUnlock;
use App\Models\TransactionDriver;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\SocketHelpersAdmin;

class WebhookController extends Controller
{

    protected $dbAccount;
    public function __construct(Request $request, AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->dbAccount = $accountService;
        $this->url = config('services.url');
    }

    public function webhookAlepay()
    {
        $data = $this->request->input();
        if (!empty($data)) {
            if (!empty($data['transactionInfo'])) {
                $transactionInfo = $data['transactionInfo'];
                $orderCode = !empty($transactionInfo['orderCode']) ? $transactionInfo['orderCode'] : null;
                $orderCodeCheck = explode('-', $orderCode);
                $type_transaction = 0;
                if ($orderCodeCheck[0] == 'GD') {
                    $transaction = Transaction::where('reference_no', $orderCode)->first();
                    $type_transaction = 1;
                } else {
                    $transaction = TransactionDriver::where('reference_no', $orderCode)->first();
                    $type_transaction = 2;
                }
                DB::table('tbl_transaction_alepay')->insert([
                    'transactionCode' => !empty($transactionInfo['transactionCode']) ? $transactionInfo['transactionCode'] : null,
                    'transaction_kanow_id' => !empty($transaction) ? $transaction->id : 0,
                    'orderCode' => !empty($transactionInfo['orderCode']) ? $transactionInfo['orderCode'] : null,
                    'amount' => !empty($transactionInfo['amount']) ? $transactionInfo['amount'] : 0,
                    'currency' => !empty($transactionInfo['currency']) ? $transactionInfo['currency'] : null,
                    'buyerEmail' => !empty($transactionInfo['buyerEmail']) ? $transactionInfo['buyerEmail'] : null,
                    'buyerPhone' => !empty($transactionInfo['buyerPhone']) ? $transactionInfo['buyerPhone'] : null,
                    'cardNumber' => !empty($transactionInfo['cardNumber']) ? $transactionInfo['cardNumber'] : null,
                    'buyerName' => !empty($transactionInfo['buyerName']) ? $transactionInfo['buyerName'] : null,
                    'cardHolderName' => !empty($transactionInfo['cardHolderName']) ? $transactionInfo['cardHolderName'] : null,
                    'status' => !empty($transactionInfo['status']) ? $transactionInfo['status'] : null,
                    'message' => !empty($transactionInfo['message']) ? $transactionInfo['message'] : null,
                    'installment' => !empty($transactionInfo['installment']) ? $transactionInfo['installment'] : null,
                    'is3D' => !empty($transactionInfo['is3D']) ? $transactionInfo['is3D'] : null,
                    'month' => !empty($transactionInfo['month']) ? $transactionInfo['month'] : 0,
                    'bankCode' => !empty($transactionInfo['bankCode']) ? $transactionInfo['bankCode'] : null,
                    'bankName' => !empty($transactionInfo['bankName']) ? $transactionInfo['bankName'] : null,
                    'bankHotline' => !empty($transactionInfo['bankHotline']) ? $transactionInfo['bankHotline'] : null,
                    'method' => !empty($transactionInfo['method']) ? $transactionInfo['method'] : null,
                    'bankType' => !empty($transactionInfo['bankType']) ? $transactionInfo['bankType'] : null,
                    'successTime' => !empty($transactionInfo['successTime']) ? $transactionInfo['successTime'] : null,
                    'merchantFee' => !empty($transactionInfo['merchantFee']) ? $transactionInfo['merchantFee'] : null,
                    'payerFee' => !empty($transactionInfo['payerFee']) ? $transactionInfo['payerFee'] : null,
                    'reason' => !empty($transactionInfo['reason']) ? $transactionInfo['reason'] : null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'type_transaction' => $type_transaction
                ]);
            }

            if (!empty($data['refundInfo'])) {
                $refundInfo = $data['refundInfo'];
                $orderCode = !empty($refundInfo['orderCode']) ? $refundInfo['orderCode'] : null;
                $transaction = TransactionDriver::where('reference_no', $orderCode)->first();
                DB::table('tbl_refund_alepay')->insert([
                    'transaction_kanow_id' => !empty($transaction) ? $transaction->id : 0,
                    'refundCode' => !empty($refundInfo['refundCode']) ? $refundInfo['refundCode'] : null,
                    'transactionCode' => !empty($refundInfo['transactionCode']) ? $refundInfo['transactionCode'] : null,
                    'orderCode' => !empty($refundInfo['orderCode']) ? $refundInfo['orderCode'] : null,
                    'refundAmount' => !empty($refundInfo['refundAmount']) ? $refundInfo['refundAmount'] : 0,
                    'totalRefundToPayer' => !empty($refundInfo['totalRefundToPayer']) ? $refundInfo['totalRefundToPayer'] : 0,
                    'refundFee' => !empty($refundInfo['refundFee']) ? $refundInfo['refundFee'] : 0,
                    'reason' => !empty($refundInfo['reason']) ? $refundInfo['reason'] : null,
                    'refundStatus' => !empty($refundInfo['refundStatus']) ? $refundInfo['refundStatus'] : null,
                    'refundTime' => !empty($refundInfo['refundTime']) ? $refundInfo['refundTime'] : null,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            if (!empty($data['cardTokenInfo'])) {
                $cardTokenInfo = $data['cardTokenInfo'];
//                DB::table('tbl_customer_card_alepay')->insert([
//                    'cardLinkStatus' => !empty($cardTokenInfo['cardLinkStatus']) ? $cardTokenInfo['cardLinkStatus'] : null,
//                    'email' => !empty($cardTokenInfo['email']) ? $cardTokenInfo['email'] : null,
//                    'customerId' => !empty($cardTokenInfo['customerId']) ? $cardTokenInfo['customerId'] : null,
//                    'token' => !empty($cardTokenInfo['token']) ? $cardTokenInfo['token'] : null,
//                    'cardNumber' => !empty($cardTokenInfo['cardNumber']) ? $cardTokenInfo['cardNumber'] : null,
//                    'cardHolderName' => !empty($cardTokenInfo['cardHolderName']) ? $cardTokenInfo['cardHolderName'] : null,
//                    'cardExpireMonth' => !empty($cardTokenInfo['cardExpireMonth']) ? $cardTokenInfo['cardExpireMonth'] : null,
//                    'cardExpireYear' => !empty($cardTokenInfo['cardExpireYear']) ? $cardTokenInfo['cardExpireYear'] : null,
//                    'paymentMethod' => !empty($cardTokenInfo['paymentMethod']) ? $cardTokenInfo['paymentMethod'] : null,
//                    'bankCode' => !empty($cardTokenInfo['bankCode']) ? $cardTokenInfo['bankCode'] : null,
//                    'reason' => !empty($cardTokenInfo['reason']) ? $cardTokenInfo['reason'] : null,
//                    'status' => !empty($cardTokenInfo['status']) ? $cardTokenInfo['status'] : null,
//                    'bankType' => !empty($cardTokenInfo['bankType']) ? $cardTokenInfo['bankType'] : null,
//                    'created_at' => date('Y-m-d H:i:s'),
//                ]);
            }
        }
    }

    public function webhookStripe(){
        $data = $this->request->input();
        \Log::info('stripe',$data);
    }

    public function WebhookPay2s(Request $request) {
        $expectedToken = 'd67624879b68397c2194960c4ef2a054e4836407f3a6f83b21';

        /** =========================
         *  CHECK AUTHORIZATION
         *  ========================= */
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Log::error('pay2s', [
                'success' => false,
                'message' => 'Invalid Authorization header'
            ]);
            return response()->json(['success' => false], 401);
        }

        $receivedToken = $matches[1];
        if ($receivedToken !== $expectedToken) {
            Log::error('pay2s', [
                'success' => false,
                'message' => 'Invalid token'
            ]);
            return response()->json(['success' => false], 403);
        }

        /** =========================
         *  GET JSON BODY
         *  ========================= */
        $data = $request->json()->all();

        if (!isset($data['transactions']) || !is_array($data['transactions'])) {
            Log::error('pay2s', [
                'success' => false,
                'message' => 'Invalid payload'
            ]);
            return response()->json(['success' => false], 400);
        }

        /** =========================
         *  PROCESS TRANSACTIONS
         *  ========================= */
        foreach ($data['transactions'] as $transaction) {
            if (($transaction['transferType'] ?? '') !== 'IN') {
                continue;
            }
            $content = extractAndNormalizeChallengeCode($transaction['content']);
            $contentArr = explode('-', $content);
            $reference_no = $contentArr[1] ?? null;

            if (!$reference_no) {
                continue;
            }

            $money = $transaction['transferAmount'];

            $ChallengeMe =$this->dbAccount->ChallengeMe();

            if (!$ChallengeMe || !in_array($ChallengeMe->status, ['part', 'create'])) {
                continue;
            }

            /** =========================
             *  SAVE PAYMENT HISTORY
             *  ========================= */
            DB::table('tbl_history_payment_upgrade_package')->insert([
                'code_payment' => $transaction['transactionNumber'],
                'code'         => $upgrade->code,
                'money'        => $money,
                'status'       => 'success',
            ]);

            /** =========================
             *  UPDATE UPGRADE PACKAGE
             *  ========================= */
            $amountPaid = $upgrade->amount_paid + $money;
            $status = $amountPaid >= $upgrade->money_need_paid ? 'success' : 'part';

            DB::table('tbl_upgrade_package')
                ->where('id', $upgrade->id)
                ->update([
                    'amount_paid' => $amountPaid,
                    'status'      => $status
                ]);

            if ($status !== 'success') {
                continue;
            }

            /** =========================
             *  SUCCESS → UPDATE COMPANY
             *  ========================= */
            $company = DB::table('tblcompany_user')
                ->where('id', $upgrade->id_client)
                ->first();

            if (!$company) {
                continue;
            }

            $type = $upgrade->type_upgrade_package ?? 1;
            $dataUpdateCompany = [];

            if ($type == 2) {
                // cộng user
                $dataUpdateCompany['number_of_users']
                    = $company->number_of_users + $upgrade->number_of_users;
            } else {
                // nâng gói
                $expireDate = Carbon::now()
                    ->addMonths($upgrade->number_month)
                    ->format('Y-m-d');

                $dataUpdateCompany = [
                    'id_package_service'        => $upgrade->id_package,
                    'id_package_service_detail' => $upgrade->id_package_detail,
                    'number_of_users'           => $upgrade->number_of_users,
                    'start_date'                => now()->format('Y-m-d'),
                    'expiration_date'           => $expireDate,
                    'monthly_rental'            => $upgrade->number_month,
                ];
            }

            DB::table('tblcompany_user')
                ->where('id', $company->id)
                ->update($dataUpdateCompany);

            /** =========================
             *  LOG BEFORE UPGRADE
             *  ========================= */
            DB::table('tbl_upgrade_package')
                ->where('id', $upgrade->id)
                ->update([
                    'log_before_upgrade' => json_encode([
                        'id_package_service' => $company->id_package_service,
                        'id_package_service_detail' => $company->id_package_service_detail,
                        'number_of_users' => $company->number_of_users,
                        'expiration_date' => $company->expiration_date,
                        'start_date' => $company->start_date,
                        'monthly_rental' => $company->monthly_rental,
                        'date_update_laster' => now()->toDateTimeString(),
                    ])
                ]);

            // TODO:
            // - ZNS Zalo
            // - Socket
            // giữ nguyên service cũ là được
        }

        Log::info('pay2s', [
            'success' => true,
            'message' => 'Transactions processed successfully'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transactions processed successfully'
        ], 200);
    }

    public function WebhookPay2sUnlockVideo(Request $request)  {
        $local = $request->local;
        if(empty($local)) {
            $expectedToken = get_option('pay2s_token_video_unlock');

            /** =========================
             *  CHECK AUTHORIZATION
             *  ========================= */
            $authHeader = $request->header('Authorization');

            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                //            Log::error('pay2s', [
                //                'success' => false,
                //                'message' => 'Invalid Authorization header'
                //            ]);
                return response()->json(['success' => false], 401);
            }

            $receivedToken = $matches[1];
            if ($receivedToken !== $expectedToken) {
                //            Log::error('pay2s', [
                //                'success' => false,
                //                'message' => 'Invalid token'
                //            ]);
                return response()->json(['success' => false], 403);
            }
            /** =========================
             *  GET JSON BODY
             *  ========================= */
            $data = $request->json()->all();
        }
        else {
            $data = $request->data;
             $data = json_decode($data, true);;
        }

//        $data = [
//            "transactions" => [
//                [
//                    "id" => 12341,
//                    "gateway" => "MBB",
//                    "transactionDate" => "2026-01-08 12:34:37",
//                    "transactionNumber" => "FT26008000642065",
//                    "vaNumber" => "963869",
//                    "accountNumber" => "95652868",
//                    "content" => "ELN-1773383404",
//                    "transferType" => "IN",
//                    "transferAmount" => 1000000,
//                    "checksum" => "b9cc91651582ddbf1568e376ec0dd555"
//                ]
//            ]
//        ];

//        dd($data);
        if (!isset($data['transactions']) || !is_array($data['transactions'])) {
            //            Log::error('pay2s', [
            //                'success' => false,
            //                'message' => 'Invalid payload'
            //            ]);
            return response()->json(['success' => false], 400);
        }
        /** =========================
         *  PROCESS TRANSACTIONS
         *  ========================= */
        foreach ($data['transactions'] as $transaction) {
            if (($transaction['transferType'] ?? '') !== 'IN') {
                continue;
            }
            $reference_no = extractAndNormalizeVideo($transaction['content']);
            $money = $transaction['transferAmount'];

            DB::table('tbl_log_webhook_payment')->insert([
                'code_payment' => $transaction['transactionNumber'] ?? NULL,
                'code' => $reference_no ?? NULL,
                'money' => $money,
                'type' => 'pay2s',
                'data_json' => json_encode($transaction),
            ]);

            if (!$reference_no) {
                continue;
            }


            $elearningWaiting = DB::table('tbl_elearning_waiting')->where('reference_no', $reference_no)->first();

            if (!$elearningWaiting || !in_array($elearningWaiting->status, ['0'])) {
                continue;
            }


            $amountPaid = $elearningWaiting->money_payment + $money;

            $statusPayment = $amountPaid >= $elearningWaiting->money ? '1' : '2';
            DB::table('tbl_elearning_waiting')->where('id', $elearningWaiting->id)->update([
                'status' =>  $statusPayment,
                'money_payment' => $amountPaid
            ]);

            $ktElearningUnlock = ElearningUnlock::where('id_client', $elearningWaiting->id_client)->where('id_elearning', $elearningWaiting->id_elearning)->first();
            if(empty($ktElearningUnlock->id)) {
                $ktElearningUnlock = new ElearningUnlock();
                $ktElearningUnlock->id_client = $elearningWaiting->id_client;
                $ktElearningUnlock->id_elearning = $elearningWaiting->id_elearning;
                $ktElearningUnlock->save();
            }

            /** =========================
             *  UPDATE UPGRADE PACKAGE
             *  ========================= */
            if(!empty($ktElearningUnlock->id)) {
                SocketHelpersAdmin::sendSocketToClient($elearningWaiting->id_client, [
                    'title' => lang('notification'),
                    'message' => lang('dt_challenge_payment_successful'),
                    'id' => $elearningWaiting->id,
                    'id_elearning' => $elearningWaiting->id_elearning,
                    'status_payment' => $elearningWaiting->status,
                    'url_detail' => $this->url . '/api/video/detail_elearning/' . $elearningWaiting->id_elearning,
                ], 'unlock_elearning');
            }
        }

        //        Log::info('pay2s', [
        //            'success' => true,
        //            'message' => 'Transactions processed successfully'
        //        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transactions processed successfully'
        ], 200);
    }

    public function test_unlock_video($code = 'ELN-1773383404', $money = '1000000')
    {
        $data = [
            "transactions" => [
                [
                    "id" => time(),
                    "gateway" => "MBB",
                    "transactionDate" => "2026-01-08 12:34:37",
                    "transactionNumber" => "FT26008000642065",
                    "vaNumber" => "963869",
                    "accountNumber" => "95652868",
                    "content" => $code,
                    "transferType" => "IN",
                    "transferAmount" => $money,
                    "checksum" => "b9cc91651582ddbf1568e376ec0dd555"
                ]
            ]
        ];

        $request = new Request();
        $request->merge(['data' => json_encode($data)]);
        $request->merge(['local' => true]);

        return $this->WebhookPay2sUnlockVideo($request);
    }
}
