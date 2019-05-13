<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\AppSignIn;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\V4\CommonController as Common;

class AddressController extends Common
{

    /**
     *添加修改地址
     * 2019-2-10
     * by chenyu
     */
    public function addAddress(Request $request)
    {
        $addid = $request->input('addid');
        $name = $request->input('name');
        $phone = $request->input('phone');
        $province = $request->input('province');
        $city = $request->input('city');
        $area = $request->input('area');
        $text = $request->input('text');
        if(empty($name) || empty($phone)|| empty($province)|| empty($city)|| empty($area)|| empty($text)){
            return api_json([],10001,'参数错误');
        }
        if($addid){//修改
            $data['province'] = $province;
            $data['city'] = $city;
            $data['area'] = $area;
            $data['text'] = $text;
            $data['name'] = $name;
            $data['add_tel'] = $phone;
            $data['update_at'] = today_time();
            $up = DB::table('app_user_add')
                ->where('id',$addid)
                ->update($data);
            if($up>0){
                return api_json(['addid'=>$addid],200,'修改成功');
            }else{
                return api_json([],90151,'地址修改失败');
            }
        }else{//新增
            $data['uid'] = $this->userinfo->id;
            $data['province'] = $province;
            $data['city'] = $city;
            $data['area'] = $area;
            $data['text'] = $text;
            $data['name'] = $name;
            $data['add_tel'] = $phone;
            $data['create_at'] = today_time();
            $data['update_at'] = $data['create_at'];
            $data['is_del'] = 0;
            $data['is_default'] = 0;

            $addid = DB::table('app_user_add')->insertGetId($data);
            if($addid>0){
                return api_json(['addid'=>$addid],200,'添加成功');
            }else{
                return api_json([],90151,'地址添加失败');
            }
        }
    }


    /**
     * 获取地区
     * 2019-2-10
     * by chenyu
     **/
    public function getCity(Request $request)
    {

        $level = $request->input('city_level');//0省,1市,2区县
        $parent = $request->input('city_parent');

        $city = DB::table('admin_city')->select('city_code','city_name','city_parent','city_level','area')->where('car_open',1);

        switch ($level){

            case 0://省
                $city->where('city_level',0);
                break;
            case 1://市
                $city->where('city_level',1);
                $city->where('city_parent',$parent);
                break;
            case 2://区
                $city->where('city_level',2);
                $city->where('city_parent',$parent);
                break;
            default:
        }
        $res = $city->get();

        return api_json(['info'=>$res],200,'获取成功');
    }


    /**
     *地址列表
     * 2019-2-11
     * by chenyu
     */
    public function getAddressList()
    {

        $address = DB::table('app_user_add')
            ->select('id','province','city','area','text','name','add_tel','is_default')
            ->where('uid',$this->userinfo->id)
            ->where('is_del',0)
            ->get();

        if ($address->isEmpty()) {
            return api_json([],10002,'无数据');
        }else{
            return api_json(['info'=>$address->toArray()],200,'获取成功');
        }
    }
    /**
     *设置默认地址
     * 2019-2-11
     * by chenyu
     */
    public function setDefault(Request $request)
    {
        $addid = $request->input('addid');

        $res = DB::table('app_user_add')
            ->where('uid', $this->userinfo->id)
            ->where('id', $addid)
            ->first();
        if(!$res){
            return api_json([],90152,'地址不存在');
        }

        //开启事务
        DB::beginTransaction();
        try {
            DB::table('app_user_add')
                ->where('uid', $this->userinfo->id)
                ->update(['is_default'=> '0']);

            $deid = DB::table('app_user_add')
                ->where('id', $addid)
                ->update(['is_default'=>'1','update_at'=>today_time()]);

            DB::commit();
        }catch(\Exception $exception) {
            //事务回滚
            DB::rollBack();
            return api_json([],90126,'失败');
//            return back()->withErrors($exception->getMessage())->withInput();
        }

        if($deid > 0 ){
            return api_json([],200,'修改成功');
        }else{
            return api_json([],90126,'失败');
        }
    }
    /**
     *删除地址
     * 2019-2-11
     * by chenyu
     */
    public function delAddress(Request $request)
    {
        $addid = $request->input('addid');

        $res = DB::table('app_user_add')
            ->where('uid', $this->userinfo->id)
            ->where('id', $addid)
            ->first();
        if(!$res){
            return api_json([],90152,'地址不存在');
        }
        $del = DB::table('app_user_add')
            ->where('id',$addid)
            ->update(['is_del'=>1,'update_at'=>today_time()]);
        if($del >0){
            return api_json([],200,'删除成功');
        }else{
            return api_json([],90153,'地址删除失败');
        }

    }
}