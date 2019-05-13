<?php

namespace App\Admin\Controllers;

use App\Search;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class SearchController extends Controller
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
            ->header('热门搜索')
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
            ->header('热门搜索')
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
            ->header('热门搜索')
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
            ->header('热门搜索')
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
        $grid = new Grid(new Search);

        $grid->id('Id');
        $grid->title('Title');
        $grid->type('Type');
        $grid->text('Text');
        $grid->status('Status');
        $grid->create_at('Create at');
        $grid->update_at('Update at');
        $grid->sort('Sort');

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
        $show = new Show(Search::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->type('Type');
        $show->text('Text');
        $show->status('Status');
        $show->create_at('Create at');
        $show->update_at('Update at');
        $show->sort('Sort');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Search);

        $form->text('title', 'Title');
        $form->text('type', 'Type');
        $form->textarea('text', 'Text');
        $form->switch('status', 'Status');
        $form->datetime('create_at', 'Create at')->default(date('Y-m-d H:i:s'));
        $form->datetime('update_at', 'Update at')->default(date('Y-m-d H:i:s'));
        $form->switch('sort', 'Sort')->default(99);

        return $form;
    }
}
