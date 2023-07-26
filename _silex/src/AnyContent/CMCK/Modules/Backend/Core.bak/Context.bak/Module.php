<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Context;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use Symfony\Component\HttpFoundation\ParameterBag;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{


    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        if ($app['env']=='console') // no session for console commands
        {
            $app['session'] = new ParameterBag();
        }

        $app['context'] = $app->share(function ($app)
        {
            $manager = new ContextManager($app);

            $manager->setDefaultNumberOfItemsPerPage($this->getOption('items_per_page',10));
            return $manager;
        });
    }


    public function run(Application $app)
    {

    }
}