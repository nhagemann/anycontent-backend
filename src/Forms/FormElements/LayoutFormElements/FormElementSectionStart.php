<?php

namespace AnyContent\Backend\Forms\FormElements\LayoutFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\FormManager;
use CMDL\FormElementDefinitions\SectionStartFormElementDefinition;
use Twig\Environment;

class FormElementSectionStart extends FormElementDefault
{
    /** @var  SectionStartFormElementDefinition */
    protected $definition;

    protected string $type = 'section-start';

    protected string $template = '@AnyContentBackend/Forms/formelement-section-start.html.twig';

    public function __construct(private FormManager $formManager){

    }

    public function render(Environment $twig)
    {
        $nr = $this->formManager->getFormVar('section.nr', 1);

        $this->formManager->setFormVar('section.nr', $nr + 1);

        $this->vars['index'] = $nr;

        $this->vars['opened'] = $this->definition->getOpened();

        return parent::render($twig);
    }
}
