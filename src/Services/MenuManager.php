<?php

namespace AnyContent\Backend\Services;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class MenuManager
{
    public function __construct(
        private RepositoryManager $repositoryManager,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function renderMainMenu()
    {
        $items = [];

        foreach ($this->repositoryManager->listRepositories() as $repositoryName => $repositoryItem) {
            $url = $this->urlGenerator->generate('anycontent_repository', ['repositoryAccessHash' => $repositoryItem['accessHash']]);
            $items[] = ['type' => 'header', 'text' => $repositoryItem['title'], 'url' => $url];

            foreach ($this->repositoryManager->listContentTypes($repositoryName) as $contentTypeItem) {
                $url = $this->urlGenerator->generate('anycontent_records', ['contentTypeAccessHash' => $contentTypeItem['accessHash'], 'page' => 1]);
                $items[] = ['type' => 'link', 'text' => $contentTypeItem['title'], 'url' => $url, 'glyphicon' => 'glyphicon-file'];
            }
            foreach ($this->repositoryManager->listConfigTypes($repositoryName) as $configTypeItem) {
                $url = $this->urlGenerator->generate('anycontent_config', ['configTypeAccessHash' => $configTypeItem['accessHash']]);
                $items[] = ['type' => 'link', 'text' => $configTypeItem['title'], 'url' => $url, 'glyphicon' => 'glyphicon-wrench'];
            }
            if ($this->repositoryManager->hasFiles($repositoryName)) {
                $url = $this->urlGenerator->generate('anycontent_files', ['repositoryAccessHash' => $repositoryItem['accessHash'], 'path' => '']);
                $items[] = ['type' => 'link', 'text' => 'Files', 'url' => $url, 'glyphicon' => 'glyphicon-folder-open'];
            }

            $items[] = ['type' => 'divider'];
        }

        if ($this->authorizationChecker->isGranted('ROLE_ANYCONTENT_ADMIN')) {
            $url = $this->urlGenerator->generate('anycontent_admin');
            $items[] = ['type' => 'link', 'text' => 'Admin', 'url' => $url, 'glyphicon' => 'glyphicon-cog'];
        }

        $url = $this->urlGenerator->generate('anycontent_help');
        $items[] = ['type' => 'link', 'text' => 'Help', 'url' => $url, 'glyphicon' => 'glyphicon-book'];

        $items[] = ['type' => 'divider'];

        $url = $this->urlGenerator->generate('anycontent_logout');
        $items[] = ['type' => 'link', 'text' => 'Logout', 'url' => $url, 'glyphicon' => 'glyphicon-user'];

        return $this->renderDropDown($items, 'mainmenu');
    }

    public function renderDropDown($items, $id = null)
    {
        $vars = ['items' => $items, 'id' => $id];

//        $event = new MenuMainMenuRenderEvent($this->app, 'core_menu_dropdown.twig', $vars);
//
//        /** @var MenuMainMenuRenderEvent $event */
//        $event    = $this->app['dispatcher']->dispatch(Module::EVENT_MENU_MAINMENU_RENDER, $event);
//        $template = $event->getTemplate();
//        $vars     = $event->getVars();

        $template = '@AnyContentBackend\Menu\core_menu_dropdown.twig';

        return $this->twig->render($template, $vars);
    }

    public function renderButtonGroup($buttons)
    {
        ksort($buttons);

//        /** @var MenuButtonGroupRenderEvent $event */
//        $event = new MenuButtonGroupRenderEvent($this->app, $buttons);
//
//        $event = $this->app['dispatcher']->dispatch(Module::EVENT_MENU_BUTTONGROUP_RENDER, $event);
//
//        $buttons = $event->getButtons();

        return $this->twig->render('@AnyContentBackend\Menu\core_menu_buttongroup.twig', ['buttons' => $buttons]);
    }
}