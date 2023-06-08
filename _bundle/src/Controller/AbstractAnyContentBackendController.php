<?php

namespace AnyContent\Backend\Controller;

use AnyContent\Backend\Services\ContentViewsManager;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\MenuManager;
use AnyContent\Backend\Services\RepositoryManager;
use CMDL\ContentTypeDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAnyContentBackendController extends AbstractController
{

    public function __construct(
        protected RepositoryManager $repositoryManager,
        protected ContextManager $contextManager,
        protected MenuManager $menuManager
    )
    {
    }
    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters['anycontent']=[];
        $parameters['anycontent']['context']=$this->contextManager;
        $parameters['anycontent']['repositories']=$this->repositoryManager;

        $workspaces = [];
        $workspaces['active'] = false;

        /** @var ContentTypeDefinition $contentType */
        $contentTypeDefinition = $this->contextManager->getCurrentContentType();

        if ($contentTypeDefinition)
        {
            if (count($contentTypeDefinition->getWorkspaces()) > 1)
            {
                $workspaces['list']   = $contentTypeDefinition->getWorkspaces();
                $workspaces['active'] = true;
                $workspaces['current'] = $this->contextManager->getCurrentWorkspace();
                $workspaces['currentName'] = $this->contextManager->getCurrentWorkspaceName();
            }
        }

        $parameters['workspaces']=$workspaces;

        $languages = [];
        $languages['active'] = false;

        /** @var ContentTypeDefinition $contentType */
        $contentTypeDefinition = $this->contextManager->getCurrentContentType();

        if ($contentTypeDefinition)
        {
            if ($contentTypeDefinition->hasLanguages())
            {
                $languages['active'] = true;
                $languages['list']   = $contentTypeDefinition->getLanguages();
                $languages['current'] = $this->contextManager->getCurrentLanguage();
                $languages['currentName'] = $this->contextManager->getCurrentLanguageName();
            }
        }

        $parameters['languages']=$languages;
        return parent::render($view, $parameters, $response);
    }

}