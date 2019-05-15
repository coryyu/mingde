<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix'=>'v4','middleware'=>['postkey']],function(){

    Route::any('login/login',['uses'=>'V4\LoginController@login']);//登录
    Route::any('login/verifycode',['uses'=>'V4\LoginController@verifyCode']);//验证 验证码
    Route::any('login/loginphone',['uses'=>'V4\LoginController@loginPhone']);//验证码登录
    Route::any('login/register',['uses'=>'V4\LoginController@register']);//注册
    Route::any('login/editpassword',['uses'=>'V4\LoginController@editPassword']);//修改密码
    Route::any('login/wxlogin',['uses'=>'V4\LoginController@wxLogin']);//微信登录
    Route::any('login/bindingphone',['uses'=>'V4\LoginController@bindingPhone']);//微信登录 绑定手机号
    Route::any('login/bindingnewphone',['uses'=>'V4\LoginController@bindingNewPhone']);//新增手机号 绑定微信


    Route::any('home/createorupdatehome',['uses'=>'V4\HomeController@createOrUpdateHome']);//创建家庭成员
    Route::any('home/gethomemember',['uses'=>'V4\HomeController@getHomeMember']);//家庭成员页
    Route::any('home/delhome',['uses'=>'V4\HomeController@delHome']);//家庭成员页
    Route::any('home/getchildrenlist',['uses'=>'V4\HomeController@getChildrenList']);//家庭成员页

    Route::any('user/getuserinfo',['uses'=>'V4\UserController@getUserInfo']);//获取用户信息
    Route::any('user/updateusername',['uses'=>'V4\UserController@updateUserName']);//修改用户名
    Route::any('user/output',['uses'=>'V4\UserController@outPut']);//退出登录
    Route::any('user/signin',['uses'=>'V4\UserController@signIn']);//签到
    Route::any('user/feedback',['uses'=>'V4\UserController@feedBack']);//意见反馈
    Route::any('user/userupload',['uses'=>'V4\UserController@userUpload']);//上传图片
    Route::any('user/useravatarupload',['uses'=>'V4\UserController@userAvatarUpload']);//更新头像
    Route::any('user/getmessagelist',['uses'=>'V4\UserController@getMessageList']);//获取消息列表
    Route::any('user/ismessage',['uses'=>'V4\UserController@isMessage']);//已读消息
    Route::any('user/gethelpcenter',['uses'=>'V4\UserController@getHelpcenter']);//帮助中心
    Route::any('user/updatephone',['uses'=>'V4\UserController@updatePhone']);//修改手机号

    Route::any('member/getcardlist',['uses'=>'V4\MemberController@getCardList']);//会员卡类型获取
    Route::any('member/getmemberinfo',['uses'=>'V4\MemberController@getMemberInfo']);//读取会员信息
    Route::any('member/getorderlist',['uses'=>'V4\MemberController@getOrderList']);//获取购买记录
    Route::any('member/addorder',['uses'=>'V4\MemberController@addOrder']);//提交生成预订单
    Route::any('member/isorderok',['uses'=>'V4\MemberController@isOrderOk']);//确定订单、支付成功
    Route::any('member/activatemember',['uses'=>'V4\MemberController@activateMember']);//实体卡信息
    Route::any('member/activatememberdo',['uses'=>'V4\MemberController@activateMemberDo']);//实体卡激活
    Route::any('member/getsaleorder',['uses'=>'V4\MemberController@getSaleOrder']);//会员推荐记录

    Route::any('docter/getdocterlist',['uses'=>'V4\DocterController@getDocterList']);//医生列表
    Route::any('docter/followdocter',['uses'=>'V4\DocterController@followDocter']);//关注医生

    Route::any('testrecord/inserttestrecord',['uses'=>'V4\TestRecordController@insertTestRecord']);//上传检测记录
    Route::any('testrecord/searchItem',['uses'=>'V4\TestRecordController@searchItem']);//快速搜索项
    Route::any('testrecord/gettestrecord',['uses'=>'V4\TestRecordController@getTestRecord']);//检查记录列表
    Route::any('testrecord/selectcode',['uses'=>'V4\TestRecordController@selectCode']);//检测片唯一性
    Route::any('testrecord/uprecordtitle',['uses'=>'V4\TestRecordController@upRecordTitle']);//检测片唯一性
    Route::any('testrecord/recorddetail',['uses'=>'V4\TestRecordController@recordDetail']);//检测结果 分析

    Route::any('integral/getintegrallist',['uses'=>'V4\IntegralController@getIntegralList']);//获取积分详情

    Route::any('lailai/addlailailist',['uses'=>'V4\LailaiController@addLailaiList']);//添加赖赖扣记录
    Route::any('lailai/getlailailist',['uses'=>'V4\LailaiController@getLailaiList']);//获取赖赖扣记录
    Route::any('lailai/test',['uses'=>'V4\LailaiController@test']);

    Route::any('appconf/getphonemodel',['uses'=>'V4\AppConfController@getPhoneModel']);//获取开放机型
    Route::any('appconf/algoversion',['uses'=>'V4\AppConfController@algoVersion']);//算法表  版本对比
    Route::any('appconf/updatetable',['uses'=>'V4\AppConfController@updateTable']);//更新表信息
    Route::any('appconf/tableversion',['uses'=>'V4\AppConfController@tableVersion']);//更新表信息
    Route::any('appconf/getadv',['uses'=>'V4\AppConfController@getAdv']);//更新表信息

    Route::any('address/getcity',['uses'=>'V4\AddressController@getCity']);//获取城市信息
    Route::any('address/addaddress',['uses'=>'V4\AddressController@addAddress']);//获取城市信息
    Route::any('address/getaddresslist',['uses'=>'V4\AddressController@getAddressList']);//获取城市信息
    Route::any('address/setdefault',['uses'=>'V4\AddressController@setDefault']);//设置默认地址
    Route::any('address/deladdress',['uses'=>'V4\AddressController@delAddress']);//删除地址



});
Route::any('v4/wx/notifyurl',['uses'=>'V4\NotifyUrlController@notifyUrl']);
Route::any('v4/helpcenter/index',['uses'=>'V4\HelpcenterController@index']);
Route::any('v4/officialtoken/index',['uses'=>'V4\OfficialTokenController@Index']);


