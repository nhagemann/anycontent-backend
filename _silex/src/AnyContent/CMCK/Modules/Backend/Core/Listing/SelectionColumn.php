<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use AnyContent\Client\Record;
use CMDL\FormElementDefinition;

class SelectionColumn extends PropertyColumn
{
    protected $type = 'Selection';

    public function getValue(Record $record)
    {
        $key = $record->getProperty($this->getProperty());

        $list = $this->getFormElementDefinition()->getList(1);

        if (array_key_exists($key,$list))
        {
            return $list[$key];
        }

        return $key;
    }





}