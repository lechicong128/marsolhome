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

class Pay2sController extends AuthController
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
        $this->Url = config('services.url');
        $this->dbService = $serviceService;
    }

    public function createQRPay2s() {
        $info = $this->request->input('info');
        $code = $info['code'];
        $amount = $info['amount'];

        return self::generateQR($code, $amount, $this->Url, $this->baseUrl);
    }

    /**
     * Generate QR code Pay2s (được gọi tự internal, không qua request)
     *
     * @param string $code   Mã tham chiếu (booking code / payment code)
     * @param float  $amount Số tiền
     * @param string $baseUrl URL website
     * @param string $storageUrl URL storage
     * @return array { qr, bank } hoặc ['qr' => null, 'message' => '...']
     */
    public static function generateQR(string $code, float $amount, string $baseUrl = '', string $storageUrl = ''): array
    {
        $account_bank        = get_option('pay2s_account_bank');
        $account_number      = get_option('pay2s_account_number');
        $account_name        = get_option('pay2s_account_name');
        $account_bank_short  = get_option('pay2s_account_bank_short');
        $account_bank_long   = get_option('pay2s_account_bank_long');
        $logo_bank           = get_option('pay2s_logo_bank');
        $account_number_show = get_option('pay2s_account_number_show');
        $account_name_show   = get_option('pay2s_account_name_show');

        $resultQr = createQrBank([
            'bankShortName' => $account_bank,
            'accountNumber' => $account_number,
            'accountName'   => $account_name,
            'amount'        => ceil($amount),
            'memo'          => $code,
            'is_mask'       => 0,
        ]);
        if (empty($resultQr)) {
            return ['qr' => null, 'message' => 'Tạo mã QR thất bại'];
        }

        return [
            'qr'   => $resultQr,
            'bank' => [
                'account_bank'     => $account_name_show,
                'account_number'   => $account_number_show,
                'account_name'     => $account_bank_short,
                'amount'           => ceil($amount),
                'note'             => $code,
                'account_name_long'=> $account_bank_long,
                'logo_bank'        => rtrim($baseUrl, '/') . '/' . $logo_bank,
            ],
        ];
    }


    public function createQRPayment() {
        $info = $this->request->input('info');
        $code = $info['code'];
        $amount = $info['amount'];
        $data =  createPay2SPayment([
            'code' => $code,
            'amount' => $amount,
        ]);
        if(is_numeric($data['resultCode']) && ($data['resultCode'] == '0' || $data['resultCode'] == '9000')) {
            return response()->json([
                'result' => 1,
                'message' => 'Tạo QR Payment thành công',
                'data' => $data,
            ]);
        }
        else {
            return response()->json([
                'result' => 0,
                'message' => 'Tạo QR Payment không thành công',
            ]);
        }
    }

}
