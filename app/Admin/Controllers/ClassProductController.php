<?php

namespace App\Admin\Controllers;

use App\ClassProduct;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\ClassChannel;
use App\ClassClothing;
use Illuminate\Support\Facades\DB;
use App\ClassGrade;

class ClassProductController extends Controller
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
            ->header('产品列表')
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
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
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
            ->header('修改产品')
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
            ->header('创建产品')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ClassProduct);

        $grid->number('商品编号');
        $grid->price('价格');
        $grid->support('是否展示')->display(function($support) {
            return $support==0?'总部':'自营';
        });
        $grid->title('标题');
        $grid->channel('渠道')->display(function($channelId) {
            $s = ClassChannel::where('id',$channelId)->first();
            return $s->name;
        });
        $grid->city('城市');
        $grid->day('行程天数');
        $grid->created_at('创建时间');
        $grid->start_time_to('预计开始时间');
        $grid->is_show('是否展示')->display(function($is_show) {
            return $is_show==0?'展示':'隐藏';
        });
        $grid->status('行程状态')->display(function($status) {
            return $status==0?'进行中':'已结束';
        });
        $grid->is_onoff('是否可报名')->display(function($is_onoff) {
            return $is_onoff==0?'是':'否';
        });
        $grid->model()->orderBy('id', 'desc');

        $grid->disableRowSelector();
//        $grid->disableActions();

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
        $show = new Show(ClassProduct::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->title_fit('Title fit');
        $show->price('Price');
        $show->sale('Sale');
        $show->sort('Sort');
        $show->is_recommend('Is recommend');
        $show->is_show('Is show');
        $show->is_onoff('Is onoff');
        $show->image1('Image1');
        $show->image2('Image2');
        $show->image3('Image3');
        $show->text_item('Text item');
        $show->text_introduce('Text introduce');
        $show->text_arrange('Text arrange');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
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
        $form = new Form(new ClassProduct);

        $form->text('title', '标题');
        $form->text('title_fit', '副标题');
        $channels = ClassChannel::where('is_del', 0)->get()->toArray();
        foreach ($channels as $k=>$v) {
            $selectchannel[$v['id']] = $v['name'];
        }
        $form->select('channel','渠道')->options($selectchannel);
        $clothing= ClassClothing::where('is_del', 0)->get()->toArray();
        foreach($clothing as $k=>$v){
            $selectclot[$v['id']] = $v['name'];
        }
        $form->select('clothing','服装')->options($selectclot);
        $selectsupport[0]= '总部';
        $selectsupport[1]= '自营';
        $form->select('support','总部/自营')->options($selectsupport);
        $grade = ClassGrade::get()->toArray();
        foreach($grade as $k=>$v){
            $selectgradeup[$v['id']] = $v['name'];
            $selectgradedo[$v['id']] = $v['name'];
        }
        $form->select('gradeup','年级区间上')->options($selectgradeup);
        $form->select('gradedo','年级区间下')->options($selectgradedo);
        $form->text('city', '城市');
        $form->text('fit', '适应人群');
        $form->text('day', '行程天数');
        $form->date('start_time_to', '预计开始时间');
        $form->date('start_time', '开始时间');
        $form->date('end_time', '结束时间');
        $form->currency('price', '产品价格')->symbol('￥');

        $sales = DB::table('admin_role_users')
            ->select('admin_users.id','admin_users.username')
            ->leftJoin('admin_roles','admin_roles.id','=','admin_role_users.role_id')
            ->leftJoin('admin_users','admin_users.id','=','admin_role_users.user_id')
            ->where('admin_roles.id',2)
            ->get();
        foreach($sales->toArray() as $k=>$v){
            $selectsale[$v->id] = $v->username;
        }
        $form->select('sale','销售')->options($selectsale);
        $form->number('sort', '排序');
        $form->switch('is_recommend', '是否推荐');
        $form->switch('is_show', '是否显示');
        $form->switch('is_onoff', '是否可报名');
        $form->switch('is_pay', '是否需要支付');
        $form->switch('is_sign', '是否需要报名信息');
        $form->text('school', '学校')->help('多个学校用英文逗号分隔（,）');
        $form->image('image1')->move('public/upload/classimage')->uniqueName();
        $form->image('image2')->move('public/upload/classimage')->uniqueName();
        $form->image('image3')->move('public/upload/classimage')->uniqueName();
        $form->editor('text_item','特色');
        $form->editor('text_introduce','课程介绍');
        $form->editor('text_arrange','课程安排');
        $form->editor('text_service','课程服务');
        $form->tools(function (Form\Tools $tools) {
            // 去掉`列表`按钮
            $tools->disableList();
        });
        $form->footer(function ($footer) {
            // 去掉`重置`按钮
            $footer->disableReset();
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });

        $form->saved(function (Form $form) {
            ClassProduct::where('id',$form->model()->id)->update(['number'=>time()]);
        });

        return $form;
    }
}
