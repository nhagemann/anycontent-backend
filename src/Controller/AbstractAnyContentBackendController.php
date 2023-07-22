<?php

namespace AnyContent\Backend\Controller;

use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use AnyContent\Backend\Services\MenuManager;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Repository;
use CMDL\ContentTypeDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAnyContentBackendController extends AbstractController
{
    public function __construct(
        protected RepositoryManager $repositoryManager,
        protected ContextManager $contextManager,
        protected FormManager $formManager,
        protected MenuManager $menuManager,
        protected EventDispatcherInterface $dispatcher
    ) {
    }

    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters['anycontent'] = [];
        $parameters['anycontent']['context'] = $this->contextManager;
        $parameters['anycontent']['repositories'] = $this->repositoryManager;

        $workspaces = [];
        $workspaces['active'] = false;

        $contentTypeDefinition = $this->contextManager->getCurrentContentType();

        if ($contentTypeDefinition) {
            $workspaces['current'] = $this->contextManager->getCurrentWorkspace();
            $workspaces['currentName'] = $this->contextManager->getCurrentWorkspaceName();
            if (count($contentTypeDefinition->getWorkspaces()) > 1) {
                $workspaces['list']   = $contentTypeDefinition->getWorkspaces();
                $workspaces['active'] = true;
            }
        }

        $parameters['workspaces'] = $workspaces;

        $languages = [];
        $languages['active'] = false;

        $contentTypeDefinition = $this->contextManager->getCurrentContentType();

        if ($contentTypeDefinition) {
            $languages['current'] = $this->contextManager->getCurrentLanguage();
            $languages['currentName'] = $this->contextManager->getCurrentLanguageName();
            if ($contentTypeDefinition->hasLanguages()) {
                $languages['active'] = true;
                $languages['list']   = $contentTypeDefinition->getLanguages();
            }
        }

        $parameters['languages'] = $languages;

        $parameters['menu_mainmenu'] = $this->menuManager->renderMainMenu();


        // TimeShift Setup
        $date = new \DateTime();

        //$timeshift              = $app['layout']->getVar('timeshift', array());
        $timeshift = [];
        $timeshift['active']    = false;
        $timeshift['date']      = $date->format('d.m.Y');
        $timeshift['time']      = $date->format('H:i');
        $timeshift['timestamp'] = time();

        if ($this->contextManager->getCurrentTimeShift() != 0)
        {
            $date->setTimestamp($this->contextManager->getCurrentTimeShift());
            $timeshift['active']    = true;
            $timeshift['timestamp'] = $this->contextManager->getCurrentTimeShift();
            $timeshift['date']      = $date->format('d.m.Y');
            $timeshift['time']      = $date->format('H:i');
        }
        $parameters['timeshift']=$timeshift;


        return parent::render($view, $parameters, $response);
    }

    protected function getButtons($contentTypeAccessHash, ContentTypeDefinition $contentTypeDefinition): array
    {
        $buttons = [];
        $buttons[100] = [
            'label' => 'List Records',
            'url' => $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage(), 'q' => '']),
            'glyphicon' => 'glyphicon-list'];

        if ($contentTypeDefinition->isSortable()) {
            $buttons[200] = ['label' => 'Sort Records',
                'url' => $this->generateUrl('anycontent_records_sort', ['contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]),
                'glyphicon' => 'glyphicon-move'];
        }

        $buttons[300] = ['label' => 'Add Record',
            'url' => $this->generateUrl('anycontent_record_add', ['contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]),
            'glyphicon' => 'glyphicon-plus'];

        $buttons[400] = ['label' => 'Export Records', 'url' => $this->generateUrl('anycontent_records_export_modal', ['contentTypeAccessHash' => $contentTypeAccessHash]), 'glyphicon' => 'glyphicon-cloud-download', 'id' => 'listing_button_export'];

        $buttons[500] = ['label' => 'Import Records', 'url' => $this->generateUrl('anycontent_records_import_modal', ['contentTypeAccessHash' => $contentTypeAccessHash]), 'glyphicon' => 'glyphicon-cloud-upload', 'id' => 'listing_button_import'];
        return $buttons;
    }

    protected function updateContext($contentTypeAccessHash, $workspace, $language): Repository
    {
        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if (!$repository) {
            throw new NotFoundHttpException();
        }
        $this->contextManager->setCurrentRepository($repository);

        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $this->contextManager->setCurrentContentType($contentTypeDefinition);

        if ($workspace != null && $contentTypeDefinition->hasWorkspace($workspace)) {
            $this->contextManager->setCurrentWorkspace($workspace);
        }
        if ($language != null && $contentTypeDefinition->hasLanguage($language)) {
            $this->contextManager->setCurrentLanguage($language);
        }

        $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
        $repository->selectLanguage($this->contextManager->getCurrentLanguage());

        $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
        $repository->selectView('default');
        return $repository;
    }

    protected function updateContextByConfigTypeAccessHash($configTypeAccessHash, $workspace, $language): Repository
    {
        $repository = $this->repositoryManager->getRepositoryByConfigTypeAccessHash($configTypeAccessHash);

        if (!$repository) {
            throw new NotFoundHttpException();
        }
        $this->contextManager->setCurrentRepository($repository);

        $configTypeDefinition = $this->repositoryManager->getConfigTypeDefinitionByConfigTypeAccessHash($configTypeAccessHash);
        $this->contextManager->setCurrentConfigType($configTypeDefinition);

        if ($workspace != null && $configTypeDefinition->hasWorkspace($workspace)) {
            $this->contextManager->setCurrentWorkspace($workspace);
        }
        if ($language != null && $configTypeDefinition->hasLanguage($language)) {
            $this->contextManager->setCurrentLanguage($language);
        }

        $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
        $repository->selectLanguage($this->contextManager->getCurrentLanguage());

        $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
        $repository->selectView('default');
        return $repository;
    }

    protected function addRepositoryLinks(array &$vars, Repository $repository, $page)
    {
        $repositoryAccessHash        = $this->repositoryManager->getRepositoryAccessHash($repository);
        $contentTypeAccessHash = $this->repositoryManager->getContentTypeAccessHash($repository, $repository->getCurrentContentTypeName());

        $vars['links']['repository'] = $this->generateUrl('anycontent_repository', ['repositoryAccessHash' => $repositoryAccessHash]);
        $vars['links']['self']       = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash]);
        $vars['links']['listRecords']       = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash]);

        // sorting links

        $vars['links']['search']         = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 's' => 'name', 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]);
        $vars['links']['closeSearchBox'] = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 'q' => '']);

        // context links
        $vars['links']['timeshift']  = '';//$this->generateUrl('timeShiftListRecords', array( 'contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page ));
        $vars['links']['workspaces'] = $this->generateUrl('anycontent_records_change_workspace', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page]);
        $vars['links']['languages'] = $this->generateUrl('anycontent_records_change_language', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page]);
        $vars['links']['reset']      = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 'q' => '']);
    }
}
