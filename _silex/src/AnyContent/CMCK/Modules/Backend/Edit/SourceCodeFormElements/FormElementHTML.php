<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements;

class FormElementHTML extends \AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements\FormElementSourceCode
{

    protected function getCodeMirrorMode()
    {
        return 'htmlmixed';
    }


    protected function getCodeMirrorOptions()
    {
        $options = ''; // if you want to add options, you need to write them as json string with quotations (e.g. $options = '{"json":true}';)
        return $options;
    }


    protected function addCodeMirrorModeJavaScriptFiles($layout)
    {
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.0.3/mode/xml/xml.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.0.3/mode/javascript/javascript.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.0.3/mode/css/css.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.0.3/mode/vbscript/vbscript.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.0.3/mode/htmlmixed/htmlmixed.min.js');

        /*
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/xml/xml.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/javascript/javascript.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/css/css.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/vbscript/vbscript.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/htmlmixed/htmlmixed.min.js');
        */

    }

}