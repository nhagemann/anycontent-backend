<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\SelectionFormElements;

use CMDL\FormElementDefinitions\SelectionFormElementDefinition;

class FormElementSelection extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    /** @var  SelectionFormElementDefinition */
    protected $definition;

    protected $autocompleteThreshold = 20;

    protected $templateName = 'formelement-selection.twig';

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
        if (array_key_exists($this->value, $options))
        {
            $label = $options[$this->value];
        }

        return $label;
    }


    protected function getSelectionType()
    {
        return $this->definition->getType();
    }


    public function render($layout)
    {
        $options = $this->getOptionsForSelectBox();

        $this->vars['type']    = $this->getSelectionType();
        $this->vars['options'] = $options;

        if (count($options) >= $this->autocompleteThreshold)
        {
            $this->vars['type']    = 'autocomplete';
            $this->vars['label']   = $this->getInitalLabelForAutoComplete();
            $this->vars['options'] = $this->getOptionsForAutocomplete();

        }

        return $this->twig->render($this->templateName, $this->vars);
    }


    protected function buildAutoCompleteLabelValueArray($options)
    {
        $array = [ ];
        foreach ($options as $k => $v)
        {
            $array[] = [ 'label' => $v, 'value' => $k ];
        }

        return $array;
    }
}