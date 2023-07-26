<?php

namespace AnyContent\Backend\Forms\FormElements\LayoutFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\SectionEndFormElementDefinition;

class FormElementSectionEnd extends FormElementDefault
{
    /** @var  SectionEndFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-section-end.html.twig';
}
