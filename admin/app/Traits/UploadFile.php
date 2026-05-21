<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Img;
use Maestroerror\HeicToJpg;

trait UploadFile
{
    public function UploadFile($file = array(), $folder = null,$with = 150,$height = 150,$resize = true,$disk = 'public')
    {
//        print_r(phpinfo());die();
//        dd(gd_info());
        $arr = ['png','jpeg','jpg','gif','jfif','ai','AI','raw','RAW','EPS','eps','indd','INDD','PNG','JPEG','JPG','GIF','JFIF','webp','WEBP'];
        $arrFile = ['docx','DOCX','doc','DOC','Doc','XLSX','xlsx','XLS','xls','CSV','csv','PDF','pdf','HEIC','heic', 'heif', 'HEIF','svg','SVG','mp4','MP4','mov','MOV','avi','AVI','flv','FLV','wmv','WMV','mkv','MKV'];
        if (is_array($file)){
            $arrPath = [];
            foreach ($file as $key => $value){
                $FileName = Str::random(10);
                $file_name =  $FileName . "___" . $this->convert_vi_to_en($value->getClientOriginalName());
                $file_name = preg_replace('/[^A-Za-z0-9\s\.\-]/', '', $file_name);
                $file_name = preg_replace('/\s+/', '_', $file_name);
                $filetype = $value->getClientOriginalExtension();
                $filetype = strtolower($filetype);
                $fileName = $disk.'/'.$folder .'/'. $file_name;
                if (in_array($filetype,$arr)) {
                    if (!Storage::disk($disk)->exists($folder)) {
                        Storage::disk($disk)->makeDirectory($folder);
                    }
                    $file = Img::make($value)->orientate();
                    if (!empty($resize)) {
                        $path = $file->resize($with, $height, function ($const) {
                            $const->aspectRatio();
                        })->save(storage_path('app/' . $fileName));
                    } else {
                        $path = $file->save(storage_path('app/' . $fileName));
                    }
                    array_push($arrPath, $folder . '/' . $path->basename);
                } else {
                    if (in_array($filetype,$arrFile)) {
                        $path = $value->storeAs($folder, $file_name, $disk);
                        array_push($arrPath, $path);
                    } else {
                        if (empty($filetype)){
                            $path = Img::make($value)->orientate()->save(storage_path('app/' . $fileName));
                            array_push($arrPath, $folder . '/' . $path->basename);
                        }
                    }
                }
            }
            $arrPath = implode('||',$arrPath);
            return trim($arrPath,'||');
        }
        else {
            $FileName = Str::random(10);
            $filetype = $file->getClientOriginalExtension();
            $filetype = strtolower($filetype);
            $file_name = $FileName . "___" . $this->convert_vi_to_en($file->getClientOriginalName());
            $file_name = preg_replace('/[^A-Za-z0-9\s\.\-]/', '', $file_name);
            $file_name = preg_replace('/\s+/', '_', $file_name);

            $fileName = $disk.'/'.$folder.'/'. $file_name;
            if (in_array($filetype,$arr)){
                if (!Storage::disk($disk)->exists($folder)) {
                    Storage::disk($disk)->makeDirectory($folder);
                }
                $file = Img::make($file)->orientate();
                if (!empty($resize)){
                    $path = $file->resize($with,$height, function ($const) {
                        $const->aspectRatio();
                    })->save(storage_path('app/' . $fileName));
                } else {
                    $path = $file->save(storage_path('app/' . $fileName));
                }
                return $folder.'/'.$path->basename;
            } else {
                if (in_array($filetype,$arrFile)) {
                    $path = $file->storeAs($folder, $file_name, $disk);
                    return $path;
                } elseif (empty($filetype)){
                    $path = Img::make($file)->orientate()->save(storage_path('app/' . $fileName));
                    return $folder . '/' . $path->basename;
                } else {
                    return null;
                }
            }
        }
    }

    public function deleteFile($path, $disk = 'public')
    {
        Storage::disk($disk)->delete($path);
    }

