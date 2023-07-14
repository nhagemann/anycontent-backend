<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\InsertFormElement;

use CMDL\DataTypeDefinition;
use CMDL\FormElementDefinitions\InsertFormElementDefinition;

class FormElementInsert extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    /** @var  InsertFormElementDefinition */
    protected $definition;


    public function __construct($id, $name, $formElementDefinition, $app, $value = '')
    {
        parent::__construct($id, $name, $formElementDefinition, $app, $value);

    }


    public function render($layout)
    {
        return '';
    }


    /**
     * @param DataTypeDefinition $dataTypeDefinition
     * @param array              $values
     *
     * @return mixed
     */
    public function getClippingDefinition($dataTypeDefinition, $values = array(), $attributes = array())
    {

        if ($this->definition->getPropertyName()) // insert is based on a property (or attribute)
        {
            $value = null;
            if (strpos($this->definition->getPropertyName(), '.') !== false)
            {
                $attribute = array_pop(explode('.', $this->definition->getPropertyName()));

                if (array_key_exists($attribute, $attributes))
                {
                    $value = $attributes[$attribute];
                }
            }
            else
            {

                if (array_key_exists($this->definition->getPropertyName(), $values))
                {
                    $value = $values[$this->definition->getPropertyName()];
                }

            }

            $clippingName = $this->definition->getClippingName($value);

        }
        else
        {
            $clippingName = $this->definition->getClippingName();
        }

        if ($dataTypeDefinition->hasClippingDefinition($clippingName))
        {
            $clippingDefinition = $dataTypeDefinition->getClippingDefinition($clippingName);

            if ($this->definition->hasWorkspacesRestriction())
            {
                if (!in_array($this->context->getCurrentWorkspace(), $this->definition->getWorkspaces()))
                {
                    return false;
                }

            }
            if ($this->definition->hasLanguagesRestriction())
            {
                if (!in_array($this->context->getCurrentLanguage(), $this->definition->getLanguages()))
                {
                    return false;
                }

            }

            return $clippingDefinition;
        }
        else
        {
            return false;
        }
    }
}