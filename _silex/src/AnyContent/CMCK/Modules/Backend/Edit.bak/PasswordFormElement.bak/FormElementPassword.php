<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\PasswordFormElement;

class FormElementPassword extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function __construct($id, $name, $formElementDefinition, $app, $value = '')
    {
        parent::__construct($id, $name, $formElementDefinition, $app, $value);
    }

    public function render($layout)
    {
        return $this->twig->render('formelement-password.twig', $this->vars);
    }

    public function parseFormInput($input)
    {
        $value = '';

        if (is_array($input)) {
            $value = $input[2];

            if ($input[0]!='') {

                $value = $input[0];
                $type = $this->definition->getType();

                $salt = md5(uniqid(mt_rand(), true));

                switch ($type) {
                    case 'md5':
                        $value = md5($value);
                        break;
                    case 'md5-salted':
                        $value = md5($value . $salt) . ':' . $salt;
                        break;
                    case 'sha1':
                        $value = sha1($value);
                        break;
                    case 'sha1-salted':
                        $value = sha1($value . $salt) . ':' . $salt;
                        break;
                }
            }
            else
            {
                if ($input[1]==1) // password has been cleared
                {
                    $value ='';
                }
            }

        }

        return $value;
    }
}