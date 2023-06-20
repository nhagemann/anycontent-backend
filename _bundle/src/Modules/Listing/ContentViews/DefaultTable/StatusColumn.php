<?php

namespace AnyContent\Backend\Modules\Listing\ContentViews\DefaultTable;

use AnyContent\Client\Record;

class StatusColumn extends PropertyColumn
{
    protected $type = 'Status';

    protected $badge = true;

    protected $property = 'status';

    public function getValue(Record $record)
    {
        return $record->getStatusLabel();
    }

    public function getClass()
    {
        return 'col-listing-status';
    }
}
