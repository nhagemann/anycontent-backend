<?php

namespace AnyContent\Backend\Forms\FormElements;

use AnyContent\Backend\Services\ContextManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface FormElementInterface
{
    public function init(ContextManager $contextManager, UrlGeneratorInterface $urlGenerator): void;
}
