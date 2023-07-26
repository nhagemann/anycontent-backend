<?php

namespace AnyContent\CMCK\Modules\Backend\Libs\jQueryAutosize;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);
        $app->addTemplatesFolders(__DIR__ . '/autosize-master');
    }


    public function run(Application $app)
    {
        $app['layout']->addJsFile('jquery.autosize.min.js');
    }

}