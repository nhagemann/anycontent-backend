<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\FormManager;

class FormElementsAdder
{
    public function __construct(private array $formElements)
    {
    }

    public function setupFormElements(FormManager $formManager)
    {
        foreach ($this->getFormElementClasses() as $type => $class) {
            $formManager->registerFormElement($type, $class);
        }
    }

    private function getFormElementClasses(): array
    {
        $classes = [];

        $classes['default'] = FormElementDefault::class;

        foreach ($this->formElements as $formElement) {
            $classes[$formElement['type']] = $formElement['class'];
        }
        return $classes;
    }
}
