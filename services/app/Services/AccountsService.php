<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AccountsService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.accounts.base_url'), '/');
    }

    public function GetCurl($url, $_locale = 'vi')
    {
        $response = Http::get("{$this->baseUrl}/{$url}",[
            '_locale' => $_locale,
        ]);
        $response = $response->json();
        return $response;
    }

    public function getListID($url, $data = [])
    {
        $request = new Request();
        foreach($data as $key => $value) {
            $request->merge([$key => $value]);
        }
        $response = Http::get("{$this->baseUrl}/{$url}",$request);
        $response = $response->json();
        return $response;
    }
}
