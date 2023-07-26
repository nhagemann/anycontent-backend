<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements;

class FormElementCMDL extends \AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements\FormElementSourceCode
{

    protected function getCodeMirrorMode()
    {
        return 'cmdl';
    }


    protected function getCodeMirrorOptions()
    {
        $options = ''; // if you want to add options, you need to write them as json string with quotations (e.g. $options = '{"json":true}';)
        return $options;
    }


    protected function addCodeMirrorModeJavaScriptFiles($layout)
    {

    }

}