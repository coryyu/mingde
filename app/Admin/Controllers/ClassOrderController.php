<?php

namespace App\Admin\Controllers;

use App\ClassOrder;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;


use App\ClassChannel;

class ClassOrderController extends Controller
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
            ->header('订单列表')
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
        $grid = new Grid(new ClassOrder);

        $grid->orders('订单编号');
        $grid->title('产品标题');
        $grid->xueshengname('学生姓名');
        $grid->school('所在学校');
        $grid->grade('年级');
        $grid->class('班级');
        $grid->pay('价格');
        $grid->pay_status('支付状态')->display(function($pay_status) {
            return $pay_status==0?'未支付':($pay_status==1?'已支付':($pay_status==1?'申请退款':'退款成功'));
        });
        $grid->channel('走账公司')->display(function($channel) {
            $channel = ClassChannel::where('id',$channel)->first();
            return $channel->username;
        });;
        $grid->sale_id('销售')->display(function($saleid) {
            $users = DB::table('admin_users')->where('id',$saleid)->first();
            return $users->username;
        });;
        $grid->created_at('创建时间');
        $grid->pay_time('支付时间');
        $grid->tui_time('申请退款时间');
        $grid->tuido_time('后台退款时间');
        $grid->tuitext('退款理由');

        $grid->disableRowSelector();
        $grid->model()->orderBy('created_at', 'desc');

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });

        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('orders', '订单编号');
            $filter->like('title', '产品标题');
            $filter->like('xueshengname', '学生姓名');
            $filter->like('xueshengname', '学生姓名');
            $filter->scope('pay_status', '男性')->where('gender', 'm');

        });

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
        $show = new Show(ClassOrder::findOrFail($id));

        $show->id('Id');
        $show->userid('Userid');
        $show->orders('Orders');
        $show->title('Title');
        $show->xueshengname('Xueshengname');
        $show->school('School');
        $show->grade('Grade');
        $show->class('Class');
        $show->pay('Pay');
        $show->pay_status('Pay status');
        $show->pay_time('Pay time');
        $show->channel('Channel');
        $show->sale('Sale');
        $show->sale_id('Sale id');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
        $show->tui_time('Tui time');
        $show->tuido_time('Tuido time');
        $show->tuitext('Tuitext');
        $show->card1('Card1');
        $show->card2('Card2');
        $show->proid('Proid');
        $show->guarder('Guarder');
        $show->trip('Trip');
        $show->is_del('Is del');
        $show->invoice('Invoice');
        $show->email('Email');
        $show->transaction_id('Transaction id');
        $show->total_fee('Total fee');
        $show->cash_fee('Cash fee');
        $show->res_json('Res json');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ClassOrder);

        $form->number('userid', 'Userid');
        $form->text('orders', 'Orders');
        $form->text('title', 'Title');
        $form->text('xueshengname', 'Xueshengname');
        $form->text('school', 'School');
        $form->text('grade', 'Grade');
        $form->text('class', 'Class');
        $form->text('pay', 'Pay');
        $form->switch('pay_status', 'Pay status');
        $form->datetime('pay_time', 'Pay time')->default(date('Y-m-d H:i:s'));
        $form->text('channel', 'Channel');
        $form->text('sale', 'Sale');
        $form->text('sale_id', 'Sale id');
        $form->datetime('tui_time', 'Tui time')->default(date('Y-m-d H:i:s'));
        $form->datetime('tuido_time', 'Tuido time')->default(date('Y-m-d H:i:s'));
        $form->text('tuitext', 'Tuitext');
        $form->textarea('card1', 'Card1');
        $form->textarea('card2', 'Card2');
        $form->number('proid', 'Proid');
        $form->number('guarder', 'Guarder');
        $form->number('trip', 'Trip');
        $form->switch('is_del', 'Is del');
        $form->switch('invoice', 'Invoice');
        $form->email('email', 'Email');
        $form->text('transaction_id', 'Transaction id');
        $form->text('total_fee', 'Total fee');
        $form->text('cash_fee', 'Cash fee');
        $form->textarea('res_json', 'Res json');

        return $form;
    }
}
