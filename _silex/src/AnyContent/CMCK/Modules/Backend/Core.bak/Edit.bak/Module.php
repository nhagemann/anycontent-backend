<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Edit;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\CMCK\Modules\Backend\Core\Edit\FormManager;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{
    const EVENT_EDIT_RECORD_BEFORE_INSERT = 'event.record.before.insert';
    const EVENT_EDIT_RECORD_BEFORE_UPDATE = 'event.edit.before.update';
    const EVENT_EDIT_RECORD_BEFORE_DUPLICATE = 'event.record.before.duplicate';
    const EVENT_EDIT_RECORD_BEFORE_DELETE = 'event.record.before.delete';



    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');
        $app
            ->get('/content/edit/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Edit\Controller::editRecord')
            ->bind('editRecord')->value('workspace',null)->value('language',null);


        $app
            ->get('/content/add/{contentTypeAccessHash}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Edit\Controller::addRecord')
            ->bind('addRecord')->value('workspace',null)->value('language',null);

        $app
            ->get('/content/add/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Edit\Controller::addRecord')
            ->bind('addRecordVersion')->value('workspace',null)->value('language',null);

        $app
            ->get('/content/delete/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Edit\Controller::deleteRecord')
            ->bind('deleteRecord')->value('workspace',null)->value('language',null);

        $app
            ->get('/content/transfer/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Edit\Controller::transferRecordModal')
            ->bind('transferRecordModal')->value('workspace',null)->value('language',null);

        // The url parts "workspace" and "language" within post routes are necessary to mirror the possible get routes, but aren't processed

        $app
            ->post('/content/transfer/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Edit\Controller::transferRecord')
            ->bind('transferRecord')->value('workspace',null)->value('language',null);


        $app->post('/content/edit/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Edit\Controller::saveRecord')->value('workspace',null)->value('language',null);
        $app->post('/content/add/{contentTypeAccessHash}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Edit\Controller::saveRecord')->value('workspace',null)->value('language',null);
        $app->post('/content/add/{contentTypeAccessHash}/{recordId}/{workspace}/{language}', 'AnyContent\CMCK\Modules\Backend\Core\Edit\Controller::saveRecord')->value('workspace',null)->value('language',null);

        $app['form'] = $app->share(function ($app)
        {
            return new FormManager($app);
        });

    }


    public function run(Application $app)
    {
        $app['form']->registerFormElement('default', 'AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault');
    }

}