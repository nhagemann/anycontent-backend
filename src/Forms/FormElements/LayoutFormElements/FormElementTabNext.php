<?php

namespace AnyContent\Backend\Forms\FormElements\LayoutFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use Twig\Environment;

class FormElementTabNext extends FormElementDefault
{
    protected function fetchTabContent()
    {
        $nr = $this->formManager->getFormVar('tab.nr', 1);
        $this->formManager->setFormVar('tab.nr', $nr + 1);

        $label = $this->formManager->getFormVar('tab.label');
        $this->formManager->setFormVar('tab.label', $this->definition->getLabel());

        $tabs       = $this->formManager->getFormVar('tabs', []);
        $tabcontent = $this->formManager->endBuffer();

        $tabs[] = ['title' => $label, 'content' => $tabcontent, 'nr' => $nr];
        $this->formManager->setFormVar('tabs', $tabs);

        return $tabcontent;
    }

    public function render(Environment $twig)
    {
        $this->fetchTabContent();

        $this->formManager->startBuffer();

        return '';
    }
}
