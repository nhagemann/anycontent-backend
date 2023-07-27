<?php

namespace AnyContent\Backend\Forms\FormElements\DateTimeFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\DateFormElementDefinition;
use Twig\Environment;

class FormElementDate extends FormElementDefault
{
    /** @var DateFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-datetime.html.twig';

    public function render(Environment $twig)
    {
        $value = $this->getValue();

        // new record, respect the init param
        if (!$this->contextManager->getCurrentRecord() and $value == '') {
            switch ($this->definition->getInit()) {
                case 'today':
                    $value = date('Y-m-d');
                    break;
                case 'now':
                    $value = date('Y-m-d') . 'T' . date('H:i:s');
                    break;
            }

            if ($this->definition->getType() == 'short') {
                $value = date('m-d');
            }
        }

        $this->vars['month']  = '';
        $this->vars['day']    = '';
        $this->vars['hour']   = '';
        $this->vars['minute'] = '';
        $this->vars['second'] = '';
        $this->vars['value']  = '';

        if (strpos($value, 'T') !== false) {
            $tokens = explode('T', $value);

            if (count($tokens) == 2) {
                $this->extractDate($tokens[0]);
                $this->extractTime($tokens[1]);
            }
        } else {
            $this->extractDate($value);
        }

        $this->vars['type'] = $this->definition->getType();

        return parent::render($twig);
    }

    public function extractDate($value)
    {
        $tokens = explode('-', $value);
        if (count($tokens) == 2) {
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
        if (isset($tokens[2])) {
            $this->vars['second'] = $tokens[2];
        }
    }

    public function parseFormInput(mixed $input): string
    {
        $value = '';

        switch ($this->definition->getType()) {
            case 'short':
                if (is_array($input) and count($input) == 2) {
                    if ((int)$input[0] != 0 and (int)$input[1] != 0) {
                        $value = str_pad($input[0], 2, '0') . '-' . str_pad($input[1], 2, '0');
                    }
                }

                break;
            case 'long':
                if (is_array($input) and count($input) == 1) {
                    $value = $input[0];
                }

                break;
            default:
                if (is_array($input) and count($input) >= 3) {
                    $value = $input[0] . 'T' . $input[1] . ':' . $input[2];

                    if (isset($input[3])) {
                        $value .= ':' . $input[3];
                    }
                }
                break;
        }

        return $value;
    }
}
