<?php

namespace AnyContent\Backend\Forms\FormElements\RangeFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use Twig\Environment;

class FormElementRange extends FormElementDefault
{
    protected string $template = '@AnyContentBackend/Forms/formelement-range.html.twig';

    public function render(Environment $twig)
    {
        if (!is_numeric($this->vars['value'])) {
            $this->vars['value'] = (float)$this->vars['value'];
        }

        $this->vars['min']  = $this->definition->getMin();
        $this->vars['max']  = $this->definition->getMax();
        $this->vars['step'] = $this->definition->getStep();

        return parent::render($twig);
    }
}
