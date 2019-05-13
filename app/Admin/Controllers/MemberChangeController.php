<?php

namespace App\Admin\Controllers;

use App\MemberChange;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\MemberCard;
use Illuminate\Support\Facades\DB;

class MemberChangeController extends Controller
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
            ->header('实体卡')
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
            ->header('实体卡')
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
        $grid = new Grid(new MemberChange);

        $grid->id('Id');
        $grid->cardid('卡信息')->display(function($cardid) {
            return MemberCard::find($cardid)->name;
        });
        $grid->channel('创建人')->display(function($cardid) {
            return $cardid == '990'?'管理员':'其他成员';
        });
        $grid->create_at('生成时间');
        $grid->status('Status')->display(function($status) {
            return $status == 0?'未使用':'已使用';
        });
        $grid->uid('绑定用户');
        $grid->code('Code');

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();

        });

        $grid->model()->orderBy('id', 'desc');
        $grid->model()->where('is_del','=', 0);
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
        $show = new Show(MemberChange::findOrFail($id));

        $show->id('Id');
        $show->cardid('Cardid');
        $show->channel('Channel');
        $show->create_at('Create at');
        $show->update_at('Update at');
        $show->is_del('Is del');
        $show->status('Status');
        $show->uid('Uid');
        $show->code('Code');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MemberChange);

        $card =  MemberCard::all('id','name')->where('status',0)->toArray();
        foreach($card as $k =>$v){
            $op[$v['id']] = $v['name'];
        }

        $form->select('cardid','卡类型')->options($op);
        $form->select('channel', 'Channel')->options(['990'=>'管理员']);
        $form->select('sum', '数量')->options(['1'=>1,'2'=>2,'5'=>5,'8'=>8,'10'=>10]);

        //保存前回调
        $form->saving(function (Form $form) {
            //...
            $cardid = $form->cardid;
            $channel = $form->channel;
            $sum = $form->sum;
            for($i=0;$i<$sum;$i++){
                $data[$i]['cardid']= $cardid;
                $data[$i]['channel']= $channel;
                $data[$i]['create_at']= today_time();
                $data[$i]['update_at']= today_time();
                $data[$i]['is_del']= 0;
                $data[$i]['status']= 0;
                $data[$i]['code']= date('sihdm',time()).$i.rand(10000,99999);
            }

            DB::beginTransaction();//开启事务
            try {

                DB::table('app_member_change')->insert($data);
                DB::commit();
            }
            catch(\Exception $exception)
            {
                //事务回滚
                DB::rollBack();
                return back()->withErrors($exception->getMessage())->withInput();
            }
            return redirect('/admin/memberchange');
        });

        return $form;
    }
}
