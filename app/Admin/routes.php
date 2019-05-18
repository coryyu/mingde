<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('user', UserController::class);
    $router->resource('doctor', DoctorController::class);
    $router->resource('appversion', AppVersionController::class);
    $router->resource('appuser', AppuserController::class);
    $router->resource('appuser', AppuserController::class);
    $router->resource('apphome', ApphomeController::class);
    $router->resource('testsrecord', TestsRecordController::class);
    $router->resource('helpcenter', HelpCenterController::class);
    $router->resource('message', MessageController::class);
    $router->resource('table', TableController::class);
    $router->resource('signin', SigninController::class);
    $router->resource('search', SearchController::class);
    $router->resource('between', BetweenController::class);
    $router->resource('kouxrecord', KouxrecordController::class);
    $router->resource('memberorder', MemberOrderController::class);
    $router->resource('memberindex', MemberIndexController::class);
    $router->resource('tablealgo', TableAlgoController::class);
    $router->resource('phonemodel', PhoneModelController::class);
    $router->resource('integralconfig', IntegralConfigController::class);
    $router->resource('membercard', MemberCardController::class);
    $router->resource('appconfig', AppconfigController::class);
    $router->resource('algosysdetectitem', AlgoSysdetectitemController::class);
    $router->resource('algoitemstandard', AlgoItemstandardController::class);
    $router->resource('algodetect', AlgoDetectController::class);
    $router->resource('memberchange', MemberChangeController::class);
    $router->any('algoitemstandard/upload', 'AlgoItemstandardController@upload');

    $router->resource('classproduct', ClassProductController::class);
    $router->resource('classrecommend', ClassreCommendController::class);
    $router->resource('classyanxuerecommend', ClassYanxuereCommendController::class);
    $router->resource('classagreement', ClassAgreementController::class);

});

