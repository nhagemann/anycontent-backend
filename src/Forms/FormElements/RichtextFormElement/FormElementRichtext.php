<?php

namespace AnyContent\Backend\Forms\FormElements\RichtextFormElement;

use AnyContent\Backend\Forms\FormElements\TextFormElements\FormElementTextArea;
use CMDL\FormElementDefinitions\RichtextFormElementDefinition;

class FormElementRichtext extends FormElementTextArea
{
    /** @var  RichtextFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-richtext.html.twig';
}
