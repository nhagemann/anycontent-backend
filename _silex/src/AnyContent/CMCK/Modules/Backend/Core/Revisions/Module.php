<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Revisions;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\CMCK\Modules\Backend\Core\Edit\FormManager;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');
        $app
            ->get('/content/revisions/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Revisions\Controller::listRecordRevisions')
            ->bind('listRecordRevisions')->value('workspace', null)->value('language', null);

        $app
            ->get('/config/revisions/{configTypeAccessHash}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Revisions\Controller::listConfigRevisions')
            ->bind('listConfigRevisions')->value('workspace', null)->value('language', null);

        $app
            ->get('/content/revision-timeshift/{contentTypeAccessHash}/{recordId}-{timeshift}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Revisions\Controller::editRecordRevision')
            ->bind('timeShiftIntoRecordRevision')->value('workspace', null)->value('language', null);

        $app
            ->get('/config/revision-timeshift/{configTypeAccessHash}/{timeshift}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Revisions\Controller::editConfigRevision')
            ->bind('timeShiftIntoConfigRevision')->value('workspace', null)->value('language', null);

        $app
            ->get('/content/revision-recreate/{contentTypeAccessHash}/{recordId}-{timeshift}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Revisions\Controller::recreateRecordRevision')
            ->bind('recreateRecordRevision')->value('workspace', null)->value('language', null);

        $app
            ->get('/config/revision-recreate/{configTypeAccessHash}/{timeshift}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Revisions\Controller::recreateConfigRevision')
            ->bind('recreateConfigRevision')->value('workspace', null)->value('language', null);
    }

}