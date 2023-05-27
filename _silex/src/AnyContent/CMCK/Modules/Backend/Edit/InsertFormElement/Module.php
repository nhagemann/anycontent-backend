<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\InsertFormElement;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function run(Application $app)
    {
        $app['form']->registerFormElement('insert', 'AnyContent\CMCK\Modules\Backend\Edit\InsertFormElement\FormElementInsert');
    }

}