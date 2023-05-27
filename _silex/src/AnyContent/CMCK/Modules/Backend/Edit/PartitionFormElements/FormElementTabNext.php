<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements;

class FormElementTabNext extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    protected function fetchTabContent()
    {


        $nr = $this->form->getFormVar('tab.nr', 1);
        $this->form->setFormVar('tab.nr', $nr + 1);

        $label = $this->form->getFormVar('tab.label');
        $this->form->setFormVar('tab.label',$this->definition->getLabel());

        $tabs       = $this->form->getFormVar('tabs', array());
        $tabcontent = $this->form->endBuffer();

        $tabs[] = array( 'title' => $label, 'content' => $tabcontent, 'nr' => $nr );
        $this->form->setFormVar('tabs', $tabs);


        return $tabcontent;
    }


    public function render($layout)
    {
        $this->fetchTabContent();

        $this->form->startBuffer();

        return '';
    }
}