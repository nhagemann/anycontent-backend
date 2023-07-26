<?php

namespace AnyContent\Backend\ContentViews\Glossary;

use AnyContent\Client\Record;

class GlossaryRecord extends Record
{
    private string $editUrl = '';

    public function getEditUrl(): string
    {
        return $this->editUrl;
    }

    public function setEditUrl(string $editUrl): void
    {
        $this->editUrl = $editUrl;
    }
}
