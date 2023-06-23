<?php

namespace AnyContent\Backend\Forms\FormElements;

use AnyContent\Backend\Services\ContextManager;

interface FormElementInterface
{
    public function init(ContextManager $contextManager): void;
}
