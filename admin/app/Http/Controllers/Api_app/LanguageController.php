<?php

namespace App\Http\Controllers\Api_app;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Language;
use App\Traits\UploadFile;

class LanguageController extends AuthController
{

    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getLanguageCurrent() {
        $baseUrl = config('services.storage.url');
        $language = Language::orderByRaw('is_default desc')->get();
        foreach($language as $key => $lang) {
            if (!empty($lang->image)) {
                $language[$key]->image = $baseUrl .'/'. $lang->image;
            }
        }

        return response()->json([
            'result' => true,
            'message' => 'Lấy danh sách thành công',
            'data' => $language,
        ]);
    }

    public function getLanguageCurrentAPP() {
        $baseUrl = config('services.storage.url');
        $language = Language::orderByRaw('is_default desc')->get();
        foreach($language as $key => $lang) {
            if (!empty($lang->image)) {
                $language[$key]->image = $baseUrl .'/'. $lang->image;
            }
            $dataLang = getLangAppDataFlatTest($lang->code);
            $language[$key]->data = $dataLang ? json_decode($dataLang, true) : new \stdClass();
        }

        return response()->json([
            'result' => true,
            'message' => lang('c_get_info_success'),
            'data' => $language,
        ]);
    }

    public function getLangApp() {
        $_locale = $this->request->header('locale');
        if(empty($_locale)) {
            $_locale = $this->request->input('_locale');
        }
        if(empty($_locale)) {
            $_locale = 'vi';
        }
        return response()->json([
            'result' => true,
            'message' => 'Lấy ngôn ngữ thành công',
            'data' => getLangAppDataFlatTest($_locale),
        ]);
    }




    public function test() {
//        $_locale = $this->request->header('locale');
//        if(empty($_locale)) {
//            $_locale = $this->request->_locale;
//        }
//        $_locale = 'vi';
//
//        dd(\View::share('langApp', getLangAppDataFlat($_locale)));
//        dd(fetJSONLang($this->request));
//        $oldMaxExec = ini_get('max_execution_time');
//        ini_set('max_execution_time', '1200');
//        $ffmpegPath = '/usr/bin/ffmpeg'; // thay bằng kết quả lệnh `which ffmpeg`
//        $input = storage_path('app/public/review/1.mov');
////        $input = storage_path('review/5/4meFNYGsXb___1747066889667_wid_NjgyMjIwMDkzZjJiOTAwMDU4OGMxYzJi.mp4');
//        $output = storage_path('app/public/review/small_1.mov');
//
//        $this->compressVideo($input, $output, 28);


//        $cmd = $ffmpegPath
//            . ' -y -i ' . escapeshellarg($input)
//            . ' -vcodec libx264 -crf 28 -preset veryfast -acodec aac '
//            . escapeshellarg($output)
//            . ' 2>&1';
//
//        $output = shell_exec($cmd);
//
//        if ($output === null) {
//            echo "Không gọi được lệnh shell_exec(). Có thể host chặn hàm này.";
//        } else {
//            if (stripos($output, 'ffmpeg version') !== false) {
//                echo "ĐÃ CÀI FFmpeg<br><pre>$output</pre>";
//            } else {
//                echo "KHÔNG TÌM THẤY FFmpeg hoặc lệnh ffmpeg không chạy được.<br><pre>$output</pre>";
//            }
//        }
//
//
//
//
//        // escape path cho an toàn
//        $inputEscaped  = escapeshellarg($input);
//        $outputEscaped = escapeshellarg($output);
//
//        $cmd = "ffmpeg -y -i $inputEscaped -vcodec libx264 -crf 28 -preset veryfast -acodec aac $outputEscaped 2>&1";
//
//        exec($cmd, $outputLog, $returnVar);
//
//        if ($returnVar === 0) {
//            echo "Nén video thành công!";
//        } else {
//            echo "Lỗi khi nén video:\n";
//            echo implode("\n", $outputLog);
//        }

    }



}
