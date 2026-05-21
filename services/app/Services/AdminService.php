<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AdminService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.admin.base_url'), '/');
    }

    public function getOrderRef($ref = '')
    {
        $response = Http::get("{$this->baseUrl}/api/getOrderRef/{$ref}");
        $response = $response->json();
        return $response;
    }

    public function updateOrderRef($ref = '')
    {
        $response = Http::get("{$this->baseUrl}/api/getOrderRef/{$ref}");
        $response = $response->json();
        return $response;
    }
    public function GetProducts($id_product, $_locale = 'vi')
    {
        $response = Http::get("{$this->baseUrl}/api/products/getListDetailShort",[
            'id_product' => $id_product,
            '_locale' => $_locale,
        ]);
        $response = $response->json();
        return $response;
    }


}
