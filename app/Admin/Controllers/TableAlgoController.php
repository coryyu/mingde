<?php

namespace App\Admin\Controllers;

use App\TableAlgo;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class TableAlgoController extends Controller
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
            ->header('算法库版本控制')
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
            ->header('算法库版本控制')
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
            ->header('算法库版本控制')
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
            ->header('算法库版本控制')
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
        $grid = new Grid(new TableAlgo);

        $grid->id('Id');
        $grid->table('Table');
        $grid->version('Version');
        $grid->update_at('Update at');
        $grid->text('Text');
        $grid->model('Model');
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
        $show = new Show(TableAlgo::findOrFail($id));

        $show->id('Id');
        $show->table('Table');
        $show->version('Version');
        $show->update_at('Update at');
        $show->text('Text');
        $show->model('Model');
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
        $form = new Form(new TableAlgo);

        $form->text('table', 'Table');
        $form->text('version', 'Version');
        $form->datetime('update_at', 'Update at')->default(date('Y-m-d H:i:s'));
        $form->textarea('text', 'Text');
        $form->text('model', 'Model');
        $form->switch('is_del', 'Is del');

        return $form;
    }
}
