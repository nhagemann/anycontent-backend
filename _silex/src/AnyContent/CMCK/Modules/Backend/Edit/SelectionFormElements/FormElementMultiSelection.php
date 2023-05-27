<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\SelectionFormElements;

class FormElementMultiSelection extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function render($layout)
    {

        if ($this->value)
        {
            $this->value = explode(',', $this->value);
        }
        else
        {
            $this->value = array();
        }

        $this->vars['type']    = $this->definition->getType();
        $this->vars['options'] = $this->definition->getOptions();

        return $this->twig->render('formelement-multiselection.twig', $this->vars);
    }


    public function parseFormInput($input)
    {
        $value = '';
        if (is_array($input))
        {
            $value = join(',', $input);
        }
        return $value;
    }
}