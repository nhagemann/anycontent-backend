<?php

namespace AnyContent\Backend\Forms\FormElements;

use CMDL\FormElementDefinition;
use Twig\Environment;

interface FormElementInterface
{
    public function getType();

    public function render(Environment $twig);

    public function setIsFirstElement($boolean);

    public function isFirstElement();

    public function setValue($value);

    public function getValue();

    public function parseFormInput(mixed $input): string;

    public function init(FormElementDefinition $definition, ?string $id, mixed $value = ''): void;

    //public function initOld(RepositoryManager $repositoryManager, ContextManager $contextManager, FormManager $formManager, UrlGeneratorInterface $urlGenerator): void;
}
