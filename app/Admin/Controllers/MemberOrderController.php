<?php

namespace App\Admin\Controllers;

use App\MemberOrder;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MemberOrderController extends Controller
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
            ->header('Index')
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
        $grid = new Grid(new MemberOrder);

        $grid->id('Id');
        $grid->name('姓名');
        $grid->add('地址');
        $grid->pay('支付金额');
        $grid->mem_type('会员类别')->display(function($mem_type) {
            return $mem_type==2?'金卡':'砖石卡';
        });
        $grid->pay_status('支付状态')->display(function($pay_status) {
            return $pay_status==0?'未支付':($pay_status==1?'支付失败':'<p style="color: #2d995b">已支付</p>');
        });
        $grid->update_time('支付时间');
        $grid->ip('Ip');
        $grid->telephone('手机号');
        $grid->orderid('订单id');
        $grid->province('省');
        $grid->city('市');
        $grid->area('县区');
        $grid->add_tel('收货手机号');
        $grid->usr('归属');
        $grid->transaction_id('微信订单id');
        $grid->total_fee('订单总金额');
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->disableCreateButton();

        $grid->model()->orderBy('create_time', 'desc');

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
        $show = new Show(MemberOrder::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->add('Add');
        $show->pay('Pay');
        $show->mem_type('Mem type');
        $show->pay_status('Pay status');
        $show->create_time('Create time');
        $show->update_time('Update time');
        $show->ip('Ip');
        $show->telephone('Telephone');
        $show->orderid('Orderid');
        $show->province('Province');
        $show->city('City');
        $show->area('Area');
        $show->add_tel('Add tel');
        $show->usr('Usr');
        $show->prepay_id('Prepay id');
        $show->transaction_id('Transaction id');
        $show->total_fee('Total fee');
        $show->cash_fee('Cash fee');
        $show->res_json('Res json');
        $show->phone_type('Phone type');
        $show->phone_type_name('Phone type name');
        $show->phone_type_do('Phone type do');
        $show->phone_type_do_name('Phone type do name');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MemberOrder);

        $form->text('name', 'Name');
        $form->textarea('add', 'Add');
        $form->decimal('pay', 'Pay');
        $form->switch('mem_type', 'Mem type');
        $form->switch('pay_status', 'Pay status');
        $form->datetime('create_time', 'Create time')->default(date('Y-m-d H:i:s'));
        $form->datetime('update_time', 'Update time')->default(date('Y-m-d H:i:s'));
        $form->ip('ip', 'Ip');
        $form->text('telephone', 'Telephone');
        $form->text('orderid', 'Orderid');
        $form->text('province', 'Province');
        $form->text('city', 'City');
        $form->text('area', 'Area');
        $form->text('add_tel', 'Add tel');
        $form->number('usr', 'Usr');
        $form->text('prepay_id', 'Prepay id');
        $form->text('transaction_id', 'Transaction id');
        $form->decimal('total_fee', 'Total fee');
        $form->decimal('cash_fee', 'Cash fee');
        $form->textarea('res_json', 'Res json');
        $form->text('phone_type', 'Phone type');
        $form->text('phone_type_name', 'Phone type name');
        $form->text('phone_type_do', 'Phone type do');
        $form->text('phone_type_do_name', 'Phone type do name');

        return $form;
    }
}
