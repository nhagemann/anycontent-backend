<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements;

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
        $app['form']->registerFormElement('sourcecode', 'AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements\FormElementSourceCode');
        $app['form']->registerFormElement('markdown', 'AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements\FormElementMarkdown');
        $app['form']->registerFormElement('html', 'AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements\FormElementHTML');
        $app['form']->registerFormElement('cmdl', 'AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements\FormElementCMDL');

    }

}