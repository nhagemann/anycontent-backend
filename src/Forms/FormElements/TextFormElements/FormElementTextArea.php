<?php

namespace AnyContent\Backend\Forms\FormElements\TextFormElements;

use CMDL\FormElementDefinitions\TextareaFormElementDefinition;

class FormElementTextArea extends FormElementTextField
{
    /** @var  TextareaFormElementDefinition */
    protected $definition;

    protected string $type = 'textarea';

    protected string $template = '@AnyContentBackend/Forms/formelement-textarea.html.twig';
}
