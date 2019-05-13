<?php

namespace App\Admin\Controllers;

use App\Between;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class BetweenController extends Controller
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
            ->header('尿检区间')
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
            ->header('尿检区间')
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
            ->header('尿检区间')
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
            ->header('尿检区间')
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
        $grid = new Grid(new Between);

        $grid->id('Id');
        $grid->name('Name');
        $grid->start('Start');
        $grid->end('End');
        $grid->update_at('Update at');
        $grid->version('Version');

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
        $show = new Show(Between::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->start('Start');
        $show->end('End');
        $show->update_at('Update at');
        $show->version('Version');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Between);

        $form->text('name', 'Name');
        $form->time('start', 'Start')->default(date('H:i:s'));
        $form->time('end', 'End')->default(date('H:i:s'));
        $form->datetime('update_at', 'Update at')->default(date('Y-m-d H:i:s'));
        $form->text('version', 'Version');

        return $form;
    }
}
