<?php

namespace App\Admin\Controllers;

use App\AppHome;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class ApphomeController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content,Request $request)
    {
        $key = $request->input('key');
        return $content
            ->header('家庭成员管理')
            ->description('description')
            ->body($this->grid($key));
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
    protected function grid($key)
    {
        $grid = new Grid(new AppHome);

        $grid->model()->where('user_id', '=', $key);
        $grid->model()->where('is_del', '=', 0);

        $grid->id('Id');
        $grid->name('Name');
        $grid->sex('Sex');
        $grid->age('Age');
        $grid->user_id('User id');
        $grid->is_use('Is use');
        $grid->wx_u('Wx u');

        $grid->actions(function ($actions) {
            $actions->disableEdit();//禁止编辑
            $actions->disableView();//禁止展示
            $actions->disableDelete();

        });

        $grid->disableRowSelector();
        $grid->disableCreateButton();//禁止创建按钮


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
        $show = new Show(AppHome::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->sex('Sex');
        $show->age('Age');
        $show->user_id('User id');
        $show->is_use('Is use');
        $show->wx_u('Wx u');
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
        $form = new Form(new AppHome);

        $form->text('name', 'Name');
        $form->switch('sex', 'Sex');
        $form->datetime('age', 'Age')->default(date('Y-m-d H:i:s'));
        $form->number('user_id', 'User id');
        $form->switch('is_use', 'Is use');
        $form->number('wx_u', 'Wx u');
        $form->switch('is_del', 'Is del');

        return $form;
    }
}
