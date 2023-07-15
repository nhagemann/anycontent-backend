<?php

namespace AnyContent\Backend\Forms\FormElements\RichtextFormElement;

use AnyContent\Backend\Forms\FormElements\TextFormElements\FormElementTextArea;

class FormElementRichtext extends FormElementTextArea
{
    protected string $template = '@AnyContentBackend/Forms/formelement-richtext.html.twig';
}