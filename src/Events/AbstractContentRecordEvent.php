<?php

namespace AnyContent\Backend\Events;

use AnyContent\Client\Record;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractContentRecordEvent extends Event
{
    public function __construct(private Record $record, protected string $action = '')
    {
    }

    public function getRecord(): Record
    {
        return $this->record;
    }
}
