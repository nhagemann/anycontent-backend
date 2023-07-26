<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\GeoLocationFormElement;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    /**
     * Available options:
     *
     * key  provide your google maps api key here
     *
     * @param Application $app
     * @param array       $options
     */
    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');
        $app
            ->get('/edit/modal/geolocation/{tempId}//', 'AnyContent\CMCK\Modules\Backend\Edit\GeoLocationFormElement\Controller::modal')
            ->value('module', $this);
        $app
            ->get('/edit/modal/geolocation/{tempId}/{lat}/', 'AnyContent\CMCK\Modules\Backend\Edit\GeoLocationFormElement\Controller::modal')
            ->value('module', $this);
        $app
            ->get('/edit/modal/geolocation/{tempId}/{lat}/{long}', 'AnyContent\CMCK\Modules\Backend\Edit\GeoLocationFormElement\Controller::modal')
            ->value('module', $this);

    }


    public function run(Application $app)
    {
        $app['form']->registerFormElement('geolocation', 'AnyContent\CMCK\Modules\Backend\Edit\GeoLocationFormElement\FormElementGeoLocation');

        $url = 'https://maps.googleapis.com/maps/api/js?v=3.19&sensor=false';

        if (array_key_exists('key', $this->options))
        {
            $url = 'https://maps.googleapis.com/maps/api/js?key=' . $this->options['key'] . '&sensor=false';
        }
        //@upgrade add only if a record with geolocation form element is edited
        $app['layout']->addJsLinkToHead($url);
    }

}