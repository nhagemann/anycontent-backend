<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\Forms\FormElements\ColorFormElement\FormElementColor;
use AnyContent\Backend\Forms\FormElements\DateTimeFormElements\FormElementDate;
use AnyContent\Backend\Forms\FormElements\DateTimeFormElements\FormElementTime;
use AnyContent\Backend\Forms\FormElements\DateTimeFormElements\FormElementTimestamp;
use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Forms\FormElements\NumberFormElement\FormElementNumber;
use AnyContent\Backend\Forms\FormElements\SelectionFormElements\FormElementCheckbox;
use AnyContent\Backend\Forms\FormElements\SelectionFormElements\FormElementMultiSelection;
use AnyContent\Backend\Forms\FormElements\SelectionFormElements\FormElementSelection;
use AnyContent\Backend\Forms\FormElements\TextFormElements\FormElementTextArea;
use AnyContent\Backend\Forms\FormElements\TextFormElements\FormElementTextField;
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

        // Default setup
        $classes['default'] = FormElementDefault::class;
        $classes['textfield'] = FormElementTextField::class;
        $classes['textarea'] = FormElementTextArea::class;
        $classes['checkbox'] = FormElementCheckbox::class;
        $classes['selection'] = FormElementSelection::class;
        $classes['multiselection'] = FormElementMultiSelection::class;
        $classes['number'] = FormElementNumber::class;
        $classes['timestamp'] = FormElementTimestamp::class;
        $classes['date'] = FormElementDate::class;
        $classes['time'] = FormElementTime::class;
        $classes['color'] = FormElementColor::class;

        foreach ($this->formElements as $formElement) {
            $classes[$formElement['type']] = $formElement['class'];
        }
        return $classes;
    }
}
