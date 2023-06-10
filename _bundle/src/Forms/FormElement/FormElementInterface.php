<?php

namespace AnyContent\Backend\Forms\FormElement;


use AnyContent\Backend\Services\ContextManager;

interface FormElementInterface
{

    public function setContext(ContextManager $contextManager): void;



}