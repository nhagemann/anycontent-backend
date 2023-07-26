<?php

namespace AnyContent\Backend\Forms\FormElements\InsertFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use CMDL\DataTypeDefinition;
use CMDL\FormElementDefinitions\InsertFormElementDefinition;
use Twig\Environment;

class FormElementInsert extends FormElementDefault
{
    /** @var  InsertFormElementDefinition */
    protected $definition;

    public function render(Environment $twig)
    {
        return '';
    }

    /**
     * @param DataTypeDefinition $dataTypeDefinition
     * @param array              $values
     *
     * @return mixed
     */
    public function getClippingDefinition($dataTypeDefinition, $values = [], $attributes = [])
    {
        if ($this->definition->getPropertyName()) { // insert is based on a property (or attribute)
            $value = null;
            if (strpos($this->definition->getPropertyName(), '.') !== false) {
                $attribute = substr($this->definition->getPropertyName(), 1);

                if (array_key_exists($attribute, $attributes)) {
                    $value = $attributes[$attribute];
                }
            } else {
                if (array_key_exists($this->definition->getPropertyName(), $values)) {
                    $value = $values[$this->definition->getPropertyName()];
                }
            }

            $clippingName = $this->definition->getClippingName($value);
        } else {
            $clippingName = $this->definition->getClippingName();
        }

        if ($dataTypeDefinition->hasClippingDefinition($clippingName)) {
            $clippingDefinition = $dataTypeDefinition->getClippingDefinition($clippingName);

            if ($this->definition->hasWorkspacesRestriction()) {
                if (!in_array($this->contextManager->getCurrentWorkspace(), $this->definition->getWorkspaces())) {
                    return false;
                }
            }
            if ($this->definition->hasLanguagesRestriction()) {
                if (!in_array($this->contextManager->getCurrentLanguage(), $this->definition->getLanguages())) {
                    return false;
                }
            }

            return $clippingDefinition;
        } else {
            return false;
        }
    }
}
