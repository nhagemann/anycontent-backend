<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Menu;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use AnyContent\CMCK\Modules\Backend\Core\Menu\MenuManager;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    const EVENT_MENU_BUTTONGROUP_RENDER = 'event.menu.buttongroup.render';
    const EVENT_MENU_MAINMENU_RENDER = 'event.menu.mainmenu.render';

    public function init(Application $app, $options = array())
    {
        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');

        $app['menus'] = $app->share(function ($app)
        {
            return new MenuManager($app);
        });

    }

}