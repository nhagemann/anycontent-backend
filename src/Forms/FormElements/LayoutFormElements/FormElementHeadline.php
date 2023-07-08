<?php

namespace AnyContent\Backend\Forms\FormElements\LayoutFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use Twig\Environment;

class FormElementHeadline extends FormElementDefault
{
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
