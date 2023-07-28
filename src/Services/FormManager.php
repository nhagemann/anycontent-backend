<?php

namespace AnyContent\Backend\Services;

use AnyContent\Backend\DependencyInjection\DefaultImplementation;
use AnyContent\Backend\Forms\FormElements\FormElementInterface;
use CMDL\FormElementDefinition;
use CMDL\FormElementDefinitions\CustomFormElementDefinition;
use Twig\Environment;

class FormManager
{
    protected $formElements = [];

    protected $formVars = [];

    protected $buffering = false;

    protected $buffer = '';

    protected $dataTypeDefinition = null;

    public function __construct(
        private Environment $twig,
    ) {
        $this->formElements['custom'] = [];
    }

    public function registerFormElement(FormElementInterface $formElement)
    {
        if (array_key_exists($formElement->getType(), $this->formElements)) {
            if (!$this->formElements[$formElement->getType()] instanceof DefaultImplementation) {
                return;
            }
        }
        $this->formElements[$formElement->getType()] = $formElement;
    }

    public function registerCustomFormElement(FormElementInterface $formElement)
    {
        if (array_key_exists($formElement->getType(), $this->formElements['custom'])) {
            if (!$this->formElements['custom'][$formElement->getType()] instanceof DefaultImplementation) {
                return;
            }
        }
        $this->formElements['custom'][$formElement->getType()] = $formElement;
    }

    public function renderFormElements($formId, $formElementsDefinition, $values = [], $attributes = [], $prefix = '')
    {
        $this->clearFormVars();

        // first check for form elements added through insert annotations
        $formElementsDefinition = $this->getFormElementsEventuallyInsertedThroughInsertAnnotation($formElementsDefinition, $values, $attributes);

        $html = '';
        $i = 0;
        /** @var FormElementDefinition $formElementDefinition */
        foreach ($formElementsDefinition as $formElementDefinition) {
            $i++;
            $value = '';
            $type = $formElementDefinition->getFormElementType();

            if (array_key_exists($formElementDefinition->getName(), $values)) {
                $value = $values[$formElementDefinition->getName()];
            }

            $name = $formElementDefinition->getName();

            if ($prefix) {
                $name = trim($prefix, '_') . '_' . $name;
            }
            $id = $formId . '_' . $type . '_' . $name;
            $formElement = $this->initFormElement($formElementDefinition, $id, $name, $value);

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

        return $html;
    }

    private function initFormElement(FormElementDefinition $formElementDefinition, ?string $formId = null, ?string $formName = null, mixed $value = null)
    {
        if ($formElementDefinition->getFormElementType() === 'custom') {
            assert($formElementDefinition instanceof CustomFormElementDefinition);
            $formElement = $this->formElements['custom'][$formElementDefinition->getType()] ?? null;
        } else {
            $formElement = $this->formElements[$formElementDefinition->getFormElementType()] ?? null;
        }

        if ($formElement === null) {
            $formElement = $this->formElements['default'];
        }

        assert($formElement instanceof FormElementInterface);

        $formElement->init($formElementDefinition, $formId, $formName, $value);

        return $formElement;
    }

    public function extractFormElementValuesFromPostRequest($request, $formElementsDefinition, $values = [], $attributes = [])
    {
        // first check for insertions and add form elements of those
        $formElementsDefinition = $this->getFormElementsEventuallyInsertedThroughInsertAnnotation($formElementsDefinition, $values, $attributes);
        //$this->formElementsDefinition = $formElementsDefinition;

        $values = [];
        /** @var FormElementDefinition $formElementDefinition */
        foreach ($formElementsDefinition as $formElementDefinition) {
            $formElement = $this->initFormElement($formElementDefinition);

            $property = $formElementDefinition->getName();
            if ($property) {
                $values[$property] = $formElement->parseFormInput($request->get($property));
            }
        }

        return $values;
    }

    public function getFormElementsEventuallyInsertedThroughInsertAnnotation($formElementsDefinition, $values, $attributes)
    {
        $integratedFormElementsDefinition = [];
        foreach ($formElementsDefinition as $formElementDefinition) {
            if ($formElementDefinition->getFormElementType() == 'insert' and array_key_exists('insert', $this->formElements)) {
                $formElement = $this->initFormElement($formElementDefinition);

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
        $this->buffer = '';
    }

    public function endBuffer()
    {
        $this->buffering = false;
        $buffer = $this->buffer;
        $this->buffer = '';

        return $buffer;
    }
}
