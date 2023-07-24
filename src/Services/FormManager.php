<?php

namespace AnyContent\Backend\Services;

use AnyContent\Backend\Setup\FormElementsAdder;
use CMDL\FormElementDefinition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class FormManager
{
    protected $formElements = [];

    protected $formVars = [];

    protected $buffering = false;

    protected $buffer = '';

    protected $dataTypeDefinition = null;

    public function __construct(
        FormElementsAdder $formElementsAdder,
        private RepositoryManager $repositoryManager,
        private ContextManager $contextManager,
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator
    ) {
        $formElementsAdder->setupFormElements($this);
    }

    public function registerFormElement($type, $class, $options = [])
    {
        $this->formElements[$type] = ['class' => $class, 'options' => $options];
    }

    public function registerCustomFormElement($type, $class, $options = [])
    {
        $this->formElements['custom'][$type] = ['class' => $class, 'options' => $options];
    }

    public function renderFormElements($formId, $formElementsDefinition, $values = [], $attributes = [], $prefix = '')
    {
        $this->clearFormVars();

        // first check for form elements added through insert annotations
        $formElementsDefinition       = $this->getFormElementsEventuallyInsertedThroughInsertAnnotation($formElementsDefinition, $values, $attributes);
        //$this->formElementsDefinition = $formElementsDefinition;

        $html = '';
        $i    = 0;
        /** @var FormElementDefinition $formElementDefinition */
        foreach ($formElementsDefinition as $formElementDefinition) {
            $i++;
            $value = '';
            $type  = $formElementDefinition->getFormElementType();

            $concrete = $this->getConcreteClassAndOptionsForFormElementDefinition($formElementDefinition);
            $class    = $concrete['class'];
            $options  = $concrete['options'];

            if (array_key_exists($formElementDefinition->getName(), $values)) {
                $value = $values[$formElementDefinition->getName()];
            }

            $name = $formElementDefinition->getName();

            if ($prefix) {
                $name = trim($prefix, '_') . '_' . $name;
            }
            $id = $formId . '_' . $type . '_' . $name;

            $formElement = new $class($id, $name, $formElementDefinition, $value, $options);
            $formElement->init($this->repositoryManager, $this->contextManager, $this, $this->urlGenerator);

            if ($i == 1) {
                $formElement->setIsFirstElement(true);
            }

            $htmlFormElement = $formElement->render($this->twig);

            if ($this->buffering) {
                $this->buffer .= $htmlFormElement;
            } else {
                $html .= $htmlFormElement;
            }
        }
        //$this->formElementsDefinition = null;

        return $html;
    }

    protected function getConcreteClassAndOptionsForFormElementDefinition($formElementDefinition)
    {
        $type = $formElementDefinition->getFormElementType();

        if (!array_key_exists($type, $this->formElements)) {
            $type = 'default';
        } else {
            if ($type == 'custom') {
                $type = $formElementDefinition->getType();

                if (array_key_exists($type, $this->formElements['custom'])) {
                    $class   = $this->formElements['custom'][$type]['class'];
                    $options = $this->formElements['custom'][$type]['options'];
                } else {
                    $type = 'default';
                }
            } else {
                $class   = $this->formElements[$type]['class'];
                $options = $this->formElements[$type]['options'];
            }
        }

        if ($type === 'default') {
            $class   = $this->formElements['default']['class'];
            $options = $this->formElements['default']['options'];
        }

        return ['class' => $class, 'options' => $options];
    }

    public function extractFormElementValuesFromPostRequest($request, $formElementsDefinition, $values = [], $attributes = [])
    {
        // first check for insertions and add form elements of those
        $formElementsDefinition       = $this->getFormElementsEventuallyInsertedThroughInsertAnnotation($formElementsDefinition, $values, $attributes);
        //$this->formElementsDefinition = $formElementsDefinition;

        $values = [];
        /** @var FormElementDefinition $formElementDefinition */
        foreach ($formElementsDefinition as $formElementDefinition) {
            $name = $formElementDefinition->getName();

            $concrete = $this->getConcreteClassAndOptionsForFormElementDefinition($formElementDefinition);
            $class    = $concrete['class'];
            $options  = $concrete['options'];

            $formElement = new $class(null, $name, $formElementDefinition, null, $options);

            $property = $formElementDefinition->getName();
            if ($property) {
                $values[$property] = $formElement->parseFormInput($request->get($property));
            }
        }

        //$this->formElementsDefinition = null;

        return $values;
    }

    public function getFormElementsEventuallyInsertedThroughInsertAnnotation($formElementsDefinition, $values, $attributes)
    {
        $integratedFormElementsDefinition = [];
        foreach ($formElementsDefinition as $formElementDefinition) {
            if ($formElementDefinition->getFormElementType() == 'insert' and array_key_exists('insert', $this->formElements)) {
                $class       = $this->formElements['insert']['class'];
                //$formElement = new $class(null, null, $formElementDefinition, $this->app, null, $this->formElements['insert']['options']);
                $formElement = new $class(null, null, $formElementDefinition, null, $this->formElements['insert']['options']);
                $formElement->init($this->repositoryManager, $this->contextManager, $this, $this->urlGenerator);

                $clippingDefinition = $formElement->getClippingDefinition($this->getDataTypeDefinition(), $values, $attributes);

                if ($clippingDefinition) {
                    foreach ($clippingDefinition->getFormElementDefinitions() as $formElementDefinitionOfClipping) {
                        $formElementDefinitionOfClipping->setInsertedByInsert($clippingDefinition->getName());
                        $integratedFormElementsDefinition[] = $formElementDefinitionOfClipping;
                    }
                }
            } else {
                $integratedFormElementsDefinition[] = $formElementDefinition;
            }
        }

        return $integratedFormElementsDefinition;
    }

    public function setDataTypeDefinition($dataTypeDefinition)
    {
        $this->dataTypeDefinition = $dataTypeDefinition;
    }

    public function getDataTypeDefinition()
    {
        return $this->dataTypeDefinition;
    }

    protected function clearFormVars()
    {
        $this->formVars = [];
    }

    public function setFormVar($key, $value)
    {
        $this->formVars[$key] = $value;
    }

    public function getFormVar($key, $default = null)
    {
        if (array_key_exists($key, $this->formVars)) {
            return $this->formVars[$key];
        }

        return $default;
    }

    public function startBuffer()
    {
        $this->buffering = true;
        $this->buffer    = '';
    }

    public function endBuffer()
    {
        $this->buffering = false;
        $buffer          = $this->buffer;
        $this->buffer    = '';

        return $buffer;
    }
}
