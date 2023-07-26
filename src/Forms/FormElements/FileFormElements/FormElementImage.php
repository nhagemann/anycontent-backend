<?php

namespace AnyContent\Backend\Forms\FormElements\FileFormElements;

use CMDL\FormElementDefinitions\ImageFormElementDefinition;

class FormElementImage extends FormElementFile
{
    /** @var  ImageFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-image.html.twig';
}
