<?php

namespace App\Admin\Controllers;

use App\MemberCard;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MemberCardController extends Controller
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
        $grid = new Grid(new MemberCard);

        $grid->id('Id');
        $grid->name('卡名');
        $grid->pay('价格');
        $grid->status('状态')->display(function($status) {
            return $status==0?'显示':'隐藏';
        });
        $grid->sort('排序');
        $grid->create_at('Create at');
        $grid->update_at('Update at');
        $grid->text('备注');
        $grid->icon('图标地址');
        $grid->card('卡片图片地址');
        $grid->logo('logo');
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();

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
        $show = new Show(MemberCard::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->pay('Pay');
        $show->status('Status');
        $show->sort('Sort');
        $show->create_at('Create at');
        $show->update_at('Update at');
        $show->text('Text');
        $show->icon('Icon');
        $show->card('Card');
        $show->card('Logo');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MemberCard);

        $form->text('name', '卡名');
        $form->decimal('pay', '金额');
        $states = [
            'on'  => ['value' => 0, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 1, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $form->switch('status', '展示')->states($states);
        $form->text('sort', '排序');
        $form->textarea('text', '备注');
        $form->image('icon')->uniqueName();
        $form->image('card')->uniqueName();
        $form->image('logo')->uniqueName();
        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
        });
        return $form;
    }
}
