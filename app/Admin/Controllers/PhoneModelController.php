<?php

namespace App\Admin\Controllers;

use App\PhoneModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class PhoneModelController extends Controller
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
            ->header('开放机型')
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
            ->header('开放机型')
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
            ->header('开放机型')
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
            ->header('开放机型')
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
        $grid = new Grid(new PhoneModel);

        $grid->id('Id');
        $grid->column('model');
        $grid->number('小型号');
        $grid->brand('品牌');
        $grid->status('状态')->display(function($status) {
            return $status == 0?'开放':'未开放';
        });;
        $grid->create_at('Create at');
        $grid->update_at('Update at');

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
        $show = new Show(PhoneModel::findOrFail($id));

        $show->id('Id');
        $show->model('Model');
        $show->number('Number');
        $show->brand('Brand');
        $show->status('Status');
        $show->create_at('Create at');
        $show->update_at('Update at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PhoneModel);

        $form->text('model', 'Model');
        $form->text('number', '小型号');
        $form->text('brand', '品牌');
        $states = [
            'on'  => ['value' => 0, 'text' => '开放', 'color' => 'success'],
            'off' => ['value' => 1, 'text' => '未开放', 'color' => 'danger'],
        ];
        $form->switch('status', '状态')->states($states);
        return $form;
    }
}
