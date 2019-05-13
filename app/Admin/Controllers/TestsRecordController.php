<?php

namespace App\Admin\Controllers;

use App\TestsRecord;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Home;
use Illuminate\Support\Facades\DB;

class TestsRecordController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('检测记录')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {

        $result = DB::table('app_testsrecord')
            ->select('app_user.avatar_url','app_testsrecord.create_at_app','app_testsrecord.id','app_home.name','app_home.age','app_user.phone','app_home.sex','app_testsrecord.state','app_testsrecord.picture_record','app_testsrecord.come','app_testsrecord.phone_model','app_testsrecord.area','app_testsrecord.algo_version','app_testsrecord.positionData','app_testsrecord.model','app_testsrecord.code','app_testsrecord.app','app_testsrecord.picture_path')
            ->leftJoin('app_home','app_home.id','=','app_testsrecord.home_id')
            ->leftJoin('app_user','app_user.id','=','app_home.user_id')
            ->where('app_testsrecord.id',$id)
            ->first();

//        print_r($result);exit;

        $result->avatar_url =config('app.app_configs.loadhost').$result->avatar_url;
        $result->picture_path =config('app.app_configs.ossUrl').$result->picture_path;
        $result->picture_record =config('app.app_configs.ossUrl').$result->picture_record;
        $result->sex = $result->sex ==1?'男':'女';
        $result->state = $result->state ==1?'正常':($result->state ==2?'轻症':'重症');

        if($result->come == 1){//todo


        }

        $result_s = DB::table('app_testsrecord_result')
            ->select('app_algo_sysdetectitem.item','app_algo_itemstandard.determine','app_algo_itemstandard.value','app_algo_itemstandard.quantify','app_testsrecord_result.diff','app_algo_sysdetectitem.standard_range','app_testsrecord_result.rgb','app_algo_sysdetectitem.unit','app_algo_sysdetectitem.row')
            ->leftJoin('app_testsrecord','app_testsrecord.id','=','app_testsrecord_result.test_id')
            ->leftJoin('app_algo_itemstandard','app_algo_itemstandard.row','=','app_testsrecord_result.sid')
            ->leftJoin('app_algo_sysdetectitem','app_algo_sysdetectitem.row','=','app_algo_itemstandard.item_id')

            ->where('app_testsrecord_result.test_id',$result->id)
            ->where('app_algo_itemstandard.model',$result->phone_model)
            ->where('app_algo_itemstandard.is_del',0)
            ->get();

        foreach($result_s as $k=>$v){
            $array = explode(",",$v->rgb);
            $result_s[$k]->rgb1 = $array[0];
            $result_s[$k]->rgb2 = $array[1];
            $result_s[$k]->rgb3 = $array[2];
        }


        return $content
            ->header('检测详情')
            ->body(view('testsrecord', ['data'=>$result,'result'=>$result_s])->render());

    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TestsRecord);

        $grid->id('Id')->sortable();
        $grid->home_id('宝宝名')->display(function($homeId) {
            return Home::find($homeId)->name;
        });
        $grid->area('坐标');
        $grid->result('检测结果');
        $grid->picture_path('Picture path');
        $grid->algo_version('Algo version');
        $grid->positionData('PositionData');
        $grid->code('Code');
        $grid->model('Model');
        $grid->come('检测来源')->display(function($come) {
            return $come==0?'家庭版':'医护版';
        });
        $grid->app('客户端')->display(function($app) {
            return $app==1?'安卓':($app==2?'IOS':'小程序');
        });
        $grid->state('结果状态')->display(function($state) {
            return $state==1?'正常':($state==2?'轻':'重');
        });;
        $grid->create_at('Create at');


        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->model()->orderBy('create_at', 'desc');
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {



        $show = new Show(TestsRecord::findOrFail($id));

        $ss = TestsRecord::findOrFail($id);
//        $arr = $ss->toArray();
//        echo '<pre>';
//        print_r($ss->toArray());exit;
//
//        $show->column('username')->display(function(){
//            return 'test';
//        });

        $show->id('Idsss');
        $show->home_id('Home id');
        $show->area('Area');
        $show->result('Result');
        $show->disease_item_id('Disease item id');
        $show->picture_path('Picture path');
        $show->picture_record('Picture record');
        $show->algo_version('Algo version');
        $show->positionData('PositionData');
        $show->code('Code');
        $show->model('Model');
        $show->come('Come');
        $show->app('App');
        $show->state('State');
        $show->is_import('Is import');
        $show->create_at('Create at');
        $show->update_at('Update at');
        $show->is_del('Is del');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TestsRecord);

        $form->number('home_id', 'Home id');
        $form->text('area', 'Area');
        $form->text('result', 'Result');
        $form->number('disease_item_id', 'Disease item id');
        $form->textarea('picture_path', 'Picture path');
        $form->textarea('picture_record', 'Picture record');
        $form->text('algo_version', 'Algo version');
        $form->text('positionData', 'PositionData');
        $form->text('code', 'Code');
        $form->text('model', 'Model');
        $form->switch('come', 'Come');
        $form->switch('app', 'App');
        $form->switch('state', 'State');
        $form->switch('is_import', 'Is import');
        $form->datetime('create_at', 'Create at')->default(date('Y-m-d H:i:s'));
        $form->datetime('update_at', 'Update at')->default(date('Y-m-d H:i:s'));
        $form->switch('is_del', 'Is del');

        return $form;
    }
    public function imgForm()
    {
        echo 'tetst';
    }
}
