<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\RangeFormElement;

class FormElementRange extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function render($layout)
    {
        if (!is_numeric($this->vars['value']))
        {
            $this->vars['value'] = (float)$this->vars['value'];
        }

        $this->vars['min']  = $this->definition->getMin();
        $this->vars['max']  = $this->definition->getMax();
        $this->vars['step'] = $this->definition->getStep();

        return $this->twig->render('formelement-range.twig', $this->vars);
    }

}