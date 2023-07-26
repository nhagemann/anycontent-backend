<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements;

use AnyContent\CMCK\Modules\Backend\Core\Layout\LayoutManager;

class FormElementSourceCode extends \AnyContent\CMCK\Modules\Backend\Edit\TextFormElements\FormElementTextarea
{

    protected function getCodeMirrorMode()
    {
        $mode = 'text';

        switch ($this->definition->getType())
        {
            case 'html':
                $mode = 'htmlmixed';
                break;
            case 'xml':
                $mode = 'xml';
                break;
            case 'html5':
                $mode = 'htmlmixed';
                break;
            case 'xhtml':
                $mode = 'htmlmixed';
                break;
            case 'javascript':
                $mode = 'javascript';
                break;
            case 'css';
                $mode = 'css';
                break;
            case 'markdown':
                $mode = 'markdown';
                break;
            case 'json':
                $mode = 'application/json';
                break;
            case 'yaml':
                $mode = 'yaml';
                break;
            case 'cmdl':
                $mode = "cmdl";
                break;
            case 'ini':
                $mode = 'properties';
                break;
            case 'sql':
                break;

        }

        return $mode;
    }


    protected function getCodeMirrorOptions()
    {
        $options = ''; // if you want to add options, you need to write them as json string with quotations (e.g. $options = '{"json":true}';)
        return $options;
    }


    protected function addCodeMirrorModeJavaScriptFiles($layout)
    {
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/xml/xml.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/javascript/javascript.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/css/css.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/vbscript/vbscript.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/htmlmixed/htmlmixed.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/markdown/markdown.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/yaml/yaml.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/properties/properties.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/mode/sql/sql.min.js');
    }


    public function render($layout)
    {

        $this->vars['mode']    = $this->getCodeMirrorMode();
        $this->vars['options'] = $this->getCodeMirrorOptions();

        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/codemirror.min.js');
        $layout->addJsLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/addon/display/autorefresh.js');
        $layout->addCSSLinkToHead('//cdnjs.cloudflare.com/ajax/libs/codemirror/5.33.0/codemirror.css');

        $this->addCodeMirrorModeJavaScriptFiles($layout);

        $layout->addJsFile('codemirror.js');

        return $this->twig->render('formelement-sourcecode.twig', $this->vars);

    }
}