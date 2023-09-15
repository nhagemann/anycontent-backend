<?php

namespace AnyContent\Backend\Command;

class Selectable
{
    public function __construct(private string $title, private ?object $object)
    {
    }

    public function getObject(): ?object
    {
        return $this->object;
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
