<?php

namespace AnyContent\Backend\Forms\FormElements\LayoutFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\FormElementDefinitions\HeadlineFormElementDefinition;
use Twig\Environment;

class FormElementHeadline extends FormElementDefault
{
    /** @var  HeadlineFormElementDefinition */
    protected $definition;

    protected string $type = 'headline';

    protected string $template = '@AnyContentBackend/Forms/formelement-headline.html.twig';

    public function render(Environment $twig)
    {
        $this->vars['first'] = false;
        if ($this->isFirstElement()) {
            $this->vars['first'] = true;
        }

        return parent::render($twig);
    }
}
