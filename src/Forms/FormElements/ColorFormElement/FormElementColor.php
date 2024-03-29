<?php

namespace AnyContent\Backend\Forms\FormElements\ColorFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\ColorFormElementDefinition;

class FormElementColor extends FormElementDefault
{
    /** @var  ColorFormElementDefinition */
    protected $definition;

    protected string $type = 'color';

    protected string $template = '@AnyContentBackend/Forms/formelement-color.html.twig';

    public function parseFormInput(mixed $input): string
    {
        $value = '';

        if (is_array($input)) {
            $value = array_shift($input);
        }

        return $value;
    }
}
