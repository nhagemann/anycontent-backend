<?php

namespace AnyContent\Backend\Modules\Listing\ContentViews\Default;

use AnyContent\Client\Record;
use CMDL\FormElementDefinition;

class PropertyColumn extends BaseColumn
{

    protected $type = 'Property';

    protected $property;

    protected $sortable = true;

    /**
     * @var FormElementDefinition
     */
    protected $formElementDefinition;


    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }


    /**
     * @param mixed $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }


    public function getValue(Record $record)
    {
        return ($record->getProperty($this->getProperty()));
    }


    /**
     * @param FormElementDefinition $formElementDefinition
     */
    public function setFormElementDefinition($formElementDefinition)
    {
        $this->formElementDefinition = $formElementDefinition;
    }


    /**
     * @return FormElementDefinition
     */
    public function getFormElementDefinition()
    {
        return $this->formElementDefinition;
    }


    public function getClass()
    {
        return 'col-listing-property-' . $this->getProperty();
    }

    public function getSortString()
    {
        return $this->getProperty();
    }

}