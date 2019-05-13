<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Controllers\V4\CommonController as Common;
use App\AppHome;

class HomeController extends Common
{

    /**
     *创建家庭成员
     * 2019-1-16
     * by chenyu
     */
    public function createOrUpdateHome(Request $request)
    {

        $homeid = $request->input("homeid");
        $name = $request->input("name");
        $sex = $request->input("sex");
        $age = $request->input("age");



        //开启事务
        DB::beginTransaction();
        try {
            if(empty($homeid)){
                //添加成员
                $data['name'] = $name;
                $data['sex'] = $sex;
                $data['age'] = $age;
                $data['is_use'] = 0;
                $data['create_at'] = today_time();
                $data['update_at'] =  $data['create_at'];
                $data['user_id'] =  $this->userinfo->id;
                $data['is_del'] =  0;

                $homeId = DB::table('app_home')->insertGetId($data);
                //更新user表
                DB::table('app_user')->where('id',$this->userinfo->id)->increment('home_sum');
            }else{
                $homes = DB::table('app_home')
                    ->where('id',$homeid)
                    ->where('user_id',$this->userinfo->id)
                    ->where('is_del',0)
                    ->first();
                if($homes){
                    $data['name'] = $name;
                    $data['sex'] = $sex;
                    $data['age'] = $age;
                    $data['update_at'] =  today_time();
                    $homeId = DB::table('app_home')
                        ->where('id',$homeid)
                        ->update($data);
                }
            }
            //提交
            DB::commit();
        }catch(\Exception $exception) {
            //事务回滚
            DB::rollBack();
            return api_json([],'90126','失败');
//            return back()->withErrors($exception->getMessage())->withInput();
        }

        if($homeId>0){//添加成员成功
            return api_json($data,200,'成功');
        }else{//添加成员失败
            return api_json([],90126,'失败');
        }
    }
    /**
     *家庭成员页
     * 2019-1-17
     * by chenyu
     **/
    public function getHomeMember(Request $request)
    {
        $homeid = $request->input("homeid");

        $home = DB::table('app_home')
            ->select('id','name','sex','age','create_at')
            ->where('id',$homeid)
            ->where('user_id',$this->userinfo->id)
            ->where('is_del',0)
            ->first();

        if($home){

            //读检测记录
            $home_list = DB::table('app_testsrecord')
                ->where('home_id',$homeid)
                ->where('is_del',0)
                ->orderBy('create_at_app','desc')
                ->first();


            //当月检测次数
            $date = date('Y-m-d',time());
            $tomonth_cou = DB::table('app_testsrecord')
                ->where('app_testsrecord.home_id',$homeid)
                ->where('app_testsrecord.is_del',0)
                ->where('app_testsrecord.come',1)
                ->where('app_testsrecord.create_at_app','>=',$date)
                ->count();
            $home->tomonth = $tomonth_cou;
            if($home_list){
                $home->last_time = $home_list->create_at_app;
            }else{
                $home->last_time = 0;
            }

            return api_json($home,200,'读取成功');

        }else{
            return api_json([],90130,'家庭成员不存在');
        }
    }

    /**
     *孩子管理
     * 2019-2-10
     * by chenyu
     */
    public function getChildrenList()
    {

        $home = DB::table('app_home')
            ->select('id','name','sex','age','create_at')
            ->where('user_id',$this->userinfo->id)
            ->where('is_del',0)
            ->get();

        if ($home->isEmpty()) {
            return api_json([],10002,'无数据');
        }else{
            return api_json(['info'=>$home->toArray()],200,'获取成功');
        }

    }
    /**
     *删除成员
     * 2019-01-22
     * by chenyu
     **/
    public function delHome(Request $request)
    {
        $homeid = $request->input('homeid');

        $homes = DB::table('app_home')
            ->where('id',$homeid)
            ->where('user_id',$this->userinfo->id)
            ->where('is_del',0)
            ->first();
        if($homes){

            DB::beginTransaction();
            try {
                DB::table('app_home')
                    ->where('id', $homeid)
                    ->update(['is_del' => 1, 'update_at' => today_time()]);

                DB::table('app_user')
                    ->where('id',$homes->user_id)
                    ->decrement('home_sum');

                DB::commit();
                return api_json(['homeid' => $homeid], 200, '删除成功');

            }catch(\Exception $exception) {
                //事务回滚
                DB::rollBack();
                return api_json([], 90139, '家庭成员删除失败');
                //            return back()->withErrors($exception->getMessage())->withInput();
            }
        }else{
            return api_json([],90130,'家庭成员不存在');
        }




    }

}