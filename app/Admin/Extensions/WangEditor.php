<?php

namespace App\Admin\Extensions;

use Encore\Admin\Form\Field;

class WangEditor extends Field
{
    protected $view = 'admin.wang-editor';

    protected static $css = [
        '/vendor/wangEditor-master/release/wangEditor.min.css',
    ];

    protected static $js = [
        '/vendor/wangEditor-master/release/wangEditor.min.js',
    ];

    public function render()
    {
        $name = $this->formatName($this->column);
        $uploadImgUrl = config('wang-editor.uploadImgUrl', '/admin/upload/uploadImg');
        $this->script = <<<EOT

        var E = window.wangEditor
        var editor = new E('#{$this->id}');
        editor.customConfig.zIndex = 0
        editor.customConfig.uploadImgShowBase64 = true
        editor.customConfig.onchange = function (html) {
            $('input[name=\'$name\']').val(html);
        }
        editor.customConfig.uploadImgServer = '/uploadFile';


        editor.create()

EOT;
        return parent::render();
    }
}