<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\AppSignIn;
use Illuminate\Support\Facades\Storage;

class AppConfController extends Controller
{

    /**
     *读取机型
     * 2019-1-25
     * by chenyu
     */
    public function getPhoneModel(Request $request)
    {
        $model = $request->input('model');
        $model_s = DB::table('app_model')
            ->select('model','brand','status','update_at')
            ->where('number',$model)
            ->first();
        if($model_s){
            return api_json($model_s,200,'获取成功');
        }else{
            return api_json([],90149,'该机型未在服务端备案');
        }
    }


    /**
     * 配置表的 版本对比
     * 2019-1-25
     * by chenyu
     **/
    public function tableVersion(Request $request)
    {

        $app_between = $request->input('app_between');
        $app_member_index = $request->input('app_member_index');
        $app_search = $request->input('app_search');


        if(!empty($app_between)){

            $app_betweens = DB::table('app_table')
                ->where('table','app_between')
                ->where('version',$app_between)
                ->where('is_del',0)
                ->first();
            if(!$app_betweens){
                $res_data['app_between'] = false;
            }else{
                $res_data['app_between'] = true;
            }
        }
        if(!empty($app_member_index)){

            $app_member_indexs = DB::table('app_table')
                ->where('table','app_member_index')
                ->where('version',$app_member_index)
                ->where('is_del',0)
                ->first();
            if(!$app_member_indexs){
                $res_data['app_member_index'] = false;
            }else{
                $res_data['app_member_index'] = true;
            }
        }
        if(!empty($app_search)){

            $app_searchs = DB::table('app_table')
                ->where('table','app_search')
                ->where('version',$app_search)
                ->where('is_del',0)
                ->first();
            if(!$app_searchs){
                $res_data['app_search'] = false;
            }else{
                $res_data['app_search'] = true;
            }
        }

        return api_json($res_data,200,'获取成功');

    }

    /**
     *算法表  版本对比
     **/
    public function algoVersion(Request $request)
    {
        $model = $request->input('model');

        $itemstandard = $request->input('itemstandard');
        $itemstandard_ver = $request->input('itemstandardVer');

        $sysdetectitem = $request->input('sysdetectitem');
        $sysdetectitem_ver = $request->input('sysdetectitemVer');

        $detect = $request->input('detect');
        $detect_ver = $request->input('detectVer');
        $res_data = [];
        if(!empty($itemstandard) && !empty($itemstandard_ver)){
            //app_algo_itemstandard
            $itemstandard = DB::table('app_table_algo')
                ->where('table','app_algo_itemstandard')
                ->where('model',$model)
                ->where('version',$itemstandard_ver)
                ->where('is_del',0)
                ->first();
            if(!$itemstandard){
                $res_data['itemstandard'] = false;
            }else{
                $res_data['itemstandard'] = true;
            }
        }

        if(!empty($sysdetectitem) && !empty($sysdetectitem_ver)) {
            //app_algo_sysdetectitem
            $sysdetectitem = DB::table('app_table_algo')
                ->where('table', 'app_algo_sysdetectitem')
                ->where('model', $model)
                ->where('version', $sysdetectitem_ver)
                ->where('is_del', 0)
                ->first();
            if(!$sysdetectitem){
                $res_data['sysdetectitem'] = false;
            }else{
                $res_data['sysdetectitem'] = true;
            }
        }

        if(!empty($detect) && !empty($detect_ver)) {
            //app_algo_detect
            $detect = DB::table('app_table_algo')
                ->where('table', 'app_algo_detect')
                ->where('model', $model)
                ->where('version', $detect_ver)
                ->where('is_del', 0)
                ->first();
            if(!$detect){
                $res_data['detect'] = false;
            }else{
                $res_data['detect'] = true;
            }
        }

        return api_json($res_data,200,'获取成功');

    }

