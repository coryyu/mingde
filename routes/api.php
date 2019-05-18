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

    Route::any('login/login',['uses'=>'V4\LoginController@login']);//��¼
    Route::any('login/verifycode',['uses'=>'V4\LoginController@verifyCode']);//��֤ ��֤��
    Route::any('login/loginphone',['uses'=>'V4\LoginController@loginPhone']);//��֤���¼
    Route::any('login/register',['uses'=>'V4\LoginController@register']);//ע��
    Route::any('login/editpassword',['uses'=>'V4\LoginController@editPassword']);//�޸�����
    Route::any('login/wxlogin',['uses'=>'V4\LoginController@wxLogin']);//΢�ŵ�¼
    Route::any('login/bindingphone',['uses'=>'V4\LoginController@bindingPhone']);//΢�ŵ�¼ ���ֻ���
    Route::any('login/bindingnewphone',['uses'=>'V4\LoginController@bindingNewPhone']);//�����ֻ��� ��΢��


    Route::any('home/createorupdatehome',['uses'=>'V4\HomeController@createOrUpdateHome']);//������ͥ��Ա
    Route::any('home/gethomemember',['uses'=>'V4\HomeController@getHomeMember']);//��ͥ��Աҳ
    Route::any('home/delhome',['uses'=>'V4\HomeController@delHome']);//��ͥ��Աҳ
    Route::any('home/getchildrenlist',['uses'=>'V4\HomeController@getChildrenList']);//��ͥ��Աҳ

    Route::any('user/getuserinfo',['uses'=>'V4\UserController@getUserInfo']);//��ȡ�û���Ϣ
    Route::any('user/updateusername',['uses'=>'V4\UserController@updateUserName']);//�޸��û���
    Route::any('user/output',['uses'=>'V4\UserController@outPut']);//�˳���¼
    Route::any('user/signin',['uses'=>'V4\UserController@signIn']);//ǩ��
    Route::any('user/feedback',['uses'=>'V4\UserController@feedBack']);//�������
    Route::any('user/userupload',['uses'=>'V4\UserController@userUpload']);//�ϴ�ͼƬ
    Route::any('user/useravatarupload',['uses'=>'V4\UserController@userAvatarUpload']);//����ͷ��
    Route::any('user/getmessagelist',['uses'=>'V4\UserController@getMessageList']);//��ȡ��Ϣ�б�
    Route::any('user/ismessage',['uses'=>'V4\UserController@isMessage']);//�Ѷ���Ϣ
    Route::any('user/gethelpcenter',['uses'=>'V4\UserController@getHelpcenter']);//��������
    Route::any('user/updatephone',['uses'=>'V4\UserController@updatePhone']);//�޸��ֻ���

    Route::any('member/getcardlist',['uses'=>'V4\MemberController@getCardList']);//��Ա�����ͻ�ȡ
    Route::any('member/getmemberinfo',['uses'=>'V4\MemberController@getMemberInfo']);//��ȡ��Ա��Ϣ
    Route::any('member/getorderlist',['uses'=>'V4\MemberController@getOrderList']);//��ȡ�����¼
    Route::any('member/addorder',['uses'=>'V4\MemberController@addOrder']);//�ύ����Ԥ����
    Route::any('member/isorderok',['uses'=>'V4\MemberController@isOrderOk']);//ȷ��������֧���ɹ�
    Route::any('member/activatemember',['uses'=>'V4\MemberController@activateMember']);//ʵ�忨��Ϣ
    Route::any('member/activatememberdo',['uses'=>'V4\MemberController@activateMemberDo']);//ʵ�忨����
    Route::any('member/getsaleorder',['uses'=>'V4\MemberController@getSaleOrder']);//��Ա�Ƽ���¼

    Route::any('docter/getdocterlist',['uses'=>'V4\DocterController@getDocterList']);//ҽ���б�
    Route::any('docter/followdocter',['uses'=>'V4\DocterController@followDocter']);//��עҽ��

    Route::any('testrecord/inserttestrecord',['uses'=>'V4\TestRecordController@insertTestRecord']);//�ϴ�����¼
    Route::any('testrecord/searchItem',['uses'=>'V4\TestRecordController@searchItem']);//����������
    Route::any('testrecord/gettestrecord',['uses'=>'V4\TestRecordController@getTestRecord']);//����¼�б�
    Route::any('testrecord/selectcode',['uses'=>'V4\TestRecordController@selectCode']);//���ƬΨһ��
    Route::any('testrecord/uprecordtitle',['uses'=>'V4\TestRecordController@upRecordTitle']);//���ƬΨһ��
    Route::any('testrecord/recorddetail',['uses'=>'V4\TestRecordController@recordDetail']);//����� ����

    Route::any('integral/getintegrallist',['uses'=>'V4\IntegralController@getIntegralList']);//��ȡ��������

    Route::any('lailai/addlailailist',['uses'=>'V4\LailaiController@addLailaiList']);//��������ۼ�¼
    Route::any('lailai/getlailailist',['uses'=>'V4\LailaiController@getLailaiList']);//��ȡ�����ۼ�¼
    Route::any('lailai/test',['uses'=>'V4\LailaiController@test']);

    Route::any('appconf/getphonemodel',['uses'=>'V4\AppConfController@getPhoneModel']);//��ȡ���Ż���
    Route::any('appconf/algoversion',['uses'=>'V4\AppConfController@algoVersion']);//�㷨��  �汾�Ա�
    Route::any('appconf/updatetable',['uses'=>'V4\AppConfController@updateTable']);//���±���Ϣ
    Route::any('appconf/tableversion',['uses'=>'V4\AppConfController@tableVersion']);//���±���Ϣ
    Route::any('appconf/getadv',['uses'=>'V4\AppConfController@getAdv']);//���±���Ϣ

    Route::any('address/getcity',['uses'=>'V4\AddressController@getCity']);//��ȡ������Ϣ
    Route::any('address/addaddress',['uses'=>'V4\AddressController@addAddress']);//��ȡ������Ϣ
    Route::any('address/getaddresslist',['uses'=>'V4\AddressController@getAddressList']);//��ȡ������Ϣ
    Route::any('address/setdefault',['uses'=>'V4\AddressController@setDefault']);//����Ĭ�ϵ�ַ
    Route::any('address/deladdress',['uses'=>'V4\AddressController@delAddress']);//ɾ����ַ



});
Route::any('v4/wx/notifyurl',['uses'=>'V4\NotifyUrlController@notifyUrl']);
Route::any('v4/helpcenter/index',['uses'=>'V4\HelpcenterController@index']);
Route::any('v4/officialtoken/index',['uses'=>'V4\OfficialTokenController@Index']);


