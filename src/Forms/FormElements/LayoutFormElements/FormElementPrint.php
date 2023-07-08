<?php

namespace AnyContent\Backend\Forms\FormElements\LayoutFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use Twig\Environment;

class FormElementPrint extends FormElementDefault
{
    protected string $template = '@AnyContentBackend/Forms/formelement-print.html.twig';

    public function render(Environment $twig)
    {
        $this->vars['display'] = $this->definition->getDisplay();

        return parent::render($twig);
    }
}
