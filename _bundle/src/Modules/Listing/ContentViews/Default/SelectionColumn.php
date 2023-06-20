<?php

namespace AnyContent\Backend\Modules\Listing\ContentViews\Default;

use AnyContent\Client\Record;

class SelectionColumn extends PropertyColumn
{
    protected $type = 'Selection';

    public function getValue(Record $record)
    {
        $key = $record->getProperty($this->getProperty());

        $list = $this->getFormElementDefinition()->getList(1);

        if (array_key_exists($key, $list)) {
            return $list[$key];
        }

        return $key;
    }
}
