<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\FileFormElements;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;


class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        $app->addTemplatesFolders(__DIR__ . '/views/');



        $app
            ->get('/edit/modal/{repositoryAccessHash}/file/{path}', 'AnyContent\CMCK\Modules\Backend\Edit\FileFormElements\Controller::modal')
            ->value('module',$this)->assert('path', '.+')->bind('formElementFileModal');


    }


    public function run(Application $app)
    {

        $app['form']->registerFormElement('file', 'AnyContent\CMCK\Modules\Backend\Edit\FileFormElements\FormElementFile');
        $app['form']->registerFormElement('image', 'AnyContent\CMCK\Modules\Backend\Edit\FileFormElements\FormElementImage');
    }

}