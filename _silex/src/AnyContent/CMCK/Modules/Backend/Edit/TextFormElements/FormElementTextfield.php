<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\TextFormElements;

class FormElementTextfield extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function __construct($id, $name, $formElementDefinition, $app, $value = '', $options = array())
    {
        parent::__construct($id, $name, $formElementDefinition, $app, $value, $options);

        $sizes = array( 'S' => 'col-xs-2', 'M' => 'col-xs-5', 'L' => 'col-xs-8', 'XL' => 'col-xs-10', 'XXL' => 'col-xs-12' );

        $this->vars['class']['size'] = $sizes[$this->definition->getSize()];

    }


    public function render($layout)
    {

        return $this->twig->render('formelement-textfield.twig', $this->vars);
    }
}