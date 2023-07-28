<?php

namespace AnyContent\Backend\Forms\FormElements;

use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use AnyContent\Backend\Services\RepositoryManager;
use CMDL\FormElementDefinition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

interface FormElementInterface
{
    public function render(Environment $twig);

    public function setIsFirstElement($boolean);

    public function isFirstElement();

    public function setValue($value);

    public function getValue();

    public function getOption($key, $default = null);

    public function parseFormInput(mixed $input): string;

    public function init(FormElementDefinition $definition, ?string $id, mixed $value = ''): void;

    //public function initOld(RepositoryManager $repositoryManager, ContextManager $contextManager, FormManager $formManager, UrlGeneratorInterface $urlGenerator): void;
}
