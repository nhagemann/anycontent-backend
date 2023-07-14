<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements;

class FormElementHeadline extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function render($layout)
    {
        $this->vars['first'] = false;
        if ($this->isFirstElement())
        {
            $this->vars['first'] = true;
        }

        return $this->twig->render('formelement-headline.twig', $this->vars);
    }
}