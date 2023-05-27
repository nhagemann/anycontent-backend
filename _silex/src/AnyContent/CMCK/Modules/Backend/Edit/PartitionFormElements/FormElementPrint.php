<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements;

class FormElementPrint extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function render($layout)
    {
        $this->vars['display'] = $this->definition->getDisplay();

        return $this->twig->render('formelement-print.twig', $this->vars);
    }
}