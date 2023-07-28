<?php

namespace AnyContent\Backend\Events;

use AnyContent\Client\Config;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractConfigRecordEvent extends Event
{
    public function __construct(private Config $record, protected string $action = '')
    {
    }

    public function getRecord(): Config
    {
        return $this->record;
    }
}
