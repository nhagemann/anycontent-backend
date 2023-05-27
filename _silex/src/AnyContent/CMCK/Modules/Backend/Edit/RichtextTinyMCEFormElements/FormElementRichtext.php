<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\RichtextTinyMCEFormElements;

use AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault;
use CMDL\FormElementDefinition;

class FormElementRichtext extends \AnyContent\CMCK\Modules\Backend\Edit\TextFormElements\FormElementTextarea
{

    public function render($layout)
    {
        // get libary from CDN or local
        $cdn = $this->getOption('cdn', true);

        if ($cdn)
        {
            $layout->addJsLinkToHead('//tinymce.cachefly.net/4/tinymce.min.js');
        }
        else
        {
            $layout->addJsLinkToHead('/js/tinymce/tinymce.min.js');
        }

        // Add default config

        $layout->addJsFile('tinymce.js');

        return $this->twig->render('formelement-richtext.twig', $this->vars);
    }
}