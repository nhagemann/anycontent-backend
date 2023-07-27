<?php

namespace AnyContent\Backend\Forms\FormElements;

use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use AnyContent\Backend\Services\RepositoryManager;
use CMDL\FormElementDefinition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class FormElementDefault implements FormElementInterface
{
    protected string $template = '@AnyContentBackend/Forms/formelement-default.html.twig';
    protected array $vars = [];

    protected bool $isFirstElement = false;

    protected RepositoryManager $repositoryManager;
    protected ContextManager $contextManager;
    protected FormManager $formManager;
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @param FormElementDefinition $definition
     * @param string|array $value
     */
    public function __construct(
        protected ?string $id,
        protected ?string $name,
        protected $definition,
        protected $value = '',
        protected array $options = []
    ) {
        $this->vars['id']         = $this->id;
        $this->vars['name']       = $this->name;
        $this->vars['definition'] = $this->definition;
        $this->vars['value']      = $this->value;
    }

    public function render(Environment $twig)
    {
        // skip elements, that don't have a name, i.e. cannot get stored into a property, unless we are in a form element extended from default
        if ($this->definition->getName() || get_class($this) !== self::class) {
            return $twig->render($this->template, $this->vars);
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

    public function parseFormInput(mixed $input): string
    {
        return $input;
    }

    public function init(
        RepositoryManager $repositoryManager,
        ContextManager $contextManager,
        FormManager $formManager,
        UrlGeneratorInterface $urlGenerator
    ): void {
        $this->repositoryManager = $repositoryManager;
        $this->contextManager = $contextManager;
        $this->formManager = $formManager;
        $this->urlGenerator = $urlGenerator;
    }
}
