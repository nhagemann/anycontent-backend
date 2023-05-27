<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\LinkFormElement;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');

        $app
            ->get('/edit/check/link/{path}', 'AnyContent\CMCK\Modules\Backend\Edit\LinkFormElement\Controller::check')
            ->value('module', $this)->assert('path', '.+')->bind('checkLink');
    }


    public function run(Application $app)
    {
        $app['form']->registerFormElement('link', 'AnyContent\CMCK\Modules\Backend\Edit\LinkFormElement\FormElementLink');

    }

}