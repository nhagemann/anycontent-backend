<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements;

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
        $app['form']->registerFormElement('print', 'AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements\FormElementPrint');
        $app['form']->registerFormElement('headline', 'AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements\FormElementHeadline');
        $app['form']->registerFormElement('section-start', 'AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements\FormElementSectionStart');
        $app['form']->registerFormElement('section-end', 'AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements\FormElementSectionEnd');
        $app['form']->registerFormElement('tab-start', 'AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements\FormElementTabStart');
        $app['form']->registerFormElement('tab-next', 'AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements\FormElementTabNext');
        $app['form']->registerFormElement('tab-end', 'AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements\FormElementTabEnd');

    }

}