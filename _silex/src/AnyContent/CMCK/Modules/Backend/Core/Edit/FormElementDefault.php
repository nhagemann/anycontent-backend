<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Edit;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;
use CMDL\FormElementDefinition;

use AnyContent\CMCK\Modules\Backend\Core\Context\ContextManager;
use AnyContent\CMCK\Modules\Backend\Core\Edit\FormManager;

class FormElementDefault
{

    protected $id = '';

    protected $name = '';

    /** @var  FormElementDefinition */
    protected $definition;
    
    protected $value = '';

    /** @var  Application */
    protected $app;

    /** @var  \Twig_Environment */
    protected $twig;

    /** @var  ContextManager */
    protected $context;

    /** @var  FormManager */
    protected $form;

    protected $vars = array();

    protected $isFirstElement = false;

    protected $options = array();

    /** @var  RepositoryManager */
    protected $repos;

    public function __construct($id, $name, $formElementDefinition, $app, $value = '', $options = array())
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->definition = $formElementDefinition;
        $this->app        = $app;
        $this->twig       = $app['twig'];
        $this->form       = $app['form'];
        $this->context    = $app['context'];
        $this->repos      = $app['repos'];
        $this->value      = $value;
        $this->options    = $options;

        $this->vars['id']         = $this->id;
        $this->vars['name']       = $this->name;
        $this->vars['definition'] = $this->definition;
        $this->vars['value']      = $this->value;
    }


    public function render($layout)
    {
        if ($this->definition->getName()) // skip elements, that don't have a name, i.e. cannot get stored into a property
        {
            return $this->twig->render('formelement-default.twig', $this->vars);
        }

    }


    public function setIsFirstElement($boolean)
    {
        $this->isFirstElement = $boolean;
    }


    public function isFirstElement()
    {
        return (boolean)$this->isFirstElement;
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
        if (array_key_exists($key, $this->options))
        {
            return $this->options[$key];
        }

        return $default;
    }


    public function parseFormInput($input)
    {
        return $input;
    }


    public function getCurrentRepositoryAccessHash()
    {
        $repository = $this->context->getCurrentRepository();

        return $this->repos->getAccessHash($repository);
    }
}