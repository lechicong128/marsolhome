<?php
namespace App\Libraries;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\AdminService;
class Invoice
{
    protected $key = "";
    protected $env = "test";
    protected $baseURL = array(
        'test' => 'https://demoapi-xbill.xcyber.vn/xbill/',
        'live' => 'https://api-xbill.xcyber.vn/xbill/',
    );
    protected $URI = array(
        'GetExternalToken' => 'api/services/hddtws/Authentication/GetExternalToken',
        'GuiHoadonGoc' => 'api/services/hddtws/GuiHoadon/GuiHoadonGoc',
        'TaiHoaDonPDF' => 'api/services/hddtws/GuiHoadon/TaiHoaDonPDF',
        'GuiHoadonHuyBo' => 'api/services/hddtws/GuiHoadon/GuiHoadonHuyBo',
    );
    protected $user = array(
        'test' => [
            'doanhnghiep_mst' => '0105232093-999',
            'username' => 'xbill2025@yopmail.com',
            'password' => 'i@6&WSc2',
            'kyhieu' => 'C26MEN',
            'mauso' => 1,
            'loaihoadon_ma' => 1,
            'hoadon_loai' => 7
        ],
        'live' => [
            'doanhnghiep_mst' => '0318140993',
            'username' => 'Hathanhphong29@gmail.com',
            'password' => '123456Aa@',
            'kyhieu' => 'C26MEN',
            'mauso' => 1,
            'loaihoadon_ma' => 1,
            'hoadon_loai' => 7
        ],
    );
    protected $token;
    public function __construct()
    {
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
        $this->token = (new AdminService())->get_option('token_invoice');
    }

    public function getExternalToken()
    {
        $url = $this->baseURL[$this->env] . $this->URI['GetExternalToken'];
        $result = $this->sendRequestToInvoice(['doanhnghiep_mst' => $this->user[$this->env]['doanhnghiep_mst'], 'username' => $this->user[$this->env]['username'], 'password' => $this->user[$this->env]['password']],'POST', $url);
        return $result;
    }

    public function guiHoadonGoc($data)
    {
        $data['doanhnghiep_mst'] = $this->user[$this->env]['doanhnghiep_mst'];
        $data['loaihoadon_ma'] = $this->user[$this->env]['loaihoadon_ma'];
        $data['mauso'] = $this->user[$this->env]['mauso'];
        $data['kyhieu'] = $this->user[$this->env]['kyhieu'];
        $url = $this->baseURL[$this->env] . $this->URI['GuiHoadonGoc'];
        $result = $this->sendRequestToInvoice($data,'POST', $url);
        return json_decode($result);
    }

    public function TaiHoaDonPDF($data)
    {
        $data['doanhnghiep_mst'] = $this->user[$this->env]['doanhnghiep_mst'];
        $url = $this->baseURL[$this->env] . $this->URI['TaiHoaDonPDF'];
        $result = $this->sendRequestToInvoice($data,'POST', $url);
        return json_decode($result);
    }

    public function GuiHoadonHuyBo($data)
    {
        $data['doanhnghiep_mst'] = $this->user[$this->env]['doanhnghiep_mst'];
        $data['hoadon_loai'] = $this->user[$this->env]['hoadon_loai'];
        $data['loaihoadon_ma'] = $this->user[$this->env]['loaihoadon_ma'];
        $data['mauso'] = $this->user[$this->env]['mauso'];
        $data['kyhieu'] = $this->user[$this->env]['kyhieu'];
        $url = $this->baseURL[$this->env] . $this->URI['GuiHoadonHuyBo'];
        $result = $this->sendRequestToInvoice($data,'POST', $url);
        return json_decode($result);
    }

    private function sendRequestToInvoice($data,$method = 'GET', $url)
    {
        $data_string = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string),
                'Authorization: Bearer ' . $this->token
            )
        );
        $result = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return $result;
    }
}

?>
