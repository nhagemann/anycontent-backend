<?php

namespace AnyContent\CMCK\Modules\Backend\Core\TimeShift;

use AnyContent\Client\ContentFilter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;

class Controller
{

    public static function timeShiftListRecords(Application $app, Request $request, $contentTypeAccessHash, $page = 1)
    {
        self::doTimeShift($app, $request);

        return $app->redirect($app['url_generator']->generate('listRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page )));
    }


    public static function timeShiftEditRecord(Application $app, Request $request, $contentTypeAccessHash, $recordId)
    {
        self::doTimeShift($app, $request);

        return $app->redirect($app['url_generator']->generate('editRecord', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId )));
    }


    public static function timeShiftSortRecords(Application $app, Request $request, $contentTypeAccessHash)
    {
        self::doTimeShift($app, $request);

        return $app->redirect($app['url_generator']->generate('sortRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash )));
    }


    public static function timeShiftEditConfig(Application $app, Request $request, $configTypeAccessHash)
    {
        self::doTimeShift($app, $request);

        return $app->redirect($app['url_generator']->generate('editConfig', array( 'configTypeAccessHash' => $configTypeAccessHash )));
    }


    protected function doTimeShift(Application $app, Request $request)
    {
        if ($request->request->has('reset'))
        {
            $app['context']->resetTimeShift();
        }
        else
        {

            try
            {
                $date = new \DateTime($request->get('date') . ' ' . $request->get('time'));

                $app['context']->setCurrentTimeShift($date->getTimestamp());
            }
            catch (\Exception $e)
            {
                $app['context']->resetTimeShift();
            }
        }
    }

}