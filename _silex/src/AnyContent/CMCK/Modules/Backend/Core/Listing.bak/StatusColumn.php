<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use AnyContent\Client\Record;
use CMDL\FormElementDefinition;

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