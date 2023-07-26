<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\DateTimeFormElements;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');

    }


    public function run(Application $app)
    {

        $app['form']->registerFormElement('timestamp', 'AnyContent\CMCK\Modules\Backend\Edit\DateTimeFormElements\FormElementTimestamp', $this->options);
        $app['form']->registerFormElement('date', 'AnyContent\CMCK\Modules\Backend\Edit\DateTimeFormElements\FormElementDate', $this->options);
        $app['form']->registerFormElement('time', 'AnyContent\CMCK\Modules\Backend\Edit\DateTimeFormElements\FormElementTime', $this->options);

    }

}