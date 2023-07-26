<?php

namespace AnyContent\CMCK\Modules\Backend\View\Glossary;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');

        $app['contentViews']->registerContentView('glossary', 'AnyContent\CMCK\Modules\Backend\View\Glossary\ContentViewGlossary');

    }

}