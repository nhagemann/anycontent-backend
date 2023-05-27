<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements;

class FormElementTabEnd extends \AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements\FormElementTabNext
{

    public function render($layout)
    {
        $this->fetchTabContent();
        $tabs = $this->form->getFormVar('tabs', array());

        $this->vars['tabs'] = $tabs;

        // Clear form var for eventually next tab
        $this->form->setFormVar('tabs',array());

        return $this->twig->render('formelement-tab.twig', $this->vars);
    }
}