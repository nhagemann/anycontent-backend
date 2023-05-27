<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Timeshift;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app
            ->post('/timeshift/content/list/{contentTypeAccessHash}/page/{page}', 'AnyContent\CMCK\Modules\Backend\Core\TimeShift\Controller::timeShiftListRecords')
            ->bind('timeShiftListRecords');
        $app
            ->post('/timeshift/content/edit/{contentTypeAccessHash}/{recordId}', 'AnyContent\CMCK\Modules\Backend\Core\TimeShift\Controller::timeShiftEditRecord')
            ->bind('timeShiftEditRecord');
        $app
            ->post('/timeshift/content/sort/{contentTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\TimeShift\Controller::timeShiftSortRecords')
            ->bind('timeShiftSortRecords');
        $app
            ->post('/timeshift/config/edit/{configTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\TimeShift\Controller::timeShiftEditConfig')
            ->bind('timeShiftEditConfig');

    }


    public function preRender(Application $app)
    {

        $date = new \DateTime();

        $timeshift              = $app['layout']->getVar('timeshift', array());
        $timeshift['active']    = false;
        $timeshift['date']      = $date->format('d.m.Y');
        $timeshift['time']      = $date->format('H:i');
        $timeshift['timestamp'] = time();

        if ($app['context']->getCurrentTimeShift() != 0)
        {

            $date->setTimestamp($app['context']->getCurrentTimeShift());
            $timeshift['active']    = true;
            $timeshift['timestamp'] = $app['context']->getCurrentTimeShift();
            $timeshift['date']      = $date->format('d.m.Y');
            $timeshift['time']      = $date->format('H:i');
        }
        $app['layout']->addVar('timeshift', $timeshift);
    }
}