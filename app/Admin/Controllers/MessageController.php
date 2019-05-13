<?php

namespace App\Admin\Controllers;

use App\Message;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Log;
use JPush\Client as JPush;

class MessageController extends Controller
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
        $grid = new Grid(new Message);

        $grid->id('Id');
        $grid->channel()->display(function($channel) {
            return $channel == 1?'系统消息':'非系统消息';
        });;
        $grid->receive()->display(function($receive) {
            return $receive == 0?'全局发送':'部分发送';
        });

        $grid->text('发送内容');
        $grid->type()->display(function($type) {
            return $type == 1?'文本消息':'其他消息';
        });;
        $grid->remarks('备注');
        $grid->create_at('创建时间');

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
        $show = new Show(Message::findOrFail($id));

        $show->id('Id');
        $show->channel('Channel');
        $show->receive('Receive');
        $show->text('Text');
        $show->type('Type');
        $show->remarks('Remarks');
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
        $form = new Form(new Message);

        $form->select('channel', 'channel')->options([1 => '系统消息', 2 => '非系统'])->setWidth(1);
        $form->select('receive', 'Receive')->options([1 => '全局发送', 2 => '非全局'])->setWidth(1);
        $form->textarea('text', 'Text');
        $states = [
            'on'  => ['value' => 1, 'text' => '文本', 'color' => 'success'],
            'off' => ['value' => 2, 'text' => '非文本', 'color' => 'danger'],
        ];
        $form->switch('type', 'Type')->states($states);
        $form->text('remarks', 'Remarks');
        //保存后回调
        $form->saved(function (Form $form) {
            //...
            //发送 推送
            Log::info('Showing user profile for user123123: '. $form->model()->id);
            try{
                $message =  'chenyu test';
                $client = new JPush("255f4c7684e2c2704de6ef83", "d51eaa6db6e5663d1ee4eec1");
                $push = $client->push();
                $platform = array('ios', 'android');
                $alert = $message;

                $tag = array('18624048520');
                $ios_notification = array(
                    'sound' => 'hello',
                    'badge' => 2,
                    'content-available' => true,
                    'category' => 'jiguang',
                    'extras' => array(
                        'key' => 'value',
                        'jiguang'
                    ),
                );
                $android_notification = array(
                    'title' => 'hello',
                    'build_id' => 2,
                    'extras' => array(
                        'key' => 'value',
                        'jiguang'
                    ),
                );
                $content = $message;
                $message = array(
                    'title' => 'hello',
                    'content_type' => $message,
                    'extras' => array(
                        'id' => 1
                    ),
                );
                $options = array();
                $response = $push->setPlatform($platform)
                    ->addTag($tag)
                    //->addRegistrationId($regId)
                    ->iosNotification($alert, $ios_notification)
                    ->androidNotification($alert, $android_notification)
                    ->message($content, $message)
                    ->options($options)->send();
                return true;

            }

            catch (Exception $e)

            {
                print_r($e);
                return true;
            }

        });
        return $form;
    }
}
