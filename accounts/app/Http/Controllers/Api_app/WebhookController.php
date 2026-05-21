<?php

namespace App\Http\Controllers\Api_app;

use App\Helpers\SocketHelpers;
use App\Http\Controllers\Api_app\Controller;
use App\Http\Controllers\Log;
use App\Http\Resources\ChallengeMeResources;
use App\Models\RankCommunity;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Clients;
use App\Models\Promotion;
use App\Models\Challenge;
use App\Models\ChallengeMe;
use App\Models\TransactionPayment;
use App\Models\Transaction;
use App\Traits\RequestServiceTrait;
use App\Libraries\Invoice;

use function Laravel\Prompts\table;

class WebhookController extends Controller
{
    use RequestServiceTrait;
    protected $AdminService;
    protected $invoice;
    public function __construct(Request $request, AdminService $adminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->AdminService = $adminService;
        $this->invoice = new Invoice();
        DB::enableQueryLog();
    }

    public function WebhookPay2s(Request $request) {
        $expectedToken = $this->AdminService->get_option('pay2s_token_webhook');

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
            $reference_no = extractAndNormalizeChallengeCode($transaction['content']);
            $money = $transaction['transferAmount'];

            DB::table('tbl_history_payment')->insert([
                'code_payment' => $transaction['transactionNumber'] ?? NULL,
                'code' => $reference_no ?? NULL,
                'money' => $money,
                'type' => 'pay2s',
                'data_json' => json_encode($transaction),
            ]);

            if (!$reference_no) {
                continue;
            }


            $ChallengeWaiting = DB::table('tbl_challenge_waiting')->where('reference_no', $reference_no)->first();

            if (!$ChallengeWaiting || !in_array($ChallengeWaiting->status, ['0'])) {
                continue;
            }


            $amountPaid = $ChallengeWaiting->deposit_payment + $money;
            $statusPayment = $amountPaid >= $ChallengeWaiting->deposit ? '1' : '2';
            $ChallengeWaiting->status = $statusPayment;
            DB::table('tbl_challenge_waiting')->where('id', $ChallengeWaiting->id)->update([
               'status' =>  $statusPayment,
               'deposit_payment' => $amountPaid
            ]);

            $createChallengeMe = $this->CreateChallengeMeFromWaiting($ChallengeWaiting->id, 'pay2s', ($transaction['transactionNumber'] ?? NULL));

            /** =========================
             *  UPDATE UPGRADE PACKAGE
             *  ========================= */
            if($createChallengeMe) {
                SocketHelpers::sendSocketPaymentChallenge($createChallengeMe->client_id, [
                    'title' => lang('notification'),
                    'message' => lang('dt_challenge_payment_successful'),
                    'id' => $createChallengeMe->id,
                    'id_event_articles' => $createChallengeMe->challenge->id_event_articles,
                    'id_challenge' => $createChallengeMe->id_challenge,
                    'status_payment' => $createChallengeMe->status_payment,
                ]);
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

    private function CreateChallengeMeFromWaiting($id_waiting = '', $type_pay = 'pay2s', $transactionNumber = NULL){

        $ChallengeWaiting = DB::table('tbl_challenge_waiting')->where('id', $id_waiting)->first();


        $input = $this->request->all();
        $customer_id = $ChallengeWaiting->id_client;
        if (empty($customer_id)) {
            return response()->json([
                'title' => lang('notification'),
                'data' => [],
                'result' => false,
                'message' => lang('dt_login_use_app')
            ]);
        }
        // kiểm tra đã tham gia challenge này chưa
        $ktChallenge = ChallengeMe::where('client_id', $customer_id)->where(
            'id_challenge',  $ChallengeWaiting->id_challenge
        )->where('status', 0)->exists();
        if ($ktChallenge) {
            return response()->json([
                'result' => false,
                'title' => lang('notification'),
                'message' => lang('dt_already_joined_challenge')
            ], 409);
        }
        DB::beginTransaction();
        try {
            $challenge = Challenge::find($ChallengeWaiting->id_challenge);
            $reference_no = $this->AdminService->getOrderRef('challengme')['reference_no'];
            // tạo record chính
            $challengeMe = new ChallengeMe();
            // dùng customer_id cho nhất quán với phần còn lại của controller
            $challengeMe->client_id = $customer_id;
            $challengeMe->id_challenge = $ChallengeWaiting->id_challenge;
            $challengeMe->reference_no = $reference_no;
            // chuẩn hoá ngày bắt đầu
            $baseDate = date('Y-m-d H:i:s');
            $challengeMe->date = $baseDate;
            // các trường bổ sung
            $challengeMe->status = (Config::get('constant')['status_request'] ?? 0);

            $challengeMe->deposit = $ChallengeWaiting->deposit_payment;
            $challengeMe->payment_mode_id = $ChallengeWaiting->payment_mode_id;

            $challengeMe->completion_rate = 0;
            $challengeMe->haru_xu = $challenge->coin_success ?? 0;
            $challengeMe->total_haru_xu = 0;
            // lấy số ngày (days) từ bảng tbl_challenge để tính date_challenge
            $days = (int)DB::table('tbl_challenge')->where('id', $ChallengeWaiting->id_challenge)->value('days') ? : 0;
            if ($days > 0) {
                $dt = new \DateTime($baseDate);
                $dt->modify("+{$days} days");
                // lưu dạng date (Y-m-d) như cấu trúc DB
                $challengeMe->date_challenge = $dt->format('Y-m-d');
            } else {
                $challengeMe->date_challenge = null;
            }

            $challengeMe->status_payment = 'success';
            $challengeMe->deposit_payment = $ChallengeWaiting->deposit_payment;

            $challengeMe->save();
            $this->AdminService->updateOrderRef('challengme');

            DB::table('tbl_history_payment_challenge')->insert([
                'id_challenge' => $challengeMe->id_challenge,
                'id_challenge_me' => $challengeMe->id,
                'id_client' => $customer_id,
                'code_payment' => $transactionNumber ?? NULL,
                'money' => $challengeMe->deposit,
                'payment_mode_id' => $challengeMe->payment_mode_id ?? 0,
                'type_pay' => $type_pay ?? 'pay2s',
            ]);

            $challenge->quantity_joined = ($challenge->quantity_joined ?? 0) + 1; // cập nhật số lượng tham gias
            $challenge->save();
            DB::commit();
            // load lại quan hệ để trả về đầy đủ dữ liệu
            $challengeMe = ChallengeMe::with(['customer', 'challenge'])->find($challengeMe->id);
            return new ChallengeMeResources($challengeMe);
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    public function WebhookPaymentPay2s(Request $request) {
        $expectedToken = $this->AdminService->get_option('pay2s_token_webhook_transaction');
        /** =========================
         *  CHECK AUTHORIZATION
         *  ========================= */
        $authHeader = $request->header('Authorization');
           \Log::error('pay2s', [
               'success' => false,
               'message' => json_encode($authHeader)
           ]);
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
            $reference_no = extractAndNormalizePaymentCode($transaction['content']);

            $money = $transaction['transferAmount'];
            DB::table('tbl_history_payment')->insert([
                'code_payment' => $transaction['transactionNumber'] ?? NULL,
                'code' => $reference_no ?? NULL,
                'money' => $money,
                'type' => 'pay2s',
                'data_json' => json_encode($transaction),
            ]);

            if (!$reference_no) {
                continue;
            }


            $TransactionPayment = TransactionPayment::where('reference_no', $reference_no)->first();
            if (!$TransactionPayment || !in_array($TransactionPayment->status, ['0'])) {
                continue;
            }
            $amountPaid = $TransactionPayment->amount_payment + $money;
            $statusPayment = $amountPaid >= $TransactionPayment->amount ? '1' : '2';
            $TransactionPayment->status = $statusPayment;
            $TransactionPayment->amount_payment = $amountPaid;
            $TransactionPayment->save();

            $successPayment = $this->changeStatusTransaction($TransactionPayment->id_transaction, 4);
            if($TransactionPayment->status == 1) {
                $transaction = Transaction::find($TransactionPayment->id_transaction);
                $transaction->warning_payment = 0;
                $transaction->save();
                $successPayment = $this->changeStatusTransaction($TransactionPayment->id_transaction, 4);
                if(!empty($successPayment)){
                    SocketHelpers::sendSocketToClient($TransactionPayment->customer_id, [
                        'title' => lang('notification'),
                        'message' => lang('dt_orders_payment_successful'),
                    ], 'payment_transaction');
                }
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Transactions processed successfully',
            'data' => $successPayment ?? []
        ], 200);
    }


    private function changeStatusTransaction($transaction_id = 0, $status = 0){
        $request = new Request();
        $request->merge(['status' => $status]);
        $request->merge(['staff_status' => 0]);
        $request->merge(['transaction_id' => $transaction_id]);
        $baseUrl = rtrim(config('services.accounts.base_url'), '/');
        try {
            $response = $this->sendRequestToService(
                'post',
                "{$baseUrl}/api/transaction/changeStatus",
                $request,
                ['token' => 'nglow']
            );
            if (!$response->successful()) {
                return false;
            }
            return $response->json();

        } catch (\Exception $e) {
            return false;
        }
    }

    public function cronLoginInvoice() {
        $result = $this->invoice->getExternalToken();
        $result = json_decode($result);
        if(!empty($result->result->access_token)) {
            $this->AdminService->updateOption('token_invoice', $result->result->access_token);
        }
        return response()->json(['result' => true, 'message' => 'Lấy token thành công', 'data' => $result]);
    }
}
