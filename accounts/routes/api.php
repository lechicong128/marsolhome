<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api_app\LoginApi;
use App\Http\Controllers\Api_app\ReviewProduct;
use App\Http\Controllers\Api_app\Api_info;
use App\Http\Controllers\Api_app\ClientController;
use App\Http\Controllers\Api_app\FeedbackController;
use App\Http\Controllers\Api_app\PromotionController;
use App\Http\Controllers\Api_app\TransactionController;
use App\Http\Controllers\Api_app\ChallengeMeController;
use App\Http\Controllers\Api_app\SettingCustomerClassController;
use App\Http\Controllers\Api_app\PointController;
use App\Http\Controllers\Api_app\RankCommunityController;
use App\Http\Controllers\Api_app\ChallengeController;
use App\Http\Controllers\Api_app\PostController;
use App\Http\Controllers\Api_app\CommentController;
use App\Http\Controllers\Api_app\ReportViolationController;
use App\Http\Controllers\Api_app\EmojiController;
use App\Http\Controllers\Api_app\WebhookController;
use App\Http\Controllers\Api_app\TransactionPaymentController;
use App\Http\Controllers\Api_app\BonusPaymentController;
use App\Http\Controllers\Api_app\ReportController;
use App\Http\Controllers\Api_app\InvoiceController;
use App\Http\Controllers\Api_app\CodeLeaderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('sign_up_to_google', [LoginApi::class, 'sign_up_to_google']); // đăng ký với google
Route::post('sign_up_to_apple', [LoginApi::class, 'sign_up_to_apple']); // đăng ký với apple

//Route::post('otpDangKyThuanFoso', [LoginApi::class, 'otpDangKyThuanFoso']); // gửi mã otp
Route::post('otpDangKyAccount', [LoginApi::class, 'otpDangKyAccount']); // gửi mã otp
//Route::get('test', [LoginApi::class, 'test']); // gửi mã otp


