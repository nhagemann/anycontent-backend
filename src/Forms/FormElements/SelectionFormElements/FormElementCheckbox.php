<?php

namespace AnyContent\Backend\Forms\FormElements\SelectionFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\CheckboxFormElementDefinition;
use Twig\Environment;

class FormElementCheckbox extends FormElementDefault
{
    /** @var CheckboxFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-checkbox.html.twig';

    public function render(Environment $twig)
    {
        $this->vars['checked'] = '';
        if ($this->getValue() == 1) {
            $this->vars['checked'] = 'checked="checked"';
        }

        $this->vars['legend'] = $this->definition->getLegend();

        return parent::render($twig);
    }

    public function parseFormInput(string|array $input): string
    {
        $value = 0;

        if ($input == 1) {
            $value = 1;
        }

        return $value;
    }
}
