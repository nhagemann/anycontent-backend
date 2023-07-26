<?php

namespace AnyContent\Backend\Forms\FormElements\SelectionFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\MultiSelectionFormElementDefinition;
use Twig\Environment;

class FormElementMultiSelection extends FormElementDefault
{
    /** @var MultiSelectionFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-multiselection.html.twig';

    public function render(Environment $twig)
    {
        if ($this->value) {
            $this->value = explode(',', $this->value);
        } else {
            $this->value = [];
        }

        $this->vars['type']    = $this->definition->getType();
        $this->vars['options'] = $this->getOptionsForSelectBox();

        return parent::render($twig);
    }

    protected function getOptionsForSelectBox()
    {
        return $this->definition->getOptions();
    }

    public function parseFormInput($input)
    {
        $value = '';
        if (is_array($input)) {
            $value = join(',', $input);
        }
        return $value;
    }
}
