<?php

namespace AnyContent\Backend\Controller;

use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use AnyContent\Backend\Services\MenuManager;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Repository;
use CMDL\ContentTypeDefinition;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
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
        // enable access to context and repository manager within template
        $parameters['anycontent'] = [];
        $parameters['anycontent']['context'] = $this->contextManager;
        $parameters['anycontent']['repositories'] = $this->repositoryManager;

        // provide selectable workspaces
        $workspaces = [];
        $workspaces['active'] = false;

        $dataTypeDefinition = $this->contextManager->getCurrentDataTypeDefinition();

        if ($dataTypeDefinition) {
            $workspaces['current'] = $this->contextManager->getCurrentWorkspace();
            $workspaces['currentName'] = $this->contextManager->getCurrentWorkspaceName();
            if (count($dataTypeDefinition->getWorkspaces()) > 1) {
                $workspaces['list'] = $dataTypeDefinition->getWorkspaces();
                $workspaces['active'] = true;
            }
        }

        $parameters['workspaces'] = $workspaces;

        //provide selectable languages
        $languages = [];
        $languages['active'] = false;

        if ($dataTypeDefinition) {
            $languages['current'] = $this->contextManager->getCurrentLanguage();
            $languages['currentName'] = $this->contextManager->getCurrentLanguageName();
            if ($dataTypeDefinition->hasLanguages()) {
                $languages['active'] = true;
                $languages['list'] = $dataTypeDefinition->getLanguages();
            }
        }

        $parameters['languages'] = $languages;

        // add main menu

        $parameters['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        // provide timeshift information
        $date = new DateTime();

        $timeshift = [];
        $timeshift['active'] = false;
        $timeshift['date'] = $date->format('d.m.Y');
        $timeshift['time'] = $date->format('H:i');
        $timeshift['timestamp'] = time();

        if ($this->contextManager->getCurrentTimeShift() != 0) {
            $date->setTimestamp($this->contextManager->getCurrentTimeShift());
            $timeshift['active'] = true;
            $timeshift['timestamp'] = $this->contextManager->getCurrentTimeShift();
            $timeshift['date'] = $date->format('d.m.Y');
            $timeshift['time'] = $date->format('H:i');
        }
        $parameters['timeshift'] = $timeshift;

        return parent::render($view, $parameters, $response);
    }

    protected function getButtons($contentTypeAccessHash, ContentTypeDefinition $contentTypeDefinition): array
    {
        $buttons = [];
        $buttons[100] = [
            'label' => 'List Records',
            'url' => $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage(), 'q' => '']),
            'glyphicon' => 'glyphicon-list',
        ];

        if ($contentTypeDefinition->isSortable()) {
            $buttons[200] = [
                'label' => 'Sort Records',
                'url' => $this->generateUrl('anycontent_records_sort', ['contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]),
                'glyphicon' => 'glyphicon-move',
            ];
        }

        $buttons[300] = [
            'label' => 'Add Record',
            'url' => $this->generateUrl('anycontent_record_add', ['contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]),
            'glyphicon' => 'glyphicon-plus',
        ];

        $buttons[400] = [
            'label' => 'Export Records',
            'url' => $this->generateUrl('anycontent_records_export_modal', ['contentTypeAccessHash' => $contentTypeAccessHash]),
            'glyphicon' => 'glyphicon-cloud-download',
            'id' => 'listing_button_export',
        ];

        $buttons[500] = [
            'label' => 'Import Records',
            'url' => $this->generateUrl('anycontent_records_import_modal', ['contentTypeAccessHash' => $contentTypeAccessHash]),
            'glyphicon' => 'glyphicon-cloud-upload',
            'id' => 'listing_button_import',
        ];
        return $buttons;
    }

    protected function updateContextByContentTypeAccessHash($contentTypeAccessHash, $workspace, $language): Repository
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

    protected function addRepositoryLinks(array &$parameters, Repository $repository, $page = null)
    {
        $repositoryAccessHash = $this->repositoryManager->getRepositoryAccessHash($repository);
        $parameters['links']['repository'] = $this->generateUrl('anycontent_repository', ['repositoryAccessHash' => $repositoryAccessHash]);
        $parameters['repository'] = $repository;

        if (!$this->contextManager->isContentContext()) {
            return;
        }

        $contentTypeAccessHash = $this->repositoryManager->getContentTypeAccessHash($repository, $repository->getCurrentContentTypeName());

        $parameters['links']['self'] = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash]);
        $parameters['links']['listRecords'] = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash]);

        // sorting links

        $parameters['links']['search'] = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 's' => 'name', 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]);
        $parameters['links']['closeSearchBox'] = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 'q' => '']);

        if ($page === null) {
            return;
        }
        // default content type specific context links
        $parameters['links']['workspaces'] = $this->generateUrl('anycontent_records_change_workspace', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page]);
        $parameters['links']['languages'] = $this->generateUrl('anycontent_records_change_language', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page]);
        $parameters['links']['reset'] = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => 1, 'q' => '']);
    }
}
