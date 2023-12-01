<?php

namespace AnyContent\Backend\Forms\FormElements;

use CMDL\FormElementDefinition;
use Twig\Environment;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class FormElementDefault implements FormElementInterface
{
    protected string $template = '@AnyContentBackend/Forms/formelement-default.html.twig';

    protected string $type = 'default';

    protected array $vars = [];

    protected bool $isFirstElement = false;

    protected $definition = null;
    protected ?string $id;
    protected ?string $name;
    protected mixed $value;

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

    public function getType()
    {
        return $this->type;
    }

//    public function getOption($key, $default = null)
//    {
//        if (array_key_exists($key, $this->options)) {
//            return $this->options[$key];
//        }
//
//        return $default;
//    }

    public function parseFormInput(mixed $input): string
    {
        return $input;
    }

    public function init(FormElementDefinition $definition, ?string $formId = null, ?string $formName = null, mixed $value = ''): void
    {
        $this->definition = $definition;
        $this->name = $formName;
        $this->id = $formId;
        $this->value = $value;

        $this->vars = [];
        $this->vars['id'] = $this->id;
        $this->vars['name'] = $this->name;
        $this->vars['definition'] = $this->definition;
        $this->vars['value'] = $this->value;
    }

//    public function initOld(
//        RepositoryManager     $repositoryManager,
//        ContextManager        $contextManager,
//        FormManager           $formManager,
//        UrlGeneratorInterface $urlGenerator
//    ): void
//    {
//        $this->repositoryManager = $repositoryManager;
//        $this->contextManager = $contextManager;
//        $this->formManager = $formManager;
//        $this->urlGenerator = $urlGenerator;
//    }
}
