<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Init;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ArrayCache;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{
    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $app->register(new \Silex\Provider\SessionServiceProvider());

        $app['cache']=new ArrayCache();

    }


}