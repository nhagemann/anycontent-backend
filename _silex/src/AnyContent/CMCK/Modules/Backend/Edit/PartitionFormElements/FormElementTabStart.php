<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements;

class FormElementTabStart extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function render($layout)
    {
        $this->form->setFormVar('tab.label', $this->definition->getLabel());

        $this->form->startBuffer();

        return '';
    }
}