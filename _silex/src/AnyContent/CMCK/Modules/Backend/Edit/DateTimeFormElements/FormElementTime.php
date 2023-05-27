<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\DateTimeFormElements;

use AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault;
use CMDL\FormElementDefinition;

class FormElementTime extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function render($layout)
    {

        $this->vars['hour']   = '';
        $this->vars['minute'] = '';
        $this->vars['second'] = '';

        $value = $this->getValue();

        // new record, respect the init param
        if (!$this->context->getCurrentRecord() AND $value == '')
        {
            if ($this->definition->getInit() == 'now')
            {
                $value = date('H:i');

                if ($this->definition->getType() == 'long')
                {
                    $value = date('H:i:s');
                }
            }
        }

        $tokens = explode(':', $value);

        if (count($tokens) >= 2)
        {
            $this->vars['hour']   = $tokens[0];
            $this->vars['minute'] = $tokens[1];
            if (count($tokens) == 3 AND $this->definition->getType() == 'long')
            {
                $this->vars['second'] = $tokens[2];
            }
        }

        $this->vars['type'] = $this->definition->getType();

        return $this->twig->render('formelement-time.twig', $this->vars);
    }


    public function parseFormInput($input)
    {

        $value = '';

        if (is_array($input))
        {
            $value = join(':', $input);
        }

        return $value;
    }

}