<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api_app\EventArticlesController;
use App\Http\Controllers\Api_app\SlideIntroduceAppController;

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

Route::group(['prefix' => 'event_articles', 'middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('get_detail', [EventArticlesController::class, 'get_detail']);
    Route::get('countAll', [EventArticlesController::class, 'countAll']);
    Route::post('submit', [EventArticlesController::class, 'submit']);
    Route::post('active', [EventArticlesController::class, 'active']);
    Route::post('change_is_hot', [EventArticlesController::class, 'change_is_hot']);
    Route::post('delete', [EventArticlesController::class, 'delete']);
});

Route::group(['prefix' => 'slide_introduce_app', 'middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('detail', [SlideIntroduceAppController::class, 'detail']);
    Route::get('countAll', [SlideIntroduceAppController::class, 'countAll']);
    Route::post('submit', [SlideIntroduceAppController::class, 'submit']);
    Route::post('order_by', [SlideIntroduceAppController::class, 'order_by']);
    Route::post('active', [SlideIntroduceAppController::class, 'active']);
    Route::post('delete', [SlideIntroduceAppController::class, 'delete']);
});

Route::group(['prefix' => 'slide_introduce_app'], function () {
    Route::get('get_list', [SlideIntroduceAppController::class, 'get_list']);
    Route::get('get_data_slide', [SlideIntroduceAppController::class, 'get_data_slide']);
});

Route::group(['prefix' => 'event_articles'], function () {
    Route::get('get_list', [EventArticlesController::class, 'get_list']);
    Route::get('info_data_articles_is_hot', [EventArticlesController::class, 'info_data_articles_is_hot']);
    Route::get('get_list_data', [EventArticlesController::class, 'get_list_data']);
    Route::post('get_list_by_ids', [EventArticlesController::class, 'get_list_by_ids']);
});

Route::get('api_list_data', [EventArticlesController::class, 'api_list_data']);
Route::get('api_list_detail/{slug}', [EventArticlesController::class, 'api_list_detail']);
Route::get('info_data_articles', [EventArticlesController::class, 'info_data_articles']);
Route::get('api_detail_to_app/{id}', [EventArticlesController::class, 'api_detail_to_app']);
