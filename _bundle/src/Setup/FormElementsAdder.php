<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\Forms\FormElement\FormElementDefault;
use AnyContent\Backend\Services\FormManager;

class FormElementsAdder
{
    public function __construct()
    {
    }

    public function setupFormElements(FormManager $formManager)
    {
         $formManager->registerFormElement('default', FormElementDefault::class);
    }
}
