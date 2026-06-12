<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api_app\OrderRefController;
use App\Http\Controllers\Api_app\CronjobEmailController;
use App\Http\Controllers\Api_app\ClientController;
use App\Http\Controllers\Api_app\HomepageController;
use App\Http\Controllers\Api_app\LanguageController;
use App\Http\Controllers\Api_app\ProductsController;
use App\Http\Controllers\Api_app\Api_info;
use App\Http\Controllers\Api_app\ReviewProduct;
use App\Http\Controllers\Api_app\SocketController;
use App\Http\Controllers\Api_app\NotificationController;
use App\Http\Controllers\Api_app\CategoryController;
use App\Http\Controllers\Api_app\ScriptController;
use App\Http\Controllers\Api_app\ReportViolationController;
use App\Http\Controllers\Api_app\ReportViolationApiController;
use App\Http\Controllers\Api_app\Pay2sController;
use App\Http\Controllers\Api_app\ServiceController;
use App\Http\Controllers\Api_app\SpaController;
use App\Http\Controllers\Api_app\BranchController;
use App\Http\Controllers\Api_app\VideoController;
use App\Http\Controllers\Api_app\BuyTreatmentController;
use App\Http\Controllers\Api_app\HomeController;
use App\Http\Controllers\Api_app\BlogController;
use App\Http\Controllers\Api_app\ApiSearchHome;
use App\Http\Controllers\Api_app\ApplicationCommentsController;
use App\Http\Controllers\Api_app\PlandofficeController;
use App\Http\Controllers\Api_app\PlanningController;
use App\Http\Controllers\Api_app\ApiVewHome;
use App\Http\Controllers\Api_app\FeaturedLocationController;
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
Route::get('insertCronjobEmail/{ref?}', [CronjobEmailController::class, 'insertCronjobEmail']);
Route::get('getOrderRef/{ref?}', [OrderRefController::class, 'getOrderRef']);
Route::get('updateOrderRef/{ref?}', [OrderRefController::class, 'updateOrderRef']);
Route::get('get_referral_program', [Api_info::class, 'get_referral_program']);
Route::get('getvideodemo', [Api_info::class, 'getvideodemo']);
Route::get('get_haru_wallet', [Api_info::class, 'get_haru_wallet']);
Route::get('getInfoContact', [Api_info::class, 'getInfoContact']);
Route::get('getInfoBannerEvent', [Api_info::class, 'getInfoBannerEvent']);
Route::get('get_version_app', [Api_info::class, 'get_version_app']);
Route::get('get_info', [Api_info::class, 'get_info']);
Route::get('get_settingwebs', [Api_info::class, 'get_settingwebs']);
Route::get('how_to_join', [Api_info::class, 'how_to_join']);
Route::get('getOption/{ref?}', [Api_info::class, 'getOption']);
Route::get('get_code_leader/{ref?}', [Api_info::class, 'get_code_leader']);
Route::post('updateOption/{field?}/{value?}', [Api_info::class, 'updateOption']);

Route::get('terms', [Api_info::class, 'terms']);
Route::get('termsQuestion', [Api_info::class, 'termsQuestion']);
Route::get('policy', [Api_info::class, 'policy']);
Route::get('getListDiscountOrder', [Api_info::class, 'getListDiscountOrder']);
// Route::post('WebhookPaymentPay2s', [WebhookController::class, 'WebhookPaymentPay2s']);

