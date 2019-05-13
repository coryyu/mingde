<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class WashData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'washData:oldtonew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '旧数据迁移新项目';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        ini_set('memory_limit','3072M');    // 临时设置最大内存占用为3G
        set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期

//        DB::beginTransaction();//开启事务
//        try {

//            echo  $this->app_home().PHP_EOL;//家庭成员
//            echo $this->app_user().PHP_EOL;//用户表
            echo $this->app_testsrecord().PHP_EOL;//检测记录
            //提交
//            DB::commit();
//        }catch(\Exception $exception) {
//            //事务回滚
//            DB::rollBack();
////            return api_json([],90602,'实体卡激活失败');
//            return back()->withErrors($exception->getMessage())->withInput();
//        }

    }
    
    public function app_home()
    {
        $app_home_list = DB::connection('yanxuxu_core')->table('app_home_list')
            ->get()->toArray();
        $app_home=[];
        foreach($app_home_list as $k=>$v){
            $app_home[$k]['id'] = $v->id;
            $app_home[$k]['name'] = $v->name;
            $app_home[$k]['sex'] = $v->sex;
            $app_home[$k]['age'] = $v->age == '0000-00-00'?'1963-01-13': $v->age;
            $app_home[$k]['user_id'] = $v->user_id;
            $app_home[$k]['is_use'] = $v->is_use;
            $app_home[$k]['wx_u'] = $v->wx_u;
            $app_home[$k]['is_del'] = $v->del==1?0:1;
            $app_home[$k]['create_at'] = $v->create_time;
            $app_home[$k]['update_at'] = today_time();
        }

        DB::table('app_home')
            ->insert($app_home);
    }

    public function app_user(){

        $user = DB::connection('yanxuxu_core')->table('user')
            ->get()->toArray();
        $app_user=[];
        foreach($user as $k=>$v){

            $app_user[$k]['id'] = $v->id;
            $app_user[$k]['token'] = $v->token;
            $app_user[$k]['name'] = $v->realname ==''?'游客'.substr( $v->telephone,-4):$v->realname;
            $app_user[$k]['phone'] = $v->telephone;
            $app_user[$k]['password'] = $v->password;
            $app_user[$k]['unionid'] = $v->unionid;
            $app_user[$k]['avatar_url'] = 'image/avater_bg.png';
            $homes =  DB::table('app_home')
                ->where('user_id',$v->id)
                ->where('is_del',0)
                ->count();
            $app_user[$k]['home_sum'] = $homes;
            $app_user[$k]['update_at'] = today_time();
            $app_user[$k]['create_at'] = $v->create_time=='0000-00-00 00:00:00'?'2017-01-01 16:56:39':$v->create_time;
            $app_user[$k]['last_at'] = $app_user[$k]['create_at'];
            $app_user[$k]['is_del'] = $v->is_del;
            $app_user[$k]['member_type'] = 0;
            $app_user[$k]['active'] = 0;
            $app_user[$k]['official_token'] = md5('pzzk'.md5($v->telephone));
            $app_user[$k]['app'] = $v->app ==0?1:$v->app;

        }
        return DB::table('app_user')
            ->insert($app_user);
    }

    public function app_testsrecord()
    {

        $values = DB::table('app_algo_itemstandard_values')
            ->get()
            ->toArray();
        $alge = [];
        foreach($values as $k=>$v){
            $alge[$v->item_id.$v->value] =$v;
        }
        $transfer = 0;
//        $limit = 2000;
//        $p = 0;
//        while(true){
            $testsrecord = DB::connection('yanxuxu_core')->table('TestsRecord')
                ->select('TestsRecord.*')
                ->join('User','User.id','=','TestsRecord.user_id')
                ->where('TestsRecord.come',0)
//                ->where('TestsRecord.id',8671)
//                ->where('TestsRecord.id',8667)
//                ->offset($p*$limit)
//                ->limit($limit)
                ->get();
//            $p++;
            if($testsrecord->isEmpty()){

            }else{
                $test = $testsrecord->toArray();
                $app_testsrecord= [];
                foreach($test as $k=>$v){
                    $result =  unserialize($v->result);
                    $result_status = 1;
                    $dete_sum = 0;
                    foreach($result as $q=>$z){
                        if(empty($z['id']) || empty($z['value'])){
                            if(empty($z['item_id']) || empty($z['real_value'])){
                                $result_status =2;
                                break;
                            }else{
                                $zz = $z['item_id'].$z['real_value'];
                            }
                        }else{
                            $zz = $z['id'].$z['value'];
                        }
                        if(!empty($alge[$zz])){//存在标准值
                            $testResult[$q]['test_id'] = $v->id;
                            $testResult[$q]['sid'] = $alge[$zz]->id;
                            $testResult[$q]['rgb'] = empty($z['RGB'])?'':(is_array($z['RGB'])?json_encode($z['RGB']):$z['RGB']);
                            $testResult[$q]['diff'] =empty($z['difValue'])?'':$z['difValue'];
                            $testResult[$q]['tag'] =  $alge[$zz]->tag;
                            $testResult[$q]['algo_version'] = 0;
                            if($alge[$zz]->determine !== '正常'){
                                $dete_sum++;
                            }
                        }else{//不存在
                            $result_status =2;
                            break;
                        }
                    }
                    if($result_status == 2){//检测结果对应不上  不做迁移
                        DB::connection('yanxuxu_core')->table('TestsRecord')
                            ->where('id',$v->id)
                            ->update(['transfer'=>1]);
                        $transfer++;
                    }else{
                        echo '处理'.$v->id.PHP_EOL;
                        DB::beginTransaction();//开启事务
                        try {
                            DB::table('app_testsrecord_result')
                                ->insert($testResult);
                            DB::commit();
                        }catch(\Exception $exception) {
                            //事务回滚
                            DB::rollBack();
                            return back()->withErrors($exception->getMessage())->withInput();
                        }
                        $app_testsrecord[$k]['id'] = $v->id;
                        $app_testsrecord[$k]['home_id'] = $v->home_id;
                        $app_testsrecord[$k]['area'] = '';
                        $app_testsrecord[$k]['picture_path'] = $v->original_picture;
                        $app_testsrecord[$k]['picture_record'] = $v->original_spot_picture;
                        $app_testsrecord[$k]['app_version'] = '';
                        $app_testsrecord[$k]['algo_version'] = '';
                        $app_testsrecord[$k]['item_version'] = '';
                        $app_testsrecord[$k]['positionData'] = '';
                        $app_testsrecord[$k]['code'] = strtoupper($v->model).$v->code;
                        $app_testsrecord[$k]['model'] = $v->model=='s'?'婴儿S型':($v->model=='m'?'婴儿M型':($v->model=='l'?'婴儿L型':'婴儿XL型'));
                        $app_testsrecord[$k]['come'] = $v->come;
                        $app_testsrecord[$k]['app'] = $v->app==0?1:$v->app;
                        $app_testsrecord[$k]['state'] = $v->state;
                        $app_testsrecord[$k]['is_import'] = $v->is_import;
                        $app_testsrecord[$k]['create_at_app'] = $v->create_time;
                        $app_testsrecord[$k]['create_at'] = $v->create_time;
                        $app_testsrecord[$k]['update_at'] = today_time();
                        $app_testsrecord[$k]['is_del'] = $v->is_del;
                        $app_testsrecord[$k]['lailai'] = 2;
                        $app_testsrecord[$k]['type_between'] = 1;
                        $app_testsrecord[$k]['no_term'] = $dete_sum;
                        $app_testsrecord[$k]['phone_model'] = '';
                        $app_testsrecord[$k]['title'] = '宝宝的检测记录';
                        $app_testsrecord[$k]['phone_model_local'] = '';
                        DB::beginTransaction();//开启事务
                        try {
                            $id = DB::table('app_testsrecord')
                                ->insertGetId($app_testsrecord[$k]);
                            DB::commit();
                            echo 'testsrecord表插入id：'.$id.PHP_EOL;
                        }catch(\Exception $exception) {
                            //事务回滚
                            DB::rollBack();
                            return back()->withErrors($exception->getMessage())->withInput();
                        }
                    }
                }
//                foreach($app_testsrecord as $k=>$v){
//                    DB::beginTransaction();//开启事务
//                    try {
//                        $id = DB::table('app_testsrecord')
//                            ->insertGetId($v);
//                        DB::commit();
//                        echo 'testsrecord表插入id：'.$id.PHP_EOL;
//                    }catch(\Exception $exception) {
//                        //事务回滚
//                        DB::rollBack();
//                        return back()->withErrors($exception->getMessage())->withInput();
//                    }
//
//                }
                echo '检测记录数据导入'.count($app_testsrecord).PHP_EOL;

            }
//        }
        return '未迁移'.$transfer.'条，检测记录end'.PHP_EOL;
    }


    public function testsrecord()
    {
        $sql = 'SELECT DISTINCT u.telephone,u.id FROM `testsrecord` AS t LEFT JOIN `user` AS u ON t.user_id=u.id WHERE transfer = 1';




    }

}
