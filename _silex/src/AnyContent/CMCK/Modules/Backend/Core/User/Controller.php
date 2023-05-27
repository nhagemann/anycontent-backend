<?php

namespace AnyContent\CMCK\Modules\Backend\Core\User;

use AnyContent\Client\ContentFilter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;

class Controller
{

    public static function login(Application $app, Request $request)
    {
        return $app->renderPage('login.twig');
    }


    public static function post(Application $app, Request $request)
    {
        if ($request->get('u') != '' && $request->get('p') != '')
        {

            if ($app['user']->login($request->get('u'), $request->get('p')))
            {
                return new RedirectResponse($app['url_generator']->generate('index'), 303);
            }
            else
            {
                $app['context']->addErrorMessage('Username and password did not match.');
            }
        }
        else
        {
            $app['context']->addAlertMessage('Please provide username and password.');
        }

        return new RedirectResponse($app['url_generator']->generate('login'), 303);
    }


    public static function logout(Application $app)
    {
        $app['user']->logout();
        $app['context']->init();
        $app['context']->addInfoMessage('You have been logged out.');

        return new RedirectResponse($app['url_generator']->generate('login'), 303);
    }

}