Route::group(['prefix'=>'mingde'],function(){
    Route::any('user/getuserinfo',['uses'=>'Mingde\UserController@getUserInfo']);//获取用户权限
    Route::any('user/getopenid',['uses'=>'Mingde\UserController@getOpenid']);//获取用户权限

    Route::any('index/indexrecommend',['uses'=>'Mingde\IndexController@indexRecommend']);//首页推荐
    Route::any('index/indexyanxuerec',['uses'=>'Mingde\IndexController@indexYanxueRec']);//研学推荐

    Route::any('index/indexdetail',['uses'=>'Mingde\IndexController@indexDetail']);//商品详情

    Route::any('order/uploadimg',['uses'=>'Mingde\OrderController@uploadImg']);//上传图片
    Route::any('order/babylist',['uses'=>'Mingde\OrderController@babyList']);//出行人列表
    Route::any('order/addorsetbaby',['uses'=>'Mingde\OrderController@addOrSetBaby']);//添加或修改出行人
    Route::any('order/babydel',['uses'=>'Mingde\OrderController@babyDel']);//删除出行人
    Route::any('order/addorseguarder',['uses'=>'Mingde\OrderController@addOrSeGuarder']);//添加或修改监护人
    Route::any('order/guarderlist',['uses'=>'Mingde\OrderController@guarderList']);//监护人列表
    Route::any('order/guarderdel',['uses'=>'Mingde\OrderController@guarderDel']);//删除监护人
});