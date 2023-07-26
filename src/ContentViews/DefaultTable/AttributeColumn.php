<?php

namespace AnyContent\Backend\ContentViews\DefaultTable;

class AttributeColumn extends BaseColumn
{
    protected $type = 'Attribute';

    protected $attribute;

    protected $sortable = true;

    /**
     * @return mixed
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param mixed $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = trim($attribute, '.');
    }

    public function getClass()
    {
        return 'col-listing-attribute-' . $this->getAttribute();
    }

    public function getSortString()
    {
        return '.' . $this->getAttribute();
    }
}
