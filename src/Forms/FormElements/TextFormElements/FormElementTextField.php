<?php

namespace AnyContent\Backend\Forms\FormElements\TextFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use AnyContent\Backend\Services\RepositoryManager;
use CMDL\FormElementDefinition;
use CMDL\FormElementDefinitions\TextfieldFormElementDefinition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormElementTextField extends FormElementDefault
{
    /** @var  TextfieldFormElementDefinition */
    protected $definition;

    protected string $type = 'textfield';
    protected string $template = '@AnyContentBackend/Forms/formelement-textfield.html.twig';

    public function init(FormElementDefinition $definition, ?string $id, mixed $value = ''): void
    {
        parent::init($definition, $id, $value);
        $sizes = ['S' => 'col-xs-2', 'M' => 'col-xs-5', 'L' => 'col-xs-8', 'XL' => 'col-xs-10', 'XXL' => 'col-xs-12'];

        $this->vars['class']['size'] = $sizes[$this->definition->getSize()];
    }

}
