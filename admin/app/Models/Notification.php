<?php

namespace App\Models;

use App\Models\SignUpReview;
use App\Traits\NotificationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\Mime\Email;

class Notification extends Model
{
    use HasFactory, NotificationTrait;

    protected $table = 'tbl_notification';

    function notification_staff()
    {
        return $this->hasMany('App\Models\NotificationStaff', 'notification_id', 'id');
    }

    function notification_tran()
    {
        return $this->hasMany('App\Models\NotificationTran', 'notification_id', 'id');
    }

    static function notifyLikePost($customer_id, $dtData = [],$type = 'notifyLikePost')
    {

        if (!empty($dtData)) {
            $arr_object_id = array_values($dtData['arr_object_id']);

            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(
                    ['customer' => $customer_id, 'object' => $dtData['object_type'], 'status' => $type, 'home_id' => $dtData['home_id'], 'customer' => $dtData['customer']],
                    JSON_UNESCAPED_UNICODE
                );
                $customer = $dtData['customer'];
                $title_home = $dtData['title_home'] ?? '';
                $title = $customer['fullname'] . ' đã thích bài đăng';
                $content = $customer['fullname'] . ' đã thích bài đăng '. $title_home.' của bạn.';
                $title_owen = $title;
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'object_id' => $dtData['home_id'],
                    'content' => $content,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotification($customer_id, $type, $data);
            }
        }
    }

    static function notifyCommentHome($customer_id, $dtData = [],$type = 'notifyCommentHome')
    {
        $notification = new Notification();
        if (!empty($dtData)) {
            $arr_object_id = array_values($dtData['arr_object_id']);
            $arr_id_comment = ($dtData['arr_id_comment']);
            $arr_reply_id = ($dtData['arr_reply_id']);
            $content_text = $dtData['comment'];
            $content_home = self::short_text($content_text, 100);

            $dataNoti = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(
                    ['customer' => $customer_id, 'object' => $dtData['object_type'], 'status' => $type, 'home_id' => $dtData['home_id'], 'comment_id' => $dtData['comment_id'],'parent_id' => $dtData['parent_id'], 'customer' => $dtData['customer']],
                    JSON_UNESCAPED_UNICODE
                );
                if(!empty($arr_id_comment)){
                    $customer = $dtData['customer'];
                    $title = $customer['fullname'] . ' đã bình luận bài đăng của bạn';
                    $content = $customer['fullname'] . ' đã bình luận '.$content_home;
                    $title_owen = $title;
                    $data = [
                        'arr_object_id' => $arr_object_id,
                        'player_id' => $playerId,
                        'json_data' => $json_data,
                        'object_id' => $dtData['comment_id'],
                        'content' => $content,
                        'created_by' => 0,
                        'title' => $title,
                        'title_owen' => $title_owen,
                    ];
                    $dataNoti[] = $data;
                }
                if(!empty($arr_reply_id)){
                    $customer = $dtData['customer'];
                    $title = $customer['fullname'] . ' đã phản hồi bình luận';
                    $content = $customer['fullname'] . ' đã phản hồi bình luận '.$content_home;
                    $title_owen = $title;
                    $data = [
                        'arr_object_id' => $arr_object_id,
                        'player_id' => $playerId,
                        'json_data' => $json_data,
                        'object_id' => $dtData['comment_id'],
                        'content' => $content,
                        'created_by' => 0,
                        'title' => $title,
                        'title_owen' => $title_owen,
                    ];
                    $dataNoti[] = $data;
                }
            }
            if(!empty($dataNoti)){
                $notification->sendNotiOnesignalMutile($dataNoti, $type);
            }
        }
    }

    static function notiChangePointClient($customer_id = 0, $dtData = [], $point = 0, $point_client = 0, $title_point = '', $locale = 'vi')
    {
        if (!empty($dtData)) {
            $arr_object_id = array_values($dtData);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(
                    ['customer' => $customer_id, 'object' => 'clients', 'status' => 'change_point'],
                    JSON_UNESCAPED_UNICODE
                );
                $title = '';
                $content = '';
                $title_owen = '';
                if ($point < 0) {
                    $prefix = '';
                } else {
                    $prefix = '+';
                }
                $arrLang = [];
                $languages = DB::table('tbl_language')->orderBy('is_default', 'desc')->get();
                $htmlPoint = '';
                if (!empty($languages)) {
                    foreach ($languages as $lang) {
                        $code_lang = $lang->code;
                        if ($lang->code == 'vi') {
                            $code_lang = 'vi';
                        } elseif ($lang->code == 'kr') {
                            $code_lang = 'kr';
                        }
                        $dt_title_noti_change_point = Lang::get('message.dt_title_noti_change_point', [], $code_lang);
                        $dt_content_noti_change_point = Lang::get('message.dt_content_noti_change_point', [], $code_lang);
                        $htmlPoint = $prefix . formatMoney($point);
                        $dt_content_noti_change_point = str_replace(['{point}', '{total_point}'], [$htmlPoint, formatMoney($point_client)], $dt_content_noti_change_point);
                        $arrLang[] = [
                            'title' => $dt_title_noti_change_point,
                            'content' => $dt_content_noti_change_point,
                            'language' => $lang->code
                        ];
                        if ($lang->code == $locale) {
                            $title = $dt_title_noti_change_point;
                            $content = $dt_content_noti_change_point;
                        }
                    }
                }
                $title_owen = $title;
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'object_id' => $customer_id,
                    'content' => $content,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'arrLang' => $arrLang,
                ];
                static::addNotification($customer_id, Config::get('constant')['noti_change_point'], $data);
            }
        }
    }
    static function notficationModule($id, $type, $arr_object_id = [], $arr_object_id_driver = [])
    {
        $moduleNoti = ModuleNoti::find($id);
        if (!empty($moduleNoti)) {
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $json_data = json_encode(['module_noti_id' => $id, 'object' => 'module_noti'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                $content = $moduleNoti->name;

                $title = 'Thông báo hệ thống';
                $title_owen = 'Thông báo hệ thống';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_html' => !empty($moduleNoti) ? $moduleNoti->content : null,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => 0,
                ];
                static::addNotification($id, $type, $data);
            }

            if (!empty($arr_object_id_driver)) {
                $playerId = array_unique(array_column($arr_object_id_driver, 'player_id'));
                $content = '';
                $json_data = json_encode(['module_noti_id' => $id, 'object' => 'module_noti'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                $content = $moduleNoti->name;

                $title = 'Thông báo hệ thống';
                $title_owen = 'Thông báo hệ thống';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_html' => !empty($moduleNoti) ? $moduleNoti->content : null,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => 0,
                ];
                static::addNotificationDriver($id, $type, $data);
            }
        }
    }

    public static function replace_name_tags($text)
    {
        return preg_replace('/@\{idUserTag:\s*\d+,\s*nameUserTag:\s*(.+?)\}@/u', '$1', $text);
    }

    public static function short_text($text, $limit = 200, $end = '...', $start = 0)
    {
        // Bỏ HTML
        $plainText = strip_tags($text);

        // Cắt từ $start, lấy $limit ký tự
        $cutText = mb_substr($plainText, $start, $limit);

        // Nếu chuỗi gốc dài hơn phần lấy => thêm $end
        if (mb_strlen($plainText) > ($start + $limit)) {
            $cutText .= $end;
        }

        return $cutText;
    }
    
}
