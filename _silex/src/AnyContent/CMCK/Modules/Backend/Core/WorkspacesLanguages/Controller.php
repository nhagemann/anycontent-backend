<?php

namespace AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages;

use AnyContent\Client\ContentFilter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;

class Controller
{

    public static function changeWorkspaceListRecords(Application $app, Request $request, $contentTypeAccessHash, $page = 1)
    {
        $app['context']->setCurrentWorkspace($request->get('workspace'));

        return $app->redirect($app['url_generator']->generate('listRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page )),303);
    }


    public static function changeWorkspaceEditRecord(Application $app, Request $request, $contentTypeAccessHash, $recordId)
    {
        $app['context']->setCurrentWorkspace($request->get('workspace'));

        return $app->redirect($app['url_generator']->generate('editRecord', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId )),303);
    }


    public static function changeWorkspaceAddRecord(Application $app, Request $request, $contentTypeAccessHash)
    {
        $app['context']->setCurrentWorkspace($request->get('workspace'));

        return $app->redirect($app['url_generator']->generate('addRecord', array( 'contentTypeAccessHash' => $contentTypeAccessHash )),303);
    }


    public static function changeWorkspaceSortRecords(Application $app, Request $request, $contentTypeAccessHash)
    {
        $app['context']->setCurrentWorkspace($request->get('workspace'));

        return $app->redirect($app['url_generator']->generate('sortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash)),303);
    }

    public static function changeWorkspaceEditConfig(Application $app, Request $request, $configTypeAccessHash)
    {
        $app['context']->setCurrentWorkspace($request->get('workspace'));;

        return $app->redirect($app['url_generator']->generate('editConfig', array( 'configTypeAccessHash' => $configTypeAccessHash)),303);
    }

    public static function changeLanguageListRecords(Application $app, Request $request, $contentTypeAccessHash, $page = 1)
    {
        $app['context']->setCurrentLanguage($request->get('language'));

        return $app->redirect($app['url_generator']->generate('listRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page )),303);
    }


    public static function changeLanguageEditRecord(Application $app, Request $request, $contentTypeAccessHash, $recordId)
    {
        $app['context']->setCurrentLanguage($request->get('language'));

        return $app->redirect($app['url_generator']->generate('editRecord', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId )),303);
    }


    public static function changeLanguageAddRecord(Application $app, Request $request, $contentTypeAccessHash)
    {
        $app['context']->setCurrentLanguage($request->get('language'));;

        return $app->redirect($app['url_generator']->generate('addRecord', array( 'contentTypeAccessHash' => $contentTypeAccessHash )),303);
    }

    public static function changeLanguageSortRecords(Application $app, Request $request, $contentTypeAccessHash)
    {
        $app['context']->setCurrentLanguage($request->get('language'));;

        return $app->redirect($app['url_generator']->generate('sortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash)),303);
    }

    public static function changeLanguageEditConfig(Application $app, Request $request, $configTypeAccessHash)
    {
        $app['context']->setCurrentLanguage($request->get('language'));;

        return $app->redirect($app['url_generator']->generate('editConfig', array( 'configTypeAccessHash' => $configTypeAccessHash)),303);
    }


}