<?php

namespace AnyContent\Backend\Controller;

use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
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
        protected FormManager $formManager,
        protected MenuManager $menuManager
    ) {
    }

    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters['anycontent'] = [];
        $parameters['anycontent']['context'] = $this->contextManager;
        $parameters['anycontent']['repositories'] = $this->repositoryManager;

        $workspaces = [];
        $workspaces['active'] = false;

        /** @var ContentTypeDefinition $contentType */
        $contentTypeDefinition = $this->contextManager->getCurrentContentType();

        if ($contentTypeDefinition) {
            if (count($contentTypeDefinition->getWorkspaces()) > 1) {
                $workspaces['list']   = $contentTypeDefinition->getWorkspaces();
                $workspaces['active'] = true;
                $workspaces['current'] = $this->contextManager->getCurrentWorkspace();
                $workspaces['currentName'] = $this->contextManager->getCurrentWorkspaceName();
            }
        }

        $parameters['workspaces'] = $workspaces;

        $languages = [];
        $languages['active'] = false;

        /** @var ContentTypeDefinition $contentType */
        $contentTypeDefinition = $this->contextManager->getCurrentContentType();

        if ($contentTypeDefinition) {
            if ($contentTypeDefinition->hasLanguages()) {
                $languages['active'] = true;
                $languages['list']   = $contentTypeDefinition->getLanguages();
                $languages['current'] = $this->contextManager->getCurrentLanguage();
                $languages['currentName'] = $this->contextManager->getCurrentLanguageName();
            }
        }

        $parameters['languages'] = $languages;

        $parameters['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        return parent::render($view, $parameters, $response);
    }

    protected function getButtons($contentTypeAccessHash, \CMDL\ContentTypeDefinition $contentTypeDefinition): array
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

        $buttons[400] = ['label' => 'Export Records', 'url' => $this->generateUrl('anycontent_records_import', ['contentTypeAccessHash' => $contentTypeAccessHash]), 'glyphicon' => 'glyphicon-cloud-download', 'id' => 'listing_button_export'];

        $buttons[500] = ['label' => 'Import Records', 'url' => $this->generateUrl('anycontent_records_export', ['contentTypeAccessHash' => $contentTypeAccessHash]), 'glyphicon' => 'glyphicon-cloud-upload', 'id' => 'listing_button_import'];
        return $buttons;
    }
}
