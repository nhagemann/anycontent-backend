<?php

namespace AnyContent\Backend\Forms\FormElements\ColorFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;

class FormElementColor extends FormElementDefault
{
    protected string $template = '@AnyContentBackend/Forms/formelement-color.html.twig';

//    public function render(Environment $twig)
//    {
//
//        $layout->addJsFile('jquery.minicolors.min.js'); // from related library module Libs/jQueryMiniColors
//        $layout->addJsFile('feco.js');
//        $layout->addCssLinkToHead('/css/jquery-minicolors/jquery.minicolors.css');  // from related library module Libs/jQueryMiniColors
//
//        return $this->twig->render('formelement-color.twig', $this->vars);
//    }

    public function parseFormInput($input)
    {
        $value = '';

        if (is_array($input)) {
            $value = array_shift($input);
        }

        return $value;
    }
}
