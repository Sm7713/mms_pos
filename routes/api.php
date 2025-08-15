<?php

use App\Http\Controllers\AccessPointController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\MaintainerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RouterOsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ReportsAdminController;
use App\Http\Controllers\ReportsSellPointController;
use App\Http\Controllers\SellPointController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\testSeeder;
use App\Models\sellPoint;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->id;
});
Route::middleware('auth:sanctum')->controller(AuthController::class)->group(function(){
    Route::post('/logout','logout');
    Route::post('/auth','auth')->middleware('checkSellPoint','is_active');
    //sell point
    Route::post('/makePayment',[PaymentController::class,'makePaymentByUser'])->middleware('checkSellPoint');
    Route::get('/myPayments',[PaymentController::class,'getAllPaymentsByUser'])->middleware('checkSellPoint');
    Route::get('/getPayments',[PaymentController::class,'getAllPayments']);
    Route::get('/deletePayment/{id}',[PaymentController::class,'deletePayment']);
});

// AuthController
Route::controller(AuthController::class)->group(function(){
    Route::post('/register','register');
    Route::post('/login','login');
});
// Route::middleware('auth:sanctum')->controller()

Route::prefix('sellPoint')->controller(OrderController::class)->middleware('auth:sanctum')->group(function(){
    Route::post('/retrive','retriveCards');
});
// ForgotPassword Controller
Route::post('password/email',[ForgotPasswordController::class,'forgotPassword']);
Route::post('password/code/check', [ForgotPasswordController::class,'codeCheck']);
Route::post('password/reset', [ForgotPasswordController::class,'resetPassword']);
// Notification Controller
Route::middleware('auth:sanctum')->controller(NotificationController::class)->group(function(){
    Route::get('/unread_notifications','getUnreadNotifications');
    Route::post('/markAsRead/{id}','markAsRead');
    Route::get('markAll','markAllAsRead');
    Route::post('all_notifications','getAllNotifications');
});
// Reports Section
Route::middleware('auth:sanctum')->controller(PaymentController::class)->group(function(){
    Route::get('/reportDay','reportByDay');
    Route::get('/repostMonth','reportByMonth');
    Route::get('/reportYear','reportByYear');
    Route::get('/reportBetween','reportByDateRange');
    Route::get('/reportSellPoint','reportBySellPoint');
});

Route::middleware('auth:sanctum')->controller(CommentsController::class)->group(function(){
    Route::post('/addComment','addComment');
    Route::get('/allComments','getAllComments');
    Route::post('/replyComment','replyComment');
    Route::get('/allCommentsByUser','getAllCommentsByUser');
    Route::get('/getReadComments','getAllCommentsRead');
    Route::get('/markAsReadComment/{id}','markAsRead');
    Route::get('/deleteComment/{id}','deleteComment');
    Route::post('/assignComment','assignComment');
});

Route::controller(ReportsAdminController::class)->group(function(){
    Route::get('/reportCards','categoryOrderSellReport');
    Route::get('/count_cards','count_cards');
    Route::get('/users_count','price_cards');
    Route::get('/sum_payment','sum_payment');
    Route::get('/reportCardsPrice','categoryOrderSellReportAdvance');
});

Route::middleware('auth:sanctum')->controller(ReportsSellPointController::class)->group(function(){
    Route::get('/sellPointReportCards','categoryOrderSellReportUser');
    Route::post('/sellPointReportCardsDate','categoryOrderSellReportRange');
    Route::get('/sellPointReportByDay','reportByDay');
    Route::get('/sellPointReportByMonth','reportByMonth');
    Route::get('/sellPointReportByYear','reportByYear');
    Route::get('/sellPointReportAll','reportAll');
    Route::post('/sellPointReportByDateRange','reportByDateRange');
    Route::get('/sellPointReportBySellPoint','reportBySellPoint');
});

