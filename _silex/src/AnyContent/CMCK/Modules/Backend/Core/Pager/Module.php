<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Pager;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use AnyContent\CMCK\Modules\Backend\Core\Pager\PagingHelper;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');

        $app['pager'] = $app->share(function ($app)
        {
            return new PagingHelper($app['twig'], $app['layout'], $app['url_generator']);
        });

    }

}