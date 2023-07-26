<?php

namespace AnyContent\Backend\Forms\FormElements\LinkFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\LinkFormElementDefinition;

class FormElementLink extends FormElementDefault
{
    /** @var  LinkFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-link.html.twig';
}
