<?php

namespace App\Admin\Controllers;

use App\AlgoSysdetectitem;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AlgoSysdetectitemController extends Controller
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
        $grid = new Grid(new AlgoSysdetectitem);

        $grid->id('Id');
        $grid->row('Row');
        $grid->item('Item');
        $grid->unit('Unit');
        $grid->reaction('Reaction');
        $grid->item_hint('Item hint');
        $grid->disease_id('Disease id');
        $grid->disease_full('Disease full');
        $grid->department('Department');
        $grid->standard_range('Standard range');
        $grid->version('Version');
        $grid->is_del('Is del');

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
        $show = new Show(AlgoSysdetectitem::findOrFail($id));

        $show->id('Id');
        $show->row('Row');
        $show->item('Item');
        $show->unit('Unit');
        $show->reaction('Reaction');
        $show->item_hint('Item hint');
        $show->disease_id('Disease id');
        $show->disease_full('Disease full');
        $show->department('Department');
        $show->standard_range('Standard range');
        $show->version('Version');
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
        $form = new Form(new AlgoSysdetectitem);

        $form->switch('row', 'Row');
        $form->text('item', 'Item');
        $form->text('unit', 'Unit');
        $form->textarea('reaction', 'Reaction');
        $form->textarea('item_hint', 'Item hint');
        $form->number('disease_id', 'Disease id');
        $form->textarea('disease_full', 'Disease full');
        $form->text('department', 'Department');
        $form->text('standard_range', 'Standard range');
        $form->text('version', 'Version');
        $form->switch('is_del', 'Is del');

        return $form;
    }
}
