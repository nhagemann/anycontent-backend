<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\ReferenceFormElements;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        $app->addTemplatesFolders(__DIR__ . '/views/');
    }


    public function run(Application $app)
    {
        $app['form']->registerFormElement('reference', 'AnyContent\CMCK\Modules\Backend\Edit\ReferenceFormElements\FormElementReference');
        $app['form']->registerFormElement('multireference', 'AnyContent\CMCK\Modules\Backend\Edit\ReferenceFormElements\FormElementMultiReference');
    }

}