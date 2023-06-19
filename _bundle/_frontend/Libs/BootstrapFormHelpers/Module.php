<?php

namespace AnyContent\CMCK\Modules\Backend\Libs\BootstrapFormHelpers;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/vlamanna-BootstrapFormHelpers/dist/css');
        $app->addTemplatesFolders(__DIR__ . '/vlamanna-BootstrapFormHelpers/dist/js');

    }


    public function run(Application $app)
    {
        $app['layout']->addCssFile('bootstrap-formhelpers.min.css');
        $app['layout']->addJsFile('bootstrap-formhelpers.min.js');
    }

}