    public function UploadFileS3($file = array(), $folder = null,$with = 150,$height = 150,$resize = true,$disk = 'public'){
        $arr = ['png','jpeg','jpg','gif','jfif','ai','AI','raw','RAW','EPS','eps','indd','INDD','PNG','JPEG','JPG','GIF','JFIF','webp','WEBP'];
        if (is_array($file)){
            $arrPath = [];
            foreach ($file as $key => $value){
                $FileName = Str::random(10);
                $file_name =  $FileName . "___" . $this->convert_vi_to_en($value->getClientOriginalName());
                $fileName = $folder.'/'. $file_name;
                $filetype = $value->getClientOriginalExtension();
                if (in_array($filetype,$arr)){
                    if (!empty($resize)){
                        $image =  Img::make($value)->resize($with,$height, function ($const) {
                            $const->aspectRatio();
                        });
                        $imageData = $image->stream();
                        Storage::disk('s3')->put($fileName, $imageData->__toString(),'public');
                        $url = Storage::disk('s3')->url($fileName);
                    } else {
                        $path = $value->storeAs($folder, $file_name, 's3');
                        Storage::disk('s3')->setVisibility($path, 'public');
                        $url = Storage::disk('s3')->url($path);
                    }
                    array_push($arrPath, $url);
                } else {
                    $path = $value->storeAs($folder, $file_name, 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');
                    $url = Storage::disk('s3')->url($path);
                    array_push($arrPath, $url);
                }
            }
            $arrPath = implode('||',$arrPath);
            return trim($arrPath,'||');
        } else {

            $FileName = Str::random(10);
            $filetype = $file->getClientOriginalExtension();
            $file_name = $FileName . "___" . $this->convert_vi_to_en($file->getClientOriginalName());
            $fileName = $folder.'/'. $file_name;
            if (in_array($filetype,$arr)){
                if (!empty($resize)){
                    $image =  Img::make($file)->resize($with,$height, function ($const) {
                        $const->aspectRatio();
                    });
                    $imageData = $image->stream();
                    Storage::disk('s3')->put($fileName, $imageData->__toString(),'public');
                    $url = Storage::disk('s3')->url($fileName);
                } else {
                    $path = $file->storeAs($folder, $file_name, 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');
                    $url = Storage::disk('s3')->url($path);
                }
                return $url;
            } else {
                $path = $file->storeAs($folder, $file_name, 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $url = Storage::disk('s3')->url($path);
                return $url;
            }
        }
    }

    public function deleteFileS3($path, $disk = 's3')
    {
        $filePath = parse_url($path, PHP_URL_PATH);
        $filePath = ltrim($filePath, '/');
        Storage::disk($disk)->delete($filePath);
    }

    public function getImageS3($path, $disk = 's3')
    {
        $imageName = parse_url($path, PHP_URL_PATH);
        $imageName = ltrim($imageName, '/');
        $imageName = str_replace('%20',' ',$imageName);
        $cacheKey = 'image_' . $imageName;
        // Kiểm tra cache trước khi tải ảnh từ S3
        $image = Cache::remember($cacheKey, 3600, function () use ($imageName) {
            return Storage::disk('s3')->temporaryUrl(
                $imageName, now()->addMinutes(60)
            );
        });
        return $image;
    }

    public function convert_vi_to_en($str)
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

    function GetDurationVideo(string $inputPath): int
    {
        ini_set('max_execution_time', '1200');
        ignore_user_abort(true);

        // 1️⃣ Kiểm tra file tồn tại
        if (!is_file($inputPath)) {
            return 0;
        }

        // 2️⃣ Kiểm tra ffprobe có tồn tại không
        $check = @shell_exec('ffprobe -version 2>&1');
        if ($check === null || stripos($check, 'ffprobe version') === false) {
            return 0;
        }

        // 3️⃣ Lấy duration
        $cmd = "ffprobe -v error -show_entries format=duration "
            . "-of default=noprint_wrappers=1:nokey=1 "
            . escapeshellarg($inputPath) . " 2>&1";

        $output = shell_exec($cmd);

        if ($output === null) {
            return 0;
        }

        return (int) floor((float) trim($output));
    }

    public function compressVideo(string $inputPath, string $outputPath, int $crf = 28): void
    {
        // Cho function này chạy lâu hơn nếu cần (20 phút)
        ini_set('max_execution_time', '1200');
        ignore_user_abort(true);

        $ffmpeg = 'ffmpeg'; // Trong container đã có ffmpeg trong PATH

        // 1. Kiểm tra ffmpeg có dùng được không
        //    (nếu bị chặn shell_exec thì $checkOutput vẫn null → coi như không dùng được)
        $checkOutput = @shell_exec($ffmpeg . ' -version 2>&1');

        if ($checkOutput === null || stripos($checkOutput, 'ffmpeg version') === false) {
            // Không tìm thấy / không chạy được ffmpeg → bỏ qua, không throw
            // Có thể log lại nếu muốn:
            // error_log('[compressVideo] FFmpeg not available, skip compress.');
            return;
        }

        // 2. Kiểm tra file input có tồn tại không
        if (!is_file($inputPath)) {
            // File nguồn không tồn tại → bỏ qua luôn
            // error_log('[compressVideo] Input file not found: ' . $inputPath);
            return;
        }

        // 3. Đảm bảo thư mục output tồn tại
        $outDir = dirname($outputPath);
        if (!is_dir($outDir)) {
            @mkdir($outDir, 0775, true);
        }

        // 4. Lệnh nén video
        $cmd = $ffmpeg
            . ' -y -i ' . escapeshellarg($inputPath)
            . ' -vcodec libx264 -crf '.$crf.' -preset veryfast'
            . ' -acodec aac '
            . escapeshellarg($outputPath)
            . ' 2>&1';

        $outputLog = [];
        $returnVar = 0;

        exec($cmd, $outputLog, $returnVar);

        // Ghép log lại cho dễ debug nếu cần
        $logText = implode("\n", $outputLog);

        // 5. Nếu lỗi, check xem có phải kiểu "ffmpeg: not found" không
        if ($returnVar !== 0) {
            // Nếu trong log có 'ffmpeg: not found' hoặc 'command not found'
            // thì coi như không có ffmpeg → bỏ qua, không throw
            if (
                stripos($logText, 'ffmpeg: not found') !== false ||
                stripos($logText, 'command not found') !== false
            ) {
                // error_log('[compressVideo] FFmpeg command not found at runtime, skip. Log: ' . $logText);
                return;
            }

            // Các lỗi khác: throw để biết có vấn đề thực sự (file hỏng, quyền ghi, v.v.)
            //            throw new \RuntimeException(
            //                "FFmpeg failed (code {$returnVar})\n" . $logText
            //            );
        }
        return;
        // Nén thành công → nếu muốn log:
        // error_log('[compressVideo] Compressed OK: ' . $inputPath . ' -> ' . $outputPath);
    }

    public function ImagecompressVideo(string $inputPath, string $outputPath): void
    {
        // Cho function này chạy lâu hơn nếu cần (20 phút)
        ini_set('max_execution_time', '1200');
        ignore_user_abort(true);
        $thumbTime   = '00:00:01';
        $ffmpeg = 'ffmpeg'; // Trong container đã có ffmpeg trong PATH

        // 1. Kiểm tra ffmpeg có dùng được không
        //    (nếu bị chặn shell_exec thì $checkOutput vẫn null → coi như không dùng được)
        $checkOutput = @shell_exec($ffmpeg . ' -version 2>&1');
        if ($checkOutput === null || stripos($checkOutput, 'ffmpeg version') === false) {
            // Không tìm thấy / không chạy được ffmpeg → bỏ qua, không throw
            // Có thể log lại nếu muốn:
            // error_log('[compressVideo] FFmpeg not available, skip compress.');
            return;
        }

        // 2. Kiểm tra file input có tồn tại không
        if (!is_file($inputPath)) {
            // File nguồn không tồn tại → bỏ qua luôn
            // error_log('[compressVideo] Input file not found: ' . $inputPath);
            return;
        }

        // 3. Đảm bảo thư mục output tồn tại
        $outDir = dirname($outputPath);

        if (!is_dir($outDir)) {
            @mkdir($outDir, 0775, true);
        }

        // 4. Lệnh nén video
//        $cmd = $ffmpeg
//            . ' -y -i ' . escapeshellarg($inputPath)
//            . ' -vcodec libx264 -crf '.$crf.' -preset veryfast'
//            . ' -acodec aac '
//            . escapeshellarg($outputPath)
//            . ' 2>&1';


        $cmdThumb = $ffmpeg
            . ' -ss ' . escapeshellarg($thumbTime)
            . ' -i ' . escapeshellarg($inputPath)
            . ' -frames:v 1 -q:v 2 '
            . escapeshellarg($outputPath)
            . ' 2>&1';

        $outputLog = [];
        $returnVar = 0;

        exec($cmdThumb, $outputLog, $returnVar);

        // Ghép log lại cho dễ debug nếu cần
        $logText = implode("\n", $outputLog);


        // 5. Nếu lỗi, check xem có phải kiểu "ffmpeg: not found" không
        if ($returnVar !== 0) {
            // Nếu trong log có 'ffmpeg: not found' hoặc 'command not found'
            // thì coi như không có ffmpeg → bỏ qua, không throw
            if (
                stripos($logText, 'ffmpeg: not found') !== false ||
                stripos($logText, 'command not found') !== false
            ) {
                // error_log('[compressVideo] FFmpeg command not found at runtime, skip. Log: ' . $logText);
                return;
            }

            // Các lỗi khác: throw để biết có vấn đề thực sự (file hỏng, quyền ghi, v.v.)
            //            throw new \RuntimeException(
            //                "FFmpeg failed (code {$returnVar})\n" . $logText
            //            );
        }
        return;
        // Nén thành công → nếu muốn log:
        // error_log('[compressVideo] Compressed OK: ' . $inputPath . ' -> ' . $outputPath);
    }


}
