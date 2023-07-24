<?php

namespace AnyContent\Backend\Modules\Sort\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class SortController extends AbstractAnyContentBackendController
{
    #[Route('/content/sort/{contentTypeAccessHash}/{workspace}/{language}', 'anycontent_records_sort', methods: ['GET'])]
    public function sortRecords($contentTypeAccessHash, $workspace = null, $language = null): Response
    {
        $vars = [];

        // Menu

        $vars['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        // Context

        $repository = $this->updateContext($contentTypeAccessHash, $workspace, $language);
        $vars['repository']          = $repository;

        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $vars['definition'] = $contentTypeDefinition;

        // Links

        $this->addRepositoryLinks($vars, $repository, 1);

        $vars['links']['sort']         = $this->generateUrl('anycontent_records_sort_post', ['contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]);

        $vars['links']['workspaces'] = $this->generateUrl(
            'anycontent_records_sort_change_workspace',
            ['contentTypeAccessHash' => $contentTypeAccessHash]
        );
        $vars['links']['languages'] = $this->generateUrl(
            'anycontent_records_sort_change_language',
            ['contentTypeAccessHash' => $contentTypeAccessHash]
        );

        $vars['links']['timeshift']  = $this->generateUrl('anycontent_timeshift_records_sort', ['contentTypeAccessHash' => $contentTypeAccessHash]);

        // Buttons

        $buttons         = [];
        $buttons[100] = [
            'label' => 'List Records',
            'url' => $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage(), 'q' => '']),
            'glyphicon' => 'glyphicon-list'];

        $vars['buttons'] = $this->menuManager->renderButtonGroup($buttons);

        // Records to be sorted

        $vars['records_left']  = $repository->getSortedRecords(0);
        $vars['records_right'] = array_diff_key($repository->getRecords(), $vars['records_left']);

        return $this->render('@AnyContentBackend/Sort/sort.html.twig', $vars);
    }

    #[Route('/content/sort/{contentTypeAccessHash}/{workspace}/{language}', 'anycontent_records_sort_post', methods: ['POST'])]
    public function postSortRecords($contentTypeAccessHash, $workspace, $language, Request $request): Response
    {
        $hidden = $request->get('$hidden');

        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        $contentTypeDefinition = $repository->getContentTypeDefinition();
        $this->contextManager->setCurrentContentType($contentTypeDefinition);
        $this->contextManager->setCurrentWorkspace($hidden['workspace']);
        $this->contextManager->setCurrentLanguage($hidden['language']);

        // set workspace, language and timeshift of repository object to make sure content views are accessing the right content dimensions

        $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
        $repository->selectLanguage($this->contextManager->getCurrentLanguage());
        $repository->setTimeshift($this->contextManager->getCurrentTimeShift());

        $list = [];

        foreach (json_decode($request->get('list'), true) as $item) {
            $list[$item['id']] = $item['parent_id'];
        }

        $repository->sortRecords($list);

        return new RedirectResponse($this->generateUrl('anycontent_records_sort', ['contentTypeAccessHash' => $contentTypeAccessHash, 'workspace' => $workspace, 'language' => $language]));
    }
}