Route::group(['prefix'=>'mingde'],function(){
    Route::any('user/getuserinfo',['uses'=>'Mingde\UserController@getUserInfo']);//��ȡ�û�Ȩ��
    Route::any('user/getopenid',['uses'=>'Mingde\UserController@getOpenid']);//��ȡ�û�Ȩ��

    Route::any('index/indexrecommend',['uses'=>'Mingde\IndexController@indexRecommend']);//��ҳ�Ƽ�
    Route::any('index/indexyanxuerec',['uses'=>'Mingde\IndexController@indexYanxueRec']);//��ѧ�Ƽ�

    Route::any('index/indexdetail',['uses'=>'Mingde\IndexController@indexDetail']);//��Ʒ����
    Route::any('index/gethomepage',['uses'=>'Mingde\IndexController@getHomePage']);//��Ʒ����
    Route::any('index/search',['uses'=>'Mingde\IndexController@search']);//�����б�
    Route::any('index/getcitylist',['uses'=>'Mingde\IndexController@getCityList']);//����

    Route::any('order/uploadimg',['uses'=>'Mingde\OrderController@uploadImg']);//�ϴ�ͼƬ
    Route::any('order/babylist',['uses'=>'Mingde\OrderController@babyList']);//�������б�
    Route::any('order/addorsetbaby',['uses'=>'Mingde\OrderController@addOrSetBaby']);//��ӻ��޸ĳ�����
    Route::any('order/babydel',['uses'=>'Mingde\OrderController@babyDel']);//ɾ��������
    Route::any('order/addorseguarder',['uses'=>'Mingde\OrderController@addOrSeGuarder']);//��ӻ��޸ļ໤��
    Route::any('order/guarderlist',['uses'=>'Mingde\OrderController@guarderList']);//�໤���б�
    Route::any('order/guarderdel',['uses'=>'Mingde\OrderController@guarderDel']);//ɾ���໤��
    Route::any('order/addorder',['uses'=>'Mingde\OrderController@addOrder']);//
    Route::any('order/isorderok',['uses'=>'Mingde\OrderController@isOrderOk']);//
    Route::any('order/orderlist',['uses'=>'Mingde\OrderController@orderList']);//
    Route::any('order/orderdetail',['uses'=>'Mingde\OrderController@orderDetail']);//
    Route::any('order/orderrefund',['uses'=>'Mingde\OrderController@orderRefund']);//
});