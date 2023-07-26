<?php

namespace AnyContent\Backend\ContentViews\DefaultTable;

use AnyContent\Backend\Modules\Listing\ContentViews\DefaultTable\ColumnRenderer;
use AnyContent\Client\Record;

class BaseColumn
{
    protected $type = 'Base';

    protected $title;

    protected $class;

    protected $badge = false;

    protected $sortable = false;

    protected $sortString = '';

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /** @var  ColumnRenderer */
    protected $renderer;

    /**
     * @var bool
     */
    protected $linkToRecord = false;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return 'col-listing';
    }

    public function getValue(Record $record)
    {
        return '';
    }

    public function formatValue(Record $record)
    {
        return $this->getRenderer()->render($this, $record);
    }

    /**
     * @return boolean
     */
    public function isBadge()
    {
        return $this->badge;
    }

    /**
     * @param boolean $badge
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;
    }

    /**
     * @return boolean
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    public function setSortString($s)
    {
        $this->sortable = true;
        $this->sortString = $s;
    }

    public function getSortString()
    {
        return $this->sortString;
    }

    /**
     * @return CellRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param CellRenderer $renderer
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return boolean
     */
    public function isLinkToRecord()
    {
        return $this->linkToRecord;
    }

    /**
     * @param boolean $linkToRecord
     */
    public function setLinkToRecord($linkToRecord)
    {
        $this->linkToRecord = $linkToRecord;
    }
}
