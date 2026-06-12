<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ProvinceResource;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Socket;

class SocketController extends AuthController
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->baseUrlAdmin = config('services.storage.url');
        $this->baseUrl = config('services.storage.url');
        $this->socket = new Socket();
    }

    public function login_socket(){
        $user_id = $this->request->input('user_id');
        $user_name = $this->request->input('user_name');
        $db_name = $this->request->input('db_name');
        if ($user_id && $user_name)
        {
            $result = $this->socket->login(['user_id' => $user_id, 'user_name' => $user_name,'db_name' => $db_name]);
            if (isset($result) && !empty($result)) {
                $data = [
                    'status' => true,
                    'db_name' => $db_name,
                    'sever' => $this->socket->socket_link_connect,
                    'message' => 'Login successful',
                    'data' => $result
                ];
            } else {
                $data = [
                    'status' => false,
                    'message' => 'Login failed',
                    'data' => null
                ];
            }
            return response()->json($data);
        } else {
            $data = [
                'status' => false,
                'message' => 'Invalid input data',
                'data' => null
            ];
            return response()->json($data);
        }
    }

    public function sendSocket() {
        $channels = $this->request->post('channels');
        $event = $this->request->post('event');
        $data = $this->request->post('data');
        if ($event) {
            $result = sendSocket($data, $channels, $event);
            if (isset($result) && !empty($result['result'])) {
                $data = [
                    'status' => true,
                    'message' => 'Notification sent successfully',
                    'data' => $result['result']
                ];
            } else {
                $data = [
                    'status' => false,
                    'message' => 'Failed to send notification',
                    'data' => null
                ];
            }
            return response()->json($data);
        } else {
            $data = [
                'status' => false,
                'message' => $channels,
                'data' => null
            ];
            return response()->json($data);
        }
    }

    public function test()
    {
        $result = sendSocket(['new_notification' => 1], [], 'loadCountPostALL');
        dd($result);
    }
}
