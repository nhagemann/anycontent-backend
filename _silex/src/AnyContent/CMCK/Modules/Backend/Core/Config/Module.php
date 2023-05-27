<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Config;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\CMCK\Modules\Backend\Core\Edit\FormManager;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');
        $app
            ->get('/config/edit/{configTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\Config\Controller::editConfig')
            ->bind('editConfig');

        $app->post('/config/edit/{configTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\Config\Controller::saveConfig');

        $app['form'] = $app->share(function ($app)
        {
            return new FormManager($app);
        });

    }

}