<?php

namespace AnyContent\Backend\Forms\FormElements\LayoutFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\FormManager;
use CMDL\FormElementDefinitions\TabEndFormElementDefinition;
use Twig\Environment;

class FormElementTabEnd extends FormElementDefault
{
    /** @var  TabEndFormElementDefinition */
    protected $definition;

    protected string $type = 'tab-end';

    protected string $template = '@AnyContentBackend/Forms/formelement-tab.html.twig';

    public function __construct(private FormManager $formManager)
    {
    }

    public function render(Environment $twig)
    {
        $this->fetchTabContent();

        $tabs = $this->formManager->getFormVar('tabs', []);

        $this->vars['tabs'] = $tabs;

        // Clear form var for eventually next tab
        $this->formManager->setFormVar('tabs', []);

        return parent::render($twig);
    }

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
}
