<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminWebsiteController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryProductsController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\ChallengeMeController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\ClientsReviewController;
use App\Http\Controllers\ContentReviewController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventArticlesController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GroupPermissionController;
use App\Http\Controllers\IntroductionAppController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\NoteAffiliateController;
use App\Http\Controllers\NoteCancelController;
use App\Http\Controllers\NoteHaruWalletController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentModeController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductsFilterController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\RankCommunityController;
use App\Http\Controllers\ReportViolationModeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScriptController;
use App\Http\Controllers\SettingCustomerClassController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SlideIntroduceAppController;
use App\Http\Controllers\TagProductController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VariantController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionPaymentController;
use App\Http\Controllers\CategoryServiceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SetDateController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentSpaController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AccumulationController;
use App\Http\Controllers\VideoFileController;
use App\Http\Controllers\BonusPaymentController;
use App\Http\Controllers\BuyTreatmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ImportComtroller;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CodeLeaderController;
use App\Http\Controllers\UnitController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});
Route::group(['prefix' => 'cron'], function () {
//    Route::get('noti_remind_transaction', [CronController::class, 'noti_remind_transaction']);
//    Route::get('noti_one_hour_remind_transaction', [CronController::class, 'noti_one_hour_remind_transaction']);
//    Route::get('updateCodeClient', [CronController::class, 'updateCodeClient']);
//    Route::get('sendSmsTransaction', [CronController::class, 'sendSmsTransaction']);
//    Route::get('addAutoReview', [CronController::class, 'addAutoReview']);
//    Route::get('remindFinishOwner', [CronController::class, 'remindFinishOwner']);
//    Route::get('autoFinishTransaction', [CronController::class, 'autoFinishTransaction']);
//    Route::get('cronCloseBalance', [CronController::class, 'cronCloseBalance']);
//    Route::get('cronCloseBalanceMonth', [CronController::class, 'cronCloseBalanceMonth']);
//    Route::get('getListBanks', [CronController::class, 'getListBanks']);
//    Route::get('cancelTransactionNotDepoist', [CronController::class, 'cancelTransactionNotDepoist']);
//    Route::get('cancelTransactionNotApprove', [CronController::class, 'cancelTransactionNotApprove']);
//    Route::get('addGroupPermistionByPermission', [CronController::class, 'addGroupPermistionByPermission']);
//    Route::get('getListBankNew', [CronController::class, 'getListBankNew']);
//    Route::get('cancelTransactionDriverNotDriver', [CronController::class, 'cancelTransactionDriverNotDriver']);
//    Route::get('getCancelSystemTransactionDriver', [CronController::class, 'getCancelSystemTransactionDriver']);
//    Route::get('sendNotificationModule', [CronController::class, 'sendNotificationModule']);
//    Route::get('noti_remind_transaction_driver_province', [CronController::class, 'noti_remind_transaction_driver_province']);
//    Route::get('noti_remind_use_point_client', [CronController::class, 'noti_remind_use_point_client']);
//    Route::get('noti_reset_use_point_client', [CronController::class, 'noti_reset_use_point_client']);
//    Route::get('moveFileToS3', [CronController::class, 'moveFileToS3']);
//    Route::get('sendNotiTransaction', [CronController::class, 'sendNotiTransaction']);
//    Route::get('cronCustomerRewardDay', [CronController::class, 'cronCustomerRewardDay']);
//    Route::get('cronCustomerClassDay', [CronController::class, 'cronCustomerClassDay']);
//    Route::get('getWarningWithDraw', [CronController::class, 'getWarningWithDraw']);
//    Route::get('updateStatusCustomerWarning', [CronController::class, 'updateStatusCustomerWarning']);
//    Route::get('createTransactionCertificate', [CronController::class, 'createTransactionCertificate']);



//    Route::get('sendNotiRemindReview', [CronController::class, 'sendNotiRemindReview']);
//    Route::get('render_video_review', [CronController::class, 'render_video_review']);
//    Route::get('render_image_video_review', [CronController::class, 'render_image_video_review']);
    Route::get('render_video_file', [CronController::class, 'render_video_file']);
    Route::get('render_thumbnail_file', [CronController::class, 'render_thumbnail_file']);
    Route::get('refresh_token_zalo', [CronController::class, 'refresh_token_zalo']);
    Route::get('cronjob_send_mail', [CronController::class, 'cronjob_send_mail']);
});


Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('route:cache');
    \Illuminate\Support\Facades\Artisan::call('config:cache');
});

Route::get('/clear1', function () {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
});

Route::group(['prefix' => 'webhook'], function () {
    Route::post('webhookAlepay', [WebhookController::class, 'webhookAlepay']);
    Route::post('webhookStripe', [WebhookController::class, 'webhookStripe']);
    Route::post('WebhookPay2sUnlockVideo', [WebhookController::class, 'WebhookPay2sUnlockVideo']);
    Route::get('test_unlock_video/{code}/{money}', [WebhookController::class, 'test_unlock_video']);
});

