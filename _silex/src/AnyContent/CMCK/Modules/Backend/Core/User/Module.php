<?php

namespace AnyContent\CMCK\Modules\Backend\Core\User;

use AnyContent\CMCK\Modules\Backend\Core\Application\ConfigService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\CMCK\Modules\Backend\Core\User\UserManager;

use Symfony\Component\HttpKernel\KernelEvents;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {

        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');

        $app->get('login', 'AnyContent\CMCK\Modules\Backend\Core\User\Controller::login')
            ->bind('login');

        $app->post('login', 'AnyContent\CMCK\Modules\Backend\Core\User\Controller::post')
            ->bind('postLogin');

        $app->get('logout', 'AnyContent\CMCK\Modules\Backend\Core\User\Controller::logout')
            ->bind('logout');

        $app['user'] = $app->share(function ($app) {
            return new UserManager($app, $app['context'], $app['config'], $app['session']);
        });

        // Perform a hard redirect, if no user is logged in
        $app->before(function (Request $request) use ($app) {

            $parts = explode('/', trim($request->getPathInfo(), '/'));

            if (isset($parts[0]) && !in_array($parts[0], $this->getListOfUnprotectedURLPaths())) {

                if (!$app['user']->isLoggedIn()) {
                    Header('Location: ' . $app['url_generator']->generate('login'), 303);
                    die ();
                }
            }

        });

        if ($this->app['env'] == 'console') {
            $app->registerAuthenticationAdapter('config',
                'AnyContent\CMCK\Modules\Backend\Core\User\ConsoleAuthenticationAdapter');
        } else {
            $app->registerAuthenticationAdapter('config',
                'AnyContent\CMCK\Modules\Backend\Core\User\ConfigAuthenticationAdapter');
        }

    }


    protected function getListOfUnprotectedURLPaths()
    {
        $list = [
            'login',
            'css',
            'js',
            'public'
        ];

        /** @var ConfigService $config */
        $config = $this->app['config'];


        if ($config->hasConfigurationSection('service'))
        {
            $section = $config->getConfigurationSection('service');
            if (array_key_exists('path',$section)) {
                $parts =  explode('/', trim($section['path'], '/'));
                $list[]=$parts[0];
            }
        }
        return $list;
    }

}