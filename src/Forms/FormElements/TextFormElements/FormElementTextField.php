<?php

namespace AnyContent\Backend\Forms\FormElements\TextFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinition;
use CMDL\FormElementDefinitions\TextfieldFormElementDefinition;

class FormElementTextField extends FormElementDefault
{
    /** @var  TextfieldFormElementDefinition */
    protected $definition;

    protected string $type = 'textfield';
    protected string $template = '@AnyContentBackend/Forms/formelement-textfield.html.twig';

    public function init(FormElementDefinition $definition, ?string $formId = null, ?string $formName = null, mixed $value = ''): void
    {
        parent::init($definition, $formId, $formName, $value);
        $sizes = ['S' => 'col-xs-2', 'M' => 'col-xs-5', 'L' => 'col-xs-8', 'XL' => 'col-xs-10', 'XXL' => 'col-xs-12'];

        $this->vars['class']['size'] = $sizes[$this->definition->getSize()];
    }
}
