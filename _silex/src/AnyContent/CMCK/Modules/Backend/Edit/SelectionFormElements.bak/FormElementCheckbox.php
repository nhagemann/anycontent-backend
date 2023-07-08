<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\SelectionFormElements;

class FormElementCheckbox extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function render($layout)
    {
        $this->vars['checked'] = '';
        if ($this->getValue() == 1)
        {
            $this->vars['checked'] = 'checked="checked"';
        }

        $this->vars['legend'] = $this->definition->getLegend();

        return $this->twig->render('formelement-checkbox.twig', $this->vars);
    }


    public function parseFormInput($input)
    {
        $value = 0;

        if ($input == 1)
        {
            $value = 1;
        }

        return $value;
    }
}