<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\DateTimeFormElements;

use AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault;
use CMDL\FormElementDefinition;

class FormElementDate extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function render($layout)
    {

        $value = $this->getValue();

        // new record, respect the init param
        if (!$this->context->getCurrentRecord() AND $value == '')
        {
            switch ($this->definition->getInit())
            {
                case 'today':
                    $value = date('Y-m-d');
                    break;
                case 'now':
                    $value = date('Y-m-d') . 'T' . date('H:i:s');
                    break;
            }

            if ($this->definition->getType() == 'short')
            {
                $value = date('m-d');
            }
        }

        $this->vars['month']  = '';
        $this->vars['day']    = '';
        $this->vars['hour']   = '';
        $this->vars['minute'] = '';
        $this->vars['second'] = '';
        $this->vars['value']  = '';

        if (strpos($value, 'T') !== false)
        {
            $tokens = explode('T', $value);

            if (count($tokens) == 2)
            {
                $this->extractDate($tokens[0]);
                $this->extractTime($tokens[1]);
            }
        }
        else
        {
            $this->extractDate($value);
        }

        $this->vars['type'] = $this->definition->getType();

        return $this->twig->render('formelement-datetime.twig', $this->vars);
    }


    public function extractDate($value)
    {

        $tokens = explode('-', $value);
        if (count($tokens) == 2)
        {
            $this->vars['month'] = $tokens[0];
            $this->vars['day']   = $tokens[1];
        }

        $this->vars['value'] = $value;
    }


    public function extractTime($value)
    {
        $tokens               = explode(':', $value);
        $this->vars['hour']   = $tokens[0];
        $this->vars['minute'] = $tokens[1];
        if (isset($tokens[2]))
        {
            $this->vars['second'] = $tokens[2];
        }

    }


    public function parseFormInput($input)
    {

        $value = '';

        switch ($this->definition->getType())
        {
            case 'short':
                if (is_array($input) AND count($input) == 2)
                {
                    if ((int)$input[0] != 0 AND (int)$input[1] != 0)
                    {
                        $value = str_pad($input[0], 2, '0') . '-' . str_pad($input[1], 2, '0');
                    }
                }

                break;
            case 'long':
                if (is_array($input) AND count($input) == 1)
                {
                    $value = $input[0];
                }

                break;
            default:

                if (is_array($input) AND count($input) >= 3)
                {
                    $value = $input[0] . 'T' . $input[1] . ':' . $input[2];

                    if (isset($input[3]))
                    {
                        $value .= ':' . $input[3];
                    }

                }
                break;
        }

        return $value;
    }

}