Route::get('accumulation', [HomepageController::class, 'accumulation'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('HomePage', [HomepageController::class, 'HomePage']);
Route::get('dataReviewHub', [HomepageController::class, 'dataReviewHub']);
Route::get('dataReviewHubProduct/{id}', [HomepageController::class, 'dataReviewHubProduct']);
Route::get('dataReviewHubOther', [HomepageController::class, 'dataReviewHubOther']);
Route::get('donations_and_charity', [HomepageController::class, 'donations_and_charity']);
Route::get('get_banner_web_to_app', [HomepageController::class, 'get_banner_web_to_app']);
Route::get('get_banner_app', [HomepageController::class, 'get_banner_app']);

Route::get('HelpCentre', [HomepageController::class, 'HelpCentre']);
Route::get('BannerReviewHub', [HomepageController::class, 'BannerReviewHub']);
Route::get('Feedback', [HomepageController::class, 'Feedback']);

Route::get('HomePageToApp', [HomepageController::class, 'HomePageToApp']);

Route::get('getLanguageCurrent', [LanguageController::class, 'getLanguageCurrent']);
Route::get('getLanguageCurrentAPP', [LanguageController::class, 'getLanguageCurrentAPP']);
Route::post('SignUpReviewProduct', [ClientController::class, 'SignUpReviewProduct']);
Route::post('ktReviewProduct', [ClientController::class, 'ktReviewProduct']);
Route::get('get_setings_account', [Api_info::class, 'get_setings_account']);
Route::get('type_evaluate', [Api_info::class, 'type_evaluate']);
Route::get('type_status_review', [Api_info::class, 'type_status_review']);
Route::post('send_zalo', [Api_info::class, 'send_zalo']);


Route::group(['middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('type_active_review', [ReviewProduct::class, 'type_active_review']);
});



Route::post('getDataClientReview', [ReviewProduct::class, 'getDataClientReview'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::post('list_review', [ReviewProduct::class, 'list_review'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('get_product_review/{id}', [ReviewProduct::class, 'get_product_review']);
Route::get('GetDetailReview/{id}', [ReviewProduct::class, 'GetDetailReview']);
Route::get('get_product_review_public/{id}', [ReviewProduct::class, 'get_product_review_public']);
Route::get('get_my_review', [ReviewProduct::class, 'get_my_review'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('get_list_product_review/{id}', [ReviewProduct::class, 'get_list_product_review'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::post('submitReview/{id}', [ReviewProduct::class, 'submitReview'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('getListDataReview', [ReviewProduct::class, 'getListData']);

Route::post('appendReviewProduct', [ClientController::class, 'appendReviewProduct'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::post('synchronized_vsession', [ClientController::class, 'synchronized_vsession'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::post('reset_vsession', [ClientController::class, 'reset_vsession'])->middleware(App\Http\Middleware\CheckLoginApi::class);

Route::post('reviewMultipleProductsInTransaction', [ReviewProduct::class, 'reviewMultipleProductsInTransaction'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::post('reviewMultipleProductsInSignUp', [ReviewProduct::class, 'reviewMultipleProductsInSignUp'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('list_review_group', [ReviewProduct::class, 'list_review_group'])->middleware(App\Http\Middleware\CheckLoginApi::class);
Route::get('CountReviewClient', [ReviewProduct::class, 'CountReviewClient']);

Route::get('cong_te st', [ClientController::class, 'cong_test']);
Route::post('createQRPayment', [Pay2sController::class, 'createQRPayment']);
Route::post('createQRPay2s', [Pay2sController::class, 'createQRPay2s']);

Route::group(['prefix' => 'customer'], function () {
    Route::post('getClientsIntroduce', [ClientController::class, 'getClientsIntroduce']);
    Route::post('getListCustomer', [ClientController::class, 'getListCustomer']);
    Route::post('countAll', [ClientController::class, 'countAll']);
});
Route::group(['prefix' => 'services'], function () {
    Route::get('categories', [ServiceController::class, 'getListCategories']);
    Route::get('getList', [ServiceController::class, 'getList']);
    Route::post('saveSearch', [ServiceController::class, 'saveSearch']);
    Route::get('services/{id}/{is_search?}', [ServiceController::class, 'getDetail']);
    Route::get('infoHistorySearch', [ServiceController::class, 'infoHistorySearch'])->middleware(App\Http\Middleware\CheckLoginApi::class);
});
Route::group(['prefix' => 'branches'], function () {
    Route::get('getList', [BranchController::class, 'getList']);
});
Route::group(['prefix' => 'spa'], function () {
    Route::post('getSession', [SpaController::class, 'getSession']);
    Route::post('getTime', [SpaController::class, 'getTime']);
    Route::post('AddBooking', [SpaController::class, 'AddBooking']);
    Route::post('AddBookingDateServices', [SpaController::class, 'AddBookingDateServices']);
    Route::post('CancelSpaBooking', [SpaController::class, 'CancelSpaBooking']);
    Route::post('GetListSpaBooking', [SpaController::class, 'GetListSpaBooking']);
    Route::post('GetListStatusFilter', [SpaController::class, 'GetListStatusFilter']);
    Route::get('updateStatusSpa', [SpaController::class, 'updateStatusSpa']);
    Route::get('testnoti', [SpaController::class, 'testnoti']);
    Route::post('GetDetailSpaBooking', [SpaController::class, 'GetDetailSpaBooking']);
    Route::get('getWorkShift', [SpaController::class, 'getWorkShift']);
    Route::post('WebhookPay2sSpa', [SpaController::class, 'WebhookPay2sSpa']);
});
Route::group(['prefix' => 'buy_treatment'], function () {
    Route::post('getList', [BuyTreatmentController::class, 'getList'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('getDetail', [BuyTreatmentController::class, 'getDetail'])->middleware('App\Http\Middleware\CheckLoginApi::class');
});
Route::group(['prefix' => 'products'], function () {
    Route::get('getList', [ProductsController::class, 'getList']);
    Route::get('getDetail/{slug?}', [ProductsController::class, 'getDetail']);
    Route::post('getListDetail', [ProductsController::class, 'getListDetail']);
    Route::get('getDetailReview/{slug}', [ProductsController::class, 'getDetailReview']);
    Route::get('getPageDetailReview/{id}', [ProductsController::class, 'getPageDetailReview']);
    Route::get('getListDetailShort', [ProductsController::class, 'getListDetailShort']);
    Route::get('top_three_product', [ProductsController::class, 'top_three_product']);
    Route::get('get_list_products_filter', [ProductsController::class, 'get_list_products_filter']);
    Route::get('get_list_products_filter_to_app', [ProductsController::class, 'get_list_products_filter_to_app']);
    Route::post('getListData', [ProductsController::class, 'getListData']);
    Route::get('getListIsUse', [ProductsController::class, 'getListIsUse']);
    Route::get('getListProductAndVideo', [ProductsController::class, 'getListProductAndVideo']);
    Route::post('changeSold', [ProductsController::class, 'changeSold'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('getListProductAffiliate', [ProductsController::class, 'getListProductAffiliate'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('getReviewProductPage/{id}', [ProductsController::class, 'getReviewProductPage']);

    Route::get('getListApp', [ProductsController::class, 'getListApp'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::post('SaveHistoryProduct', [ProductsController::class, 'SaveHistoryProduct'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('infoHistorySearch', [ProductsController::class, 'infoHistorySearch'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('SearchInputtingProduct', [ProductsController::class, 'SearchInputtingProduct']);
    Route::post('get_list_product_id', [ProductsController::class, 'get_list_product_id']);
    Route::post('get_list_product_variant', [ProductsController::class, 'get_list_product_variant']);
});

Route::group(['prefix' => 'socket'], function () {
    Route::get('connect', [SocketController::class, 'login_socket']);
    Route::get('test', [SocketController::class, 'test']);
    Route::post('sendSocket', [SocketController::class, 'sendSocket']);
    Route::post('send', [SocketController::class, 'sendSocket']);
});

Route::group(['prefix' => 'notification'], function () {
    Route::get('getListNotification', [NotificationController::class, 'getListNotification'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('readAllNotification', [NotificationController::class, 'readAllNotification'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('readSingleNotification', [NotificationController::class, 'readSingleNotification'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('checkReadNoti', [NotificationController::class, 'checkReadNoti'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('CountNotification', [NotificationController::class, 'CountNotification'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('getDetail/{id}', [NotificationController::class, 'getDetail'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('addNoti', [NotificationController::class, 'addNoti']);
    Route::post('addNotiPost', [NotificationController::class, 'addNotiPost']);
    Route::post('addNotiMutil', [NotificationController::class, 'addNotiMutil']);
});

Route::group(['prefix' => 'category'], function () {
    Route::get('getListProduct/{id?}', [CategoryController::class, 'getListProduct']);
    Route::get('getListPaymentMode', [CategoryController::class, 'getListPaymentMode']);
    Route::get('getListNoteCancel', [CategoryController::class, 'getListNoteCancel']);
    Route::get('getListNoteAffiliate', [CategoryController::class, 'getListNoteAffiliate']);
    Route::get('getListNoteHaruWallet', [CategoryController::class, 'getListNoteHaruWallet']);
    Route::get('searchPropertyType', [CategoryController::class, 'searchPropertyType']);
    Route::get('getListProvince', [CategoryController::class, 'getListProvince']);
    Route::get('getListWard', [CategoryController::class, 'getListWard']);
    Route::get('getListUtilitiesFilter', [CategoryController::class, 'getListUtilitiesFilter']);
});

Route::group(['prefix' => 'script'], function () {
    Route::get('get_info_script', [ScriptController::class, 'get_info_script']);
    Route::get('createSession', [ScriptController::class, 'createSession']);
    Route::get('list_chat', [ScriptController::class, 'list_chat']);
    Route::get('active_script/{id_script}/{id_script_detail?}/{id_item?}', [ScriptController::class, 'active_script']);
    Route::get('active_script_products_filter', [ScriptController::class, 'active_script_products_filter']);
    Route::get('active_result_script_products_filter/{id_script}/{id_script_detail?}/{id_item?}', [ScriptController::class, 'active_result_script_products_filter']);
});


Route::group(['prefix' => 'video'], function () {
    Route::get('get_list', [VideoController::class, 'get_list']);
    Route::get('list_elearning', [VideoController::class, 'list_elearning'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('detail_review/{id}', [VideoController::class, 'detail_review'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('detail_tips/{id}', [VideoController::class, 'detail_tips'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('detail_elearning/{id}', [VideoController::class, 'detail_elearning'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('see_video/{id}', [VideoController::class, 'see_video'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('like_video/{id}/{like}', [VideoController::class, 'like_video'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('share_video/{id}', [VideoController::class, 'share_video'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('play_video/{id}', [VideoController::class, 'play_video'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('unlockElearning/{id}', [VideoController::class, 'unlockElearning'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('detail_video/{id}', [VideoController::class, 'detail_video'])->middleware('App\Http\Middleware\CheckLoginApi::class');

    Route::post('comment/{id}', [VideoController::class, 'comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('edit_comment/{id}', [VideoController::class, 'edit_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('list_comment/{id}/{id_parent?}', [VideoController::class, 'list_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('like_comment/{id}/{like}', [VideoController::class, 'like_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('delete_comment/{id}', [VideoController::class, 'delete_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
});


//Route::get('test', [LanguageController::class, 'test']);

Route::get('getLangApp', [LanguageController::class, 'getLangApp']);

Route::group(['prefix' => 'report_violation'], function () {
    Route::post('getList', [ReportViolationApiController::class, 'getList']);
});


Route::get('test', [ScriptController::class, 'addNotiMutil']);

Route::group(['prefix' => 'home'], function () {
    Route::get('getListHome', [HomeController::class, 'getListHome'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('getListHomeAll', [HomeController::class, 'getListHomeAll'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('getDetail/{id}', [HomeController::class, 'getDetail'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('getRelatedData/{id}', [HomeController::class, 'getRelatedData'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('countHomes', [HomeController::class, 'countHomes']);
    Route::post('detail', [HomeController::class, 'detail'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('changeFavouriteHome', [HomeController::class, 'changeFavouriteHome'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('changeSaveHome', [HomeController::class, 'changeSaveHome'])->middleware('App\Http\Middleware\CheckLoginApi::class');

    Route::post('comment', [HomeController::class, 'comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('edit_comment/{id}', [HomeController::class, 'edit_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('list_comment', [HomeController::class, 'list_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('like_comment/{id}/{like}', [HomeController::class, 'like_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('dislike_comment/{id}/{dislike}', [HomeController::class, 'dislike_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('delete_comment/{id}', [HomeController::class, 'delete_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('report_comment', [HomeController::class, 'report_comment'])->middleware('App\Http\Middleware\CheckLoginApi::class');

    Route::post('add_review', [HomeController::class, 'add_review'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('edit_review/{id}', [HomeController::class, 'edit_review'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('delete_review/{id}', [HomeController::class, 'delete_review'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('list_review', [HomeController::class, 'list_review'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('get_review_stats', [HomeController::class, 'get_review_stats']);

    Route::get('getListUtilities', [HomeController::class, 'getListUtilities']);
    Route::get('checkHome', [HomeController::class, 'checkHome'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('extendHome', [HomeController::class, 'extendHome'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('checkEditHome', [HomeController::class, 'checkEditHome'])->middleware('App\Http\Middleware\CheckLoginApi::class');
});

Route::group(['prefix' => 'blog'], function () {
    Route::get('getListBlog', [BlogController::class, 'getListBlog']);
    Route::get('getListBlogNext', [BlogController::class, 'getListBlogNext']);
    Route::get('getDetail/{id}', [BlogController::class, 'getDetail']);
    Route::get('getListBlogHomePage', [BlogController::class, 'getListBlogHomePage']);
});
Route::group(['prefix' => 'searchhome'], function () {
    Route::get('getListSearch', [ApiSearchHome::class, 'GetListSearch']);
    Route::get('getListSearchHome', [ApiSearchHome::class, 'GetListSearchHome']);
    Route::get('getListHome', [ApiSearchHome::class, 'getListHome'])->middleware(App\Http\Middleware\CheckLoginApi::class);
    Route::get('GetListHistorySearch', [ApiSearchHome::class, 'GetListHistorySearch'])->middleware(App\Http\Middleware\CheckLoginApi::class);
});

Route::group(['prefix' => 'application_comments', 'middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::post('submit', [ApplicationCommentsController::class, 'submit']);
    Route::get('listappcomment', [ApplicationCommentsController::class, 'listappcomment']);
});

Route::group(['prefix' => 'plandoffice'], function () {
    Route::get('get-nearby-parcels', [PlandofficeController::class, 'getNearbyParcels']);
    Route::get('get-info-parcels/{id?}', [PlandofficeController::class, 'GetInfoParcels']);
});

Route::get('kmz/{filename}', [PlanningController::class, 'processKmz']);

Route::group(['prefix' => 'plannings'], function () {
    Route::get('getList', [PlanningController::class, 'getList']);
    Route::get('getDetail/{id?}', [PlanningController::class, 'getDetail']);
});

Route::group(['prefix' => 'viewhome'], function () {
    Route::get('getListViewers/{home_id}', [ApiVewHome::class, 'getListViewers'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('trackView', [ApiVewHome::class, 'trackView'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('getListViewedByClient', [ApiVewHome::class, 'getListViewedByClient'])->middleware('App\Http\Middleware\CheckLoginApi::class');
});
Route::get('featured-locations', [FeaturedLocationController::class, 'getList']);
Route::get('get_info_home', [Api_info::class, 'get_info_home']);
