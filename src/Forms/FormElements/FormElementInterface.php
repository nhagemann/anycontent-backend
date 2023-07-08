<?php

namespace AnyContent\Backend\Forms\FormElements;

use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface FormElementInterface
{
    public function init(ContextManager $contextManager, FormManager $formManager, UrlGeneratorInterface $urlGenerator): void;
}
