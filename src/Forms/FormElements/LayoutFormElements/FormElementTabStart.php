<?php

namespace AnyContent\Backend\Forms\FormElements\LayoutFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\TabStartFormElementDefinition;
use Twig\Environment;

class FormElementTabStart extends FormElementDefault
{
    /** @var  TabStartFormElementDefinition */
    protected $definition;

    public function render(Environment $twig)
    {
        $this->formManager->setFormVar('tab.label', $this->definition->getLabel());

        $this->formManager->startBuffer();

        return '';
    }
}
