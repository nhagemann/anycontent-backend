<?php

namespace AnyContent\CMCK\Modules\Backend\View\CustomList;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

         $app['contentViews']->registerContentView('list', 'AnyContent\CMCK\Modules\Backend\View\CustomList\ContentViewCustomList');

    }


}