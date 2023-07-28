<?php

namespace AnyContent\Backend\Forms\FormElements\SelectionFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\SelectionFormElementDefinition;
use Twig\Environment;

class FormElementSelection extends FormElementDefault
{
    /** @var  SelectionFormElementDefinition */
    protected $definition;

    protected string $type = 'selection';

    protected string $template = '@AnyContentBackend/Forms/formelement-selection.html.twig';

    protected $autocompleteThreshold = 20;

    protected function getOptionsForSelectBox()
    {
        return $this->definition->getOptions();
    }

    protected function getOptionsForAutocomplete()
    {
        return $this->buildAutoCompleteLabelValueArray($this->getOptionsForSelectBox());
    }

    protected function getInitalLabelForAutoComplete()
    {
        $label   = '';
        $options = $this->getOptionsForSelectBox();
        if (array_key_exists($this->value, $options)) {
            $label = $options[$this->value];
        }

        return $label;
    }

    protected function getSelectionType()
    {
        return $this->definition->getType();
    }

    public function render(Environment $twig)
    {
        $options = $this->getOptionsForSelectBox();

        $this->vars['type']    = $this->getSelectionType();
        $this->vars['options'] = $options;

        if (count($options) >= $this->autocompleteThreshold) {
            $this->vars['type']    = 'autocomplete';
            $this->vars['label']   = $this->getInitalLabelForAutoComplete();
            $this->vars['options'] = $this->getOptionsForAutocomplete();
        }

        return parent::render($twig);
    }

    protected function buildAutoCompleteLabelValueArray($options)
    {
        $array = [ ];
        foreach ($options as $k => $v) {
            $array[] = ['label' => $v, 'value' => $k];
        }

        return $array;
    }
}
