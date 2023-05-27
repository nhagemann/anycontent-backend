<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\SelectionFormElements;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        $app->addTemplatesFolders(__DIR__ . '/views/');

    }


    public function run(Application $app)
    {
        $app['form']->registerFormElement('checkbox', 'AnyContent\CMCK\Modules\Backend\Edit\SelectionFormElements\FormElementCheckbox');
        $app['form']->registerFormElement('selection', 'AnyContent\CMCK\Modules\Backend\Edit\SelectionFormElements\FormElementSelection');
        $app['form']->registerFormElement('multiselection', 'AnyContent\CMCK\Modules\Backend\Edit\SelectionFormElements\FormElementMultiSelection');
    }

}