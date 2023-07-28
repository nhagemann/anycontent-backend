<?php

namespace AnyContent\Backend\Forms\FormElements\EmailFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\EmailFormElementDefinition;

class FormElementEmail extends FormElementDefault
{
    /** @var  EmailFormElementDefinition */
    protected $definition;

    protected string $type = 'email';

    protected string $template = '@AnyContentBackend/Forms/formelement-email.html.twig';
}
