<?php

namespace AnyContent\Backend\ContentViews\DefaultTable;

use AnyContent\Client\Record;

class SubtypeColumn extends PropertyColumn
{
    protected $type = 'Subtype';

    protected $badge = true;

    protected $property = 'subtype';

    public function getValue(Record $record)
    {
        return $record->getSubtypeLabel();
    }

    public function getClass()
    {
        return 'col-listing-subtype';
    }
}
