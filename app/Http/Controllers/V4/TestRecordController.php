<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Controllers\V4\CommonController as Common;

class TestRecordController extends Common
{

    /**
     *上传检测记录
     * 2019-1-21
     * by chenyu
     */
    public function insertTestRecord(Request $request)
    {
        $home_id = $request->input('home_id');//家庭成员id
        $area = $request->input('area');//扫码地点 坐标
        $picture_path = $request->input('picture_path');//图片地址
        $algo_version = $request->input('algo_version');//版本库
        $item_version = $request->input('item_version');//标准值版本
        $positionData = $request->input('positionData');//识别点坐标
        $code = $request->input('code');//纸尿片 识别码
        $model = $request->input('model');//纸尿片 型号
        $app = $request->input('app');//客户端来源
        $lailai = $request->input('lailai');//懒懒扣 链接状态
        $type_between = $request->input('type_between');//尿 区间
        $create_at_app = $request->input('create_at_app');//app端 扫码时间
        $no_term = $request->input('no_term');//异常项个数
        $phone_model = $request->input('phone_model');//手机型号
        $app_version = $request->input('app_version');//app版本
        $title = $request->input('title');//备注
        $phone_model_local = $request->input('phone_model_local');//备注

        //二维码信息
        $model_type = ['M','L','S','X'];
        if(in_array($model,$model_type) && strlen($code) != 14){
            return api_json([],90134,'二维码信息不正确');
        }

        //手机型号
        $app_model_table = DB::table('app_model')
            ->where('model',$phone_model)
            ->where('status',0)
            ->get();
        if($app_model_table->isEmpty()){
            return api_json([],90138,'该机型未开放');
        }

        //算法版本是否存在
        $version_db = DB::table('app_algo_itemstandard')
            ->where('version',$item_version)
            ->get();

        if ($version_db->isEmpty()) {
            return api_json([],90135,'标准值版本信息有误');
        }
        if(empty($app)){
            return api_json([],90131,'app参数错误');
        }
        if(empty($lailai)){
            return api_json([],90131,'lailai参数错误');
        }
        if(empty($type_between)){
            return api_json([],90131,'type_between参数错误');
        }
        if(empty($create_at_app)){
            return api_json([],90131,'create_at_app参数错误');
        }

        //家庭成员 是否在该用户下
        $home_id_db = DB::table('app_home')
            ->where('id',$home_id)
            ->where('user_id',$this->userinfo->id)
            ->where('is_del',0)
            ->first();
        if(!$home_id_db){
            return api_json([],90136,'该家庭成员不存在');
        }
        DB::beginTransaction();//开启事务
        try {
            //组装数据
            $table_data['home_id'] = $home_id;
            $table_data['area'] = empty($area)?'':$area;
            $table_data['picture_path'] = $picture_path;
            $table_data['algo_version'] = $algo_version;
            $table_data['app_version'] = $app_version;
            $table_data['item_version'] = $item_version;
            $table_data['positionData'] = $positionData;
            $table_data['code'] = $code;
            $table_data['model'] = $model;
            $table_data['come'] = 0;
            $table_data['app'] = $app;
            $table_data['state'] = $no_term>0?1:0;
            $table_data['is_import'] = 0;
            $table_data['create_at_app'] = $create_at_app;
            $table_data['create_at'] = today_time();
            $table_data['update_at'] = $table_data['create_at'];
            $table_data['is_del'] = 0;
            $table_data['lailai'] = $lailai;
            $table_data['type_between'] = $type_between;
            $table_data['no_term'] = $no_term;//异常项 个数
            $table_data['phone_model'] = $phone_model;
            $table_data['title'] = $title;
            $table_data['phone_model_local'] = $phone_model_local;//详细手机型号

            $test_id = DB::table('app_testsrecord')
                ->insertGetId($table_data);

            if($this->is_json($request->input('LEU'))){
                $res =  json_decode($request->input('LEU'),true);

                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'LEU','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('NIT'))){
                $res =  json_decode($request->input('NIT'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'NIT','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('URO'))){
                $res =  json_decode($request->input('URO'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'URO','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('PRO'))){
                $res =  json_decode($request->input('PRO'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'PRO','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('PH'))){
                $res =  json_decode($request->input('PH'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'PH','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('BLO'))){
                $res =  json_decode($request->input('BLO'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'BLO','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('SG'))){
                $res =  json_decode($request->input('SG'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'SG','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('KET'))){
                $res =  json_decode($request->input('KET'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'KET','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('BIL'))){
                $res =  json_decode($request->input('BIL'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'BIL','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('GLU'))){
                $res =  json_decode($request->input('GLU'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'GLU','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('VC'))){
                $res =  json_decode($request->input('VC'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'VC','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('CA'))){
                $res =  json_decode($request->input('CA'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'CA','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('CRE'))){
                $res =  json_decode($request->input('CRE'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'CRE','algo_version'=>$item_version];
            }
            if($this->is_json($request->input('MCA'))){
                $res =  json_decode($request->input('MCA'),true);
                $app_result[]= ['test_id'=>$test_id,'sid'=>$res['sid'],'rgb'=>$res['rgb'],'diff'=>$res['diff'],'tag'=>'MCA','algo_version'=>$item_version];
            }
            DB::table('app_testsrecord_result')
                ->insert($app_result);

            DB::commit();
            return api_json(['test_id'=>$test_id],200,'成功');

        }catch(\Exception $exception) {

            //事务回滚
            DB::rollBack();
            return api_json([],90137,'上传失败');
//            return back()->withErrors($exception->getMessage())->withInput();

        }


    }


    /**
     *读取检测记录
     * 2019-1-22
     * by chenyu
     */
    public function getTestRecord(Request $request)
    {
        $home_id = (int) $request->input("home_id");//成员id
        $create_at_app = $request->input("create_at_app");//创建时间
        $search = $request->input("search");//搜索
        $item = $request->input("item");//搜索


        //判断家庭成员是否存在
        $homes =$this->isHome($home_id);

        if(!$homes){
            return api_json([],90126,'家庭成员不存在');
        }

        $testrecord = DB::table('app_testsrecord')
            ->select('app_testsrecord.id','app_testsrecord.home_id','app_testsrecord.algo_version','app_testsrecord.code','app_testsrecord.model','app_testsrecord.state','app_testsrecord.create_at_app','app_testsrecord.type_between','app_testsrecord.no_term','app_testsrecord.title')
            ->where('app_testsrecord.home_id',$home_id)
            ->where('app_testsrecord.is_del',0)
            ->where('create_at_app','<',$create_at_app);



        if(!empty($search)){
            $lists = DB::table('app_testsrecord')
                ->select('create_at_app')
                ->where('app_testsrecord.title','like',"%$search%")
                ->where('app_testsrecord.home_id',$home_id)
                ->where('app_testsrecord.is_del',0)
                ->where('create_at_app','<',$create_at_app)
                ->orderBy('create_at_app','desc')
                ->limit(15)
                ->get();
            $arr = $lists->toArray();
            $arr_end = end($arr);

            $testrecord->where('create_at_app','>=',$arr_end->create_at_app);

        }elseif(!empty($item)){
            //检索项
            $record = DB::table('app_testsrecord')
                ->select('create_at_app')
                ->where('app_testsrecord.title','like',"%$search%")
                ->where('app_testsrecord.home_id',$home_id)
                ->where('app_testsrecord.is_del',0)
                ->where('create_at_app','<',$create_at_app);

            $search_sql = DB::table('app_search')
                ->select('text')
                ->where('id',$item)
                ->first();

            eval($search_sql->text);
            $record
                ->orderBy('create_at_app','desc')
                ->limit(15)
                ->get();
            $arr = $record->toArray();
            $arr_end = end($arr);
            $testrecord->where('create_at_app','>=',$arr_end->create_at_app);

        }else{
            $testrecord
                ->limit(15);
        }

        $res_list = $testrecord
            ->orderBy('create_at_app','desc')
            ->get();


        if (!$res_list->isEmpty()) {
            $testlist = $res_list->toArray();
            foreach($testlist as $k=>$v){

                $tags = DB::table('app_testsrecord_result')
                    ->select('sid','tag')
                    ->where('test_id',$v->id)
                    ->get();
                foreach($tags->toArray() as $i=>$j){
                    $tagsArr[$j->tag] =$j->sid;
                }
                $v->info=$tagsArr;
                $res_list[$k]= $v;
            }
            return api_json(['info'=>$res_list],200,'获取成功');
        }else{
            return api_json([],10002,'无数据');
        }




        if(!empty($search)){
            $record->where('app_testsrecord.title','like',"%$search%");
        }elseif(!empty($item)){
            //检索项
            $search_sql = DB::table('app_search')
                ->select('text')
                ->where('id',$item)
                ->first();
            eval($search_sql->text);
        }
        $lists = $record
            ->where('create_at_app','<',$create_at_app)
            ->orderBy('create_at_app','desc')
            ->get();
//        var_dump($lists);exit;
        if (!$lists->isEmpty()) {
            $testlist = $lists->toArray();
            foreach($testlist as $k=>$v){

                $tags = DB::table('app_testsrecord_result')
                    ->select('sid','tag')
                    ->where('test_id',$v->id)
                    ->get();
                foreach($tags->toArray() as $i=>$j){
                    $tagsArr[$j->tag] =$j->sid;
                }
                $v->info=$tagsArr;
                $testlist[$k]= $v;
            }
            return api_json(['info'=>$testlist],200,'获取成功');
        }else{
            return api_json([],10002,'无数据');
        }

    }
    /**
     *读取检测记录
     * 2019-1-22
     * by chenyu
     */
    public function getTestRecord_demo(Request $request)
    {
        $home_id = (int) $request->input("home_id");//成员id
        $page = (int) $request->input("page");//页码
        $search = $request->input("search");//搜索
        $item = $request->input("item");//搜索
        $limit = (int) $request->input("limit");
        $limit = empty($limit)?15:$limit;

        //判断家庭成员是否存在
        $this->isHome($home_id);

        $record = DB::table('app_testsrecord')
            ->select('app_testsrecord.id','app_testsrecord.home_id','app_testsrecord.algo_version','app_testsrecord.code','app_testsrecord.model','app_testsrecord.state','app_testsrecord.create_at_app','app_testsrecord.type_between','app_testsrecord.no_term','app_testsrecord.title')
            ->where('app_testsrecord.home_id',$home_id)
            ->where('app_testsrecord.is_del',0);
        if(!empty($search)){
            $record->where('app_testsrecord.title','like',"%$search%");
        }elseif(!empty($item)){
            //检索项
            $search_sql = DB::table('app_search')
                ->select('text')
                ->where('id',$item)
                ->first();
            eval($search_sql->text);
        }
        $lists = $record
            ->orderBy('create_at_app','desc')
            ->offset(($page-1)*$limit)
            ->limit($limit)
            ->get();
//        var_dump($lists);exit;
        if (!$lists->isEmpty()) {
            $testlist = $lists->toArray();
            foreach($testlist as $k=>$v){

                $tags = DB::table('app_testsrecord_result')
                    ->select('sid','tag')
                    ->where('test_id',$v->id)
                    ->get();
                foreach($tags->toArray() as $i=>$j){
                    $tagsArr[$j->tag] =$j->sid;
                }
                $v->info=$tagsArr;
                $testlist[$k]= $v;
            }
            return api_json(['info'=>$testlist],200,'获取成功');
        }else{
            return api_json([],10002,'无数据');
        }

    }
    /**
     *快速搜索项
     * 2019-1-22
     * by chenyu
     **/
    public function searchItem(Request $request)
    {
        $search = $request->input('search');
        switch ($search)
        {
            case 'record':
                $search = DB::table('app_search')
                    ->select('id','title','sort')
                    ->where('type','test')
                    ->where('status',0)
                    ->orderBy('sort')
                    ->get();
                if (!$search->isEmpty()) {
                    return api_json([],200,'最新版本');
                }else{
                    return api_json([],10002,'无数据');
                }

                break;
            default:
                return api_json([],10001,'参数信息有误');
        }
    }

    /**
     *更新记录备注
     * 2019-2-13
     * by chenyu
     **/
    public function upRecordTitle(Request $request)
    {
        $testid=  $request->input('testid');
        $title=  $request->input('title');

        $test = DB::table('app_testsrecord')
            ->where('id',$testid)
            ->update(['title'=>$title,'update_at'=>today_time()]);
        if($test>0){
            return api_json([],200,'更新成功');
        }else{
            return api_json([],90158,'备注更新失败');
        }
    }

    /**
     *检测片唯一性
     * 2019-2-19
     * by chenyu
     */
    public function selectCode(Request $request)
    {

        $code = $request->input('code');

        if(in_array($code,$this->codeList())){
            return api_json(['info'=>'0'],200,'未使用过');
        }

        $tests = DB::table('app_testsrecord')
            ->where('code',$code)
            ->first();
        if($tests){
            return api_json(['info'=>'1'],90400,'检测片已经使用过了');
        }else{
            return api_json(['info'=>'0'],200,'未使用过');
        }

    }
    /**
     *检测结果 分析
     * 2019-2-27
     * chenyu
     **/
    public function recordDetail(Request $request)
    {
        $tid = $request->input('tid');

        $record = DB::table('app_testsrecord')
            ->select('phone_model','item_version','home_id')
            ->where('id',$tid)
            ->where('is_del',0)
            ->first();

        $home = DB::table('app_home')
            ->where('id',$record->home_id)
            ->first();

        $result = DB::table('app_testsrecord_result')
            ->select('app_testsrecord_result.id','app_testsrecord_result.tag','app_algo_itemstandard.row','app_algo_sysdetectitem.item','app_testrecord_mp3.path','app_testrecord_mp3.text')
            ->leftJoin('app_algo_itemstandard','app_algo_itemstandard.row','=','app_testsrecord_result.sid')
            ->leftJoin('app_algo_sysdetectitem','app_algo_sysdetectitem.row','=','app_algo_itemstandard.item_id')
            ->leftJoin('app_testrecord_mp3','app_testrecord_mp3.item','=','app_testsrecord_result.tag')
            ->where('app_testsrecord_result.test_id',$tid)
            ->where('app_algo_itemstandard.version',$record->item_version)
            ->where('app_algo_itemstandard.model',$record->phone_model)
            ->where('app_algo_itemstandard.determine','!=','正常')
            ->get()
            ->toArray();


        $title = '“'.$home->name.'”家长您好，我是赖赖医生！';
        $text = '根据检测结果，您宝宝的';
        foreach ($result as $k=>$v) {
            $text .= '“'.$v->item.'”、';
            $item[] = $v->item;
            $result[$k]->path = config('app.app_configs.loadhost').$v->path;

        }
        $text = rtrim($text, '、');
        $text .='值出现异常。不用着急，赖赖医生告诉你什么原因及如何调整。';

        $item_str = implode(',',$item);

        return api_json(['info'=>$result,'title'=>$title,'text'=>$text,'item'=>$item_str],200,'成功');


    }


    private function codeList()
    {
        $data =[
            'S011111111111111',
            'M011111111111111',
            'L011111111111111',
            'X011111111111111',
            'SM11170526005132',
            'SM11170526003447',
            'SM11170219048409',
            'SN11160923020581',
            'SM11170416020763',
            'SM11170526002889',
            'SM11160318000001',
            'SM11170615000827',
            'MB12180222888888',
            'MB12180224888888',
            'MB12180220888888',
            'SB12180226888888',
            'SB12180228000578',
            'SB12180228000206',
            'SG12180320027342',
            'SG12180617035596',
            'MG12190116031442'
        ];
        return $data;
    }


    private function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}