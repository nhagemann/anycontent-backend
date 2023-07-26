<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use AnyContent\Client\Record;
use CMDL\FormElementDefinition;

class ButtonColumn extends BaseColumn
{

    protected $type = 'Button';

    protected $editButton = false;
    protected $deleteButton = false;


    /**
     * @return boolean
     */
    public function isEditButton()
    {
        return $this->editButton;
    }


    /**
     * @param boolean $editButton
     */
    public function setEditButton($editButton)
    {
        $this->editButton = $editButton;
    }


    /**
     * @return boolean
     */
    public function isDeleteButton()
    {
        return $this->deleteButton;
    }


    /**
     * @param boolean $deleteButton
     */
    public function setDeleteButton($deleteButton)
    {
        $this->deleteButton = $deleteButton;
    }


    public function getClass()
    {
        return 'col-listing-buttons';
    }

}