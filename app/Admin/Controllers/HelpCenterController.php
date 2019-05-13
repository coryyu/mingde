<?php

namespace App\Admin\Controllers;

use App\HelpCenter;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class HelpCenterController extends Controller
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
            ->header('帮助中心')
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
            ->header('帮助中心')
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
            ->header('帮助中心')
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
            ->header('帮助中心')
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
        $grid = new Grid(new HelpCenter);

        $grid->id('Id');
        $grid->text('内容');
        $grid->type('Type')->display(function ($type) {
            return $type ==1? '使用指南' :($type==2?'扫描说明':($type==3?'手动调整说明':'意见反馈') );
        });
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
        $show = new Show(HelpCenter::findOrFail($id));

        $show->id('Id');
        $show->text('Text');
        $show->type('Type');
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
        $form = new Form(new HelpCenter);

//        $form->textarea('text', 'Text');
//        $form->editor('text','Text');
        $form->editor('text');
//        $form->switch('type', 'Type');
        $form->select('type', 'Type')->options([1 => '使用指南', 2 => '扫描说明',  3 => '手动调整说明',4 => '意见反馈'])->setWidth(2);

        return $form;
    }
}
