<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\Exception\AnyContentBackendException;
use AnyContent\Backend\Forms\FormElements\ColorFormElement\FormElementColor;
use AnyContent\Backend\Forms\FormElements\DateTimeFormElements\FormElementDate;
use AnyContent\Backend\Forms\FormElements\DateTimeFormElements\FormElementTime;
use AnyContent\Backend\Forms\FormElements\DateTimeFormElements\FormElementTimestamp;
use AnyContent\Backend\Forms\FormElements\EmailFormElement\FormElementEmail;
use AnyContent\Backend\Forms\FormElements\FileFormElements\FormElementFile;
use AnyContent\Backend\Forms\FormElements\FileFormElements\FormElementImage;
use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Forms\FormElements\InsertFormElement\FormElementInsert;
use AnyContent\Backend\Forms\FormElements\LayoutFormElements\FormElementHeadline;
use AnyContent\Backend\Forms\FormElements\LayoutFormElements\FormElementPrint;
use AnyContent\Backend\Forms\FormElements\LayoutFormElements\FormElementSectionEnd;
use AnyContent\Backend\Forms\FormElements\LayoutFormElements\FormElementSectionStart;
use AnyContent\Backend\Forms\FormElements\LayoutFormElements\FormElementTabEnd;
use AnyContent\Backend\Forms\FormElements\LayoutFormElements\FormElementTabNext;
use AnyContent\Backend\Forms\FormElements\LayoutFormElements\FormElementTabStart;
use AnyContent\Backend\Forms\FormElements\LinkFormElement\FormElementLink;
use AnyContent\Backend\Forms\FormElements\NumberFormElement\FormElementNumber;
use AnyContent\Backend\Forms\FormElements\PasswordFormElement\FormElementPassword;
use AnyContent\Backend\Forms\FormElements\RangeFormElement\FormElementRange;
use AnyContent\Backend\Forms\FormElements\ReferenceFormElements\FormElementMultiReference;
use AnyContent\Backend\Forms\FormElements\ReferenceFormElements\FormElementReference;
use AnyContent\Backend\Forms\FormElements\RichtextFormElement\FormElementRichtext;
use AnyContent\Backend\Forms\FormElements\SelectionFormElements\FormElementCheckbox;
use AnyContent\Backend\Forms\FormElements\SelectionFormElements\FormElementMultiSelection;
use AnyContent\Backend\Forms\FormElements\SelectionFormElements\FormElementSelection;
use AnyContent\Backend\Forms\FormElements\SequenceFormElement\FormElementSequence;
use AnyContent\Backend\Forms\FormElements\TableFormElement\FormElementTable;
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
        foreach ($this->getCustomFormElementClasses() as $type => $class) {
            $formManager->registerCustomFormElement($type, $class);
        }
    }

    private function getFormElementClasses(): array
    {
        $classes = [];

        // Default setup
        $classes['default'] = FormElementDefault::class;
        $classes['textfield'] = FormElementTextField::class;
        $classes['password'] = FormElementPassword::class;
        $classes['email'] = FormElementEmail::class;
        $classes['link'] = FormElementLink::class;
        $classes['textarea'] = FormElementTextArea::class;
        $classes['richtext'] = FormElementRichtext::class;
        $classes['checkbox'] = FormElementCheckbox::class;
        $classes['selection'] = FormElementSelection::class;
        $classes['multiselection'] = FormElementMultiSelection::class;
        $classes['reference'] = FormElementReference::class;
        $classes['multireference'] = FormElementMultiReference::class;
        $classes['number'] = FormElementNumber::class;
        $classes['range'] = FormElementRange::class;
        $classes['timestamp'] = FormElementTimestamp::class;
        $classes['date'] = FormElementDate::class;
        $classes['time'] = FormElementTime::class;
        $classes['color'] = FormElementColor::class;
        // Media form elements
        $classes['file'] = FormElementFile::class;
        $classes['image'] = FormElementImage::class;
        // layout form elements
        $classes['print'] = FormElementPrint::class;
        $classes['headline'] = FormElementHeadline::class;
        $classes['section-start'] = FormElementSectionStart::class;
        $classes['section-end'] = FormElementSectionEnd::class;
        $classes['tab-start'] = FormElementTabStart::class;
        $classes['tab-next'] = FormElementTabNext::class;
        $classes['tab-end'] = FormElementTabEnd::class;
        $classes['table'] = FormElementTable::class;

        // content type structure elements
        $classes['sequence'] = FormElementSequence::class;
        $classes['insert'] = FormElementInsert::class;

        // now add potentially configured standard form elements
        foreach ($this->formElements as $formElement) {
            if ($formElement['type'] !== 'custom') {
                $classes[$formElement['type']] = $formElement['class'];
            }
        }
        return $classes;
    }

    private function getCustomFormElementClasses(): array
    {
        $classes = [];
        foreach ($this->formElements as $formElement) {
            if ($formElement['type'] === 'custom') {
                if (!array_key_exists('custom_type', $formElement)) {
                    throw new AnyContentBackendException('Missing mandatory parameter custom_type for custom form elements.');
                }
                $classes[$formElement['custom_type']] = $formElement['class'];
            }
        }
        return $classes;
    }
}
