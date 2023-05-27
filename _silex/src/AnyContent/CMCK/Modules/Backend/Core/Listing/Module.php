<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Listing;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app['contentViews'] = $app->share(function ($app)
        {
            return new ContentViewsManager($app);
        });

        $app->addTemplatesFolders(__DIR__ . '/views/');
        $app->get('/content/list/{contentTypeAccessHash}/{nr}/page/{page}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Listing\Controller::listRecords')
            ->bind('listRecords')->value('page', 1)->value('workspace', null)->value('language', null)->value('nr', 0);
        $app->get('/content/list/{contentTypeAccessHash}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Listing\Controller::listRecords')
            ->bind('listRecordsReset')->value('workspace', null)->value('language', null)->value('nr', 1);

        $app['contentViews']->registerContentView('default', 'AnyContent\CMCK\Modules\Backend\Core\Listing\ContentViewDefault');

    }

}