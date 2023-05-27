<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use AnyContent\Client\Record;
use CMDL\FormElementDefinition;

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