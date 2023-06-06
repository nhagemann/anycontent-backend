<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Menu;

use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;
use Symfony\Component\HttpFoundation\Session\Session;

class MenuManager
{

    protected $app;
    /**
     * @var RepositoryManager
     */
    protected $repositoryManager;
    protected $twig;
    protected $layout;
    protected $urlGenerator;
    /**
     * @var Session
     */
    protected $session;


    public function __construct($app)
    {
        $this->app               = $app;
        $this->session           = $app['session'];
        $this->repositoryManager = $app['repos'];
        $this->twig              = $app['twig'];
        $this->layout            = $app['layout'];
        $this->urlGenerator      = $app['url_generator'];

    }


    public function renderMainMenu()
    {
        if ($this->session->has('sessioncache.menu.main'))
        {
            $items = $this->session->get('sessioncache.menu.main');
        }
        else
        {

            $items = array();

            foreach ($this->repositoryManager->listRepositories() as $repositoryName => $repositoryItem)
            {

                $url     = $this->urlGenerator->generate('indexRepository', array( 'repositoryAccessHash' => $repositoryItem['accessHash'] ));
                $items[] = array( 'type' => 'header', 'text' => $repositoryItem['title'], 'url' => $url );

                foreach ($this->repositoryManager->listContentTypes($repositoryName) as $contentTypName => $contentTypeItem)
                {
                    $url     = $this->urlGenerator->generate('listRecords', array( 'contentTypeAccessHash' => $contentTypeItem['accessHash'], 'page' => 1 ));
                    $items[] = array( 'type' => 'link', 'text' => $contentTypeItem['title'], 'url' => $url, 'glyphicon' => 'glyphicon-file' );
                }
                foreach ($this->repositoryManager->listConfigTypes($repositoryName) as $configTypeName => $configTypeItem)
                {
                    $url     = $this->urlGenerator->generate('editConfig', array( 'configTypeAccessHash' => $configTypeItem['accessHash'] ));
                    $items[] = array( 'type' => 'link', 'text' => $configTypeItem['title'], 'url' => $url, 'glyphicon' => 'glyphicon-wrench' );
                }
                if ($this->repositoryManager->hasFiles($repositoryName))
                {
                    $url     = $this->urlGenerator->generate('listFiles', array( 'repositoryAccessHash' => $repositoryItem['accessHash'], 'path' => '' ));
                    $items[] = array( 'type' => 'link', 'text' => 'Files', 'url' => $url, 'glyphicon' => 'glyphicon-folder-open' );
                }
                foreach ($this->repositoryManager->listApps($repositoryName) as $appName => $appItem)
                {

                    $url     = rtrim($appItem['url'], '/') . '/' . $repositoryItem['accessHash'];
                    $items[] = array( 'type' => 'link', 'text' => $appName, 'url' => $url, 'glyphicon' => 'glyphicon-dashboard' );
                }
                $items[] = array( 'type' => 'divider' );
            }

            // Add menu items Admin and/or Help if appropriate routes exist

            if ($this->app->routeExists('admin') || $this->app->routeExists('help'))
            {
                if ($this->app->routeExists('admin'))
                {
                    $url     = $this->urlGenerator->generate('admin');
                    $items[] = array( 'type' => 'link', 'text' => 'Admin', 'url' => $url, 'glyphicon' => 'glyphicon-cog' );

                }
                if ($this->app->routeExists('help'))
                {
                    $url     = $this->urlGenerator->generate('help');
                    $items[] = array( 'type' => 'link', 'text' => 'Help', 'url' => $url, 'glyphicon' => 'glyphicon-book' );

                }
                $items[] = array( 'type' => 'divider' );
            }

            $url     = $this->urlGenerator->generate('logout');
            $items[] = array( 'type' => 'link', 'text' => 'Logout', 'url' => $url, 'glyphicon' => 'glyphicon-user' );

            $this->session->set('sessioncache.menu.main', $items);

        }
        $html = $this->renderDropDown($items, 'mainmenu');

        return $html;
    }


    public function renderDropDown($items, $id = null)
    {
        $vars = array( 'items' => $items, 'id' => $id );

        $event = new MenuMainMenuRenderEvent($this->app, 'core_menu_dropdown.twig', $vars);

        /** @var MenuMainMenuRenderEvent $event */
        $event    = $this->app['dispatcher']->dispatch(Module::EVENT_MENU_MAINMENU_RENDER, $event);
        $template = $event->getTemplate();
        $vars     = $event->getVars();

        return $this->twig->render($template, $vars);
    }


    public function renderButtonGroup($buttons)
    {

        ksort($buttons);

        /** @var MenuButtonGroupRenderEvent $event */
        $event = new MenuButtonGroupRenderEvent($this->app, $buttons);

        $event = $this->app['dispatcher']->dispatch(Module::EVENT_MENU_BUTTONGROUP_RENDER, $event);

        $buttons = $event->getButtons();

        return $this->twig->render('core_menu_buttongroup.twig', array( 'buttons' => $buttons ));
    }


    public function clearCache()
    {
        $this->session->remove('sessioncache.menu.main');
    }
}