Route::get('info_introduce', [LoginApi::class, 'info_introduce'])->middleware(App\Http\Middleware\CheckLoginApi::class); // lấy chi tiết giới thiệu
Route::post('send_otp_forgot_password', [LoginApi::class, 'send_otp_forgot_password']); // check otp quên mật khẩu
Route::post('check_otp_forgot_password', [LoginApi::class, 'check_otp_forgot_password']); // check otp quên mật khẩu
Route::post('forgot_password', [LoginApi::class, 'forgot_password']); // quên mật khẩu
Route::post('login', [LoginApi::class, 'login']); // đăng nhập
Route::post('verifyOtp', [LoginApi::class, 'verifyOtp']); // Kiểm tra OTP
Route::post('start_sign_up', [LoginApi::class, 'start_sign_up']); // check số điện thoại - email đã tồn tại và gửi OTP
Route::post('sign_up', [LoginApi::class, 'sign_up']); // đăng ký với số điện thoại - email
Route::post('logout', [LoginApi::class, 'logout']); // đăng xuất phiên đăng nhập
Route::post('update_account', [LoginApi::class, 'update_account'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::post('update_account_register', [LoginApi::class, 'update_account_register'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::post('changePassword', [LoginApi::class, 'changePassword'])->middleware(App\Http\Middleware\CheckLoginApi::class); // cập nhật mật khẩu
Route::post('update_password', [LoginApi::class, 'update_password'])->middleware(App\Http\Middleware\CheckLoginApi::class); // cập nhật mật khẩu
Route::post('send_otp_update_password', [LoginApi::class, 'send_otp_update_password'])->middleware(App\Http\Middleware\CheckLoginApi::class); //  gửi OTP cập nhật mật khẩu
Route::post('get_info_account', [LoginApi::class, 'get_info_account'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::post('update_account_signup_review', [LoginApi::class, 'update_account_signup_review'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::post('lockAccount', [LoginApi::class, 'lockAccount']);
Route::get('cong_test', [LoginApi::class, 'cong_test']);
Route::get('checkDeleteCodeLeader/{ref?}', [LoginApi::class, 'checkDeleteCodeLeader']);

Route::get('language_user_active', [LoginApi::class, 'language_user_active'])->middleware(App\Http\Middleware\CheckLoginApi::class);

Route::get('viewReviewer', [Api_info::class, 'viewReviewer']);

Route::group(['prefix' => 'customer'], function () {
    Route::get('getClientsIntroduce', [ClientController::class, 'getClientsIntroduce']);
    Route::get('getListCustomer', [ClientController::class, 'getListCustomer']);
    Route::get('getListData', [ClientController::class, 'getListData']);
    Route::get('countAll', [ClientController::class, 'countAll']);
    Route::get('getDetailCustomer', [ClientController::class, 'getDetailCustomer']);
    Route::get('getListDetailCustomer', [ClientController::class, 'getListDetailCustomer']);
    Route::get('getDetailCustomerPlayerid', [ClientController::class, 'getDetailCustomerPlayerid']);
    Route::post('detail', [ClientController::class, 'detail']);
    Route::post('deleteCustomer', [ClientController::class, 'deleteCustomer']);
    Route::post('active', [ClientController::class, 'active']);
    Route::post('changeStatusLeader', [ClientController::class, 'changeStatusLeader']);
    Route::post('changeStatusTypeLeader', [ClientController::class, 'changeStatusTypeLeader']);
    Route::post('updateTypeClient', [ClientController::class, 'updateTypeClient']);
    Route::post('getListInfoShortClient', [ClientController::class, 'getListInfoShortClient']);
    Route::post('addReferral', [ClientController::class, 'addReferral']);
    Route::get('getListCustonerIdReferral', [ClientController::class, 'getListCustonerIdReferral']);
    Route::get('getDataReferral', [ClientController::class, 'getDataReferral'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('getDetailDataReferral', [ClientController::class, 'getDetailDataReferral'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('getDetailDataOrderReferral', [ClientController::class, 'getDetailDataOrderReferral'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('get_list_address', [ClientController::class, 'get_list_address'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::post('delete_address', [ClientController::class, 'delete_address'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::post('update_client_address', [ClientController::class, 'update_client_address'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('get_address_default', [ClientController::class, 'get_address_default'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('getListDataOrderReferral', [ClientController::class, 'getListDataOrderReferral']);
    Route::post('addAffiliate', [ClientController::class, 'addAffiliate']);
    Route::get('getDataAffiliate', [ClientController::class, 'getDataAffiliate'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('getDataProductAffiliateNext', [ClientController::class, 'getDataProductAffiliateNext'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('getListFillterDay', [ClientController::class, 'getListFillterDay']);
    Route::get('getDataAffiliateChart', [ClientController::class, 'getDataAffiliateChart'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('getDataLeader', [ClientController::class, 'getDataLeader'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('getDataTransactionLeaderNext', [ClientController::class, 'getDataTransactionLeaderNext'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('getListBonusPayment', [ClientController::class, 'getListBonusPayment'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('getListTopLeader', [ClientController::class, 'getListTopLeader']);
    Route::get('getClientsOrderLeader', [ClientController::class, 'getClientsOrderLeader']);
    Route::post('detailInformationVat', [ClientController::class, 'detailInformationVat']);
});


Route::group(['prefix' => 'feedback'], function () {
    Route::get('getList', [FeedbackController::class, 'getList']);
    Route::get('countAll', [FeedbackController::class, 'countAll']);
    Route::get('getDetail', [FeedbackController::class, 'getDetail'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::post('send_feedback', [FeedbackController::class, 'send_feedback'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::post('delete', [FeedbackController::class, 'delete'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('feedback_improve', [FeedbackController::class, 'feedback_improve']);
    Route::post('update_feedback_improve', [FeedbackController::class, 'update_feedback_improve'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('get_improve_feedback', [FeedbackController::class, 'get_improve_feedback']);
});

Route::group(['prefix' => 'promotion','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [PromotionController::class, 'getList']);
    Route::get('getListData', [PromotionController::class, 'getListData']);
    Route::get('getPromotionReferral', [PromotionController::class, 'getPromotionReferral']);
    Route::get('getDetail', [PromotionController::class, 'getDetail']);
    Route::post('detail', [PromotionController::class, 'detail']);
    Route::post('delete', [PromotionController::class, 'delete']);
    Route::post('active', [PromotionController::class, 'active']);
    Route::get('countAll', [PromotionController::class, 'countAll']);
});




Route::group(['prefix' => 'transaction','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [TransactionController::class, 'getList']);
    Route::get('getListData', [TransactionController::class, 'getListData']);
    Route::get('getListDataDetail/{id}', [TransactionController::class, 'getListDataDetail']);
    Route::get('getDetail', [TransactionController::class, 'getDetail']);
    Route::post('detail', [TransactionController::class, 'detail']);
    Route::post('delete', [TransactionController::class, 'delete']);
    Route::post('changeStatus', [TransactionController::class, 'changeStatus']);
    Route::post('changeWarehouseStatus', [TransactionController::class, 'changeWarehouseStatus']);
    Route::get('countAll', [TransactionController::class, 'countAll']);
    Route::post('addTransaction', [TransactionController::class, 'addTransaction']);
    Route::get('getListStatusTransaction', [TransactionController::class, 'getListStatusTransaction']);
    Route::get('getTransactionDetailById/{id}', [TransactionController::class, 'getTransactionDetailById']);
    Route::get('CheckTransactionReview/{id}', [TransactionController::class, 'CheckTransactionReview']);
    Route::get('UnCheckTransactionReview/{id}', [TransactionController::class, 'UnCheckTransactionReview']);
    Route::post('getDiscountCustomer', [TransactionController::class, 'getDiscountCustomer']);
    Route::post('submitTransaction', [TransactionController::class, 'submitTransaction']);
});

Route::group(['prefix' => 'setting_customer_class'], function () {
    Route::get('getList', [SettingCustomerClassController::class, 'getList']);
    Route::get('getListData', [SettingCustomerClassController::class, 'getListData']);
    Route::get('getDetail', [SettingCustomerClassController::class, 'getDetail']);
    Route::post('detail', [SettingCustomerClassController::class, 'detail']);
});

Route::group(['prefix' => 'rank_community'], function () {
    Route::get('getList', [RankCommunityController::class, 'getList']);
//    Route::get('getListData', [SettingCustomerClassController::class, 'getListData']);
    Route::get('getDetail', [RankCommunityController::class, 'getDetail']);
    Route::post('detail', [RankCommunityController::class, 'detail']);
});

Route::group(['prefix' => 'challenge'], function () {
    Route::get('getList', [ChallengeController::class, 'getList']);
//    Route::get('getListData', [SettingCustomerClassController::class, 'getListData']);
    Route::get('getDetail', [ChallengeController::class, 'getDetail']);
    Route::post('detail', [ChallengeController::class, 'detail']);
    Route::post('changeStatus', [ChallengeController::class, 'changeStatus']);
    Route::post('delete', [ChallengeController::class, 'delete']);
    Route::get('get_list_status', [ChallengeController::class, 'get_list_status']);
    Route::get('get_list_challenge_short', [ChallengeController::class, 'get_list_challenge_short']);
    Route::get('statistical', [ChallengeController::class, 'statistical']);
    Route::get('get_client_join/{id}', [ChallengeController::class, 'get_client_join']);
    Route::get('countJoin', [ChallengeController::class, 'countJoin']);
    Route::get('history_join_challenge', [ChallengeController::class, 'history_join_challenge'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::post('contribute', [ChallengeController::class, 'contribute'])->middleware(App\Http\Middleware\CheckLoginApi::class);
});

Route::group(['prefix' => 'point','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListHistoryPoint', [PointController::class, 'getListHistoryPoint']);
    Route::get('getListMonthPoint', [PointController::class, 'getListMonthPoint']);
    Route::post('updatePointReview', [PointController::class, 'updatePointReview']);
    Route::post('getDetailPointReview', [PointController::class, 'getDetailPointReview']);
    Route::post('getDeleteDetailPointReivew', [PointController::class, 'getDeleteDetailPointReivew']);
});


Route::get('apiGetClassList', [SettingCustomerClassController::class, 'apiGetClassList']);
Route::get('reviewClassClientApi', [SettingCustomerClassController::class, 'reviewClassClientApi']);
Route::get('get_list_rank_community', [RankCommunityController::class, 'get_list_rank_community'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('get_list_challenge', [ChallengeController::class, 'get_list_challenge'])
    ->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('get_list_challenge_join', [ChallengeController::class, 'get_list_challenge_join'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('detail_challenge/{id?}', [ChallengeController::class, 'detail_challenge'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('history_challenge_payment', [ChallengeController::class, 'history_challenge_payment'])->middleware(App\Http\Middleware\CheckLoginApi::class);

Route::get('test', [TransactionController::class, 'test']);
Route::group(['prefix' => 'reportViolation','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [ReportViolationController::class, 'getList']);
});
Route::group(['prefix' => 'challenge_me','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [ChallengeMeController::class, 'getList']);
    Route::get('getListData', [ChallengeMeController::class, 'getListData']);
    Route::post('addChallenge', [ChallengeMeController::class, 'addChallenge']);
    Route::get('getListDataDetail/{id}', [ChallengeMeController::class, 'getListDataDetail']);
    Route::get('getDetail', [ChallengeMeController::class, 'getDetail']);
    Route::post('detail', [ChallengeMeController::class, 'detail']);
    Route::post('delete', [ChallengeMeController::class, 'delete']);
    Route::post('authentic_challenge', [ChallengeMeController::class, 'authentic_challenge']);
    Route::post('edit_authentic_challenge', [ChallengeMeController::class, 'edit_authentic_challenge']);
    Route::post('changeStatus', [ChallengeMeController::class, 'changeStatus']);
    Route::get('countAll', [ChallengeMeController::class, 'countAll']);

    Route::post('addChallengeWaiting', [ChallengeMeController::class, 'addChallengeWaiting']);
});

Route::group(['prefix' => 'transaction_payment','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [TransactionPaymentController::class, 'getList']);
    Route::post('delete', [TransactionPaymentController::class, 'delete']);
    Route::get('countAll', [TransactionPaymentController::class, 'countAll']);
    Route::post('changeStatus', [TransactionPaymentController::class, 'changeStatus']);
});

Route::group(['prefix' => 'bonus_payment','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [BonusPaymentController::class, 'getList']);
    Route::post('delete', [BonusPaymentController::class, 'delete']);
    Route::get('countAll', [BonusPaymentController::class, 'countAll']);
    Route::post('changeStatus', [BonusPaymentController::class, 'changeStatus']);
    Route::post('bonus_payment', [BonusPaymentController::class, 'bonus_payment']);
    Route::post('detail', [BonusPaymentController::class, 'detail']);
});

Route::group(['prefix' => 'invoice','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListWaitingInvoice', [InvoiceController::class, 'getListWaitingInvoice']);
    Route::post('changeStatusInvoice', [InvoiceController::class, 'changeStatusInvoice']);
    Route::post('getTransactionItem', [InvoiceController::class, 'getTransactionItem']);
    Route::post('submitDetailInvoice', [InvoiceController::class, 'submitDetailInvoice']);
    Route::get('getListInvoice', [InvoiceController::class, 'getListInvoice']);
    Route::post('viewInvoice', [InvoiceController::class, 'viewInvoice']);
    Route::post('deleteInvoice', [InvoiceController::class, 'deleteInvoice']);
    Route::post('detailInvoice', [InvoiceController::class, 'detailInvoice']);
});
Route::group(['prefix' => 'code_leader'], function () {
    Route::get('getList', [CodeLeaderController::class, 'getList']);
    Route::post('submit', [CodeLeaderController::class, 'submit']);
    Route::post('delete', [CodeLeaderController::class, 'delete']);
    Route::post('changeStatus', [CodeLeaderController::class, 'changeStatus']);
    Route::post('addClient', [CodeLeaderController::class, 'addClient']);
    Route::get('countAll', [CodeLeaderController::class, 'countAll']);
    Route::get('updatedataold', [CodeLeaderController::class, 'updatedataold']);
});

Route::group(['prefix' => 'newsfeed', 'middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('detail/{id}', [PostController::class, 'show']);
    Route::get('getListNewsFeed', [PostController::class, 'feed']);
    Route::post('likePost/{id}', [PostController::class, 'like']);        // POST /api/newsfeed/likePost/{id}
    Route::post('deletePost/{id}', [PostController::class, 'destroy']);        // POST /api/newsfeed/likePost/{id}
    Route::delete('unlikePost/{id}', [PostController::class, 'unlike']);  // DELETE /api/newsfeed/unlikePost/{id}
    Route::post('createPost', [PostController::class, 'store']);          // POST /api/newsfeed/createPost
    Route::post('updatePost/{id}', [PostController::class, 'updatePost']);          // POST /api/newsfeed/updatePost
    Route::post('getReportViolation', [PostController::class, 'getReportViolation']);
    Route::get('getComments/{post_id}', [CommentController::class, 'index']);         // GET /api/newsfeed/getComments/{post_id}
    Route::post('createComment/{post_id}', [CommentController::class, 'store']);      // POST /api/newsfeed/createComment/{post_id}
    Route::post('updateComment/{id_comment}', [CommentController::class, 'update']);      // POST /api/newsfeed/updateComment/{post_id}
    Route::post('likeComment/{id}', [CommentController::class, 'like']);              // POST /api/newsfeed/likeComment/{id}
    Route::delete('unlikeComment/{id}', [CommentController::class, 'unlike']);        // DELETE /api/newsfeed/unlikeComment/{id}
    Route::delete('deleteComment/{id}', [CommentController::class, 'deleteComment']);        // DELETE /api/newsfeed/deleteComment/{id}
    Route::post('reportComment', [CommentController::class, 'report']);  
    Route::post('report', [PostController::class, 'report']);
    Route::post('ignore', [PostController::class, 'ignore']);
    Route::post('hidePost', [PostController::class, 'hidePost']);
    Route::get('getNewPost', [PostController::class, 'getNewPost']);
    Route::get('readNewPost', [PostController::class, 'readNewPost']);
    Route::post('blockUser', [PostController::class, 'blockUser']);
    
});
Route::get('getEmoji', [EmojiController::class, 'emoji']); // get Emoji

Route::group(['prefix' => 'cron'], function () {
    Route::get('cron_challenge_expired', [ChallengeMeController::class, 'cron_challenge_expired']);//đổi trạng thái nhưng thử thách hết hạn
    Route::get('remind_challenge', [ChallengeMeController::class, 'remind_challenge']);//Nhắc nhở 10h00 hàng ngày những người tham gia thử thách chưa hoàn thành thử thách
});


Route::group(['prefix' => 'webhook'], function () {
    Route::post('WebhookPay2s', [WebhookController::class, 'WebhookPay2s']);
    Route::post('WebhookPaymentPay2s', [WebhookController::class, 'WebhookPaymentPay2s']);
    Route::get('cronLoginInvoice', [WebhookController::class, 'cronLoginInvoice']);
});

Route::group(['prefix' => 'community'], function () {
    Route::match(['get', 'post'], 'getList', [PostController::class, 'getListAdmin']);
    Route::get('getDetail/{id}', [PostController::class, 'getDetailAdmin']);
    Route::match(['get', 'post'], 'countAll', [PostController::class, 'countAllAdmin']);
    Route::post('delete', [PostController::class, 'deleteAdmin']);
    Route::post('toggleHide', [PostController::class, 'toggleHideAdmin']);
    Route::match(['get', 'post'], 'getReports', [PostController::class, 'getReportsAdmin']);
    Route::get('getViolations', [PostController::class, 'getViolationsAdmin']);
    Route::match(['get', 'post'], 'violations/store', [PostController::class, 'storeViolationAdmin']);
    Route::match(['get', 'post'], 'violations/update/{id}', [PostController::class, 'updateViolationAdmin']);
    Route::match(['get', 'post', 'delete'], 'violations/delete/{id}', [PostController::class, 'deleteViolationAdmin']);
});


Route::group(['prefix' => 'report','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListReportTransaction', [ReportController::class, 'getListReportTransaction']);
    Route::get('getListReportTransactionDetail', [ReportController::class, 'getListReportTransactionDetail']);
});