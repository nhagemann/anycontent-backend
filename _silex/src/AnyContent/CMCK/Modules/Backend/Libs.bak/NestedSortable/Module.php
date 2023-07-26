<?php

namespace AnyContent\CMCK\Modules\Backend\Libs\NestedSortable;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/mjsarfatti-nestedSortable');
    }


    public function run(Application $app)
    {
        $app['layout']->addJsFile('jquery.mjs.nestedSortable.js');
    }

}