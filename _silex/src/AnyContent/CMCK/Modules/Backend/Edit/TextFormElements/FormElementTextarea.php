<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\TextFormElements;

class FormElementTextarea extends \AnyContent\CMCK\Modules\Backend\Edit\TextFormElements\FormElementTextfield
{

    public function render($layout)
    {

        return $this->twig->render('formelement-textarea.twig', $this->vars);
    }
}