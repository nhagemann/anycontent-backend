<?php

namespace AnyContent\Backend\Forms\FormElements\DateTimeFormElements;

use Twig\Environment;

class FormElementTime extends \AnyContent\Backend\Forms\FormElements\FormElementDefault
{
    protected string $template = '@AnyContentBackend/Forms/formelement-time.html.twig';

    public function render(Environment $twig)
    {
        $this->vars['hour']   = '';
        $this->vars['minute'] = '';
        $this->vars['second'] = '';

        $value = $this->getValue();

        // new record, respect the init param
        if (!$this->context->getCurrentRecord() and $value == '') {
            if ($this->definition->getInit() == 'now') {
                $value = date('H:i');

                if ($this->definition->getType() == 'long') {
                    $value = date('H:i:s');
                }
            }
        }

        $tokens = explode(':', $value);

        if (count($tokens) >= 2) {
            $this->vars['hour']   = $tokens[0];
            $this->vars['minute'] = $tokens[1];
            if (count($tokens) == 3 and $this->definition->getType() == 'long') {
                $this->vars['second'] = $tokens[2];
            }
        }

        $this->vars['type'] = $this->definition->getType();

        return parent::render($twig);
    }

    public function parseFormInput($input)
    {
        $value = '';

        if (is_array($input)) {
            $value = join(':', $input);
        }

        return $value;
    }
}