Route::prefix('admin')->group(function () {
    Route::get('/test',[testSeeder::class,'test']);
    //sell point route
    Route::post('/sellCards',[CardController::class,'sellCard']);

    Route::get('/con',[RouterOsController::class,'connection']);

    Route::get('/connetion',[RouterOsController::class,'routeros_connection']);

    Route::get('/db',[RouterOsController::class,'save_db']);

    Route::get('/env',[RouterOsController::class,'test_env']);

    Route::Post('/createCategory',[CategoryController::class,'createCategory']);

    Route::post('/editCategory',[CategoryController::class,'EditCategory']);

    Route::post('/removeCategory',[CategoryController::class,'DeleteCategory']);

    Route::post('/disableCategory',[CategoryController::class,'disableCategory']);

    Route::post('/show',[CategoryController::class,'ShowCategories']);
    
    //sell point route
    Route::get('/getCategories',[CategoryController::class,'getCategoriesDB']);
    //admin
    Route::get('/getCategoriesforAdmin',[CategoryController::class,'getCategoriesforAdmin']);

    Route::get('/card',[CategoryController::class,'test_create_cards']);

    Route::post('/changeStatusCard',[CardController::class,'changeStatusCard']);

    Route::post('/create',[CardController::class,'createCards']);

    Route::get('/generate',[CategoryController::class,'generateRandomString']);

    Route::get('/print',[CardController::class,'ShowCards']);

    Route::get('/c',[CardController::class,'getCards']);

    Route::get('/change',[CardController::class,'changeStatus']);

    Route::get('/pdf',[CardController::class,'generatePDF']);

    Route::get('/session',[CardController::class,'getSession']);

    Route::get('/getLogs/{user}',[CardController::class,'getLogsByCard']);

    Route::get('/active',[CardController::class,'activeUsersCards']);

    Route::post('/deleteCard',[CardController::class,'deleteCard']);

    Route::post('/card_details',[CardController::class,'cardWithCategory']);

    Route::controller(OwnerController::class)->group(function(){
        Route::post('/addOwner','addOwner');
        Route::get('/getOwners','getAllOwners');
        Route::get('/getOwner/{id}','getOwner');
        Route::delete('/deleteOwner/{id}','deleteOwner');
        Route::post('/updateOwner','updateOwner');
        Route::post('/sellPointWithOwner','sellPointWithOwner');
    });

    Route::get('/payment',[testSeeder::class,'getPayments']);
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/confirm',[PaymentController::class,'confirmPayment'])->middleware('is_admin');
    Route::get('/mark',[OrderController::class,'markAsRead']);
    Route::get('/myorders',[OrderController::class,'getOrdersToSellPoint']);
    // Route::get('/orderDetails',[OrderController::class,'makeCardsWithOrder'])->middleware('is_admin');
    Route::post('/order',[OrderController::class,'makeOrder'])->middleware('is_active');
    Route::get('/allCards',[CardController::class,'getAllCardByUser']);

});

Route::post('/orderDetails',[OrderController::class,'makeCardsWithOrder']);
    Route::post('/getorderDetalis',[OrderController::class,'orderDetails']);
    
    Route::get('/orders',OrderController::class);
    Route::get('/delete/{id}',[OrderController::class,'deleteOrder']);
    Route::get('report',[CardController::class,'reportCards']);
    Route::post('/forget',[AuthController::class,'forgetPassword']);
    
    
    //Subscribers
    Route::post('/addSubscriber',[SubscriberController::class,'registerSubscriber']);
    Route::post('/changeSubscriber',[SubscriberController::class,'changeStatusSubscriber']);
    Route::get('/getSubscribers',SubscriberController::class);
    Route::post('/getSpecificSubscriber',[SubscriberController::class,'getSpecificSubscriber']);
    Route::post('/deleteSubscriber',[SubscriberController::class,'deleteSubscriber']);
    Route::post('/editSubscriber',[SubscriberController::class,'editSubscriberInfo']);
    Route::get('/getSessions/{user}',[SubscriberController::class,'getLogsBySubscriber']);
    Route::get('/generalInfo',[SubscriberController::class,'generalInfo'])->middleware('auth:sanctum');
    Route::get('/set',[SubscriberController::class,'test']);
    //Maintainer
    Route::post('/addMaintainer',[MaintainerController::class,'registerMaintainer']);
    Route::get('/getMaintainer',MaintainerController::class);
    Route::post('/changeMaintainer',[MaintainerController::class,'changeStatusMaintainer']);
    Route::post('/deleteMaintainer',[MaintainerController::class,'deleteMaintainer']);
    
    //sell Point Controller
    Route::controller(SellPointController::class)->group(function(){
        Route::post('/addSellPoint','addSellPoint');
        Route::get('/sellpoint',SellPointController::class);
        Route::post('/changeSellPoint','changeStatusSellPoint');
        Route::post('/getSellPointData','getSellPointData');
        Route::get('/profileSellPoint','profileSellPoint')->middleware('auth:sanctum');
        Route::get('/profileOwner','profileOwner')->middleware('auth:sanctum');
    });
    Route::post('/qwe',[SellPointController::class,'accountReports'])->middleware('auth:sanctum');
    // Access Point Controller
    Route::controller(AccessPointController::class)->group(function(){
        Route::post('/addAccessPoint','addAccessPoint');
        Route::get('/showAllAccessPoints','showAllAccessPoints');
        Route::get('/deleteAccessPoint/{id}','deleteAccessPoint');
        Route::get('/getAccessPoint/{id}','getAccessPoint');
        Route::post('/updateAccessPoint','updateAccessPoint');
    });

    Route::controller(PositionController::class)->group(function(){
        Route::post('/addPoistion','store');
        Route::get('/deletePosition','destroy');
        Route::get('/allPositions','index');
    });
    // Route::post('/addSellPoint',[SellPointController::class,'addSellPoint']);
    // Route::get('/sellpoint',SellPointController::class);
    // Route::get('/changeSellPoint',[SellPointController::class,'changeStatusSellPoint']);
    // Route::post('/getSellPointData',[SellPointController::class,'getSellPointData']);
});