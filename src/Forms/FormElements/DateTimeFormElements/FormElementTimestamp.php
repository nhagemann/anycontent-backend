<?php

namespace AnyContent\Backend\Forms\FormElements\DateTimeFormElements;

use CMDL\FormElementDefinitions\TimestampFormElementDefinition;
use Twig\Environment;

class FormElementTimestamp extends \AnyContent\Backend\Forms\FormElements\FormElementDefault
{
    /** @var  TimestampFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-datetime.html.twig';

    public function render(Environment $twig)
    {
        $value = $this->getValue();

        // new record, respect the init param
        if (!$this->contextManager->getCurrentRecord() and $value == '') {
            switch ($this->definition->getInit()) {
                case 'today':
                    $value = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                    break;
                case 'now':
                    $value = time();
                    break;
            }
        }

        if (is_numeric($value)) {
            $this->vars['month']  = date('m', $value);
            $this->vars['day']    = date('d', $value);
            $this->vars['hour']   = date('H', $value);
            $this->vars['minute'] = date('i', $value);
            $this->vars['second'] = 0;
            if ($this->definition->getType() == 'full') {
                $this->vars['second'] = date('s', $value);
            }

            $this->vars['value'] = date('Y-m-d', $value);
        } else {
            $this->vars['month']  = '';
            $this->vars['day']    = '';
            $this->vars['hour']   = '';
            $this->vars['minute'] = '';
            $this->vars['second'] = '';
            $this->vars['value']  = '';
        }

        $this->vars['type'] = $this->definition->getType();

        return parent::render($twig);
    }

    public function parseFormInput(mixed $input): string
    {
        $value = '';

        if (is_array($input)) {
            $tokens = explode('-', $input[0]);
            if (count($tokens) == 3) {
                $year    = $tokens[0];
                $month   = $tokens[1];
                $day     = $tokens[2];
                $hour    = $input[1];
                $minute  = $input[2];
                $seconds = 0;

                if ($this->definition->getType() == 'full') {
                    $seconds = $input[3];
                }

                $value = mktime($hour, $minute, $seconds, $month, $day, $year);
            }
        }

        return $value;
    }
}
