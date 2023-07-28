<?php

namespace AnyContent\Backend\Forms\FormElements\DateTimeFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\ContextManager;
use CMDL\FormElementDefinitions\TimeFormElementDefinition;
use Twig\Environment;

class FormElementTime extends FormElementDefault
{
    /** @var  TimeFormElementDefinition */
    protected $definition;

    protected string $type = 'time';

    protected string $template = '@AnyContentBackend/Forms/formelement-time.html.twig';

    public function __construct(private ContextManager $contextManager)
    {
    }

    public function render(Environment $twig)
    {
        $this->vars['hour']   = '';
        $this->vars['minute'] = '';
        $this->vars['second'] = '';

        $value = $this->getValue();

        // new record, respect the init param
        if (!$this->contextManager->getCurrentRecord() and $value == '') {
            if ($this->definition->getInit() == 'now') {
                $value = date('H:i');

                if ($this->definition->getType() == 'long') {
                    $value = date('H:i:s');
                }
            }
        }

        $tokens = explode(':', $value);

        if (count($tokens) >= 2) {
            $this->vars['hour']   = $tokens[0];
            $this->vars['minute'] = $tokens[1];
            if (count($tokens) == 3 and $this->definition->getType() == 'long') {
                $this->vars['second'] = $tokens[2];
            }
        }

        $this->vars['type'] = $this->definition->getType();

        return parent::render($twig);
    }

    public function parseFormInput(mixed $input): string
    {
        $value = '';

        if (is_array($input)) {
            $value = join(':', $input);
        }

        return $value;
    }
}
