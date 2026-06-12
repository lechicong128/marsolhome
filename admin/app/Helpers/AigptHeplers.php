<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AigptHeplers
{
    
    public static function getChatbotResponse($prompt) {
    
        $apiKey = get_option('gpt_api_key');

        $messages = [];
        if (is_array($prompt)) {
            $messages = $prompt;
        } else {
            $messages = [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ];
        }

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'gpt-4o-mini',
                'temperature' => 0.1,
                'messages' => $messages
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $json = json_decode($response, true);
        return $json['choices'][0]['message']['content'] ?? 'GPT không phản hồi.';
    }
}