//Route::get('.well-known/acme-challenge/{key?}', function (string $key) {
//    return require_once __DIR__.'/../.well-known/acme-challenge/'.$key;
//});
Route::get('/admin/123', function () {
    return view('admin.email-template.new_customer_register');
});
Route::get('/', function () {
    return view('welcome');
})->middleware('checkLogin');
Route::get('admin/login', [AdminController::class, 'get_login']);
Route::post('admin/login', [AdminController::class, 'post_login']);
Route::get('admin/logout', [AdminController::class, 'get_logout']);
Route::group(['prefix' => 'admin', 'middleware' => 'checkLogin:admin'], function () {
    Route::get('dashboard', [AdminController::class, 'index']);
    Route::post('loadDataChartDashboard', [AdminController::class, 'loadDataChartDashboard']);
    Route::get('loadDataCustomerClass/{id}/{type}', [AdminController::class, 'loadDataCustomerClass']);
    Route::group(['prefix' => 'user'], function () {
        Route::get('list', [UserController::class, 'get_list']);
        Route::post('getUsers', [UserController::class, 'getUsers']);
        Route::get('detail/{id?}', [UserController::class, 'get_detail']);
        Route::post('submit/{id}', [UserController::class, 'submit']);
        Route::post('getPermissonByRole', [UserController::class, 'getPermissonByRole']);
        Route::get('delete/{id}', [UserController::class, 'delete']);
        Route::get('active/{id}', [UserController::class, 'active']);
        Route::post('updatePriority', [UserController::class, 'updatePriority']);
        Route::post('changeLangSystem', [UserController::class, 'changeLangSystem']);
        Route::get('profile/{id?}', [UserController::class, 'profile']);
        Route::post('profile/{id?}', [UserController::class, 'profile']);
    });
    Route::group(['prefix' => 'department'], function () {
        Route::get('list', [DepartmentController::class, 'get_list']);
        Route::post('getDepartment', [DepartmentController::class, 'getDepartment']);
        Route::get('detail/{id?}', [DepartmentController::class, 'get_detail']);
        Route::post('submit/{id}', [DepartmentController::class, 'submit']);
        Route::get('delete/{id}', [DepartmentController::class, 'delete']);
        Route::get('changeStatus/{id}', [DepartmentController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'accumulation'], function () {
        Route::get('list', [AccumulationController::class, 'get_list']);
        Route::post('submit', [AccumulationController::class, 'submit']);
    });

    Route::group(['prefix' => 'role'], function () {
        Route::get('list', [RoleController::class, 'get_list']);
        Route::post('getRole', [RoleController::class, 'getRole']);
        Route::get('detail/{id?}', [RoleController::class, 'get_detail']);
        Route::post('submit/{id}', [RoleController::class, 'submit']);
        Route::get('delete/{id}', [RoleController::class, 'delete']);
    });
    Route::group(['prefix' => 'group_permission'], function () {
        Route::get('list', [GroupPermissionController::class, 'get_list']);
        Route::post('getGroupPermission', [GroupPermissionController::class, 'getGroupPermission']);
        Route::get('detail/{id?}', [GroupPermissionController::class, 'get_detail']);
        Route::post('submit/{id}', [GroupPermissionController::class, 'submit']);
        Route::get('delete/{id}', [GroupPermissionController::class, 'delete']);
    });
    Route::group(['prefix' => 'permission'], function () {
        Route::get('list', [PermissionController::class, 'get_list']);
        Route::post('getPermission', [PermissionController::class, 'getPermission']);
        Route::get('detail/{id?}', [PermissionController::class, 'get_detail']);
        Route::post('submit/{id}', [PermissionController::class, 'submit']);
        Route::get('delete/{id}', [PermissionController::class, 'delete']);
    });
//    Route::group(['prefix' => 'company_car'], function () {
//        Route::get('list', [CompanyCarController::class, 'get_list']);
//        Route::post('getCompanyCar', [CompanyCarController::class, 'getCompanyCar']);
//        Route::get('detail/{id?}', [CompanyCarController::class, 'get_detail']);
//        Route::post('submit/{id}', [CompanyCarController::class, 'submit']);
//        Route::get('delete/{id}', [CompanyCarController::class, 'delete']);
//    });
//    Route::group(['prefix' => 'type_car'], function () {
//        Route::get('list', [TypeCarController::class, 'get_list']);
//        Route::post('getTypeCar', [TypeCarController::class, 'getTypeCar']);
//        Route::get('detail/{id?}', [TypeCarController::class, 'get_detail']);
//        Route::post('submit/{id}', [TypeCarController::class, 'submit']);
//        Route::get('delete/{id}', [TypeCarController::class, 'delete']);
//    });
//    Route::group(['prefix' => 'model_car'], function () {
//        Route::get('list', [ModelCarController::class, 'get_list']);
//        Route::post('getModelCar', [ModelCarController::class, 'getModelCar']);
//        Route::get('detail/{id?}', [ModelCarController::class, 'get_detail']);
//        Route::post('submit/{id}', [ModelCarController::class, 'submit']);
//        Route::get('delete/{id}', [ModelCarController::class, 'delete']);
//    });

//    Route::group(['prefix' => 'other_amenities_car'], function () {
//        Route::get('list', [OtherAmenitiesCarController::class, 'get_list']);
//        Route::post('getOtherAmenitiesCar', [OtherAmenitiesCarController::class, 'getOtherAmenitiesCar']);
//        Route::get('detail/{id?}', [OtherAmenitiesCarController::class, 'get_detail']);
//        Route::post('submit/{id}', [OtherAmenitiesCarController::class, 'submit']);
//        Route::get('delete/{id}', [OtherAmenitiesCarController::class, 'delete']);
//    });

//    Route::group(['prefix' => 'surcharge_car'], function () {
//        Route::get('list', [SurchargeCarController::class, 'get_list']);
//        Route::post('getSurchargeCar', [SurchargeCarController::class, 'getSurchargeCar']);
//        Route::get('detail/{id?}', [SurchargeCarController::class, 'get_detail']);
//        Route::post('submit/{id}', [SurchargeCarController::class, 'submit']);
//        Route::get('delete/{id}', [SurchargeCarController::class, 'delete']);
//    });
//
//    Route::group(['prefix' => 'category_report'], function () {
//        Route::get('list', [CategoryReportController::class, 'get_list']);
//        Route::post('getCategoryReport', [CategoryReportController::class, 'getCategoryReport']);
//        Route::get('detail/{id?}', [CategoryReportController::class, 'get_detail']);
//        Route::post('submit/{id}', [CategoryReportController::class, 'submit']);
//        Route::get('delete/{id}', [CategoryReportController::class, 'delete']);
//    });

//    Route::group(['prefix' => 'car'], function () {
//        Route::get('list', [CarController::class, 'get_list']);
//        Route::post('getCar', [CarController::class, 'getCar']);
//        Route::get('detail/{id?}', [CarController::class, 'get_detail']);
//        Route::post('submit/{id}', [CarController::class, 'submit']);
//        Route::get('delete/{id}', [CarController::class, 'delete']);
//        Route::get('searchCustomer/{id?}', [CarController::class, 'searchCustomer']);
//        Route::post('getDistrict', [CarController::class, 'getDistrict']);
//        Route::post('getWard', [CarController::class, 'getWard']);
//        Route::get('view/{id}', [CarController::class, 'getView']);
//        Route::get('updateSurcharge', [CarController::class, 'updateSurcharge']);
//        Route::post('updatePromotionCar', [CarController::class, 'updatePromotionCar']);
//        Route::post('getReviewCar/{id?}', [CarController::class, 'getReviewCar']);
//        Route::post('getReportCar/{id?}', [CarController::class, 'getReportCar']);
//        Route::post('loadTransaction', [CarController::class, 'loadTransaction']);
//        Route::post('loadMoreTransaction', [CarController::class, 'loadMoreTransaction']);
//        Route::post('changeStatus', [CarController::class, 'changeStatus']);
//        Route::get('price_month_car/{id}', [CarController::class, 'price_month_car']);
//        Route::post('price_month_car/{id}', [CarController::class, 'price_month_car']);
//        Route::get('updatePrice/{id}/{type}', [CarController::class, 'updatePrice']);
//        Route::post('updatePrice/{id}/{type}', [CarController::class, 'updatePrice']);
//        Route::get('updateStatusPriceSunday/{id}/{type}', [CarController::class, 'updateStatusPriceSunday']);
//        Route::post('updateStatusPriceSunday/{id}/{type}', [CarController::class, 'updateStatusPriceSunday']);
//        Route::post('updateStatusPrice', [CarController::class, 'updateStatusPrice']);
//        Route::get('changeStatusType/{id}/{type}', [CarController::class, 'changeStatusType']);
//        Route::post('getModelCarByCompany', [CarController::class, 'getModelCarByCompany']);
//        Route::post('updateImageCar/{id}', [CarController::class, 'updateImageCar']);
//        Route::get('rental_period/{id}', [CarController::class, 'rental_period']);
//        Route::post('rental_period/{id}', [CarController::class, 'rental_period']);
//        Route::get('deleteReviewCar/{id}', [CarController::class, 'deleteReviewCar']);
//    });
//
//    Route::group(['prefix' => 'transaction'], function () {
//        Route::get('list', [TransactionController::class, 'get_list']);
//        Route::post('getTransaction', [TransactionController::class, 'getTransaction']);
//        Route::post('changeStatus', [TransactionController::class, 'changeStatus']);
//        Route::get('view/{id}', [TransactionController::class, 'view']);
//        Route::get('delete/{id}', [TransactionController::class, 'delete']);
//        Route::post('countAll', [TransactionController::class, 'countAll']);
//        Route::get('searchCar/{id?}', [TransactionController::class, 'searchCar']);
//        Route::post('addComment', [TransactionController::class, 'addComment']);
//        Route::post('updateComment', [TransactionController::class, 'updateComment']);
//        Route::post('deleteComment', [TransactionController::class, 'deleteComment']);
//        Route::get('loadModalNoteTransaction', [TransactionController::class, 'loadModalNoteTransaction']);
//        Route::post('updateDateTransaction', [TransactionController::class, 'updateDateTransaction']);
//        Route::post('changeStatusCancel', [TransactionController::class, 'changeStatusCancel']);
//        Route::post('changeStatusCancelOwen', [TransactionController::class, 'changeStatusCancelOwen']);
//        Route::post('addContractTran', [TransactionController::class, 'addContractTran']);
//        Route::post('addHandoverRecord', [TransactionController::class, 'addHandoverRecord']);
//        Route::get('add_payment/{id}', [TransactionController::class, 'add_payment']);
//        Route::post('add_payment/{id}', [TransactionController::class, 'add_payment']);
//        Route::get('add_payment_alepay/{id}', [TransactionController::class, 'add_payment_alepay']);
//        Route::post('add_payment_alepay/{id}', [TransactionController::class, 'add_payment_alepay']);
//        Route::get('view_certificate/{id}', [TransactionController::class, 'view_certificate']);
//    });

//    Route::group(['prefix' => 'clients'], function () {
//        Route::get('list', [ClientsController::class, 'get_list']);
//        Route::post('getClients', [ClientsController::class, 'getClients']);
//        Route::post('getCountClients', [ClientsController::class, 'getCountClients']);
//        Route::get('getCountClients', [ClientsController::class, 'getCountClients']);
//        Route::get('detail/{id?}', [ClientsController::class, 'get_detail']);
//        Route::post('submit/{id}', [ClientsController::class, 'submit']);
//        Route::get('delete/{id}', [ClientsController::class, 'delete']);
//        Route::get('active/{id}', [ClientsController::class, 'active']);
//        Route::post('changeStatusClientBusiness', [ClientsController::class, 'changeStatusClientBusiness']);
//        Route::post('changeStatusDrivingLiscense', [ClientsController::class, 'changeStatusDrivingLiscense']);
//        Route::post('kt_isset_phone', [ClientsController::class, 'kt_isset_phone']);
//        Route::get('view/{id}', [ClientsController::class, 'view']);
//        Route::post('updateBusiness/{id}', [ClientsController::class, 'updateBusiness']);
//        Route::post('updateDrivingLiscense/{id}', [ClientsController::class, 'updateDrivingLiscense']);
//        Route::post('deleteDrivingLiscense', [ClientsController::class, 'deleteDrivingLiscense']);
//        Route::post('deleteClientBusinessImage', [ClientsController::class, 'deleteClientBusinessImage']);
//        Route::post('loadTransaction', [ClientsController::class, 'loadTransaction']);
//        Route::post('loadMoreTransaction', [ClientsController::class, 'loadMoreTransaction']);
//        Route::post('changeStatus', [ClientsController::class, 'changeStatus']);
//        Route::post('updateBusinessPercent/{id}', [ClientsController::class, 'updateBusinessPercent']);
//        Route::post('getTransactionBusinessRose', [ClientsController::class, 'getTransactionBusinessRose']);
//        Route::get('adjust_balance/{id}', [ClientsController::class, 'adjust_balance']);
//        Route::post('adjust_balance/{id}', [ClientsController::class, 'adjust_balance']);
//    });

//    Route::group(['prefix' => 'driver'], function () {
//        Route::get('list', [DriverController::class, 'get_list']);
//        Route::post('getDrivers', [DriverController::class, 'getDrivers']);
//        Route::get('detail/{id?}', [DriverController::class, 'get_detail']);
//        Route::post('submit/{id}', [DriverController::class, 'submit']);
//        Route::get('delete/{id}', [DriverController::class, 'delete']);
//        Route::get('active/{id}', [DriverController::class, 'active']);
//        Route::get('view/{id}', [DriverController::class, 'view']);
//        Route::post('checkExistsPhone', [DriverController::class, 'checkExistsPhone']);
//        Route::post('updateDrivingLiscense/{id}', [DriverController::class, 'updateDrivingLiscense']);
//        Route::post('deleteDrivingLiscense', [DriverController::class, 'deleteDrivingLiscense']);
//        Route::post('changeStatusDrivingLiscense', [DriverController::class, 'changeStatusDrivingLiscense']);
//        Route::get('updateServiceRegister', [DriverController::class, 'updateServiceRegister']);
//        Route::post('changeStatus', [DriverController::class, 'changeStatus']);
//        Route::post('updatePaperDriver/{id}', [DriverController::class, 'updatePaperDriver']);
//        Route::post('changeStatusPaper', [DriverController::class, 'changeStatusPaper']);
//    });
    Route::group(['prefix' => 'promotion'], function () {
        Route::get('list', [PromotionController::class, 'get_list']);
        Route::post('getList', [PromotionController::class, 'getList']);
        Route::get('detail/{id?}', [PromotionController::class, 'get_detail']);
        Route::post('detail/{id?}', [PromotionController::class, 'detail']);
        Route::get('delete/{id}', [PromotionController::class, 'delete']);
        Route::get('changeStatus/{id}', [PromotionController::class, 'changeStatus']);
        Route::post('countAll', [PromotionController::class, 'countAll']);
    });

    Route::group(['prefix' => 'setting_customer_class'], function () {
        Route::get('list', [SettingCustomerClassController::class, 'get_list']);
        Route::post('getList', [SettingCustomerClassController::class, 'getList']);
        Route::get('detail/{id?}', [SettingCustomerClassController::class, 'get_detail']);
        Route::post('detail/{id?}', [SettingCustomerClassController::class, 'detail']);
    });


    Route::group(['prefix' => 'transaction'], function () {
        Route::get('list', [TransactionController::class, 'get_list']);
        Route::post('getList', [TransactionController::class, 'getList']);
        Route::get('detail/{id?}', [TransactionController::class, 'get_detail']);
        Route::post('detail/{id?}', [TransactionController::class, 'detail']);
        Route::get('delete/{id}', [TransactionController::class, 'delete']);
        Route::post('changeStatus', [TransactionController::class, 'changeStatus']);
        Route::post('countAll', [TransactionController::class, 'countAll']);
        Route::get('view/{id?}', [TransactionController::class, 'view']);
        Route::get('getOrderItemsForWarehouse/{id}', [TransactionController::class, 'getOrderItemsForWarehouse']);
        Route::post('approveWarehouse/{id}', [TransactionController::class, 'approveWarehouse']);
        Route::post('cancelWarehouseApprove/{id}', [TransactionController::class, 'cancelWarehouseApprove']);
        Route::get('detailTransaction/{id}', [TransactionController::class, 'detailTransaction']);
        Route::post('getDiscountCustomer', [TransactionController::class, 'getDiscountCustomer']);
        Route::post('submitTransaction/{id?}', [TransactionController::class, 'submitTransaction']);
    });

    Route::group(['prefix' => 'payment_mode'], function () {
        Route::get('list', [PaymentModeController::class, 'get_list']);
        Route::post('getPaymentMode', [PaymentModeController::class, 'getPaymentMode']);
        Route::get('detail/{id?}', [PaymentModeController::class, 'get_detail']);
        Route::post('submit/{id}', [PaymentModeController::class, 'submit']);
        Route::get('delete/{id}', [PaymentModeController::class, 'delete']);
        Route::get('changeStatus/{id}', [PaymentModeController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'unit'], function () {
        Route::get('list', [UnitController::class, 'get_list']);
        Route::post('getUnit', [UnitController::class, 'getUnit']);
        Route::get('detail/{id?}', [UnitController::class, 'get_detail']);
        Route::post('submit/{id}', [UnitController::class, 'submit']);
        Route::get('delete/{id}', [UnitController::class, 'delete']);
    });

    Route::group(['prefix' => 'note_cancel'], function () {
        Route::post('getNoteCancel', [NoteCancelController::class, 'getNoteCancel']);
        Route::get('detail/{id?}', [NoteCancelController::class, 'get_detail']);
        Route::post('submit/{id}', [NoteCancelController::class, 'submit']);
        Route::get('delete/{id}', [NoteCancelController::class, 'delete']);
    });
    Route::group(['prefix' => 'note_haru_wallet'], function () {
        Route::get('list', [NoteHaruWalletController::class, 'get_list']);
        Route::post('getList', [NoteHaruWalletController::class, 'getList']);
        Route::get('detail/{id?}', [NoteHaruWalletController::class, 'get_detail']);
        Route::post('submit/{id}', [NoteHaruWalletController::class, 'submit']);
        Route::get('delete/{id}', [NoteHaruWalletController::class, 'delete']);
    });
    Route::group(['prefix' => 'note_affiliate'], function () {
        Route::get('list', [NoteAffiliateController::class, 'get_list']);
        Route::post('getList', [NoteAffiliateController::class, 'getList']);
        Route::get('detail/{id?}', [NoteAffiliateController::class, 'get_detail']);
        Route::post('submit/{id}', [NoteAffiliateController::class, 'submit']);
        Route::get('delete/{id}', [NoteAffiliateController::class, 'delete']);
    });

    Route::group(['prefix' => 'payment'], function () {
        Route::get('list', [PaymentController::class, 'get_list'])->name('payment');
        Route::post('getPayment', [PaymentController::class, 'getPayment']);
        Route::get('view/{id}', [PaymentController::class, 'view']);
        Route::get('delete/{id}', [PaymentController::class, 'delete']);
        Route::get('transfer_transaction/{id}', [PaymentController::class, 'transfer_transaction']);
        Route::post('transfer_transaction/{id}', [PaymentController::class, 'transfer_transaction']);
        Route::get('create_refund/{id}', [PaymentController::class, 'create_refund']);
        Route::post('create_refund/{id}', [PaymentController::class, 'create_refund']);
    });

//    Route::group(['prefix' => 'payment_driver'], function () {
//        Route::get('list', [PaymentDriverController::class, 'get_list']);
//        Route::post('getPaymentDriver', [PaymentDriverController::class, 'getPaymentDriver']);
//        Route::get('view/{id}', [PaymentDriverController::class, 'view']);
//        Route::get('delete/{id}', [PaymentDriverController::class, 'delete']);
//    });

//    Route::group(['prefix' => 'pay_slip'], function () {
//        Route::get('list', [PayslipController::class, 'get_list']);
//        Route::post('getPaySlip', [PayslipController::class, 'getPaySlip']);
//        Route::get('view/{id}', [PayslipController::class, 'view']);
//        Route::get('delete/{id}', [PayslipController::class, 'delete']);
//        Route::post('getCount', [PayslipController::class, 'getCount']);
//    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('', [SettingsController::class, 'get_list']);
        Route::get('down/{id}', [SettingsController::class, 'download']);
        Route::post('submit/{id?}', [SettingsController::class, 'submit']);
        Route::get('changeStatus/{id?}', [SettingsController::class, 'changeStatus']);
        Route::get('changeStatusDisplay/{id?}', [SettingsController::class, 'changeStatusDisplay']);
        Route::get('changeStatusCheckOtp', [SettingsController::class, 'changeStatusCheckOtp']);
        Route::get('changeTypeTransferAddress', [SettingsController::class, 'changeTypeTransferAddress']);
        Route::post('loadCustomerClass', [SettingsController::class, 'loadCustomerClass']);
        Route::post('loadCustomerLeaderShip', [SettingsController::class, 'loadCustomerLeaderShip']);
    });

//    Route::group(['prefix' => 'category_report_driver'], function () {
//        Route::post('getCategoryReportDriver', [CategoryReportDriverController::class, 'getCategoryReportDriver']);
//        Route::get('detail/{id?}', [CategoryReportDriverController::class, 'get_detail']);
//        Route::post('submit/{id}', [CategoryReportDriverController::class, 'submit']);
//        Route::get('delete/{id}', [CategoryReportDriverController::class, 'delete']);
//    });
//
//
//    Route::group(['prefix' => 'synthetic_transaction'], function () {
//        Route::get('list', [SyntheticTransaction::class, 'get_list']);
//        Route::post('getSyntheticTransaction', [SyntheticTransaction::class, 'getSyntheticTransaction']);
//        Route::get('list_transaction_driver', [SyntheticTransaction::class, 'list_transaction_driver']);
//        Route::post('getSyntheticTransactionDriver', [SyntheticTransaction::class, 'getSyntheticTransactionDriver']);
//    });

    Route::group(['prefix' => 'category'], function () {
        Route::get('searchCustomer/{id?}', [CategoryController::class, 'searchCustomer']);
        Route::get('searchDriver/{id?}', [CategoryController::class, 'searchDriver']);
        Route::get('searchTransaction/{id?}', [CategoryController::class, 'searchTransaction']);
        Route::get('searchTransactionInvoice/{id?}', [CategoryController::class, 'searchTransactionInvoice']);
        Route::post('searchTransactionItem', [CategoryController::class, 'searchTransactionItem']);
        Route::get('searchTransactionDriver/{id?}', [CategoryController::class, 'searchTransactionDriver']);
        Route::get('searchTransactionAll/{id?}', [CategoryController::class, 'searchTransactionAll']);
        Route::get('searchTransferMoney/{id?}', [CategoryController::class, 'searchTransferMoney']);
        Route::get('searchRequestWithdrawMoney/{id?}', [CategoryController::class, 'searchRequestWithdrawMoney']);
        Route::get('searchBlog/{id?}', [CategoryController::class, 'searchBlog']);
        Route::get('searchCategoryCard/{id?}', [CategoryController::class, 'searchCategoryCard']);
    });


    Route::group(['prefix' => 'notification'], function () {
        Route::post('loadNoti', [NotificationController::class, 'loadNoti']);
        Route::post('loadMoreNoti', [NotificationController::class, 'loadMoreNoti']);
        Route::post('readSingleNoti', [NotificationController::class, 'readSingleNoti']);
        Route::post('readAllNoti', [NotificationController::class, 'readAllNoti']);
    });

//    Route::group(['prefix' => 'report'], function () {
//        Route::get('synthetic_revenue', [ReportController::class, 'synthetic_revenue']);
//        Route::post('getSyntheticRevenue', [ReportController::class, 'getSyntheticRevenue']);
//        Route::get('report_detail_revenue', [ReportController::class, 'report_detail_revenue']);
//        Route::post('getReportDetailRevenue', [ReportController::class, 'getReportDetailRevenue']);
//        Route::get('report_cash_book', [ReportController::class, 'report_cash_book']);
//        Route::post('getReportCashBook', [ReportController::class, 'getReportCashBook']);
//        Route::get('synthetic_revenue_detail_rose', [ReportController::class, 'synthetic_revenue_detail_rose']);
//        Route::post('getReportDetailRevenueRose', [ReportController::class, 'getReportDetailRevenueRose']);
//    });


    Route::group(['prefix' => 'content_review'], function () {
        Route::post('getContentReview', [ContentReviewController::class, 'getContentReview']);
        Route::get('detail/{id?}', [ContentReviewController::class, 'get_detail']);
        Route::post('submit/{id}', [ContentReviewController::class, 'submit']);
        Route::get('delete/{id}', [ContentReviewController::class, 'delete']);
    });

//    Route::group(['prefix' => 'blog'], function () {
//        Route::get('list', [BlogController::class, 'get_list']);
//        Route::post('getBlog', [BlogController::class, 'getBlog']);
//        Route::get('detail/{id?}', [BlogController::class, 'get_detail']);
//        Route::post('submit/{id}', [BlogController::class, 'submit']);
//        Route::get('delete/{id}', [BlogController::class, 'delete']);
//        Route::get('changeStatus/{id}', [BlogController::class, 'changeStatus']);
//        Route::get('changeHomePage/{id}', [BlogController::class, 'changeHomePage']);
//        Route::get('changeHot/{id}', [BlogController::class, 'changeHot']);
//    });

//    Route::group(['prefix' => 'blog_recruitment'], function () {
//        Route::get('list', [BlogRecruitmentController::class, 'get_list']);
//        Route::post('getBlogRecruitment', [BlogRecruitmentController::class, 'getBlogRecruitment']);
//        Route::get('detail/{id?}', [BlogRecruitmentController::class, 'get_detail']);
//        Route::post('submit/{id}', [BlogRecruitmentController::class, 'submit']);
//        Route::get('delete/{id}', [BlogRecruitmentController::class, 'delete']);
//        Route::get('changeStatus/{id}', [BlogRecruitmentController::class, 'changeStatus']);
//    });

    Route::group(['prefix' => 'pdf'], function () {
        Route::get('contractPdf/{id}', [PDFController::class, 'contractPdf']);
        Route::get('handoverRecordPdf/{id}', [PDFController::class, 'handoverRecordPdf']);
    });

    Route::group(['prefix' => 'policy'], function () {
        Route::get('list', [PolicyController::class, 'get_list']);
        Route::post('getPolicy', [PolicyController::class, 'getPolicy']);
        Route::get('detail/{id?}', [PolicyController::class, 'get_detail']);
        Route::post('submit/{id}', [PolicyController::class, 'submit']);
        Route::get('delete/{id}', [PolicyController::class, 'delete']);
        Route::get('changeStatus/{id}', [PolicyController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'banner'], function () {
        Route::get('list', [BannerController::class, 'get_list']);
        Route::post('getBanner', [BannerController::class, 'getBanner']);
        Route::get('detail/{id?}', [BannerController::class, 'get_detail']);
        Route::post('submit/{id}', [BannerController::class, 'submit']);
        Route::get('delete/{id}', [BannerController::class, 'delete']);
        Route::get('changeStatus/{id}', [BannerController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'module_noti'], function () {
        Route::get('list', [ModuleNotiController::class, 'getList']);
        Route::post('getModuleNoti', [ModuleNotiController::class, 'getModuleNoti']);
        Route::get('detail/{id?}', [ModuleNotiController::class, 'get_detail']);
        Route::post('submit/{id}', [ModuleNotiController::class, 'submit']);
        Route::get('delete/{id}', [ModuleNotiController::class, 'delete']);
        Route::get('changeStatus/{id}', [ModuleNotiController::class, 'changeStatus']);
    });

//    Route::group(['prefix' => 'request_withdraw_money'], function () {
//        Route::get('list', [RequestWithdrawMoneyController::class, 'get_list']);
//        Route::post('getRequestWithdrawMoney', [RequestWithdrawMoneyController::class, 'getRequestWithdrawMoney']);
//        Route::get('view/{id}', [RequestWithdrawMoneyController::class, 'view']);
//        Route::get('delete/{id}', [RequestWithdrawMoneyController::class, 'delete']);
//        Route::post('agreeStatus', [RequestWithdrawMoneyController::class, 'agreeStatus']);
//        Route::get('create_tranfer_money/{id}', [RequestWithdrawMoneyController::class, 'create_tranfer_money']);
//        Route::post('create_tranfer_money/{id}', [RequestWithdrawMoneyController::class, 'create_tranfer_money']);
//    });

//    Route::group(['prefix' => 'transfer_package'], function () {
//        Route::get('list', [TransferPackageController::class, 'get_list']);
//        Route::post('getTransferPackage', [TransferPackageController::class, 'getTransferPackage']);
//        Route::get('view/{id}', [TransferPackageController::class, 'view']);
//        Route::get('delete/{id}', [TransferPackageController::class, 'delete']);
//        Route::post('agreeStatus', [TransferPackageController::class, 'agreeStatus']);
//    });

//    Route::group(['prefix' => 'transfer_money'], function () {
//        Route::get('list', [TransferMoneyController::class, 'get_list']);
//        Route::post('getTransferMoney', [TransferMoneyController::class, 'getTransferMoney']);
//        Route::get('view/{id}', [TransferMoneyController::class, 'view']);
//        Route::get('delete/{id}', [TransferMoneyController::class, 'delete']);
//    });

    Route::group(['prefix' => 'admin_website'], function () {
        Route::get('homepage', [AdminWebsiteController::class, 'homepage']);
        Route::post('submit_homepage', [AdminWebsiteController::class, 'submit_homepage']);
        Route::get('helpcentre', [AdminWebsiteController::class, 'helpcentre']);
        Route::post('submit_helpcentre', [AdminWebsiteController::class, 'submit_helpcentre']);
        Route::get('feedback', [AdminWebsiteController::class, 'feedback']);
        Route::post('submit_feedback', [AdminWebsiteController::class, 'submit_feedback']);
        Route::get('reviewhub', [AdminWebsiteController::class, 'reviewhub']);
        Route::post('submit_reviewhub', [AdminWebsiteController::class, 'submit_reviewhub']);

        Route::get('donations_and_charity', [AdminWebsiteController::class, 'donations_and_charity']);
        Route::post('submit_donations_and_charity', [AdminWebsiteController::class, 'submit_donations_and_charity']);
//        Route::get('privilege', [AdminWebsiteController::class, 'privilege']);
//        Route::post('submit_privilege', [AdminWebsiteController::class, 'submit_privilege']);
//        Route::get('white_paper', [AdminWebsiteController::class, 'white_paper']);
//        Route::post('submit_white_paper', [AdminWebsiteController::class, 'submit_white_paper']);
//        Route::get('page_not_found', [AdminWebsiteController::class, 'page_not_found']);
//        Route::post('submit_page_not_found', [AdminWebsiteController::class, 'submit_page_not_found']);
    });


//    Route::group(['prefix' => 'category_preferential'], function () {
//        Route::get('list', [CategoryPreferentialController::class, 'get_list']);
//        Route::post('getCategoryPreferential', [CategoryPreferentialController::class, 'getCategoryPreferential']);
//        Route::get('detail/{id?}', [CategoryPreferentialController::class, 'get_detail']);
//        Route::post('submit/{id}', [CategoryPreferentialController::class, 'submit']);
//        Route::get('delete/{id}', [CategoryPreferentialController::class, 'delete']);
//        Route::get('searchCategoryCard/{id?}', [CategoryPreferentialController::class, 'searchCategoryCard']);
//        Route::get('changeStatus/{id}', [CategoryPreferentialController::class, 'changeStatus']);
//    });

//    Route::group(['prefix' => 'category_card'], function () {
//        Route::get('list', [CategoryCardController::class, 'get_list']);
//        Route::post('getCategoryCard', [CategoryCardController::class, 'getCategoryCard']);
//        Route::get('detail/{id?}', [CategoryCardController::class, 'get_detail']);
//        Route::post('submit/{id}', [CategoryCardController::class, 'submit']);
//        Route::get('delete/{id}', [CategoryCardController::class, 'delete']);
//        Route::get('changeStatus/{id}', [CategoryCardController::class, 'changeStatus']);
//        Route::post('updatePriority', [CategoryCardController::class, 'updatePriority']);
//    });

//    Route::group(['prefix' => 'contact'], function () {
//        Route::get('list', [ContactController::class, 'get_list']);
//        Route::post('table', [ContactController::class, 'table']);
//        Route::get('view/{id}', [ContactController::class, 'view']);
//        Route::get('delete/{id}', [ContactController::class, 'delete']);
//        Route::post('changeStatus', [ContactController::class, 'changeStatus']);
////        Route::get('detail/{id?}', [CategoryCardController::class, 'get_detail']);
////        Route::post('submit/{id}', [CategoryCardController::class, 'submit']);
////        Route::get('delete/{id}', [CategoryCardController::class, 'delete']);
//    });

//    Route::group(['prefix' => 'transfer_address'], function () {
//        Route::post('getTransferAddress', [TransferAddressController::class, 'getTransferAddress']);
//        Route::get('detail/{id?}', [TransferAddressController::class, 'get_detail']);
//        Route::post('submit/{id}', [TransferAddressController::class, 'submit']);
//        Route::get('delete/{id}', [TransferAddressController::class, 'delete']);
//        Route::get('changeStatus/{id}', [TransferAddressController::class, 'changeStatus']);
//    });

//    Route::group(['prefix' => 'transfer_address_request'], function () {
//        Route::post('getTransferAddressRequest', [TransferAddressRequestController::class, 'getTransferAddressRequest']);
//        Route::get('detail/{id?}', [TransferAddressRequestController::class, 'get_detail']);
//        Route::post('submit/{id}', [TransferAddressRequestController::class, 'submit']);
//        Route::get('delete/{id}', [TransferAddressRequestController::class, 'delete']);
//        Route::get('changeStatus/{id}', [TransferAddressRequestController::class, 'changeStatus']);
//    });


    Route::group(['prefix' => 'tag_product'], function () {
        Route::get('list', [TagProductController::class, 'get_list']);
        Route::post('getTable', [TagProductController::class, 'getTable']);
        Route::get('detail/{id?}', [TagProductController::class, 'detail']);
        Route::post('submit/{id?}', [TagProductController::class, 'submit']);
        Route::get('delete/{id}', [TagProductController::class, 'delete']);
    });

    Route::group(['prefix' => 'category_products'], function () {
        Route::get('list', [CategoryProductsController::class, 'get_list']);
        Route::post('getTable', [CategoryProductsController::class, 'getTable']);
        Route::get('detail/{id?}', [CategoryProductsController::class, 'detail']);
        Route::post('submit/{id?}', [CategoryProductsController::class, 'submit']);
        Route::get('delete/{id}', [CategoryProductsController::class, 'delete']);
        Route::get('changeStatus/{id}', [CategoryProductsController::class, 'changeStatus']);
    });
    Route::group(['prefix' => 'category_service'], function () {
        Route::get('list', [CategoryServiceController::class, 'get_list']);
        Route::post('getTable', [CategoryServiceController::class, 'getTable']);
        Route::get('detail/{id?}', [CategoryServiceController::class, 'detail']);
        Route::post('submit/{id?}', [CategoryServiceController::class, 'submit']);
        Route::get('delete/{id}', [CategoryServiceController::class, 'delete']);
        Route::get('changeStatus/{id}', [CategoryServiceController::class, 'changeStatus']);
    });
    Route::group(['prefix' => 'services'], function () {
        Route::get('list', [ServiceController::class, 'get_list']);
        Route::post('getTable', [ServiceController::class, 'getTable']);
        Route::get('view/{id}', [ServiceController::class, 'view']);
        Route::get('detail/{id?}', [ServiceController::class, 'detail']);
        Route::post('submit/{id?}', [ServiceController::class, 'submit']);
        Route::get('delete/{id}', [ServiceController::class, 'delete']);
        Route::get('changeStatus/{id}', [ServiceController::class, 'changeStatus']);
        Route::get('changeIsHot/{id}', [ServiceController::class, 'changeIsHot']);
    });

    Route::group(['prefix' => 'set_date'], function () {
        Route::get('list', [SetDateController::class, 'get_list']);
        Route::post('getTable', [SetDateController::class, 'getTable']);
        Route::post('submit', [SetDateController::class, 'submit']);
        Route::get('delete/{id}', [SetDateController::class, 'delete']);
        Route::get('changeStatus/{id}', [SetDateController::class, 'changeStatus']);
        Route::get('getCurrentShifts', [SetDateController::class, 'getCurrentShifts']);
    });

    Route::group(['prefix' => 'branch'], function () {
        Route::get('list', [BranchController::class, 'get_list']);
        Route::post('getTable', [BranchController::class, 'getTable']);
        Route::get('detail/{id?}', [BranchController::class, 'detail']);
        Route::post('submit/{id?}', [BranchController::class, 'submit']);
        Route::get('delete/{id}', [BranchController::class, 'delete']);
        Route::get('changeStatus/{id}', [BranchController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'booking'], function () {
        Route::get('list', [BookingController::class, 'get_list']);
        Route::post('getTable', [BookingController::class, 'getTable']);
        Route::get('view/{id}', [BookingController::class, 'view']);
        Route::post('changeStatus/{id}', [BookingController::class, 'changeStatus']);
        Route::get('delete/{id}', [BookingController::class, 'delete']);
        Route::post('applyTreatment', [BookingController::class, 'applyTreatment']);
        Route::get('print-bill/{id}', [BookingController::class, 'print_bill']);
    });

    Route::group(['prefix' => 'payment_spa'], function () {
        Route::get('list', [PaymentSpaController::class, 'get_list']);
        Route::post('getTable', [PaymentSpaController::class, 'getTable']);
        Route::get('view/{id}', [PaymentSpaController::class, 'view']);
        Route::post('approve/{id}', [PaymentSpaController::class, 'approve']);
        Route::post('reject/{id}', [PaymentSpaController::class, 'reject']);
    });

    Route::group(['prefix' => 'buy_treatment'], function () {
        Route::get('list', [BuyTreatmentController::class, 'get_list']);
        Route::post('getTable', [BuyTreatmentController::class, 'getTable']);
        Route::get('detail/{id?}', [BuyTreatmentController::class, 'detail']);
        Route::post('submit/{id?}', [BuyTreatmentController::class, 'submit']);
        Route::get('view/{id}', [BuyTreatmentController::class, 'view']);
        Route::post('useSession/{id}', [BuyTreatmentController::class, 'useSession']);
        Route::post('changeStatus/{id}', [BuyTreatmentController::class, 'changeStatus']);
        Route::get('delete/{id}', [BuyTreatmentController::class, 'delete']);
    });

    Route::group(['prefix' => 'products'], function () {
        Route::get('list', [ProductsController::class, 'get_list']);
        Route::post('getTable', [ProductsController::class, 'getTable']);
        Route::get('detail/{id?}', [ProductsController::class, 'detail']);
        Route::post('submit/{id?}', [ProductsController::class, 'submit']);
        Route::get('delete/{id}', [ProductsController::class, 'delete']);
        Route::get('changeStatus/{id}', [ProductsController::class, 'changeStatus']);
        Route::get('changeIsHot/{id}', [ProductsController::class, 'changeIsHot']);
        Route::get('changeUse/{id}', [ProductsController::class, 'changeUse']);
    });

    Route::group(['prefix' => 'clients_review'], function () {
        Route::get('list', [ClientsReviewController::class, 'get_list']);
        Route::post('getTableReview', [ClientsReviewController::class, 'getTableReview']);
        Route::post('getTable', [ClientsReviewController::class, 'getTable']);
        Route::get('getTable', [ClientsReviewController::class, 'getTable']);
        Route::get('delete/{id}', [ClientsReviewController::class, 'delete']);
        Route::get('deleteReview/{id}', [ClientsReviewController::class, 'deleteReview']);
        Route::get('changeActive/{id}', [ClientsReviewController::class, 'changeActive']);
        Route::post('changeStatus', [ClientsReviewController::class, 'changeStatus']);
        Route::get('library_review', [ClientsReviewController::class, 'library_review']);
        Route::post('countAll', [ClientsReviewController::class, 'countAll']);
        Route::post('active_review', [ClientsReviewController::class, 'active_review']);
        Route::get('viewReview/{id}', [ClientsReviewController::class, 'viewReview']);
        Route::get('view/{id}', [ClientsReviewController::class, 'view']);
        Route::get('modal_cancel_review/{id}', [ClientsReviewController::class, 'modal_cancel_review']);
        Route::post('removeItems', [ClientsReviewController::class, 'removeItems']);
        Route::get('export', [ClientsReviewController::class, 'exportReviewExcel']);
        Route::post('getClientsIntroduceReview', [ClientsReviewController::class, 'getClientsIntroduceReview']);
    });

    Route::group(['prefix' => 'clients'], function () {
        Route::get('list', [ClientsController::class, 'get_list']);
        Route::get('detail/{id?}', [ClientsController::class, 'get_detail']);
        Route::get('view/{id}', [ClientsController::class, 'view']);
        Route::post('getListCustomer', [ClientsController::class, 'getListCustomer']);
        Route::post('countAll', [ClientsController::class, 'countAll']);
        Route::get('getDetailCustomer', [ClientsController::class, 'getDetailCustomer']);
        Route::post('detail', [ClientsController::class, 'detail']);
        Route::get('delete/{id}', [ClientsController::class, 'delete']);
        Route::get('active/{id}', [ClientsController::class, 'active']);
        Route::post('getClientsIntroduceOrder', [ClientsController::class, 'getClientsIntroduceOrder']);
        Route::post('getClientsOrderLeader', [ClientsController::class, 'getClientsOrderLeader']);
        Route::get('changeStatusLeader/{id}', [ClientsController::class, 'changeStatusLeader']);
        Route::post('changeStatusTypeLeader', [ClientsController::class, 'changeStatusTypeLeader']);
        Route::post('detailInformationVat/{id}', [ClientsController::class, 'detailInformationVat']);
    });


    Route::group(['prefix' => 'event_articles'], function () {
        Route::get('list', [EventArticlesController::class, 'get_list']);
        Route::get('detail/{id?}', [EventArticlesController::class, 'detail']);
        Route::post('getTable', [EventArticlesController::class, 'getTable']);
        Route::post('submit/{id}', [EventArticlesController::class, 'submit']);
        Route::get('delete/{id}', [EventArticlesController::class, 'delete']);
        Route::get('active/{id}', [EventArticlesController::class, 'active']);
        Route::get('changeIsHot/{id}', [EventArticlesController::class, 'changeIsHot']);

//        Route::post('countAll', [EventArticlesController::class, 'countAll']);
//        Route::get('getDetailCustomer', [EventArticlesController::class, 'getDetailCustomer']);
    });

    Route::group(['prefix' => 'feedback'], function () {
        Route::get('list', [FeedbackController::class, 'get_list']);
        Route::get('detail/{id?}', [FeedbackController::class, 'detail']);
        Route::post('getTable', [FeedbackController::class, 'getTable']);
        Route::post('countAll', [FeedbackController::class, 'countAll']);
        Route::get('delete/{id?}', [FeedbackController::class, 'delete']);
    });


    Route::group(['prefix' => 'products_filter'], function () {
        Route::get('list', [ProductsFilterController::class, 'get_list']);
        Route::post('getTable', [ProductsFilterController::class, 'getTable']);
        Route::get('detail/{id?}', [ProductsFilterController::class, 'detail']);
        Route::get('detail_child/{id_parent?}/{id?}', [ProductsFilterController::class, 'detail_child']);
        Route::post('submit/{id?}', [ProductsFilterController::class, 'submit']);
        Route::post('submit_child/{id_parent?}/{id?}', [ProductsFilterController::class, 'submit_child']);
        Route::get('delete/{id}', [ProductsFilterController::class, 'delete']);
        Route::get('changeStatus/{id}', [ProductsFilterController::class, 'changeStatus']);
        Route::get('changeFilterMain/{id}', [ProductsFilterController::class, 'changeFilterMain']);
        Route::post('order_by', [ProductsFilterController::class, 'order_by']);
    });


    Route::group(['prefix' => 'imports'], function () {
        Route::get('list', [ImportComtroller::class, 'get_list']);
        Route::post('getTable', [ImportComtroller::class, 'getTable']);
        Route::get('detail/{id?}', [ImportComtroller::class, 'detail']);
        Route::post('submit/{id?}', [ImportComtroller::class, 'submit']);
        Route::post('approve/{id}', [ImportComtroller::class, 'approve']);
        Route::post('unapprove/{id}', [ImportComtroller::class, 'unapprove']);
        Route::get('delete/{id}', [ImportComtroller::class, 'delete']);
    });

    Route::group(['prefix' => 'warehouse'], function () {
        Route::get('list', [WarehouseController::class, 'get_list']);
        Route::post('getTable', [WarehouseController::class, 'getTable']);
        Route::get('getImportHistory/{id_product}', [WarehouseController::class, 'getImportHistory']);
    });

    Route::group(['prefix' => 'terms'], function () {
        Route::get('list', [TermsController::class, 'get_list']);
        Route::post('getTable', [TermsController::class, 'getTable']);
        Route::get('detail/{id?}', [TermsController::class, 'detail']);
        Route::post('submit/{id?}', [TermsController::class, 'submit']);
        Route::get('delete/{id}', [TermsController::class, 'delete']);
        Route::get('changeStatus/{id}', [TermsController::class, 'changeStatus']);
        Route::post('order_by', [TermsController::class, 'order_by']);
    });

    Route::group(['prefix' => 'slide_introduce_app'], function () {
        Route::get('list', [SlideIntroduceAppController::class, 'get_list']);
        Route::post('getTable', [SlideIntroduceAppController::class, 'getTable']);
        Route::get('detail/{id?}', [SlideIntroduceAppController::class, 'detail']);
        Route::post('submit/{id?}', [SlideIntroduceAppController::class, 'submit']);
        Route::get('delete/{id}', [SlideIntroduceAppController::class, 'delete']);
        Route::get('active/{id}', [SlideIntroduceAppController::class, 'active']);
        Route::post('order_by', [SlideIntroduceAppController::class, 'order_by']);
    });


    Route::group(['prefix' => 'script'], function () {
        Route::get('list', [ScriptController::class, 'get_list']);
        Route::get('get_id_event_app', [ScriptController::class, 'get_id_event_app']);
        Route::get('setting/{id?}', [ScriptController::class, 'setting']);
        Route::post('submit/{id?}', [ScriptController::class, 'submit']);
        Route::get('detail/{id?}', [ScriptController::class, 'detail']);
        Route::get('delete/{id}', [ScriptController::class, 'delete']);
        Route::post('getTable/{id?}', [ScriptController::class, 'getTable']);
        Route::get('changeStatus/{id}', [ScriptController::class, 'changeStatus']);
        Route::get('edit_detail_child/{id?}', [ScriptController::class, 'edit_detail_child']);
        Route::post('submit_edit_detail_child/{id?}', [ScriptController::class, 'submit_edit_detail_child']);
        Route::post('add_detail_parent', [ScriptController::class, 'add_detail_parent']);
        Route::post('delete_robot_detail/{id?}', [ScriptController::class, 'delete_robot_detail']);
        Route::post('change_status_detail/{id?}', [ScriptController::class, 'change_status_detail']);
        Route::get('changeStatusDefault/{id}', [ScriptController::class, 'changeStatusDefault']);
        Route::get('changeStatusOrder/{id}', [ScriptController::class, 'changeStatusOrder']);
        Route::get('changeStatusAppendProduct/{id}', [ScriptController::class, 'changeStatusAppendProduct']);
        Route::post('submit_content/{id}', [ScriptController::class, 'submit_content']);
        Route::get('detail_content/{id}', [ScriptController::class, 'detail_content']);
    });

    Route::group(['prefix' => 'lang'], function () {
        Route::get('app', [LangController::class, 'app']);
        Route::post('submit', [LangController::class, 'submit']);
    });

    Route::group(['prefix' => 'introduction_app'], function () {
        Route::get('list', [IntroductionAppController::class, 'get_list']);
        Route::post('getTable', [IntroductionAppController::class, 'getTable']);
        Route::get('detail/{id?}', [IntroductionAppController::class, 'get_detail']);
        Route::post('submit/{id}', [IntroductionAppController::class, 'submit']);
        Route::get('delete/{id}', [IntroductionAppController::class, 'delete']);
        Route::get('changeStatus/{id}', [IntroductionAppController::class, 'changeStatus']);
        Route::post('order_by', [IntroductionAppController::class, 'order_by']);
    });


    Route::group(['prefix' => 'variant'], function () {
        Route::get('list', [VariantController::class, 'get_list']);
        Route::post('getTable', [VariantController::class, 'getTable']);
        Route::get('detail/{id?}', [VariantController::class, 'detail']);
        Route::get('detail_child/{id_parent?}/{id?}', [VariantController::class, 'detail_child']);
        Route::post('submit/{id?}', [VariantController::class, 'submit']);
        Route::post('submit_child/{id_parent?}/{id?}', [VariantController::class, 'submit_child']);
        Route::get('delete/{id}', [VariantController::class, 'delete']);
        Route::get('delete_child/{id}', [VariantController::class, 'delete_child']);
        Route::get('changeStatus/{id}', [VariantController::class, 'changeStatus']);
        Route::get('changeStatusOption/{id}', [VariantController::class, 'changeStatusOption']);
        Route::get('getVariantOptions', [VariantController::class, 'getVariantOptions']);
    });


    Route::group(['prefix' => 'rank_community'], function () {
        Route::get('list', [RankCommunityController::class, 'get_list']);
        Route::post('getList', [RankCommunityController::class, 'getList']);
        Route::get('detail/{id?}', [RankCommunityController::class, 'get_detail']);
        Route::post('detail/{id?}', [RankCommunityController::class, 'detail']);
    });

    Route::group(['prefix' => 'challenge'], function () {
        Route::get('list', [ChallengeController::class, 'get_list']);
        Route::post('getList', [ChallengeController::class, 'getList']);
        Route::get('detail/{id?}', [ChallengeController::class, 'get_detail']);
        Route::post('detail/{id?}', [ChallengeController::class, 'detail']);
        Route::post('changeStatus', [ChallengeController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'challenge_me'], function () {
        Route::get('list', [ChallengeMeController::class, 'get_list']);
        Route::post('getList', [ChallengeMeController::class, 'getList']);
        Route::get('detail/{id?}', [ChallengeMeController::class, 'get_detail']);
        Route::get('view/{id?}', [ChallengeMeController::class, 'view']);
        Route::post('countAll', [ChallengeMeController::class, 'countAll']);
        Route::get('delete/{id}', [ChallengeMeController::class, 'delete']);
    });

    Route::group(['prefix' => 'community'], function () {
        Route::get('list', [CommunityController::class, 'get_list']);
        Route::post('getList', [CommunityController::class, 'getList']);
        Route::get('view/{id?}', [CommunityController::class, 'view']);
        Route::post('countAll', [CommunityController::class, 'countAll']);
        Route::get('delete/{id}', [CommunityController::class, 'delete']);
        Route::get('toggleHide/{id}', [CommunityController::class, 'toggleHide']);
        Route::get('reports', [CommunityController::class, 'get_report_list']);
        Route::post('getReportList', [CommunityController::class, 'getReportList']);
        Route::get('violations', [CommunityController::class, 'get_violation_list']);
        Route::post('getViolationTable', [CommunityController::class, 'getViolationTable']);
        Route::post('violations/store', [CommunityController::class, 'storeViolation']);
        Route::post('violations/update/{id}', [CommunityController::class, 'updateViolation']);
        Route::get('violations/delete/{id}', [CommunityController::class, 'deleteViolation']);
    });
    Route::group(['prefix' => 'transaction_payment'], function () {
        Route::get('list', [TransactionPaymentController::class, 'get_list']);
        Route::post('getList', [TransactionPaymentController::class, 'getList']);
//        Route::get('detail/{id?}', [TransactionPaymentController::class, 'get_detail']);
//        Route::get('view/{id?}', [TransactionPaymentController::class, 'view']);
        Route::get('changeStatus/{id}', [TransactionPaymentController::class, 'changeStatus']);
        Route::post('countAll', [TransactionPaymentController::class, 'countAll']);
        Route::get('delete/{id}', [TransactionPaymentController::class, 'delete']);
    });

    Route::group(['prefix' => 'bonus_payment'], function () {
        Route::get('list', [BonusPaymentController::class, 'get_list']);
        Route::post('getList', [BonusPaymentController::class, 'getList']);
        Route::get('changeStatus/{id}', [BonusPaymentController::class, 'changeStatus']);
        Route::post('countAll', [BonusPaymentController::class, 'countAll']);
        Route::get('delete/{id}', [BonusPaymentController::class, 'delete']);
        Route::get('detail/{id?}', [BonusPaymentController::class, 'get_detail']);
        Route::post('submit/{id}', [BonusPaymentController::class, 'submit']);
    });

    Route::group(['prefix' => 'report_violation'], function () {
        Route::get('list', [ReportViolationModeController::class, 'get_list']);
        Route::post('getReportViolation', [ReportViolationModeController::class, 'getReportViolation']);
        Route::get('detail/{id?}', [ReportViolationModeController::class, 'get_detail']);
        Route::post('submit/{id}', [ReportViolationModeController::class, 'submit']);
        Route::get('delete/{id}', [ReportViolationModeController::class, 'delete']);
    });

    Route::group(['prefix' => 'video'], function () {
        Route::get('tips', [VideoFileController::class, 'tips']);
        Route::post('getTips', [VideoFileController::class, 'getTips']);
        Route::get('detail_tips/{id?}', [VideoFileController::class, 'detail_tips']);
        Route::post('submit_tips/{id?}', [VideoFileController::class, 'submit_tips']);
        Route::get('changeStatus/{id}/{type}', [VideoFileController::class, 'changeStatus']);
        Route::get('view_tips/{id}', [VideoFileController::class, 'view_tips']);
        Route::get('renderVideo/{id}', [VideoFileController::class, 'renderVideo']);

        Route::get('review', [VideoFileController::class, 'review']);
        Route::post('getReview', [VideoFileController::class, 'getReview']);

        Route::get('elearning', [VideoFileController::class, 'elearning']);
        Route::post('getElearning', [VideoFileController::class, 'getElearning']);
        Route::get('view_elearning/{id?}', [VideoFileController::class, 'view_elearning']);
        Route::get('detail_elearning/{id?}', [VideoFileController::class, 'detail_elearning']);
        Route::post('submit_elearning/{id?}', [VideoFileController::class, 'submit_elearning']);
        Route::post('submit_video_elearning/{id_elearning}/{id?}', [VideoFileController::class, 'submit_video_elearning']);
        Route::get('setup_video_elearning/{id}', [VideoFileController::class, 'setup_video_elearning']);
        Route::get('detail_video_elearning/{id_elearning}/{id?}', [VideoFileController::class, 'detail_video_elearning']);

        Route::post('get_video_elearning/{id_elearning}', [VideoFileController::class, 'get_video_elearning']);
        Route::get('changeStatusElearning/{id}', [VideoFileController::class, 'changeStatusElearning']);
        Route::get('changeStatusCheckNewElearning/{id}', [VideoFileController::class, 'changeStatusCheckNewElearning']);
        Route::post('order_by_video', [VideoFileController::class, 'order_by_video']);
        Route::get('delete_video_elearning/{id_elearning}/{id}', [VideoFileController::class, 'delete_video_elearning']);
        Route::get('delete_elearning/{id}', [VideoFileController::class, 'delete_elearning']);
        Route::get('delete/{id}/{type}', [VideoFileController::class, 'delete']);
        Route::post('getCustomerUnlock', [VideoFileController::class, 'getCustomerUnlock']);
        Route::get('delete_unlock/{id}', [VideoFileController::class, 'delete_unlock']);
        Route::get('changeShowHome/{id}/{type}', [VideoFileController::class, 'changeShowHome']);
    });

    Route::group(['prefix' => 'report'], function () {
        Route::get('report_transaction', [ReportController::class, 'report_transaction']);
        Route::post('getListReportTransaction', [ReportController::class, 'getListReportTransaction']);
        Route::get('report_transaction_detail', [ReportController::class, 'report_transaction_detail']);
        Route::post('getListReportTransactionDetail', [ReportController::class, 'getListReportTransactionDetail']);
        Route::get('report_booking', [ReportController::class, 'report_booking']);
        Route::post('getListReportBooking', [ReportController::class, 'getListReportBooking']);
        Route::post('getModalBookingDetail', [ReportController::class, 'getModalBookingDetail']);
        Route::get('report_booking_detail', [ReportController::class, 'report_booking_detail']);
        Route::post('getListReportBookingDetail', [ReportController::class, 'getListReportBookingDetail']);
    });

    
    Route::group(['prefix' => 'invoice'], function () {
        Route::get('waiting_invoice', [InvoiceController::class, 'waiting_invoice']);
        Route::post('getListWaitingInvoice', [InvoiceController::class, 'getListWaitingInvoice']);
        Route::post('detailInvoice', [InvoiceController::class, 'detailInvoice']);
        Route::post('submitDetailInvoice', [InvoiceController::class, 'submitDetailInvoice']);
        Route::get('list', [InvoiceController::class, 'listInvoice']);
        Route::post('getListInvoice', [InvoiceController::class, 'getListInvoice']);
        Route::post('viewInvoice', [InvoiceController::class, 'viewInvoice']);
        Route::get('deleteInvoice/{id}', [InvoiceController::class, 'deleteInvoice']);
        Route::get('view/{id}', [InvoiceController::class, 'view']);
    });

    Route::group(['prefix' => 'code_leader'], function () {
        Route::get('list', [CodeLeaderController::class, 'get_list']);
        Route::post('getList', [CodeLeaderController::class, 'getList']);
        Route::get('detail/{id?}', [CodeLeaderController::class, 'get_detail']);
        Route::post('submit/{id?}', [CodeLeaderController::class, 'submit']);
        Route::get('delete/{id}', [CodeLeaderController::class, 'delete']);
        Route::get('changeStatus/{id}', [CodeLeaderController::class, 'changeStatus']);
        Route::post('addClient', [CodeLeaderController::class, 'addClient']);
        Route::post('countAll', [CodeLeaderController::class, 'countAll']);
    });

});
