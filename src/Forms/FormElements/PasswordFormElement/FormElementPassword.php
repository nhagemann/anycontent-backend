<?php

namespace AnyContent\Backend\Forms\FormElements\PasswordFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;

class FormElementPassword extends FormElementDefault
{
    protected string $template = '@AnyContentBackend/Forms/formelement-password.html.twig';

    public function parseFormInput($input)
    {
        $value = '';

        if (is_array($input)) {
            $value = $input[2];

            if ($input[0] != '') {
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
            } else {
                if ($input[1] == 1) { // password has been cleared
                    $value = '';
                }
            }
        }

        return $value;
    }
}
