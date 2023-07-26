<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\GeoLocationFormElement;

class FormElementGeoLocation extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    public function __construct($id, $name, $formElementDefinition, $app, $value = '')
    {
        parent::__construct($id, $name, $formElementDefinition, $app, $value);

    }


    public function render($layout)
    {

        $position = explode(',', (string)$this->value);
        if (count($position) == 2)
        {
            $this->vars['lat']  = $position[0];
            $this->vars['long'] = $position[1];
        }
        else
        {
            $this->vars['lat']  = '';
            $this->vars['long'] = '';
        }

        $tempId                  = md5(uniqid());
        $this->vars['tempid']    = $tempId;
        $this->vars['url_modal'] = '/edit/modal/geolocation/' . $tempId;

        return $this->twig->render('formelement-geolocation.twig', $this->vars);

    }


    public function parseFormInput($input)
    {
        $value        = '';
        $allowedchars = '-0123456789.,';
        $patterns     = "/[^" . $allowedchars . "]*/";

        $input[0] = str_replace(',', '.', preg_replace($patterns, "", $input[0]));
        $input[1] = str_replace(',', '.', preg_replace($patterns, "", $input[1]));

        if ($input[0] != '' AND $input[1] != '')
        {
            $value = $input[0] . ',' . $input[1];
        }

        return $value;
    }
}