<?php

namespace AnyContent\Backend\Forms\FormElement;

use AnyContent\Backend\Services\ContextManager;
use CMDL\FormElementDefinition;
use Twig\Environment;

class FormElementDefault implements FormElementInterface
{
    protected array $vars = array();

    protected bool $isFirstElement = false;

    public function __construct(
        protected ?string $id,
        protected string $name,
        protected FormElementDefinition $definition,
        protected ?string $value = '',
        protected array $options = array()
    ) {
        $this->vars['id']         = $this->id;
        $this->vars['name']       = $this->name;
        $this->vars['definition'] = $this->definition;
        $this->vars['value']      = $this->value;
    }

    public function render(Environment $twig)
    {
        if ($this->definition->getName()) { // skip elements, that don't have a name, i.e. cannot get stored into a property
            return $twig->render('@AnyContentBackend/Forms/formelement-default.html.twig', $this->vars);
        }
    }

    public function setIsFirstElement($boolean)
    {
        $this->isFirstElement = $boolean;
    }

    public function isFirstElement()
    {
        return (bool)$this->isFirstElement;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getOption($key, $default = null)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        return $default;
    }

    public function parseFormInput($input)
    {
        return $input;
    }

    public function setContext(ContextManager $contextManager): void
    {
        // TODO: Implement setContext() method.
    }

//
//    public function getCurrentRepositoryAccessHash()
//    {
//        $repository = $this->context->getCurrentRepository();
//
//        return $this->repos->getAccessHash($repository);
//    }
}
