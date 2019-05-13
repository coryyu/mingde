<?php

namespace App\Admin\Controllers;

use App\MemberIndex;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MemberIndexController extends Controller
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
        $grid = new Grid(new MemberIndex);

        $grid->id('Id');
        $grid->title('Title');
        $grid->path('Path');
        $grid->sort('Sort');
        $grid->text('Text');
        $grid->is_del('Is del');
        $grid->create_at('Create at');
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
        $show = new Show(MemberIndex::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->path('Path');
        $show->sort('Sort');
        $show->text('Text');
        $show->is_del('Is del');
        $show->create_at('Create at');
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
        $form = new Form(new MemberIndex);

        $form->text('title', 'Title');
        $form->text('path', 'Path');
        $form->switch('sort', 'Sort')->default(99);
        $form->textarea('text', 'Text');
        $form->switch('is_del', 'Is del');
        $form->datetime('create_at', 'Create at')->default(date('Y-m-d H:i:s'));
        $form->datetime('update_at', 'Update at')->default(date('Y-m-d H:i:s'));
        $form->text('version', 'Version');

        return $form;
    }
}
