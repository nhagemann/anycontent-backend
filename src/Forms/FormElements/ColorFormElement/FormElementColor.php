<?php

namespace AnyContent\Backend\Forms\FormElements\ColorFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\ColorFormElementDefinition;

class FormElementColor extends FormElementDefault
{
    /** @var  ColorFormElementDefinition */
    protected $definition;
    protected string $template = '@AnyContentBackend/Forms/formelement-color.html.twig';

    public function parseFormInput(string|array $input): string
    {
        $value = '';

        if (is_array($input)) {
            $value = array_shift($input);
        }

        return $value;
    }
}
