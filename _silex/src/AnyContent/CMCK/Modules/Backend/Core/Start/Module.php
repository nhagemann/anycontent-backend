<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Start;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\CMCK\Modules\Backend\Core\Edit\FormManager;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');

        $app->get('/', 'AnyContent\CMCK\Modules\Backend\Core\Start\Controller::index')->bind('index');
        $app->get('/index/{repositoryAccessHash}', 'AnyContent\CMCK\Modules\Backend\Core\Start\Controller::indexRepository')->bind('indexRepository');
    }


    public function run(Application $app)
    {
    }
}