    /**
     *更新表信息
     * 2019-1-25
     * by chenyu
     */
    public function updateTable(Request $request)
    {
        $table = $request->input('table');
        $model = $request->input('model');

        $mo = DB::table('app_model')
            ->where('model',$model)
            ->first();
        if(!$mo){
            return api_json([],90149,'该机型未在服务端备案');
        }


        switch ($table){
            case 'itemstandard':

                $itemstandards = DB::table('app_algo_itemstandard')
                    ->select('row','version','model','product_id','item_id','standard_r','standard_g','standard_b','determine','quantify','value','srow','scol','sthreshold')
                    ->where('model',$model)
                    ->where('is_del',0)
                    ->get()
                    ->toArray();

                $version = DB::table('app_table_algo')
                    ->select('version')
                    ->where('model',$model)
                    ->where('table','app_algo_itemstandard')
                    ->where('is_del',0)
                    ->first();
                return api_json(['table'=>$itemstandards,'version'=>$version->version,'tablename'=>'itemstandard'],200,'获取成功');
                break;
            case 'sysdetectitem':

                $sysdetectitem =DB::table('app_algo_sysdetectitem')
                    ->where('is_del',0)
                    ->get()
                    ->toArray();

                $version = DB::table('app_table_algo')
                    ->select('version')
                    ->where('table','app_algo_sysdetectitem')
                    ->where('is_del',0)
                    ->first();

                return api_json(['table'=>$sysdetectitem,'version'=>$version->version,'tablename'=>'sysdetectitem'],200,'获取成功');
                break;
            case 'detect':

                $detect =DB::table('app_algo_detect')
                    ->where('is_del',0)
                    ->get()
                    ->toArray();

                $version = DB::table('app_table_algo')
                    ->select('version')
                    ->where('table','app_algo_detect')
                    ->where('is_del',0)
                    ->first();

                return api_json(['table'=>$detect,'version'=>$version->version,'tablename'=>'detect'],200,'获取成功');
                break;
            case 'app_between':

                $app_between =DB::table('app_between')
                    ->get()
                    ->toArray();

                $version = DB::table('app_table')
                    ->select('version')
                    ->where('table','app_between')
                    ->where('is_del',0)
                    ->first();

                return api_json(['table'=>$app_between,'version'=>$version->version,'tablename'=>'app_between'],200,'获取成功');
                break;
            case 'app_member_index':

                $app_member_index =DB::table('app_member_index')
                    ->where('is_del',0)
                    ->orderBy('sort','desc')
                    ->get()
                    ->toArray();

                $version = DB::table('app_table')
                    ->select('version')
                    ->where('table','app_member_index')
                    ->where('is_del',0)
                    ->first();

                return api_json(['table'=>$app_member_index,'version'=>$version->version,'tablename'=>'app_member_index'],200,'获取成功');
                break;
            case 'app_search':

                $app_search =DB::table('app_search')
                    ->where('status',0)
                    ->orderBy('sort','desc')
                    ->get()
                    ->toArray();

                $version = DB::table('app_table')
                    ->select('version')
                    ->where('table','app_search')
                    ->where('is_del',0)
                    ->first();

                return api_json(['table'=>$app_search,'version'=>$version->version,'tablename'=>'app_search'],200,'获取成功');
                break;
            default:
                return api_json([],10001,'参数信息有误');
        }
    }

    /**
     * 首页广告
     *  2019-2-21
     * by chenyu
     **/
    public function getAdv()
    {
        //首页
        $data = DB::table('app_adv')
            ->select('id','text','sort','update_at')
            ->where('is_del',0)
            ->orderBy('sort','desc')
            ->orderBy('update_at','desc')
            ->limit(3)
            ->get();

        $speed =DB::table('app_config')
            ->select('text','type')
            ->where('is_del',0)
            ->get()
            ->toArray();
        foreach($speed as $k=>$v){
            if($v->type==1){//首页下方 图像展示
                $adv = $v;
            }
            if($v->type==2){//开放机型提醒
                $openModel = $v;
            }
        }

        if($data->isEmpty()){
            return api_json([],10002,'无数据');
        }else{
            return api_json(['adv'=>$data->toArray(),'openModel'=>$openModel->text,'speed'=>config('app.app_configs.loadhost').$adv->text],200,'获取成功');
        }
    }


}